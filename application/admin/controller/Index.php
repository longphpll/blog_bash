<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;

//后台 Index 控制器
class Index extends AdminBase
{
    //访问路径:admin/index/index
    public function index()
    {
        return $this->fetch();
    }
}