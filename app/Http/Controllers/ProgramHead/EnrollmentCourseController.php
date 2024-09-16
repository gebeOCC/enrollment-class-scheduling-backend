<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\YearLevel;
use App\Models\YearSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentCourseController extends Controller
{
    public function getYearLevelSections($hashedCourseId)
    {
        $courseId = DB::table('course')
            ->select('id')
            ->where(DB::raw('MD5(id)'), '=', $hashedCourseId)
            ->first()->id;

        $schoolYearId = SchoolYear::where('enrollment_status', '=', 'ongoing')->first()->id;

        $yearLevels = YearLevel::select('year_level.id', 'year_level_name')
        ->with(['YearSection' => function ($query) use ($schoolYearId, $courseId) {
            $query->select(
                'year_section.id',
                'year_section.school_year_id',
                'year_section.course_id',
                'year_section.year_level_id',
                'year_section.section',
                'year_section.max_students'
            )
            ->where('school_year_id', '=', $schoolYearId)
            ->where('course_id', '=', $courseId)
            ->leftJoin('enrolled_students', 'year_section.id', '=', 'enrolled_students.year_section_id')
            ->groupBy(
                'year_section.id',
                'year_section.school_year_id',
                'year_section.course_id',
                'year_section.year_level_id',
                'year_section.section',
                'year_section.max_students'
            )
            ->selectRaw('COUNT(enrolled_students.id) as student_count');
        }])
            ->get();


        return $yearLevels;
    }

    public function addNewSection(Request $request)
    {


        $courseId = DB::table('course')
            ->select('id')
            ->where(DB::raw('MD5(id)'), '=', $request->course_id)
            ->first()->id;

        $schoolYearId = SchoolYear::where('enrollment_status', '=', 'ongoing')->first()->id;

        YearSection::create([
            'school_year_id' => $schoolYearId,
            'course_id' => $courseId,
            'year_level_id' => $request->year_level_id,
            'section' => $request->section,
            'max_students' => $request->max_students,
        ]);

        return response(['message' => 'success']);
    }
}
