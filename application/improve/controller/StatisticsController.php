<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/8
 * Time: 11:24
 */

namespace app\improve\controller;

use think\Controller;
use app\improve\model\BaseDb;
use app\improve\model\InstitutionDb;
use app\improve\model\MoneyintoDb;
use app\improve\model\PesticideDb;
use app\improve\model\StatisticsDb;
use app\improve\validate\BaseValidate;
use think\Db;

/*
 * 统计
 */
class StatisticsController
{
    function RegionOptional(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'type|类型' => 'require|in: 1,2,3'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        if($data['type'] == 1){
            //机构统计图
            $result = InstitutionDb::RegionOptional();
        }else if ($data['type'] == 2){
            //投入统计图
            $result = MoneyintoDb::RegionOptional();
        }else if($data['type'] == 3){
            $result = PesticideDb::RegionOptional();
        }
        return Helper::reJson($result);
    }

    function RegionList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'type|类型' => 'require|in: 1,2,3',
            'region|区域' => 'require|region|max:20'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
		$region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        if($data['type'] == 1){
            $result = InstitutionDb::OptionalList($data);
        }else if ($data['type'] == 2){
            $result = MoneyintoDb::OptionalList($data);
        }else if($data['type'] == 3){
            $result = PesticideDb::OptionalList($data);
        }
        return Helper::reJson($result);
    }

    //人财物管理统计图
    function StatisticsList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'region|区域' => 'require|max:20|region',
            'type|类型' => 'require|in: 1,2,3',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
		$region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        if($data['type'] == 1){//森防机构
            $res = InstitutionDb::InstitutionList($data);
        }else if ($data['type'] == 2){//资金投入
            $res = MoneyintoDb::MoneyintoList($data);
        }else if($data['type'] == 3){//农药使用
            $res = PesticideDb::PesticideList($data);
        }
        if(empty($res)){
            $region = '';
        }else{
            $region = BaseDb::areaName($data['region']);
        }
        $result = [
            'data' => $res,
            'region' => $region
        ];
        return Helper::reJson([true,$result]);
    }
}