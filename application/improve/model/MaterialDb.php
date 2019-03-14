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

class MaterialDb extends BaseDb
{
    /*已改 Lxl*/
    static function add($data)
    {
        try {
            //设置创建时间
            $data['create_time'] = date('Y-m-d H:i:s');
            //设置初始修改时间为创建时间
            $data['update_time'] = $data['create_time'];
            $data['status']      = 1;
            //销毁变量id，防止数据库出现重复id
            unset($data['id']);
            //插入记录并获得新增id

            $result = Db::table('improve_material')->insertGetId($data);

            //判断id是否为数字，如果是返回id，如果不是返回ERROR
            return is_numeric($result) ? Communal::success('添加成功,记录id为：' . $result) : Error::error('添加错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    static function ls($data)
    {
        try {
            $where = 'm.status = 1';
            $field = 'm.id,m.region,m.region_name,m.unit,m.name,m.amount,m.price,u.name adder,m.create_time';
            $order = 'm.create_time desc';
            if (!empty($data['region'])) $where .= " and m.region like '%" . $data['region'] . "%'";
            if (!empty($data['unit'])) $where .= " and m.unit like '%" . $data['unit'] . "%'";
            if (!empty($data['name'])) $where .= " and m.name like '%" . $data['name'] . "%'";
            $dataRes = Db::table('improve_material')->alias('m')->join('frame_base_staff u', 'u.uid = m.adder', 'left')->field($field)->where($where)->order($order)
                ->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();

            return is_array($dataRes) ? Communal::successData($dataRes) : Error::error('未找到相应数据');

        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    static function query($id)
    {
        try {
            $dbRes = Db::table('improve_material')->alias('m')
                ->join('frame_base_staff u', 'u.uid = m.adder', 'left')
                ->where('m.id', $id)
                ->where('m.status', 1)
                ->field('m.id,m.region,m.region_name,m.unit,m.name,m.version,m.measure,m.amount,m.price,u.name adder,m.create_time')
                ->find();

            if (empty($dbRes)) return Error::error('未找到相应数据');

            return is_array($dbRes) ? Communal::successData($dbRes) : Error::error('未找到相应数据');
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

            $dbRes = Db::table('improve_material')->field('status,create_time', true)->update($data);

            return $dbRes == 1 ? Communal::success('编辑信息成功') : Error::error('修改错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    static function deleteChecked($ids)
    {
        try {
            $dataRes = Db::table('improve_material')->whereIn('id', $ids)->update(['status' => 2]);

            return empty($dataRes) ? Error::error('删除错误') : Communal::success('删除成功');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
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
            if (!empty($condition['unit'])) $where .= " and unit like '%" . $condition['unit'] . "%'";
            if (!empty($condition['name'])) $where .= " and name like '%" . $condition['name'] . "%'";
            $dataRes = Db::table('improve_material')->field($field)->where($where)->order($order)->select();

            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
}