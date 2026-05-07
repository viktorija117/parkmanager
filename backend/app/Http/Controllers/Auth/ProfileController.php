<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'name'   => 'sometimes|string|max:120',
            'locale' => 'sometimes|in:' . implode(',', config('parking.locales')),
        ]);

        $request->user()->update($request->only('name', 'locale'));

        return response()->json(['user' => new UserResource($request->user()->fresh())]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|confirmed|min:8',
        ]);

        if (! Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => __('auth.password')]);
        }

        $request->user()->update(['password' => Hash::make($request->password)]);

        return response()->noContent();
    }
}
