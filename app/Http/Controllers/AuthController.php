<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'user_id_no' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('user_id_no', $request->user_id_no)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $userRole = User::select('user_role')
            ->where('user_id_no', '=', $request->user_id_no)
            ->first();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'success', 'user_role' =>  $userRole->user_role])
            ->cookie('token', $token, 60 * 24);
    }

    public function logout(Request $request)
    {
        $cookie = Cookie::forget('token');

        Auth::user();
        return response([
            'message' => 'success'
        ])->withCookie($cookie);
    }

    public function user(Request $request)
    {
        return response()->json(['message' => 'success', 'user_role' => $request->user()->user_role]);
    }
}
