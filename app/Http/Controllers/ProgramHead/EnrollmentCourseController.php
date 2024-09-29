<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Room;
use App\Models\SchoolYear;
use App\Models\User;
use App\Models\YearLevel;
use App\Models\YearSection;
use App\Models\YearSectionSubjects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ->with([
                'YearSection' => function ($query) use ($schoolYearId, $courseId) {
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
                }
            ])
            ->get();

        return $yearLevels;
    }

    public function addNewSection($hashedCourseId, Request $request)
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

        $courseId = DB::table('course')
            ->select('id')
            ->where(DB::raw('MD5(id)'), '=', $hashedCourseId)
            ->first()->id;

        $schoolYearId = SchoolYear::where('enrollment_status', '=', 'ongoing')->first()->id;

        $yearLevels = YearLevel::select('year_level.id', 'year_level_name')
            ->with([
                'YearSection' => function ($query) use ($schoolYearId, $courseId) {
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
                }
            ])
            ->get();

        return response(['message' => 'success', 'yearLevels' => $yearLevels]);
    }

    public function getDepartmentRooms()
    {
        $userId = Auth::user()->id;

        $deptId = Faculty::select('department_id')
            ->where('faculty_id', '=', $userId)
            ->first()->department_id;

        return Room::select('id', 'room_name')
            ->where('department_id', '=', $deptId)
            ->get();
    }

    public function getInstructors()
    {
        return User::select('users.id', 'user_id_no', 'first_name', 'last_name')
            ->join('user_information', 'users.id', '=', 'user_information.user_id')
            ->whereIn('users.user_role', ['faculty', 'program_head', 'registrar'])
            ->get();
    }
    public function getYearSectionId(Request $request)
    {
        $course = DB::table('course')
            ->select('id')
            ->where(DB::raw('MD5(id)'), '=', $request->course_id)
            ->first();

        $yearLevel = DB::table('year_level')
            ->select('id')
            ->where('year_level_name', '=', $request->year_level_name)
            ->first();

        $schoolYear = SchoolYear::where('enrollment_status', '=', 'ongoing')->first();

        // Check for null values before accessing id
        if (!$course || !$yearLevel || !$schoolYear) {
            return response()->json(['error' => 'Invalid course, year level, or school year'], 404);
        }

        $section = $request->section;

        $yearSectionId = YearSection::select('id')
            ->where('course_id', '=', $course->id)
            ->where('year_level_id', '=', $yearLevel->id)
            ->where('school_year_id', '=', $schoolYear->id)
            ->where('section', '=', $section)
            ->first()
            ->id;

        // Check if YearSection was found
        if (!$yearSectionId) {
            return response()->json(['error' => 'Year section not found'], 404);
        }

        return response()->json(['year_section_id' => $yearSectionId]);
    }

    public function addClass($yearSectionId, Request $request)
    {
        YearSectionSubjects::create([
            'year_section_id' => $yearSectionId,
            'faculty_id' => $request->faculty_id,
            'room_id' => $request->room_id,
            'subject_id' => $request->subject_id,
            'class_code' => $request->class_code,
            'day' => $request->day,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);
        return response(['message' => 'success']);
    }

    public function getClasses($courseId, $yearLevelName, $section)
    {
        $course = DB::table('course')
            ->select('id')
            ->where(DB::raw('MD5(id)'), '=', $courseId)
            ->first();

        $yearLevel = DB::table('year_level')
            ->select('id')
            ->where('year_level_name', '=', $yearLevelName)
            ->first();

        $schoolYear = SchoolYear::where('enrollment_status', '=', 'ongoing')->first();

        if (!$course || !$yearLevel || !$schoolYear) {
            return response()->json(['error' => 'Invalid course, year level, or school year'], 404);
        }

        $yearSectionId = YearSection::select('id')
            ->where('course_id', '=', $course->id)
            ->where('year_level_id', '=', $yearLevel->id)
            ->where('school_year_id', '=', $schoolYear->id)
            ->where('section', '=', $section)
            ->first()
            ->id;

        if (!$yearSectionId) {
            return response()->json(['error' => 'Year section not found'], 404);
        }

        $classes = YearSectionSubjects::select('year_section_subjects.id', 'class_code', 'subject_id', 'day', 'start_time', 'end_time', 'room_id', 'faculty_id', 'subject_code', 'descriptive_title', 'first_name', 'last_name', 'room_name')
            ->join('user_information', 'year_section_subjects.faculty_id', '=', 'user_information.user_id')
            ->join('subjects', 'subjects.id', '=', 'year_section_subjects.subject_id')
            ->join('rooms', 'rooms.id', '=', 'year_section_subjects.room_id')
            ->where('year_section_id', '=', $yearSectionId)
            ->get();

        return response(['classes' => $classes, 'yearSectionId' => $yearSectionId]);
    }

    public function getRoomTime($yearLevelSectionId, $roomId, $day)
    {
        return YearSectionSubjects::select('start_time', 'end_time')
            ->where('year_section_id', '=', $yearLevelSectionId)
            ->where('room_id', '=', $roomId)
            ->where('day', '=', $day)
            ->get();
    }

    public function getInstructorTime($yearLevelSectionId, $instructorId, $day)
    {
        return YearSectionSubjects::select('start_time', 'end_time')
            ->where('year_section_id', '=', $yearLevelSectionId)
            ->where('faculty_id', '=', $instructorId)
            ->where('day', '=', $day)
            ->get();
    }
}
