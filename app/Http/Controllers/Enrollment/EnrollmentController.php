<?php

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\EnrolledStudent;
use App\Models\Faculty;
use App\Models\Room;
use App\Models\SchoolYear;
use App\Models\StudentSubject;
use App\Models\StudentType;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserInformation;
use App\Models\YearSection;
use App\Models\YearSectionSubjects;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\select;

class EnrollmentController extends Controller
{
    public function getYearLevelSectionSectionSubjects($courseid, $yearLevelNumber, $section)
    {

        $course = Course::select('id')
            ->where(DB::raw('MD5(id)'), '=', $courseid)
            ->first();

        $schoolYear = getPreparingOrOngoingSchoolYear()['school_year'];

        $yearSectionId = YearSection::where('course_id', '=', $course->id)
            ->where('year_level_id', '=', $yearLevelNumber)
            ->where('school_year_id', '=', $schoolYear->id)
            ->where('section', '=', $section)->first()->id;

        $classes = YearSectionSubjects::select(
            'year_section_subjects.id',
            'class_code',
            'day',
            'end_time',
            'faculty_id',
            'year_section_subjects.id',
            'room_id',
            'start_time',
            'subject_id',
            'year_section_id',
            'subject_code',
            'descriptive_title',
            'credit_units',
        )
            ->with('SubjectSecondarySchedule')
            ->join('subjects', 'subjects.id', '=', 'subject_id')
            ->where('year_section_id', '=', $yearSectionId)
            ->orderBy('class_code', 'asc')
            ->get();

        $studentType = StudentType::select('id', 'student_type_name')
            ->get();

        return response([
            'message' => 'success',
            'classes' => $classes,
            'studentType' => $studentType,
            'yearSectionId' => $yearSectionId
        ]);
    }

    public function getClasses($subjectCode)
    {
        $schoolYear = getPreparingOrOngoingSchoolYear()['school_year'];

        $subject = Subject::where('subject_code', '=', $subjectCode)->first();

        if (!$subject) {
            return response([
                'message' => 'subject not found',
            ]);
        }

        $classes = YearSectionSubjects::select(
            'year_section_subjects.id',
            'class_code',
            'day',
            'end_time',
            'faculty_id',
            'room_id',
            'start_time',
            'subject_id',
            'year_section_id',
            'subject_code',
            'descriptive_title',
            'credit_units',
            'school_year_id',
        )
            ->with('SubjectSecondarySchedule')
            ->join('subjects', 'subjects.id', '=', 'subject_id')
            ->join('year_section', 'year_section.id', '=', 'year_section_id')
            ->where('subject_id', '=', $subject->id)
            ->where('school_year_id', '=', $schoolYear->id)
            ->get();

        return response([
            'message' => 'success',
            'classes' => $classes,
        ]);
    }

    public function enrollStudent($studentId, $studentTypeId, $yearSectionId, Request $request)
    {
        $today = Carbon::now();
        $twoWeeksLater = Carbon::now()->addWeeks(2);

        $currentSchoolYearenrollment = SchoolYear::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($currentSchoolYearenrollment) {
            $schoolYearId = $currentSchoolYearenrollment->id;
        } else {
            $upcomingSchoolYear = SchoolYear::where('start_date', '<=', $twoWeeksLater)
                ->orderBy('start_date', 'asc')
                ->first();
            $schoolYearId = $upcomingSchoolYear ? $upcomingSchoolYear->id : null;
        }

        if (EnrolledStudent::select('student_id', 'year_section_id', 'school_year_id')
            ->join('year_section', 'year_section.id', '=', 'enrolled_students.year_section_id')
            ->where('student_id', '=', $studentId)
            ->where('school_year_id', '=', $schoolYearId)
            ->exists()
        ) {
            return response(['message' => 'student already enrolled']);
        }

        $studentInfo = UserInformation::select('first_name', 'middle_name', 'last_name')
            ->where('id', '=', $studentId)
            ->first();

        $firstInitial = $studentInfo->first_name[0] ?? '';
        $middleInitial = $studentInfo->middle_name[0] ?? '';
        $lastInitial = $studentInfo->last_name[0] ?? '';
        $yearLastTwoDigits = substr($currentSchoolYearenrollment->start_date, 2, 2);

        $regNo = $firstInitial . $middleInitial . $lastInitial . $yearLastTwoDigits . rand(100, 999);

        $evaluatorId = $request->user()->id;

        $enrolledStudent = EnrolledStudent::create([
            'student_id' => $studentId,
            'year_section_id' => $yearSectionId,
            'student_type_id' => $studentTypeId,
            'evaluator_id' => $evaluatorId,
            'registration_number' => $regNo,
            'enroll_type' => 'on-time',
            'date_enrolled' => now(),
        ]);

        $classes = $request->input('classes');

        foreach ($classes as $classSubject) {
            StudentSubject::create([
                'enrolled_students_id' => $enrolledStudent->id,
                'year_section_subjects_id' => $classSubject['id'],
            ]);
        }

        return response(['message' => 'success']);
    }

