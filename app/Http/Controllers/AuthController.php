<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /*****************************************
     *  LOGIN
     *****************************************/

    public function showLoginPage()
    {
        if(Auth::check()){
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

        if (Auth::attempt($credentials)) {
            return redirect()->intended('/');
        }

        return redirect()->route("login_page")->withErrors('Login details are not valid');
    }

    /*****************************************
     *  LOGOUT
     *****************************************/

    public function logout() {
        Session::flush();
        Auth::logout();
        return redirect()->route('login_page');
    }
}
