<?php
/**
 * Created by qiumu.
 * User: Administrator
 * Date: 2017/12/13 0013
 * Time: 10:50
 */

namespace app\improve\controller;

use app\improve\model\MaintainDb;
use app\improve\model\BaseDb as BaseDbModel;
use app\improve\validate\BaseValidate;
use think\Controller;
use think\Loader;
use think\Validate;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/*
 * 诱捕器维护数据
 */

class MaintainController extends RedisBase
{
    /*已改 Lxl*/
    // 添加-普通用户权限 添加-移动端
    function add()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Maintain.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);
        $images              = request()->file("images");

        $dbRes = MaintainDb::add($data, $images);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //根据id查看
    function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Maintain.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = MaintainDb::query($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    // 移动端-查看诱捕器信息 诱捕器设备详情
    function tarpQuery()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $dbRes = MaintainDb::tarpQuery($data['number']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    // 编辑-普通用户权限 编辑-移动端
    function edit()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Maintain.edit');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $adder               = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);
        $data['adder_name']  = $checkout[1]->name;
        $images              = request()->file("images");

        $dbRes = MaintainDb::edit($data, $images, $adder);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    // web列表
    function ls()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $adder = $checkout[1]->uid;
        $rid   = $checkout[1]->rid;

        $validate = new BaseValidate([
            'region|区域'            => 'max:20',
            'trap_number|诱捕器编号'    => 'max:20',
            'maintain_number|维护编号' => 'max:20',
            'date|维护日期'            => 'dateFormat: Y-m-d'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = MaintainDb::ls($data, $rid, $adder);

        return Communal::return_Json($result);
    }

    /*已改*/
    //列表 app
    function maintainls()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $adder = $checkout[1]->uid;
        $rid   = $checkout[1]->rid;

        $validate = new BaseValidate([
            'region|区域'            => 'max:20',
            'trap_number|诱捕器编号'    => 'max:20',
            'maintain_number|维护编号' => 'max:20',
            'date|维护日期'            => 'dateFormat: Y-m-d'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = MaintainDb::maintainls($data, $rid, $adder);

        return Communal::return_Json($result);
    }

    /*已改*/
    // 已有录入人查询-管理员权限 已有录入人查询
    function userls()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'name|录入人' => 'max:6'
        ]);

        $result = MaintainDb::userls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    // 已有使用的诱捕器编号查询-管理员权限 已使用诱捕器编号查询
    function trapls()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'number|诱捕器编号' => 'max:20'
        ]);

        $result = MaintainDb::trapls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    // 历史维护记录列表 维护数据历史记录列表
    function historyls()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'date|维护日期' => 'dateFormat:Y-m-d',
            'id|维护记录id' => 'require|number'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = MaintainDb::historyls($data);

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
            "trap_number"      => "诱捕器编号",
            "maintain_number"  => "维护编号",
            "region_name"      => "区域",
            "positions"        => "地理位置",
            "maintenance_date" => "维护日期",
            "female_number"    => "雌虫量",
            "male_number"      => "雄虫量",
            "total"            => "本期诱捕总量",
            "drug_model"       => "药剂型号",
            "remarks"          => "备注",
            "adder_name"       => "录入人",
            "create_time"      => "录入时间"
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

//        print_r($title);//Array ( [0] => 区域 [1] => 诱捕器编号 )
//        print_r($field);die;//region_name,trap_number

        $res = MaintainDb::exportls($data, $field, $condition);

        if ($res[0]) {
            $result = $res[1];
            if (empty($result)) {
                return Communal::return_Json(Error::error('未找到相关数据'));
            }
            $name = '诱捕器维护数据记录表';
            excelExport($name, $title, $result);
        }
    }

}