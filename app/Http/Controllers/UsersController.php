<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index() {
        $users = User::all();

        return view('users.list_page')
            ->with('users', $users);
    }

    public function edit($user_id) {
        $user = User::findOrFail($user_id);

        return view('users.list_page')
            ->with('user', $user);
    }

    public function update(Request $request) {
        $user = User::findOrFail($request->user_id);


    }

    private function setPermissions($user) {
        //$user->givePermissionTo();
    }
}
