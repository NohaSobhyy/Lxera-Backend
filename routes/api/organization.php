<?php

use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\EnrollmentsController;
use App\Http\Controllers\Api\Admin\CodesController;
use App\Http\Controllers\Api\Panel\DashboardController;
use App\Http\Controllers\Api\Panel\RequirementsController;
use App\Http\Controllers\Api\Panel\SalesController;
use App\Http\Controllers\Api\Panel\UsersController;
use App\Http\Controllers\Api\Admin\ServicesController;
use App\Http\Controllers\Api\Admin\StudyClassesController;
use App\Http\Controllers\Api\Admin\CertificatesController;
use App\Http\Controllers\Api\Admin\WebinarCertificateController;
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
        Route::prefix('services')->group(function () {
            Route::get('', [ServicesController::class, 'index']);
            Route::get('{service}', [ServicesController::class, 'show']);
            Route::post('', [ServicesController::class, 'store']);
            Route::put('{service}', [ServicesController::class, 'update']);
            Route::delete('{service}', [ServicesController::class, 'destroy']);
            Route::get('{service}/requests', [ServicesController::class, 'requests']);
            Route::get('/requests/{service}/export', [ServicesController::class, 'exportRequests']);
        });

        // Academic Classes
        Route::prefix('classes')->group(function () {
            Route::get('/', [StudyClassesController::class, 'index']);
            Route::post('/', [StudyClassesController::class, 'store']);
            Route::put('/{class}', [StudyClassesController::class, 'update']);
            Route::delete('/{class}', [StudyClassesController::class, 'destroy']);
            Route::get('/{class}/students', [StudyClassesController::class, 'students']);
            Route::get('/{class}/excelStudent', [StudyClassesController::class, 'exportExcelBatchStudents']);
            Route::get('/{class}/registered_users', [StudyClassesController::class, 'RegisteredUsers']);
            Route::get('/{class}/users', [StudyClassesController::class, 'Users']);
            Route::get('/{class}/enrollers', [StudyClassesController::class, 'Enrollers']);
            Route::get('/{class}/direct_register', [StudyClassesController::class, 'directRegister']);
        });

        // Codes
        Route::prefix('codes')->group(function () {
            Route::get('/', [CodesController::class, 'index']);
            Route::post('/', [CodesController::class, 'store']);
            Route::get('/instructor', [CodesController::class, 'index_instructor']);
            Route::post('/instructor_store', [CodesController::class, 'store_instructor']);
        });

        // Certificates
        Route::prefix('certificates')->group(function () {
            Route::get('/', [CertificatesController::class, 'index']);
            Route::get('/excel', [CertificatesController::class, 'exportExcel']);
            Route::get('/course-competition', [WebinarCertificateController::class, 'index']);
            Route::prefix('templates')->group(function () {
                Route::get('/', [CertificatesController::class, 'CertificatesTemplatesList']);
                Route::post('/', [CertificatesController::class, 'CertificatesTemplateStore']);
                Route::put('/{template_id}', [CertificatesController::class, 'CertificatesTemplateStore']);
                Route::delete('/{template_id}', [CertificatesController::class, 'CertificatesTemplatesDelete']);
            });
        });

        // Registrations (enrollments)
        Route::prefix('enrollments')->group(function () {
            Route::get('/history', [EnrollmentsController::class, 'history']);
            Route::get('/{sale_id}/block-access', [EnrollmentsController::class, 'blockAccess']);
            Route::get('/{sale_id}/enable-access', [EnrollmentsController::class, 'enableAccess']);
            Route::get('/export', [EnrollmentsController::class, 'exportExcel']);
            Route::post('/store', [EnrollmentsController::class, 'store']);
        });

        // Categories
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{id}/update', [CategoryController::class, 'update']);
            Route::delete('/{id}/delete', [CategoryController::class, 'destroy']);
        });
    });
});
