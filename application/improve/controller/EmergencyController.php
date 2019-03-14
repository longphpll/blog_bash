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
use app\improve\model\EmergencyDb;
use app\improve\validate\BaseValidate;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/**
 * 应急管理
 */
class EmergencyController extends RedisBase
{
    /*已改 Lxl*/
    function add()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Emergency.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码

        $dbRes = EmergencyDb::add($data);

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
            'ename|事件名称'   => 'max:20',
            'region|区域'    => 'max:20|region'
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

        $result = EmergencyDb::ls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    //详情
    function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));

        $result = $this->validate($data, 'Emergency.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = EmergencyDb::query($data['id']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    function edit()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Emergency.edit');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $dbRes = EmergencyDb::edit($data);

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

        $result = $this->validate($data, 'Emergency.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = EmergencyDb::deleteChecked($data['ids']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    // 已有行政单位查询
    function eventls()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'ename|事件名称' => 'max:6'
        ]);
        if (!array_key_exists("region", $data)) {
            $data['region'] = session('staff')['region'];
        }

        $result = EmergencyDb::eventls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    // 导出字段显示 //导出的数据字段
    function exportList()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        // $dbRes = ItemDb::exportList();
        // dump($dbRes);die;
        $data = [
            "ename"         => "事件名称",
            "positions"     => "地理位置",
            "location_name" => "地理位置名称",
            "region_name"   => "所在区域",
            "level"         => "灾害级别",
            "name"          => "负责人",
            "tel"           => "联系电话",
            "workunit"      => "工作单位",
            "eduty"         => "应急职务",
            "create_time"   => "上报时间",
            "begintime"     => "事件启动时间",
            "overtime"      => "事件结束时间",
            "beginunit"     => "启动单位",
            "emeasure"      => "应急处理措施",
            "elog"          => "应急工作日志",
            "esummarize"    => "应急工作总结"
        ];
        return json_encode(["code" => 's_ok', "var" => [$data]]);
    }

    /*已改*/
    //导出 //应急事件导出
    function exportExcel()
    {
        $data = $_GET;

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
        $field = substr($keys, 31);
        $title = array_values($data);
        array_splice($title, 0, 1);

//        print_r($title);//Array ( [0] => 事件名称 [1] => 地理位置 [2] => 地理位置名称 )
//        print_r($field);die; //ename,positions,location_name

        $res = EmergencyDb::exportls($data, $field, $condition);

        if ($res[0]) {
            $result = $res[1];
            if (empty($result)) {
                return Communal::return_Json(Error::error('未找到相关数据'));
            }
            $name = '应急事件记录表';
            excelExport($name, $title, $result);
        }
    }
}