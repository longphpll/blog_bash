<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/10
 * Time: 10:38
 *
 */

namespace app\improve\controller;

use think\Controller;
use app\improve\model\BaseDb as BaseDbModel;
use app\improve\model\PesticideDb;
use app\improve\validate\BaseValidate;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/**
 * 农药使用情况管理
 */
class PesticideController extends RedisBase
{
    /*已改 Lxl*/
    function add()
    {
        //只有管理员可以执行添加操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Pesticide.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);

        $dbRes = PesticideDb::add($data);

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
            'region|区域'    => 'max:20|region',
            'unit|行政单位'    => 'max:50',
            'years|年度'     => 'max:4|dateFormat:Y'
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

        $result = PesticideDb::ls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Pesticide.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = PesticideDb::query($data['id']);

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

        $result = $this->validate($data, 'Pesticide.edit');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);

        $dbRes = PesticideDb::edit($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    function deleteChecked()
    {
        //只有管理员可以执行删除操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Pesticide.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = PesticideDb::deleteChecked($data['ids']);

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
            "unit"         => "行政单位",
            "region_name"  => "区域",
            "years"        => "年度",
            "naturals"     => "天敌生物（亿头）",
            "biochemistry" => "生物化学农药（千克）",
            "chemistry"    => "化学农药（千克）",
            "germ"         => "微生物农药（千克）",
            "chemistry"    => "化学农药（千克）"


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

//        print_r($title); //Array ( [0] => 行政单位 [1] => 区域 [2] => 年度 )
//        print_r($field);//unit,region_name,years
//        die;

        $res = PesticideDb::exportls($data, $field, $condition);

        if ($res[0]) {
            $result = $res[1];
            if (empty($result)) {
//                return json_encode(["code" => 'error', "var" => ['未找到数据']]);
                return Communal::return_Json(Error::error('未找到数据'));
            }
            $name = '农药使用情况记录表';
            excelExport($name, $title, $result);
        }
    }
}