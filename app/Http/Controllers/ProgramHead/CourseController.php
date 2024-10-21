<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Curriculum;

class CourseController extends Controller
{
    public function getDepartmentCourses()
    {
        $userId = Auth::user()->id;
        return DB::table('course')
            ->select(DB::raw("MD5(course.id) as hashed_course_id, course_name, course_name_abbreviation"))
            ->join('department', 'course.department_id', '=', 'department.id')
            ->join('faculty', 'faculty.department_id', '=', 'department.id')
            ->join('users', 'faculty.faculty_id', '=', 'users.id')
            ->where('users.id', '=', $userId)
            ->get();
    }

    public function getCourseCurriculums($hashedCourseId)
    {
        $course = DB::table('course')
            ->select('id')
            ->where(DB::raw('MD5(id)'), '=', $hashedCourseId)
            ->first();

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        return Curriculum::select('curriculum.id', 'course_id', 'school_year_start', 'school_year_end')
            ->join('course', 'course.id', '=', 'curriculum.course_id')
            ->where('course_id', '=', $course->id)
            ->get();
    }

    public function getCourseName($hashedCourseId)
    {
        return DB::table('course')
            ->select('id', 'course_name', 'course_name_abbreviation')
            ->where(DB::raw('MD5(id)'), '=', $hashedCourseId)
            ->first();
    }

    public function addCourseCurriculum($hashedCourseId, Request $request)
    {

        $courseId = DB::table('course')
            ->select('id')
            ->where(DB::raw('MD5(id)'), '=', $hashedCourseId)
            ->first()->id;


        $existingCurriculum = Curriculum::where('course_id', $courseId)
            ->where('school_year_start', $request->school_year_start)
            ->where('school_year_end', $request->school_year_end)
            ->first();

        if (!$existingCurriculum) {
            Curriculum::create([
                'course_id' => $courseId,
                'school_year_start' => $request->school_year_start,
                'school_year_end' => $request->school_year_end,
            ]);
        } else {
            return response()->json(['message' => 'Curriculum already exists']);
        }

        return response(['message' => 'success']);
    }
}
