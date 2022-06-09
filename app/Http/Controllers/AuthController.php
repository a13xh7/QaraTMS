<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    /*****************************************
     *  REGISTER
     *****************************************/

    public function showRegistrationPage()
    {
//        if(Auth::check()){
//            return view('auth.dashboard');
//        }
        return view('auth.registration_page');
    }

    public function registerUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return  redirect('/');
    }



}
