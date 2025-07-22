<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post(
    "/upload-file",
    [App\Http\Controllers\FileUploadController::class, "uploadFileToCloud"]
);
Route::post('/login', [AuthController::class, 'getToken']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/import/test_cases', [ApiController::class, 'import']);
    Route::post('/export/test_cases', [ApiController::class, 'export']);
});

// GitLab API Integration Routes
Route::post('/test-gitlab-connection', function (Request $request) {
    $url = $request->input('url');
    $token = $request->input('token');
    $group = $request->input('group', null);

    if (empty($url) || empty($token)) {
        return response()->json([
            'success' => false,
            'message' => 'URL and token are required'
        ]);
    }

    try {
        // Attempt to connect to GitLab API
        $response = Http::withHeaders([
            'PRIVATE-TOKEN' => $token
        ])->get($url . '/user');

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate with GitLab: ' . $response->status() . ' ' . $response->body()
            ]);
        }

        $userData = $response->json();

        // If a group was specified, check if it exists and is accessible
        if (!empty($group)) {
            $groupResponse = Http::withHeaders([
                'PRIVATE-TOKEN' => $token
            ])->get($url . '/groups/' . urlencode($group));

            if ($groupResponse->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication successful, but the specified group "' . $group . '" was not found or is not accessible.'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully connected to GitLab as ' . $userData['name'],
            'user' => $userData['username']
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error connecting to GitLab: ' . $e->getMessage()
        ]);
    }
});

// Webhooks
Route::post('/webhooks/gitlab', function (Request $request) {
    // Process GitLab webhook
    \Log::info('GitLab Webhook received', $request->all());
    return response()->json(['status' => 'received']);
});

// Debugging endpoint to check tokens and headers
Route::post('/debug-request', function (Request $request) {
    return response()->json([
        'success' => true,
        'headers' => $request->headers->all(),
        'csrf_token_in_session' => $request->session()->token(),
        'csrf_token_in_request' => $request->header('X-CSRF-TOKEN'),
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'inputs' => $request->all()
    ]);
});
