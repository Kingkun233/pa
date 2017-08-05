<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{

    /**加入课程
     * @param Request $request
     * @return mixed
     */
    public function join_course(Request $request)
    {
        $type = 'S2001';
        $post = $request->all();
        login_pretreat($type, $post);
        $add_take['course_id'] = $post['course_id'];
        $add_take['class_id'] = $post['class_id'];
        $add_take['pre_course_score'] = $post['pre_course_score'];
        $add_take['aim_score'] = $post['aim_score'];
        $add_take['aim_text'] = $post['aim_text'];
        $add_take['student_id'] = session('id');
        $flag = DB::table('student_course')->insert($add_take);
        if ($flag) {
            return response_treatment(0, $type);
        } else {
            return response_treatment(1, $type);
        }
    }

    /**获取该学生参加了的课程
     * @param Request $request
     */
    public function get_joined_course_list(Request $request)
    {
        $type = 'S2002';
        $post = $request->all();
        login_pretreat($type, $post);
        $student_id = session('id');
        //student_course表中找当前时间
        $courses = DB::table('student_course')->where('student_id', $student_id)->get()->toArray();
        return response_treatment(0, $type, $courses);
    }

    /**学生：根据课程id获取课程信息
     * @param Request $request
     */
    public function get_course_info_by_id(Request $request)
    {
        $type = 'S2003';
        $post = $request->all();
        tourist_pretreat($type, $post);
        //course表中找
        $course_info = DB::table('courses')->where('id', $post['course_id'])->first();
        //整合老师姓名
        $course_info->teacher_name = DB::table('teachers')->where('id', $course_info->teacher_id)->value('name');
        //整合上课班级
        $course_info->classes = DB::table('classes')->where('course_id', $post['course_id'])->get()->toArray();
        if ($course_info) {
            return response_treatment(0, $type, $course_info);
        } else {
            return response_treatment(1, $type);
        }
    }

    /**根据课程获取班别
     * @param Request $request
     */
    public function get_class_by_course(Request $request)
    {
        $type = 'S2004';
        $post = $request->all();
        login_pretreat($type, $post);
        //classes表
        $classes = DB::table('classes')->where('course_id', $post['course_id'])->get()->toArray();
        if ($classes) {
            return response_treatment(0, $type, $classes);
        } else {
            return response_treatment(1, $type);
        }
    }

    /**
     * 获取所有可添加课程
     */
    public function get_all_joinable_course(Request $request)
    {
        $type = 'S2005';
        $post = $request->all();
        login_pretreat($type, $post);
        //courses
        $courses = DB::table('courses')->where('state', '<>', 2)->get()->toArray();
        //整合教师姓名
        foreach ($courses as $course) {
            $course->teacher_name = DB::table('teachers')->where('id', $course->teacher_id)->value('name');
        }
        return response_treatment(0, $type, $courses);
    }

}
