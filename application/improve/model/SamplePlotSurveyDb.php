<?php
/**
 * Created by qiu.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 19:39
 */

namespace app\improve\model;

use think\Db;
use app\improve\controller\Helper;
use app\improve\controller\Errors;

/*
 * 固定标准地调查
 */
class SamplePlotSurveyDb extends BaseDb
{
    // 添加记录
    static function add($data)
    {
        try {
            $data['status'] = 1;
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            if (Helper::lsWhere($data, 'images')){
                $images = $data['images'];
            }
            unset($data['images']);
            if (Helper::lsWhere($data, 'record')){
                $record = $data['record'];
            }
            unset($data['record']);
             //虫害类型
            if ($data['hazard_type'] == '1'){
                $field = 'sample_plot_number,hazard_type,small_place_name,altitude,canopy_density,strain_rate,create_time,update_time,adder,report_name,status';
                $table = 'b_insect_record';
            };
            //病害类型
            if ($data['hazard_type'] == '2'){
                $field = 'sample_plot_number,hazard_type,small_place_name,average_dbh,average_tree_height,canopy_density,growth_trend,terrain,
                happen_area,harm_level,distribution,create_time,update_time,adder,report_name,status';
                $table = 'b_disease_record';
            };
            //有害植物类型
            if ($data['hazard_type'] == '3'){
                $field = 'sample_plot_number,hazard_type,small_place_name,average_dbh,canopy_density,growth_trend,terrain,happen_area,harm_level,
                distribution,create_time,update_time,adder,report_name,status';
                $table = 'b_plants_record';
            };
            Db::startTrans();
            //插入数据
            $dbRes = Db::table('b_sample_plot_survey')->field($field)->insertGetId($data);
            if ($dbRes <= 0) return Errors::ADD_ERROR;
            //插入记录数据
            if (!empty($record)) {
                foreach ($record as $key => $val) {
                    if(isset($val['id']) && $val['id'] == 0 ){
                        unset($val['check']);
                        unset($val['id']);
                        if ($data['hazard_type'] == '1'){
                            switch ($val['degree_level'])
                            {
                                case "1":$val['degree_level_name'] = "轻";
                                    break;
                                case "2":$val['degree_level_name'] = "中";
                                    break;
                                case "3":$val['degree_level_name'] = "重";
                                    break;
                            }
                        }
                        if ($data['hazard_type'] == '2'){
                            switch ($val['harmful_part'])
                            {
                                case "1":$val['harmful_part_name'] = "叶部";
                                    break;
                                case "2":$val['harmful_part_name'] = "干部";
                                    break;
                                case "3":$val['harmful_part_name'] = "枝梢部";
                                    break;
                                case "4":$val['harmful_part_name'] = "根部";
                                    break;
                                case "5":$val['harmful_part_name'] = "种实";
                                    break;
                            }
                            switch ($val['disease_grade'])
                            {
                                case "0":$val['disease_grade_name'] = "零级";
                                    break;
                                case "1":$val['disease_grade_name'] = "一级";
                                    break;
                                case "2":$val['disease_grade_name'] = "二级";
                                    break;
                                case "3":$val['disease_grade_name'] = "三级";
                                    break;
                                case "4":$val['disease_grade_name'] = "四级";
                                    break;
                            }
                        }  
                        if ($data['hazard_type'] == '3'){
                            switch ($val['growth_influence'])
                            {
                                case "1":$val['growth_influence_name'] = "构成影响";
                                    break;
                                case "2":$val['growth_influence_name'] = "明显影响";
                                    break;
                                case "3":$val['growth_influence_name'] = "严重影响";
                                    break;
                            }
                            switch ($val['update_influence'])
                            {
                                case "1":$val['update_influence_name'] = "构成影响";
                                    break;
                                case "2":$val['update_influence_name'] = "明显影响";
                                    break;
                                case "3":$val['update_influence_name'] = "严重影响";
                                    break;
                            }
                        }
                        $val['sv_id'] = $dbRes;
                        $val['create_time'] = $data['create_time'];
                        $val['update_time'] = $data['update_time'];
                        $val['status'] = 1;
                        $res = Db::table($table)->insertGetId($val);
                        if ($res <= 0) return Errors::ADD_RECORD_ERROR;
                    }
                }    
            }
            //图片上传
            if (!empty($images)) {
                $path = Db::table('b_uploads')->whereIn('id',$images)->field('path')->select();
                if (empty($path)) return Errors::HAS_NO_FILE;
                foreach ($path as $val) {
                    $img_data = [
                        'sv_id' => $dbRes,
                        'path' => $val['path'],
                        'create_time' => $data['create_time'],
                        'update_time' => $data['update_time'],
                        'status' => 1
                    ];
                    $a = Db::table('b_survey_image')->insert($img_data);
                    if ($a <= 0) return Errors::IMAGES_INSERT_ERROR;
                }
            }
            Db::commit();
            return [true, $dbRes];
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    // 根据id查看
    static function query($data)
    {
        try {
            $dv = Db::table('b_sample_plot_survey')->where('id', $data['id'])->field('hazard_type')->find();
            if (empty($dv)) return Errors::TYPE_ERROR;
             //虫害类型
			if ($dv['hazard_type'] == '1'){
                $field = 'sps.id,reg.id sample_plot_number,reg.number,sps.hazard_type,sps.small_place_name,sps.altitude,sps.canopy_density,sps.strain_rate,u.name adder,u.tel,sps.create_time';
                $table = 'b_insect_record';
            }
            //病害类型
			if ($dv['hazard_type'] == '2'){ 
                $field = 'sps.id,reg.id sample_plot_number,reg.number,sps.hazard_type,sps.small_place_name,sps.average_dbh,sps.average_tree_height,sps.canopy_density,
                sps.growth_trend,sps.terrain,sps.happen_area,sps.harm_level,sps.distribution,u.name adder,u.tel,sps.create_time';
                $table = 'b_disease_record';
            }
            //有害植物类型
            if ($dv['hazard_type'] == '3'){
                $field = 'sps.id,reg.id sample_plot_number,reg.number,sps.hazard_type,sps.small_place_name,sps.average_dbh,sps.canopy_density,
                sps.growth_trend,sps.terrain,sps.happen_area,sps.harm_level,sps.distribution,u.name adder,u.tel,sps.create_time';
                $table = 'b_plants_record';
            }
            $dbRes = Db::table('b_sample_plot_survey')->alias('sps')->where('sps.id', $data['id'])
                ->join('b_regularly reg', 'reg.id = sps.sample_plot_number', 'left')
                ->join('u_user u', 'u.uid = sps.adder', 'left')
                ->field($field)->find();
            if (empty($dbRes)) return Errors::DATA_NOT_FIND;
            $dbRes['record'] = Db::table($table)->field('create_time,update_time,status,sv_id',true)->where('sv_id', $data['id'])->where('status',1)->select();
            $dbRes['images'] = Db::table('b_survey_image')->field('id,path')->where('sv_id', $data['id'])->where('status',1)->select();
            return [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //列表
    static function ls($data,$sample = false){
        try {
            $where = 'sps.status = 1';
            $order = 'sps.update_time desc';
            if(!empty($data['type'])) $where.=" and sps.hazard_type = ".$data['type'];
            if(!empty($data['number'])) $where.=" and sps.sample_plot_number like '".$data['number']."%'";
            if(!empty($data['surveyer'])) $where.=" and u.name like '%".$data['surveyer']."%'";
            if(!empty($data['start_time'])) $where.=" and DATE(sps.update_time) >='".$data['start_time']."'";
            if(!empty($data['end_time'])) $where.=" and DATE(sps.update_time) <='".$data['end_time']."'";
            if(!empty($data['tel'])) $where.=" and u.tel = ".$data['tel'];
            if ($sample) {
                $field = 'sps.id';
            } else {
                $field = 'sps.id,sps.sample_plot_number number,sps.hazard_type,sps.small_place_name,u.name adder,sps.adder uid,u.tel,sps.update_time';
                $result = Db::table('b_sample_plot_survey')->alias('sps')
                ->join('b_regularly reg', 'reg.id = sps.sample_plot_number', 'left')
                ->join('u_user u', 'u.uid = sps.adder', 'left')
                ->field($field)->where($where)->order($order)
                    ->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            }
            return [true, $result];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //列表
    static function appls($data,$sample = false){
        try {
            $where = 'sps.status = 1';
            $order = 'sps.update_time desc';
            if(!empty($data['type'])) $where.=" and sps.hazard_type = ".$data['type'];
            if(!empty($data['number'])) $where.=" and sps.sample_plot_number like '".$data['number']."%'";
            if(!empty($data['surveyer'])) $where.=" and u.name like '%".$data['surveyer']."%'";
            if(!empty($data['start_time'])) $where.=" and DATE(sps.update_time) >='".$data['start_time']."'";
            if(!empty($data['end_time'])) $where.=" and DATE(sps.update_time) <='".$data['end_time']."'";
            if(!empty($data['tel'])) $where.=" and u.tel = ".$data['tel'];
            if ($sample) {
                $field = 'sps.id';
            } else {
                $field = 'sps.id,sps.sample_plot_number number,sps.hazard_type,sps.small_place_name,u.name adder,sps.adder uid,u.tel,sps.update_time';
                $result = Db::table('b_sample_plot_survey')->alias('sps')
                ->join('b_regularly reg', 'reg.id = sps.sample_plot_number', 'left')
                ->join('u_user u', 'u.uid = sps.adder', 'left')
                ->field($field)->where($where)->order($order)
                ->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            }
            return [true, $result];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //删除
    static function deleteChecked($ids){
        try {
            $dataRes = Db::table('b_sample_plot_survey')->whereIn('id', $ids)->update(['status'=> 2]);
            return empty($dataRes) ? Errors::DELETE_ERROR : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 编辑
    static function edit($data){
        try {
            if (!static::query($data['id'])) return Errors::DATA_NOT_FIND;
            $dv = Db::table('b_sample_plot_survey')->where('id', $data['id'])->field('hazard_type')->find();
            if (empty($dv)) return Errors::TYPE_ERROR;
             //虫害类型
            if ($data['hazard_type'] == '1'){
                $field = 'hazard_type,small_place_name,altitude,canopy_density,strain_rate,update_time,adder,report_name';
                $table = 'b_insect_record';
            };
            //病害类型
            if ($data['hazard_type'] == '2'){
                $field = 'hazard_type,small_place_name,average_dbh,average_tree_height,canopy_density,growth_trend,terrain,
                happen_area,harm_level,distribution,update_time,adder,report_name';
                $table = 'b_disease_record';
            };
            //有害植物类型
            if ($data['hazard_type'] == '3'){
                $field = 'hazard_type,small_place_name,average_dbh,canopy_density,growth_trend,terrain,happen_area,harm_level,
                distribution,update_time,adder,report_name';
                $table = 'b_plants_record';
            };
            $paths = [];
            $data['update_time'] = date('Y-m-d H:i:s');
            Db::startTrans();
            //图片上传与修改处理
            if (Helper::lsWhere($data,'images')) {
                $images = $data['images'];
                unset($data['images']);
                $path = Db::table('b_uploads')->whereIn('id',$images)->field('path')->select();
                if (empty($path)) return Errors::HAS_NO_FILE;
                foreach ($path as $val) {
                    $record = [
                        'sv_id' => $data['id'],
                        'path' => $val['path'],
                        'create_time' => $data['update_time'],
                        'update_time' => $data['update_time'],
                        'status' => 1
                    ];
                    $a = Db::table('b_survey_image')->insert($record);
                    if ($a <= 0) return Errors::IMAGES_INSERT_ERROR;
                }
            }
            //图片删除处理
            if (Helper::lsWhere($data, 'del_images')) {
                $del_images = explode(',',$data['del_images']);
                $paths = Db::table('b_survey_image')->field('path')->where('sv_id', $data['id'])->whereIn('id', $del_images)->select();
                if (count($paths) !== count($del_images)) return Errors::NO_IMAGES_DELETED;
                $delRes = Db::table('b_survey_image')->whereIn('id',$del_images)->update(['status' => 2]);
                if ($delRes !== count($del_images)) return Errors::DELETE_ERROR;
            }
            unset($data['del_images']);
            //样本记录--删除处理
            if(Helper::lsWhere($data,'del_records')){
                $del_ids = explode(',',$data['del_records']);
                $record_res = Db::table($table)->whereIn('id',$del_ids)->update(['status' => 2]);
                if ($record_res <= 0) return Errors::DELETE_RECORD_ERROR;
            }
            unset($data['del_records']);
            //样本记录--修改和添加处理
            if(Helper::lsWhere($data,'record')){
                $survey_record = $data['record'];
                foreach ($survey_record as $key => $val) {
                    if ($data['hazard_type'] == '1'){
                        switch ($val['degree_level'])
                        {
                            case "1":$val['degree_level_name'] = "轻";
                                break;
                            case "2":$val['degree_level_name'] = "中";
                                break;
                            case "3":$val['degree_level_name'] = "重";
                                break;
                        }
                    }
                    if ($data['hazard_type'] == '2'){
                        switch ($val['harmful_part'])
                        {
                            case "1":$val['harmful_part_name'] = "叶部";
                                break;
                            case "2":$val['harmful_part_name'] = "干部";
                                break;
                            case "3":$val['harmful_part_name'] = "枝梢部";
                                break;
                            case "4":$val['harmful_part_name'] = "根部";
                                break;
                            case "5":$val['harmful_part_name'] = "种实";
                                break;
                        }
                        switch ($val['disease_grade'])
                        {
                            case "0":$val['disease_grade_name'] = "零级";
                                break;
                            case "1":$val['disease_grade_name'] = "一级";
                                break;
                            case "2":$val['disease_grade_name'] = "二级";
                                break;
                            case "3":$val['disease_grade_name'] = "三级";
                                break;
                            case "4":$val['disease_grade_name'] = "四级";
                                break;
                        }
                    }  
                    if ($data['hazard_type'] == '3'){
                        switch ($val['growth_influence'])
                        {
                            case "1":$val['growth_influence_name'] = "构成影响";
                                break;
                            case "2":$val['growth_influence_name'] = "明显影响";
                                break;
                            case "3":$val['growth_influence_name'] = "严重影响";
                                break;
                        }
                        switch ($val['update_influence'])
                        {
                            case "1":$val['update_influence_name'] = "构成影响";
                                break;
                            case "2":$val['update_influence_name'] = "明显影响";
                                break;
                            case "3":$val['update_influence_name'] = "严重影响";
                                break;
                        }
                    }
                    //修改操作
                    if(isset($val['id']) && $val['id'] != 0 ){
                        unset($val['check']);
                        $val['update_time'] = $data['update_time'];
                        $dbRes = Db::table($table)->update($val);
                        if ($dbRes <= 0) return Errors::UPDATE_RECORD_ERROR;
                    }else{
                        if(isset($val['tense']) && !empty($val['tense'])){
                            $tense = $val['tense'];
                        }
                        unset($val['check']);
                        unset($val['id']);
                        $val['sv_id'] = $data['id'];
                        $val['create_time'] = $data['update_time'];
                        $val['update_time'] = $data['update_time'];
                        $val['status'] = 1;
                        $record_dbRes = Db::table($table)->insertGetId($val);
                    }     
                }
            }
            unset($data['record']);
            $dbRes = Db::table('b_sample_plot_survey')->field("create_time,status",true)->update($data);
            Db::commit();
            return $dbRes == 1 ? [true , $dbRes] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    //根据类型查询固定标准地信息
     static function info($data){
        try {
            $where = 'status = 1';
            $order = 'update_time desc';
            $field = 'id value,number label';
            if (!empty($data['region'])) $where.=" and region like '%".$data['region']."%'";
            if (!empty($data['name'])) $where.=" and number like '%".$data['name']."%'";
            if (!empty($data['type'])) $where.=" and type = ".$data['type'];
            $dbRes = Db::table('b_regularly')->field($field)->where($where)->order($order)->select();
            return empty($dbRes) ? Errors::DATA_NOT_FIND : [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 数据导出
    static function exportls($data,$field,$type,$img,$record,$table,$condition){
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            $field.=',id';
            if(!empty($type)) $where.=" and hazard_type = ".$type;
            if(!empty($condition['number'])) $where.=" and sample_plot_number like '".$condition['number']."%'";
            if(!empty($condition['surveyer'])) $where.=" and report_name like '%".$condition['surveyer']."%'";
            if(!empty($condition['start_time'])) $where.=" and DATE(update_time) >='".$condition['start_time']."'";
            if(!empty($condition['end_time'])) $where.=" and DATE(update_time) <='".$condition['end_time']."'";
            $dataRes = Db::table('b_sample_plot_survey')->field($field)->where($where)->order($order)->select();
            // 获取图片
            if ($img){
                foreach ($dataRes as $key => $val) {
                    $dataRes[$key]['img'] = Db::table('b_survey_image')->where('sv_id', $val['id'])->where('status',1)->field('path')->select();
                }
            }
            // 获取记录
            if ($record != false){
                foreach ($dataRes as $key => $val) {
                    $result = Db::table($table)->where('sv_id', $val['id'])->where('status',1)->field($record)->select();
                    if (!empty($result)){
                        foreach ($result as $ky => $vl) {
                            $val['record'][$ky] = array_values($vl);
                        }
                        $dataRes [$key] = $val;
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