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
        Route::post('/join', ['uses' => 'Student\UserController@student_join']);
        Route::post('/login', ['uses' => 'Student\UserController@student_login']);
        //课程模块
        Route::post('/join_course', ['uses' => 'Student\CourseController@join_course']);
        Route::post('/get_joined_course_list', ['uses' => 'Student\CourseController@get_joined_course_list']);
        Route::post('/get_course_info_by_id', ['uses' => 'Student\CourseController@get_course_info_by_id']);
        Route::post('/get_class_by_course', ['uses' => 'Student\CourseController@get_class_by_course']);
        Route::post('/get_all_joinable_course', ['uses' => 'Student\CourseController@get_all_joinable_course']);
        //作业模块
        Route::post('/submit_homework', ['uses' => 'Student\HomeworkController@submit_homework']);
        Route::post('/get_four_homework', ['uses' => 'Student\HomeworkController@get_four_homework']);
        Route::post('/get_homework_standard', ['uses' => 'Student\HomeworkController@get_homework_standard']);
        Route::post('/assess_other', ['uses' => 'Student\HomeworkController@assess_other']);
        Route::post('/assess_myself', ['uses' => 'Student\HomeworkController@assess_myself']);
        Route::post('/modify_homework', ['uses' => 'Student\HomeworkController@modify_homework']);
        Route::post('/get_homework_list_by_time', ['uses' => 'Student\HomeworkController@get_homework_list_by_time']);
        Route::post('/get_homework_info_by_id', ['uses' => 'Student\HomeworkController@get_homework_info_by_id']);
        Route::post('/get_assessment', ['uses' => 'Student\HomeworkController@get_assessment']);
        Route::post('/get_modify', ['uses' => 'Student\HomeworkController@get_modify']);
        Route::post('/get_homework_class_result', ['uses' => 'Student\HomeworkController@get_homework_class_result']);
        Route::post('/get_homework_personal_result', ['uses' => 'Student\HomeworkController@get_homework_personal_result']);
        //定时器
        Route::any('/crontab_change_homework_state', ['uses' => 'Student\HomeworkController@crontab_change_homework_state']);
    });
    //teacher接口
    Route::group(['prefix' => 'teacher'], function () {
        //用户模块
        Route::post('/join', ['uses' => 'Teacher\UserController@teacher_join']);
        Route::post('/login', ['uses' => 'Teacher\UserController@teacher_login']);
        //课程模块
        Route::post('/add_course', ['uses' => 'Teacher\CourseController@add_course']);
        Route::post('/get_my_course', ['uses' => 'Teacher\CourseController@get_my_course']);

        //作业模块
        Route::post('/add_homework', ['uses' => 'Teacher\HomeworkController@add_homework']);
        Route::post('/add_new_round_homework', ['uses' => 'Teacher\HomeworkController@add_new_round_homework']);
        Route::post('/get_homework_of_course', ['uses' => 'Teacher\HomeworkController@get_homework_of_course']);
        Route::post('/get_homework_score_percent', ['uses' => 'Teacher\HomeworkController@get_homework_score_percent']);
    });
    //admin接口
    Route::group(['prefix' => 'admin'], function () {
        //用户模块
        Route::post('/add_admin', ['uses' => 'Admin\UserController@add_admin']);
        Route::post('/login', ['uses' => 'Admin\UserController@login']);
        Route::post('/get_student_list', ['uses' => 'Admin\UserController@get_student_list']);
        Route::post('/get_college_school', ['uses' => 'Admin\UserController@get_college_school']);
        Route::post('/get_teacher_list', ['uses' => 'Admin\UserController@get_teacher_list']);
        Route::post('/get_admin_list', ['uses' => 'Admin\UserController@get_admin_list']);
        //课程模块
        Route::post('/get_course_list', ['uses' => 'Admin\CourseController@get_course_list']);
    });
});

