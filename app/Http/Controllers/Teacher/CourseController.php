<?php

namespace App\Http\Controllers\Teacher;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**添加课程
     * @param Request $request
     * @return mixed
     */
    public function add_course(Request $request)
    {
        $type = 'T2001';
        $post = $request->all();
        login_pretreat($type, $post);
        $add_course['name'] = $post['name'];
        $add_course['grade'] = $post['grade'];
        $add_course['description'] = $post['description'];
        $add_course['start_day'] = $post['start_day'];
        $add_course['end_day'] = $post['end_day'];
        $add_course['total_hours'] = $post['total_hours'];
        $add_course['pre_course_name'] = $post['pre_course_name'];
        $add_course['aim'] = $post['aim'];
        $add_course['progress'] = $post['progress'];
        $add_course['teacher_id'] = session('id');
        $add_course['school_id'] = DB::table('teachers')->where('id', $add_course['teacher_id'])->first()->school_id;
        DB::beginTransaction();
        $flag = DB::table('courses')->insertGetId($add_course);
        if ($flag) {
            //插入班级表
            foreach ($post['classes'] as $class_name) {
                DB::table('classes')->insert(['class_name' => $class_name, 'course_id' => $flag]);
            }
            DB::commit();
            return response_treatment(0, $type);
        } else {
            DB::rollback();
            return response_treatment(1, $type);
        }
    }

    /**
     * 老师：获取我开设的所有课程
     */
    public function get_my_course(Request $request)
    {
        $type = 'T2002';
        $post = $request->all();
        login_pretreat($type, $post);
        $teacher_id = session('id');
        //course
        $courses = DB::table('courses')->where('teacher_id', $teacher_id)->get()->toArray();
        if ($courses) {
            return response_treatment(0, $type, $courses);
        } else {
            return response_treatment(1, $type);
        }
    }

}
