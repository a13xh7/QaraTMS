<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /*****************************************
     *  LOGIN
     *****************************************/

    public function showLoginPage()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.login_page');
    }

    public function authorizeUser(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();
        $alwaysActiveEmails = ['admin@admin.com'];

        if ($user && !in_array(strtolower($user->email), $alwaysActiveEmails) && !$user->is_active) {
            return redirect()->route("login_page")->withErrors('Your account is deactivated. Please contact admin.');
        }

        if (Auth::attempt($credentials)) {
            return redirect()->intended('/');
        }

        return redirect()->route("login_page")->withErrors('Login details are not valid');
    }

    /*****************************************
     *  LOGOUT
     *****************************************/

    public function logout()
    {
        Session::flush();
        Auth::logout();
        return redirect()->route('login_page');
    }

    /**
     * API
     */

    public function getToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();
        $alwaysActiveEmails = ['admin@admin.com'];

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!in_array(strtolower($user->email), $alwaysActiveEmails) && !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is deactivated. Please contact admin.'],
            ]);
        }

        // Hapus token lama jika ada
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token
        ]);
    }
}
