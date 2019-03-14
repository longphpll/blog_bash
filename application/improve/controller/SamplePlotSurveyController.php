<?php
/**
 * Created by qiu.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 17:20
 */

namespace app\improve\controller;

use think\Controller;
use app\improve\validate\BaseValidate;
use app\improve\model\SamplePlotSurveyDb;
use app\improve\model\BaseDb;
/*
 * 固定标准地调查
 */
class SamplePlotSurveyController extends Controller
{
    function add(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        if ($data['hazard_type'] == 1 ) {
            $result = $this->validate($data, 'SamplePlotSurvey.insect');
            if ($result != true) return Helper::reJson(Errors::Error($result));
        }
        if ($data['hazard_type'] == 2 ) {
            $result = $this->validate($data, 'SamplePlotSurvey.disease');
            if ($result != true) return Helper::reJson(Errors::Error($result));
        }
         if ($data['hazard_type'] == 3 ) {
            $result = $this->validate($data, 'SamplePlotSurvey.plant');
            if ($result != true) return Helper::reJson(Errors::Error($result));
        }
        $data['adder'] = $auth[1]['s_uid'];
        $data['report_name'] = BaseDb::name($data['adder']);
        $dbRes = SamplePlotSurveyDb::add($data);
        return Helper::reJson($dbRes);
    }

