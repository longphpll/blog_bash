<?php
/**
 * Created by qiumu.
 * User: Administrator
 * Date: 2017/12/28 
 * Time: 11:32
 */
namespace app\improve\controller;

use think\Controller;
use app\improve\model\StatisticsDb;
use app\improve\model\RegularlyDb;
use app\improve\model\BaseDb;
use app\improve\validate\BaseValidate;
use tool\Communal;
use tool\Error;
use tool\BaseDb as ToolBaseDb;
use base_frame\RedisBase;

/*
 * 固定标准地管理
 */
 
class RegularlyController extends RedisBase
{
    /** 固定标准地信息新增(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.09
     * 修改内容：权限重写
    */
    public function add(){
//        $auth = Helper::auth([1]);
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        $result = $this->validate($data, 'Regularly.add');
//        if ($result !== true) return Helper::reJson(Errors::Error($result));
//        $region_result = Helper::authRegion($data);
//        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
//        $data['adder'] = $auth[1]['s_uid'];
//        $data['region_name'] = BaseDb::areaName($data['region']);
//        $data['pests_name']  = BaseDb::pest($data['pests']);
//        $data['plant_name']  = BaseDb::plant($data['plant']);
//        $dbRes = RegularlyDb::add($data);
//        return Helper::reJson($dbRes);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did'],1);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数请求验证
        $result = $this->validate($data, 'Quarantine.add');
        if($result !== true) return  Communal::return_Json(Error::validateError($result));
        //区域验证
        $regionVerify=Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        //追加参数
        $data['adder'] = $checkout[1]->uid;
//        $data['region_name'] = BaseDb::areaName($data['region']);
        $data['region_name'] = ToolBaseDb::regionName($data['region']);
        $data['pests_name']  = BaseDb::pest($data['pests']);
        $data['plant_name']  = BaseDb::plant($data['plant']);
        //调起模型执行添加操作
        $dbRes = RegularlyDb::add($data);
        return Communal::return_Json($dbRes);
    }

    /** 固定标准地信息列表(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.09
     * 修改内容：权限重写
     */
    function ls($sample = false,$download = false)
    {
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = $download ? $_GET : Helper::getPostJson();
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数请求验证
        $validate = new BaseValidate([
            'per_page' =>'require|number|between:1,50',
            'current_page' =>'require|number|min:1',
            'region|区域' => 'max:6',
            'number|标准地编号' => 'max:20',
            'pests|有害生物种类' => 'number',
            'plant|寄主树种' => 'number'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));
        //区域验证(带片区搜索的情形使用)
        if( (array_key_exists("region", $data) && empty($data['region']) || !array_key_exists("region", $data) ))
        {
            $data['region'] = $checkout[1]->region;
        }else{
            //如果有写入片区搜索，执行片区验证
            $regionVerify = Communal::regionVerify($data,$checkout[1]);
            if(!$regionVerify){
                return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
            }
        }
//        $region_result = Helper::authRegion($data);
//        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $result = RegularlyDb::ls($data,$sample);
        return Communal::return_Json($result);
    }
	
	//总体概况-固定标准地信息
    function sampleMap()
    {
       return $this->ls(true);
    }

