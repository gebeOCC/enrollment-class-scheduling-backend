<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FacultyRole;
use Illuminate\Support\Facades\Hash;

class FacultyController extends Controller
{
    public function getFacultyList()
    {
        return User::select('users.id', 'user_id_no', 'email_address', 'department.department_name_abbreviation')
            ->selectRaw('CONCAT(first_name, " ", middle_name, " ", last_name) AS full_name')
            ->join('faculty', 'users.id', '=', 'faculty.faculty_id')
            ->join('user_information', 'user_information.user_id', '=', 'users.id')
            ->join('department', 'department.id', '=', 'faculty.department_id')
            ->whereIn('user_role', ['program_head', 'faculty', 'registrar'])
            ->get();
    }

    public function addFaculty(Request $request)
    {
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

        FacultyRole::create([
            'faculty_id_no' => $request->user_id_no,
            'department_id' => $request->department_id,
        ]);

        return response(["message" => "success"]);
    }
}
