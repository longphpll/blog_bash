<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/2/002
 * Time: 16:58
 */

namespace app\improve\controller;

use app\improve\validate\BaseValidate;
use app\improve\model\BaseDb;
use app\improve\model\TaskDb;
use app\improve\validate\Task;
use think\Controller;
/*
 * 任务管理
 * 任务状态，0：已发布 1：执行中 2：已完成，-1：已取消，-2：已过期，-3：已拒绝
 */
 
class TaskController extends Controller
{

    function add(){
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
//        $result = $this->validate($data, 'Task.add');
//        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        if (strtotime($data['deadline'])< time()) return Helper::reJson(Errors::DEADLINE_ERROR);
        $data['founder'] = $auth[1]['s_uid'];
        $data['region_name'] = BaseDb::areaName($data['region']);
        $data['founder_name'] = BaseDb::name($data['founder']);
        $images =  request()->file("images");
        $dbRes = TaskDb::add($data, $images);
        return Helper::reJson($dbRes);
    }
	
    /**
     * 所有人都可以看详情
     */
    function query(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Task.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = TaskDb::query($data['id']);
        return Helper::reJson($dbRes);
    }

    /**
     * 重新发布的任务详情
     */
    function republishQuery(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Task.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = TaskDb::query($data['id']);
        if($dbRes[1]['status'] == 3) return Helper::reJson(Errors::TASK_HAS_RELEASE);//该任务已发布
        if($dbRes[1]['status'] == 1) return Helper::reJson(Errors::TASK_IS_IMPLEMENT);//该任务正在执行
        if($dbRes[1]['status'] == 2) return Helper::reJson(Errors::TASK_HAS_FINISHED);//该任务已完成
		$founder = TaskDb::queryAssgin($data['id']);
        if ($founder[1]['founder'] != $auth[1]['s_uid']) return Helper::reJson(Errors::NO_RELEASE);
        $dbRes = TaskDb::republishQuery($data['id']);
        return Helper::reJson($dbRes);
    }

