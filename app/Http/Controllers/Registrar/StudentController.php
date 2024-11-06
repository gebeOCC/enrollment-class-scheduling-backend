<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Mail\StudentCreated;
use App\Models\SchoolYear;
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

    public function addNewStudent(Request $request)
    {
        $today = Carbon::now();
        $twoWeeksLater = Carbon::now()->addWeeks(2);

        // Attempt to find the current school year
        $currentSchoolYearenrollment = SchoolYear::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($currentSchoolYearenrollment) {
            $schoolYear = $currentSchoolYearenrollment;
        } else {
            // If no current school year is found, check for one starting within the next two weeks
            $upcomingSchoolYear = SchoolYear::where('start_date', '<=', $twoWeeksLater)
                ->orderBy('start_date', 'asc') // Optional: to get the earliest upcoming year
                ->first();

            $schoolYear = $upcomingSchoolYear ? $upcomingSchoolYear : null;
        }

        do {
            $userIdNo = strval($schoolYear->start_year) . '-' . strval($schoolYear->semester_id) . '-' . strval($this->generateRandomFiveDigit());
            $userIdExist = User::where('user_id_no', $userIdNo)->first();
        } while ($userIdExist);

        // Generate a random password
        $password = $this->generateRandomPassword();

        $user = User::create([
            'user_id_no' => $userIdNo,
            'password' => Hash::make($password),
            'user_role' => 'student',
        ]);

        $contactNumber = $request->contact_number;
        if (strlen($contactNumber) === 10 && $contactNumber[0] !== '0') {
            $contactNumber = '0' . $contactNumber;
        }

        UserInformation::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'gender' => $request->gender,
            'birthday' => $request->birthday,
            'contact_number' => $contactNumber,
            'email_address' => $request->email_address,
            'present_address' => $request->present_address,
            'zip_code' => $request->zip_code,
        ]);

        $student = UserInformation::select('user_id', 'first_name', 'middle_name', 'last_name')
            ->where('user_id', '=', $user->id)
            ->first();

        // send the id number and the password to the users email
        if ($request->email_address) {
            Mail::to($request->email_address)->send(new StudentCreated($userIdNo, $password));
        }

        $studentDetails = User::where('id', '=', $user->id)
            ->with('UserInformation')->first();

        return response(["message" => "success", 'studentDetails' => $studentDetails, 'student' => $student, 'userIdNo' => $userIdNo]);
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

    public function getStudentDetails($id)
    {
        $studentDetails = User::where('user_id_no', '=', $id)
            ->with('UserInformation')
            ->first();

        if (!$studentDetails) {
            return response(['message' => 'student not found']);
        }

        return response(['message' => 'success', 'studentDetails' => $studentDetails]);
    }

    public function generateRandomFiveDigit()
    {
        return str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
    }
}
