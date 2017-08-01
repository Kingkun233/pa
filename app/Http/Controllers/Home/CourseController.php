<?php

namespace App\Http\Controllers\Home;

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
        login_pretreat($type, $post, 2);
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

}
