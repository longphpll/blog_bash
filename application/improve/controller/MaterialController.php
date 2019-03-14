<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/16
 * Time: 16:12
 */

namespace app\improve\controller;

use think\Controller;
use app\improve\model\BaseDb as BaseDbModel;
use app\improve\model\MaterialDb;
use app\improve\model\UserDb;
use app\improve\validate\BaseValidate;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/**
 * 物料管理
 */
class MaterialController extends RedisBase
{
    /*已改 Lxl*/
    function add()
    {
        //只有管理员可以执行添加操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Material.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码

        $dbRes = MaterialDb::add($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    function ls()
    {
        //所有用户都可以进行查询操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'per_page'     => 'require|number|max:50|min:1',
            'current_page' => 'require|number|min:1',
            'region'       => 'max:20|region',
            'name|设备名称'    => 'max:50',
            'unit|行政单位'    => 'max:50'
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

        $result = MaterialDb::ls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Material.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = MaterialDb::query($data['id']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    function edit()
    {
        //只有管理员可以执行编辑操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Material.edit');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);

        $dbRes = MaterialDb::edit($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    // 删除选中
    function deleteChecked()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Material.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = MaterialDb::deleteChecked($data['ids']);

        return Communal::return_Json($dbRes);
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
            "name"        => "设备名称",
            "version"     => "型号",
            "measure"     => "计量单位",
            "amount"      => "数量",
            "price"       => "单价",
            "unit"        => "行政单位",
            "region_name" => "区域"
        ];
        return json_encode(["code" => 's_ok', "var" => [$data]]);
    }

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

//        print_r($title);//Array ( [0] => 设备名称 [1] => 型号 [2] => 数量 )
//        print_r($field);die;//name,version,amount

        $res = MaterialDb::exportls($data, $field, $condition);

        if ($res[0]) {
            $result = $res[1];
            if (empty($result)) {
                return Communal::return_Json(Error::error('未找到相关数据'));
            }
            $name = '物料记录表';
            excelExport($name, $title, $result);
        }
    }
}