<?php

use App\Http\Controllers\Api\Instructor\DashboardController;
use App\Http\Controllers\Api\Instructor\WebinarsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('{url_name}')->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'dashboard'])->middleware('can:show_panel');

        // Webinars
        Route::group(['prefix' => 'webinars', 'middleware' => 'can:student_showClasses'], function () {
            Route::get('/', [WebinarsController::class, 'index']);
        });


        /***** bundles *****/
        Route::get('bundles/{bundle}/export', ['uses' => 'BundleController@export'])->middleware('api.level-access:teacher');
        Route::apiResource('bundles', BundleController::class)->middleware('api.level-access:teacher');
        Route::apiResource('bundles.webinars', BundleWebinarController::class)->middleware('api.level-access:teacher')->only(['index']);




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
});
