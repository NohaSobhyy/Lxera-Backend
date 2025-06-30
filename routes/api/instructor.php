<?php


use App\Http\Controllers\Api\Instructor\AssignmentController;
use App\Http\Controllers\Api\Instructor\DashboardController;
use App\Http\Controllers\Api\Instructor\LearningPageController;
use App\Http\Controllers\Api\Instructor\QuizzesController;
use App\Http\Controllers\Api\Instructor\WebinarsController;
use App\Http\Controllers\Api\Panel\NotificationsController;
use App\Http\Controllers\Api\Instructor\EmployeeProgressController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('{url_name}')->group(function () {
        
        // Dashboard
        Route::get('/', [DashboardController::class, 'dashboard'])->middleware('can:show_panel');

        // Webinars
        Route::group(['prefix' => 'webinars'], function () {
            Route::group(['prefix' => 'assignment'], function () {
                Route::get('/', [AssignmentController::class, 'index']);
                Route::post('/', [AssignmentController::class, 'store']);
                Route::put('/{id}', [AssignmentController::class, 'update']);
                Route::delete('/{id}', [AssignmentController::class, 'destroy']);
                Route::get('/{id}/students', [AssignmentController::class, 'submmision']);
                Route::post('/histories/{assignment_history}/rate', [AssignmentController::class, 'setGrade']);
                Route::post('/{assignmentId}/history/{historyId}/message', [AssignmentController::class, 'storeMessage']);
            });
            Route::get('/', [WebinarsController::class, 'index']);
            Route::get('/{id}', [WebinarsController::class, 'showSections']);
            Route::post('/addSection/{webinarId}', [WebinarsController::class, 'addNewSection']);
            Route::group(['prefix' => 'quiz'], function () {
                Route::get('/', [QuizzesController::class, 'index']);
                Route::post('/', [QuizzesController::class, 'store']);
                Route::put('/{id}', [QuizzesController::class, 'update']);
                Route::delete('/{id}', [QuizzesController::class, 'destroy']);
            });
        });

        Route::group(["prefix" => '/panel'], function () {


           
            /***** bundles *****/
            Route::get('bundles/{bundle}/export', ['uses' => 'BundleController@export'])->middleware('api.level-access:teacher');
            Route::apiResource('bundles', BundleController::class)->middleware('api.level-access:teacher');
            Route::apiResource('bundles.webinars', BundleWebinarController::class)->middleware('api.level-access:teacher')->only(['index']);

  
            Route::group(['prefix' => 'notifications'], function () {
                Route::get('/', [NotificationsController::class, 'list']);
                Route::post('/{id}/seen', [NotificationsController::class, 'seen']);
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
            Route::group(['prefix' => 'course'], function () {
                Route::get('/learning/{id}/{bundle?}', [LearningPageController::class, 'index']);
            });
        });
    });
});
