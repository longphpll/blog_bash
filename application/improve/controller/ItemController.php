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
use app\improve\model\ItemDb;
use app\improve\validate\BaseValidate;
use app\improve\Controller\ShowProduct;
use app\improve\Controller\Shop;
use app\improve\Controller\CdProduct;
use app\improve\Controller\BookProduct;
use app\improve\Wrong\Wrong;
use app\improve\Controller\ShopProduct;


use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/**
 * 项目基本信息  项目管理
 */
class ItemController extends RedisBase
{
    /*已改 Lxl*/
    function add()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Item.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);//区域编码

        $dbRes = ItemDb::add($data);

        return Communal::return_Json($dbRes);
        // $shopProduct = new ShopProduct("魅族zero 正式发布了","魅族zero",3999);
        // $writer = new ShopProductWriter();
        // dump($writer->setWriter('学习面向对象编程！'));
        // // $cd = new CdProduct('行走的cd','魔鬼中的天使',155,'2:35');
        // // $cdPlayLength = $cd->getPlayLength();
        // // echo $cdPlayLength;
        // // $cdInfo = $cd->getProductInfo();
        // // echo $cdInfo;
        // // $book = new BookProduct('平凡的世界 一个不一样的世界','平凡的世界',87,255);
        // // $bookInfo = $book->getProductInfo();
        // // echo $bookInfo;

        // $product = new ShopProduct('深入了解PHP','PHP学习',79);
        // $discout = $product->setDiscount(20);
        // $nowPrice = $product->getPrice();
        // echo $nowPrice;
    }

    /*已改*/
    function ls($sample = false)
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'per_page'            => 'require|number|max:100|min:1',
            'current_page'        => 'require|number|min:1',
            'name|项目名称'           => 'max:20',
            'person|项目法人'         => 'max:10',
            'begin_time|项目建设开始期限' => 'dateFormat:Y-m-d',
            'end_time|项目建设结束期限'   => 'dateFormat:Y-m-d'
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

        $result = ItemDb::ls($data, $sample);

        return Communal::return_Json($result);
    }

    /*已改*/
    //总体概况--项目信息
    function sampleMap()
    {
        return $this->ls(true);
    }

    /*已改*/
    function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Item.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = ItemDb::query($data['id']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    function edit()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Item.edit');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $regionVerify = Communal::regionVerify($data, $checkout[1]);
        if (!$regionVerify) {
            //您无权操作该区域数据
            return Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }

        $data['adder']       = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);
        $dbRes               = ItemDb::edit($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    function deleteChecked()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Item.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = ItemDb::deleteChecked($data['ids']);

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
            "unit"           => "项目单位",
            "nature"         => "项目性质",
            "person"         => "项目法人",
            "begin_time"     => "项目开始建设时间",
            "end_time"       => "项目结束建设时间",
            "region_name"    => "项目建设区域",
            "positions"      => "地图位置",
            "location_name"  => "地理位置名称",
            "reply"          => "项目立项批复",
            "reply_time"     => "项目立项批复时间",
            "work"           => "作业设计立项批复",
            "work_time"      => "作业设计立项批复时间",
            "middle_price"   => "中央投资（万元）",
            "province_price" => "省投资（万元）",
            "place_price"    => "地方配套（万元）",
            "sum_price"      => "总投资（万元）",
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
        $field = substr($keys, 26);
        $title = array_values($data);
        array_splice($title, 0, 1);

//        print_r($title);//Array ( [0] => 项目名称 [1] => 单位 [2] => 项目法人 [3] => 项目开始建设时间 [4] => 项目结束建设时间 [5] => 项目建设区域 )
//        print_R($field);die;//name,unit,person,begin_time,end_time,region_name
        $res = ItemDb::exportls($data, $field, $condition);

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
                            $val['nature'] = "县级项目";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '项目管理记录表';
            excelExport($name, $title, $result);
        }
    }
}