    public function getYearLevelSectionSectionStudents($courseid, $yearLevelNumber, $section)
    {
        $course = Course::select('id')
            ->where(DB::raw('MD5(id)'), '=', $courseid)
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

        $yearSectionId = YearSection::where('course_id', '=', $course->id)
            ->where('year_level_id', '=', $yearLevelNumber)
            ->where('school_year_id', '=', $schoolYearId)
            ->where('section', '=', $section)->first()->id;

        $students =  EnrolledStudent::where('year_section_id', '=', $yearSectionId)
            ->with('User.UserInformation')
            ->get();

        return response(['message' => 'success', 'students' => $students]);
    }

    public function getStudentEnrollmentInfo($courseid, $yearLevelNumber, $section, $studentid)
    {
        $course = Course::select('id')
            ->where(DB::raw('MD5(id)'), '=', $courseid)
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

        $yearSectionId = YearSection::where('course_id', '=', $course->id)
            ->where('year_level_id', '=', $yearLevelNumber)
            ->where('school_year_id', '=', $schoolYearId)
            ->where('section', '=', $section)->first()->id;

        $studentId = User::where('user_id_no', '=', $studentid)->first()->id;
        $students = EnrolledStudent::where('year_section_id', '=', $yearSectionId)
            ->with(
                'Evaluator.EvaluatorInformation',
                'StudentType',
                'YearSection.Course',
                'YearSection.YearLevel',
                'YearSection.SchoolYear.Semester',
                'StudentSubject.YearSectionSubjects.Subject',
                'StudentSubject.YearSectionSubjects.Instructor.InstructorInformation',
                'StudentSubject.YearSectionSubjects.Room',
                'Student.StudentInformation'
            )
            ->where('student_id', '=', $studentId)
            ->first();

        return response(['message' => 'success', 'studentinfo' => $students, 'studentId' =>  $studentid]);
    }

    public function getStudentEnrollmentSubjects($courseid, $yearLevelNumber, $section, $studentid)
    {
        $course = Course::where(DB::raw('MD5(id)'), '=', $courseid)
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

        $studentInfo = User::select('users.id', 'user_id_no', 'first_name', 'middle_name', 'last_name')
            ->where('user_id_no', '=', $studentid)
            ->join('user_information', 'users.id', '=', 'user_information.user_id')
            ->first();

        $studentEnrolled = EnrolledStudent::select('enrolled_students.id')
            ->join('year_section', 'enrolled_students.year_section_id', '=', 'year_section.id')
            ->where('student_id', '=', $studentInfo->id)
            ->where('year_section.school_year_id', '=', $schoolYearId)
            ->first();

        $classes = StudentSubject::select('student_subjects.id', 'subject_id', 'class_code', 'subject_code', 'descriptive_title', 'day', 'start_time', 'end_time', 'credit_units')
            ->join('year_section_subjects', 'year_section_subjects.id', '=', 'student_subjects.year_section_subjects_id')
            ->join('subjects', 'subjects.id', '=', 'year_section_subjects.subject_id',)
            ->where('enrolled_students_id', '=', $studentEnrolled->id)
            ->get();

        return response(['message' => 'success', 'studentInfo' => $studentInfo, 'course' =>  $course, 'classes' => $classes, 'enrolledStudentId' => $studentEnrolled->id]);
    }

