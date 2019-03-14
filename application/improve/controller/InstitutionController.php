<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2018/3/10
 * Time: 10:38
 *
 */

namespace app\improve\controller;

use think\Controller;
use app\improve\model\InstitutionDb;
use app\improve\validate\BaseValidate;
use app\improve\model\BaseDb as BaseDbModel;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/**
 * 森防机构
 */
class InstitutionController extends RedisBase
{
    /*已改 Lxl*/
    function add()
    {
        //只有管理员可以执行添加操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Institution.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码

        $dbRes = InstitutionDb::add($data);

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
            'per_page'         => 'require|number|max:50|min:1',
            'current_page'     => 'require|number|min:1',
            'region|区域'        => 'max:20|region',
            'designation|单位名称' => 'max:20',
            'nature|机构性质'      => 'in:1,2',
            'level|机构类型'       => 'in:1,2,3'
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

        $result = InstitutionDb::ls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Institution.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = InstitutionDb::query($data['id']);

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

        $result = $this->validate($data, 'Institution.edit');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        if (!array_key_exists("region", $data)) {
            $data['region'] = session('staff')['region'];
        }

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码

        $dbRes = InstitutionDb::edit($data);

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

        $result = $this->validate($data, 'Institution.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = InstitutionDb::deleteChecked($data['ids']);

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
            "designation" => "单位名称",
            "region_name" => "所在区域",
            "nature"      => "机构性质",
            "level"       => "机构类型",
            "totality"    => "编制人数",
            "working"     => "在岗人数",
            "name"        => "负责人",
            "tel"         => "联系号码",
            "remark"      => "备注"
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
        $field = substr($keys, 33);
        $title = array_values($data);
        array_splice($title, 0, 1);

//        print_r($title); //Array ( [0] => 单位名称 [1] => 机构性质 [2] => 机构类型 [3] => 所在区域 )
//        print_r($field);
//        die; //designation,nature,level,region_name

        $res = InstitutionDb::exportls($data, $field, $condition);

        if ($res[0]) {
            $dataRes = $res[1];
            if (empty($dataRes)) {
                return Communal::return_Json(Error::error('未找到相关数据'));
            }
            foreach ($dataRes as $key => $val) {
                if (!empty($val['nature'])) {
                    switch ($val['nature']) {
                        case "1":
                            $val['nature'] = "事业";
                            break;
                        case "2":
                            $val['nature'] = "非事业";
                            break;
                    }
                }
                if (!empty($val['level'])) {
                    switch ($val['level']) {
                        case "1":
                            $val['level'] = "省级";
                            break;
                        case "2":
                            $val['level'] = "地级";
                            break;
                        case "3":
                            $val['level'] = "县级";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '森防机构记录表';
            excelExport($name, $title, $result);
        }
    }
}