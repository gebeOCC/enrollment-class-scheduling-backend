<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Mail\StudentCreated;
use App\Models\User;
use App\Models\UserInformation;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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

    public function importStudents(Request $request)
    {
        // Check if the user ID already exists
        $userIdExist = User::where('user_id_no', $request->user_id_no)->first();
        if ($userIdExist) {
            return response(["message" => "User ID already exists"]);
        }

        // Generate a random password
        $password = $this->generateRandomPassword();

        // Create the user with the generated password
        $user = User::create([
            'user_id_no' => $request->user_id_no,
            'password' => Hash::make($password),
            'user_role' => "student",
        ]);

        // Create user information
        UserInformation::create([
            'user_id' => $user->id,
            'password' => Hash::make($request->password),
            'user_role' => $request->user_role,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'gender' => $request->gender,
            'birthday' => $request->birthday, // Use the formatted birthday
            'contact_number' => $request->contact_number,
            'email_address' => $request->email_address,
            'present_address' => $request->present_address,
            'zip_code' => $request->zip_code,
        ]);

        // send the id number and the password to the users email
        // if ($request->email_address) {
        //     Mail::to($request->email_address)->send(new StudentCreated($request->user_id_no, $password));
        // }

        sleep(2);

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
}
