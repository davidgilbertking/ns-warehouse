<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
                               'email' => 'required|email|unique:users,email,' . $user->id,
                               'role' => 'required|in:admin,user,viewer',
                               'password' => 'nullable|string|min:8',
                           ]);

        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Пользователь обновлён.');
    }


    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
                               'name' => 'required|string|max:255',
                               'email' => 'required|email|unique:users,email',
                               'password' => 'required|string|min:6|confirmed',
                               'role' => 'required|in:admin,user,viewer'
                           ]);

        \App\Models\User::create([
                                     'name' => $request->name,
                                     'email' => $request->email,
                                     'password' => bcrypt($request->password),
                                     'role' => $request->role,
                                 ]);

        return redirect()->route('admin.users.index')->with('success', 'Пользователь создан!');
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')->with('error', 'Нельзя удалить другого администратора!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Пользователь удалён!');
    }

}
