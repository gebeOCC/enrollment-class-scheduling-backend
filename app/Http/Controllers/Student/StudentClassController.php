<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EnrolledStudent;
use App\Models\SchoolYear;
use App\Models\YearSectionSubjects;
use Illuminate\Http\Request;

class StudentClassController extends Controller
{
    public function getStudentClasses(Request $request)
    {
        $studentId = $request->user()->id;

        $currentSchoolYear = SchoolYear::select('school_years.id', 'start_year', 'end_year', 'semester_id', 'semester_name')
            ->where('is_current', '=', 1)
            ->join('semesters', 'semesters.id', '=', 'school_years.semester_id')
            ->first();

        if (!$currentSchoolYear) {
            return response(['message' => 'no current school year']);
        }

        $studentClasses = EnrolledStudent::where('student_id', '=', $studentId)
            ->with([
                'YearSection' => function ($query) use ($currentSchoolYear) {
                    $query->where('school_year_id', '=', $currentSchoolYear->id);
                },
                'User',
                'Evaluator.EvaluatorInformation',
                'StudentType',
                'YearSection.Course',
                'YearSection.YearLevel',
                'YearSection.SchoolYear.Semester',
                'StudentSubject.YearSectionSubjects.Subject',
                'StudentSubject.YearSectionSubjects.UserInformation',
                'StudentSubject.YearSectionSubjects.Room',
                'User.UserInformation'
            ])
            ->first();

        if (!$studentClasses->YearSection) {
            return response([
                'message' => 'not enrolled',
                'schoolYear' => $currentSchoolYear,
            ]);
        }

        return response(['message' => 'success', 'studentClasses' => $studentClasses, 'schoolYear' => $currentSchoolYear]);
    }

    public function
    getEnrollmentRecord(Request $request)
    {
        $studentId = $request->user()->id;

        $studentClasses = EnrolledStudent::where('student_id', '=', $studentId)
            ->with(
                'User',
                'Evaluator.EvaluatorInformation',
                'StudentType',
                'YearSection.Course',
                'YearSection.YearLevel',
                'YearSection.SchoolYear.Semester',
                'StudentSubject.YearSectionSubjects.Subject',
                'StudentSubject.YearSectionSubjects.UserInformation',
                'StudentSubject.YearSectionSubjects.Room',
                'User.UserInformation'
            )
            ->get();

        if ($studentClasses->isEmpty()) {
            return response(['message' => 'no data']);
        }

        return response(['message' => 'success', 'studentClasses' => $studentClasses]);
    }
}
