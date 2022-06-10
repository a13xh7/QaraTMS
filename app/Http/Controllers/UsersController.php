<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index() {
        $users = User::all();

        return view('users.list_page')
            ->with('users', $users);
    }

    public function create() {
        return view('users.create_page');
    }

    public function edit($user_id) {
        $user = User::findOrFail($user_id);

        return view('users.edit_page')
            ->with('user', $user);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $this->setPermissions($request, $newUser);

        return redirect()->route('users_list_page');
    }



    public function update(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$user->id
        ]);


        $user->name = $request->name;
        $user->email = $request->email;

        if($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $this->setPermissions($request, $user);

        return redirect()->route('users_list_page');
    }

    public function destroy(Request $request) {
        $user = User::findOrFail($request->user_id);
        $user->delete();
        return redirect()->route('users_list_page');
    }


    private function setPermissions($request, $user) {

        // PROJECTS
        if($request->add_edit_projects) {
            $user->givePermissionTo('add_edit_projects');
        } else {
            $user->revokePermissionTo('add_edit_projects');
        }

        if($request->delete_projects) {
            $user->givePermissionTo('delete_projects');
        } else {
            $user->revokePermissionTo('delete_projects');
        }

        // REPOSITORIES
        if($request->add_edit_repositories) {
            $user->givePermissionTo('add_edit_repositories');
        } else {
            $user->revokePermissionTo('add_edit_repositories');
        }

        if($request->delete_repositories) {
            $user->givePermissionTo('delete_repositories');
        } else {
            $user->revokePermissionTo('delete_repositories');
        }
    }
}
