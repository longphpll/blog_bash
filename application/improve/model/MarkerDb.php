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

//无人机上报数据
class MarkerDb extends BaseDb
{

    //无人机轨迹保存
    static function add($data)
    {
        try {
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $data['status']      = 1;
            unset($data['id']);
            $result = Db::table('improve_uav_locus')->insertGetId($data);
            return is_numeric($result) ? [true, $result] : Errors::ADD_ERROR;
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //列表
    static function ls($data)
    {
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            $field = 'id,user_name name,phone,create_time,drone_type,region,region_name,
            drone_lat,drone_lng,drone_height,target_lat_lng,target_size,marker_time';

            if (!empty($data['region'])) $where .= " and region like '%" . $data['region'] . "%'";
            if (!empty($data['name'])) $where .= " and user_name like" . "'%" . $data['name'] . "%'" . "or phone like" . "'%" . $data['name'] . "%'";
            if (!empty($data['begin_time'])) $where .= " and DATE(create_time) >='" . $data['begin_time'] . "'";
            if (!empty($data['end_time'])) $where .= " and DATE(create_time) <='" . $data['end_time'] . "'";
            $dataRes = Db::table('improve_marker')->field($field)->where($where)->order($order)
                ->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();

            $result = Communal::removeEmpty($dataRes);
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    // 详情
    static function query($data)
    {
        try {
            $res = Db::table('improve_marker')->where('id', $data['id'])->field('update_time,status', true)->find();

            $result = Communal::removeEmpty($res);

            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //查询用户轨迹记录
    static function getUvaLocusQueryList($data)
    {
        try {
            $where = '1 = 1';
            $order = 't.create_time desc';
            $field = 't.id,t.uid,t.name,t.tel,t.start_time,t.over_time,t.start_locus,t.over_locus,t.location_str,t.create_time';
            if (!empty($data['region'])) $where .= " and u.region like '" . $data['region'] . "%'";
            if (!empty($data['name'])) $where .= " and t.name like '" . $data['name'] . "%'";
            if (!empty($data['tel'])) $where .= " and t.tel like '" . $data['tel'] . "%'";
            if (!empty($data['playback_time'])) $where .= " and DATE(start_time) = '" . $data['playback_time'] . "'";

            $result = Db::table('improve_uav_locus')->alias('t')->join('frame_base_staff u', 'u.uid = t.uid')->field($field)->where($where)->order($order)->select();

            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
}
