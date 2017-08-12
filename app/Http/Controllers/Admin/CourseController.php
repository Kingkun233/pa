<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**获取课程列表
     * @param Request $request
     */
    public function get_course_list(Request $request)
    {
        $type = 'A2001';
        $post = $request->all();
        login_pretreat($type, $post);
        //搜索判别
        $courses = DB::table('courses')->get()->toArray();
        foreach ($courses as $course) {
            //整合老师名字和email
            $teacher = DB::table('teachers')->where('id', $course->teacher_id)->first();
            $course->teacher_name = $teacher->name;
            $course->teacher_email = $teacher->email;
            //整合学生人数
            $course->student_num = DB::table('student_course')->where('course_id', $course->id)->count();
            unset($teacher->password);
            unset($teacher->token);
        }
        return response_treatment(0, $type, $courses);
    }
}
