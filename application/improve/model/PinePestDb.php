<?php
/**
 * Created by sevenlong.
 * User: Administrator
 * Date: 2017/12/13 0013
 * Time: 11:35
 */

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use app\improve\controller\UploadHelper;
use think\Db;
use think\Exception;

/*
 * 松材线虫病调查--外业调查
 */
class PinePestDb extends BaseDb
{
    // 添加记录
    static function add($data){
        try {
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $data['status'] = 1;
            if (Helper::lsWhere($data, 'images')){
                $images = $data['images'];
            }
            unset($data['images']);
            Db::startTrans();
            if (Helper::lsWhere($data, 'record')){
                $record = $data['record'];
                unset($data['record']);
                $dbRes = Db::table('b_pineline_abroad')->insertGetId($data);
                foreach ($record as $key => $val) {
                    unset($val['check']);
                    unset($val['id']);
                    // 取样部位
                    switch ($val['sampling_part'])
                    {
                        case "1":$val['sampling_part_name'] = "上";
                            break;
                        case "2":$val['sampling_part_name'] = "中";
                            break;
                        case "3":$val['sampling_part_name'] = "下";
                            break;
                    }
                    // 送检结果，1表示无线虫、2表示松材线虫、3表示拟松材线虫、4表示其他线虫
                    switch ($val['results'])
                    {
                        case "1":$val['results_name'] = "无线虫";
                            break;
                        case "2":$val['results_name'] = "松材线虫";
                            break;
                        case "3":$val['results_name'] = "拟松材线虫";
                            break;
                        case "4":$val['results_name'] = "其他线虫";
                            break;
                    }
                    $res = Db::table('b_pineline_abroad_record')->insertGetId($val);
                    $abroad_data = [
                        'abroad_id' => $dbRes,
                        'create_time' => $data['create_time'],
                        'update_time' => $data['create_time'],
                        'status' => 1
                    ];
                    $abroad_dbRes = Db::table('b_pineline_abroad_record')->where('id',$res)->update($abroad_data);
                }    
            }else{
                unset($data['record']);
                $dbRes = Db::table('b_pineline_abroad')->insertGetId($data);
            }
            if ($dbRes < 1) return Errors::ADD_ERROR;
            if (!empty($images)) {
                $path = Db::table('b_uploads')->whereIn('id',$images)->field('path')->select();
                if (empty($path)) return Errors::HAS_NO_FILE;
                foreach ($path as $val) {
                    $record = [
                        'record_id' => $dbRes,
                        'path' => $val['path'],
                        'create_time' => $data['create_time'],
                        'status' => 1
                    ];
                    $a = Db::table('b_pineline_abroad_image')->insert($record);
                    if ($a < 1) return Errors::IMAGES_INSERT_ERROR;
                }
            }
            Db::commit();
            return [true, $dbRes];
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    //列表
    static function ls($data){
        try {
            $where = 'plp.status = 1';
            $order = 'plp.create_time desc';
            if(!empty($data['region'])) $where.=" and plp.region like '%".$data['region']."%'";
            if(!empty($data['surveyer'])) $where.=" and u.name like '%".$data['surveyer']."%'";
            if(!empty($data['start_time'])) $where.=" and DATE(plp.create_time) >='".$data['start_time']."'";
            if(!empty($data['end_time'])) $where.=" and DATE(plp.create_time) <='".$data['end_time']."'";
            if(!empty($data['tel'])) $where.=" and u.tel = ".$data['tel'];
            $field = 'plp.id, plp.region, plp.region_name, plp.class_number, plp.forest_class_area,plp.main_tree, 
            plp.dead_pine_num, u.name surveyer,u.tel,plp.adder uid, plp.create_time survey_time';
            $dataRes = Db::table('b_pineline_abroad')->alias('plp')->field($field)->join('u_user u', 'u.uid = plp.adder', 'left')->where($where)
            ->order($order)->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            if(empty($dataRes)) return Errors::DATA_NOT_FIND;
            return [true,$dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

     //松材线虫病调查信息
     static function sampleMap($data){
        try {
            $where = 'plp.status = 1';
            $order = 'plp.create_time desc';
            if (!empty($data['region'])) $where.=" and plp.region like '%".$data['region']."%'";
            if (!empty($data['start_time'])) $where.=" and DATE(plp.create_time) >='".$data['start_time']."'";
            if (!empty($data['end_time'])) $where.=" and DATE(plp.create_time) <='".$data['end_time']."'";
            if ($data['type'] == 1){
                $field = 'plp.id, plp.positions,plp.location_name,plp.region_name, plp.class_number, plp.forest_class_area,plp.main_tree,u.name surveyer,plp.adder uid,u.tel, plp.create_time survey_time';
                $table = 'b_pineline_abroad';
            }
            if ($data['type'] == 2){
                $field = 'plp.id, plp.positions,plp.location_name,plp.region_name, plp.number,plp.sampling_part,plp.appraiser,u.name adder, u.tel,plp.adder uid,plp.create_time appra_time';
                $table = 'b_pineline_indoor';
            }
            $dataRes = Db::table($table)->alias('plp')->field($field)->join('u_user u', 'u.uid = plp.adder','left')->where($where)
            ->order($order)->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            if(empty($dataRes)) return Errors::DATA_NOT_FIND;
            return [true,$dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

	 // 根据id查询
    static function query($data){
        try {
            $dbRes = Db::table('b_pineline_abroad')->alias('plp')->where('plp.id', $data['id'])->where('plp.status',1)
                ->join('u_user u', 'u.uid = plp.adder', 'left')
                ->field('plp.*,u.name surveyer,u.tel')
                ->find();
            if (!is_array($dbRes)) return Errors::DATA_NOT_FIND;
            $dbRes['record'] = Db::table('b_pineline_abroad_record')->where('abroad_id', $data['id'])->where('status',1)->field('abroad_id,create_time,update_time,status',true)->select();       
            $dbRes['images'] = Db::table('b_pineline_abroad_image')->where('record_id', $data['id'])->where('status',1)->field('id,path')->select();       
            $result = Helper::transFormation($dbRes);
            return !empty($result) ? [true, $result] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // 查询添加人
    static function adder($id){
        try {
            $dbRes = Db::table('b_pineline_abroad')->field('adder')->where('id',$id)
                ->find();
            return !empty($dbRes) ? [true, $dbRes] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // 编辑
    static function edit($data){
        try {
            // $dv = static::query($data['id']);
            if (!static::query($data['id'])) return Errors::DATA_NOT_FIND;
            $path = [];
            $data['update_time'] =  date('Y-m-d H:i:s');
            Db::startTrans();
            //图片上传与修改处理
            if (Helper::lsWhere($data,'images')) {
                $images = $data['images'];
                unset($data['images']);
                $path = Db::table('b_uploads')->whereIn('id',$images)->field('path')->select();
                if (empty($path)) return Errors::HAS_NO_FILE;
                foreach ($path as $val) {
                    $record = [
                        'record_id' => $data['id'],
                        'path' => $val['path'],
                        'create_time' => $data['update_time'],
                        'status' => 1
                    ];
                    $a = Db::table('b_pineline_abroad_image')->insert($record);
                    if ($a < 1) return Errors::IMAGES_INSERT_ERROR;
                }
            }
            //删除图片处理
            if(Helper::lsWhere($data,'del_images')){
                $del_images = explode(',',$data['del_images']);
                $paths = Db::table('b_pineline_abroad_image')->field('path')->where('record_id', $data['id'])->whereIn('id',$del_images)->select();
                if (count($paths) !== count($del_images)) return Errors::NO_IMAGES_DELETED;
                $delRes = Db::table('b_pineline_abroad_image')->whereIn('id',$del_images)->update(['status' => 2]);
                if ($delRes !== count($del_images)) return Errors::DELETE_ERROR;
            }
            unset($data['del_images']);
            //样本记录--删除处理
            if(Helper::lsWhere($data,'del_records')){
                $del_ids = explode(',',$data['del_records']);
                $record_res = Db::table('b_pineline_abroad_record')->whereIn('id',$del_ids)->update(['status' => 2]);
                if ($record_res < 0) return Errors::DELETE_RECORD_ERROR;
            }
            unset($data['del_records']);
            //样本记录--修改和添加处理
            if(Helper::lsWhere($data,'record')){
                $pine_record = $data['record'];
                foreach ($pine_record as $key => $val) {
                    // 取样部位
                    switch ($val['sampling_part'])
                    {
                        case "1":$val['sampling_part_name'] = "上";
                            break;
                        case "2":$val['sampling_part_name'] = "中";
                            break;
                        case "3":$val['sampling_part_name'] = "下";
                            break;
                    }
                    // 送检结果，1表示无线虫、2表示松材线虫、3表示拟松材线虫、4表示其他线虫
                    switch ($val['results'])
                    {
                        case "1":$val['results_name'] = "无线虫";
                            break;
                        case "2":$val['results_name'] = "松材线虫";
                            break;
                        case "3":$val['results_name'] = "拟松材线虫";
                            break;
                        case "4":$val['results_name'] = "其他线虫";
                            break;
                    }
                    if(isset($val['id']) && $val['id'] != 0 ){
                        unset($val['check']);
                        $val['update_time'] = $data['update_time'];
                        $dbRes = Db::table('b_pineline_abroad_record')->update($val);
                        if (empty($dbRes)) return Errors::UPDATE_RECORD_ERROR;
                    }else{
                        unset($val['check']);
                        unset($val['id']);
                        $val['abroad_id'] = $data['id'];
                        $val['create_time'] = $data['update_time'];
                        $val['update_time'] = $data['update_time'];
                        $val['status'] = 1;
                        $record_dbRes = Db::table('b_pineline_abroad_record')->insertGetId($val);
                    }
                }
            }
            unset($data['sampling_part']);
            unset($data['record']);
            //修改
            $res = Db::table('b_pineline_abroad')->field('region, region_name, positions, position_type,location_name, class_number, forest_class_area, forest_composition, main_tree,
            number_of_plants, forest_age, dbh, tree_height, accumulative_volume, slope_direction, canopy_density, vegetation_type, dead_pine_num,
            dead_rate, dead_area, dead_reason,adder,report_name, update_time')->update($data); 
            Db::commit();
            return $res == 1 ? [true , $res] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

	//疫情发展趋势图
    static function trendChart($data){
        try {
            $res = [];
            $dbRes = PinePestDb::content($data,true);
            if(empty($dbRes)) return Errors::DATA_NOT_FIND;
            //总数统计
            $total_res = PinePestDb::content($data);
            $region_name = BaseDb::areaName($data['region']);
            $res['data'] = $dbRes;
            $res['title'] = $region_name.''.$data['start_time'].'年'.'到'.$data['end_time'].'年'.'松材线虫总共调查面积'.$total_res['total_class_area'].'亩,'.'枯死株数总数'.$total_res['total_dead_num'].'株,'.'受灾市'.$total_res['total_city_num'].'个';
            return empty($res) ? Errors::DATA_NOT_FIND : [true, $res];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function content($data,$total = false){
        try {
            $a = 'year(create_time) year,SUM(forest_class_area) forest_class_area,SUM(dead_pine_num) dead_pine_num,count(distinct(region)) city_num';
            $b = 'SUM(forest_class_area) total_class_area,SUM(dead_pine_num) total_dead_num,count(distinct(region)) total_city_num';
            $query = Db::table('b_pineline_abroad')
                ->whereLike('region', $data['region'].'%')
                ->where('status',1)
                ->where("YEAR(create_time) >='".$data['start_time']."'" )
                ->where("YEAR(create_time) <='".$data['end_time']."'" )
                ->where('LENGTH(region) = 4');
            if($total){
                $db = $query->field($a)->group('year')->select();
            }else{
                $db = $query->field($b)->find();
            }
            return $db;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //全选删除
    static function deleteChecked($ids){
        try {
            $dataRes = Db::table('b_pineline_abroad')->whereIn('id', $ids)->update(['status'=> 2]);
            return empty($dataRes) ? Errors::DELETE_ERROR : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 数据导出
    static function exportls($data,$field,$img,$record,$condition){
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            $field.=',id';
            if(!empty($condition['region'])) $where.=" and region like '%".$condition['region']."%'";
            if(!empty($condition['surveyer'])) $where.=" and report_name like '%".$condition['surveyer']."%'";
            if(!empty($condition['start_time'])) $where.=" and DATE(create_time) >='".$condition['start_time']."'";
            if(!empty($condition['end_time'])) $where.=" and DATE(create_time) <='".$condition['end_time']."'";
            $dataRes = Db::table('b_pineline_abroad')->field($field)->where($where)->order($order)->select();
                // 获取图片
                if ($img){
                foreach ($dataRes as $key => $val) {
                    $dataRes[$key]['img'] = Db::table('b_pineline_abroad_image')->where('record_id', $val['id'])->where('status',1)->field('path')->select();
                }
            }
            // 获取记录
            if ($record != false){
                foreach ($dataRes as $key => $val) {
                    $result = Db::table('b_pineline_abroad_record')->where('abroad_id', $val['id'])->where('status',1)->field($record)->select();
                    if (!empty($result)){
                        foreach ($result as $ky => $vl) {
                            $val['record'][$ky] = array_values($vl);
                        }
                        $dataRes[$key] = $val;
                    }else{
                        $dataRes[$key]['record'] = [];
                    }
                }
            }
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
}