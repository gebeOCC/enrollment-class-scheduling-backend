<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Registrar\DepartmentController;
use App\Http\Controllers\Registrar\RoomController;
use App\Http\Controllers\Registrar\FacultyController;
use App\Http\Controllers\Registrar\StudentController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('add-department', [DepartmentController::class, 'addDepartment']);
Route::get('get-departments-courses', [DepartmentController::class, 'getDepartmentsCourses']);
Route::get('get-departments', [DepartmentController::class, 'getDepartments']);

Route::post('add-course', [DepartmentController::class, 'addCourse']);

Route::post('add-room', [RoomController::class, 'addRoom']);
Route::get('get-rooms', [RoomController::class, 'getRooms']);
Route::post('assign-room', [RoomController::class, 'assignRoom']);
Route::post('unassign-room/{id}', [RoomController::class, 'unassignRoom']);

Route::get('get-faculty-list', [FacultyController::class, 'getFacultyList']);
Route::post('add-faculty', [FacultyController::class, 'addFaculty']);

Route::get('get-student-list', [StudentController::class, 'getStudentList']);
Route::post('add-student', [StudentController::class, 'addStudent']);

Route::get('get-drivers', [DriverController::class, 'getDrivers']);
Route::post('add-driver', [DriverController::class, 'addDriver']);
Route::get('get-driver-profile/{id}', [DriverController::class, 'getDriverProfile']);
Route::get('get-driver-credentials/{id}', [DriverController::class, 'getDriverCredentials']);
Route::get('get-driver-info/{id}', [DriverController::class, 'getDriverInfo']);
Route::post('update-driver-profile/{id}', [DriverController::class, 'updateDriverProfile']);
Route::post('update-driver-info/{id}', [DriverController::class, 'updateDriverInfo']);
Route::post('update-driver-credentials/{id}', [DriverController::class, 'updateDriverCredentials']);
Route::get('driver-travels/{id}', [DriverController::class, 'getDriverTravels']);
Route::get('travel-details/{id}', [DriverController::class, 'travelDetails']);