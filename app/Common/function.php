<?php

use \Illuminate\Support\Facades\URL;
use \Illuminate\Support\Facades\DB;

/**数据整合返回
 * @param int $re
 * @param string $type
 * @param array $msg
 * @return mixed
 */
function response_treatment($re = 0, $type = '', $msg = [])
{
    $res['re'] = $re;
    $res['type'] = $type;
    $res['msg'] = $msg;
    return $res;
}

/**游客数据预处理
 * @param $type
 * @param Request $request
 * @return mixed
 */
function tourist_pretreat($type, $post)
{
    if ($type != $post['type']) {
        my_redirect('return', 2, $type);
    }
}

/**登录接口预处理
 * @param $type
 * @param $post
 * @param int $role 1是学生，2是老师
 */
function login_pretreat($type, $post, $role = 1)
{
    if ($type != $post['type']) {
        my_redirect('return', 2, $type);
    }
    //判断token是否存在
    $token = $post['token'];
    if ($role == 1) {
        $user_info = DB::table('students')->where('token', $token)->select(['id', 'token'])->first();
    } else if ($role == 2) {
        $user_info = DB::table('teachers')->where('token', $token)->select(['id', 'token'])->first();
    }
    if ($user_info) {
        //如果存在，获取用户id并存进session
        \Illuminate\Support\Facades\Session::put('id', $user_info->id);
    } else {
        //如果不存在，session置空，token就不用管了，因为每次登录都回自动跟新token
        \Illuminate\Support\Facades\Session::flush();
        //并且返回re=4
        my_redirect('return', 4, $type);
    }
}

/**自己的重定向
 * @param $route_name
 * @param $re
 * @param $type
 */
function my_redirect($route_name, $re, $type)
{
    header("Location:" . URL::route($route_name) . "?re=" . $re . "&type=" . $type);
    exit();
}
