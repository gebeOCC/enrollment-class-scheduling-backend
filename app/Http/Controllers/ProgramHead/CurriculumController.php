<?php

namespace App\Http\Controllers\ProgramHead;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\YearLevel;
use App\Models\Curriculum;
use App\Models\CurriculumTerm;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\FuncCall;

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

        $yearLevels = YearLevel::select('year_level.id', 'year_level_name')
            ->with(['CurriculumTerm' => function ($query) use ($curriculumId) {
                $query->select('curriculum_term.id', 'curriculum_id', 'year_level_id', 'semester_id', 'semester.semester_name')
                    ->join('semester', 'semester.id', '=', 'curriculum_term.semester_id')
                    ->where('curriculum_term.curriculum_id', '=', $curriculumId)
                    ->with(['CurriculumTermSubject' => function ($query) {
                        $query->select(
                            'curriculum_term_subjects.id',
                            'curriculum_term_subjects.curriculum_term_id',
                            'curriculum_term_subjects.subject_id',
                            'curriculum_term_subjects.pre_requisite_subject_id',
                            'subjects.credit_units',
                            'subjects.lecture_hours',
                            'subjects.laboratory_hours',
                            'subjects.subject_code',
                            'subjects.descriptive_title',
                            'pre_req_subjects.subject_code AS pre_requisite_subject_code'
                        )
                            ->join('subjects', 'subjects.id', '=', 'curriculum_term_subjects.subject_id')
                            ->leftJoin('subjects AS pre_req_subjects', 'pre_req_subjects.id', '=', 'curriculum_term_subjects.pre_requisite_subject_id')
                            ->get();
                    }]);
            }])
            ->get();

        return response(['yearLevels' => $yearLevels, 'curriculumId' => $curriculumId]);
    }

    public function addCurriculumTerm(Request $request)
    {
        $currTermId = CurriculumTerm::create([
            'semester_id' => $request->semester_id,
            'year_level_id' => $request->year_level_id,
            'curriculum_id' => $request->curriculum_id,
        ]);

        return response(['message' => 'success', 'currTermId' => $currTermId->id]);
    }
}
