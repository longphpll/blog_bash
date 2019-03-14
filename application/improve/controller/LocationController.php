<?php
/**
 * 实时定位
 * Created by PhpStorm.
 * User: wendaomumao
 * Date: 2018/3/12 0012
 * Time: 10:46
 */

namespace app\improve\controller;

use app\improve\model\LocationDb;
use think\Controller;
use think\Cache;
use think\Log;
// use think\cache\Driver\Redis;
use Redis;
use app\improve\model\UserDb;
use think\Db;

/**
 * 人员在线定位
 */
class LocationController extends Controller
{

    //获取推流地址
    function getPushUrl()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reError($auth[1][1] == '您没有该权限' ? 'error' : 'loginOut', $auth[1]);
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $data = Helper::getPostJson();
        if (empty($data['is_open'])) return Helper::reJson(Errors::Error('is_open 字段不能为空', '参数错误'));
        if ($data['is_open'] != 'open' && $data['is_open'] != 'close') return Helper::reJson(Errors::Error('is_open的值只能为：open、close', '参数错误'));
        $user = json_decode($redis->Hget('location_infos', $auth[1]['s_uid']));
        if (empty($user)) return Helper::reJson(Errors::Error('误操作', '参数错误'));
        if ($data['is_open'] == 'open') {
            $bizId   = '28556';
            $key     = 'e62bfc243954d84a601ec0121f2f498e';
            $time    = date('Y-m-d H:i:s', time() + 86400);
            $PushUrl = getPushUrl($bizId, $user->tel, $key, $time);
        }
        $user->live      = $data['is_open'] == 'open' ? 0 : 1;
        $user->last_time = $data['is_open'] == 'open' ? date('Y-m-d H:i:s') : '';
        $redis->hSet('location_infos', $auth[1]['s_uid'], json_encode(Helper::removeEmpty($user), JSON_UNESCAPED_UNICODE));
        $user = json_decode($redis->Hget('location_infos', $auth[1]['s_uid']));
        if ($user->live != 1 && $data['is_open'] == 'open' && empty($PushUrl)) {
            return Helper::reJson(Errors::Error('开启直播失败', '温馨提示'));
        }
        return Helper::reJson([true, ['PushUrl' => $data['is_open'] == 'open' ? $PushUrl : '直播关闭']]);
    }

    //获取直播地址
    function getPlayUrl()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reError($auth[1][1] == '您没有该权限' ? 'error' : 'loginOut', $auth[1]);
        $data = Helper::getPostJson();
        if (empty($data['tel'])) return Helper::reJson(Errors::Error('tel 为空', 'tel 为空'));
        $bizId   = '28556';
        $PlayUrl = getPlayUrl($bizId, $data['tel']);
        return Helper::reJson([true, ['PlayUrl' => $PlayUrl]]);
    }

    /**
     *
     * app用户实时定位列表--无人机相关
     */
    function userLocationList()
    {
        $cookie = request()->cookie(['s_region']);
        if (empty($cookie)) return json(['code' => 'error', 'var' => ['请登录后再操作']]);//return Helper::return_Json(Errors::Error('请登录后再操作','操作错误'));
        $data  = Helper::getPostJson();
        $redis = new redis();
        $redis->connect('127.0.0.1', 6379);
        $userInfo         = $redis->Hget('userLogin', 'userInfo');//获取在线用户列表
        $userInfo         = explode(',', $userInfo);//转换成数组
        $orientationArray = array();
        $where            = "status='1' ";
        $teyp             = false;
        if (empty($cookie)) {//区域ID
            //return Helper::return_Json(Errors::Error('用户登录信息不完善'));
            return json(['code' => 'error', 'var' => ['用户登录信息不完善']]);
        }
        if (Helper::lsWhere($data, 'region_id')) {
            $s_region = $data['region_id'];
            $teyp     = true;
        } else {
            $s_region = '43';
        }
        //根据区域ID region_id
        $where .= " and  region like '" . $s_region . "%'";
        //↓↓↓↓↓ 其他条件
        if (!empty($data['name'])) {//用户名称、手机号
            $where .= " and ( name like '%" . $data['name'] . "%'  or  tel like '%" . $data['name'] . "%') ";
            $teyp  = true;
        }
        $uid_array = array();
        if ($teyp == true) {
            $rs = Db::table('u_user')->where($where)->field('uid')->select();
            foreach ($rs as $key => $uid) {
                $uid_array[$key] = $uid['uid'];
            }
            // array_intersect 取相同的值
            $userInfo = array_intersect($uid_array, $userInfo);
        }
        $shu = 0;
        foreach ($userInfo as $key => $value) {
            $user = json_decode($redis->Hget('location_infos', $value));//取用户具体定位信息
            if (empty($user)) continue;
            //移除离线用户
            if (strtotime(date('Y-m-d H:i:s')) - strtotime($user->last_time) > 20) {
                $redis->hDel('location_infos', $value);
                unset($userInfo[array_search($user->uid, $userInfo)]);
                if (!empty($userInfo)) {
                    $userInfo_s = implode(',', array_values($userInfo));
                    $redis->hSet('userLogin', 'userInfo', $userInfo_s);//存
                }
                continue;
            }
            //开启直播
            if (!empty($data['live'])) {
                if ($data['live'] == 'yes' && $user->live == 1) {//开启直播
                    $orientationArray[$shu] = $user;
                    $shu++;
                    continue;
                } else if ($data['is_live'] == 'no' && $user->live == 0) {//未直播
                    $orientationArray[$shu] = $user;
                    $shu++;
                    continue;
                }
            } else {//全部
                $orientationArray[$shu] = $user;
                $shu++;
                continue;
            }
        }
        return json(['code' => 's_ok', 'var' => $orientationArray]);
        // return Helper::return_Json([true,Helper::removeEmpty($orientationArray)]);
    }

    // app用户在线定位信息更新--无人机相关
    function userLocation()
    {
        $cookie = request()->cookie();
        if (empty($cookie)) return Helper::reJson(Errors::Error('请登录后再操作', '操作错误'));
        $data = Helper::getPostJson();
        if (empty($data['location'])) return json(['code' => 'error', 'var' => ['location 不能为空']]);
        if (empty($data['location_name'])) return json(['code' => 'error', 'var' => ['location_name 不能为空']]);
        if ($data['live'] != 0 && $data['live'] != 1) return json(['code' => 'error', 'var' => ['live 不能为空']]);
        $location         = explode(',', $data['location']);
        $longitude        = $location[0];
        $latitude         = $location[1];
        $orientationArray = array();
        //经度最大是180° 最小是0°
        if (0.0 > $longitude || 180.0 < $longitude) {
            return Helper::reJson(Errors::Error('经度最大是180° 最小是0°', '参数错误'));
        }
        //纬度最大是90° 最小是0°
        if (0.0 > $latitude || 90.0 < $latitude) {
            return Helper::reJson(Errors::Error('纬度最大是90° 最小是0°', '参数错误'));
        }
        // 是否有无人机位置信息
        if (!empty($data['uav_location'])) {
            $uav_location  = explode(',', $data['uav_location']);
            $uva_longitude = $uav_location[0];
            $uva_latitude  = $uav_location[1];
            if (0.0 > $uva_longitude || 180.0 < $uva_longitude) {
                return Helper::reJson(Errors::Error('飞机经度最大是180° 最小是0°', '参数错误'));
            }
            //纬度最大是90° 最小是0°
            if (0.0 > $uva_latitude || 90.0 < $uva_latitude) {
                return Helper::reJson(Errors::Error('飞机纬度最大是90° 最小是0°', '参数错误'));
            }
        }
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        // 判断该用户是否有定位
        $LocationInfo = json_decode($redis->Hget('location_infos', $cookie['s_uid']));
        $case         = false;//位置操作情况
        if (empty($LocationInfo)) {
            //判断 userInfo 是否有值
            if (empty($redis->Hget('userLogin', 'userInfo'))) {
                $redis->hSet('userLogin', 'userInfo', $cookie['s_uid']);//存
            } else {
                $info          = $cookie['s_uid'];
                $userInfo      = $redis->Hget('userLogin', 'userInfo');
                $userInfoArray = explode(',', $userInfo);
                //↓↓↓↓ 判断 $userInfoArray数组中是否存在 $info 信息
                if (!in_array($info, $userInfoArray)) {// 不存在则添加
                    $info = $userInfo . ',' . $info;
                    $redis->hSet('userLogin', 'userInfo', $info);//存
                }
            }
            // 获取人员信息
            $info_res = UserDb::userLocal($cookie['s_uid']);
            if (empty($info_res)) {
                return Helper::reJson(Errors::Error('未找到该用户', '参数错误'));
            }
            $value['name']        = $info_res['name']; // 用户名
            $value['rid']         = $cookie['s_client']; // 用户身份,2表示护林员，3表示无人机
            $value['uid']         = $info_res['uid']; // 用户uid
            $value['region_name'] = $data['location_name']; // 区域名称
            $value['region']      = $info_res['region']; // 区域
            $value['live']        = $data['live']; // 直播状态,0表示未开启；1表示已开启
            $value['tel']         = $info_res['tel']; // 手机号
            $value['location']    = $data['location'];//位置
            $value['last_time']   = date('Y-m-d H:i:s'); //最后请求时间
            // 是否有无人机定位信息
            if (!empty($data['uav_location']) || !empty($data['data'])) {
                //新加
                $value['data']         = !empty($data['data']) == false ? '' : $data['data'];//飞行参数
                $value['uav_location'] = !empty($data['uav_location']) == false ? '' : $data['uav_location'];//位置飞机
                $value['live']         = !empty($data['live']) == false ? 0 : $data['live'];//直播状态, 0表示未开启；1表示已开启
                $value['yaw']          = !empty(explode('_', $data['data'])[2]) == false ? '' : explode('_', $data['data'])[2];// 2飞机偏航角
            } else {
                $value['data']         = '';
                $value['uav_location'] = '';
                $value['live']         = $data['live'];
                $value['yaw']          = '';
            }
            $redis->hSet('location_infos', $cookie['s_uid'], json_encode(Helper::removeEmpty($value), JSON_UNESCAPED_UNICODE));
            $LocationInfo = $redis->Hget('location_infos', $cookie['s_uid']);
            if (!empty($LocationInfo)) $case = true;
        } else {
            // 已有人员信息，则更新定位信息
            $LocationInfo->location  = $data['location']; // 位置
            $LocationInfo->last_time = date('Y-m-d H:i:s'); //最后请求时间
            // 是否有无人机定位信息
            if (!empty($data['uav_location']) || !empty($data['data'])) {
                $LocationInfo->live         = !empty($data['live']) == false ? 0 : $data['live'];//直播状态, 0表示未开启；1表示已开启
                $LocationInfo->data         = !empty($data['data']) == false ? '' : $data['data'];//飞行参数
                $LocationInfo->uav_location = !empty($data['uav_location']) == false ? '' : $data['uav_location'];//位置飞机
                $LocationInfo->yaw          = !empty(explode('_', $data['data'])[2]) == false ? '' : explode('_', $data['data'])[2];// 2飞机偏航角
            } else {
                $LocationInfo->live         = !empty($data['live']) == false ? 0 : $data['live'];//直播状态, 0表示未开启；1表示已开启
                $LocationInfo->data         = '';//飞行参数
                $LocationInfo->uav_location = '';//位置飞机
                $LocationInfo->yaw          = '';// 2飞机偏航角
            }
            $redis->hSet('location_infos', $cookie['s_uid'], json_encode($LocationInfo, JSON_UNESCAPED_UNICODE));
            $LocationInfo = $redis->Hget('location_infos', $cookie['s_uid']);
            if (!empty($LocationInfo)) $case = true;
        }
        if ($case && !empty($data['uav_location'])) {
            $uavLocus = json_decode($redis->Hget('uav_Locus', $cookie['s_uid']));
            if (empty($uavLocus)) {
                $value = UserDb::userLocal($cookie['s_uid']);
                //用户名，手机号，出发时间，结束时间，出发区域，结束区域
                $value['location'] = $data['uav_location'];//位置
                //$values['location_name']=$data['location_name'];//位置名称
                $value['last_time']      = date('Y-m-d H:i:s');
                $value['start_time']     = date('Y-m-d H:i:s');//开始时间
                $value['start_location'] = $data['uav_location'];//开始位置
                $value['location_s']     = $data['uav_location'];//位置轨迹
                //$values['start_location_name']=$data['location_name'];//开始位置名称
                $redis->hSet('uav_Locus', $cookie['s_uid'], json_encode(Helper::removeEmpty($value), JSON_UNESCAPED_UNICODE));
            } else {
                $uavLocus->last_time = date('Y-m-d H:i:s');
                $uavLocus->location  = $data['uav_location'];//位置轨迹
                //$uavLocus->location_name=$data['location_name'];//位置名称
                $uavLocus->location_s = $uavLocus->location_s . ';' . $data['uav_location'];//位置
                $uavLocus->location_s = implode(';', array_unique(explode(';', $uavLocus->location_s)));
                $redis->hSet('uav_Locus', $cookie['s_uid'], json_encode($uavLocus, JSON_UNESCAPED_UNICODE));
            }
        }
        if ($case == false) {
            return Helper::reJson([true, ['msg_customer'   => '操作失败',
                                          'msg_programmer' => 'success']]);
        }
        $shu      = 0;
        $userInfo = $redis->Hget('userLogin', 'userInfo');
        $userInfo = explode(',', $userInfo);
        foreach ($userInfo as $key => $value) {
            $user = json_decode($redis->Hget('location_infos', $value));
            if (empty($user)) continue;
            //移除离线用户
            if (strtotime(date('Y-m-d H:i:s')) - strtotime($user->last_time) > 20) {
                $redis->hDel('location_infos', $user->uid);
                unset($userInfo[array_search($user->uid, $userInfo)]);
                if (!empty($userInfo)) {
                    $userInfo_s = implode(',', array_values($userInfo));
                    $redis->hSet('userLogin', 'userInfo', $userInfo_s);//存
                }
                continue;
            }
            $orientationArray[$shu] = $user;
            $shu++;
        }

        return json(['code' => 's_ok', 'var' => $orientationArray]);
    }

}