<?php
/**
 * Created by PhpStorm.
 * Date: 2017/12/7/007
 * Time: 10:54
 */

namespace app\improve\controller;

use app\improve\model\CountryRecordDb;
use app\improve\model\StatisticsDb;
use app\improve\validate\BaseValidate;
use app\improve\model\BaseDb as BaseDbModel;


use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/**
 * 病虫害调查记录
 */
class CountryRecordController extends RedisBase
{
    /*已改 Lxl*/
    function add()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));

        //病虫害调查--虫害类型
        if ($data['hazard_type'] == '1') {
            $result = $this->validate($data, 'CountryRecord.insect');
            if ($result !== true) return Communal::return_Json(Error::validateError($result));
            $data['plant_name'] = BaseDbModel::plant($data['plant_id']);//查询寄主树种名称
        };
        //病虫害调查--病害类型
        if ($data['hazard_type'] == '2') {
            $result = $this->validate($data, 'CountryRecord.disease');
            if ($result !== true) return Communal::return_Json(Error::validateError($result));
            $data['plant_name'] = BaseDbModel::plant($data['plant_id']);
        };
        //病虫害调查--有害植物类型
        if ($data['hazard_type'] == '3') {
            $result = $this->validate($data, 'CountryRecord.plant');
            if ($result !== true) return Communal::return_Json(Error::validateError($result));
        };


        //区域检验 判断当前用户是否有权限操作
        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        //用户的uid
        $data['adder'] = $checkout[1]->uid;
        //tool BaseDb
        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码
        $data['pest_name']   = BaseDbModel::pest($data['pest_id']);//有害生物种类，id
        $data['report_name'] = $checkout[1]->name;//当前用户名字

        //调用模型层里的方法
        $dbRes = CountryRecordDb::add($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //列表
    function ls($sample = false)
    {
        return Communal::return_Json($this->lsDb($sample));
    }

    /*已改*/
    private function lsDb($sample = false, $download = false)
    {
        $data = $download ? $_GET : Communal::getPostJson();

        //验证did
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) {
            return Error::error($checkout[1], '', $checkout[2]);
        }

        $validate = new BaseValidate([
            'per_page'          => 'require|number|max:500|min:1',
            'current_page'      => 'require|number|min:1',
            'region|区域'         => 'max:20|region',
            'pest|有害生物种类'       => 'max:30',
            'type|有害生物类型'       => 'in:1,2,3',
            'begin_time|调查开始时间' => 'dateFormat:Y-m-d',
            'end_time|调查结束时间'   => 'dateFormat:Y-m-d',
            'adder|调查人'         => 'max:16',
        ]);
        if (!array_key_exists("region", $data)) {
            $data['region'] = session('staff')['region'];
        }

        //区域检验 判断当前用户是否有权限操作
        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Error::error(Error::Region_Verify_Error);
        }

        //如果数据验证成功就调用模型层里的 ls() 方法
        return $validate->check($data) ? CountryRecordDb::ls($data, $sample) : Error::error($validate->getError());
    }

    /*已改*/
    //总体概况--病虫害调查信息  改
    function sampleMap($sample = true)
    {
        return Communal::return_Json($this->lsDb($sample));
    }

    /*已改*/
    //app类型关联--生物种类查询
    function pestList()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));

        $validate = new BaseValidate([
            'per_page'     => 'require|number|max:500|min:1',  //每页数量
            'current_page' => 'require|number|min:1',//当前页
            'type|生物类型'    => 'require|number'//1表示虫害，2表示病害，3表示有害植物
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = CountryRecordDb::pestList($data);

        return Communal::return_Json($result);

    }

    /*已改*/
    //web类型关联--生物种类查询
    function pestWebList()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));

        $result = CountryRecordDb::pestWebList($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    //病虫害详情
    function query()
    {
        $data = Communal::getPostJson();
        //验证did
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));

        //验证参数
        $result = $this->validate($data, 'CountryRecord.id');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $result = CountryRecordDb::query($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    // 删除
    function deleteChecked()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));

        $result = $this->validate($data, 'CountryRecord.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = CountryRecordDb::deleteChecked($data['ids']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    // 编辑
    function edit()
    {
        $data = Communal::getPostJson();
        //验证did
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));

        //病虫害调查--虫害类型
        if ($data['hazard_type'] == '1') {
            $result = $this->validate($data, 'CountryRecord.insect_edit');
            if ($result !== true) return Communal::return_Json(Error::validateError($result));
            $data['plant_name'] = BaseDbModel::plant($data['plant_id']);
        };
        //病虫害调查--病害类型
        if ($data['hazard_type'] == '2') {
            $result = $this->validate($data, 'CountryRecord.disease_edit');
            if ($result !== true) return Communal::return_Json(Error::validateError($result));
            $data['plant_name'] = BaseDbModel::plant($data['plant_id']);
        };
        //病虫害调查--有害植物类型
        if ($data['hazard_type'] == '3') {
            $result = $this->validate($data, 'CountryRecord.plant_edit');
            if ($result !== true) return Communal::return_Json(Error::validateError($result));
        };

        //区域检验 判断当前用户是否有权限操作
        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        //查询添加者 //87ef035ebb54db09b6af0d886a1b5091 用户的uid
        $adder = Helper::queryAdder($data['id'], "improve_survey_record");
        if (!$adder[0]) return Communal::return_Json($adder);

        //查添加人是不是自己或者自己是管理员
