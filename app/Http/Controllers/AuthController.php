<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Faculty;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserInformation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

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
            return response()->json(['message' => 'Invalid credentials']);
        }

        $userRole = User::select('user_role')
            ->where('user_id_no', '=', $request->user_id_no)
            ->first();

        $expiration = Carbon::now()->addWeek();
        $token = $user->createToken('auth_token', ['*'], $expiration)->plainTextToken;

        return response()->json(['message' => 'success', 'user_role' =>  $userRole->user_role])
            ->cookie('token', $token, 60 * 24 * 5);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $cookie = Cookie::forget('token');

        return response([
            'message' => 'success'
        ])->withCookie($cookie);
    }

    public function user(Request $request)
    {
        $userRole = $request->user()->user_role;
        $user = $request->user();

        $today = Carbon::now(); // Get today's date
        $twoWeeksBeforeToday = $today->copy()->subWeeks(2); // 2 weeks before today, stored separately

        // Check if enrollment preparation is within 2 weeks before today and today
        $enrollmentPreparation = SchoolYear::whereDate('start_date', '>=', $twoWeeksBeforeToday->toDateString())
            ->whereDate('start_date', '<=', $today->toDateString())
            ->exists();

        // Check if enrollment is ongoing (start_date <= today <= end_date)
        $enrollmentOngoing = SchoolYear::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->exists();

        $schoolYear = [];

        if ($enrollmentPreparation) {
            // Get the first SchoolYear record that matches the enrollment preparation criteria
            $schoolYear = SchoolYear::whereDate('start_date', '>=', $twoWeeksBeforeToday->toDateString())
                ->whereDate('start_date', '<=', $today->toDateString())
                ->first();
        } elseif ($enrollmentOngoing) {
            // Get the first SchoolYear record that matches the ongoing enrollment criteria
            $schoolYear = SchoolYear::whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
        }

        $courses = [];

        if (($userRole == 'program_head' || $userRole == 'evaluator') && ($enrollmentOngoing || $enrollmentPreparation) && ($enrollmentPreparation || $enrollmentOngoing)) {
            $courses = DB::table('course')
                ->select(DB::raw("MD5(course.id) as hashed_course_id, course_name, course_name_abbreviation"))
                ->join(
                    'department',
                    'course.department_id',
                    '=',
                    'department.id'
                )
                ->join('faculty', 'faculty.department_id', '=', 'department.id')
                ->join('users', 'faculty.faculty_id', '=', 'users.id')
                ->where('users.id', '=', $user->id)
                ->get();
        } else if ($userRole == 'registrar' && ($enrollmentPreparation || $enrollmentOngoing)) {
            $courses = Course::select(DB::raw("MD5(course.id) as hashed_course_id, course_name, course_name_abbreviation"))
                ->get();
        }

        $firstName = UserInformation::where('user_id', '=', $user->id)
            ->first()->first_name;

        return response([
            'message' => 'success',
            'user_role' => $userRole,
            'enrollmentOngoing' => $enrollmentOngoing,
            'preparation' => $enrollmentPreparation,
            'courses' => $courses,
            'schoolYear' => $schoolYear,
            'firstName' => $firstName,
            'passwordChange' => $user->password_change,
            'date' => $today,
        ]);
    }
}
