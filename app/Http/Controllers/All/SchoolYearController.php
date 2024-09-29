<?php

namespace App\Http\Controllers\All;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolYear;
use App\Models\Semester;

class SchoolYearController extends Controller
{
    public function addSchoolYear(Request $request)
    {
        $validatedData = $request->validate([
            'school_year' => 'required|string|max:255',
            'semester_id' => 'required|integer|exists:semester,id',
        ]);

        $existingRecord = SchoolYear::where('school_year', $validatedData['school_year'])
            ->where('semester_id', $validatedData['semester_id'])
            ->first();

        if ($existingRecord) {
            return response()->json(["message" => "School year and semester already exist"]);
        }

        SchoolYear::create([
            'school_year' => $validatedData['school_year'],
            'semester_id' => $validatedData['semester_id'],
        ]);

        $schoolYear = SchoolYear::select('school_year.id', 'school_year', 'semester_name')
            ->join('semester', 'school_year.semester_id', '=', 'semester.id')
            ->orderBy('school_year.created_at', 'asc')
            ->get();


        return response()->json(["message" => "Success", 'schoolYear' => $schoolYear], 201);
    }

    public function getSchoolYears()
    {
        $schoolYear = SchoolYear::select('school_year.id', 'school_year', 'semester_name')
            ->join('semester', 'school_year.semester_id', '=', 'semester.id')
            ->orderBy('school_year.created_at', 'asc')
            ->get();

        $semesters = Semester::select('id', 'semester_name')->get();

        return response(['school_years' => $schoolYear, 'semesters' => $semesters]);
    }

    public function getSchoolYearDetails($schoolYear, $semester)
    {
        $schoolYearDetails = SchoolYear::select('school_year.id', 'school_year.enrollment_status', 'semester.id as semester_id')
            ->join('semester', 'semester.id', '=', 'school_year.semester_id')
            ->where('school_year.school_year', '=', $schoolYear)
            ->where('semester.semester_name', '=', $semester)
            ->first();

        return response([
            "message" => "success",
            "schoolYearDetails" => $schoolYearDetails
        ]);
    }

    public function stopEnrollment($id)
    {
        SchoolYear::where('id', '=', $id)
            ->update(['enrollment_status' => 'ended']);
        return response(['message' => 'success']);
    }

    public function startEnrollment($id)
    {
        $ongoingenrollmentExist =  SchoolYear::where('enrollment_status', '=', "ongoing")->first();

        if ($ongoingenrollmentExist) {
            return response()->json(["message" => "There's current enrollent"]);
        } else {
            SchoolYear::where('id', '=', $id)
                ->update(['enrollment_status' => 'ongoing']);
        }

        return response()->json(["message" => "success"]);
    }

    public function resumeEnrollment($id)
    {
        $ongoingenrollmentExist =  SchoolYear::where('enrollment_status', '=', "ongoing")->first();

        if ($ongoingenrollmentExist) {
            return response()->json(["message" => "There's current enrollent"]);
        } else {
            SchoolYear::where('id', '=', $id)
                ->update(['enrollment_status' => 'ongoing']);
        }

        return response()->json(["message" => "success"]);
    }
}
