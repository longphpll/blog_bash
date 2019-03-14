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

/**
 * 人员管理
 */
class PersonnelDb extends BaseDb
{
    /*已改 Lxl*/
    static function add($data)
    {
        try {
            $data['status'] = 1;
            //设置创建时间
            $data['create_time'] = date('Y-m-d H:i:s');
            //设置初始修改时间为创建时间
            $data['update_time'] = $data['create_time'];
            //销毁变量id，防止数据库出现重复id
            unset($data['id']);

            //插入记录并获得新增id
            $result = Db::table('improve_personnel')->insertGetId($data);

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
            $where = 'status = 1';
            $field = 'id,name,unit,job,sex,entryday,tel,guard';
            $order = 'create_time desc';
            if (!empty($data['name'])) $where .= " and name like '%" . $data['name'] . "%'";
            if (!empty($data['unit'])) $where .= " and unit like '%" . $data['unit'] . "%'";
            if (!empty($data['guard'])) $where .= " and guard = " . $data['guard'];

            $result = Db::table('improve_personnel')->field($field)->where($where)->order($order)->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();

            return Communal::successData($result);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /*已改*/
    static function query($id)
    {
        try {
            $dbRes = Db::table('improve_personnel')
                ->where('id', $id)
                ->where('status', 1)
                ->field('status,update_time,adder', true)->find();

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

            $dbRes = Db::table('improve_personnel')->field('name,job,tel,sex,birthday,unit,technical,
                education,academy,entryday,guard,update_time')->update($data);

            return $dbRes == 1 ? Communal::success('编辑信息成功') : Error::error('修改错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    static function delete($ids)
    {
        try {
            $dataRes = Db::table('improve_personnel')->whereIn('id', $ids)->update(['status' => 2]);

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
            if (!empty($condition['name'])) $where .= " and name like '%" . $condition['name'] . "%'";
            if (!empty($condition['unit'])) $where .= " and unit like '%" . $condition['unit'] . "%'";
            if (!empty($condition['guard'])) $where .= " and guard = " . $condition['guard'];

            $dataRes = Db::table('improve_personnel')->field($field)->where($where)->order($order)->select();

            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
}