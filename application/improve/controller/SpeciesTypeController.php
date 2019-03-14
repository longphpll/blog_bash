<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2018/3/16
 * Time: 16:12
 */

namespace app\improve\controller;

use think\Controller;
use app\improve\model\BaseDb;
use app\improve\model\SpeciesTypeDb;
use app\improve\validate\BaseValidate;

/**
 * 物种分类管理 
 */

class SpeciesTypeController extends Controller
{
    function add(){
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'SpeciesType.add');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $data['adder'] = $auth[1]['s_uid'];
        $dbRes = SpeciesTypeDb::add($data);
        return Helper::reJson($dbRes);
    }
	
    function ls()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $result = SpeciesTypeDb::ls();
        // 获取分类树型结构
        $array = make_tree($result[1]);
        return json_encode(["code" => 's_ok',"var" => $array]);
    }

    function edit()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'SpeciesType.edit');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $data['adder'] = $auth[1]['s_uid'];
        $dbRes = SpeciesTypeDb::edit($data);
        return Helper::reJson($dbRes);
    }

    function deleteChecked()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $dbRes = SpeciesTypeDb::deleteChecked($data['id']);
        return Helper::reJson($dbRes);
    }

}