<?php
/**
 * Created by Administrator.
 * Date: 2017/11/25
 * 用户接口
 */

namespace app\improve\controller;

use app\improve\model\BaseDb;
use app\improve\model\UserDb;
use app\improve\validate\BaseValidate;
use think\Controller;
use think\Db;

class UserController extends Controller
{

    function add(){
        return Helper::reJson($this->addImpl());
    }

    function register(){
        return Helper::reJson($this->appregister());
    }
	
	function center(){
        $auth = Helper::auth();
        if ($auth[0] !== true) return Helper::reJson($auth);
        $data = Helper::getPostJson();
		$validate = new BaseValidate([
            'pwd|密码'=>'length:6,16'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        if (Helper::lsWhere($data, "pwd")) {
            $news = UserDb::queryByPwd($data['uid']);
            $oldPwd = md5($data['oldpwd'] . $news['salt']);
            if ($news['pwd'] !== $oldPwd) return  Helper::reJson(Errors::validateError('旧密码错误'));
            if ($data['pwd'] === $data['oldpwd']) return Helper::reJson(Errors::validateError('旧密码和新密码一致'));
            $data['salt'] = Helper::getRandChar(6);
            $data['pwd'] = md5($data['pwd'] . $data['salt']);
            unset($data['oldpwd']);
        } else unset($data['pwd'],$data['oldpwd']);
        $imgHead = request()->file("imgHead");
        $daRes = UserDb::center($data,$imgHead);
        return Helper::reJson($daRes); 
    }
    
    public function wxRegister()
    {
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.wxRegister');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        if(!Helper::lswhere($data, 'pwd')) return Errors::validateError('密码必填');
        $data['uid'] = Helper::uniqStr();
        $data['salt'] = Helper::getRandChar(6);
        $data['pwd'] = md5($data['pwd'] . $data['salt']);
        $data['origin'] = 2;
        $data['region_name'] = BaseDb::areaName($data['region']);
        $daRes = UserDb::wxRegister($data);
        return Helper::reJson($daRes);
    }

    // 新加 29.3.1
    function wxCenter(){
        $auth = Helper::auth();
        if ($auth[0] !== true) return Helper::reJson($auth);
        $data = Helper::getPostJson();
		$validate = new BaseValidate([
            'pwd|密码'=>'length:6,16'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        if (Helper::lsWhere($data, "pwd")) {
            $news = UserDb::queryByPwd($data['uid']);
            $oldPwd = md5($data['oldpwd'] . $news['salt']);
            if ($news['pwd'] !== $oldPwd) return  Helper::reJson(Errors::validateError('旧密码错误'));
            if ($data['pwd'] === $data['oldpwd']) return Helper::reJson(Errors::validateError('旧密码和新密码一致'));
            $data['salt'] = Helper::getRandChar(6);
            $data['pwd'] = md5($data['pwd'] . $data['salt']);
            unset($data['oldpwd']);
        } else unset($data['pwd'],$data['oldpwd']);
        $daRes = UserDb::wxCenter($data);
        return Helper::reJson($daRes); 
    }

    private function appregister()
    {
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.User');
        if($result !== true) return Errors::validateError($result);
        if(!Helper::lswhere($data, 'pwd')) return Errors::validateError('密码必填');
        $data['uid'] = Helper::uniqStr();
        $data['salt'] = Helper::getRandChar(6);
        $data['pwd'] = md5($data['pwd'] . $data['salt']);
		$data['origin'] = 2;
        $data['region_name'] = BaseDb::areaName($data['region']);
        $imgHead = request()->file("imgHead");
        $daRes = UserDb::register($data,$imgHead);
        return $daRes;
    }

    private function addImpl()
    {
        $auth = Helper::auth([1]);
        if ($auth[0] !== true) return $auth;
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.add');
        if ($result !== true) return Errors::validateError($result);
        if (!Helper::lsWhere($data, 'pwd')) return Errors::validateError('密码必填');
        $permission = $this->permission($data);
        if(!$permission[0]) return $permission;
        $addPermission = $this->addPermission($data,$permission);
        if(!$addPermission[0]) return $addPermission;
        UserDb:$this->queryRegionUser();
        $data['uid'] = Helper::uniqStr();
        $data['salt'] = Helper::getRandChar(6);
        $data['pwd'] = md5($data['pwd'] . $data['salt']);
        $data['examine'] = 1;
        $data['origin'] = 1;
        $data['region_name'] = BaseDb::areaName($data['region']);
        $imgHead = request()->file("imgHead");
        $daRes = UserDb::add($data,$imgHead);
        return $daRes;
    }
    
    function edit(){
        return Helper::reJson($this->editImpl());
    }

    private function editImpl()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return $auth;
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.edit');
        if ($result !== true) return Errors::validateError($result);
        $permission = $this->permission($data);
        if(!$permission[0]) return $permission;
        $editPermission = $this->editPermission($data,$permission);
        if(!$editPermission[0]) return $editPermission;
        if (Helper::lsWhere($data, "pwd")) {
            $data['salt'] = Helper::getRandChar(6);
            $data['pwd'] = md5($data['pwd'] . $data['salt']);
        } else unset($data['pwd']);
        $data['region_name'] = BaseDb::areaName($data['region']);
		$imgHead = request()->file("imgHead");
        $dbRes = UserDb::edit($data,$imgHead);
        if (Helper::lsWhere($data, "pwd") && $dbRes[0] ) UserDb::deleteAuth($data['uid']);
        return $dbRes;
    }

    public function editExamineUser()
    {
        $auth = Helper::auth([1]);
        if ($auth[0] !== true) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        // $result = $this->validate($data, 'User.edit');
        // if ($result !== true) return Helper::reJson(Errors::validateError($result));
        $permission = $this->permission($data);
        if(!$permission[0]) return Helper::reJson($permission);
        $editPermission = $this->editPermission($data,$permission);
        if(!$editPermission[0]) return Helper::reJson($editPermission);
        $data['region_name'] = BaseDb::areaName($data['region']);
        $dbRes = UserDb::editExamineUser($data);
        if ($dbRes[0]) UserDb::deleteAuth($data['uid']);
        return Helper::reJson($dbRes);
    }

    function deleteChecked()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        // $result = $this->validate($data, 'User.ids');
        // if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = UserDb::deleteChecked($data['ids']);
        return Helper::reJson($dbRes);
    }

    private function permission($data){
        //不允许添加、修改超级管理员
        if($data['rid'] == 3){
            return Errors::AUTH_PREMISSION_EMPTY;
        }
        $adduser_level = UserDb::queryRegionLevel($data['region']);
        if (!$adduser_level[0]) return [ false  , ["找不到该区域"]];
        //添加、修改管理员，首先判断被添加的用户是否大于县级
        if ($data['rid'] == 1 && $adduser_level[1]['level'] > 3) {
            return Errors::AUTH_PREMISSION_LEVEL;
        }
        //判断添加、修改的对象区域是否属于自己以内
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Errors::AUTH_PREMISSION_REJECTED;
        return $adduser_level;
    }

    private function addPermission($data,$permission){
        //当前用户是否为超级管理员，如是，则不处理
        $user_detail = UserDb::queryUser(cookie('s_uid'));
        if($user_detail[1]['rid'] != 3) {
            //判断参数传入是否普通用户
            if ($data['rid'] != 2) {
                //添加管理员，判断添加用户权限等级是否小于添加角色前端
                //dump($user_detail[1]['level'] >= $permission[1]['level']);die;
                if ($user_detail[1]['level'] >= $permission[1]['level']) {
                    //否，返回权限不足
                    return Errors::AUTH_PREMISSION_EMPTY;
                }
            }
        }
        return [true];
    }

    private function editPermission($data,$permission){
        //当前用户是否为超级管理员，如是，则不处理
        //dump($data);die;
        $user_detail = UserDb::queryUser(cookie('s_uid'));
        if (!$user_detail[0]) return [ false  , ["网络错误"]];
        if($user_detail[1]['rid'] != 3) {
            //获得修改对象原本身份
            $edit_user_detail = UserDb::queryUser($data['uid']);
            if (!$edit_user_detail[0]) return [ false  , ["找不到该用户"]];
            if($edit_user_detail[1]['rid'] == 1){
                if($data['rid'] == 1){
                    if ($user_detail[1]['level'] >= $permission[1]['level']) {
                        //否，返回权限不足
                        return Errors::AUTH_PREMISSION_EMPTY;
                    }
                }else{
                    if ($user_detail[1]['level'] > $permission[1]['level']) {
                        //否，返回权限不足
                        return Errors::AUTH_PREMISSION_EMPTY;
                    }
                }
                // //修改管理员
                // if ($user_detail[1]['level'] >= $edit_user_detail[1]['level']) {
                //     //否，返回权限不足
                //     return Errors::AUTH_PREMISSION_EMPTY;
                // }
            }else{
                //修改普通用户    判断修改的信息权限是否高于当前管理员权限
                if($data['rid'] == 1){
                    if ($user_detail[1]['level'] >= $permission[1]['level']) {
                        //否，返回权限不足
                        return Errors::AUTH_PREMISSION_EMPTY;
                    }
                }else{
                    if ($user_detail[1]['level'] > $permission[1]['level']) {
                        //否，返回权限不足
                        return Errors::AUTH_PREMISSION_EMPTY;
                    }
                }
            }
        }
        return [true];
    }
    
    private function delPermission($uid){
            $del_user_detail = UserDb::queryUser($uid);
            if (!$del_user_detail[0]) return [ false  , ["找不到该用户"]];
            $user_detail = UserDb::queryUser(cookie('s_uid'));
            if (!$user_detail[0]) return [ false  , ["网络错误"]];
            //不允许禁用超级管理员
            if($del_user_detail[1]['rid'] == 3) {
                return Errors::AUTH_PREMISSION_EMPTY;
            }
            //判断是否超级管理员
            if($user_detail[1]['rid']!=3) {
                //判断用户所属区域
                if(strpos($del_user_detail[1]['id'],$user_detail[1]['id'])===false){
                    return [ false  , ["不可删除其他区域用户"]] ;
                }
                //判断传入是否普通用户
                if ($del_user_detail[1]['rid'] != 2) {
                    //判断删除用户权限等级是否小于当前用户
                    if ($user_detail[1]['level'] >= $del_user_detail[1]['level']) {
                        //否，返回权限不足
                        return Errors::AUTH_PREMISSION_EMPTY;
                    }
                }
            } 
        return [true];
    }

    private function examinePermission($uid){
            $del_user_detail = UserDb::queryUser($uid);
            if (!$del_user_detail[0]) return [ false  , ["找不到该用户"]];
            $user_detail = UserDb::queryUser(cookie('s_uid'));
            if (!$user_detail[0]) return [ false  , ["网络错误"]];
            //不允许禁用超级管理员
            if($del_user_detail[1]['rid'] == 3) {
                return Errors::AUTH_PREMISSION_EMPTY;
            }
            //判断是否超级管理员
            if($user_detail[1]['rid']!=3) {
                //判断用户所属区域
                if(strpos($del_user_detail[1]['id'],$user_detail[1]['id'])===false){
                    return [ false  , ["不可审核其他区域用户"]] ;
                }
                //判断传入是否普通用户
                if ($del_user_detail[1]['rid'] != 2) {
                    //判断审核用户权限等级是否小于当前用户
                    if ($user_detail[1]['level'] >= $del_user_detail[1]['level']) {
                        //否，返回权限不足
                        return Errors::AUTH_PREMISSION_EMPTY;
                    }
                }
            } 
        return [true];
    }

    function updateStatus()
    {
        $auth = Helper::auth([1]);
        if ($auth[0] !== true) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.status');
        if ($result !== true) return Helper::reJson(Errors::validateError($result));
        $permission = $this->delPermission($data['uid']);
        if(!$permission[0])  return Helper::reJson($permission);
        $dbRes = UserDb::updateStatus($data['uid'], $data['status']);
        return Helper::reJson($dbRes);
    }

    // 修改密码
    function updatePwd()
    {
        $auth = Helper::auth([1]);
        if ($auth[0] !== true) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $dbRes = UserDb::updatePwd($data['uid']);
        return Helper::reJson($dbRes);
    }

    function examineStatus()
    {
        $auth = Helper::auth([1]);
        if ($auth[0] !== true) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.examines');
        if(!is_array($data['uids'])) return Helper::reJson([false,'数据异常,参数错误']);
        $result = [];
        try{
            Db::startTrans();
            foreach ($data['uids'] as $key=> $uid) {
                $examinesion = $this->examinePermission($uid['uid']);
                if(!$examinesion[0])  return Helper::reJson($examinesion);
                $dbRes = UserDb::examineStatus($uid['uid'], $data['examine']);
                if($data['examine'] == '-1')
                {
                    $auditRes = UserDb::auditResult($uid['uid'],$data['reason']);
                }
                if($dbRes) $result[$key] = 'success';
            }
            Db::commit();
            return Helper::reJson([true,$result]);
        } catch (Exception $e) {
            Db::rollback();
            return Helper::reJson(Errors::Error($e->getMessage()));
        }  
    }

    function query()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.query');
        if ($result !== true) return Helper::reJson(Errors::validateError($result));
        $dbRes = UserDb::query($data['uid']);
        return Helper::reJson($dbRes);
    }

    function ls()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' => 'require|number|max:500|min:1',
            'current_page' => 'require|number|min:1',
            'dept|部门' => 'number',
            'name|用户名' => 'max:16',
            'examine|审核状态' => 'in:-1,0,1,2',
            'region|区域'=>'max:20|region',
            'tel|手机号' => 'max:11|number'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::validateError($validate->getError()));
        if(!array_key_exists("region", $data))
        {
            $data['region'] = cookie('s_region');   
        }
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $user = $auth[1]['s_uid'];
        $dbRes = UserDb::ls($data,$user);
        return Helper::reJson($dbRes);
    }

    function queryRegionUser()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Region.query');
        if ($result !== true) return Helper::reJson(Errors::validateError($result));
        $region_result = Helper::userRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $dbRes = UserDb::queryRegionUser($data);
        return Helper::reJson($dbRes);
    }

     // 导出字段显示
     function exportList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = [
            "name" => "用户名",
            "tel" => "手机号",
            "region_name" => "管辖区域",
            "user_level" => "用户级别",
            "user_mold" => "用户类型",
            "user_role" => "用户角色",
            "dept" => "所属机构",
            "job" => "职务",
            "create_time" => "注册时间",
            "img" => "头像"
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
        $res = UserDb::exportls($data,$field,$img,$condition);
        if ($res[0]){
            $dataRes = $res[1];
            if(empty($dataRes)){
                return json_encode(["code" => 'error',"var" => ['未找到数据']]); 
            }
            foreach ($dataRes as $key => $val) {
                if (!empty($val['user_level'])){
                    switch ($val['user_level'])
                    {
                        case "1":$val['user_level'] = "省级";
                            break;
                        case "2":$val['user_level'] = "市级";
                            break;
                        case "3":$val['user_level'] = "区(县）级";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '用户管理信息表';
            excelExport($name, $title, $result);
        }
    }
}

?>