<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22 0022
 * Time: 15:22
 */

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;
//use think\Error;
use think\Exception;

use tool\Communal;
use tool\Error;

class BaseDb
{

    //查询用户区域id
    static function regionCode($name, $parent, $level)
    {
        try {
            $dbRes = Db::table('improve_region')
                ->whereLike('name', $name . '%')
                ->where('parentId', $parent)
                ->where('level', $level)
                ->field('id')
                ->find();
            return $dbRes['id'];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }


    // 查询添加者
    static function queryAdder($id, $db_name)
    {
        try {
            $dbRes = Db::table($db_name)->where('id', $id)->field('adder')->find();
//            return !empty($dbRes) ? [true ,$dbRes ] :Errors::DATA_NOT_FIND;
            return !empty($dbRes) ? Communal::successData($dbRes) : Error::error('未找到相应数据');
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 查询数据添加者
    static function findAdder($id, $db_name)
    {
        try {
            $dbRes = Db::table($db_name)->where('id', $id)->field('uid')->find();
            return !empty($dbRes) ? [true, $dbRes] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 查询用户区域
    static function queryRegion($suid, $db_name)
    {
        try {
            $dbRes = Db::table($db_name)->where('uid', $suid)->field('region')->find();
            return !empty($dbRes) ? [true, $dbRes] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //查询用户区域id
    static function regionId($id, $db_name)
    {
        try {
            $dbRes  = Db::table($db_name)->alias('bt')
                ->where('bt.id', $id)
                ->join('improve_region r', 'r.id = bt.region', 'left')
                ->join('improve_region r2', 'r.parentId = r2.id', 'left')
                ->join('improve_region r3', 'r2.parentId = r3.id', 'left')
                ->join('improve_region r4', 'r3.parentId = r4.id', 'left')
                ->field('r4.id r4,r3.id r3,r2.id r2,r.id r1')
                ->find();
            $region = array_values($dbRes);
            return $region;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //查询用户区域是否存在
    static function region($name)
    {
        try {
            $dbRes = Db::table('improve_region')
                ->whereLike('name', $name . '%')
                ->field('name')
                ->find();
            return !empty($dbRes) ? [true, $dbRes] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //查询区域name
    static function areaName($id)
    {
        try {
            $dbRes = Db::table('improve_region')->alias('bt')
                ->where('bt.id', $id)
                ->join('improve_region r', 'r.id = bt.id', 'left')
                ->join('improve_region r2', 'r.parentId = r2.id', 'left')
                ->join('improve_region r3', 'r2.parentId = r3.id', 'left')
                ->join('improve_region r4', 'r3.parentId = r4.id', 'left')
                ->field('r4.name r4,r3.name r3,r2.name r2,r.name r1')
                ->find();
            return $name = implode("", $dbRes);
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //查询用户区域name
    static function regionName($id, $db_name)
    {
        try {
            $dbRes  = Db::table($db_name)->alias('bt')
                ->where('bt.id', $id)
                ->join('improve_region r', 'r.id = bt.region', 'left')
                ->join('improve_region r2', 'r.parentId = r2.id', 'left')
                ->join('improve_region r3', 'r2.parentId = r3.id', 'left')
                ->join('improve_region r4', 'r3.parentId = r4.id', 'left')
                ->field('r4.name r4,r3.name r3,r2.name r2,r.name r1')
                ->find();
            $region = array_values($dbRes);
            return $region;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //查询用户区域name
    static function pruName($id)
    {
        try {
            $dbRes = Db::table('improve_region')
                ->where('id', $id)
                ->field('name')
                ->find();
            return !empty($dbRes) ? [true, $dbRes] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // //根据区域生成顺序编号
    // static function prNum($table,$name,$region_name)
    // {
    //     $rs = Db::ta ble($table)->where('region_name',$region_name)->field(' COUNT(region_name) as code')->find();
    //     if(empty($rs['code'])){
    //         $prNum = $name.'0001';
    //     }else{
    //         $prNum = $name.str_pad ($rs['code']+1, 4, 0, STR_PAD_LEFT );
    //     }
    //     return $prNum;
    // }


    //根据编号生成顺序编号
    static function prNum($table, $number)
    {
        $rs = Db::table($table)->where('trap_number', $number)->field(' COUNT(trap_number) as code')->find();
        if (empty($rs['code'])) {
            $prNum = $number . '0001';
        } else {
            $prNum = $number . str_pad($rs['code'] + 1, 4, 0, STR_PAD_LEFT);
        }
        return $prNum;
    }


    //查询有害生物种类名称
    static function pest($id)
    {
        try {
            $dbRes = Db::table('improve_species')
                ->field('cn_name')
                ->where('id', $id)
                ->where('status', 1)
                ->find();
            return $dbRes['cn_name'];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //查询寄主树种名称
    static function plant($id)
    {
        try {
            $dbRes = Db::table('improve_plant_copy')
                ->where('id', $id)
                ->field('cn_name')
                ->find();
            if (empty($dbRes)) return Errors::DATA_NOT_FIND;
            return $dbRes['cn_name'];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //查询寄主树种拉丁名
    static function ename($id)
    {
        try {
            $dbRes = Db::table('improve_plant_copy')
                ->where('id', $id)
                ->field('eng_name')
                ->find();
            if (empty($dbRes)) return Errors::DATA_NOT_FIND;
            return $dbRes['eng_name'];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }


    // 查询用户区域
    static function name($id)
    {
        try {
//            $dbRes = Db::table('u_user')->where('uid', $id)->field('name')->find();
            $dbRes = Db::table('frame_base_staff')->where('uid', $id)->field('name')->find();
            if (empty($dbRes)) return Errors::DATA_NOT_FIND;
            return $dbRes['name'];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }


}