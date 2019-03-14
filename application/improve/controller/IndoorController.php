<?php
/**
 * Created by qiumu.
 * User: Administrator
 * Date: 2017/12/13 0013
 * Time: 10:50
 */

namespace app\improve\controller;

use app\improve\model\IndoorDb;
use app\improve\model\StatisticsDb;
use app\improve\model\BaseDb as BaseDbModel;
use app\improve\validate\BaseValidate;
use think\Controller;
use think\Loader;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/*
 * 松材线虫病调查--室内监测
 */

class IndoorController extends RedisBase
{
    /*已改 Lxl*/
    //添加
    function add()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Indoor.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = $data['create_time'];
        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码
        $images              = request()->file("images");

        $dbRes = IndoorDb::add($data, $images);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //列表
    function ls()
    {
        //所有用户都可以进行查询操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'per_page'          => 'require|number|max:50|min:1',
            'current_page'      => 'require|number|min:1',
            'region|区域'         => 'max:20|number',
            'name|鉴定人姓名'        => 'max:6|chs',
            'begin_time|鉴定开始时间' => 'date',
            'end_time|鉴定结束时间'   => 'date'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = IndoorDb::ls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    //详情 根据id查看
    function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Indoor.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = IndoorDb::query($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //编辑
    function edit()
    {
        //只有管理员可以执行编辑操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Indoor.edit');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        //判断权限,
        //先根据id 从表 松材线虫病调查--室内监测表 improve_pineline_indoor 查询是否有该条记录
        $adder = IndoorDb::adder($data['id']); //adder 87ef035ebb54db09b6af0d886a1b5091
        if (!$adder[0]) return Communal::return_Json($adder);

//        查添加人是不是自己或者自己是管理员
//        $a = Helper::checkAdderOrManage($adder[1]['adder'], $auth[1]['s_uid']);
//        if (!$a[0]) return Helper::reJson($a);

        //不用上面的来判断是否是本人或者管理员,用下面的直接判断即可
        if (!(($checkout[1]->rid != 3) || ($adder[1]["adder"] != $checkout[1]->uid))) {
            return Communal::return_Json(Error::error('你不是管理,也不是本人,无权修改'));
        }

        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码
        $images              = request()->file("images");

        $dbRes = IndoorDb::edit($data, $images);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //删除选中
    function deleteChecked()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Indoor.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = IndoorDb::deleteChecked($data['ids']);

        return Communal::return_Json($dbRes);
    }


    /*已改*/
    //导出字段显示
    function exportList()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $data = [
            "number"        => "编号",
            "region_name"   => "区域",
            "positions"     => "地图位置",
            "location_name" => "地图位置名称",
            "sampling_part" => "取样部位",
            "results"       => "鉴定结果",
            "appraiser"     => "鉴定人",
            "reviewer"      => "复检人",
            "create_time"   => "鉴定时间",
            "img"           => "图片"
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

        if (isset($data['img'])) {
            $img = true;
            unset($data['img']);
        } else {
            $img = false;
        }

        $keys  = implode(',', array_keys($data));
        $field = substr($keys, 28);
        $title = array_values($data);
        array_splice($title, 0, 1);

//        print_r($title);//Array ( [0] => 样本编号 [1] => 地理位置名称 [2] => 取样部位 )
//        print_r($field);die;//number,region_name,sampling_part

        $res = IndoorDb::exportls($data, $field, $img, $condition);

        if ($res[0]) {
            $dataRes = $res[1];
            if (empty($dataRes)) {
                return Communal::return_Json(Error::error('未找到相关数据'));
            }
            foreach ($dataRes as $key => $val) {
                unset($val['id']);
                if (!empty($val['sampling_part'])) {
                    switch ($val['sampling_part']) {
                        case "1":
                            $val['sampling_part'] = "上";
                            break;
                        case "2":
                            $val['sampling_part'] = "中";
                            break;
                        case "3":
                            $val['sampling_part'] = "下";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '室内监测调查记录表';
            excelExport($name, $title, $result);
        }
    }
}