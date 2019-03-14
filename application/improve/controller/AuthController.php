<?php
/**
 * Created by xwpeng.
 * Date: 2017/11/25
 * 用户接口
 */

namespace app\improve\controller;

use app\improve\model\UserDb;
use think\Controller;
use think\Cookie;
use think\Error;
use think\Db;
use think\Validate;

class AuthController extends Controller
{

    function login()
    {
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.login');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        //核对账号密码，成功得到user.
        $user = UserDb::login($data);
        if (!$user[0]) return Helper::reJson($user);
        $user = $user[1];
        //判断登录类型
        $count = 0;
        foreach ($user['molds'] as $value){
            if($value['mid'] == $data['client']){
                $count++;
            }
        }
        if ($count==0) return Helper::reJson(Errors::AUTH_PREMISSION_EMPTY);
        unset($user['pwd']);
        unset($user['salt']);
        $user['s_token'] = Helper::uniqStr();
        //重置短效token。Helper::uniqStr()
        $auth = $this->resetAuth($user, $data['client']);
         Cookie('s_uid', null);
         Cookie('s_name', null);
         Cookie('s_token', null);
         Cookie('s_region', null);
         Cookie('s_region_name', null);
         Cookie('s_rid', null);
         Cookie('p_rid', null);
         Cookie('s_tel', null);
         Cookie('s_client', $data['client']);
        if ($auth[0]) {
            Cookie('s_uid', $user['uid']);
            Cookie('s_name', $user['name']);
            Cookie('s_rid', $user['roles'][0]['rid']);
            Cookie('p_rid', $user['rid']);
            Cookie('s_tel',$user['tel']);
            Cookie('s_token', $user['s_token']);
            Cookie('s_region', $user['region']);
            Cookie('s_region_name', urlencode($user['area_name']));
            Cookie('s_client', $data['client']);
            return Helper::reJson([true ,$user]);
        }
        return Helper::reJson($auth);
    }

    function plogin()
    {
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.plogin');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        //核对账号密码，成功得到user.
        $user = UserDb::plogin($data);
        if (!$user[0]) return Helper::reJson($user);
        $user = $user[1];
        Cookie('s_tel',$user['tel']);
        return Helper::reJson([true ,$user]);
    }

    function captcha()
    {

        header("Access-Control-Allow-Origin: *"); //允许跨域访问的
        return Helper::reJson([true,captcha_src()]);
    }

    function loginOut()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'User.loginOut');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = UserDb::deleteAuth($auth[1]['s_uid'], $data['client']);
        return Helper::reJson($dbRes);
    }

    private function resetAuth($user, $client)
    {
        $data = [
            'uid' => $user['uid'],
            's_token' => $user['s_token'],
            's_update_time' => date('Y-m-d H:i:s', time()),
            'client' => $client
        ];
        return UserDb::resetAuth($data);
    }
	
}

?>