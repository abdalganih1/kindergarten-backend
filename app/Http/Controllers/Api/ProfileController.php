<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use App\Http\Resources\UserResource;

class ProfileController extends Controller
{
    /**
     * Show the authenticated user's profile (User + Parent/Admin profile).
     */
    public function show(Request $request)
    {
        $user = $request->user()->load(['parentProfile', 'adminProfile']); // Load relevant profile
        return new UserResource($user);
    }

    /**
     * Update the authenticated user's profile information.
     * (Focus on Parent profile data for this example)
     */
    public function update(Request $request)
    {
        $user = $request->user();
        // Assuming we are updating a Parent's profile via API
        $parentProfile = $user->parentProfile;

        if (!$parentProfile) {
             return response()->json(['error' => 'Parent profile not found for this user.'], 404);
        }

        $validatedUserData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            // Add other User fields if editable, but generally avoid email changes via API without verification
        ]);

        $validatedParentData = $request->validate([
            'contact_email' => [ // Allow updating contact email, maybe different from login email
                'sometimes',
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('parents', 'contact_email')->ignore($parentProfile->parent_id, 'parent_id'),
            ],
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
        ]);

         // Update User model
        if (!empty($validatedUserData)) {
             $user->update($validatedUserData);
        }

         // Update ParentProfile model
        $parentProfile->update($validatedParentData);


        return new UserResource($user->fresh()->load('parentProfile')); // Return fresh data
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'], // Special rule checks current password
            'password' => ['required', 'confirmed', Password::defaults()], // Use default strong password rules
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Optional: Logout other devices/tokens
        // Auth::logoutOtherDevices($validated['current_password']); // Requires confirmation

        return response()->json(['message' => 'Password updated successfully.']);
    }
}