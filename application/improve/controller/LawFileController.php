<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22 0022
 * Time: 10:04
 */

namespace app\improve\controller;

use app\improve\model\LawFileDb;
use app\improve\model\BaseDb as BaseDbModel;
use think\Controller;
use think\Validate;
use app\improve\validate\BaseValidate;

use base_frame\RedisBase;
use tool\Communal;
use tool\BaseDb;
use tool\Error;

/**
 * 法规文件
 */
class LawFileController extends RedisBase
{
    /*已改 Lxl*/
    public function add()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        unset($data['submit']);

        $result = $this->validate($data, 'LawFile.add');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $data['adder'] = $checkout[1]->uid;
        $attach        = request()->file('attach');
        if (!empty($attach)) {
            $info = $attach->validate(['ext' => 'doc,docx,xls,xlsx'])->move(Error::FILE_ROOT_PATH . DS . 'law');
            if ($info) {
                $name              = 'file' . DS . 'law' . DS . $info->getSaveName();
                $data['file_path'] = $name;
            } else {
                return Communal::return_Json(Error::error($attach->getError()));
            };
        }
        unset($data['attach']);
        $data['report'] = $checkout[1]->name;
//        $data['file_path'] = '134';//为了测试接口,需要给 file_path 一个值，表中该字段不能为空,正式使用时需注释该行

        $results = LawFileDb::add($data);

        return Communal::return_Json($results);

    }

    /*已改*/
    public function ls()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $validate = new BaseValidate([
            'create_time_min|发布开始时间' => 'dateFormat:Y-m-d',
            'create_time_max|发布结束时间' => 'dateFormat:Y-m-d',
            'per_page|每页数'           => 'require|number',
            'current_page|当前页'       => 'require|number',
            'adder|发布人'              => 'max:32',
            'sort|类别'                => 'in:1,2',
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));

        $results = LawFileDb::ls($data);

        return Communal::return_Json($results);
    }

    /*已改*/
    public function edit()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'LawFile.edit');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $data['adder'] = $checkout[1]->uid;
        $attach        = request()->file('attach');
        if (!empty($attach)) {
            unset($data['attach']);
            $info = $attach->validate(['ext' => 'doc,docx,ppt,pdf,xlsx,zip,rar,xls'])->move(Error::FILE_ROOT_PATH . DS . 'law');
            if ($info) {
                $name              = 'file' . DS . 'law' . DS . $info->getSaveName();
                $data['file_path'] = $name;
            } else {
                return Communal::return_Json(Error::error($attach->getError()));
            };
        } else {
            //如果编辑时没有上传图片
            $data['file_path'] = '';
            unset($data['attach']);
            $dbRes = LawFileDb::updateFile($data);
            return Communal::return_Json($dbRes);

        }
        unset($data['attach']);

        $data['report'] = $checkout[1]->name;

        $dbRes = LawFileDb::edit($data);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    public function query()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'LawFile.id');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = LawFileDb::query($data['id']);

        return Communal::return_Json($dbRes);
    }

    /*已改*/
    public function delete()
    {
        $data     = Communal::getPostJson();
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $result = $this->validate($data, 'LawFile.ids');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));

        $dbRes = LawFileDb::deleteChecked($data['ids']);

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
            "sort"    => "文件类型",
            "title"   => "文件标题",
            "content" => "文件内容",
            "report"  => "发布人"
        ];

        return json_encode(["code" => 's_ok', "var" => [$data]]);
    }

    /*已改*/
    //导出
    function exportExcel()
    {
        $data     = $_GET;
        $checkout = $this->checkout($data['did'], 1);
        if ($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1], '', $checkout[2]));
        unset($data['did']);

        $condition = array();
        if (!empty($data['condition'])) {
            $condition = $data['condition'];//检索条件
            unset($data['condition']);
        }

        $keys  = implode(',', array_keys($data));
        $field = substr($keys, 30);
        $title = array_values($data);
        array_splice($title, 0, 1);

//        print_r($title);//Array ( [0] => 文件类型 [1] => 文件标题 [2] => 备注 [3] => 发布人 )
//        print_r($field);//sort,title,content,report
//        die;

        $res = LawFileDb::exportls($data, $field, $condition);

        if ($res[0]) {
            $dataRes = $res[1];
            if (empty($dataRes)) {
                return Communal::return_Json(Error::error('未找到相关数据'));
            }
            foreach ($dataRes as $key => $val) {
                unset($val['id']);
                if (!empty($val['sort'])) {
                    switch ($val['sort']) {
                        case "1":
                            $val['sort'] = "一般文件";
                            break;
                        case "2":
                            $val['sort'] = "检疫管理文件";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '政策法规记录表';
            excelExport($name, $title, $result);
        }
    }
}