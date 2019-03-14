<?php
/**
 * Created by PhpStorm.
 * User: userYang
 * Date: 2018/7/31
 * Time: 14:12
 */
namespace app\improve\controller;


use think\worker\Server;

class WorkerController extends Server{

    protected $processes=1;
    function onWorkerStart($work){
        $handle=new TimerTaskController();
        $handle->add_timer();
    }
}
