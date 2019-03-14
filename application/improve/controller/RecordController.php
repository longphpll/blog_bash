<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2018/3/16
 * Time: 16:12
 */

namespace app\improve\controller;

use think\Controller;
use app\improve\model\RecordDb;
use app\improve\validate\BaseValidate;
use tool\Communal;
use tool\Error;
use tool\BaseDb;
use base_frame\RedisBase;

/**
 * 物种数据库-采集记录 
 */

class RecordController extends RedisBase
{
    /** 物种数据库-采集记录-添加(已改)
     * 修改人：余思渡
     * 修改时间：2019-03-09
     * 修改内容：权限重写
    */
    function add(){
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did'],1);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数请求验证
        $validate = new BaseValidate([
            'name|生物名称' => 'require|max:30',
            'xmin|请框选图片' => 'require',
            'ymin|请框选图片' => 'require',
            'xmax|请框选图片' => 'require',
            'ymax|请框选图片' => 'require',
            'width|图片宽度' => 'require',
            'height|图片高度' => 'require'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));
        $data['adder'] = $checkout[1]->uid;
        // 获取审核图
        $file = request()->file('images');
        // 获取原图
        $yt_file = request()->file('yt_images');
        if (empty($file)) {
            return [false,['请上传图片']];
        }else{
            $info = $file->validate(['ext'=>'jpg,png,jpeg'])->move(Error::FILE_ROOT_PATH. DS. 'cj_record');
            if($info){
                $name = 'file'.DS .'cj_record'. DS .$info->getSaveName();
            }else{
                $name = '';
            }
        }
        // 原图保存
        if (!empty($yt_file)) {
            $yt_size = $yt_file->getInfo()['size'];
            $yt_name = $yt_file->getInfo()['name'];
            $yt_info = $yt_file->validate(['ext'=>'jpg,png,jpeg'])->move(Error::FILE_ROOT_PATH. DS. 'cj_record');
            if($info){
                $yt_img_path = 'file'.DS .'cj_record'. DS .$info->getSaveName();
            }else{
                $yt_img_path = '';
            }
            $res = makeXml($yt_name,$yt_img_path,$yt_size,$data);//????有何意义？返回的XML并未被调用
        }
        $data['report'] = BaseDb::regionName($checkout[1]->region);
        $result = RecordDb::add($data,$name);
        return Communal::return_Json($result);
    }

    /** 物种数据库-采集记录-列表(已改)
     * 修改人：余思渡
     * 修改时间：2019-03-09
     * 修改内容：权限重写
    */
    function ls()
    {
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $validate = new BaseValidate([
            'per_page' =>'require|number|between:1,100',
            'current_page' =>'require|number|min:1',
            'type' => 'require|in:1,2,3',
            'name|生物名称' => 'max:50',
            'person|采集人' => 'max:10',
            'start_time|采集开始时间' => 'dateFormat:Y-m-d',
            'end_time|采集结束时间' => 'dateFormat:Y-m-d'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));
        $result = RecordDb::ls($data);
        return Communal::return_Json($result);
    }

    /** 物种数据库-采集记录-详情(已改)
     * 修改人：余思渡
     * 修改时间：2019-03-09
     * 修改内容：权限重写
    */
    function query()
    {
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        $result = $this->validate($data, 'Record.query');
//        if ($result !== true) return Helper::reJson(Errors::Error($result));
//        $dbRes = RecordDb::query($data['id']);
//        return Helper::reJson($dbRes);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数验证
        $result = $this->validate($data, 'Record.query');
        if ($result !== true) return Communal::return_Json(Error::error($result));
        //执行模型
        $dbRes = RecordDb::query($data['id']);
        return Communal::return_Json($dbRes);

    }

    /** 物种数据库-采集记录-审核()
     * 修改人：余思渡
     * 修改时间：2019-03-09
     * 修改内容：权限重写
    */
    function examine()
    {
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        $result = $this->validate($data, 'Record.examine');
//        if ($result !== true) return Helper::reJson(Errors::Error($result));
//        $data['auditor'] = $auth[1]['s_uid'];
//        $data['auditor_name'] = BaseDb::name($auth[1]['s_uid']);
//        $dbRes = RecordDb::examine($data);
//        return Helper::reJson($dbRes);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数验证
        $result = $this->validate($data, 'Record.examine');
        if ($result !== true) return Communal::return_Json(Error::error($result));
        //return json($checkout[1]);
        //追加参数
        $data['auditor'] = $checkout[1]->uid;
        $data['auditor_name'] = $checkout[1]->name;
        $dbRes = RecordDb::examine($data);
        return Communal::return_Json($dbRes);
    }

    // 导出字段显示
    function exportList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = [
            "name" => "生物名称",
            "create_time" => "采集时间",
            "report" => "采集人",
            "auditor_name" => "审核人",
            "examine_time" => "审核时间",
            "status" => "审核状态",
            "img" => "采集图片"
        ];
        return json_encode(["code" => 's_ok',"var" => [$data]]);
    }

    //导出
    function exportExcel(){
        $data[] = $_GET;
        $condition=[];
        if(!empty($data['condition'])){
            $condition = $data['condition'];//检索条件
            unset($data['condition']);
        }
        if (isset($data['img'])){
            $img = true;
            unset($data['img']);
        }else{
            $img = false;
        }
        $keys = implode(',',array_keys($data));
        $field = substr($keys,28);
        $title = array_values($data);
        array_splice($title, 0, 1);
        $res = RecordDb::exportls($data,$field,$img,$condition);
        if ($res[0]){
            $dataRes = $res[1];
            if(empty($dataRes)){
                return json_encode(["code" => 'error',"var" => ['未找到数据']]); 
            }
            foreach ($dataRes as $key => $val) {
                unset($val['id']);
                if (!empty($val['status'])){
                    switch ($val['status'])
                    {
                        case "1":$val['status'] = "待审核";
                            break;
                        case "2":$val['status'] = "已通过";
                            break;
                        case "3":$val['status'] = "未通过";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '生物采集记录表';
            excelExport($name, $title, $result);
        }
    }
}