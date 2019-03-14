<?php
/**
 * Created by PhpStorm.
 * User: LiuTao
 * Date: 2017/12/7/007
 * Time: 10:53
 *
 */

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use app\improve\model\BaseDb;
use think\Db;


use tool\Error;
use tool\Communal;

//病虫害调查
class CountryRecordDb extends BaseDb
{
    /*已改 Lxl*/
    //增加记录
    static function add($data)
    {
        try {
            $data['status']      = 1;
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];

            if (Helper::lsWhere($data, 'images')) {
                $images = $data['images'];
            }
            unset($data['images']);
            if (Helper::lsWhere($data, 'record')) {
                $record = $data['record'];
            }
            unset($data['record']);

            Db::startTrans();
            //病虫害调查--虫害类型
            if ($data['hazard_type'] == '1') {
                $dbRes = Db::table('improve_survey_record')
                    ->field('region,region_name,hazard_type,pest_id,pest_name,plant_id,plant_name,happen_level,hazard_level,generation,harmful_part,strain_rate,is_main_pests,distribution_area,damaged_area,positions,position_type,location_name,remarks,create_time,update_time,adder,status,report_name')
                    ->strict(false)
                    ->insertGetId($data);
                //虫态龄期记录上传
                if (!empty($record)) {
                    foreach ($record as $key => $val) {
                        unset($val['check']);
                        unset($val['id']);
                        switch ($val['tense']) {
                            case "1":
                                $val['tense_name'] = "卵";
                                break;
                            case "2":
                                $val['tense_name'] = "幼虫(若虫)";
                                break;
                            case "3":
                                $val['tense_name'] = "蛹";
                                break;
                            case "4":
                                $val['tense_name'] = "成虫";
                                break;
                        }
                        $res          = Db::table('improve_survey_insect_record')->insertGetId($val);
                        $abroad_data  = [
                            'sr_id'       => $dbRes,
                            'create_time' => $data['create_time'],
                            'update_time' => $data['create_time'],
                            'status'      => 1
                        ];
                        $abroad_dbRes = Db::table('improve_survey_insect_record')->where('id', $res)->update($abroad_data);
                    }
                }
            };
            //病虫害调查--病害类型
            if ($data['hazard_type'] == '2') {
                $dbRes = Db::table('improve_survey_record')->field('region,region_name,hazard_type,pest_id,pest_name,plant_id,plant_name,happen_level,hazard_level,harmful_part,
                susceptibility,is_main_pests,distribution_area,damaged_area,positions,position_type,location_name,remarks,strain_rate,create_time,
                update_time,adder,status,report_name')->strict(false)->insertGetId($data);
            }
            //病虫害调查--有害植物类型
            if ($data['hazard_type'] == '3') {
                $dbRes = Db::table('improve_survey_record')->field('region,region_name,hazard_type,pest_id,pest_name,happen_level,hazard_level,plant_cover_degree,habitat_type,
                is_main_pests,distribution_area,damaged_area,species_type,positions,position_type,location_name,remarks,coverage,
                strain_rate,create_time,update_time,status,adder,report_name')->strict(false)->insertGetId($data);
            }
            if ($dbRes < 0) return Error::error('添加错误');

            //图片上传
            if (!empty($images)) {
                $path = Db::table('improve_uploads')->whereIn('id', $images)->field('path')->select();
                if (empty($path)) return Error::error('上传的文件未找到');
                foreach ($path as $val) {
                    // 原图保存
                    $record = [
                        'record_id'   => $dbRes,
                        'type'        => 2,
                        'path'        => $val['path'],
                        'create_time' => $data['create_time'],
                        'status'      => 1
                    ];
                    $a      = Db::table('improve_survey_record_image')->insert($record);
                    if ($a < 1) return Error::error('图片添加失败');
                }
            }
            Db::commit();
            return Communal::success('添加成功,记录id为：' . $dbRes);
        } catch (\Exception $e) {
            Db::rollback();
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //列表
    static function ls($data, $sample = false)
    {
        try {
            $where = 'sr.status = 1';
            $order = 'sr.create_time desc';
            if ($sample) {
                $field = 'sr.id, sr.positions, sr.location_name, sr.region_name, sr.hazard_type, sr.pest_name, sr.create_time surveyer_time, u.name surveyer,u.cellphone';
            } else {
                $field = 'sr.id, sr.region_name, sr.hazard_type, sr.pest_name, sr.happen_level, sr.hazard_level, sr.distribution_area, sr.damaged_area, u.name adder,sr.adder uid,u.cellphone, sr.create_time';
            }
            if (!empty($data['region'])) $where .= " and sr.region like '%" . $data['region'] . "%'";
            if (!empty($data['pest'])) $where .= " and sr.pest_id = " . $data['pest'];
            if (!empty($data['type'])) $where .= " and sr.hazard_type = " . $data['type'];
            if (!empty($data['begin_time'])) $where .= " and DATE(sr.create_time) >='" . $data['begin_time'] . "'";
            if (!empty($data['end_time'])) $where .= " and DATE(sr.create_time) <='" . $data['end_time'] . "'";
            if (!empty($data['adder'])) $where .= " and u.name like'%" . $data['adder'] . "%'";
            if (!empty($data['user_name'])) $where .= " and u.`name` like '%" . $data['user_name'] . "%'";
            if (!empty($data['tel'])) $where .= " and u.cellphone = " . $data['tel'];
            $dataRes = Db::table('improve_survey_record')->alias('sr')->join('frame_base_staff u', 'u.uid = sr.adder', 'left')->field($field)->where($where)->order($order)
                ->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //app--类型查询有害生物种类
    static function pestList($data)
    {
        try {
            $where = 'status = 1';
            $order = 'pest_id desc';
            if (!empty($data['name'])) $where .= " and pest_name like '%" . $data['name'] . "%'";
            if (!empty($data['type'])) $where .= " and hazard_type = " . $data['type'];
            $dataRes = Db::table('improve_survey_record')->field('pest_id value, pest_name label')->where($where)->group('pest_name')->order($order)->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            return is_array($dataRes) ? Communal::successData($dataRes) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //Web--生物类型关联列表中已有生物--查询
    static function pestWebList($data)
    {
        try {
            $where              = 'status = 1';
            $order              = 'pest_id desc';
            $dataRes['insect']  = Db::table('improve_survey_record')->field('pest_id value, pest_name label')->where($where)->where('hazard_type', 1)->group('pest_name')->order($order)->select();
            $dataRes['disease'] = Db::table('improve_survey_record')->field('pest_id value, pest_name label')->where($where)->where('hazard_type', 2)->group('pest_name')->order($order)->select();
            $dataRes['plant']   = Db::table('improve_survey_record')->field('pest_id value, pest_name label')->where($where)->where('hazard_type', 3)->group('pest_name')->order($order)->select();
            $dataRes['all']     = Db::table('improve_survey_record')->field('pest_id value, pest_name label')->where($where)->group('pest_name')->order($order)->select();
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //病虫害详情
    static function query($data)
    {
        try {
            //improve_survey_record 病虫害调查表,根据id查询出危害类型
            $type = Db::table('improve_survey_record')->where('id', $data['id'])->field('hazard_type')->find();
            if (empty($type)) return Error::error('危害类型错误');
            if ($type['hazard_type'] == '1') {
                $field = 'sr.id,sr.region,sr.region_name,sr.positions,sr.position_type,sr.location_name,sr.hazard_type,sr.pest_id,sr.pest_name,sr.plant_id,sr.plant_name,sr.happen_level,sr.harmful_part,sr.generation,
                sr.strain_rate,sr.hazard_level,sr.distribution_area,sr.damaged_area,sr.is_main_pests,sr.remarks,u.name adder,u.cellphone,sr.create_time';
            };
            if ($type['hazard_type'] == '2') {
                $field = 'sr.id,sr.region,sr.region_name,sr.positions,sr.position_type,sr.location_name,sr.hazard_type,sr.pest_id,sr.pest_name,sr.plant_id,sr.plant_name,sr.happen_level,sr.harmful_part,sr.susceptibility,
                sr.strain_rate,sr.hazard_level,sr.distribution_area,sr.damaged_area,sr.is_main_pests,sr.remarks, u.name adder,u.cellphone,sr.create_time';
            };
            if ($type['hazard_type'] == '3') {
                $field = 'sr.id,sr.region,sr.region_name,sr.positions,sr.position_type,sr.hazard_type,sr.location_name,sr.pest_id,sr.pest_name,sr.habitat_type,sr.happen_level,sr.species_type,sr.plant_cover_degree,
                sr.strain_rate,sr.hazard_level,sr.distribution_area,sr.damaged_area,sr.is_main_pests,sr.remarks, u.name adder,u.cellphone,sr.create_time';
            };

            $dbRes = Db::table('improve_survey_record')->alias('sr')->join('frame_base_staff u', 'u.uid = sr.adder', 'left')->field($field)
                ->where('sr.id', $data['id'])->where('sr.status', 1)->find();
            if (empty($dbRes)) return Error::error('未找到相应数据');

            //表 improve_survey_record_image 根据外键 record_id = id 查出图片id,path
            $dbRes['images'] = Db::table('improve_survey_record_image')->field('id,path')->where('record_id', $data['id'])->where('status', 1)->select();
            if ($type['hazard_type'] == '1') { //如果危害类型为虫害 表 improve_survey_insect_record(病虫害调查--虫害记录表) 根据外键 sr_id = id 查出信息
                $dbRes['record'] = Db::table('improve_survey_insect_record')->field('id,tense,tense_name,age,pests_density,pests_unit')->where('sr_id', $data['id'])->where('status', 1)->select();
            };

            return Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //删除选中
    static function deleteChecked($ids)
    {
        try {
            $dataRes = Db::table('improve_survey_record')->whereIn('id', $ids)->update(['status' => 2]);
            return empty($dataRes) ? Error::error('删除错误') : Communal::success('删除成功');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    // 编辑
    static function edit($data)
    {
        try {
            // $dv = static::query($data['id']);
            if (!static::query($data['id'])) return Error::error('未找到相应数据');
            $path                = [];
            $data['update_time'] = date('Y-m-d H:i:s');
            Db::startTrans();
            //图片上传与修改处理
            if (Helper::lsWhere($data, 'images')) {
                $images = $data['images'];
                unset($data['images']);
                $path = Db::table('improve_uploads')->whereIn('id', $images)->field('path')->select();
                if (empty($path)) return Error::error('上传的文件未找到');
                foreach ($path as $val) {
                    $record = [
                        'record_id'   => $data['id'],
                        'path'        => $val['path'],
                        'create_time' => $data['update_time'],
                        'status'      => 1
                    ];
                    $a      = Db::table('improve_survey_record_image')->insert($record);
                    if ($a < 1) return Error::error('图片添加失败');
                }
            }

            //删除图片处理
            if (Helper::lsWhere($data, 'del_images')) {
                $del_images = explode(',', $data['del_images']);
                $paths      = Db::table('improve_survey_record_image')->field('path')->where('record_id', $data['id'])->whereIn('id', $del_images)->select();
                if (count($paths) !== count($del_images)) return Error::error('删除的图片没有找到');
                $delRes = Db::table('improve_survey_record_image')->whereIn('id', $del_images)->update(['status' => 2]);
                if ($delRes !== count($del_images)) return Error::error('删除错误');
            }
            unset($data['del_images']);
            $type = Db::table('improve_survey_record')->where('id', $data['id'])->field('hazard_type')->find();
            if (empty($type)) return Error::error('类型错误');
            if ($type['hazard_type'] == '1') {

                //虫害相关记录--删除处理
                if (Helper::lsWhere($data, 'del_records')) {
                    $del_ids    = explode(',', $data['del_records']);
                    $record_res = Db::table('improve_survey_insect_record')->whereIn('id', $del_ids)->update(['status' => 2]);
                    if ($record_res < 0) return Error::error('删除样本记录错误');
                }
                unset($data['del_records']);
                //虫害相关记录--修改和添加处理
                if (Helper::lsWhere($data, 'record')) {
                    $pine_record = $data['record'];

                    foreach ($pine_record as $key => $val) {
                        switch ($val['tense']) {
                            case "1":
                                $val['tense_name'] = "卵";
                                break;
                            case "2":
                                $val['tense_name'] = "幼虫(若虫)";
                                break;
                            case "3":
                                $val['tense_name'] = "蛹";
                                break;
                            case "4":
                                $val['tense_name'] = "成虫";
                                break;
                        }
                        if (isset($val['id']) && $val['id'] != 0) {
                            unset($val['check']);
                            $val['update_time'] = $data['update_time'];
                            $dbRes              = Db::table('improve_survey_insect_record')->update($val);
                            if (empty($dbRes)) return Error::error('修改样本记录错误');
                        } else {
                            unset($val['check']);
                            unset($val['id']);
                            $val['sr_id']       = $data['id'];
                            $val['create_time'] = $data['update_time'];
                            $val['update_time'] = $data['update_time'];
                            $val['status']      = 1;
                            $record_dbRes       = Db::table('improve_survey_insect_record')->insertGetId($val);
                        }
                    }
                }
                unset($data['record']);
                $field = 'region,region_name,hazard_type,pest_id,pest_name,plant_id,plant_name,happen_level,hazard_level,
                generation,harmful_part,strain_rate,is_main_pests,distribution_area,damaged_area,positions,position_type,
                location_name,remarks,update_time,adder,report_name';
            };
            //病害类型
            if ($type['hazard_type'] == '2') {
                $field = 'region,region_name,hazard_type,pest_id,pest_name,plant_id,plant_name,happen_level,hazard_level,susceptibility,
                harmful_part,strain_rate,is_main_pests,distribution_area,damaged_area,positions,position_type,location_name,
                remarks,update_time,adder,report_name';
            };
            //有害植物
            if ($type['hazard_type'] == '3') {
                $field = 'region,region_name,hazard_type,pest_id,pest_name,happen_level,hazard_level,plant_cover_degree,habitat_type,
                is_main_pests,distribution_area,damaged_area,species_type,positions,position_type,location_name,remarks,
                strain_rate,update_time,adder,report_name';
            };
            unset($data['did']);
            $dbRes = Db::table('improve_survey_record')->field($field)->update($data);
            Db::commit();
            return $dbRes == 1 ? Communal::success('编辑信息成功') : Error::error('修改错误');
        } catch (\Exception $e) {
            Db::rollback();
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //调查记录统计图
    static function trendChart($data)
    {
        try {
            $res = [];
            //分布面积
            if ($data['type'] == 1) {
                $field = "SUM(distribution_area) distribution_area,DATE_FORMAT(create_time,'%Y-%m') time";
                $name  = 'SUM(distribution_area) total';
            }
            //成灾面积
            if ($data['type'] == 2) {
                $field = "SUM(damaged_area) damaged_area,DATE_FORMAT(create_time,'%Y-%m') time";
                $name  = 'SUM(damaged_area) total';
            }
            $dbRes = Db::table('improve_survey_record')
                ->field($field)
                ->where('status', 1)
                ->where('pest_id', $data['pest'])
                ->whereLike('region', $data['region'] . '%')
                ->where("DATE_FORMAT(create_time, '%Y-%m') >='" . $data['start_time'] . "'")
                ->where("DATE_FORMAT(create_time, '%Y-%m') <='" . $data['end_time'] . "'")
                ->group('time')
                ->select();

            if (empty($dbRes)) {
                $res['title'] = '';
            } else {
                $total_res   = Db::table('improve_survey_record')
                    ->field($name)
                    ->where('pest_id', $data['pest'])
                    ->whereLike('region', $data['region'] . '%')
                    ->where('status', 1)
                    ->where("DATE_FORMAT(create_time, '%Y-%m') >='" . $data['start_time'] . "'")
                    ->where("DATE_FORMAT(create_time, '%Y-%m') <='" . $data['end_time'] . "'")
                    ->find();
                $region_name = BaseDb::areaName($data['region']);
                $pest_name   = BaseDb::pest($data['pest']);
                $begin_year  = substr($data['start_time'], 0, 4);
                $begin_month = substr($data['start_time'], -2);
                $end_year    = substr($data['end_time'], 0, 4);
                $end_month   = substr($data['end_time'], -2);
                //分布面积
                if ($data['type'] == 1) {
                    $res['title'] = $region_name . $pest_name . '--' . $begin_year . '年' . $begin_month . '月' . '到' . $end_year . '年' . $end_month . '月' . '分布面积为' . $total_res['total'] . '亩';
                }
                //成灾面积
                if ($data['type'] == 2) {
                    $res['title'] = $region_name . $pest_name . '--' . $begin_year . '年' . $begin_month . '月' . '到' . $end_year . '年' . $end_month . '月' . '成灾面积为' . $total_res['total'] . '亩';
                }
            }
            $res['data'] = $dbRes;
            return Communal::successData($res);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    // 调查记录统计
    static function villagesList($data)
    {
        try {
            //总数统计
            $dataResOne = CountryRecordDb::villagesTol($data);
            //按月统计
            $dataResTwo = CountryRecordDb::villagesListSon($data);
            $result     = array('dataResOne' => $dataResOne, 'dataResTwo' => $dataResTwo);
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    //调查记录统计--列表导出
    static function recordExcel($data)
    {
        try {
            //总数统计
            $dataResOne = CountryRecordDb::villagesTol($data);
            //相关数据
            $dataResTwo = CountryRecordDb::villagesRecord($data);
            $result     = array('dataResOne' => $dataResOne, 'dataResTwo' => $dataResTwo);
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //总数统计
    static function villagesTol($data)
    {
        $d  = "count(distinct(region)) total_town,sum(distribution_area) total_distribution_area,sum(damaged_area) total_damaged_area, count(id) total_survey";
        $db = Db::table('improve_survey_record')
            ->field($d)
            ->where('status', 1)
            ->whereLike('region', $data['region'] . '%')
            ->where('pest_id', $data['pest'])
            ->where("DATE_FORMAT(create_time, '%Y-%m') >='" . $data['start_time'] . "'")
            ->where("DATE_FORMAT(create_time, '%Y-%m') <='" . $data['end_time'] . "'")
            ->find();
        return $db;
    }

    /*已改*/
    // 按月统计
    static function villagesListSon($data)
    {
        $a  = "region,region_name, pest_name, DATE_FORMAT(create_time,'%Y-%m') create_time,SUM(distribution_area) distribution_area,SUM(damaged_area) damaged_area,count(id) sum_survey";
        $db = Db::table('improve_survey_record')
            ->field($a)
            ->where('status', 1)
            ->where('pest_id', $data['pest'])
            ->whereLike('region', $data['region'] . '%')
            ->where("DATE_FORMAT(create_time, '%Y-%m') >='" . $data['start_time'] . "'")
            ->where("DATE_FORMAT(create_time, '%Y-%m') <='" . $data['end_time'] . "'")
            ->group('create_time,region')->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
        return $db;
    }

    // 按月统计--列表导出
    static function villagesRecord($data)
    {
        $a  = "region,region_name, pest_name, DATE_FORMAT(create_time,'%Y-%m') create_time,SUM(distribution_area) distribution_area,SUM(damaged_area) damaged_area,count(id) sum_survey";
        $db = Db::table('improve_survey_record')
            ->field($a)
            ->where('status', 1)
            ->where('pest_id', $data['pest'])
            ->whereLike('region', $data['region'] . '%')
            ->where("DATE_FORMAT(create_time, '%Y-%m') >='" . $data['start_time'] . "'")
            ->where("DATE_FORMAT(create_time, '%Y-%m') <='" . $data['end_time'] . "'")
            ->group('create_time,region_name')->select();
        return $db;
    }

    /*已改*/
    //历史对比图
    static function history($data)
    {
        try {
            $dbRes       = Db::table('improve_survey_record')
                ->field('COUNT(DISTINCT(region)) AS num, YEAR (create_time) AS year')
                ->where('status', 1)
                ->whereLike('region', $data['region'] . '%')
                ->where('LENGTH(region) = 9')
                ->where("YEAR (create_time) >='" . $data['start_time'] . "'")
                ->where("YEAR (create_time) <='" . $data['end_time'] . "'")
                ->where('pest_id', $data['pest'])
                ->group('year')
                ->select();
            $region_name = BaseDb::areaName($data['region']);
            $pest_name   = BaseDb::pest($data['pest']);
            if (empty($dbRes)) {
                $res['title'] = '';
            } else {
                $res['title'] = $region_name . $pest_name . '--' . $data['start_time'] . '年与' . $data['end_time'] . '年' . $pest_name . '发生范围历史对比图';
            }
            $res['data'] = $dbRes;
            return Communal::successData($res);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    // static function heatMap($data){
    //     try {
    //         $res = [];
    //         $last_month = strtotime("-1 Months");
    //         $begin_time = date('Y-m', time());
    //         $end_time = date("Y-m", $last_month);
    //         $month = [$begin_time,$end_time];
    //         foreach ($month as $key=> $item){
    //             switch ($data['type']) {
    //                 case '1':
    //                     $querySql='SELECT SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(positions, ";", 1),",",1),2) lng,
    //                     SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(positions, ";", 1),",",-1),")",1) lat
    //                     FROM b_pineline_abroad';
    //                     break;
    //                 case '2':
    //                     $querySql='SELECT SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(positions, ";", 1),",",1),2) lng,
    //                     SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(positions, ";", 1),",",-1),")",1) lat
    //                     FROM improve_pineline_indoor';
    //                     break;
    //                 default:
    //                     $querySql='SELECT SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(positions, ";", 1),",",1),2) lng,
    //                     SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(positions, ";", 1),",",-1),")",1) lat
    //                     FROM improve_survey_record';
    //                     break;
    //             }
    //             $querySql.=" where create_time like"."'".$item.'%'."'";
    //             if (Helper::lsWhere($data, 'region'))
    //                 $querySql.=" and region like"."'".$data['region'].'%'."'";
    //             if ($data['type'] == 3) {
    //                 if (Helper::lsWhere($data, 'pest_id'))
    //                 $querySql.=" and pest_id = ".$data['pest_id'];
    //             }
    //             $query = Db::query($querySql);
    //             array_push($res, $query);
    //         }
    //         return [true , $res];
    //     } catch (\Exception $e) {
    //         return Error::error($e->getMessage());
    //     }
    // }

    /*已改*/
    //病虫害分布信息
    static function heatMap($data)
    {
        try {
            $result     = [];
            $last_month = strtotime("-1 Months");
            $begin_time = date('Y-m', time());
            $end_time   = date("Y-m", $last_month);
            $month      = [$begin_time, $end_time];
            foreach ($month as $key => $item) {
                switch ($data['type']) {
                    case '1':
                        $abroad_Sql = 'SELECT SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(positions, ";", 1),",",1),2) lng,
                        SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(positions, ";", 1),",",-1),")",1) lat
                        FROM improve_pineline_abroad';
                        $indoor_Sql = 'SELECT SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(positions, ";", 1),",",1),2) lng,
                        SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(positions, ";", 1),",",-1),")",1) lat
                        FROM improve_pineline_indoor';
                        $abroad_Sql .= " where create_time like" . "'" . $item . '%' . "'";
                        $indoor_Sql .= " where create_time like" . "'" . $item . '%' . "'";
                        if (Helper::lsWhere($data, 'region')) {
                            $abroad_Sql .= " and region like" . "'" . $data['region'] . '%' . "'";
                            $indoor_Sql .= " and region like" . "'" . $data['region'] . '%' . "'";
                        }
                        $indoor_query = Db::query($indoor_Sql);
                        $abroad_query = Db::query($abroad_Sql);
                        $res[]        = array_merge($indoor_query, $abroad_query);
                        break;
                    case '2':
                        $querySql = 'SELECT SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(positions, ";", 1),",",1),2) lng,
                        SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(positions, ";", 1),",",-1),")",1) lat
                        FROM improve_survey_record';
                        $querySql .= " where create_time like" . "'" . $item . '%' . "'";
                        if (Helper::lsWhere($data, 'pest_id')) {
                            $querySql .= " and pest_id = " . $data['pest_id'];
                        }
                        if (Helper::lsWhere($data, 'region')) {
                            $querySql .= " and region like" . "'" . $data['region'] . '%' . "'";
                        }
                        $res[] = Db::query($querySql);
                        break;
                }
                if ($data['type'] == 1) {
                    $result = $res;
                } elseif ($data['type'] == 2) {
                    $result = $res;
                }
            }
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }


    /*已改*/
    //导出
    static function exportls($data, $field, $img, $record, $condition)
    {
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            $field .= ',id';
            if (!empty($condition['type'])) $where .= " and hazard_type = " . $condition['type'];
            if (!empty($condition['region'])) $where .= " and region like '%" . $condition['region'] . "%'";
            if (!empty($condition['surveyer'])) $where .= " and report_name like '%" . $condition['surveyer'] . "%'";
            if (!empty($condition['pest'])) $where .= " and pest_name like '%" . $condition['pest'] . "%'";
            if (!empty($condition['begin_time'])) $where .= " and DATE(create_time) >='" . $condition['begin_time'] . "'";
            if (!empty($condition['end_time'])) $where .= " and DATE(create_time) <='" . $condition['end_time'] . "'";
            $dataRes = Db::table('improve_survey_record')->field($field)->where($where)->order($order)->select();

            // 获取图片
            if ($img) {
                foreach ($dataRes as $key => $val) {
                    $dataRes[$key]['img'] = Db::table('improve_survey_record_image')->where('record_id', $val['id'])->where('status', 1)->field('path')->select();
                }
            }
            // 获取记录
            if ($record != false) {
                foreach ($dataRes as $key => $val) {
                    $result = Db::table('improve_survey_insect_record')->where('sr_id', $val['id'])->where('status', 1)->field($record)->select();
                    if (!empty($result)) {
                        foreach ($result as $ky => $vl) {
                            $val['record'][$ky] = array_values($vl);
                        }
                        $dataRes [$key] = $val;
                    } else {
                        $dataRes[$key]['record'] = [];
                    }
                }
            }
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
}