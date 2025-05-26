<?php

use App\Http\Controllers\Api\Panel\DashboardController;
use App\Http\Controllers\Api\Panel\RequirementsController;
use App\Http\Controllers\Api\Panel\SalesController;
use App\Http\Controllers\Api\Panel\UsersController;
use App\Http\Controllers\Api\Admin\ServicesController;
use Illuminate\Support\Facades\Route;

Route::prefix('{url_name}')->group(function () {
    Route::middleware(['auth:api'])->group(function () {
        // User Dashboard
        Route::get('/', [DashboardController::class, 'dashboard']);

        // Admission Requirments
        Route::group(['prefix' => 'requirements'], function () {
            Route::get('/list', [RequirementsController::class, 'index']);
            Route::get('/{id}/approve', [RequirementsController::class, 'approve']);
            Route::get('/{id}/reject', [RequirementsController::class, 'reject'])->middleware('can:admin_requirements_reject');
            Route::get('/excel', [RequirementsController::class, 'exportExcelRequirements']);
        });

        // Students Permissions
        Route::prefix('permission')->group(function () {
            Route::get('/user_access', [SalesController::class, 'index2']);
            Route::post('/toggle_access/{id}', [SalesController::class, 'toggleAccess']);
            Route::get('/export', [SalesController::class, 'exportExcel']);
        });

        // Students Records
        Route::prefix('students')->group(function () {
            Route::get('/all', [UsersController::class, 'students']);
            Route::get('/excelAll', [UsersController::class, 'exportExcelAll']);
            Route::get('/registered_users', [UsersController::class, 'RegisteredUsers']);
            Route::get('/excelRegisteredUsers',  [UsersController::class, 'exportExcelRegisteredUsers']);
            Route::get('/reserve_seat', [UsersController::class, 'reserveSeat']);
            Route::get('/excelReserveSeat', [UsersController::class, 'exportExcelReserveSeat']);
            Route::get('/enrollers', [UsersController::class, 'Enrollers']);
            Route::get('/excelEnroller', [UsersController::class, 'exportExcelEnrollers']);
            Route::get('/direct_register', [UsersController::class, 'directRegister']);
            Route::get('/excelDirectRegister', [UsersController::class, 'exportExcelDirectRegister']);
            Route::get('/scholarship', [UsersController::class, 'ScholarshipStudent']);
            Route::get('/excelScholarship',  [UsersController::class, 'exportExcelScholarship']);
            Route::put('/{id}', [UsersController::class, 'update']);
            Route::delete('/{id}', [UsersController::class, 'destroy']);
        });

        // Electronic Services
        Route::apiResource('services', ServicesController::class);

        Route::prefix('courses')->group(function () {
            Route::get('/list', 'UserController@coursesList');
            Route::get('/{id}', 'UserController@Courses');
            Route::get('/groups/{id}/show', 'UserController@groupInfo');
            Route::get('/groups/{group}/edit', 'UserController@groupEdit');
            Route::put('/groups/{group}/update', 'UserController@groupUpdate');
            Route::post('/groups/{group}/change', 'UserController@changeGroup');
            Route::get('/groups/{id}/delete', 'GroupController@destroy');
            Route::get('/groups/{group}/exportExcel', 'UserController@groupExportExcel');
        });
    });
});
