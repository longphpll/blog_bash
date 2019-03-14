<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/2/002
 * Time: 16:58
 */

namespace app\improve\controller;

use tool\BaseDb;
use app\improve\model\BaseDb as BaseDbs;
use app\improve\model\VillageHandDb;
use app\improve\validate\BaseValidate;
use tool\Communal;
use tool\Error;
use base_frame\RedisBase;

/*
 * 病虫害防治
 */
class VillageHandController extends RedisBase
{
    /**
     * 已改
     * @return \think\response\Json
     */
    function add(){
        $data=Communal::getPostJson();
        $checkout= $this->checkout($data['did'],1);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $result = $this->validate($data, 'VillageHand.add');
        if($result !== true) return  Communal::return_Json(Error::validateError($result));
        //区域检验 判断当前用户是否有权限操作
        $regionVerify=Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        $data['adder'] = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);
        $data['pest_name']  = BaseDbs::pest($data['pest_id']);
        $dbRes = VillageHandDb::add($data);
        return Communal::return_Json($dbRes);
    }

    /**
     * 已改
     * @param bool $sample
     * @param bool $download
     * @return \think\response\Json
     */
    function ls($sample = false,$download = false)
    {
        $data = $download ? $_GET : Communal::getPostJson();
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $data['uid']  = $checkout[1]->uid;
        $validate = new BaseValidate([
            'per_page' => 'require|number|max:50|min:1',
            'current_page' => 'require|number|min:1',
            'pest|有害生物种类' => 'max:30',
            'type|有害生物类型' => 'in:1,2,3,4',
            'start_time|防治开始时间' => 'dateFormat:Y-m-d',
            'end_time|防治结束时间' => 'dateFormat:Y-m-d',
            'region|区域' => 'max:20',
        ]);
        if (!$validate->check($data))  return  Communal::return_Json(Error::validateError($validate->getError()));
        if(!array_key_exists("region", $data))
        {
            $data['region'] = $checkout[1]->region;
        }
        //区域检验 判断当前用户是否有权限操作
        $regionVerify=Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        $result = VillageHandDb::ls($data, $sample);
        return Communal::return_Json($result);
    }

    //总体概况--病虫害防治信息
    function sampleMap()
    {
        return $this->ls(true);
    }

    /**
     * 已改
     * @return \think\response\Json
     */
    function query()
    {
        $data = Communal::getPostJson();
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $result = $this->validate($data, 'VillageHand.id');
        if ($result !== true) return  Communal::return_Json(Error::validateError($result));
        if(!array_key_exists("region", $data))
        {
            $data['region'] = cookie('s_region');
        }
        $dbRes = VillageHandDb::query($data['id']);
        return Communal::return_Json($dbRes);
    }

    /**
     * 修改操作   已改
     * @return \think\response\Json
     */
    function edit()
    {
        $data = Communal::getPostJson();
        $checkout= $this->checkout($data['did'],1);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $result = $this->validate($data, 'VillageHand.edit');
        if ($result !== true) return  Communal::return_Json(Error::validateError($result));
        //区域检验 判断当前用户是否有权限操作
        $regionVerify=Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        $adder = Helper::queryAdder($data['id'], "improve_village_hand");
        if (!$adder[0])  return Communal::return_Json(Error::error('未找到相应数据'));
        if($adder!=$checkout[1]->uid && $checkout[1]->rid==3) return Communal::return_Json(Error::error('你无权操作该数据'));
        $data['region_name'] = BaseDb::regionName($data['region']);
        $data['pest_name']  = BaseDbs::pest($data['pest_id']);
        $dbRes = VillageHandDb::edit($data);
        return Communal::return_Json($dbRes);
    }

    /**
     * 已改
     * @return \think\response\Json
     */
    function deleteChecked()
    {
        $data = Communal::getPostJson();
        $checkout= $this->checkout($data['did'],1);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $result = $this->validate($data, 'VillageHand.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));
        $dbRes = VillageHandDb::deleteChecked($data['ids']);
        return Communal::return_Json($dbRes);
    }

    /**
     * 防治记录统计图  已改
     * @return \think\response\Json
     */
    function messageChart()
    {
        $data = Communal::getPostJson();
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $validate = new BaseValidate([
            'region|区域' => 'require|max:20',
            'pest|病虫种类' => 'require|number',
            'start_time|开始时间' => 'require|dateFormat:Y-m',
            'end_time|结束时间' => 'require|dateFormat:Y-m',
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::validateError($validate->getError()));
        //区域检验 判断当前用户是否有权限操作
        $regionVerify=Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        $result = VillageHandDb::messageChart($data);
        return Communal::return_Json($result);
    }

    /**
     * 防治记录统计 已改
     * @return \think\response\Json
     */
    function statisticslist(){
        $data = Communal::getPostJson();
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $validate = new BaseValidate([
            'per_page' => 'require|number|max:500|min:1',
            'current_page' => 'require|number|min:1',
            'region|区域' => 'require|max:20',
            'pest|有害生物种类' => 'require|number',
            'start_time|调查开始时间' => 'require|dateFormat:Y-m',
            'end_time|调查结束时间' => 'require|dateFormat:Y-m'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::validateError($validate->getError()));
        //区域检验 判断当前用户是否有权限操作
        $regionVerify=Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        $result = VillageHandDb::villagesList($data);
        return Communal::return_Json($result);
    }

    /**
     * 防治记录统计导出 已改
     * @return \think\response\Json
     */
    function recordExcel()
    {
        $data = $_GET;
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $validate = new BaseValidate([
            'region|区域' => 'require',
            'pest|有害生物种类' => 'require|number',
            'start_time|调查开始时间' => 'require|dateFormat:Y-m',
            'end_time|调查结束时间' => 'require|dateFormat:Y-m'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::validateError($validate->getError()));
        $res = VillageHandDb::villagesRecord($data);
        if ($res[0]){
            $result = $res[1];
            if(empty($result)){
                return Communal::return_Json(Error::error('未找到数据'));
            }
            unset($result['dataResOne']);
            $name = '防治记录统计表';
            $header = ['区域', '有害生物种类', '防治时间',  '防治费用(元)','发生面积(亩)','防治面积(亩)','挽回灾害面积(亩)','防治次数'];
            statisticsExport($name, $header, $result['dataResTwo']);
        }
        return Communal::return_Json($res);
    }

    /**
     * APP--列表查询关联--类型关联生物种类 已改
     * @return \think\response\Json
     */
    function typeList(){
        $data = Communal::getPostJson();
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $validate = new BaseValidate([
            'per_page' => 'require|number|max:500|min:1',
            'current_page' => 'require|number|min:1',
            'type|生物类型' => 'require|number'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));
        $result = VillageHandDb::typeList($data);
        return Communal::return_Json($result);
    }

    /**
     * Web--统计--已有生物种类  （已改）
     * @return \think\response\Json
     */
    function pestList(){
        $data = Communal::getPostJson();
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $result = VillageHandDb::pestList($data);
        return Communal::return_Json($result);
    }

    /**
     * Web--列表查询关联--类型关联生物种类  已改
     * @return \think\response\Json
     */
    function typeWebList(){
        $data = Communal::getPostJson();
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $validate = new BaseValidate([
            'type|生物类型' => 'require|number'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::validateError($validate->getError()));
        $result = VillageHandDb::typeWebList($data);
        return Communal::return_Json($result);
    }

    /**
     * 防治措施信息录入  已改
     * @return \think\response\Json
     */
    function info(){
        $data = Communal::getPostJson();
        $result = VillageHandDb::info($data);
        return Communal::return_Json($result);
    }

    /**
     * 防治措施信息  已改
     * @return \think\response\Json
     */
    function handWay(){
        $data = Communal::getPostJson();
        $result = VillageHandDb::handWay($data);
        return Communal::return_Json($result);
    }

    /**
     * 防治药剂信息  已改
     * @return \think\response\Json
     */
    function drugInfo(){
        $data = Communal::getPostJson();
        $validate = $this->validate($data, 'VillageHand.id');
        if (!$validate->check($data)) return Communal::return_Json(Error::validateError($validate->getError()));
        $result = VillageHandDb::drugInfo($data);
        return Communal::return_Json($result);
    }

    // 导出字段显示
    function exportList(){
        $data = [
            "type" => "生物类型",
            "pest_name" => "生物种类名称",
            "positions" => "地理位置",
            "location_name" => "地理位置名称",
            "region_name" => "所在区域",
            "happen_area" => "发生面积（亩）",
            "hand_area" => "防治面积（亩）",
            "hand_cost" => "防治费用(元)",
            "hand_effect" => "防治效果(%)",
            "save_pest_area" => "挽回灾害面积（亩）",
            "happen_area" => "发生面积（亩）",
            "hand_area" => "防治面积（亩）",
            "hand_time" => "防治时间",
            "hander" => "防治人",
            "img" => "图片",
            "record" => [
                "one_class_name" => "防治措施一级",
                "two_class_name" => "防治措施二级",
                "drug_chs_name" => "防治药剂",
                "drug_amount" => "用药量",
                "drug_unit" => "单位"
            ]
        ];
        return Communal::return_Json(Communal::successData($data));
    }

    //导出
    function exportExcel(){
        $data = $_GET;
        $condition=[];
        if(!empty($data['condition'])){
            $condition = $data['condition'];//检索条件
            unset($data['condition']);
        }else{
            $condition['region'] = cookie('s_region');
        }
        // 是否导出图片
        if (isset($data['img'])){
            $img = true;
            unset($data['img']);
        }else{
            $img = false;
        }
        // 是否导出记录
        if (isset($data['record'])){
            $record_keys = array_keys($data['record']);
            $record_names = array_values($data['record']);
            $record = implode(',',$record_keys);
            unset($data['record']);
        }else{
            $record = false;
        }
        $keys = implode(',',array_keys($data));
        $field = substr($keys,34);
        $title = array_values($data);
        array_splice($title, 0, 1);
        $res = VillageHandDb::exportls($data,$field,$img,$record,$condition);
        if ($res[0]){
            $dataRes = $res[1];
            if(empty($dataRes)){
                return Communal::return_Json(Error::error('未找到数据'));
            }
            foreach ($dataRes as $key => $val) {
                unset($val['id']);
                if (!empty($val['record'])){
                    foreach ($val['record'] as $ky => $vl) {
                        for ($i=0; $i < count($record_names); $i++) {
                            $cont[] = $record_names[$i]."：".$vl[$i]."\r\n";
                            $recordCont = implode(',',$cont);
                            $val['record'][$ky] = $recordCont;
                        }
                        unset($cont);
                    }
                }
                if (!empty($val['type'])){
                    switch ($val['type'])
                    {
                        case "1":$val['type'] = "虫害";
                            break;
                        case "2":$val['type'] = "病害";
                            break;
                        case "3":$val['type'] = "有害植物";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '病虫害防治记录表';

            excelExport($name, $title, $result);
        }
    }
}