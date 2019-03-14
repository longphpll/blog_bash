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

use tool\Error;
use tool\Communal;

/*
 * 应急管理
 */

class EmergencyDb extends BaseDb
{
    /*已改 Lxl*/
    static function add($data)
    {
        try {
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $data['status']      = 1;
            unset($data['id']);
            $result = Db::table('improve_emergency')->insertGetId($data);
            return is_numeric($result) ? Communal::success('添加成功,记录id为：' . $result) : Error::error('添加错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    static function ls($data)
    {
        try {
            $where = 'status = 1';
            $field = 'id,ename,region,region_name,level,begintime,overtime,beginunit,name,tel';
            $order = 'create_time desc';
            if (!empty($data['region'])) $where .= " and region like '%" . $data['region'] . "%'";
            if (!empty($data['ename'])) $where .= " and ename like '%" . $data['ename'] . "%'";
            $dataRes = Db::table('improve_emergency')->field($field)->where($where)->order($order)
                ->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //详情
    static function query($id)
    {
        try {
            $dbRes = Db::table('improve_emergency')
                ->where('id', $id)
                ->where('status', 1)
                ->field('update_time,status,adder', true)
                ->find();
            if (empty($dbRes)) return Error::error('未找到相应数据');
            //将数组中的null值转化成''
            $result = Communal::removeEmpty($dbRes);
            return is_array($result) ? Communal::successData($result) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    static function edit($data)
    {
        try {
            if (!static::query($data['id'])[0]) return Error::error('未找到相应数据');
            $data['update_time'] = date('Y-m-d H:i:s');
            unset($data['create_time']);
            $dbRes = Db::table('improve_emergency')
                ->field('create_time,status,adder', true)->update($data);
            return $dbRes == 1 ? Communal::success('编辑信息成功') : Error::error('修改错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    static function deleteChecked($ids)
    {
        try {
            $dataRes = Db::table('improve_emergency')->whereIn('id', $ids)->update(['status' => 2]);
            return empty($dataRes) ? Error::error('删除错误') : Communal::success('删除成功');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    // 已有事件查询
    static function eventls($data)
    {
        try {
            $where = 'status=1';
            if (!empty($data['region'])) $where .= " and region like '%" . $data['region'] . "%'";
            if (!empty($data['ename'])) $where .= " and ename like '%" . $data['ename'] . "%'";
            $result = Db::table('improve_emergency')->where($where)->field('distinct ename')->select();
            return is_array($result) ? Communal::successData($result) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /*已改*/
    // 数据导出
    static function exportls($data, $field, $condition)
    {
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            if (!empty($condition['region'])) $where .= " and region like '%" . $condition['region'] . "%'";
            if (!empty($condition['name'])) $where .= " and ename like '%" . $condition['name'] . "%'";
            $dataRes = Db::table('improve_emergency')->field($field)->where($where)->order($order)->select();
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
}