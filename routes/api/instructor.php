<?php

use App\Http\Controllers\Api\Panel\NotificationsController;
use App\Http\Controllers\Api\Instructor\EmployeeProgressController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix"=>'/panel'], function () {


    /***** bundles *****/
    Route::get('bundles/{bundle}/export', ['uses' => 'BundleController@export'])->middleware('api.level-access:teacher');
    Route::apiResource('bundles', BundleController::class)->middleware('api.level-access:teacher');
    Route::apiResource('bundles.webinars', BundleWebinarController::class)->middleware('api.level-access:teacher')->only(['index']);

    Route::get('employee_progress',[EmployeeProgressController::class,'index']);
    
    Route::delete('/{bundle_id}/{student_id}/remove',[EmployeeProgressController::class,'destroy']);
    Route::post('/add_employee',[EmployeeProgressController::class,'store']);

    Route::group(['prefix'=>'notifications'],function(){
        Route::get('/',[NotificationsController::class,'list']);
        Route::post('/{id}/seen', [NotificationsController::class, 'seen']);
    });

    Route::group(['prefix' => 'webinar'], function () {
        Route::post('/', ['uses' => 'WebinarsController@storeAll']);

    });

    Route::group(['prefix' => 'quizzes'], function () {
        Route::get('/list', ['uses' => 'QuizzesController@results']);
        Route::post('/', ['uses' => 'QuizzesController@store']);
        Route::put('/{id}', ['uses' => 'QuizzesController@update']);
        Route::delete('/{id}', ['uses' => 'QuizzesController@destroy']);

    });
//  Route::get('sales', ['uses' => 'SalesController@list']);
    Route::group(['prefix' => 'meetings'], function () {
        Route::get('/', function () {
            dd('ff');
        });

        Route::get('/requests', ['uses' => 'ReserveMeetingController@requests']);
        Route::post('/create-link', ['uses' => 'ReserveMeetingController@createLink']);
        Route::post('/{id}/finish', ['uses' => 'ReserveMeetingController@finish']);

    });
    Route::group(['prefix' => 'comments'], function () {
        Route::get('/', ['uses' => 'CommentsController@myClassComments']);
        Route::post('/{id}/reply', ['uses' => 'CommentsController@reply']);
    });
    Route::group(['prefix' => 'assignments'], function () {
        Route::get('/{assignment}/students', ['uses' => 'AssignmentController@submmision']);
        Route::get('/students', ['uses' => 'AssignmentController@students']);
        Route::get('/', ['uses' => 'AssignmentController@index']);
        Route::post('/histories/{assignment_history}/rate', ['uses' => 'AssignmentController@setGrade']);
    });


});





