<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function getStudentList()
    {
        return User::select('id', 'user_id_no', 'email_address', 'contact_number')
            ->selectRaw('CONCAT(first_name, " ", middle_name, " ", last_name) AS full_name')
            ->where('user_role', '=', 'student')
            ->get();
    }

    public function addStudent(Request $request) {
        
        User::create([
            'user_id_no' => $request->user_id_no,
            'password' => Hash::make($request->password),
            'user_role' => $request->user_role,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'gender' => $request->gender,
            'birthday' => $request->birthday,
            'contact_number' => $request->contact_number,
            'email_address' => $request->email_address,
            'present_address' => $request->present_address,
            'zip_code' => $request->zip_code,
        ]);

        return response(["message" => "success"]);

    }
}
