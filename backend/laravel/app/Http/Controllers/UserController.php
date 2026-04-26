<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        return response()->json(
            User::query()
                ->with('role:id,name')
                ->latest()
                ->get()
        );
    }

    public function meta(Request $request)
    {
        $this->ensureAdmin($request);

        return response()->json([
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('role:id,name'),
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $this->ensureAdmin($request);

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string'],
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['sometimes', 'required', 'integer', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('role:id,name'),
        ]);
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless(optional($request->user()->role)->name === 'Admin', 403, 'Admin access required.');
    }
}
