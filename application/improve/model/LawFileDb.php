<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22 0022
 * Time: 14:41
 */

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use Exception;
use think\Db;

use tool\Error;
use tool\Communal;

/**
 * 法规文件
 */
class LawFileDb
{
    /*已改 Lxl*/
    static function add($data)
    {
        try {
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];

            $result = Db::table('improve_law_file')->insertGetId($data);

            return is_numeric($result) ? Communal::success('添加成功,记录id为：' . $result) : Error::error('添加错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    static function ls($data)
    {
        try {
            $where = 'vh.status = 1';
            $order = 'vh.create_time desc';
            $field = 'vh.id,vh.create_time,vh.title,vh.sort,u.name';
            if (!empty($data['title'])) $where .= " and vh.title like '%" . $data['title'] . "%'";
            if (!empty($data['sort'])) $where .= " and vh.sort like '%" . $data['sort'] . "%'";
            if (!empty($data['create_time_min'])) $where .= " and DATE(vh.create_time) >='" . $data['create_time_min'] . "'";
            if (!empty($data['create_time_max'])) $where .= " and DATE(vh.create_time) <='" . $data['create_time_max'] . "'";

            $dataRes = Db::table('improve_law_file')->alias('vh')->join('frame_base_staff u', 'u.uid = vh.adder', 'left')->field($field)->where($where)->order($order)
                ->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();

            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    static function query($id)
    {
        try {
            $query = Db::table('improve_law_file')->alias('vh')->where('vh.id', $id)
                ->where('vh.status', '=', '1')
                ->join('frame_base_staff p', 'p.uid = vh.adder', 'left')
                ->field('vh.id,vh.sort,vh.title,vh.content,vh.file_path,vh.create_time,p.name')
                ->find();

            return !empty($query) ? Communal::successData($query) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/ //编辑时上传了图片
    static function edit($data)
    {
        try {
            unset($data['create_time'], $data['adder']);
            $data['update_time'] = date('Y-m-d H:i:s');
            $dbRes               = Db::table('improve_law_file')->update($data);
            return $dbRes == 1 ? Communal::success('编辑信息成功') : Error::error('修改错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/ //编辑时没有上传图片
    static function updateFile($data)
    {
        try {
            unset($data['create_time'], $data['adder']);
            $data['update_time'] = date('Y-m-d H:i:s');
            $dbRes               = Db::table('improve_law_file')->where('id', $data['id'])->update($data);
            return $dbRes == 1 ? Communal::success('编辑信息成功') : Error::error('修改错误');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //全选删除
    static function deleteChecked($ids)
    {
        try {
            $dataRes = Db::table('improve_law_file')->whereIn('id', $ids)->update(['status' => 2]);
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
            $where = 'status = 1 ';
            $order = 'create_time desc';
            if (!empty($condition['title'])) $where .= " and title like '%" . $condition['title'] . "%'";
            if (!empty($condition['sort'])) $where .= " and sort = " . $condition['sort'];
            if (!empty($condition['start_time'])) $where .= " and DATE(create_time) >='" . $condition['start_time'] . "'";
            if (!empty($condition['end_time'])) $where .= " and DATE(create_time) <='" . $condition['end_time'] . "'";
            $dataRes = Db::table('improve_law_file')->field($field)->where($where)->order($order)->select();

            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
}