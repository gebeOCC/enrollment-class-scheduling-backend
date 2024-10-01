<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\EnrolledStudent;
use App\Models\Faculty;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getCourseEnrolledStudents()
    {
        $userId = Auth::user()->id;

        $department = Faculty::select('department_id', 'faculty_id', 'department_name')
            ->where('faculty_id', '=', $userId)
            ->join('department', 'department.id', '=', 'faculty.department_id')
            ->first();

        $schoolYearId = SchoolYear::where('enrollment_status', '=', 'ongoing')->first()->id;

        $totalStudents = Course::where('department_id', '=', $department->department_id)
            ->leftJoin('year_section', 'course.id', '=', 'year_section.course_id')
            ->leftJoin('enrolled_students', function ($join) use ($schoolYearId) {
                $join->on('year_section.id', '=', 'enrolled_students.year_section_id')
                    ->where('year_section.school_year_id', '=', $schoolYearId);
            })
            ->select('course.id', 'course.course_name', 'course.course_name_abbreviation', DB::raw('COUNT(enrolled_students.id) as total_students'))
            ->groupBy('course.id', 'course.course_name', 'course.course_name_abbreviation', )
            ->get();

        $dateEnrolled = EnrolledStudent::select('date_enrolled', 'enrolled_students.year_section_id', 'course_id', 'course_name_abbreviation', DB::raw('COUNT(enrolled_students.id) as total_students'))
            ->groupBy('date_enrolled', 'enrolled_students.year_section_id', 'course_id', 'course_name_abbreviation')
            ->join('year_section', 'year_section.id', '=', 'enrolled_students.year_section_id')
            ->join('course', 'course.id', '=', 'year_section.course_id')
            ->get();

        return response(['message' => 'success', 'totalStudents' => $totalStudents, 'department' => $department, 'dateEnrolled' => $dateEnrolled]);
    }
}
