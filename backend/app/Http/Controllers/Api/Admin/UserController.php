<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InviteUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return UserResource::collection(
            User::orderBy('name')->paginate(50)
        );
    }

    public function store(InviteUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => bcrypt(Str::random(16)),
            'company_id' => $request->user()->company_id,
            'is_active' => true,
        ]);

        Password::sendResetLink(['email' => $user->email]);

        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'role' => ['sometimes', Rule::in(['user', 'admin'])],
            'is_active' => ['sometimes', 'boolean'],
            'name' => ['sometimes', 'string', 'max:120'],
        ]);

        $user->update($request->only('role', 'is_active', 'name'));

        return new UserResource($user->fresh());
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->is($request->user()), 422, "Can't deactivate yourself");

        $user->update(['is_active' => false]);

        return response()->noContent();
    }
}
