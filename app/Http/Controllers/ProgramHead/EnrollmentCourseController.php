<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Room;
use App\Models\SchoolYear;
use App\Models\SubjectSecondarySchedule;
use App\Models\User;
use App\Models\YearLevel;
use App\Models\YearSection;
use App\Models\YearSectionSubjects;
use Carbon\Carbon;
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

        $today = Carbon::now();
        $twoWeeksLater = Carbon::now()->addWeeks(2);

        // Attempt to find the current school year
        $currentSchoolYearenrollment = SchoolYear::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($currentSchoolYearenrollment) {
            $schoolYearId = $currentSchoolYearenrollment->id;
        } else {
            // If no current school year is found, check for one starting within the next two weeks
            $upcomingSchoolYear = SchoolYear::where('start_date', '<=', $twoWeeksLater)
                ->orderBy('start_date', 'asc') // Optional: to get the earliest upcoming year
                ->first();

            $schoolYearId = $upcomingSchoolYear ? $upcomingSchoolYear->id : null;
        }

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

        $today = Carbon::now();
        $twoWeeksLater = Carbon::now()->addWeeks(2);

        // Attempt to find the current school year
        $currentSchoolYearenrollment = SchoolYear::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($currentSchoolYearenrollment) {
            $schoolYearId = $currentSchoolYearenrollment->id;
        } else {
            // If no current school year is found, check for one starting within the next two weeks
            $upcomingSchoolYear = SchoolYear::where('start_date', '<=', $twoWeeksLater)
                ->orderBy('start_date', 'asc') // Optional: to get the earliest upcoming year
                ->first();

            $schoolYearId = $upcomingSchoolYear ? $upcomingSchoolYear->id : null;
        }

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
        return User::select('users.id', 'user_id_no', 'first_name', 'last_name', 'active')
            ->join('user_information', 'users.id', '=', 'user_information.user_id')
            ->join('faculty', 'users.id', '=', 'faculty.faculty_id')
            ->where('active', '=', 1)
            ->whereIn('users.user_role', ['faculty', 'program_head', 'registrar', 'evaluator'])
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

    public function deleteClass(Request $request)
    {
        YearSectionSubjects::where('id', '=', $request->id)
            ->delete();
        return response(['message' => 'success']);
    }

    public function deleteSecondaryClass(Request $request)
    {
        SubjectSecondarySchedule::where('id', '=', $request->id)
            ->delete();
        return response(['message' => 'success']);
    }

    public function addSecondaryClass($classId, Request $request)
    {
        SubjectSecondarySchedule::create([
            'year_section_subjects_id' => $classId,
            'room_id' => $request->room_id,
            'day' => $request->day,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response(['message' => 'success']);
    }

    public function updateClass(Request $request)
    {

        YearSectionSubjects::where('id', '=', $request->id)
            ->update([
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

        $today = Carbon::now();
        $twoWeeksLater = Carbon::now()->addWeeks(2);

        // Attempt to find the current school year
        $currentSchoolYearenrollment = SchoolYear::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($currentSchoolYearenrollment) {
            $schoolYearId = $currentSchoolYearenrollment->id;
        } else {
            // If no current school year is found, check for one starting within the next two weeks
            $upcomingSchoolYear = SchoolYear::where('start_date', '<=', $twoWeeksLater)
                ->orderBy('start_date', 'asc') // Optional: to get the earliest upcoming year
                ->first();

            $schoolYearId = $upcomingSchoolYear ? $upcomingSchoolYear->id : null;
        }

        if (!$course || !$yearLevel || !$schoolYearId) {
            return response()->json(['error' => 'Invalid course, year level, or school year'], 404);
        }

        $yearSectionId = YearSection::select('id')
            ->where('course_id', '=', $course->id)
            ->where('year_level_id', '=', $yearLevel->id)
            ->where('school_year_id', '=', $schoolYearId)
            ->where('section', '=', $section)
            ->first()
            ->id;

        if (!$yearSectionId) {
            return response()->json(['error' => 'Year section not found'], 404);
        }

        $classes = YearSectionSubjects::select(
            'lecture_hours',
            'laboratory_hours',
            'year_section_subjects.id',
            'class_code',
            'year_section_subjects.subject_id',
            'day',
            'start_time',
            'end_time',
            'room_id',
            'faculty_id',
            'subject_code',
            'descriptive_title',
            'first_name',
            'last_name',
            'room_name',
            'year_section_id'
        )
            ->join('user_information', 'year_section_subjects.faculty_id', '=', 'user_information.user_id')
            ->join('subjects', 'subjects.id', '=', 'year_section_subjects.subject_id')
            ->join('rooms', 'rooms.id', '=', 'year_section_subjects.room_id')
            ->with(['SubjectSecondarySchedule' => function ($query) {
                $query->join('rooms', 'rooms.id', '=', 'subject_secondary_schedule.room_id')
                    ->select('subject_secondary_schedule.*', 'rooms.room_name');
            }])
            ->where('year_section_id', '=', $yearSectionId)
            ->orderBy('class_code', 'asc')
            ->get();

        return response(['classes' => $classes, 'yearSectionId' => $yearSectionId]);
    }

    public function getRoomTime($roomId, $day)
    {
        $schoolYear = getPreparingOrOngoingSchoolYear()['school_year'];

        $secondarySchedule =  SubjectSecondarySchedule::select(
            'subject_secondary_schedule.year_section_subjects_id',
            'subject_secondary_schedule.id',
            'subject_secondary_schedule.start_time',
            'subject_secondary_schedule.end_time',
            'subject_secondary_schedule.day'
        )
            ->join('year_section_subjects', 'year_section_subjects.id', '=', 'subject_secondary_schedule.year_section_subjects_id')
            ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
            ->where('school_year_id', '=', $schoolYear->id)
            ->where('subject_secondary_schedule.room_id', '=', $roomId)
            ->where('subject_secondary_schedule.day', '=', $day)
            ->get();

        $mainSchedule = YearSectionSubjects::select('id', 'start_time', 'end_time', 'day')
            ->where('room_id', '=', $roomId)
            ->where('day', '=', $day)
            ->get();

        $combinedSchedule = $mainSchedule->merge($secondarySchedule);

        return response($combinedSchedule);
    }

    public function getInstructorTime($instructorId, $day)
    {
        $schoolYear = getPreparingOrOngoingSchoolYear()['school_year'];

        $secondarySchedule = SubjectSecondarySchedule::select(
            'subject_secondary_schedule.year_section_subjects_id',
            'subject_secondary_schedule.id',
            'subject_secondary_schedule.start_time',
            'subject_secondary_schedule.end_time',
            'subject_secondary_schedule.day'
        )
            ->join('year_section_subjects', 'year_section_subjects.id', '=', 'subject_secondary_schedule.year_section_subjects_id')
            ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
            ->where('school_year_id', '=', $schoolYear->id)
            ->where('faculty_id', '=', $instructorId)
            ->where('subject_secondary_schedule.day', '=', $day)
            ->get();

        $mainSchedule = YearSectionSubjects::select(
            'year_section_subjects.id',
            'start_time',
            'end_time',
            'day'
        )
            ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
            ->where('school_year_id', '=', $schoolYear->id)
            ->where('faculty_id', '=', $instructorId)
            ->where('day', '=', $day)
            ->get();

        $combinedSchedule = $mainSchedule->merge($secondarySchedule);

        return response($combinedSchedule);
    }

    public function updateSecondaryClass(Request $request)
    {
        SubjectSecondarySchedule::where('id', $request->id)
            ->update([
                'room_id' => $request->room_id,
                'day' => $request->day,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

        return response(['message' => 'success']);
    }
}
