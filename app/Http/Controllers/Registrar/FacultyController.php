<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Mail\FacultyCreated;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserInformation;
use App\Models\Faculty;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class FacultyController extends Controller
{
    public function getFacultyList()
    {
        return User::select('users.id', 'user_id_no', 'email_address', 'department.department_name_abbreviation', 'first_name', 'middle_name', 'last_name')
            ->leftJoin('faculty', 'users.id', '=', 'faculty.faculty_id')
            ->join('user_information', 'user_information.user_id', '=', 'users.id')
            ->leftJoin('department', 'department.id', '=', 'faculty.department_id')
            ->whereIn('user_role', ['program_head', 'faculty', 'registrar', 'evaluator'])
            ->get();
    }

    public function addFaculty(Request $request)
    {
        $yearLastTwoDigits = date('y');

        do {
            $randomNumber = rand(0, 999);
            $randomNumberPadded = str_pad($randomNumber, 3, '0', STR_PAD_LEFT);
            $userId = "FAC-" . $yearLastTwoDigits . $randomNumberPadded;
            $userIdExist = User::where('user_id_no', $userId)->first();
        } while ($userIdExist);

        // Generate a random password
        $password = $this->generateRandomPassword();

        $user = User::create([
            'user_id_no' => $userId,
            'password' => Hash::make($password),
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

        // Send the email
        Mail::to($request->email_address)->send(new FacultyCreated($userId, $password));

        return response(["message" => "success"]);
    }

    function generateRandomPassword($length = 8)
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';

        $password = $uppercase[random_int(0, strlen($uppercase) - 1)] .
            $lowercase[random_int(0, strlen($lowercase) - 1)] .
            $numbers[random_int(0, strlen($numbers) - 1)];

        $allCharacters = $uppercase . $lowercase . $numbers;
        for ($i = 3; $i < $length; $i++) {
            $password .= $allCharacters[random_int(0, strlen($allCharacters) - 1)];
        }

        return str_shuffle($password);
    }

    public function getFacultyDetails($id)
    {
        $studentDetails = User::where('user_id_no', '=', $id)
            ->with('UserInformation')
            ->with('Faculty.Department')
            ->first();

        if (!$studentDetails) {
            return response(['message' => 'student not found']);
        }

        return response(['message' => 'success', 'studentDetails' => $studentDetails]);
    }

    public function setFacultyDepartment(Request $request)
    {
        Faculty::where('faculty_id', '=', $request->user_id)
            ->update(['department_id' => $request->department_id]);

        return response(['message' => 'success']);
    }
}
