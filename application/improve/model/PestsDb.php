<?php

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;

use tool\Error;
use tool\Communal;

/**
 * 有害生物信息维护
 */
class PestsDb
{
    /*已改 Lxl*/
//    static function ls($data)
//    {
//        try {
//            $query = Db::table("b_identify")->alias('bf')->join('b_identify_images bi', 'bf.id = bi.sid');
//            if (Helper::lsWhere($data, 'name')) $query->whereLike('cn_name', '%' . $data['name'] . '%');
//            $query->order("update_time", 'desc');
//            $dataRes = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
//            return [true, $dataRes];
//        } catch (\Exception $e) {
//            return Error::error($e->getMessage());
//        }
//    }/*不需要此方法了*/

//    static function query($id)
//    {
//        try {
//            $dbRes = Db::table('b_identify')
//                ->where('id', $id)
//                ->find();
//            if (empty($dbRes)) return Errors::DATA_NOT_FIND;
//            $dbRes['images'] = Db::table('b_identify_images')->where('sid', $id)->field('id,image')->select();
//            return [true, $dbRes];
//        } catch (\Exception $e) {
//            return Error::error($e->getMessage());
//        }
//    }/*不需要此方法了*/

    /*已改*/
    //根据有害生物类型判断--对应有害生物种类--APP端
    static function pestInfo($data)
    {
        try {
            $where = 'status = 1';
            $field = 'id value,cn_name label,eng_name';
            $order = 'update_time desc';
            if (!empty($data['name'])) $where .= " and cn_name like" . "'%" . $data['name'] . "%'" . "or eng_name like" . "'%" . $data['name'] . "%'";
            $dbRes = Db::table('improve_species')->field($field)->where($where)
                ->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();

            return empty($dbRes) ? Error::error('未找到相应数据') : Communal::successData($dbRes);

        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //寄主树种列表
    static function plantInfo($data)
    {
        try {
            $where = '1 = 1';
            $field = 'id value, cn_name label';
            $order = 'update_time desc';
            if (!empty($data['name'])) $where .= " and cn_name like" . "'%" . $data['name'] . "%'";

            $dbRes = Db::table('improve_plant')->field($field)->order($order)->where($where)->select();

            return empty($dbRes) ? Error::error('未找到相应数据') : Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //寄主树种列表-app端
    static function plantApp($data)
    {
        try {
            $where = '1 = 1';
            $field = 'id value, cn_name label,eng_name';
            $order = 'update_time desc';
            if (!empty($data['name'])) $where .= " and cn_name like" . "'%" . $data['name'] . "%'" . "or eng_name like" . "'%" . $data['name'] . "%'";

            $dbRes = Db::table('improve_plant')
                ->field($field)
                ->where($where)
                ->order($order)
                ->paginate($data['per_page'], false, ['page' => $data['current_page']])
                ->toArray();

            return empty($dbRes) ? Error::error('未找到相应数据') : Communal::successData($dbRes);

        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //根据有害生物类型判断--对应有害生物种类--APP端
    static function typeInfo($data)
    {
        try {
            $where = 'status = 1 and local = 2';
            $field = 'id value,cn_name label,eng_name';
            $order = 'create_time desc';
            if (!empty($data['name'])) $where .= " and cn_name like" . "'%" . $data['name'] . "%'" . "or eng_name like" . "'%" . $data['name'] . "%'";
            $dbRes = Db::table('improve_species')->field($field)->where($where)
                ->where('genre_type', $data['type'])
                ->group('id')
                ->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();

            return empty($dbRes) ? Error::error('未找到相应数据') : Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //根据有害生物类型判断--对应有害生物种类--web端
    static function pestType($data)
    {
        try {
            $where = 'status = 1 and local = 2';
            $field = 'id value,cn_name label';
            $order = 'create_time desc';
            if (!empty($data['name'])) $where .= " and cn_name like" . "'%" . $data['name'] . "%'";
            $dbRes = Db::table('improve_species')
                ->field($field)
                ->where($where)
                ->where('genre_type', $data['type'])
                ->group('id')
                ->select();
            return Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

}