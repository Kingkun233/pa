<?php

use \Illuminate\Support\Facades\URL;
use \Illuminate\Support\Facades\DB;

/**数据整合返回
 * @param int $re
 * @param string $type
 * @param array $msg
 * @return mixed
 */
function response_treatment($re = 0, $type = '', $msg = null)
{
    if ($msg === null) {
        $msg = new stdClass;
    }
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
 * @param int $role 0是学生,1是老师,2是管理员
 */
function login_pretreat($type, $post)
{
    if ($type != $post['type']) {
        my_redirect('return', 2, $type);
    }
    //判断token是否存在
    $token = $post['token'];
    $user_info = DB::table('students')->where('token', $token)->select(['id', 'token'])->first();
    if (!$user_info) {
        $user_info = DB::table('teachers')->where('token', $token)->select(['id', 'token'])->first();
        if (!$user_info) {
            $user_info = DB::table('admins')->where('token', $token)->select(['id', 'token'])->first();
        }
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

/**以数组形式返回对象数组的某个值
 * @param $objects
 * @param $value_name
 * @return array
 */
function get_object_value_as_array($objects, $value_name)
{
    $value_list = [];
    foreach ($objects as $object) {
        $value_list[] = $object->$value_name;
    }
    return $value_list;
}
