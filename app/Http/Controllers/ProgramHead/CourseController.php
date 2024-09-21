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
        // Find the course based on the hashed ID
        $course = DB::table('course')
            ->select('id')
            ->where(DB::raw('MD5(id)'), '=', $hashedCourseId)
            ->first();

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        return Curriculum::select('curriculum.id', 'course_id', 'school_year_id','school_year','school_year.semester_id', 'semester.semester_name')
            ->join('course', 'course.id', '=', 'curriculum.course_id')
            ->join('school_year', 'school_year.id', '=', 'curriculum.school_year_id')
            ->join('semester', 'semester.id', '=', 'school_year.semester_id')
            ->where('course_id', '=', $course->id)
            ->get();
    }

    public function getCourseName($hashedCourseId) {
        return DB::table('course')
            ->select('id', 'course_name', 'course_name_abbreviation')
            ->where(DB::raw('MD5(id)'), '=', $hashedCourseId)
            ->first();
    }

    public function addCourseCurriculum($hashedCourseId, Request $request){

        $courseId = DB::table('course')
            ->select('id')
            ->where(DB::raw('MD5(id)'), '=', $hashedCourseId)
            ->first()->id;
        
        $curriculum = Curriculum::create([
            'course_id' => $courseId,
            'school_year_id' => $request->school_year_id,
        ]);

        return response(['message' => 'success']);
    }
}
