<?php
/**
 * Created by PhpStorm.
 * User: XieLe
 * Date: 2018/8/4
 * Time: 11:52
 */

namespace app\improve\controller;
use think\Controller;

class PlayPushController extends Controller
{

    //获取推流地址
    public function getPushUrl(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $bizId = '28556';
        $key = 'e62bfc243954d84a601ec0121f2f498e';
        $time =date('Y-m-d H:i:s',time()+86400 );
        $PushUrl = getPushUrl($bizId, $auth[1]['s_uid'], $key, $time);
        return Helper::reJson([true,['PushUrl' => $PushUrl]]);
    }

    //获取直播地址
    public function getPlayUrl(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $bizId = '28556';
        $data = $_GET;
        $PlayUrl = getPlayUrl($bizId, $data['live_id']);
        return Helper::reJson([true,['PlayUrl' => $PlayUrl]]);
    }
}