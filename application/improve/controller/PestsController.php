<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/2/002
 * Time: 16:58
 */

namespace app\improve\controller;

use app\improve\model\PestsDb;
use app\improve\validate\Pests;
use think\Controller;
use think\Validate;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/**
 * 有害生物信息维护
 */
class PestsController extends RedisBase
{
    /*已改 Lxl*/
//    function ls()
//    {
//        $data     = Communal::getPostJson();
//        $checkout = $this->checkout($data['did'], 1);
//        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
//        unset($data['did']);
//
//        $validate = new Validate([
//            'per_page'     => 'require|number|max:500|min:1',
//            'current_page' => 'require|number|min:1',
//            'name|病虫害名称 '  => 'max:16',
//            //'type' =>'in:N,Q,H',
//        ]);
//        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));
//
//
//        $dbRes = PestsDb::ls($data);
//
//        return Communal::return_Json($dbRes);
//    }/*不需要此方法了*/

//    function query()
//    {
//        $data     = Communal::getPostJson();
//        $checkout = $this->checkout($data['did'], 1);
//        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
//        unset($data['did']);
//
//        $result = $this->validate($data, 'Pests.id');
//        if ($result !== true) return Communal::return_Json(Error::validateError($result));
//
//        $dbRes = PestsDb::query($data['id']);
//
//        return Communal::return_Json($dbRes);
//    }/*不需要此方法了*/

    /*已改*/
    //有害生物信息列表
    function pestInfo()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new Validate([
            'per_page'     => 'require|number|max:30|min:1',
            'current_page' => 'require|number|min:1'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $dbRes = PestsDb::pestInfo($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //寄主树种信息列表-web端
    function plantInfo()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $dbRes = PestsDb::plantInfo($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //寄主树种信息列表
    function plantApp()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $dbRes = PestsDb::plantApp($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //根据危害类型选择对应有害生物--APP端
    function typeInfo()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new Validate([
            'per_page'     => 'require|number|max:30|min:1',
            'current_page' => 'require|number|min:1',
            'name|有害生物种类'  => 'max:10',
            'type|有害生物类型'  => 'require|in:1,2,3'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $dbRes = PestsDb::typeInfo($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //根据危害类型选择对应有害生物--web端
    function pestType()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new Validate([
            'name|有害生物种类' => 'max:10',
            'type|有害生物类型' => 'require|in:1,2,3'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $dbRes = PestsDb::pestType($data);

        return Communal::return_Json($dbRes);
    }


    //根据有害生物选择其危害部位
//    function partInfo()
//    {
//        $data     = Communal::getPostJson();
//        $checkout = $this->checkout($data['did'], 1);
//        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
//        unset($data['did']);
//
//        //$result = $this->validate($data, 'Pests.imageId');
//        // if ($result !== true) return Helper::reJson(Errors::Error($result));
//
//        $dbRes = PestsDb::partInfo($data['id']);
//
//        return Communal::return_Json($dbRes);
//    } /*不需要此方法了*/

}