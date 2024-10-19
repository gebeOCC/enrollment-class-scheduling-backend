<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
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
}
