<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Faculty;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use App\Models\User;
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

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'success', 'user_role' =>  $userRole->user_role])
            ->cookie('token', $token, 60 * 24);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        $cookie = Cookie::forget('token');

        return response([
            'message' => 'success'
        ])->withCookie($cookie);
    }

    public function user(Request $request)
    {
        $userRole = $request->user()->user_role;
        $userId = $request->user()->id;

        $today = Carbon::now();
        $twoWeeksLater = Carbon::now()->addWeeks(2);

        $enrollmentOngoing = SchoolYear::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->exists();

        $enrollmentPreparation = SchoolYear::whereDate('start_date', '<=', $twoWeeksLater)->exists();

        $courses = [];

        if ($userRole == 'program_head' && ($enrollmentOngoing || $enrollmentPreparation)) {
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
                ->where('users.id', '=', $userId)
                ->get();
        }

        return response()->json([
            'message' => 'success',
            'user_role' => $userRole,
            'enrollmentOngoing' => $enrollmentOngoing,
            'preparation' => $enrollmentPreparation,
            'courses' => $courses,
        ]);
    }
}