    //任务名称检索
    function taskNameList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $data['region'] = cookie('s_region');
        $dbRes = TaskDb::taskNameList($data);
        return Helper::reJson($dbRes);
    }

    function ls(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' => 'require|number|max:50|min:1',
            'current_page' => 'require|number|min:1',
            'name|任务名称' => 'max:32',
            'type|任务类型' => 'in:1,2',
            'region|区域' => 'max:20',
            'status|任务状态' => 'in:3,1,2,-2,-1,-3',
            'founder_name|发布人' => 'max:16'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        if(!array_key_exists("region", $data))
        {
            $data['region'] = cookie('s_region');   
        }
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $user = $auth[1]['s_uid'];
        $rid = $auth[1]['s_rid'];
        $expired = TaskDb::expired();
        $dbRes = TaskDb::ls($data,$rid,$user,$auth[1]);
        return Helper::reJson($dbRes);
    }

    // 接受任务
    function receive(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Task.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = TaskDb::query($data['id']);
        if (!$dbRes[0]) return Helper::reJson($dbRes);
        //任务是否你是被指派人
        $assigners = $dbRes[1]['assigner'];
        $flag = false;
        foreach ($assigners as $a) {
			if ($auth[1]['s_uid'] == $a['uid']) {
				$flag = true;
				break;
			}
        }
        if (!$flag) return Helper::reJson(Errors::ASSIGN_ERROR);
        //任务是否已发布与过期
        if ($dbRes[1]['status'] != 3) return Helper::reJson(Errors::TASK_HAS_RECEIVERD);
        if (strtotime($dbRes[1]['deadline']) < time()) return Helper::reJson(Errors::TASK_EXPIRED);
        $data['recevier_name'] = BaseDb::name($auth[1]['s_uid']);
        //数据库修改
        $task = [
            'id' => $data['id'],
            'status' => 1,
            'recevier' => $auth[1]['s_uid']
        ];
        $dbRes = TaskDb::receive($task);
        return Helper::reJson($dbRes);
    }

    // 取消任务
    function cancelTask(){
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Task.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = TaskDb::query($data['id']);
        if (!$dbRes[0]) return Helper::reJson($dbRes);
        //任务是否你是发布人
        if ($dbRes[1]['founder'] != $auth[1]['s_uid']) return Helper::reJson(Errors::NO_RELEASE);
        //任务状态是否正在发布中
        if ($dbRes[1]['status'] != 3) return Helper::reJson(Errors::TASL_STATUS_NO_RELEASE);
        //任务是否过期
        if (strtotime($dbRes[1]['deadline']) < time()) return Helper::reJson(Errors::TASK_EXPIRED);
        $data['status'] = -1;
        $dbRes = TaskDb::cancelTask($data);
        return Helper::reJson($dbRes);
    }

    // 拒绝任务
    function refuseTask(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Task.refuse');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = TaskDb::query($data['id']);
        if (!$dbRes[0]) return Helper::reJson($dbRes);
        $type=false;
        for($i=0;$i<count($dbRes[1]['assigner']);$i++){
            if ($dbRes[1]['assigner'][$i]['uid'] == $auth[1]['s_uid']){
                $type=true;
            }
        }
        if($type==false) return Helper::reJson(Errors::NO_INCIDENT);
        if ($dbRes[1]['status'] != 3) return Helper::reJson(Errors::TASL_STATUS_NO_RELEASE);
        //任务是否过期
        if (strtotime($dbRes[1]['deadline']) < time()) return Helper::reJson(Errors::TASK_EXPIRED);
        $data['status'] = -3;
        $dbRes = TaskDb::refuseTask($data);
        return Helper::reJson($dbRes);
    }

    // 完成任务
    function finish(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'id' => 'require|number',
            'result|反馈结果' => 'require|max:255'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $images =  request()->file("images");
        $dbRes = TaskDb::query($data['id']);
        if (!$dbRes[0]) return Helper::reJson($dbRes);
         //任务是否过期
         if (strtotime($dbRes[1]['deadline']) < time()) return Helper::reJson(Errors::TASK_EXPIRED);
        //任务状态是否正在执行
        if ($dbRes[1]['status'] != 1) return Helper::reJson(Errors::TASL_STATUS_NO_IMPLEMENT);
        $data['status'] = 2;
        $data['finish_time'] = date('Y-m-d H:i:s');
        $daRes = TaskDb::finishEdit($data, $images);
        return Helper::reJson($daRes);
    }

    //重新发布任务
    function republish(){
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Task.republishs');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $images =  request()->file("images");
        if(count($images) > 6) return Helper::reJson(Errors::IMAGE_COUNT_ERROR);
        $dbRes = TaskDb::republishQuery($data['id']);
        if($dbRes[1]['status'] == 3) return Helper::reJson(Errors::TASK_HAS_RELEASE);//该任务已发布
        if($dbRes[1]['status'] == 1) return Helper::reJson(Errors::TASK_IS_IMPLEMENT);//该任务正在执行
        if($dbRes[1]['status'] == 2) return Helper::reJson(Errors::TASK_HAS_FINISHED);//该任务已完成
        if($dbRes[1]['status'] == -1) return Helper::reJson(Errors::TASK_HAS_CANCELED);//该任务已取消
        if(array_key_exists("region", $dbRes[1]))
        {
            $dbRes['region'] = $dbRes[1]['region'];
        }
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        //任务是否你是发布人
        if ($dbRes[1]['founder'] !== $auth[1]['s_uid']) return Helper::reJson(Errors::NO_RELEASE);       
        //任务截止时间不能少于当前时间
        if(strtotime($data['deadline'])< time()) return Helper::reJson(Errors::DEADLINE_ERROR);
        $status = $dbRes[1]['status'];
        $data['founder'] = $auth[1]['s_uid'];
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = $data['create_time'];
        $data['region_name'] = BaseDb::areaName($data['region']);
        $dbRes = TaskDb::republish($data, $images, $status);
        return Helper::reJson($dbRes);    
    }

    function deleteImage(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Task.deleteImage');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        //检测是否有这个id
        $task = TaskDb::queryPeople($data['id']);
        if (!is_array($task)) Helper::reErrorJson($task);
        $uid = $auth[1]['s_uid'];
        switch ($data['image_use']) {
            case 1:
                if ($uid !== $task['founder']) return Helper::reErrorJson(Errors::IS_NOT_I);
                break;
            case 2:
                if ($uid !== $task['recevier']) return Helper::reErrorJson(Errors::NO_INCIDENT);
                break;
        }
        $dbRes = TaskDb::deleteImage($data['id'], $data['image_use'], $data['image_id']);
        return Helper::reJson($dbRes);
    }

    //删除指派人-APP端
    function deleteAssgin(){
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->Validate($data, 'Task.deleteAssgin');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = TaskDb::deleteAssgin($data);
        return Helper::reJson($dbRes);
    }

    //搜索与筛选指派人
    function findAssgin(){
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $Assgindata = Helper::getPostJson();
        // 改
        if(!empty($Assgindata['region'])){
            $region_result = Helper::authRegion($Assgindata);
            if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
            $AsdbRes = TaskDb::findAssgin($Assgindata,$auth[1]);
        }else{
            $AsdbRes = TaskDb::findAssgin($Assgindata,$auth);
        }
        return Helper::reJson($AsdbRes);
    }

    function sampleMap(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'app' => 'require|in:1,2',
            'type|任务类型' => 'in:1,2',
            'status|任务状态' => 'in:3,1,2,-1,-2,-3',
            'region|区域' => 'max:20|region'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        if(empty($data['region']))
        {
            $data['region'] = cookie('s_region');   
        }
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $dbRes = TaskDb::taskOverview($data, $auth[1]['s_uid']);
        return Helper::reJson($dbRes);
    }

    function listPort(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $result = TaskDb::listPort();
        return Helper::reJson($result);
    }

    //总体概况  --任务接口
    function taskOverview(){

    }

    // 导出字段显示
    function exportList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = [
            "name" => "任务名称",
            "type" => "任务类型",
            "positions" => "地图位置",
            "location_name" => "地图位置名称",
            "region_name" => "区域",
            "deadline" => "截止时间",
            "content" => "任务内容",
            "founder_name" => "发布人",
            "recevier_name" => "接受人",
            "status" => "任务状态",
            "create_time" => "发布时间",
            "finish_time" => "完成时间",
            "result" => "任务结果描述",
            "reason" => "拒绝原因",
            "img" => "图片"
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
        if (isset($data['img'])){
            $img = true;
            unset($data['img']);
        }else{
            $img = false;
        }
        $keys = implode(',',array_keys($data));
        $field = substr($keys,26);
        $title = array_values($data);
        array_splice($title, 0, 1);
        $res = TaskDb::exportls($data,$field,$img,$condition);
        if ($res[0]){
            $dataRes = $res[1];
            if(empty($dataRes)){
                return json_encode(["code" => 'error',"var" => ['未找到数据']]); 
            }
            foreach ($dataRes as $key => $val) {
                unset($val['id']);
                if (!empty($val['type'])){
                    switch ($val['type'])
                    {
                        case "1":$val['type'] = "病虫害调查";
                            break;
                        case "2":$val['type'] = "病虫害防治";
                            break;
                    }
                }
                if (!empty($val['status'])){
                    switch ($val['status'])
                    {
                        case "1":$val['status'] = "执行中";
                            break;
                        case "2":$val['status'] = "已完成";
                            break;
                        case "3":$val['status'] = "已发布";
                            break;
                        case "-1":$val['status'] = "已取消";
                            break;
                        case "-2":$val['status'] = "已过期";
                            break;
                        case "-3":$val['status'] = "已拒绝";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '任务管理记录表';
            excelExport($name, $title, $result);
        }
    }
}