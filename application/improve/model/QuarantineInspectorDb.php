<?php
/**
 * Created by PhpStorm.
 * User: XieLe
 * Date: 2018/3/10
 * Time: 11:24
 *
 */

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;
/**
 * 追加调用 Communal 工具类
 */
use tool\Communal;
use tool\Error;

class QuarantineInspectorDb extends BaseDb
{
    /**
     * 修改人：余思渡
     * 修改时间:2019.03.07
     * 修改内容:将 DB_name  由 b_quarantine_inspector 改为 improve_quarantine_inspector
    */
    static function add($data){
        unset($data['did']);
        try {
            $data['create_time'] =  date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $data['status'] = 1;
            unset($data['id']);
            $result = Db::table('improve_quarantine_inspector')->insertGetId($data);
            return is_numeric($result) ? Communal::success('添加成功') : Error::error('添加失败');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /**
     * 修改人：余思渡
     * 修改时间:2019.03.07
     * 修改内容:将 DB_name  由 b_quarantine_inspector 改为 improve_quarantine_inspector
    */
    static function ls($data){
        try {
            $where = 'status = 1';
            $field = 'id,region,region_name,name,type,unit,job,technical,guard,create_time';
            $order = 'create_time desc';
            if (!empty($data['region'])) $where.=" and region like '%".$data['region']."%'";//此处的搜索逻辑需要重写
            if (!empty($data['type'])) $where.=" and type = ".$data['type'];
            if (!empty($data['name'])) $where.=" and name like '%".$data['name']."%'";
            $dataRes = Db::table('improve_quarantine_inspector')->field($field)->where($where)->order($order)
                ->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            return count($dataRes['data']) !==0 ? Communal::successData($dataRes) : Error::error('未找到数据') ;
            //return [true, $dataRes];
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /**
     * 修改人：余思渡
     * 修改时间:2019.03.08
     * 修改内容:将 DB_name  由 b_quarantine_inspector 改为 improve_quarantine_inspector
    */
    static function query($id)
    {
        try {
            $dbRes = Db::table('improve_quarantine_inspector')
                ->field('update_time,status,adder',true)
                ->where('id', $id)
                ->where('status',1)
                ->find();
            return empty($dbRes) ? Error::error('未找到数据') : Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /**
     * 修改人：余思渡
     * 修改时间:2019.03.08
     * 修改内容:将 DB_name  由 b_quarantine_inspector 改为 improve_quarantine_inspector
    */
    static function edit($data)
    {
        try {
            if (!static::query($data['id'])[0]) return Error::error('未找到数据');
            $data['update_time'] =  date('Y-m-d H:i:s');
            unset($data['create_time']);//  ?????为何修改操作需要移除创建时间????数据格式冲突？？
            $dbRes = Db::table('improve_quarantine_inspector')->field('create_time,status',true)->update($data);
            return empty($dbRes) ? Error::error('修改失败') : Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /**
     * 修改人：余思渡
     * 修改时间:2019.03.08
     * 修改内容:将 DB_name  由 b_quarantine_inspector 改为 improve_quarantine_inspector
     */
    static function deleteChecked($ids){
        try {
            $dataRes = Db::table('improve_quarantine_inspector')->whereIn('id', $ids)->update(['status'=> 2]);
            return empty($dataRes) ? Error::error('删除失败') : Communal::success('删除成功');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    // 检疫员统计
    static function statisticsList($data){
        try {
            //总数统计
            $dataResOne = QuarantineInspectorDb::sumList($data);
            //相关数据
            $dataResTwo = QuarantineInspectorDb::monthList($data);
            $result = array('dataResOne'=>$dataResOne,'dataResTwo'=>$dataResTwo);
            return [true , $result];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 检疫员统计-列表导出
    static function recordExecl($data){
        try {
            //总数统计
            $dataResOne = QuarantineInspectorDb::sumList($data);
            //相关数据
            $dataResTwo = QuarantineInspectorDb::monthListRecord($data);
            $result = array('dataResOne'=>$dataResOne,'dataResTwo'=>$dataResTwo);
            return [true , $result];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

     // 总数统计
     static function sumList($data){
        $db = Db::query("SELECT
        sum(t.zz) total_full, SUM(t.jz) total_part, SUM(t.num) total_num
            FROM ( SELECT SUM(type = 1) zz, SUM(type = 2) jz, COUNT(id) num
            FROM b_quarantine_inspector WHERE `status` = 1
            AND region LIKE "."'".$data['region']."%'"." GROUP BY region
            ) t");
        if(empty($db)){
            $db['total_full'] = 0;
            $db['total_part'] = 0;
            $db['total_num'] = 0;
        }
        return $db;
    }

    // 按月统计
    static function monthList($data){
        $a = 'distinct(region) region,region_name,sum(type = 1) full,sum(type = 2) part,count(id) num';
        $db = Db::table('b_quarantine_inspector')
            ->field($a)
            ->where('status',1)
            ->whereLike('region', $data['region'] . '%')
            ->group('region')->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();  
        return $db;
    }

    // 按月统计--列表导出
    static function monthListRecord($data){
        $a = 'distinct(region) region,region_name,sum(type = 1) full,sum(type = 2) part,count(id) num';
        $db = Db::table('b_quarantine_inspector')
            ->field($a)
            ->where('status',1)
            ->whereLike('region', $data['region'] . '%')
            ->group('region')->select();  
        return $db;
    }

    // 数据导出
    static function exportls($data,$field,$condition){
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            if (!empty($condition['region'])) $where.=" and region like '%".$condition['region']."%'";
            if (!empty($condition['name'])) $where.=" and name like '%".$condition['name']."%'";
            if (!empty($condition['type'])) $where.=" and type = ".$condition['type'];
            $dataRes = Db::table('b_quarantine_inspector')->field($field)->where($where)->order($order)->select();
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
}