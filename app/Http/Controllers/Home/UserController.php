<?php

namespace App\Http\Controllers\Home;

use App\Model\Student;
use App\Model\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

//use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**注册
     * @param Request $request
     * @return mixed
     */
    public function student_join(Request $request)
    {
        $type = 'S1001';
        $post = $request->all();
        tourist_pretreat($type, $post);
        $add_user['name'] = $post['name'];
        $add_user['password'] = $post['password'];
        $add_user['sex'] = $post['sex'];
        $add_user['email'] = $post['email'];
        $add_user['school_num'] = $post['school_num'];
        $add_user['grade'] = $post['school_num'];
        $add_user['class_name'] = $post['class_name'];
        //检查数据库中是否存在该用户名,email,学号
        $users = DB::table('students')
            ->where('name', $post['name'])
            ->orWhere('email', $post['name'])
            ->orWhere('school_num', $post['school_num'])
            ->get()->toArray();
//        var_dump($users);die;
        if ($users) {
            return response_treatment(3, $type);
        }
        if (Student::create($add_user)) {
            return response_treatment(0, $type);
        } else {
            return response_treatment(1, $type);
        }


    }

    /**登录
     * @param Request $request
     * @return mixed
     */
    public function student_login(Request $request)
    {
        $type = 'S1002';
        $post = $request->all();
        tourist_pretreat($type, $post);
        $email = $post['email'];
        $password = $post['password'];
        $row =  Student::where('email', $email)->first();
        //用户是否存在
        if ($row) {
            //密码是否正确
            if ($row->password == $password) {
                //生成token，存入数据库并且反回
                $token = $this->getToken($email, $password);
                $update['token'] = $token;
                Student::where('email', $email)->update($update);
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

    /**注册
     * @param Request $request
     * @return mixed
     */
    public function teacher_join(Request $request)
    {
        $type = 'T1001';
        $post = $request->all();
        tourist_pretreat($type, $post);
        $add_user['name'] = $post['name'];
        $add_user['password'] = $post['password'];
        $add_user['email'] = $post['email'];
        $add_user['school_id'] = $post['school_id'];
        //检查数据库中是否已经存在该用户名,email
        $users = DB::table('teachers')
            ->where('email', $post['email'])
            ->first();
        if ($users) {
            return response_treatment(3, $type);
        }
        //插入数据库
        $flag = DB::table('teachers')->insert(
            $add_user
        );
        if ($flag) {
            return response_treatment(0, $type);
        } else {
            return response_treatment(1, $type);
        }
    }

    /**登录
     * @param Request $request
     * @return mixed
     */
    public function teacher_login(Request $request)
    {
        $type = 'T1002';
        $post = $request->all();
        tourist_pretreat($type, $post);
        $email = $post['email'];
        $password = $post['password'];
        $row  = DB::table('teachers')->where('email', $email)->first();
        //用户是否存在
        if ($row) {
            //密码是否正确
            if ($row->password == $password) {
                //生成token，存入数据库并且反回
                $token = $this->getToken($email, $password);
                $update['token'] = $token;
                DB::table('teachers')->where('email', $email)->update($update);
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
}
