<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/5 0005
 * Time: 15:57
 */

namespace app\improve\model;

use think\Db;
use app\improve\controller\Errors;
use app\improve\controller\Helper;
/**
 * 追加调用 Communal 工具类
*/
use tool\Communal;
use tool\Error;
use tool\BaseDb as ToolBaseDb;

class QuarantineDb
{
    /**
     * 修改人：余思渡
     * 修改时间:2019.03.07
     * 修改内容:将 DB_name  由 b_quarantine 改为 improve_quarantine
     *            2019.03.08 重写数据返回格式
    */
    static function add($data)
    {
        unset($data['did']);
        try {
            $data['create_time'] =  date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $data['status'] = 1;
            $result = Db::table('improve_quarantine')->insertGetId($data);
            return is_numeric($result) ? Communal::success('添加成功') : Error::error('添加失败');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /**
     * 修改人：余思渡
     * 修改时间:2019.03.07
     * 修改内容:将 DB_name  由 b_quarantine 改为 improve_quarantine
    */
    static function ls($data,$sample = false){
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            if (!empty($data['region'])) $where.=" and region like '%".$data['region']."%'";
            if (!empty($data['organization'])) $where.=" and organization like '%".$data['organization']."%'";
            if ($sample) {
                $field = 'id,positions,location_name,region_name,organization,nature,found_time,administrator,tel';
            } else {
                $field = 'id,region,region_name,organization,nature,found_time,create_time';
            }
            $dataRes = Db::table('improve_quarantine')->field($field)->where($where)->order($order)
                ->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            return count($dataRes['data']) !==0 ? Communal::successData($dataRes) : Error::error('未找到数据') ;
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /**
     * 修改人：余思渡
     * 修改时间:2019.03.07
     * 修改内容:将 DB_name  由 b_quarantine 改为 improve_quarantine
    */
    static function query($id)
    {
        try {
            $dbRes = Db::table('improve_quarantine')->where('id', $id)
                ->where('status',1)
                ->field('update_time,status,adder',true)
                ->find();
            return empty($dbRes) ? Error::error('未找到数据') : Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /**
     * 修改人：余思渡
     * 修改时间:2019.03.07
     * 修改内容:将 DB_name  由 b_quarantine 改为 improve_quarantine
    */
    static function edit($data)
    {
        unset($data['did']);
        try {
            $check = Db::table('improve_quarantine')->where('id', $data['id'])->where('status',1)->value('id');
            if ($check <= 0) {
                return  Error::error('未找到数据');
            }else{
                $data['update_time'] =  date('Y-m-d H:i:s');
                $dbRes = Db::table('improve_quarantine')->field('create_time,status',true)->update($data);
                return $dbRes == 1 ? Communal::success('修改成功') : Error::error('修改失败');
            }
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /**
     * 修改人：余思渡
     * 修改时间:2019.03.07
     * 修改内容:将 DB_name  由 b_quarantine 改为 improve_quarantine
     * 备注：标准化返回格式(操作提示类)
    */
    static function deleteChecked($ids){
        try {
            $dataRes = Db::table('improve_quarantine')->whereIn('id', $ids)->update(['status'=> 2]);
            return empty($dataRes) ? Error::error('删除失败') : Communal::success('删除成功');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    // 数据导出
    static function exportls($data,$field,$condition){
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            if (!empty($condition['region'])) $where.=" and region like '%".$condition['region']."%'";
            if (!empty($condition['name'])) $where.=" and organization like '%".$condition['name']."%'";
            $dataRes = Db::table('b_quarantine')->field($field)->where($where)->order($order)->select();
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
}