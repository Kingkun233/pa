<?php

namespace App\Http\Controllers\Home;

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

    /**提交作业
     * @param Request $request
     */
    public function submit_homework(Request $request)
    {
        $type = 'S3001';
        $post = $request->all();
        login_pretreat($type, $post);
        $add_homework["student_id"] = session('id');
        $add_homework["homework_id"] = $post["homework_id"];
        $add_homework["content"] = $post["content"];
        $add_homework["modify"] = '';
        $add_homework["creat_time"] = date('Y-m-d H:i:s');
        $add_homework["update_time"] = date('Y-m-d H:i:s');
        $flag = DB::table('student_homework')->insert($add_homework);
        if ($flag) {
            return response_treatment(0, $type);
        } else {
            return response_treatment(1, $type);
        }
    }

    /**获取四份作业
     * @param Request $request
     */
    public function get_four_homework(Request $request)
    {
        $type = 'S3002';
        $post = $request->all();
        login_pretreat($type, $post);
        $homework_id = $post['homework_id'];
        $student_id = session('id');
//        $num = DB::table('student_homework')->where('homework_id', $homework_id)->count();
        //组内两篇
        //组外两篇
        //现在先获取除了自己的四份作业，等分组模块出来后再改

        //获取所有批改数小于4的student_homework_id和内容
        $homeworks = DB::table('student_homework')->where('homework_id', $homework_id)->where('assess_num', '<=', '4')->get();
        $n = 0;
        $array_homeworks = [];
        foreach ($homeworks as $homework) {
            $array_homeworks[$n]['student_homework_id'] = $homework->id;
            $array_homeworks[$n]['content'] = $homework->content;
            $n += 1;
        }
        //随机挑选四个下标
        $random_array = $this->getRandomNums(4, 0, $n - 1);

        //遍历这些下标然后对应的student_homework的assess_num++
//        var_dump($random_array);die;
        $msg = [];
        DB::beginTransaction();
        foreach ($random_array as $random_num) {
            $random_id = $array_homeworks[$random_num]['student_homework_id'];
            $flag = DB::table('student_homework')->where('id', $random_id)->increment('assess_num');
            if (!$flag) {
                DB::rollback();
                return response_treatment(1, $type);
            }
            $flag = 0;
            $msg[] = $array_homeworks[$random_num];
        }
        DB::commit();
        return response_treatment(0, $type, $msg);
    }

    /**返回随机数组
     * @param $long 随机数组长度
     * @param $low 最小值
     * @param $high 最大值
     * @return array
     */
    private function getRandomNums($long, $low, $high)
    {
        if ($long > ($high - $low + 1)) {
            $long = $high - $low + 1;
        }
        $numbers = range($low, $high);
        //shuffle 将数组顺序随即打乱
        shuffle($numbers);
        //array_slice 取该数组中的某一段
        return array_slice($numbers, 0, $long);
    }

    /**获取作业标准
     * @param Request $request
     */
    public function get_homework_standard(Request $request)
    {
        $type = 'S3004';
        $post = $request->all();
        login_pretreat($type, $post);
        $standards = DB::table('homework_standard')->where('homework_id', $post['homework_id'])->get()->toArray();
        if ($standards) {
            return response_treatment(0, $type, $standards);
        } else {
            return response_treatment(1, $type);
        }

    }

    /**评价他人
     * @param Request $request
     */
    public function assess_other(Request $request)
    {
        $type = 'S3005';
        $post = $request->all();
        login_pretreat($type, $post);
        //数据存进assessment表
        $add_assess['student_id'] = session('id');
        $add_assess['student_homework_id'] = $post['student_homework_id'];
        $add_assess['time'] = date('Y-m-d H:i:s');
        $add_assess['total_score'] = $post['total_score'];
        DB::beginTransaction();
        foreach ($post['assessment'] as $assess) {
            $add_assess['content'] = $assess['content'];
            $add_assess['standard_id'] = $assess['standard_id'];
            $add_assess['stars'] = $assess['stars'];
            $flag = DB::table('assessment')->insert($add_assess);
            if (!$flag) {
                DB::rollback();
                return response_treatment(1, $type);
            }
        }
        DB::commit();
        return response_treatment(0, $type);
    }

    /**评价自己
     * @param Request $request
     */
    public function assess_myself(Request $request)
    {
        $type = 'S3003';
        $post = $request->all();
        login_pretreat($type, $post);
        //数据存进assessment表
        $add_assess['student_id'] = session('id');
        $add_assess['student_homework_id'] = $post['student_homework_id'];
        $add_assess['time'] = date('Y-m-d H:i:s');
        $add_assess['total_score'] = $post['total_score'];
        //他评为0，自评为1
        $add_assess['type'] = 1;
        DB::beginTransaction();
        foreach ($post['assessment'] as $assess) {
            $add_assess['content'] = $assess['content'];
            $add_assess['standard_id'] = $assess['standard_id'];
            $add_assess['stars'] = $assess['stars'];
            $flag = DB::table('assessment')->insert($add_assess);
            if (!$flag) {
                DB::rollback();
                return response_treatment(1, $type);
            }
        }
        DB::commit();
        return response_treatment(0, $type);
    }
}
