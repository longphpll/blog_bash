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

class ImplementDb extends BaseDb
{
    /*已改 Lxl*/
    static function add($data)
    {
        try {
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $data['status']      = 1;
            unset($data['id']);

            $result = Db::table('improve_implement')->insertGetId($data);

            return is_numeric($result) ? Communal::success('添加成功,记录id为：' . $result) : Error::error('添加错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //列表
    static function ls($data, $sample = false)
    {
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            if (!empty($data['region'])) $where .= " and region like '%" . $data['region'] . "%'";
            if (!empty($data['name'])) $where .= " and name like '%" . $data['name'] . "%'";
            if (!empty($data['person'])) $where .= " and person like '%" . $data['person'] . "%'";
            if ($sample) {
                $field = 'id,name,unit,person,nature,region_name,positions,location_name';
            } else {
                $field = 'id,name,unit,person,nature,region_name';
            }
            $dataRes = Db::table('improve_implement')->field($field)->where($where)->order($order)
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
            $dbRes = Db::table('improve_implement')->field('create_time,update_time,status,adder', true)->where('id', $id)->where('status', 1)->find();
            return empty($dbRes) ? Error::error('未找到相应数据') : Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //编辑
    static function edit($data)
    {
        try {
            if (!static::query($data['id'])[0]) return Error::error('未找到相应数据');
            $data['update_time'] = date('Y-m-d H:i:s');
            unset($data['create_time']);
            $dbRes = Db::table('improve_implement')->field('create_time,status', true)->update($data);
            return $dbRes == 1 ? Communal::success('编辑信息成功') : Error::error('修改错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //删除
    static function deleteChecked($ids)
    {
        try {
            $dataRes = Db::table('improve_implement')->whereIn('id', $ids)->update(['status' => 2]);
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
            if (!empty($condition['name'])) $where .= " and name like '%" . $condition['name'] . "%'";
            if (!empty($condition['person'])) $where .= " and person like '%" . $condition['person'] . "%'";
            $dataRes = Db::table('improve_implement')->field($field)->where($where)->order($order)->select();
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
}