    function query(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'SamplePlotSurvey.id');
        if ($result != true) return Helper::reJson(Errors::Error($result));
        $dbRes = SamplePlotSurveyDb::query($data);
        return Helper::reJson($dbRes);
    }

    function ls($sample = false){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' => 'require|number|max:500|min:1',
            'current_page' => 'require|number|min:1',
            'type|生物类型' => 'require|number',
            'number|标准地编号' => 'number|min:1',
            'surveyer|调查人' => 'max:16',
            'start_time|调查开始时间' => 'dateFormat:Y-m-d',
            'end_time|调查结束时间' => 'dateFormat:Y-m-d'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $result = SamplePlotSurveyDb::ls($data,$sample);
        return Helper::reJson($result);
    }

    function appls($sample = false){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' => 'require|number|max:500|min:1',
            'current_page' => 'require|number|min:1',
            'number|标准地编号' => 'number|min:1',
            'surveyer|调查人' => 'max:16',
            'start_time|调查开始时间' => 'dateFormat:Y-m-d',
            'end_time|调查结束时间' => 'dateFormat:Y-m-d'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $result = SamplePlotSurveyDb::appls($data,$sample);
        return Helper::reJson($result);
    }

    function info(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'type|有害生物类型' => 'require|in:1,2,3'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $data['region'] = cookie('s_region');   
        $result = SamplePlotSurveyDb::info($data);
        return Helper::reJson($result);
    }

    function edit(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        if ($data['hazard_type'] == '1' ) {
            $result = $this->validate($data, 'SamplePlotSurvey.insect_edit');
            if ($result !== true) return Helper::reJson(Errors::Error($result));
        };
        if ($data['hazard_type'] == '2' ) {
            $result = $this->validate($data, 'SamplePlotSurvey.disease_edit');
            if ($result !== true) return Helper::reJson(Errors::Error($result));
        };
        if ($data['hazard_type'] == '3' ) {
            $result = $this->validate($data, 'SamplePlotSurvey.plant_edit');
            if ($result !== true) return Helper::reJson(Errors::Error($result));
        };
        $adder = Helper::queryAdder($data['id'],"b_sample_plot_survey");
        if (!$adder[0]) return Helper::reJson(Errors::DATA_NOT_FIND);
        $a = Helper::checkAdderOrManage($adder, $auth[1]['s_uid']);
        if (!$a) return Helper::reJson(Errors::LIMITED_AUTHORITY);
        $dbRes = SamplePlotSurveyDb::edit($data);
        return Helper::reJson($dbRes);
    }

    function deleteChecked(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'SamplePlotSurvey.ids');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = SamplePlotSurveyDb::deleteChecked($data['ids']);
        return Helper::reJson($dbRes);
    }

    function sampleMap(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' => 'number|max:50|min:1|notin:0',
			'region|区域' => 'max:20|region',
            'pest_id|监测对象' => 'number',
            'start_time|开始时间' => 'dateFormat:Y-m-d',
            'end_time|结束时间' => 'dateFormat:Y-m-d',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $result = SamplePlotSurveyDb::sampleMap($data);
        return Helper::reJson($result);
    }

    // 导出虫害类型--字段显示
    function exportList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = [
            "hazard_type" => "生物类型",
            "sample_plot_number" => "标准地编号",
            "small_place_name" => "小地名",
            "altitude" => "海拔（M）",
            "canopy_density" => "郁闭度",
            "strain_rate" => "有虫株率",
            "img" => "图片",
            "record" => [
                "number" => "样木号",
                "tree_height" => "树高（M)",
                "crown_width" => "冠幅（M)",
                "dbh" => "胸径（M）",
                "degree_level_name" => "危害程度",
                "egg" => "卵数量",
                "larva" => "幼虫数量",
                "pupa" => "蛹数量",
                "adult" => "成虫数量",
                "live_number" => "活虫数",
                "parasitic_number" => "寄生个数",
                "parasitism_rate" => "寄生率（%）",
                "natural_enemy_species" => "天敌种类"
            ]
        ];
        return json_encode(["code" => 's_ok',"var" => [$data]]);
    }

    // 导出病害类型--字段显示
    function diseaseExportList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = [
            "hazard_type" => "生物类型",
            "sample_plot_number" => "标准地编号",
            "small_place_name" => "小地名",
            "average_dbh" => "平均胸径（cm）",
            "average_tree_height" => "平均树高（m)",
            "canopy_density" => "郁闭度",
            "growth_trend" => "生长势",
            "terrain" => "地形地势",
            "happen_area" => "发生面积（亩)",
            "harm_level" => "危害程度",
            "distribution" => "病株分布情况",
            "img" => "图片",
            "record" => [
                "number" => "样木号",
                "harmful_part_name" => "危害部位",
                "disease_grade_name" => "危害等级",
                "total_number" => "总株数",
                "total_real_number" => "总种实数",
                "disease_index" => "感病指数",
                "disease_rate" => "发病率（%）",
                "death_rate" => "死亡率（%）"
            ]
        ];
        return json_encode(["code" => 's_ok',"var" => [$data]]);
    }

    // 导出有害植物类型--字段显示
    function plantExportList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = [
            "hazard_type" => "生物类型",
            "sample_plot_number" => "标准地编号",
            "small_place_name" => "小地名",
            "average_dbh" => "平均胸径（cm）",
            "canopy_density" => "郁闭度",
            "growth_trend" => "生长势",
            "terrain" => "地形地势",
            "happen_area" => "发生面积（亩)",
            "harm_level" => "危害程度",
            "distribution" => "害草分布情况",
            "img" => "图片",
            "record" => [
                "number" => "样木号",
                "sample_area" => "样方面积(亩)",
                "plant_area" => "植物占据面积（亩）",
                "coverage_rate" => "覆盖度（%）",
                "growth_influence_name" => "对树木生长影响的程度",
                "update_influence_name" => "对森林更新的影响程度",
                "tree_death_rate" => "树木死亡率 (%)"
            ]
        ];
        return json_encode(["code" => 's_ok',"var" => [$data]]);
    }

    //导出
    function exportExcel(){
        $data = $_GET;
        if (!empty($data['type'])){
            switch ($data['type'])
            {
                case "1":$table = 'b_insect_record';
                    break;
                case "2":$table = "b_disease_record";
                    break;
                case "3":$table = "b_plants_record";
                    break;
            }
            $type = $data['type'];//检索条件
            unset($data['type']);
        }else{
            return json_encode(["code" => 'error',"var" => ['导出失败']]); 
        }
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
        $field = substr($keys,40);
        $title = array_values($data);
        array_splice($title, 0, 1);
        $res = SamplePlotSurveyDb::exportls($data,$field,$type,$img,$record,$table,$condition);
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
                if (!empty($val['hazard_type'])){
                    switch ($val['hazard_type'])
                    {
                        case "1":$val['hazard_type'] = "虫害";
                            break;
                        case "2":$val['hazard_type'] = "病害";
                            break;
                        case "3":$val['hazard_type'] = "有害生物";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '固定标准地调查信息记录表';
            excelExport($name, $title, $result);
        }else{
            return json_encode(["code" => 'error',"var" => ['导出失败']]); 
        }
    }
}