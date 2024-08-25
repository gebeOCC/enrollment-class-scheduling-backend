<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Registrar\DepartmentController;
use App\Http\Controllers\Registrar\RoomController;
use App\Http\Controllers\Registrar\FacultyController;
use App\Http\Controllers\Registrar\StudentController;
use App\Http\Controllers\All\SemesterController;
use App\Http\Controllers\All\SchoolYearController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('add-department', [DepartmentController::class, 'addDepartment']);
Route::get('get-departments-courses', [DepartmentController::class, 'getDepartmentsCourses']);
Route::get('get-departments', [DepartmentController::class, 'getDepartments']);
Route::post('assign-program-head', [DepartmentController::class, 'assignProgramHead']);
Route::post('assign-new-program-head', [DepartmentController::class, 'assignNewProgramHead']);

Route::post('add-course', [DepartmentController::class, 'addCourse']);

Route::post('add-room', [RoomController::class, 'addRoom']);
Route::get('get-rooms', [RoomController::class, 'getRooms']);
Route::post('assign-room', [RoomController::class, 'assignRoom']);
Route::post('unassign-room/{id}', [RoomController::class, 'unassignRoom']);

Route::get('get-faculty-list', [FacultyController::class, 'getFacultyList']);
Route::post('add-faculty', [FacultyController::class, 'addFaculty']);

Route::get('get-student-list', [StudentController::class, 'getStudentList']);
Route::post('add-student', [StudentController::class, 'addStudent']);

Route::get('get-semesters', [SemesterController::class, 'getSemesters']);
Route::get('get-school-years', [SchoolYearController::class, 'getSchoolYears']);
Route::get('get-school-year-details/{schoolYear}/{semester}', [SchoolYearController::class, 'getSchoolYearDetails']);
Route::post('add-school-year', [SchoolYearController::class, 'addSchoolYear']);
Route::post('stop-enrollment/{id}', [SchoolYearController::class, 'stopEnrollment']);
Route::post('start-enrollment/{id}', [SchoolYearController::class, 'startEnrollment']);
Route::post('resume-enrollment/{id}', [SchoolYearController::class, 'resumeEnrollment']);