//        $a = Helper::checkAdderOrManage($adder[1]["adder"], $auth[1]['s_uid']);
//        $a = Helper::checkAdderOrManage($adder[1]["adder"], $checkout[1]->uid);
//        if (!$a[0]) return Communal::return_Json($a);

        //不用上面的来判断是否是本人或者管理员,用下面的直接判断即可
        if (!(($checkout[1]->rid != 3) || ($adder[1]["adder"] != $checkout[1]->uid))) {
            return Communal::return_Json(Error::error('你不是管理,也不是本人,无权修改'));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码
        $data['pest_name']   = BaseDbModel::pest($data['pest_id']);//有害生物种类，id
        $data['report_name'] = $checkout[1]->name;//查询用户区域

        $dbRes = CountryRecordDb::edit($data);

        return Communal::return_Json($dbRes);
    }

    //统计管理------------------------------------------------------------------------------------------------------------------------------
    /*已改*/
    /**
     * 调查记录统计图
     */
    function villagesChart()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'region|区域'       => 'require',
            'type|统计类型'       => 'require|number|in:1,2',
            'pest|害虫种类'       => 'require|number',
            'start_time|开始时间' => 'require|dateFormat:Y-m',
            'end_time|结束时间'   => 'require|dateFormat:Y-m'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = CountryRecordDb::trendChart($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    //调查记录统计
    function statisticslist()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'per_page'        => 'require|number|max:500|min:1|notin:0',
            'current_page'    => 'require|number|min:1|notin:0',
            'region|区域'       => 'require|max:20|region',
            'pest|有害生物种类'     => 'require|number',
            'start_time|开始月份' => 'require|dateFormat:Y-m',
            'end_time|结束年份'   => 'require|dateFormat:Y-m'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = CountryRecordDb::villagesList($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    //调查记录统计导出
    function recordExcel()
    {
        $data     = $_GET;
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'region|区域'       => 'require',
            'pest|有害生物种类'     => 'require|number',
            'start_time|开始月份' => 'require|dateFormat:Y-m',
            'end_time|结束年份'   => 'require|dateFormat:Y-m'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $res = CountryRecordDb::recordExcel($data);

        if ($res[0]) {
            $result = $res[1];
            if (empty($result)) {
                return json_encode(["code" => 'error', "var" => ['未找到数据']]);
            }
            unset($result['dataResOne']);
            $name   = '调查记录统计表';
            $header = ['区域', '有害生物种类', '调查时间', '分布面积（亩）', '成灾面积（亩）', '调查次数'];
            statisticsExport($name, $header, $result['dataResTwo']);
        }
    }

    /*已改*/
    //历史对比图
    function historyList()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'region|区域'       => 'require|region',
            'pest|有害生物'       => 'require|number',
            'start_time|开始年份' => 'require|dateFormat:Y|<=:end_time',
            'end_time|结束年份'   => 'require|dateFormat:Y|>=:start_time',
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = CountryRecordDb::history($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    //病虫害分布信息  热力图
    function heatMap()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'type|类型'      => 'require',
            'pest_id|害虫种类' => 'number',
            'region|区域'    => 'max:20|region'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));


        if (!array_key_exists("region", $data)) {
            $data['region'] = session('staff')['region'];
        }

        //区域检验 判断当前用户是否有权限操作
        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $result = CountryRecordDb::heatMap($data);

        return Communal::return_Json($result);
    }

    //病虫害分布信息 //没有接口文档
    function heatMap22()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        if (!array_key_exists("region", $data)) {
            $data['region'] = session('staff')['region'];
        }
        $region_result = Helper::authRegion($data);
        if (!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $result = CountryRecordDb::heatMap22($data);
        return Helper::reJson($result);
    }

    /*已改*/
    // 导出--字段显示  虫害--获取导出字段
    function exportList()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $data = [
            "pest_name"         => "生物种类",
            "plant_name"        => "寄主",
            "hazard_type"       => "危害类型",
            "harmful_part"      => "危害部位",
            "happen_level"      => "发生程度",
            "hazard_level"      => "危害程度",
            "generation"        => "世代",
            "region_name"       => "所在区域",
            "positions"         => "地理位置",
            "location_name"     => "地理位置名称",
            "distribution_area" => "分布面积（亩）",
            "damaged_area"      => "成灾面积（亩）",
            "is_main_pests"     => "是否是主要病虫害",
            "remarks"           => "备注",
            "img"               => "图片",
            "strain_rate"       => "有虫株率",
            "record"            => [
                "tense_name"    => "虫态",
                "age"           => "虫龄",
                "pests_density" => "虫口密度",
                "pests_unit"    => "虫口密度单位"
            ]
        ];
        return json_encode(["code" => 's_ok', "var" => [$data]]);
    }

    /*已改*/
    // 导出--病害字段显示 病害--获取导出字段
    function dieaseList()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $data = [
            "positions"         => "地理位置",
            "location_name"     => "地理位置名称",
            "region_name"       => "所在区域",
            "hazard_type"       => "危害类型",
            "happen_level"      => "发生程度",
            "pest_name"         => "生物种类",
            "plant_name"        => "寄主",
            "harmful_part"      => "危害部位",
            "susceptibility"    => "感病指数",
            "strain_rate"       => "感病株率（%）",
            "hazard_level"      => "危害程度",
            "distribution_area" => "分布面积（亩）",
            "damaged_area"      => "成灾面积（亩）",
            "is_main_pests"     => "是否是主要病虫害",
            "remarks"           => "备注",
            "img"               => "图片"
        ];
        return json_encode(["code" => 's_ok', "var" => [$data]]);
    }

    /*已改*/
    // 导出--有害植物字段显示 有害植物--获取导出字段
    function plantList()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $data = [
            "positions"          => "地理位置",
            "location_name"      => "地理位置名称",
            "region_name"        => "所在区域",
            "hazard_type"        => "危害类型",
            "happen_level"       => "发生程度",
            "pest_name"          => "生物种类",
            "plant_name"         => "寄主",
            "harmful_part"       => "危害部位",
            "species_type"       => "物种类型",
            "habitat_type"       => "生境类型",
            "plant_cover_degree" => "盖度",
            "hazard_level"       => "危害程度",
            "strain_rate"        => "受害株率（%）",
            "distribution_area"  => "分布面积（亩）",
            "damaged_area"       => "成灾面积（亩）",
            "is_main_pests"      => "是否是主要病虫害",
            "remarks"            => "备注",
            "img"                => "图片"
        ];
        return json_encode(["code" => 's_ok', "var" => [$data]]);
    }

    /*已改*/
    //导出
    function exportExcel()
    {
        $data = $_GET;

        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        //去掉 键位 did,后面不需要
        unset($data['did']);

        $condition = [];
        if (!empty($data['condition'])) {
            $condition = $data['condition'];//检索条件
            unset($data['condition']);
        } else {
            $condition['region'] = session('staff')['region'];
        }

        // 是否导出图片
        if (isset($data['img'])) {
            $img = true;
            unset($data['img']);
        } else {
            $img = false;
        }

        // 是否导出记录
        if (isset($data['record'])) {
            $record_keys  = array_keys($data['record']);
            $record_names = array_values($data['record']);
            $record       = implode(',', $record_keys);
            unset($data['record']);
        } else {
            $record = false;
        }
        $keys  = implode(',', array_keys($data));
        $field = substr($keys, 36);
        $title = array_values($data);
        array_splice($title, 0, 1);

        //print_r($title);//Array ( [0] => 危害类型 [1] => 发生程度 [2] => 区域 [3] => 地理位置名称 [4] => 世代 )
        //print_r($field);die; //string 'hazard_type,happen_level,region_name,location_name,generation'

        $res = CountryRecordDb::exportls($data, $field, $img, $record, $condition);

        if ($res[0]) {
            $dataRes = $res[1];
            if (empty($dataRes)) {
                return Communal::return_Json(Error::error('未找到相关数据'));
            }
            foreach ($dataRes as $key => $val) {
                unset($val['id']);
                if (!empty($val['record'])) {
                    foreach ($val['record'] as $ky => $vl) {
                        for ($i = 0; $i < count($record_names); $i++) {
                            $cont[]             = $record_names[$i] . "：" . $vl[$i] . "\r\n";
                            $recordCont         = implode(',', $cont);
                            $val['record'][$ky] = $recordCont;
                        }
                        unset($cont);
                    }
                }
                if (!empty($val['hazard_type'])) {
                    switch ($val['hazard_type']) {
                        case "1":
                            $val['hazard_type'] = "虫害";
                            break;
                        case "2":
                            $val['hazard_type'] = "病害";
                            break;
                        case "3":
                            $val['hazard_type'] = "有害生物";
                            break;
                    }
                }
                // 危害部位
                if (!empty($val['harmful_part'])) {
                    switch ($val['harmful_part']) {
                        case "1":
                            $val['harmful_part'] = "叶部";
                            break;
                        case "2":
                            $val['harmful_part'] = "干部";
                            break;
                        case "3":
                            $val['harmful_part'] = "枝梢部";
                            break;
                        case "4":
                            $val['harmful_part'] = "根部";
                            break;
                        case "5":
                            $val['harmful_part'] = "种实";
                            break;
                    }
                }
                // 世代
                if (!empty($val['generation'])) {
                    switch ($val['generation']) {
                        case "1":
                            $val['generation'] = "越冬代";
                            break;
                        case "2":
                            $val['generation'] = "第一代";
                            break;
                        case "3":
                            $val['generation'] = "第二代";
                            break;
                        case "4":
                            $val['generation'] = "第三代";
                            break;
                        case "5":
                            $val['generation'] = "第四代";
                            break;
                        case "6":
                            $val['generation'] = "第五代";
                            break;
                        case "7":
                            $val['generation'] = "第六代";
                            break;
                        case "8":
                            $val['generation'] = "第七代";
                            break;
                    }
                }
                // 危害程度
                if (!empty($val['hazard_level'])) {
                    switch ($val['hazard_level']) {
                        case "1":
                            $val['hazard_level'] = "轻";
                            break;
                        case "2":
                            $val['hazard_level'] = "中";
                            break;
                        case "3":
                            $val['hazard_level'] = "重";
                            break;
                    }
                }
                // 发生程度
                if (!empty($val['happen_level'])) {
                    switch ($val['happen_level']) {
                        case "1":
                            $val['happen_level'] = "轻";
                            break;
                        case "2":
                            $val['happen_level'] = "中";
                            break;
                        case "3":
                            $val['happen_level'] = "重";
                            break;
                    }
                }
                // 世代
                if (!empty($val['is_main_pests'])) {
                    switch ($val['is_main_pests']) {
                        case "1":
                            $val['is_main_pests'] = "是";
                            break;
                        case "2":
                            $val['is_main_pests'] = "否";
                            break;
                    }
                }

                $result[] = $val;
            }
            $name = '病虫害调查信息记录表';
            excelExport($name, $title, $result);
        }
    }
}