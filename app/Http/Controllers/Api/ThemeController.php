<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ThemeController extends Controller
{
    /**
     * Update user's theme preference
     */
    public function updatePreference(Request $request): JsonResponse
    {
        $request->validate([
            'theme' => ['required', Rule::in(['auto', 'light', 'dark'])]
        ]);

        if (!Auth::check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $user = Auth::user();
        $user->theme_preference = $request->theme;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Theme preference updated successfully',
            'theme' => $request->theme
        ]);
    }

    /**
     * Get user's current theme preference
     */
    public function getPreference(): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['theme' => 'auto']);
        }

        $user = Auth::user();

        return response()->json([
            'theme' => $user->theme_preference ?? 'auto'
        ]);
    }
}
