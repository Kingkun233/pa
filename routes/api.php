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
        Route::post('/get_joined_course_list', ['uses' => 'Home\CourseController@get_joined_course_list']);
        Route::post('/get_course_info_by_id', ['uses' => 'Home\CourseController@get_course_info_by_id']);
        Route::post('/get_class_by_course', ['uses' => 'Home\CourseController@get_class_by_course']);
        Route::post('/get_all_joinable_course', ['uses' => 'Home\CourseController@get_all_joinable_course']);
        //作业模块
        Route::post('/submit_homework', ['uses' => 'Home\HomeworkController@submit_homework']);
        Route::post('/get_four_homework', ['uses' => 'Home\HomeworkController@get_four_homework']);
        Route::post('/get_homework_standard', ['uses' => 'Home\HomeworkController@get_homework_standard']);
        Route::post('/assess_other', ['uses' => 'Home\HomeworkController@assess_other']);
        Route::post('/assess_myself', ['uses' => 'Home\HomeworkController@assess_myself']);
        Route::post('/modify_homework', ['uses' => 'Home\HomeworkController@modify_homework']);
        Route::post('/get_homework_list_by_time', ['uses' => 'Home\HomeworkController@get_homework_list_by_time']);
        Route::post('/get_homework_info_by_id', ['uses' => 'Home\HomeworkController@get_homework_info_by_id']);
        Route::post('/get_assessment', ['uses' => 'Home\HomeworkController@get_assessment']);
        Route::post('/get_modify', ['uses' => 'Home\HomeworkController@get_modify']);

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

