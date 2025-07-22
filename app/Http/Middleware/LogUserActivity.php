<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Log as ActivityLog;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if (auth()->check()) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Log user activity
     */
    private function logActivity(Request $request, Response $response): void
    {
        try {
            // Skip logging for certain routes or methods
            if ($this->shouldSkipLogging($request)) {
                return;
            }

            $user = auth()->user();
            $method = $request->method();
            $path = $request->path();
            $statusCode = $response->getStatusCode();

            // Determine log level based on response status
            $level = $this->getLogLevel($statusCode);

            // Create activity log entry
            ActivityLog::create([
                'level' => $level,
                'message' => "User {$user->name} ({$user->email}) performed {$method} on {$path}",
                'context' => [
                    'user_id' => $user->id,
                    'method' => $method,
                    'path' => $path,
                    'status_code' => $statusCode,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'request_data' => $this->sanitizeRequestData($request),
                    'response_size' => $this->getResponseSize($response),
                ],
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

        } catch (\Exception $e) {
            // Log the error but don't break the application
            Log::error('Failed to log user activity', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'path' => $request->path(),
            ]);
        }
    }

    /**
     * Determine if logging should be skipped for this request
     */
    private function shouldSkipLogging(Request $request): bool
    {
        $skipPaths = [
            'api/health',
            'api/ping',
            'favicon.ico',
            'robots.txt',
        ];

        $skipMethods = [
            'OPTIONS',
        ];

        // Skip for certain paths
        foreach ($skipPaths as $path) {
            if (str_contains($request->path(), $path)) {
                return true;
            }
        }

        // Skip for certain methods
        if (in_array($request->method(), $skipMethods)) {
            return true;
        }

        return false;
    }

    /**
     * Get log level based on HTTP status code
     */
    private function getLogLevel(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'error';
        } elseif ($statusCode >= 400) {
            return 'warning';
        } elseif ($statusCode >= 300) {
            return 'info';
        } else {
            return 'info';
        }
    }

    /**
     * Sanitize request data for logging
     */
    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();

        // Remove sensitive fields
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***REDACTED***';
            }
        }

        return $data;
    }

    /**
     * Get response size in bytes
     */
    private function getResponseSize(Response $response): int
    {
        $content = $response->getContent();
        return $content ? strlen($content) : 0;
    }
}
