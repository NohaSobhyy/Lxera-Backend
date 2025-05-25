<?php

use App\Http\Controllers\Api\Panel\DashboardController;
use App\Http\Controllers\Api\Panel\RequirementsController;
use App\Http\Controllers\Api\Panel\SalesController;
use App\Http\Controllers\Api\Panel\UsersController;
use Illuminate\Support\Facades\Route;

Route::prefix('{url_name}')->group(function () {
    Route::middleware(['auth:api'])->group(function () {
        Route::get('/', [DashboardController::class, 'dashboard']);

        Route::group(['prefix' => 'requirements'], function () {
            Route::get('/list', [RequirementsController::class, 'index']);
            Route::get('/{id}/approve', [RequirementsController::class, 'approve']);
            Route::get('/{id}/reject', [RequirementsController::class, 'reject'])->middleware('can:admin_requirements_reject');
            Route::get('/excel', [RequirementsController::class, 'exportExcelRequirements']);
        });

        Route::get('/user_access', [SalesController::class, 'index2']);
        Route::post('/toggle_access/{id}', [SalesController::class, 'toggleAccess']);

        Route::prefix('students')->group(function () {
            Route::get('/all', [UsersController::class, 'students']);
            Route::get('/registered_users', [UsersController::class, 'RegisteredUsers']);
            Route::get('/users', [UsersController::class, 'Users']);
            Route::get('/enrollers', [UsersController::class, 'Enrollers']);
            Route::get('/direct_register', [UsersController::class, 'directRegister']);
            Route::put('/{id}', [UsersController::class, 'edit']);
            // Route::post('/update', [UsersController::class, 'update']);
            Route::get('/{id}/delete', [UsersController::class, 'destroy']);
        });

        // Route::group(['prefix' => 'students'], function () {
        //     Route::get('/scholarship', 'UserController@ScholarshipStudent');
        //     Route::get('/excel', 'UserController@exportExcelUsers');
        //     Route::get('/excelStudent', 'UserController@exportExcelStudents');
        //     Route::post('/importStudent', 'UserController@importExcelStudents');
        //     Route::post('/importScholarshipStudent', 'UserController@importExcelScholarshipStudents');
        //     Route::post('/sendStudentMail', 'UserController@sendStudentMail');
        //     Route::post('/importCourseStudent', 'UserController@importExcelCourseStudents');
        //     Route::get('/excelEnroller', 'UserController@exportExcelEnrollers');
        //     Route::get('/excelScholarship', 'UserController@exportExcelScholarship');
        //     Route::get('/excelDirectRegister', 'UserController@exportExcelDirectRegister');
        //     Route::get('/excelAll', 'UserController@exportExcelAll');
        // });


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
