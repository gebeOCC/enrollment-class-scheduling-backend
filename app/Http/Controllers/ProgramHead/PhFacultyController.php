<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\Request;

class PhFacultyController extends Controller
{
    public function getFacultyList(Request $request)
    {
        $userId = $request->user()->id;
        $departmentId = Faculty::where('faculty_id', '=', $userId)->first()->department_id;

        return Faculty::where('department_id', '=', $departmentId)
            ->with('User.UserInformation')
            ->get();
    }

    public function setActive($id)
    {
        Faculty::where('faculty_id', $id)
            ->update(['active' => 1]);

        return response(['message' => "success"]);
    }

    public function setInactive($id)
    {
        Faculty::where('faculty_id', $id)
            ->update(['active' => 0]);

        return response(['message' => "success"]);
    }

    public function setFacultyEvaluator($id)
    {
        User::where('id', $id)
            ->update(['user_role' => 'evaluator']);

        return response(['message' => "success"]);
    }

    public function setFacultyFaculty($id)
    {
        User::where('id', $id)
            ->update(['user_role' => 'faculty']);

        return response(['message' => "success"]);
    }

}
