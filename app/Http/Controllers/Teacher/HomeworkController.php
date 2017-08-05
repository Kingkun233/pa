<?php

namespace App\Http\Controllers\Teacher;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HomeworkController extends Controller
{
    /**创建作业
     * @param Request $request
     * @return mixed
     */
    public function add_homework(Request $request)
    {
        $type = 'T3001';
        $post = $request->all();
        login_pretreat($type, $post, 2);
        $add_homework["course_id"] = $post["course_id"];
        $add_homework["class_id"] = $post["class_id"];
        $add_homework["requirement"] = $post["requirement"];
        $add_homework["name"] = $post["name"];
        $add_homework["extend_from"] = 0;
        $add_homework["submit_ddl"] = $post["submit_ddl"];
        $add_homework["assessment_ddl"] = $post["assessment_ddl"];
        $add_homework["modify_ddl"] = $post["modify_ddl"];
        $add_homework["round"] = 1;
        //1:提交阶段；2：互评阶段；3：修改阶段；4：已结束
        $add_homework["state"] = 1;
        DB::beginTransaction();
        $flag = DB::table('homeworks')->insertGetId($add_homework);
        if ($flag) {
            //插入作业标准表
            foreach ($post['standard'] as $standard) {
                DB::table('homework_standard')->insert(['homework_id' => $flag, 'standard' => $standard]);
            }
            //更新作业源为它自己
            $update_homework["extend_from"] = $flag;
            DB::table('homeworks')->where('id', $flag)->update($update_homework);
            DB::commit();
            return response_treatment(0, $type);
        } else {
            DB::rollback();
            return response_treatment(1, $type);
        }
    }


    /**发布新一轮作业
     * @param Request $request
     */
    public function add_new_round_homework(Request $request)
    {
        $type = 'T3002';
        $post = $request->all();
        login_pretreat($type, $post, 2);
        $add_homework["course_id"] = $post["course_id"];
        $add_homework["class_id"] = $post["class_id"];
        $add_homework["requirement"] = $post["requirement"];
        $add_homework["name"] = $post["name"];
        $add_homework["extend_from"] = $post["extend_from"];
        $add_homework["submit_ddl"] = $post["submit_ddl"];
        $add_homework["assessment_ddl"] = $post["assessment_ddl"];
        $add_homework["modify_ddl"] = $post["modify_ddl"];
        $add_homework["round"] = 2;
        //1:提交阶段；2：互评阶段；3：修改阶段；4：已结束
        $add_homework["state"] = 1;
        DB::beginTransaction();
        $flag = DB::table('homeworks')->insertGetId($add_homework);
        if ($flag) {
            DB::commit();
            return response_treatment(0, $type);
        } else {
            DB::rollback();
            return response_treatment(1, $type);
        }
    }
}
