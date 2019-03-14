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
use app\improve\model\ImplementDb;
use app\improve\validate\BaseValidate;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/**
 * 实施单位基本情况
 */
class ImplementController extends RedisBase
{
    /*已改 Lxl*/
    function add()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Implement.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码

        $dbRes = ImplementDb::add($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //列表
    function ls($sample = false)
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'per_page'     => 'require|number|max:50|min:1',
            'current_page' => 'require|number|min:1',
            'name|项目名称'    => 'max:20',
            'person|项目负责人' => 'max:10'
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

        $result = ImplementDb::ls($data, $sample);

        return Communal::return_Json($result);
    }

    /*直接调用 ls() 方法*/
    //总体概况--实施单位基本情况
    function sampleMap()
    {
        return $this->ls(true);
    }

    /*已改*/
    //详情
    function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Implement.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = ImplementDb::query($data['id']);

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

        $result = $this->validate($data, 'Implement.edit');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码

        $dbRes = ImplementDb::edit($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //删除
    function deleteChecked()
    {
        //只有管理员可以执行删除操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Implement.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = ImplementDb::deleteChecked($data['ids']);

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
            "name"           => "项目名称",
            "unit"           => "项目实施单位",
            "nature"         => "项目性质",
            "person"         => "项目负责人",
            "region_name"    => "项目建设区域",
            "positions"      => "地图位置",
            "location_name"  => "地图位置名称",
            "middle_price"   => "中央投资（万元）",
            "province_price" => "省投资（万元）",
            "place_price"    => "地方配套（万元）",
            "sum_price"      => "总投资（万元）",
            "plan"           => "项目建设进度",
            "content"        => "项目建设内容",
            "note"           => "备注"
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

//        print_r($title);//Array ( [0] => 项目名称 [1] => 单位 [2] => 项目性质 [3] => 项目建设区域 )
//        print_r($field);die;// name,unit,nature,region_name

        $res = ImplementDb::exportls($data, $field, $condition);

        if ($res[0]) {
            $dataRes = $res[1];
            if (empty($dataRes)) {
                return Communal::return_Json(Error::error('未找到相关数据'));
            }
            foreach ($dataRes as $key => $val) {
                if (!empty($val['nature'])) {
                    switch ($val['nature']) {
                        case "1":
                            $val['nature'] = "国家项目";
                            break;
                        case "2":
                            $val['nature'] = "省级项目";
                            break;
                        case "3":
                            $val['hazard_type'] = "县级项目";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '实施单位基本情况记录表';
            excelExport($name, $title, $result);
        }
    }
}