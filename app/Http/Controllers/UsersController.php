<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;
class UsersController extends Controller
{
    /**
     * Summary of apicon
     * @var ApiController
     */
    private $apiController;

    /**
     * Summary of __construct
     * @return void
     */
    public function __construct()
    {
        $this->apiController = new ApiController();
    }
    public function index()
    {
        $users = User::query()
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('users.list_page')
            ->with('users', $users);
    }

    public function create()
    {
        if (!auth()->user()->can('manage_users')) {
            abort(403);
        }

        return view('users.create_page');
    }

    public function edit($user_id)
    {
        if (!auth()->user()->can('manage_users')) {
            abort(403);
        }

        $user = User::findOrFail($user_id);

        return view('users.edit_page')
            ->with('user', $user);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('manage_users')) {
            abort(403);
        }

        // Check if at least one permission is selected
        $hasAnyPermission = collect($request->all())->filter(function ($value, $key) {
            return (
                str_starts_with($key, 'add_edit_') ||
                str_starts_with($key, 'delete_') ||
                $key === 'manage_users'
            ) && $value === 'on';
        })->isNotEmpty();

        if (!$hasAnyPermission) {
            return back()
                ->withInput()
                ->withErrors(['permissions' => 'At least one permission must be selected.']);
        }

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

        if ($newUser) {
            $this->apiController->pushToLogDatabase("created", "user", $request);
        }

        return redirect()->route('users_list_page');
    }


    public function update(Request $request)
    {
        if (!auth()->user()->can('manage_users')) {
            abort(403);
        }

        $user = User::findOrFail($request->user_id);

        // Add validation for at least one permission
        $hasAnyPermission = collect($request->all())->filter(function ($value, $key) {
            return (
                str_starts_with($key, 'add_edit_') ||
                str_starts_with($key, 'delete_') ||
                $key === 'manage_users'
            ) && $value === 'on';
        })->isNotEmpty();

        if (!$hasAnyPermission) {
            return back()
                ->withInput()
                ->withErrors(['permissions' => 'At least one permission must be selected.']);
        }

        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $user->id
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $result = $user->save();

        if ($result) {
            $this->apiController->pushToLogDatabase("updated", "user", $request);
        }

        $this->setPermissions($request, $user);

        return redirect()->route('users_list_page');
    }

    public function destroy(Request $request)
    {
        if (!auth()->user()->can('manage_users')) {
            abort(403);
        }

        $user = User::findOrFail($request->user_id);
        $result = $user->delete();
        if ($result) {
            $this->apiController->pushToLogDatabase("deleted", "user", $request);
        }
        return redirect()->route('users_list_page');
    }

    public function toggleActive($user_id)
    {
        if (!auth()->user()->can('manage_users')) {
            abort(403);
        }

        $user = User::findOrFail($user_id);
        $user->is_active = !$user->is_active;
        $user->save();

        return redirect()->back();
    }

    private function setPermissions($request, $user)
    {

        // PROJECTS
        if ($request->add_edit_projects) {
            $user->givePermissionTo('add_edit_projects');
        } else {
            $user->revokePermissionTo('add_edit_projects');
        }

        if ($request->delete_projects) {
            $user->givePermissionTo('delete_projects');
        } else {
            $user->revokePermissionTo('delete_projects');
        }

        // REPOSITORIES
        if ($request->add_edit_repositories) {
            $user->givePermissionTo('add_edit_repositories');
        } else {
            $user->revokePermissionTo('add_edit_repositories');
        }

        if ($request->delete_repositories) {
            $user->givePermissionTo('delete_repositories');
        } else {
            $user->revokePermissionTo('delete_repositories');
        }

        // TEST SUITES
        if ($request->add_edit_test_suites) {
            $user->givePermissionTo('add_edit_test_suites');
        } else {
            $user->revokePermissionTo('add_edit_test_suites');
        }

        if ($request->delete_test_suites) {
            $user->givePermissionTo('delete_test_suites');
        } else {
            $user->revokePermissionTo('delete_test_suites');
        }

        // TEST CASES
        if ($request->add_edit_test_cases) {
            $user->givePermissionTo('add_edit_test_cases');
        } else {
            $user->revokePermissionTo('add_edit_test_cases');
        }

        if ($request->delete_test_cases) {
            $user->givePermissionTo('delete_test_cases');
        } else {
            $user->revokePermissionTo('delete_test_cases');
        }

        // USERS
        if ($request->manage_users) {
            $user->givePermissionTo('manage_users');
        } else {
            $user->revokePermissionTo('manage_users');
        }

        // TEST PLANS
        if ($request->add_edit_test_plans) {
            $user->givePermissionTo('add_edit_test_plans');
        } else {
            $user->revokePermissionTo('add_edit_test_plans');
        }

        if ($request->delete_test_plans) {
            $user->givePermissionTo('delete_test_plans');
        } else {
            $user->revokePermissionTo('delete_test_plans');
        }

        // TEST RUNS
        if ($request->add_edit_test_runs) {
            $user->givePermissionTo('add_edit_test_runs');
        } else {
            $user->revokePermissionTo('add_edit_test_runs');
        }

        if ($request->delete_test_runs) {
            $user->givePermissionTo('delete_test_runs');
        } else {
            $user->revokePermissionTo('delete_test_runs');
        }

        // DOCUMENTS
        if ($request->add_edit_documents) {
            $user->givePermissionTo('add_edit_documents');
        } else {
            $user->revokePermissionTo('add_edit_documents');
        }

        if ($request->delete_documents) {
            $user->givePermissionTo('delete_documents');
        } else {
            $user->revokePermissionTo('delete_documents');
        }
    }
}
