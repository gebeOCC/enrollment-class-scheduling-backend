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
}
