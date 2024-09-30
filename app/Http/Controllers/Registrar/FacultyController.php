<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserInformation;
use App\Models\Faculty;
use Illuminate\Support\Facades\Hash;

class FacultyController extends Controller
{
    public function getFacultyList()
    {
        return User::select('users.id', 'user_id_no', 'email_address', 'department.department_name_abbreviation', 'first_name', 'middle_name', 'last_name')
            ->leftJoin('faculty', 'users.id', '=', 'faculty.faculty_id')
            ->join('user_information', 'user_information.user_id', '=', 'users.id')
            ->leftJoin('department', 'department.id', '=', 'faculty.department_id') // Use leftJoin instead of join
            ->whereIn('user_role', ['program_head', 'faculty', 'registrar'])
            ->get();
    }

    public function addFaculty(Request $request)
    {
        $userIdExist = User::where('user_id_no', $request->user_id_no)->first();

        if ($userIdExist) {
            return response(["message" => "User ID already exists"]);
        }
        
        $user = User::create([
            'user_id_no' => $request->user_id_no,
            'password' => Hash::make($request->password),
            'user_role' => $request->user_role,
        ]);

        UserInformation::create([
            'user_id' => $user->id,
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

        Faculty::create([
            'faculty_id' => $user->id,
            'department_id' => $request->department_id,
        ]);

        return response(["message" => "success"]);
    }
}
