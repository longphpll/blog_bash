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
use app\improve\model\MoneyintoDb;
use app\improve\model\UserDb;
use app\improve\model\BaseDb as BaseDbModel;
use app\improve\validate\BaseValidate;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/**
 * 资金投入管理
 */
class MoneyintoController extends RedisBase
{
    /*已改 Lxl*/
    function add()
    {
        //只有管理员可以执行添加操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Moneyinto.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);

        $dbRes = MoneyintoDb::add($data);

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

        $result = MoneyintoDb::ls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Moneyinto.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = MoneyintoDb::query($data['id']);

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

        $result = $this->validate($data, 'Moneyinto.edit');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);

        $dbRes = MoneyintoDb::edit($data);

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

        $result = $this->validate($data, 'Moneyinto.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = MoneyintoDb::deleteChecked($data['ids']);

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
            "unit"        => "行政单位",
            "region_name" => "区域",
            "years"       => "年度",
            "financial"   => "财政资金(万)",
            "society"     => "社会投入(万)",
            "budget"      => "预算内投入(万)"
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
        $field = substr($keys, 31);
        $title = array_values($data);
        array_splice($title, 0, 1);

//        print_r($title);//Array ( [0] => 行政单位 [1] => 区域 [2] => 年度 )
//        print_r($field);//unit,region_name,years
//        die;

        $res = MoneyintoDb::exportls($data, $field, $condition);

        if ($res[0]) {
            $result = $res[1];
            if (empty($result)) {
                return json_encode(["code" => 'error', "var" => ['未找到数据']]);
            }
            $name = '资金投入记录表';
            excelExport($name, $title, $result);
        }
    }

}