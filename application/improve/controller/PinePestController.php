<?php
/**
 * Created by sevenlong.
 * User: Administrator
 * Date: 2017/12/13 0013
 * Time: 10:50
 */

namespace app\improve\controller;

use app\improve\model\PinePestDb;
use app\improve\model\StatisticsDb;
use app\improve\validate\PinePest;
use app\improve\model\BaseDb;
use app\improve\validate\BaseValidate;
use think\Controller;

/*
 * 松材线虫病调查--外业调查
 */
 
class PinePestController extends Controller
{
    function add(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'PinePest.add');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $data['adder'] = $auth[1]['s_uid'];
        $data['region_name'] = BaseDb::areaName($data['region']);
        $data['report_name'] = BaseDb::name($data['adder']);
        $dbRes = PinePestDb::add($data);
        return Helper::reJson($dbRes);
    }

	function ls(){
		$auth = Helper::auth();
		if (!$auth[0]) return Helper::reJson($auth);
		$data = Helper::getPostJson();
		$validate = new BaseValidate([
			'per_page' => 'require|number|max:50|min:1',
			'current_page' => 'require|number|min:1',
			'region|区域' => 'max:20|region',
			'surveryer|调查人' => 'max:16',
			'start_time|调查开始时间' => 'dateFormat:Y-m-d',
			'end_time|调查结束时间' => 'dateFormat:Y-m-d|>=:start_time'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
		if(!array_key_exists("region", $data))
        {
            $data['region'] = cookie('s_region');   
        }
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $result = PinePestDb::ls($data);
        return Helper::reJson($result);
    }
    
    function sampleMap(){
        $auth = Helper::auth();
		if (!$auth[0]) return Helper::reJson($auth);
		$data = Helper::getPostJson();
		$validate = new BaseValidate([
			'per_page' => 'require|number|max:50|min:1',
			'current_page' => 'require|number|min:1',
			'region|区域' => 'max:20|region',
			'type|调查类型' => 'require|in:1,2',
			'start_time|开始时间' => 'dateFormat:Y-m-d',
			'end_time|结束时间' => 'dateFormat:Y-m-d|>=:start_time'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
		if(empty($data['region']))
        {
            $data['region'] = cookie('s_region');   
        }
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $result = PinePestDb::sampleMap($data);
        return Helper::reJson($result);
    }

     // 根据id查看
    function query(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'PinePest.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = PinePestDb::query($data);
        return Helper::reJson($dbRes);
    }

     // 编辑
     function edit(){
         $auth = Helper::auth();
         if (!$auth[0]) return Helper::reJson($auth);
         $data = Helper::getPostJson();
         $result = $this->validate($data, 'PinePest.edit');
         if ($result !== true) return Helper::reJson(Errors::Error($result));
         if(!array_key_exists("region", $data))
         {
             $data['region'] = cookie('s_region');   
         }
         $region_result = Helper::authRegion($data);
         if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
         $adder = PinePestDb::adder($data['id']);
         if (!$adder[0]) return Helper::reJson($adder);
         $a = Helper::checkAdderOrManage($adder[1]['adder'], $auth[1]['s_uid']);
         if (!$a[0]) return Helper::reJson($a);
         $data['adder'] = $auth[1]['s_uid'];
         $data['region_name'] = BaseDb::areaName($data['region']);
         $data['report_name'] = BaseDb::name($data['adder']);
         $dbRes = PinePestDb::edit($data);
         return Helper::reJson($dbRes);
     }

    // 删除选中
    function deleteChecked(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'PinePest.ids');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = PinePestDb::deleteChecked($data['ids']);
        return Helper::reJson($dbRes);
    }	
	
	//疫情发展趋势图
    function trendChart(){
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'region' => 'require|max:20|region',
            'start_time' => 'require|dateFormat:Y',
            'end_time' => 'require|dateFormat:Y'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $result = PinePestDb::trendChart($data);
        return Helper::reJson($result);
    }

    // 导出字段显示
    function exportList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = [
            "class_number" => "小班号",
            "positions" => "地理位置",
            "location_name" => "地理位置名称",
            "region_name" => "所在区域",
            "forest_class_area" => "林班面积（亩）",
            "main_tree" => "主要树种",
            "forest_composition" => "林木组成",
            "number_of_plants" => "每亩株数",
            "forest_age" => "树龄 （年）",
            "dbh" => "胸径（CM）",
            "tree_height" => "树高(m)",
            "accumulative_volume" => "防治时间",
            "slope_direction" => "坡向",
            "canopy_density" => "郁闭度",
            "vegetation_type" => "植被种类",
            "dead_pine_num" => "枯死株数（株）",
            "dead_rate" => "枯死率（%）",
            "dead_area" => "枯死面积（亩）",
            "dead_reason" => "枯死原因初步分析",
            "report_name" => "上报人",
            "img" => "图片",
            "record" => [
                "sample_number" => "样本号",
                "sampling_part_name" => "危害部位",
                "results_name" => "送检结果",
                "illness_number" => "感病株数",
                "disease_rate" => "感病率（%）",
                "disease_area" => "感病面积（亩）"
            ]
        ];
        return json_encode(["code" => 's_ok',"var" => [$data]]);
    }

    //导出
    function exportExcel(){
        $data = $_GET;
        $condition=[];
        // 是否检索
        if(!empty($data['condition'])){
            $condition = $data['condition'];
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
        $field = substr($keys,31);
        $title = array_values($data);
        array_splice($title, 0, 1);
        $res = PinePestDb::exportls($data,$field,$img,$record,$condition);
        if ($res[0]){
            $dataRes = $res[1];
            if(empty($dataRes)){
                return json_encode(["code" => 'error',"var" => ['未找到数据']]); 
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
                $result[] = $val;
            }
            $name = '外业监测信息记录表';
            excelExport($name, $title, $result);
        }
    }
}