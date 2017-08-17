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


    /**获取该课程的学生
     * @param Request $request
     */
    public function get_student_by_course(Request $request)
    {
        $type = 'T2003';
        $post = $request->all();
        login_pretreat($type, $post);
        $course_id = $post['course_id'];
        $students = DB::table('student_course')->where('course_id', $course_id)->get()->toArray();
        foreach ($students as $student) {
            //整合学生信息
            $student_info = DB::table('students')->where('id', $student->student_id)->first();
            $student->name = $student_info->name;
            $student->school_num = $student_info->school_num;
        }
        $student_group_by_class = [];
        $class_ids = array_unique(get_object_value_as_array($students, 'class_id'));
        foreach ($class_ids as $class_id) {
            $student_group_by_class[] = ['class_id' => $class_id];
        }
        foreach ($students as $student) {
            $key=0;
            foreach ($student_group_by_class as $k=>$v){
                if($v['class_id']==$student->class_id){
                    $key=$k;
                }
            }
            $student_group_by_class[$key]['class_name'] = DB::table('classes')->where('id', $student->class_id)->value('class_name');
            $student_group_by_class[$key]['students'][] = $student;
        }
        return response_treatment(0, $type, $student_group_by_class);
    }

    /**将对象数组按照每个属性分类返回二维对象数组
     * @param $array_one_degree
     * @param $key
     */
    private function group($objs, $attr)
    {
        $return_array = [];
        foreach ($objs as $obj) {
            $return_array[$obj->$attr][] = $obj;
        }
    }
}
