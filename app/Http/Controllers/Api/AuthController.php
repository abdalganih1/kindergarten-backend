<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            // 'username' => 'required|string', // Or use email
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string', // Device name for token identification
        ]);

        // $user = User::where('username', $request->username)->first();
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')], // Use Laravel's built-in translation
            ]);
        }

        // Revoke old tokens if needed, or allow multiple logins
        // $user->tokens()->delete(); // Optional: Force single login

        // Ensure user is active and has an allowed role for API login (e.g., Parent)
        if (!$user->is_active || !in_array($user->role, ['Parent', 'Supervisor', 'Admin'])) { // Adjust allowed roles
             return response()->json(['error' => 'Account inactive or role not permitted for API access.'], 403);
        }


        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load(['parentProfile', 'adminProfile']) // Load relevant profile based on role
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request...
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}