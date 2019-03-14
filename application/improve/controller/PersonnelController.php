<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/16
 * Time: 16:12
 */

namespace app\improve\controller;

use think\Controller;
use app\improve\model\PersonnelDb;
use app\improve\validate\BaseValidate;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/**
 * 人员管理
 */
class PersonnelController extends RedisBase
{
    /*已改 Lxl*/
    function add()
    {
        //只有管理员可以执行添加操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Personnel.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $data['adder'] = $checkout[1]->uid;

        $dbRes = PersonnelDb::add($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    function ls()
    {
        //所有用户都可以进行查询操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'per_page'     => 'require|number|max:50|min:1',
            'current_page' => 'require|number|min:1',
            'name|姓名'      => 'max:6',
            'unit|所属单位'    => 'max:10',
            'job|是否在岗'     => 'in:1,2',
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $result = PersonnelDb::ls($data);

        return Communal::return_Json($result);
    }

    /*已改*/
    function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Personnel.query');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = PersonnelDb::query($data['id']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    function edit()
    {
        //只有管理员可以执行编辑操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Personnel.edit');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = PersonnelDb::edit($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    function deleteChecked()
    {
        //只有管理员可以执行删除操作
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'Personnel.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = PersonnelDb::delete($data['ids']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    // 导出字段显示
    function exportList()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $data = [
            "name"      => "姓名",
            "unit"      => "所在单位",
            "sex"       => "性别",
            "birthday"  => "出生日期",
            "job"       => "岗位",
            "technical" => "职称",
            "education" => "学历",
            "academy"   => "毕业院校",
            "tel"       => "联系号码",
            "entryday"  => "从业时长",
            "guard"     => "在岗情况"
        ];
        return json_encode(["code" => 's_ok', "var" => [$data]]);
    }

    /*已改*/
    //导出
    function exportExcel()
    {
//        $data[] = $_GET; //用下面的方式赋值
        $data     = $_GET;
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $condition = [];
        if (!empty($data['condition'])) {
            $condition = $data['condition'];//检索条件
            unset($data['condition']);
        } else {
            $condition['region'] = session('staff')['region'];
        }

        $keys  = implode(',', array_keys($data));
        $field = substr($keys, 31);
        $title = array_values($data);
        array_splice($title, 0, 1);

//        print_r($title);//Array ( [0] => 姓名 [1] => 性别 [2] => 出生日期 )
//        print_r($field);//name,sex,birthday
//        die;

        $res = PersonnelDb::exportls($data, $field, $condition);

        if ($res[0]) {
            $dataRes = $res[1];
            if (empty($dataRes)) {
                return Communal::return_Json(Error::error('未找到相关数据'));
            }
            foreach ($dataRes as $key => $val) {
                if (!empty($val['sex'])) {
                    switch ($val['sex']) {
                        case "1":
                            $val['sex'] = "男";
                            break;
                        case "2":
                            $val['sex'] = "女";
                            break;
                    }
                }
                if (!empty($val['guard'])) {
                    switch ($val['guard']) {
                        case "1":
                            $val['guard'] = "是";
                            break;
                        case "2":
                            $val['guard'] = "否";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '人员管理记录表';
            excelExport($name, $title, $result);
        }
    }
}