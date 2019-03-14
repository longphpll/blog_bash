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

use tool\Error;
use tool\Communal;

/**
 * 森防机构
 */
class InstitutionDb extends BaseDb
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
            $result = Db::table('improve_institution')->insertGetId($data);
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
            $field = 'id,region,region_name,designation,nature,level,name,tel';
            $order = 'create_time desc';
            if (!empty($data['region'])) $where .= " and region like '%" . $data['region'] . "%'";
            if (!empty($data['designation'])) $where .= " and designation like '%" . $data['designation'] . "%'";
            if (!empty($data['nature'])) $where .= " and nature = " . $data['nature'];
            if (!empty($data['level'])) $where .= " and level = " . $data['level'];

            $dataRes = Db::table('improve_institution')->field($field)->where($where)->order($order)
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
            $dbRes = Db::table('improve_institution')->alias('m')
                ->join('frame_base_staff u', 'u.uid = m.adder', 'left')
                ->field('m.id,m.region,m.region_name,m.positions,m.position_type,m.location_name,m.designation,m.nature,m.level,m.name,m.tel,m.totality,m.working,m.remark,u.name adder,m.create_time')
                ->where('m.id', $id)
                ->where('m.status', 1)
                ->find();

            $result = Communal::removeEmpty($dbRes);

            return empty($result) ? Error::error('未找到相应数据') : Communal::successData($result);
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

            $dbRes = Db::table('improve_institution')
                ->field('designation,nature,totality,working,region,region_name,location_name,adder,positions,position_type,
                name,tel,level,remark,update_time')->update($data);

            return $dbRes == 1 ? Communal::success('编辑信息成功') : Error::error('修改错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    static function deleteChecked($ids)
    {
        try {
            $dataRes = Db::table('improve_institution')->whereIn('id', $ids)->update(['status' => 2]);
            return empty($dataRes) ? Error::error('删除错误') : Communal::success('删除成功');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    static function RegionOptional()
    {
        try {
            $datasql = "(SELECT DISTINCT substring(region,1,9) region FROM b_institution  where status = '1') ";
            $dataRes = Db::table('c_region')->alias('cr')
                ->join([$datasql => 'bi'], 'cr.id = bi.region')
                ->where('cr.level = 4')
                ->field('cr.id lable,cr.name value')
                ->select();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    static function OptionalList($data)
    {
        try {
            $datasql = Db::table('improve_institution')
                ->field('DISTINCT region')
                ->where('status', '1')
                ->whereLike('region', $data['region'] . '%')
                ->buildSql();
            $dataRes = Db::table('c_region')->alias('cr')
                ->join([$datasql => 'bi'], 'cr.id = bi.region')
                ->where('cr.level = 5')
                ->field('cr.id lable,cr.name value')
                ->select();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    //机构统计图
    static function InstitutionList($data)
    {
        try {
            $dataRes = Db::table('improve_institution')
                ->field('level,count(id) number,sum(working) works')
                ->where('status', 1)
                ->whereLike('region', $data['region'] . '%')
                ->group('level')
                ->select();
            return $dataRes;
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
            if (!empty($condition['designation'])) $where .= " and designation like '%" . $condition['designation'] . "%'";
            if (!empty($condition['nature'])) $where .= " and nature = " . $condition['nature'];
            if (!empty($condition['level'])) $where .= " and level = " . $condition['level'];
            $dataRes = Db::table('improve_institution')->field($field)->where($where)->order($order)->select();
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
}