    public function removeStudentSubject(Request $request)
    {
        StudentSubject::where('id', '=', $request->id)
            ->delete();
        return response(['message' => 'success']);
    }

    public function addStudentSubject(Request $request)
    {
        StudentSubject::create([
            'enrolled_students_id' =>  $request->studentId,
            'year_section_subjects_id' => $request->id
        ]);

        return response(['message' => 'success']);
    }

    public function getEnrollmentRoomSchedules()
    {
        $user = Auth::user();

        $departmentId = Faculty::where('faculty_id', '=', $user->id)->first()->department_id;

        $schoolYear = getPreparingOrOngoingSchoolYear()['school_year'];

        return Room::select('rooms.id', 'room_name')
            ->where('department_id', '=', $departmentId)
            ->with(['Schedules' => function ($query) use ($schoolYear) {
                // Primary schedules query
                $query->select(
                    'day',
                    'descriptive_title',
                    'end_time',
                    'faculty_id',
                    'year_section_subjects.id',
                    'room_id',
                    'start_time',
                    'subject_id',
                    'year_section_id',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'class_code',
                    'school_year_id'
                )
                    ->join('subjects', 'subjects.id', '=', 'year_section_subjects.subject_id')
                    ->join('users', 'users.id', '=', 'year_section_subjects.faculty_id')
                    ->join('user_information', 'users.id', '=', 'user_information.user_id')
                    ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
                    ->where('school_year_id', '=', $schoolYear->id);

                // Secondary schedules query
                $secondarySchedules = DB::table('subject_secondary_schedule')
                    ->select(
                        'subject_secondary_schedule.day',
                        'descriptive_title',
                        'subject_secondary_schedule.end_time',
                        'faculty_id',
                        'year_section_subjects.id',
                        'subject_secondary_schedule.room_id', // Correct room_id for secondary schedules
                        'subject_secondary_schedule.start_time',
                        'subject_id',
                        'year_section_id',
                        'first_name',
                        'middle_name',
                        'last_name',
                        'class_code',
                        'school_year_id'
                    )
                    ->join('year_section_subjects', 'year_section_subjects.id', '=', 'subject_secondary_schedule.year_section_subjects_id') // Corrected join condition
                    ->join('subjects', 'subjects.id', '=', 'year_section_subjects.subject_id')
                    ->join('users', 'users.id', '=', 'year_section_subjects.faculty_id')
                    ->join('user_information', 'users.id', '=', 'user_information.user_id')
                    ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
                    ->where('school_year_id', '=', $schoolYear->id);

                // Combine primary and secondary schedules using union
                $query->union($secondarySchedules);
            }])
            ->get();
    }

    public function getEnrollmentFacultySchedules()
    {
        $user = Auth::user();

        $departmentId = Faculty::where('faculty_id', '=', $user->id)->first()->department_id;

        $schoolYear = getPreparingOrOngoingSchoolYear()['school_year'];

        return User::select('users.id', 'faculty_id', 'first_name', 'middle_name', 'last_name', 'active')
            ->with(['Schedules' => function ($query) use ($schoolYear) {
                $query->select('room_name', 'day', 'descriptive_title', 'end_time', 'faculty_id', 'year_section_subjects.id', 'room_id', 'start_time', 'subject_id', 'year_section_id', 'class_code', 'school_year_id')
                    ->join('subjects', 'subjects.id', '=', 'year_section_subjects.subject_id')
                    ->join('rooms', 'rooms.id', '=', 'year_section_subjects.room_id')
                    ->join('year_section', 'year_section.id', '=', 'year_section_subjects.year_section_id')
                    ->with('SubjectSecondarySchedule.Room')
                    ->where('school_year_id', '=', $schoolYear->id);
            }])
            ->join('faculty', 'users.id', '=', 'faculty.faculty_id')
            ->join('user_information', 'users.id', '=', 'user_information.user_id')
            ->where('department_id', '=', $departmentId)
            ->where('active', '=', 1)
            ->get();
    }
}
