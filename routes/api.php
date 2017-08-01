<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//api中间件，有session
Route::group(['middleware' => ['api']], function () {
    //工具路由
    Route::get('/return', ['uses' => 'Home\ReturnController@ReturnStandard'])->name('return');
    //student接口
    Route::group(['prefix' => 'student'], function () {
        //用户模块
        Route::post('/join', ['uses' => 'Home\UserController@student_join']);
        Route::post('/login', ['uses' => 'Home\UserController@student_login']);
        //课程模块
        Route::post('/join_course', ['uses' => 'Home\CourseController@join_course']);
        //作业模块
        Route::post('/submit_homework', ['uses' => 'Home\HomeworkController@submit_homework']);
        Route::post('/get_four_homework', ['uses' => 'Home\HomeworkController@get_four_homework']);
        Route::post('/get_homework_standard', ['uses' => 'Home\HomeworkController@get_homework_standard']);
        Route::post('/assess_other', ['uses' => 'Home\HomeworkController@assess_other']);
        Route::post('/assess_myself', ['uses' => 'Home\HomeworkController@assess_myself']);
    });
    //teacher接口
    Route::group(['prefix' => 'teacher'], function () {
        //用户模块
        Route::post('/join', ['uses' => 'Home\UserController@teacher_join']);
        Route::post('/login', ['uses' => 'Home\UserController@teacher_login']);
        //课程模块
        Route::post('/add_course', ['uses' => 'Home\CourseController@add_course']);
        //作业模块
        Route::post('/add_homework', ['uses' => 'Home\HomeworkController@add_homework']);
    });
});

