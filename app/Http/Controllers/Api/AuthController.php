<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle a login request for the API.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required', // Good practice to know which device is logging in
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // --- Optional: Add role check for security ---
        // Ensure only Field Officers or other authorized roles can log in via the API
        if (!$user->hasRole(['Field Officer', 'Branch Manager', 'Organization Admin'])) {
            throw ValidationException::withMessages([
                'email' => ['You do not have permission to access this application.'],
            ]);
        }

        // --- Revoke all old tokens and create a new one ---
        // This ensures a user can only be logged in on one mobile device at a time.
        // You can remove this line if you want to allow multiple logins.
        $user->tokens()->delete();

        $token = $user->createToken($request->device_name)->plainTextToken;

        // Return the token and user info in the response
        return response()->json([
            'token' => $token,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first()
            ]
        ]);
    }

    /**
     * Handle a logout request for the API.
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
