<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Course;
use App\Models\User;
use App\Models\FacultyRole;

class DepartmentController extends Controller
{
    public function addDepartment(Request $request)
    {
        Department::create([
            'department_name' => $request->department_name,
            'department_name_abbreviation' => $request->department_name_abbreviation,
        ]);

        $departments = Department::select('id', 'department_name', 'department_name_abbreviation')
            ->with(['Course' => function ($query) {
                $query->select('id', 'department_id', 'course_name', 'course_name_abbreviation');
            }])->get();

        return response(["message" => "success", "department" =>  $departments]);
    }

    public function getDepartmentsCourses()
    {
        // Select department details and full name of program head if exists
        // $departments = Department::select(
        //     'department.id',
        //     'department.department_name',
        //     'department.department_name_abbreviation'
        // )
        //     // ->selectRaw('CONCAT(COALESCE(user_information.first_name, ""), " ", COALESCE(user_information.middle_name, ""), " ", COALESCE(user_information.last_name, "")) AS full_name')
        //     // ->join('faculty', 'department.id', '=', 'faculty.department_id')
        //     // ->join('users', 'users.id', '=', 'faculty.faculty_id')
        //     // ->join('user_information', 'users.id', '=', 'user_information.user_id')
        //     // ->where('users.user_role', '=', 'program_head')
        //     ->with(['courses' => function ($query) {
        //         $query->select('id', 'department_id', 'course_name', 'course_name_abbreviation');
        //     }])
        //     ->get();

        $departments = Department::select(
            'department.id',
            'department_name',
            'department_name_abbreviation'
        )
            ->selectRaw('CONCAT(COALESCE(user_information.first_name, ""), " ", COALESCE(user_information.middle_name, ""), " ", COALESCE(user_information.last_name, "")) AS full_name')
            ->join('faculty', 'faculty.department_id', '=', 'department.id')
            ->join('users', 'users.id', '=', 'faculty.faculty_id')
            ->join('user_information', 'users.id', '=', 'user_information.user_id')
            ->where('users.user_role', '=', 'program_head')
            ->with(['Course' => function ($query) {
                $query->select('id', 'department_id', 'course_name', 'course_name_abbreviation');
            }])
            ->get();

        return response()->json($departments);
    }




    public function addCourse(Request $request)
    {
        Course::create([
            'department_id' => $request->id,
            'course_name' => $request->course_name,
            'course_name_abbreviation' => $request->course_name_abbreviation,
        ]);

        $departments = Department::select('id', 'department_name', 'department_name_abbreviation')
            ->with(['Course' => function ($query) {
                $query->select('id', 'department_id', 'course_name', 'course_name_abbreviation');
            }])->get();

        return response(["message" => "success", "department" =>  $departments]);
    }

    public function getDepartments()
    {
        return Department::select('id', 'department_name_abbreviation')->get();
    }

    public function assignProgramHead(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => 'required|string',
            'department_id' => 'required|integer',
        ]);

        $existingRecord = FacultyRole::where('faculty_id_no', $validated['faculty_id'])->first();
        if (!$existingRecord) {
            return response()->json(["message" => "Faculty ID no doesn't exist"]);
        }

        // Check if the faculty member is associated with the specified department
        $isFacultyInDepartment = FacultyRole::where('department_id', $validated['department_id'])
            ->where('faculty_id_no', $validated['faculty_id'])
            ->first();
        if (!$isFacultyInDepartment) {
            return response()->json(["message" => "Faculty doesn't belong to that department"]);
        }

        FacultyRole::where('faculty_id_no', '=', $validated['faculty_id'])
            ->update(['faculty_role' => 'program_head']);

        // Select department details and full name of program head if exists
        $departments = Department::select(
            'department.id',
            'department_name',
            'department_name_abbreviation',
            'user_id_no'
        )
            ->selectRaw('CONCAT(COALESCE(first_name, ""), " ", COALESCE(middle_name, ""), " ", COALESCE(last_name, "")) AS full_name')
            ->leftJoin('faculty_role', function ($join) {
                $join->on('department.id', '=', 'faculty_role.department_id')
                    ->where('faculty_role.faculty_role', '=', 'program_head');
            })
            ->leftJoin('users', 'faculty_role.faculty_id_no', '=', 'users.user_id_no')
            ->with(['Course' => function ($query) {
                $query->select('id', 'department_id', 'course_name', 'course_name_abbreviation');
            }])
            ->get();

        return response(['message' => "success", "departments" => $departments]);
    }

    public function assignNewProgramHead(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => 'required|string',
            'department_id' => 'required|integer',
        ]);

        $existingRecord = FacultyRole::where('faculty_id_no', $validated['faculty_id'])->first();
        if (!$existingRecord) {
            return response()->json(["message" => "Faculty ID no doesn't exist"]);
        }

        // Check if the faculty member is associated with the specified department
        $isFacultyInDepartment = FacultyRole::where('department_id', $validated['department_id'])
            ->where('faculty_id_no', $validated['faculty_id'])
            ->first();
        if (!$isFacultyInDepartment) {
            return response()->json(["message" => "Faculty doesn't belong to that department"]);
        }

        FacultyRole::where('faculty_role', '=', "program_head")
            ->where('department_id', $validated['department_id'])
            ->update(['faculty_role' => null]);

        FacultyRole::where('faculty_id_no', '=', $validated['faculty_id'])
            ->update(['faculty_role' => 'program_head']);

        // Select department details and full name of program head if exists
        $departments = Department::select(
            'department.id',
            'department_name',
            'department_name_abbreviation',
            'user_id_no'
        )
            ->selectRaw('CONCAT(COALESCE(first_name, ""), " ", COALESCE(middle_name, ""), " ", COALESCE(last_name, "")) AS full_name')
            ->leftJoin('faculty_role', function ($join) {
                $join->on('department.id', '=', 'faculty_role.department_id')
                    ->where('faculty_role.faculty_role', '=', 'program_head');
            })
            ->leftJoin('users', 'faculty_role.faculty_id_no', '=', 'users.user_id_no')
            ->with(['Course' => function ($query) {
                $query->select('id', 'department_id', 'course_name', 'course_name_abbreviation');
            }])
            ->get();

        return response(['message' => "success", "departments" => $departments]);
    }
}
