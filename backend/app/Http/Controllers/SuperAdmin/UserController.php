<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'admin');
        $search = $request->get('search');
        $roleFilter = $request->get('role');

        $query = User::with('role');

        if ($tab === 'admin') {
            $query->whereHas('role', function($q) {
                $q->where('name', 'admin');
            });
        } elseif ($tab === 'tables') {
            // For tables tab - we'll show tables management
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('username', 'like', '%' . $search . '%');
            });
        }

        if ($roleFilter) {
            $query->whereHas('role', function($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        $users = $query->latest()->paginate(10);

        return view('superadmin.users.index', compact('users', 'tab'));
    }

    public function create()
    {
        return view('superadmin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Create admin role for this user
        Role::create([
            'user_id' => $user->id,
            'name' => 'admin'
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'Admin user created successfully!');
    }    public function edit(User $user)
    {
        return view('superadmin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User updated successfully!');
    }    public function destroy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'Cannot delete superadmin user!');
        }

        $user->delete();

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User deleted successfully!');
    }
}
