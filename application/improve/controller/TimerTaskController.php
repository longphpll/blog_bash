<?php
/**
 * Created by PhpStorm.
 * User: userYang
 * Date: 2018/7/31
 * Time: 14:17
 */
namespace app\improve\controller;
use think\App;
use Redis;
use think\Controller;
use think\Log;
use think\Db;
use Workerman\Lib\Timer;
use app\improve\model\MarkerDb;


class TimerTaskController extends Controller{

    public function __construct(App $app = null)
    {
        parent::__construct($app);
    }

    public function add_timer(){
        Timer::add(1, array($this, 'run'), array(), true);
    }

    function run(){
        //每天0点执行任务
        if(date('H:i:s')==='23:59:01'){
            echo 'carry out user';
            $redis=new Redis();
            $redis->connect('127.0.0.1',6379);
            /// 无人机
            $uav_rs = $redis->Hgetall('uav_Locus');//获取用户列表ID
            if (!empty($rs)){
                echo 'carry out uav';
                if (!empty($uav_rs)){
                    $shu=0;
                    $create_time=date('Y-m-d H:i:s');
                    foreach ( $uav_rs as $key =>$value ){
                        $list =array();
                        $val=json_decode($value);
                        if(empty($val->location_s)){
                            $redis->Hdel('uav_Locus',$key);
                            continue;
                        }
                        $list['start_time']=$val->start_time;//开始时间
                        $list['over_time']=$val->last_time;//结束时间
                        $list['start_locus']=$val->start_location;//开始地点
                        $list['over_locus']=$val->location;//结束地点
                        $list['location_str']=$val->location_s;//轨迹
                        $list['create_time']=$create_time;//创建时间
                        $list['start_locus_name']=$val->start_location_name;//开始位置名称
                        $list['over_locus_name']=$val->location_name;//结束位置名称
                        $result = MarkerDb::add($list);
                        if(!empty($result)){
                            $redis->Hdel('uav_Locus',$key);
                        }
                    }
                }
            }
        }
    }
}