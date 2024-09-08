<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\YearLevel;
use App\Models\Curriculum;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\DB;

class CurriculumController extends Controller
{
    public function getYearLevels()
    {
        return YearLevel::select('id', 'year_level_name')
            ->with(['CurriculumTerm' => function ($query) {
                $query->select('id', 'department_id', 'course_name', 'course_name_abbreviation');
            }])
            ->join('curriculum', 'curriculum.id', '=', 'curriculum_term.curriculum_id')
            ->where('curriculum.course_id', '=', 'curriculum_term.curriculum_id')
            ->get();
    }

    public function getCurriculumTermsSubjects($hashedCourseId, $schoolyear)
    {
        $courseId = DB::table('course')
            ->select('id')
            ->where(DB::raw('MD5(id)'), '=', $hashedCourseId)
            ->first()->id;

        $schoolYearId = SchoolYear::select('school_year.id')
            ->join('semester', 'semester.id', '=', 'school_year.semester_id')
            ->where('school_year.school_year', '=', $schoolyear)
            ->where('semester.semester_name', '=', 'First')
            ->first()->id;


        $curriculumId = Curriculum::select('curriculum.id')
            ->where('curriculum.course_id', '=', $courseId)
            ->where('curriculum.school_year_id', '=', $schoolYearId)
            ->first()->id;

        return YearLevel::select('year_level.id', 'year_level_name')
        ->with(['CurriculumTerm' => function ($query) use ($curriculumId) {
            $query->select('curriculum_term.id', 'currriculum_id', 'year_level_id', 'semester_id', 'semester.semester_name')
            ->join('semester', 'semester.id', '=', 'curriculum_term.semester_id')
            ->where('curriculum_term.currriculum_id', '=', $curriculumId)
                ->with(['CurriculumTermSubject' => function ($query) {
                    $query->select('curriculum_term_subjects.id', 'curriculum_term_subjects.curriculum_term_id', 'subject_id', 'pre_requisite_subject_id', 'credit_units', 'lecture_hours', 'laboratory_hours')
                    ->join('subjects', 'subjects.id', '=', 'curriculum_term_subjects.subject_id');
                }]);
        }])
        ->get();
    }
}
