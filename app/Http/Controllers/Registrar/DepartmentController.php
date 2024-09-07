<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Course;
use App\Models\User;

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
        $departments = Department::select(
            'department.id',
            'department_name',
            'department_name_abbreviation'
        )
            ->selectRaw('GROUP_CONCAT(DISTINCT 
                CASE 
                WHEN COALESCE(user_information.first_name, "") != "" 
                OR COALESCE(user_information.last_name, "") != "" 
                THEN TRIM(CONCAT(COALESCE(user_information.first_name, ""), " ", 
                             COALESCE(user_information.middle_name, ""), " ", 
                             COALESCE(user_information.last_name, "")))
                ELSE NULL 
                END 
                SEPARATOR ", ") AS full_name')
            ->leftJoin('faculty', 'department.id', '=', 'faculty.department_id')
            ->leftJoin('users', function ($join) {
                $join->on('faculty.faculty_id', '=', 'users.id')
                    ->where('users.user_role', '=', 'program_head');
            })
            ->leftJoin('user_information', 'users.id', '=', 'user_information.user_id')
            ->groupBy('department.id', 'department_name', 'department_name_abbreviation')
            ->orderBy('department.id')
            ->with(['Course' => function ($query) {
                $query->select('id', 'department_id', 'course_name', 'course_name_abbreviation');
            }])
            ->get();


        return response($departments);
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

    public function getDepartmentFaculties($id)
    {
        return User::select('users.id', 'user_id_no', 'users.user_role')
            ->selectRaw('CONCAT(first_name, " ", middle_name, " ", last_name) AS full_name')
            ->join('user_information', 'user_information.user_id', '=', 'users.id')
            ->join('faculty', 'faculty.faculty_id', '=', 'users.id')
            ->where('faculty.department_id', '=', $id)
            ->whereIn('user_role', ['program_head', 'faculty', 'registrar'])
            ->get();
    }

    public function assignProgramHead(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => 'required|integer',
            'department_id' => 'required|integer',
        ]);

        $existingRecord = User::where('id', $validated['faculty_id'])->first();
        if (!$existingRecord) {
            return response()->json(["message" => "Faculty ID no doesn't exist"]);
        }

        // Check if the faculty member is associated with the specified department
        // $isFacultyInDepartment = FacultyRole::where('department_id', $validated['department_id'])
        //     ->where('faculty_id_no', $validated['faculty_id'])
        //     ->first();

        // if (!$isFacultyInDepartment) {
        //     return response()->json(["message" => "Faculty doesn't belong to that department"]);
        // }

        User::where('id', '=', $validated['faculty_id'])
            ->update(['user_role' => 'program_head']);

        // Select department details and full name of program head if exists
        $departments = Department::select(
            'department.id',
            'department_name',
            'department_name_abbreviation'
        )
            ->selectRaw('GROUP_CONCAT(DISTINCT 
                CASE 
                WHEN COALESCE(user_information.first_name, "") != "" 
                OR COALESCE(user_information.last_name, "") != "" 
                THEN TRIM(CONCAT(COALESCE(user_information.first_name, ""), " ", 
                             COALESCE(user_information.middle_name, ""), " ", 
                             COALESCE(user_information.last_name, "")))
                ELSE NULL 
                END 
                SEPARATOR ", ") AS full_name')
            ->leftJoin('faculty', 'department.id', '=', 'faculty.department_id')
            ->leftJoin('users', function ($join) {
                $join->on('faculty.faculty_id', '=', 'users.id')
                    ->where('users.user_role', '=', 'program_head');
            })
            ->leftJoin('user_information', 'users.id', '=', 'user_information.user_id')
            ->groupBy('department.id', 'department_name', 'department_name_abbreviation')
            ->orderBy('department.id')
            ->with(['Course' => function ($query) {
                $query->select('id', 'department_id', 'course_name', 'course_name_abbreviation');
            }])
            ->get();

        return response(['message' => "success", "departments" => $departments]);
    }

    public function assignNewProgramHead(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => 'required|integer',
            'department_id' => 'required|integer',
        ]);

        // $existingRecord = User::where('id', $validated['faculty_id'])->first();
        // if (!$existingRecord) {
        //     return response()->json(["message" => "Faculty ID no doesn't exist"]);
        // }

        // User::where('user_role', '=', "program_head")
        //     ->join('faculty', 'users.id', '=', 'faculty.faculty_id')
        //     ->join('department', 'users.id', '=', 'department.faculty_id')
        //     ->where('department.id', '=', $validated['department_id'])
        //     ->update(['user_role' => 'faculty']);

        Department::where('department.id', '=', $validated['department_id'])
            ->join('faculty', 'department.id', '=', 'faculty.department_id')
            ->join('users', 'faculty.faculty_id', '=', 'users.id')
            ->where('user_role', '=', "program_head")
            ->update(['user_role' => 'faculty']);

        User::where('id', '=', $validated['faculty_id'])
            ->update(['user_role' => 'program_head']);

        $departments = Department::select(
            'department.id',
            'department_name',
            'department_name_abbreviation'
        )
            ->selectRaw('GROUP_CONCAT(DISTINCT 
                CASE
                WHEN COALESCE(user_information.first_name, "") != "" 
                OR COALESCE(user_information.last_name, "") != "" 
                THEN TRIM(CONCAT(COALESCE(user_information.first_name, ""), " ", 
                             COALESCE(user_information.middle_name, ""), " ", 
                             COALESCE(user_information.last_name, "")))
                ELSE NULL 
                END 
                SEPARATOR ", ") AS full_name')
            ->leftJoin('faculty', 'department.id', '=', 'faculty.department_id')
            ->leftJoin('users', function ($join) {
                $join->on('faculty.faculty_id', '=', 'users.id')
                    ->where('users.user_role', '=', 'program_head');
            })
            ->leftJoin('user_information', 'users.id', '=', 'user_information.user_id')
            ->groupBy('department.id', 'department_name', 'department_name_abbreviation')
            ->orderBy('department.id')
            ->with(['Course' => function ($query) {
                $query->select('id', 'department_id', 'course_name', 'course_name_abbreviation');
            }])
            ->get();

        return response(['message' => "success", "departments" => $departments]);
    }
}
