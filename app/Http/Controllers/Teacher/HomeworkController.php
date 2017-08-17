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
        login_pretreat($type, $post);
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
        login_pretreat($type, $post);
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

    /**获取该课程下的作业
     * @param Request $request
     */
    public function get_homework_of_course(Request $request)
    {
        $type = 'T3003';
        $post = $request->all();
        login_pretreat($type, $post);
        $course_id = $post['course_id'];
        $homeworks = DB::table('homeworks')->where('course_id', $course_id)->get()->toArray();
        foreach ($homeworks as $homework) {
            //整合作业平均分
            if (in_array($homework->state, [3, 4])) {
                $student_homeworks = DB::table('student_homework')->where('homework_id', $homework->id)->get()->toArray();
                $student_homework_ids = $this->get_object_value_as_array($student_homeworks, 'id');
                $assessmens = DB::table('assessment')->where('student_homework_id', $student_homework_ids)->get()->toArray();
                $scores = $this->get_object_value_as_array($assessmens, 'total_score');
                $avg_score = round(((float)array_sum($scores)) / (float)count($scores), 2);
                $homework->avg_score = $avg_score;
            } else {
                $homework->avg_score = 0;
            }
            //整合提交人数
            $submit_num = DB::table('student_homework')->where('homework_id', $homework->id)->count();
            $homework->submit_num = $submit_num;
        }
        //按班级整合
        $homework_group_by_class = [];
        $class_ids = array_unique(get_object_value_as_array($homeworks, 'class_id'));
        foreach ($class_ids as $class_id) {
            $homework_group_by_class[] = ['class_id' => $class_id];
        }

        foreach ($homeworks as $homework) {
            $key=0;
            foreach ($homework_group_by_class as $k=>$v){
                if($v['class_id']==$homework->class_id){
                    $key=$k;
                }
            }
            $homework_group_by_class[$key]['class_name'] = DB::table('classes')->where('id', $homework->class_id)->value('class_name');
            $homework_group_by_class[$key]['homeworks'][] = $homework;
        }
        return response_treatment(0, $type, $homework_group_by_class);
    }

    /**
     * 获得分数段百分比
     */
    public function get_homework_score_percent(Request $request)
    {
        $type = 'T3004';
        $post = $request->all();
        login_pretreat($type, $post);
        $homework_id = $post['homework_id'];
        //assessment
        $student_homeworks = $this->get_student_homework_by_homework($homework_id);
        $student_homework_ids = $this->get_object_value_as_array($student_homeworks, 'id');

        $scores2 = DB::table('assessment')
            ->select('student_homework_id', DB::raw('AVG(total_score) as total_score'))
            ->groupBy('student_homework_id')
            ->whereIn('student_homework_id', $student_homework_ids)
            ->get()->toArray();
//            ->limit(1)->groupBy('student_homework_id')->whereIn('student_homework_id', $student_homework_ids)->get();
//        var_dump($scores2);die;
        $scores = $this->get_object_value_as_array($scores2, 'total_score');
        $msg['_60'] = 0;
        $msg['_70'] = 0;
        $msg['_80'] = 0;
        $msg['_90'] = 0;
        $msg['_100'] = 0;
        foreach ($scores as $score) {
            if ($score < 60) {
                $msg['_60']++;
            } else if ($score < 70) {
                $msg['_70']++;
            } else if ($score < 80) {
                $msg['_80']++;
            } else if ($score < 90) {
                $msg['_90']++;
            } else if ($score < 100) {
                $msg['_100']++;
            }
        }
        return response_treatment(0, $type, $msg);
    }


    /**获取作业轮数
     * @param $homework_id
     * @return bool
     */
    private function get_homework_round($homework_id)
    {
        $round = DB::table('homeworks')->where('id', $homework_id)->value('round');
        return $round;
    }


    /**获取作业原始轮作业id
     * @param $homework_id
     */
    private function get_original_homework_id($homework_id)
    {
        $extend_from = DB::table('homeworks')->where('id', $homework_id)->value('extend_from');
        return $extend_from;
    }

    /**以数组形式返回对象数组的某个值
     * @param $objects
     * @param $value_name
     * @return array
     */
    private function get_object_value_as_array($objects, $value_name)
    {
        $value_list = [];
        foreach ($objects as $object) {
            $value_list[] = $object->$value_name;
        }
        return $value_list;
    }

    /**获取作业标准id并且以数组返回
     * @param $homework_id
     * @return array
     */
    private function get_standards($homework_id)
    {
        //判断该作业是否原始轮
        $round = $this->get_homework_round($homework_id);
        //不是的话获取原始轮作业id
        if ($round != 1) {
            $homework_id = $this->get_original_homework_id($homework_id);
        }
        //根据作业id获取标准id数组
        $standards = DB::table('homework_standard')->where('homework_id', $homework_id)->get()->toArray();
        return $this->get_object_value_as_array($standards, 'id');
    }

    /**获取作业的轮次和作业id
     * @param $homework_id
     * @return array
     */
    private function get_round_and_homework_id($homework_id)
    {
        $origin_homework_id = DB::table('homeworks')->where('id', $homework_id)->value('extend_from');
        $round_id = DB::table('homeworks')->where('extend_from', $origin_homework_id)->get()->toArray();
        $round_id_list = [];
        foreach ($round_id as $k => $v) {
            $round_id_list[$v->round] = $v->id;
        }
        return $round_id_list;
    }

    /**获取学生作业
     * @param $homework
     */
    private function get_student_homework_by_homework($homework_id)
    {
        return DB::table('student_homework')->where('homework_id', $homework_id)->get()->toArray();
    }
}
