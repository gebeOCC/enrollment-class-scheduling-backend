<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Course;

class DepartmentController extends Controller
{
    public function addDepartment(Request $request) {
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
        $departments = Department::select('id', 'department_name', 'department_name_abbreviation')
            ->with(['Course' => function ($query) {
                $query->select('id', 'department_id', 'course_name', 'course_name_abbreviation');
            }])->get();

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

    public function getDepartments() {
        return Department::select('id', 'department_name_abbreviation')->get();
    }
}
