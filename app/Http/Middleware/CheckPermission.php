<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!Auth::check()) {
            return redirect()->route('login_page');
        }

        if (!Auth::user()->hasPermissionTo($permission)) {
            // If it's an AJAX request, return a JSON response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Permission Denied',
                    'message' => 'You do not have permission to access this resource.',
                    'required_permission' => $permission
                ], 403);
            }
            
            // For regular requests, show the permission denied view
            return response()->view('errors.permission_denied', [
                'permission' => $permission
            ], 403);
        }

        return $next($request);
    }
} 