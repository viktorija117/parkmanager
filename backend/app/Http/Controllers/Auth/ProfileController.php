<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class ProfileController extends Controller
{

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:120',
            'locale' => 'sometimes|in:sr,en',
        ]);
        $request->user()->update($request->only('name', 'locale'));
        return response()->json(['user' => $request->user()->fresh()]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);
        if (! Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => __('auth.password')]);
        }
        $request->user()->update(['password' => bcrypt($request->password)]);
        return response()->noContent();
    }
}
