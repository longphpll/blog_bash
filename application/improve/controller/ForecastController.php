<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2018/3/16
 * Time: 16:12
 */

namespace app\improve\controller;

use think\Controller;
use app\improve\model\BaseDb as BaseDbModel;
use app\improve\model\ForecastDb;
use app\improve\validate\BaseValidate;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/**
 * 预测预报
 */
class ForecastController extends RedisBase
{
    /*已改 Lxl*/
    function add()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Forecast.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['report']      = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码
        $data['pest_name']   = BaseDbModel::pest($data['pest']);//有害生物种类，id
        $data['plant_name']  = BaseDbModel::pest($data['plant']);
        $data['report_name'] = $checkout[1]->name;

        $dbRes = ForecastDb::add($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //列表
    function ls()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'per_page'     => 'require|number|max:50|min:1',
            'current_page' => 'require|number|min:1',
            'object|预测对象'  => 'number',
            'region|区域'    => 'region|max:20'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        if (!array_key_exists("region", $data)) {
            $data['region'] = session('staff')['region'];
        }

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $result = ForecastDb::ls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    //详情
    function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Forecast.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = ForecastDb::query($data['id']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //编辑
    function edit()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Forecast.edit');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['report']      = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码
        $data['pest_name']   = BaseDbModel::pest($data['pest']);
        $data['plant_name']  = BaseDbModel::pest($data['plant']);
        $data['report_name'] = $checkout[1]->name;

        $dbRes = ForecastDb::edit($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //全选删除
    function deleteChecked()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Forecast.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = ForecastDb::delete($data['ids']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    // 预测对象查询
    function pestls()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'name|预测对象' => 'max:20'
        ]);

        $result = ForecastDb::pestls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    // 导出字段显示
    function exportList()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $data = [
            "pest_name"       => "预测对象",
            "plant_name"      => "寄主",
            "positions"       => "地理位置",
            "location_name"   => "地理位置名称",
            "region_name"     => "区域",
            "generation"      => "世代",
            "parasitism_area" => "寄主面积（亩）",
            "begin_time"      => "预计开始时间",
            "end_time"        => "预计结束时间",
            "mild_area"       => "预计发生轻度面积（亩）",
            "moderate_area"   => "预计发生中度面积（亩）",
            "severe_area"     => "预计发生重度面积（亩）",
            "happen_area"     => "发生面积（亩）",
            "disaster_area"   => "预计成灾面积（亩）",
            "moderate_area"   => "预计发生中度面积（亩）",
            "severe_area"     => "预计发生重度面积（亩）",
            "report_name"     => "上报人"
        ];
        return json_encode(["code" => 's_ok', "var" => [$data]]);
    }

    /*已改*/
    //导出
    function exportExcel()
    {
        $data     = $_GET;
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $condition = [];
        if (!empty($data['condition'])) {
            $condition = $data['condition'];//检索条件
            unset($data['condition']);
        } else {
            $condition['region'] = session('staff')['region'];
        }

        $keys  = implode(',', array_keys($data));
        $field = substr($keys, 30);
        $title = array_values($data);
        array_splice($title, 0, 1);

//        print_r($title);//Array ( [0] => 生物种类 [1] => 区域 [2] => 地理位置名称 )
//        print_r($field); //pest_name,region_name,location_name

        $res = ForecastDb::exportls($data, $field, $condition);

        if ($res[0]) {
            $dataRes = $res[1];
            if (empty($dataRes)) {
                return Communal::return_Json(Error::error('未找到相关数据'));
            }
            foreach ($dataRes as $key => $val) {
                unset($val['id']);
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
                $result[] = $val;
            }
            $name = '预测预报信息记录表';
            excelExport($name, $title, $result);
        }
    }
}