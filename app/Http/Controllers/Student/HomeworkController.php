<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HomeworkController extends Controller
{

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
    public function get_four_homework1(Request $request)
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

        //获取所有批改数小于4并且assessing不为一的student_homework_id和内容
        $homeworks = DB::table('student_homework')->where('homework_id', $homework_id)->where('assess_num', '<=', 4)->where('assessing', 0)->get();
        $n = 0;
        $array_homeworks = [];
        foreach ($homeworks as $homework) {
            $array_homeworks[$n]['student_homework_id'] = $homework->id;
            $array_homeworks[$n]['content'] = $homework->content;
            $n += 1;
        }
        //随机挑选四个下标
        $random_array = $this->getRandomNums(4, 0, $n - 1);
        if (count($random_array) < 4) {
            return response_treatment(1, $type);
        }
        //遍历这些下标然后对应的student_homework的assessing置一
        $msg = [];
        foreach ($random_array as $random_num) {
            $random_id = $array_homeworks[$random_num]['student_homework_id'];
            DB::table('student_homework')->where('id', $random_id)->update(['assessing' => 1]);
            $msg[] = $array_homeworks[$random_num];
        }

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
        $homework_id = $post['homework_id'];
        //先判断是不是原始轮的
        $round = $this->get_homework_round($homework_id);
        if ($round == 1) {
            $standards = DB::table('homework_standard')->where('homework_id', $homework_id)->get()->toArray();
        } else {
            //获取原始轮作业id
            $ori_homework_id = $this->get_original_homework_id($homework_id);
            $standards = DB::table('homework_standard')->where('homework_id', $ori_homework_id)->get()->toArray();
        }

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
//            DB::table('student_homework')->where('id', $post['student_homework_id'])->update(['assessing' => 0]);
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

    /**修改作业
     * @param Request $request
     * @return mixed
     */
    public function modify_homework(Request $request)
    {
        $type = 'S3006';
        $post = $request->all();
        login_pretreat($type, $post);
        $update['modify'] = $post['modify'];
        DB::table('student_homework')->where('id', $post['student_homework_id'])->update($update);
        return response_treatment(0, $type);
    }

    /**
     * 获取作业列表（按时间排序）
     */
    public function get_homework_list_by_time(Request $request)
    {
        $type = 'S3007';
        $post = $request->all();
        login_pretreat($type, $post);
        //获取学生班别
        $student_id = session('id');
        $class_ids2 = DB::table('student_course')->where('student_id', $student_id)->select('class_id')->get()->toArray();
        //二维转一维
        $class_ids = [];
        foreach ($class_ids2 as $class_id) {
            $class_ids[] = $class_id->class_id;
        }
        //根据班别获取作业
        $homeworks = DB::table('homeworks')->whereIn('class_id', $class_ids)->orderBy('submit_ddl', 'desc')->get()->toArray();
        //提交人数
        foreach ($homeworks as $k => $homework) {
            $submit_num = DB::table('student_homework')->where('homework_id', $homework->id)->count();
            $homeworks[$k]->submit_num = $submit_num;
            //我的状态
            if ($homework->state == 1) {
                //如果是提交,查找student_homework表有没有该学生的作业
                $flag = DB::table('student_homework')
                    ->where(['student_id' => $student_id, 'homework_id' => $homework->id])
                    ->get()
                    ->toArray();
                if ($flag) {
                    $homeworks[$k]->student_homework_state = 1;
                } else {
                    $homeworks[$k]->student_homework_state = 0;
                }
            } elseif ($homework->state == 2) {
                //如果是互评,查找assessment有没有该学生的评价
                $flag = DB::table('assessment')
                    ->leftJoin('student_homework', 'assessment.student_homework_id', '=', 'student_homework.id')
                    ->where(['assessment.student_id' => $student_id, 'student_homework.homework_id' => $homework->id])
                    ->get()
                    ->toArray();
                if ($flag) {
                    $homeworks[$k]->student_homework_state = 1;
                } else {
                    $homeworks[$k]->student_homework_state = 0;
                }
            } elseif ($homework->state == 3) {
                //如果是修改,查找student_homework表中该学生的modify字段是否为空
                $flag = DB::table('student_homework')
                    ->where(['student_id' => $student_id, 'homework_id' => $homework->id])
                    ->value('modify')
                    ->toArray();
                if ($flag) {
                    $homeworks[$k]->student_homework_state = 1;
                } else {
                    $homeworks[$k]->student_homework_state = 0;
                }
            } else {
                //如果为完成,状态为完成
                $homeworks[$k]->student_homework_state = 1;
            }
        }
        return response_treatment(0, $type, $homeworks);
    }

    /**通过id获取作业详情
     * @param Request $request
     */
    public function get_homework_info_by_id(Request $request)
    {
        $type = 'S3008';
        $post = $request->all();
        login_pretreat($type, $post);
        //homeworks
        $homework_info = DB::table('homeworks')->where('id', $post['homework_id'])->first();
        if ($homework_info) {
            return response_treatment(0, $type, $homework_info);
        } else {
            return response_treatment(1, $type);
        }
    }

    /**
     * 获取该作业的同伴评价
     */
    public function get_assessment(Request $request)
    {
        $type = 'S3009';
        $post = $request->all();
        login_pretreat($type, $post);
        $student_id = session('id');
        //判断是不是原始轮
        //获取作业id
        $homework_id = DB::table('student_homework')->where('id', $post['student_homework_id'])->value('homework_id');
        //先获取该作业所有轮次的homework_id
        //获取该学生在该作业所有轮次的student_homework_id
        $homeworks = DB::table('homeworks')->where('extend_from', $homework_id)->get()->toArray();
        foreach ($homeworks as $homework) {
            //根据student_homework_id在assessment表找评价
            $student_homework = DB::table('student_homework')->where(['student_id' => $student_id, 'homework_id' => $homework->id])->first();
            if ($student_homework) {
                $assessments = DB::table('assessment')->where('student_homework_id', $student_homework->id)->get()->toArray();
                foreach ($assessments as $ac) {
                    //整合标准名
                    $ac->standard_name = DB::table('homework_standard')->where('id', $ac->standard_id)->value('standard');
                    //整合轮数
                    $ac->round = $this->get_homework_round($homework->id);
                }
            }
        }
        //整理结构，按标准分类
        return response_treatment(0, $type, $assessments);
    }

    /**获取该作业的修改
     * @param Request $request
     */
    public function get_modify(Request $request)
    {
        $type = 'S3010';
        $post = $request->all();
        login_pretreat($type, $post);
        //student_homework
        $modify = DB::table('student_homework')->where('id', $post['student_homework_id'])->select('modify')->first();
        if ($modify->modify) {
            return response_treatment(0, $type, $modify);
        } else {
            return response_treatment(1, $type);
        }
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


    /**随机获取四分作业
     * @param Request $request
     */
    public function get_four_homework(Request $request)
    {
        $type = 'S3002';
        $post = $request->all();
        login_pretreat($type, $post);
        $homework_id = $post['homework_id'];
        $student_id = session('id');
        //在assess_assign表中找到该学生和该作业的分配记录id，然后获取身后四个
//        $where_assess['student_id'] = $student_id;
        $where_assess['homework_id'] = $homework_id;
        $assigns = DB::table('assess_assign')->where($where_assess)->get()->toArray();
        $index = 0;
        foreach ($assigns as $k => $assign) {
            if ($assign->student_id == $student_id) {
                $index = $k;
                break;
            }
        }
        //判断$index之后有没有四个
        $student_ids = [];
        $num = count($assigns);
        $remain = $num - 1 - $index;
        if ($remain < 4) {
            $tail = array_slice($assigns, $index + 1, $remain);
            $head = array_slice($assigns, 0, 4 - $remain);
            $slice = array_merge($tail, $head);
        } else {
            $slice = array_slice($assigns, $index + 1, $remain);
        }
        foreach ($slice as $s) {
            $student_ids[] = $s->student_id;
        }
        //遍历$student_ids在student_homework中找学生作业
        $student_homeworks = DB::table('student_homework')
            ->whereIn('student_id', $student_ids)
            ->where('homework_id', $homework_id)
            ->get()
            ->toArray();
        if ($student_homeworks) {
            //assess_assign表assigned字段置一
            DB::table('assess_assign')
                ->whereIn('student_id', $student_ids)
                ->where('homework_id', $homework_id)
                ->update(['assigned' => 1]);
            return response_treatment(0, $type, $student_homeworks);
        } else {
            return response_treatment(1, $type);
        }
    }

    /**
     * 获取班级作业情况
     */
    public function get_homework_class_result(Request $request)
    {
        $type = 'S3012';
        $post = $request->all();
        login_pretreat($type, $post);
        $homework_id = $post['homework_id'];
        $round_id = $this->get_round_and_homework_id($homework_id);
        $class_result = [];
        foreach ($round_id as $round => $homework_id) {
            $student_homeworks = DB::table('student_homework')->where('homework_id', $homework_id)->get()->toArray();
            $student_homework_ids = $this->get_object_value_as_array($student_homeworks, 'id');
            //获取这个作业的标准id
            $standard_ids = $this->get_standards($homework_id);
            //通过标准id和学生作业id获取星数
            $class_star_avg = [];
            foreach ($standard_ids as $k => $standard_id) {
                $assessments = DB::table('assessment')->whereIn('student_homework_id', $student_homework_ids)->where('standard_id', $standard_id)->get()->toArray();
                $stars = $this->get_object_value_as_array($assessments, 'stars');
                $class_star_avg[$k]['standard_id'] = $standard_id;
                $class_star_avg[$k]['standard_name'] = DB::table('homework_standard')->where('id', $standard_id)->value('standard');
                $class_star_avg[$k]['avg_star'] = round(((float)array_sum($stars)) / (float)count($stars), 2);
            }
            //统计班级平均分
            $assessmens = DB::table('assessment')->where('student_homework_id', $student_homework_ids)->get()->toArray();
            $scores = $this->get_object_value_as_array($assessmens, 'total_score');
            $avg_score = round(((float)array_sum($scores)) / (float)count($scores), 2);
            $class_round_result['avg_score'] = $avg_score;
            $class_round_result['avg_star'] = $class_star_avg;
            $class_result[$round] = $class_round_result;
        }
        return response_treatment(0, $type, $class_result);
    }

    /**
     * 获取个人作业情况
     */
    public function get_homework_personal_result(Request $request)
    {
        $type = 'S3013';
        $post = $request->all();
        login_pretreat($type, $post);
        $student_id = session('id');
        $homework_id = $post['homework_id'];
        $round_id = $this->get_round_and_homework_id($homework_id);
        $class_result = [];
        foreach ($round_id as $round => $homework_id) {
            $student_homeworks = DB::table('student_homework')->where('homework_id', $homework_id)->where('student_id', $student_id)->get()->toArray();
            $student_homework_ids = $this->get_object_value_as_array($student_homeworks, 'id');
            //获取这个作业的标准id
            $standard_ids = $this->get_standards($homework_id);
            //通过标准id和学生作业id获取星数
            $personal_star_avg = [];
            foreach ($standard_ids as $k => $standard_id) {
                $assessments = DB::table('assessment')->whereIn('student_homework_id', $student_homework_ids)->where('standard_id', $standard_id)->get()->toArray();
                $stars = $this->get_object_value_as_array($assessments, 'stars');
                $personal_star_avg[$k]['standard_id'] = $standard_id;
                $personal_star_avg[$k]['standard_name'] = DB::table('homework_standard')->where('id', $standard_id)->value('standard');
                $personal_star_avg[$k]['avg_star'] = round(((float)array_sum($stars)) / (float)count($stars), 2);
                $personal_star_avg[$k]['assessments'] = $this->get_object_value_as_array($assessments, 'content');
            }
            //统计个人平均分
            $assessmens = DB::table('assessment')->where('student_homework_id', $student_homework_ids)->get()->toArray();
            $scores = $this->get_object_value_as_array($assessmens, 'total_score');
            $avg_score = round(((float)array_sum($scores)) / (float)count($scores), 2);
            $personal_round_result['avg_score'] = $avg_score;
            $personal_round_result['avg_star'] = $personal_star_avg;
            $personal_result[$round] = $personal_round_result;
        }
        return $personal_result;
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

    /**
     * 定时器：修改作业状态
     */
    public function crontab_change_homework_state(Request $request)
    {
        //获取当前时间
        $now = date("Y-m-d H:i:s");
        //在homework表里面查找状态不为4的记录
        $homeworks = DB::table('homeworks')->where('state', '<>', 4)->get()->toArray();
        //按照state类型判断相应的ddl
        foreach ($homeworks as $homework) {
            if ($homework->state == 1) {
                $submit_ddl = $homework->submit_ddl;
                if (strtotime($submit_ddl) < strtotime($now)) {
                    //如果当前时间大于ddl，则state加一
                    DB::table('homeworks')->where('id', $homework->id)->increment('state');
                    //给学生分配作业
                    $this->assess_assign($homework->id);
                }
            } else if ($homework->state == 2) {
                $assess_ddl = $homework->assessment_ddl;
                if (strtotime($assess_ddl) < strtotime($now)) {
                    DB::table('homeworks')->where('id', $homework->id)->increment('state');
                }
            } else if ($homework->state == 3) {
                $modify_ddl = $homework->modify_ddl;
                if (strtotime($modify_ddl) < strtotime($now)) {
                    DB::table('homeworks')->where('id', $homework->id)->increment('state');
                }
            }
        }
    }

    /**
     * 评价分配表
     */
    private function assess_assign($homework_id)
    {
        //获取该作业对应的班级的所有的学生id
        $class_id = DB::table('homeworks')->where('id', $homework_id)->value('class_id');
        $students = DB::table('student_course')->where('class_id', $class_id)->get()->toArray();
        $student_ids = [];
        foreach ($students as $student) {
            $student_ids[] = $student->student_id;
        }
        //学生id打乱
        shuffle($student_ids);
        //存进数据库,homework_id,学生id
        foreach ($student_ids as $student_id) {
            $insert['homework_id'] = $homework_id;
            $insert['student_id'] = $student_id;
            $assign = DB::table('assess_assign')->where($insert)->get()->toArray();
            if (!$assign) {
                DB::table('assess_assign')->insert($insert);
            }
        }
    }
}
