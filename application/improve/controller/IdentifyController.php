<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/16
 * Time: 16:12
 */

namespace app\improve\controller;

use app\improve\validate\BaseValidate;
use app\improve\model\IdentifyDb;
use think\Controller;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/*智能识别*/

class IdentifyController extends RedisBase
{
    /*已改 Lxl*/
    //历史记录添加
    function add()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Identify.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        //通过昆虫名称获取该记录id
        $res = IdentifyDb::Identify($data['name']);
        unset($data['name']);
        $data['sid'] = $res[1]['id'];
        $data['uid'] = $checkout[1]->uid;
        $images      = request()->file("image");

        $dbRes = IdentifyDb::add($data, $images);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //智能识别数据首次录入
    function insectAdd()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Identify.entry');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        //通过昆虫名称获取该记录id
        $res = IdentifyDb::Identify($data['name']);
        //如果为true,说明从表 improve_identify 智能识别数据表 查找到了id
        if ($res[0]) return Communal::return_Json(Error::error('该数据已存在'));

        $data['adder'] = $checkout[1]->uid;
        $images        = request()->file("image");

        $dbRes = IdentifyDb::baseAdd($data, $images);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //智能识别已有数据录入
    function insectExist()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Identify.exist');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));
        unset($data['subjects']);

        $res = IdentifyDb::Identify($data['name']);
        if ($res[0]) {
            $data['sid'] = $res[1]['id'];
        } else {
            return Communal::return_Json(Error::error('未找到相应数据'));
        }

        $images = request()->file("image");

        $dbRes = IdentifyDb::baseEdit($data, $images);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //智能识别数据查询
    function insectls()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'per_page'     => 'require|number|max:50|min:1',
            'current_page' => 'require|number|min:1',
            'name|昆虫名称'    => 'max:20',
            'subjects|科目'  => 'max:20',
            'adder|录入人'    => 'max:4',
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = IdentifyDb::insectls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    //智能识别数据详情
    function insectQuery()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Identify.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = IdentifyDb::insectQuery($data['id']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //历史记录列表
    function historyls($sample = false)
    {
        return Communal::return_Json($this->lsDb($sample));
    }

    /*已改*/
    private function lsDb($sample = false)
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) {
            return Error::error($checkout[1], '', $checkout[2]);
        }
        unset($data['did']);

        $validate = new BaseValidate([
            'per_page'        => 'require|number|max:50|min:1',
            'current_page'    => 'require|number|min:1',
            'start_time|识别时间' => 'dateFormat:Y-m-d',
            'uid'             => 'require|max:32'
        ]);

        //如果数据验证成功就调用模型层里的 ls() 方法
        return $validate->check($data) ? IdentifyDb::ls($data, $sample) : Error::error($validate->getError());
    }

    /*已改*/
    //我的收藏列表
    function collectls()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        return $this->historyls(true);
    }

    /*已改*/
    //历史记录地图概况
    function historyMap()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'per_page'        => 'number|max:50|min:1',
            'start_time|识别时间' => 'dateFormat:Y-m-d'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = IdentifyDb::historyMap($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    //详情
    function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Identify.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = IdentifyDb::query($data['id']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //是否收藏
    function collect()
    {
        //只有管理员可以执行编辑操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Identify.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $data['uid'] = $checkout[1]->uid;

        $dbRes = IdentifyDb::collect($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    //清空或删除历史记录
    function deleteChecked()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Identify.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = IdentifyDb::deleteChecked($data['ids'],$checkout[1]->uid);

        return Communal::return_Json($dbRes);
    }
}