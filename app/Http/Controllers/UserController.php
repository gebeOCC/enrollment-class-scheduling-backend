<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function changePassword(Request $request)
    {
        User::where('id', '=', $request->id)
            ->update(['password' => Hash::make($request->password)]);

        return response(['message' => 'success']);
    }

    public function updatePassword(Request $request)
    {
        // Validate the request data
        $request->validate([
            'current_password' => 'required',
            'password' => 'required', // password confirmation and minimum length validation
        ]);

        // Get the authenticated user
        $user = $request->user();

        // Check if the current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            // Return a response with an error message if the current password is incorrect
            return response(['message' => 'The current password is incorrect']);
        }

        // Update the user's password if the current password is correct
        $user->update(['password' => Hash::make($request->password), 'password_change' => 1]);

        // Return a success response
        return response(['message' => 'success']);
    }

    public function getUserInfo(Request $request)
    {
        $userId = $request->user()->id;
        return User::where('id', '=', $userId)
            ->with('UserInformation')
            ->first();
    }
}
