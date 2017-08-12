<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    /**
     * 添加普通管理员
     */
    public function add_admin(Request $request)
    {
        $type = 'A1001';
        $post = $request->all();
        login_pretreat($type, $post);
        $is_super = $this->is_superadmin(session('id'));
        if (!$is_super) {
            return response_treatment(6, $type);
        }
        $Admin = DB::table('admins');
        $add_admin['name'] = $post['name'];
        $add_admin['email'] = $post['email'];
        $add_admin['password'] = $post['password'];
        $add_admin['create_time'] = date('Y-m-d H:i:s');
        $add_admin['type'] = 0;
        //用户是否存在
        $flag_exist = $Admin->where('name', $post['name'])->orWhere('email', $post['email'])->first();
        if (!$flag_exist) {
            //插入
            $flag = $Admin->insert($add_admin);
            if ($flag) {
                return response_treatment(0, $type);
            } else {
                return response_treatment(1, $type);
            }
        } else {
            return response_treatment(3, $type);
        }
    }

    /**
     * 管理员登录
     */
    public function login(Request $request)
    {
        $type = 'A1002';
        $post = $request->all();
        tourist_pretreat($type, $post);
        //查看有没有该用户
        //查看密码对不对
        $email = $post['email'];
        $password = $post['password'];
        $Admin = DB::table('admins');
        $row = $Admin->where('email', $email)->first();
        //用户是否存在
        if ($row) {
            //密码是否正确
            if ($row->password == $password) {
                //生成token，存入数据库并且反回
                $token = $this->getToken($email, $password);
                $update['token'] = $token;
                $Admin->where('email', $email)->update($update);
                //个人id存进session
                Session::flush();
                $request->session()->put('id', $row->id);
                $msg['name'] = $row->name;
                $msg['email'] = $row->email;
                $res = response_treatment(0, $type, $msg);
                $res['token'] = $token;
                return $res;
            } else {
                return response_treatment(1, $type);
            }
        } else {
            return response_treatment(5, $type);
        }
    }

    /**生成token
     * @param $email
     * @param $password
     * @return string
     */
    private function getToken($email, $password)
    {
        $time = time();
        return md5($email . $password . $time);
    }

    /**是否超级管理员
     * @param $admin_id
     * @return mixed
     */
    private function is_superadmin($admin_id)
    {
        $is_superadmin = DB::table('admins')->where('id', $admin_id)->value('type');
        return $is_superadmin;
    }

    /**获取学生列表
     * @param Request $request
     */
    public function get_student_list(Request $request)
    {
        $type = 'A1003';
        $post = $request->all();
        login_pretreat($type, $post);
        //搜索判别
        $where = [];
        if ($post['college_id']) {
            $where[] = ['college_id', '=', $post['college_id']];
            if ($post['school_id']) {
                $where[] = ['school_id', '=', $post['school_id']];
            }
            if ($post['key']) {
                $where[] = ['name', 'like', '%' . $post['key'] . '%'];
            }
        } else {
            if ($post['key']) {
                $where[] = ['name', 'like', '%' . $post['key'] . '%'];
            }
        }
        $students = DB::table('students')->where($where)->get()->toArray();
        foreach ($students as $k => $student) {
            //整合学院名字和学校名字
            $students[$k]->school_name = DB::table('schools')->where('id', $student->school_id)->value('name');
            $students[$k]->college_name = DB::table('colleges')->where('id', $student->college_id)->value('name');
            unset($students[$k]->password);
            unset($students[$k]->token);
        }
        return response_treatment(0, $type, $students);
    }

    /**
     * 获取学校
     */
    public function get_college_school(Request $request)
    {
        $type = 'A1004';
        $post = $request->all();
        login_pretreat($type, $post);
        $School = DB::table('schools');
        $colleges = DB::table('colleges')->get()->toArray();
        foreach ($colleges as $college) {
            $schools = $School->where('college_id', $college->id)->get()->toArray();
            $college->schools = $schools;
        }
        return response_treatment(0, $type, $colleges);
    }

    /**获取教师列表
     * @param Request $request
     * @return mixed
     */
    public function get_teacher_list(Request $request)
    {
        $type = 'A1005';
        $post = $request->all();
        login_pretreat($type, $post);
        //搜索判别
        $where = [];
        if ($post['college_id']) {
            $where[] = ['college_id', '=', $post['college_id']];
            if ($post['school_id']) {
                $where[] = ['school_id', '=', $post['school_id']];
            }
            if ($post['key']) {
                $where[] = ['name', 'like', '%' . $post['key'] . '%'];
            }
        } else {
            if ($post['key']) {
                $where[] = ['name', 'like', '%' . $post['key'] . '%'];
            }
        }
        $teachers = DB::table('teachers')->where($where)->get()->toArray();
        foreach ($teachers as $teacher) {
            //整合学院名字和学校名字
            $teacher->school_name = DB::table('schools')->where('id', $teacher->school_id)->value('name');
            $teacher->college_name = DB::table('colleges')->where('id', $teacher->college_id)->value('name');
            unset($teacher->password);
            unset($teacher->token);
        }
        return response_treatment(0, $type, $teachers);
    }
}