    /** 固定标准地信息详情()
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function query()
    {
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        $result = $this->validate($data, 'Regularly.id');
//        if ($result !== true) return Helper::reJson(Errors::Error($result));
//        $dbRes = RegularlyDb::query($data['id']);
//        return Helper::reJson($dbRes);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $result = $this->validate($data, 'Regularly.id');
        if ($result !== true) return Communal::return_Json(Error::error($result));
        $dbRes = RegularlyDb::query($data['id']);
        return Communal::return_Json($dbRes);
    }

    /** 固定标准地信息编辑(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function edit()
    {
//        $auth = Helper::auth([1]);
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        $result = $this->validate($data, 'Regularly.edit');
//        if ($result !== true) return Helper::reJson(Errors::Error($result));
//        $data['adder'] = $auth[1]['s_uid'];
//        $data['region_name'] = BaseDb::areaName($data['region']);
//        $dbRes = RegularlyDb::edit($data);
//        return Helper::reJson($dbRes);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数验证
        $result = $this->validate($data, 'Regularly.edit');
        if ($result !== true) return Communal::return_Json(Error::error($result));
        //区域验证
        $regionVerify = Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        //参数追加
        $data['adder'] = $checkout[1]->uid;
        $data['region_name'] = ToolBaseDb::regionName($data['region']);
        //调起模型执行执行数据操作
        $dbRes = RegularlyDb::edit($data);
        return Communal::return_Json($dbRes);
    }

    /** 固定标准地信息删除(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function deleteChecked()
    {
//        $auth = Helper::auth([1]);
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        $result = $this->validate($data, 'Regularly.ids');
//        if ($result !== true) return Helper::reJson(Errors::Error($result));
//        $dbRes = RegularlyDb::delete($data['ids']);
//        return Helper::reJson($dbRes);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数验证
        $result = $this->validate($data, 'Regularly.ids');
        if ($result !== true) return Communal::return_Json(Error::error($result));
        //调起模型
        $dbRes = RegularlyDb::delete($data['ids']);
        return Communal::return_Json($dbRes);
    }
	
	//标准地信息历史对比图
    function history(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'region|区域' => 'require|region',
            'pest|有害生物' => 'require|number',
            'start_time|开始年份' => 'require|dateFormat:Y|<=:end_time',
            'end_time|结束年份' => 'require|dateFormat:Y|>=:start_time'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $result = RegularlyDb::history($data);
        return Helper::reJson($result);
    }

    //已有生物种类查询
    /** 固定标准地信息-已有生物种类查询(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function pestList(){
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        if(!array_key_exists("region", $data))
//        {
//            $data['region'] = cookie('s_region');
//        }
//        $region_result = Helper::authRegion($data);
//        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
//        $result = RegularlyDb::pestList($data);
//        return Helper::reJson($result);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //区域验证(带片区搜索的情形使用)
        if( (array_key_exists("region", $data) && empty($data['region']) || !array_key_exists("region", $data) ))
        {
            $data['region'] = $checkout[1]->region;
        }else{
            //如果有写入片区搜索，执行片区验证
            $regionVerify = Communal::regionVerify($data,$checkout[1]);
            if(!$regionVerify){
                return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
            }
        }
        $result = RegularlyDb::pestList($data);
        return Communal::return_Json($result);
    }

    //已有寄主树种查询
    /** 固定标准地信息-已有寄主树种查询(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function plantList(){
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数验证
        $validate = new BaseValidate([
            'id|生物id' => 'require|number'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));
        //区域验证(带片区搜索的情形使用)
        if( (array_key_exists("region", $data) && empty($data['region']) || !array_key_exists("region", $data) ))
        {
            $data['region'] = $checkout[1]->region;
        }else{
            //如果有写入片区搜索，执行片区验证
            $regionVerify = Communal::regionVerify($data,$checkout[1]);
            if(!$regionVerify){
                return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
            }
        }
        //调起模型
        $result = RegularlyDb::plantList($data);
        return Communal::return_Json($result);
    }

    // 导出字段显示
    /** 固定标准地信息-导出字段显示()
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function exportList(){
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $data = [
            "number" => "固定标准地编号",
            "positions" => "地理位置",
            "location_name" => "地理位置名称",
            "region_name" => "区域",
            "type" => "生物类型",
            "pests_name" => "生物种类名称",
            "plant_name" => "寄主名称",
            "regularly_area" => "标准地面积（亩）",
            "stand_composition" => "林分组成",
            "stand_area" => "林分面积（亩）",
            "forest_age" => "林龄",
            "coverage" => "植被覆盖度"
        ];
        return json_encode(["code" => 's_ok',"var" => [$data]]);
    }

    //导出
    function exportExcel(){
        $data = $_GET;
        $condition=[];
        if(!empty($data['condition'])){
            $condition = $data['condition'];//检索条件
            unset($data['condition']);
        }else{
            $condition['region'] = cookie('s_region'); 
        }
        $keys = implode(',',array_keys($data));
        $field = substr($keys,31);
        $title = array_values($data);
        array_splice($title, 0, 1);
        $res = RegularlyDb::exportls($data,$field,$condition);
        if ($res[0]){
            $dataRes = $res[1];
            if(empty($dataRes)){
                return json_encode(["code" => 'error',"var" => ['未找到数据']]); 
            }
            foreach ($dataRes as $key => $val) {
                if (!empty($val['type'])){
                    switch ($val['type'])
                    {
                        case "1":$val['type'] = "虫害";
                            break;
                        case "2":$val['type'] = "病害";
                            break;
                        case "3":$val['type'] = "有害植物";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '固定标准地信息记录表';
            excelExport($name, $title, $result);
        }
    }

}