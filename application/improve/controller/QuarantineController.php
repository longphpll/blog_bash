<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/5 
 * Time: 14:24
 */

namespace app\improve\controller;

use app\improve\model\QuarantineDb;
use app\improve\validate\BaseValidate;
use tool\Communal;
use tool\Error;
use tool\BaseDb;
use base_frame\RedisBase;

/*
 * 检疫检查站管理
 */
 
class QuarantineController extends RedisBase
{
    /** 检疫站新增
     * 修改人:余思渡
     * 修改日期：2019.03.07
     * 修改内容: 权限重写
    */
    public function add(){
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
        if(array_key_exists('region',$data))
        {
            $data['region_name'] = BaseDb::regionName($data['region']);
        }
        $data['adder'] = $checkout[1]->uid;
        $dbRes = QuarantineDb::add($data);
        return Communal::return_Json($dbRes);
    }

    /** 检疫站列表
     * 修改人:余思渡
     * 修改日期：2019.03.07
     * 修改内容: 权限+验证重写
    */
    public function ls($sample = false){
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数请求验证
        $validate = new BaseValidate([
            'per_page' =>'require|number|between:1,50',
            'current_page' =>'require|number|min:1',
            'region|区域' => 'max:20',
            'organization|检疫站名称' => 'max:20'
        ]);
        if (!$validate->check($data)){
            $error = Error::error($validate->getError());
            return Communal::return_Json($error);
        }
        //区域验证
        if(!array_key_exists("region", $data))
        {
            $data['region'] = $checkout[1]->region;
        }else{
            //如果有写入片区搜索，执行片区验证
            $regionVerify = Communal::regionVerify($data,$checkout[1]);
            if(!$regionVerify){
                return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
            }
        }
        $result = QuarantineDb::ls($data,$sample);
        return Communal::return_Json($result);
    }

    //总体概况--检疫站信息
    public function sampleMap(){
        return $this->ls(true);
    }

    /** 检疫站查站详情 (已改)
     * 修改人:余思渡
     * 修改日期：2019.03.07
     * 修改内容: 权限+验证重写
    */
    public function query(){
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数请求验证
        $result = $this->validate($data, 'Quarantine.query');
        if($result !== true) return  Communal::return_Json(Error::validateError($result));
        $dbRes = QuarantineDb::query($data['id']);
        return Communal::return_Json($dbRes);
    }

    /** 检疫站查站编辑 (已改)
     * 修改人:余思渡
     * 修改日期：2019.03.07
     * 修改内容: 权限+验证重写
    */
    public function edit(){
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did'],1);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数请求验证
        $result = $this->validate($data, 'Quarantine.edit');
        if($result !== true) return  Communal::return_Json(Error::validateError($result));
        //区域验证
        $regionVerify=Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        //追加参数
        if(array_key_exists('region',$data))
        {
            $data['region_name'] = BaseDb::regionName($data['region']);
        }
        $data['adder'] = $checkout[1]->uid;
        $dbRes = QuarantineDb::edit($data);return json($dbRes);
        return Communal::return_Json($dbRes);
    }

    /** 检疫站查站删除 (已改)
     * 修改人:余思渡
     * 修改日期：2019.03.07
     * 修改内容: 权限+验证重写
    */
    function deletechecked(){
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did'],1);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数请求验证
        $result = $this->validate($data, 'Quarantine.ids');
        if($result !== true) return  Communal::return_Json(Error::validateError($result));
        //调起模型操作数据
        $dbRes = QuarantineDb::deleteChecked($data['ids']);
        return  Communal::return_Json($dbRes);
    }

    // 导出字段显示
    function exportList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = [
            "organization" => "检疫站名称",
            "nature" => "建筑性质",
            "positions" => "地理位置",
            "location_name" => "地理位置名称",
            "region_name" => "所在区域",
            "found_time" => "建站时间",
            "administrator" => "管理者",
            "tel" => "电话号码"
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
        $field = substr($keys,32);
        $title = array_values($data);
        array_splice($title, 0, 1);
        $res = QuarantineDb::exportls($data,$field,$condition);
        if ($res[0]){
            $dataRes = $res[1];
            if(empty($dataRes)){
                return json_encode(["code" => 'error',"var" => ['未找到数据']]); 
            }
            foreach ($dataRes as $key => $val) {
                if (!empty($val['nature'])){
                    switch ($val['nature'])
                    {
                        case "0":$val['nature'] = "无";
                            break;
                        case "1":$val['nature'] = "临时";
                            break;
                        case "2":$val['nature'] = "长期";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '检疫站记录表';
            excelExport($name, $title, $result);
        }
    }
}