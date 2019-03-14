<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/10
 * Time: 10:38
 */

namespace app\improve\controller;

use app\improve\model\QuarantineInspectorDb;
use app\improve\validate\BaseValidate;
use tool\Communal;
use tool\Error;
use tool\BaseDb;
use base_frame\RedisBase;

/*
 * 检疫员管理
 */

class QuarantineinspectorController extends RedisBase
{
    /** 检疫站新增 已改
     * 修改人:余思渡
     * 修改日期：2019.03.07
     * 修改内容: 权限重写
    */
    function add(){
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
        $data['adder'] = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);
        $dbRes = QuarantineInspectorDb::add($data);
        return Communal::return_Json($dbRes);
    }

    /** 检疫站列表 已改
     * 修改人:余思渡
     * 修改日期：2019.03.07
     * 修改内容: 权限重写
    */
    function ls(){
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数效验
        $validate = new BaseValidate([
            'per_page' =>'require|number|between:1,50',
            'current_page' =>'require|number|min:1',
            'type|类别' => 'in:1,2',
            'name|姓名' => 'max:15',
            'region|区域' => 'number|max:6',
        ]);
        if (!$validate->check($data)){
            $error = Error::error($validate->getError());
            return Communal::return_Json($error);
        }
        if(!array_key_exists("region", $data) || empty($data['region'])) {
            //如果没有写入片区搜索，则默认写入账号的片区ID
            $data['region'] = $checkout[1]->region;
        }else{
            //如果有写入片区搜索，执行片区验证
            $regionVerify = Communal::regionVerify($data,$checkout[1]);
            if(!$regionVerify){
                return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
            }
        }
        $result = QuarantineInspectorDb::ls($data);
        return Communal::return_Json($result);
    }

    /** 检疫站详情 已改
     * 修改人:余思渡
     * 修改日期：2019.03.08
     * 修改内容: 权限重写
    */
    function query(){
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数请求验证
        $result = $this->validate($data, 'QuarantineInspector.query');
        if($result !== true) return  Communal::return_Json(Error::validateError($result));
        //区域验证
        $regionVerify=Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        $data['adder'] = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);
        $dbRes = QuarantineInspectorDb::query($data['id']);
        return Communal::return_Json($dbRes);
    }

    /** 检疫站编辑 已改
     * 修改人:余思渡
     * 修改日期：2019.03.08
     * 修改内容: 权限重写
    */
    function edit(){
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did'],1);
        //return json($checkout[1]);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数效验
        $result = $this->validate($data, 'QuarantineInspector.edit');
        if($result !== true) return  Communal::return_Json(Error::validateError($result));
        if(!array_key_exists("region", $data))
        {
            //如果没有写入片区搜索，则默认写入账号的片区ID
            $data['region'] = $checkout[1]->region;
        }else{
            //如果有写入片区搜索，执行片区验证
            $regionVerify = Communal::regionVerify($data,$checkout[1]);
            if(!$regionVerify){
                return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
            }
        }
        $dbRes = QuarantineInspectorDb::edit($data);
        return Communal::return_Json($dbRes);
    }

    /** 检疫站删除 已改
     * 修改人:余思渡
     * 修改日期：2019.03.08
     * 修改内容: 权限重写
    */
    function deleteChecked(){
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did'],1);
        //return json($checkout[1]);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数效验
        $result = $this->validate($data, 'QuarantineInspector.ids');
        if($result !== true) return  Communal::return_Json(Error::validateError($result));
        $dbRes = QuarantineInspectorDb::deleteChecked($data['ids']);
        return Communal::return_Json($dbRes);
    }

    /** 检疫员统计
     * 未发现对应的接口文档数据，暂时搁置
     */
    function statistics(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' =>'require|number|max:50|min:1',
            'current_page' =>'require|number|min:1',
            'region|区域' => 'require|max:20|region'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $result = QuarantineInspectorDb::statisticsList($data);
        return Helper::reJson($result);
//        //参数抓取
//        $data=Communal::getPostJson();
//        //DID登录验证
//        $checkout= $this->checkout($data['did'],1);
//        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
//        //参数请求验证
//        $validate = new BaseValidate([
//            'per_page' =>'require|number|between:1,50',
//            'current_page' =>'require|number|min:1',
//            'region|区域' => 'require|max:20|region'
//        ]);
//        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
//        //区域验证
//        $regionVerify=Communal::regionVerify($data,$checkout[1]);
//        if(!$regionVerify){
//            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
//        }
//        $result = QuarantineInspectorDb::statisticsList($data);return json($result);
//        return Communal::return_Json($result);
    }
    
    //检疫员统计导出
    function recordExcel()
    {
        $data = $_GET;
        $validate = new BaseValidate([
            'region|区域' => 'require|max:20|region',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $res = QuarantineInspectorDb::recordExecl($data);
        if ($res[0]){
            $dbRes = $res[1];
            if(empty($dbRes)){
                return json_encode(["code" => 'error',"var" => ['未找到数据']]); 
            }
            unset($dbRes['dataResOne']);
            $name = '检疫人员统计记录';
            $header = ['区域', '专职检疫人数', '兼职检疫人数', '人数'];
            excelExport($name, $header, $dbRes['dataResTwo']);
        }
    }

    // 导出字段显示
    function exportList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = [
            "name" => "姓名",
            "type" => "检疫员类型",
            "unit" => "所在单位",
            "sex" => "性别",
            "region_name" => "所在区域",
            "birthday" => "出生日期",
            "job" => "岗位",
            "technical" => "职称",
            "education" => "学历",
            "academy" => "毕业院校",
            "tel" => "联系号码",
            "entryday" => "从业时长",
            "guard" => "在岗情况"
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
        $field = substr($keys,41);
        $title = array_values($data);
        array_splice($title, 0, 1);
        $res = QuarantineInspectorDb::exportls($data,$field,$condition);
        if ($res[0]){
            $dataRes = $res[1];
            if(empty($dataRes)){
                return json_encode(["code" => 'error',"var" => ['未找到数据']]); 
            }
            foreach ($dataRes as $key => $val) {
                if (!empty($val['type'])){
                    switch ($val['type'])
                    {
                        case "1":$val['type'] = "专职";
                            break;
                        case "2":$val['type'] = "兼职";
                            break;
                    }
                }
                if (!empty($val['sex'])){
                    switch ($val['sex'])
                    {
                        case "1":$val['sex'] = "男";
                            break;
                        case "2":$val['sex'] = "女";
                            break;
                    }
                }
                if (!empty($val['guard'])){
                    switch ($val['guard'])
                    {
                        case "1":$val['guard'] = "是";
                            break;
                        case "2":$val['guard'] = "否";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '检疫人员记录表';
            excelExport($name, $title, $result);
        }
    }
}