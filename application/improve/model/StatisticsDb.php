<?php
/**
 * Created by PhpStorm.
 * User: XieLe
 * Date: 2018/4/10
 * Time: 13:41
 * 统计图
 */

namespace app\improve\model;
use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;

class StatisticsDb extends BaseDb
{
	//获取查询区域名称
    static function region($data)
    {
        try {
            $dataRes = Db::table('c_region')->alias('r0')
                    ->where('r0.id', $data['region'])
                    ->join('c_region r', 'r.id = r0.id', 'left')
                    ->join('c_region r2', 'r.parentId = r2.id', 'left')
                    ->join('c_region r3', 'r2.parentId = r3.id', 'left')
                    ->join('c_region r4', 'r3.parentId = r4.id', 'left')
                    ->field('r4.name r4,r3.name r3,r2.name r2,r.name r1')
                    ->select();
			$result = Helper::transFormation($dataRes);  
            return is_array($result) ? [true, $result] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
	
	//害虫名称
    static function getPestName($id)
    {
        try {
            $dataRes = Db::table('b_pests')
                ->field('cn_name')
                ->where('id',$id)
                ->find();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
}