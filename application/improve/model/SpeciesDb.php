<?php
/**
 * Created by PhpStorm.
 * User: XieLe
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;
use think\Exception;

class SpeciesTypeDb  extends BaseDb
{
    static function add($data){
        try {
            $data['create_time'] =  date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $data['status'] = 1;
            unset($data['id']);
            $result = Db::table('b_item')->insertGetId($data);
            return is_numeric($result) ? [true ,$result] : Errors::ADD_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function ls($data,$sample = false){
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            if (!empty($data['region'])) $where.=" and region like '%".$data['region']."%'";
            if (!empty($data['name'])) $where.=" and name like '%".$data['name']."%'";
            if (!empty($data['person'])) $where.=" and person like '%".$data['person']."%'";
            if (!empty($data['begin_time'])) $where.=" and begin_time >='".$data['begin_time']."'";
            if (!empty($data['end_time'])) $where.=" and end_time <='".$data['end_time']."'";
            if ($sample) {
                $field = 'id,name,unit,person,nature,begin_time,end_time,region_name,positions,location_name';
            } else {
                $field = 'id,name,unit,person,nature,begin_time,end_time,region_name';
            }
            $dataRes = Db::table('b_item')->field($field)->where($where)->order($order)
                ->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
    
    static function query($id){
        try {
            $dbRes = Db::table('b_item')->field('create_time,update_time,status,adder',true)->where('id', $id)->where('status',1)->find();         
            return empty($dbRes) ? Errors::DATA_NOT_FIND :[true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function edit($data){
        try {
            if (!static::query($data['id'])[0]) return Errors::DATA_NOT_FIND;
            $data['update_time'] =  date('Y-m-d H:i:s');
            unset($data['create_time']);
            $dbRes = Db::table('b_item')->field('create_time,status',true)->update($data);
            return $dbRes == 1 ? [true, $dbRes] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function deleteChecked($ids){
        try {
            $dataRes = Db::table('b_item')->whereIn('id', $ids)->update(['status'=> 2]);
            return empty($dataRes) ? Errors::DELETE_ERROR : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 数据导出
    static function exportls($data,$field){
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            if (!empty($data['region'])) $where.=" and region like '%".$data['region']."%'";
            // if (!empty($data['name'])) $where.=" and name like '%".$data['name']."%'";
            // if (!empty($data['person'])) $where.=" and person like '%".$data['person']."%'";
            // if (!empty($data['begin_time'])) $where.=" and begin_time >='".$data['begin_time']."'";
            // if (!empty($data['end_time'])) $where.=" and end_time <='".$data['end_time']."'";
            $dataRes = Db::table('b_item')->field($field)->where($where)->order($order)->select();
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
}