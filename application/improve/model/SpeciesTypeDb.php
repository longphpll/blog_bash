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
use think\Exception;

class SpeciesTypeDb  extends BaseDb
{
    static function add($data){
        try {
            $data['create_time'] =  date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            unset($data['id']);
            $result = Db::table('b_species_type')->insertGetId($data);
            return is_numeric($result) ? [true ,$result] : Errors::ADD_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function ls(){
        try {
            $order = 'create_time asc';
            $field = 'id,parentId,name,type';
            $dataRes = Db::table('b_species_type')->field($field)->order($order)->select();
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function edit($data){
        try {
            $data['update_time'] =  date('Y-m-d H:i:s');
            unset($data['create_time']);
            $dbRes = Db::table('b_species_type')->field('name,update_time,adder')->update($data);
            return $dbRes == 1 ? [true, $dbRes] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function deleteChecked($id){
        try {
            $dataRes = Db::table('b_species_type')->where('id', $id)->delete();
            return empty($dataRes) ? Errors::DELETE_ERROR : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }


}