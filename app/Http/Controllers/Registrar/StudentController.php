<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInformation;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function getStudentList()
    {
        return User::select(
            'users.id',
            'user_id_no',
            'email_address',
            'contact_number',
            'user_information.first_name',
            'user_information.middle_name',
            'user_information.last_name',
        )
            ->join('user_information', 'user_information.user_id', '=', 'users.id')
            ->where('users.user_role', '=', 'student')
            ->get();
    }

    public function addStudent(Request $request)
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

        return response(["message" => "success"]);
    }
}
