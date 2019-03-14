<?php
/**
 * Created by PhpStorm.
 * User: qiumu
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\model;


use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;

use tool\Error;
use tool\Communal;

/*预测预报*/

class ForecastDb extends BaseDb
{
    /*已改 Lxl*/
    static function add($data)
    {
        try {
            //设置创建时间
            $data['create_time'] = date('Y-m-d H:i:s');
            //设置初始修改时间为创建时间
            $data['update_time'] = $data['create_time'];
            $res                 = Db::table('improve_species')->where('id', $data['pest'])->field('cn_name')->find();
            $data['pest_name']   = $res['cn_name'];
            $data['status']      = 1;
            //销毁变量id，防止数据库出现重复id
            unset($data['id']);
            //插入记录并获得新增id
            $result = Db::table('improve_forecast')->insertGetId($data);
            //判断id是否为数字，如果是返回id，如果不是返回ERROR
            return is_numeric($result) ? Communal::success('添加成功,记录id为：' . $result) : Error::error('添加错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //列表
    static function ls($data)
    {
        try {
            $query = Db::table('improve_forecast')->alias('bf')
                ->where('bf.status', 1)
                ->join('frame_base_staff u', 'u.uid = bf.report', 'left')
                ->join('improve_plant pl', 'pl.id = bf.plant', 'left')
                ->field('bf.id, bf.region, bf.region_name,bf.positions,bf.position_type,bf.location_name,bf.pest,bf.plant,bf.pest_name, pl.cn_name plant_name, bf.generation, bf.parasitism_area,bf.mild_area,bf.moderate_area,bf.severe_area,bf.happen_area, bf.disaster_area, bf.begin_time, bf.end_time, u.name adder, u.cellphone,bf.report uid');
            if (Helper::lsWhere($data, 'object')) $query->whereLike('pest_name', '%' . $data['object'] . '%');
            if (Helper::lsWhere($data, 'region')) $query->whereLike('bf.region', $data['region'] . '%');
            if (Helper::lsWhere($data, 'tel')) $query->where('u.cellphone', $data['tel']);

            $query->order('bf.create_time', 'desc');
            $dataRes = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            $result  = Communal::removeEmpty($dataRes);
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //详情
    static function query($id)
    {
        try {
            $dbRes = Db::table('improve_forecast')->alias('bf')
                ->where('bf.id', $id)
                ->where('bf.status', '1')
                ->join('frame_base_staff u', 'u.uid = bf.report', 'left')
                ->join('improve_plant pl', 'pl.id = bf.plant', 'left')
                ->field('bf.id,bf.region,bf.region_name,bf.positions,bf.position_type,bf.location_name,bf.pest,bf.pest_name,bf.plant,pl.cn_name plant_name,bf.generation,
                    bf.parasitism_area,bf.begin_time,bf.end_time,bf.mild_area,bf.moderate_area,bf.severe_area,bf.happen_area,bf.disaster_area,u.name adder,u.cellphone')
                ->find();
            if (!$dbRes) return Error::error('未找到相应数据');
            $result = Communal::removeEmpty($dbRes);
            return is_array($result) ? Communal::successData($result) : Error::error('未找到相应数据');
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

            //用户 rid
            $rid = session('staff')['rid'];
            if (empty($rid)) return Error::error('未找到相应数据');
            if ($rid == 2) { //如果是管理员
                if (session('staff')['uid'] != $data['report']) return Error::error('没有权限');
            } elseif ($rid == 1) { //如果是超级管理员
                if (strpos($data['region'], session('staff')['region']) === false) {
                    return Error::error('不可修改其他区域用户');
                }
            }

            $data['update_time'] = date('Y-m-d H:i:s');
            $res                 = Db::table('improve_species')->where('id', $data['pest'])->field('cn_name')->find();
            $data['pest_name']   = $res['cn_name'];

            try {
                $dbRes = Db::table('improve_forecast')
                    ->field('create_time,status', true)->update($data);
            } catch (\Exception $e) {
                dump($e);
                die;
            }
            return $dbRes == 1 ? Communal::success('编辑信息成功') : Error::error('修改错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    //不用此方法了
    //通过用户 uid 查询用户角色
    static function queryrole($id)
    {
        try {
            $dbRes = Db::table('u_user_role')->where('uid', $id)->find();
            return is_array($dbRes) ? [true, $dbRes] : Errors::DATA_NOT_FIND;
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //全选删除
    static function delete($ids)
    {
        try {
            $dataRes = Db::table('improve_forecast')->whereIn('id', $ids)->update(['status' => 2]);
            return empty($dataRes) ? Error::error('删除错误') : Communal::success('删除成功');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    // 预测对象查询
    static function pestls($data)
    {
        try {
            $where = 'bs.status = 1 and bs.local = 2';
            $field = 'bs.id value,bs.cn_name label';
            $order = 'bs.create_time desc';
            if (!empty($data['name'])) $where .= " and bs.pest_name like" . "'%" . $data['name'] . "%'";
            //improve_species 物种数据库信息表
            $dbRes = Db::table('improve_species')->alias('bs')->join('improve_species_relation bf', 'bs.id = bf.pest_id')
                ->field($field)
                ->where($where)
                ->group('label')
                ->select();
            return Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //导出
    static function exportls($data, $field, $condition)
    {
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            if (!empty($condition['region'])) $where .= " and region like '%" . $condition['region'] . "%'";
            if (!empty($condition['name'])) $where .= " and pest_name like '%" . $condition['name'] . "%'";
            $dataRes = Db::table('improve_forecast')->field($field)->where($where)->order($order)->select();
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

}