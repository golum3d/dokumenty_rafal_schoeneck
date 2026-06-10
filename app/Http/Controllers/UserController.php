<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderByRaw('LOWER(name) asc')->get();

        return view('users.index', [
            'users' => $users,
            'editUser' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['required', 'array'],
            'roles.*' => ['required', Rule::in([User::ROLE_USER, User::ROLE_DOCUMENT_STAFF, User::ROLE_ADMIN])],
        ]);

        User::create($data);

        return redirect()->route('users.index')->with('success', 'Nowy użytkownik został dodany.');
    }

    public function edit(User $user)
    {
        $users = User::orderByRaw('LOWER(name) asc')->get();

        return view('users.index', [
            'users' => $users,
            'editUser' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['required', 'array'],
            'roles.*' => ['required', Rule::in([User::ROLE_USER, User::ROLE_DOCUMENT_STAFF, User::ROLE_ADMIN])],
        ]);

        if (! $request->filled('password')) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Dane użytkownika zostały zaktualizowane.');
    }
}
