<?php
/**
 * Created by qiumu.
 * Date: 2019/02/15
 * 检疫站相关信息
 */

namespace app\improve\controller;

use app\improve\model\QuarantineCaseDb;
use app\improve\validate\BaseValidate;
use tool\Communal;
use tool\Error;
use tool\BaseDb;
use base_frame\RedisBase;

class QuarantineCaseController extends RedisBase
{
    /** 已改
     * 修改日期：2019.03.06
     * 修改人：余思渡
     * 修改内容: 2019.03.08  修改数据返回格式
    */
    function add(){
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $result = $this->validate($data, 'QuarantineCase.add');
        if($result !== true) return  Communal::return_Json(Error::validateError($result));
        $regionVerify=Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        $data['adder'] = $checkout[1]->uid;
        $data['adder_name'] = $checkout[1]->name;
        $data['region_name'] = BaseDb::regionName($data['region']);
        $dbRes = QuarantineCaseDb::add($data);
        return Communal::return_Json($dbRes);
    }

    /** 已改
     * 修改日期：2019.03.06
     * 修改人：余思渡
     * 修改内容: 2019.03.08  修改数据返回格式
    */
    function ls(){
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //追加参数
        $adder = $checkout[1]->uid;
        $rid = $checkout[1]->rid;
        //参数验证
        $validate = new BaseValidate([
            'per_page' =>'require|number|between:1,50',
            'current_page' =>'require|number|min:1',
            'year|年份' => 'max:4',
            'region|区域' => 'max:20'
        ]);
        if (!$validate->check($data)){
            $error = Error::error($validate->getError());
            return Communal::return_Json($error);
        }
        //区域验证(带片区搜索的情形使用)
        if(!array_key_exists("region", $data))
        {
            $data['region'] = $checkout[1]->region;
        }else{
            //如果有写入片区搜索，执行片区验证
            $regionVerify = Communal::regionVerify($data,$checkout[1]);
            if(!$regionVerify){
                return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
            }
        }
        $dbRes = QuarantineCaseDb::ls($data,$rid,$adder);
        return Communal::return_Json($dbRes);
    }

    /**
     * 修改日期：2019.03.06
     * 修改人：余思渡
     * 修改内容: 2019.03.08  修改数据返回格式
    */
    function query(){
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数验证
        $validate = new BaseValidate([
            'id' =>'require|number|min:1'
        ]);
        if (!$validate->check($data)){
            $error = Errors::Error($validate->getError());
            return Communal::return_Json($error);
        }
        $dbRes = QuarantineCaseDb::query($data['id']);
        return Communal::return_Json($dbRes);
    }

    /** 已改
     * 修改日期：2019.03.06
     * 修改人：余思渡
    */
    function edit()
    {
        //参数获取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数验证
        $validate = new BaseValidate([
            'id' =>'require|number|min:1',
            'region|区域' => 'max:6'
        ]);
        if (!$validate->check($data)){
            $error = Error::error($validate->getError());
            return Communal::return_Json($error);
        }
        //区域验证
        $regionVerify = Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        $data['adder'] = $checkout[1]->uid;
        $data['region_name'] = BaseDb::regionName($data['region']);
        $dbRes = QuarantineCaseDb::edit($data);
        return Communal::return_Json($dbRes);
    }
    /** 已改
     * 修改日期：2019.03.06 2019.03.08
     * 修改人：余思渡
    */
    function deleteChecked()
    {
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数验证
        $result = $this->validate($data, 'QuarantineCase.deleteChecked');
        if ($result !== true) return Communal::return_Json(Error::validateError($result));
        $dbRes = QuarantineCaseDb::deleteChecked($data['ids']);
        return Communal::return_Json($dbRes);
    }

    // 获取上传导入的execl文件
    function imports()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $file = request()->file('file');
        // 移动到框架应用根目录/public/file/execl 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'file' . DS . 'execl');
            if($info){
                // 获取文件完整路径
                $filename = ROOT_PATH . 'public' . DS . 'file' . DS . 'execl'. DS . $info->getSaveName();
                // 获取文件后缀名
                $exts = $info->getExtension();
                // 获取上传人
                $adder = $auth[1]['s_uid'];
                // 读取Execl 内容
                $result = $this->importExecl($filename, $exts, 3,$adder);
                return json_encode($result);
            }else{
                // 上传失败获取错误信息
                return Helper::reJson(Errors::Error($file->getError()));
            }
        }
    }

    // 读取Execl 内容
    function importExecl($filename, $exts = 'xls', $or,$adder)
    {
        // 导入PHPExcel类库
        require_once '../extend/PHPExcel.php';
        //实例化phpexcel类
        $PHPExcel = new \PHPExcel(); 
        if ($exts == 'xls') {
            require_once '../extend/PHPExcel/Reader/Excel5.php';
            $PHPReader = new \PHPExcel_Reader_Excel5();
        } else {
            require_once '../extend/PHPExcel/Reader/Excel2007.php';
            $PHPReader = new \PHPExcel_Reader_Excel2007();
        }
        // 载入文件
        $PHPExcel = $PHPReader->load($filename);
        // 获取表中的第一个工作表，如果是获取第二个，把0改成1，依次类推
        $currentSheet = $PHPExcel->getSheet(0);
        // 获取总列数
        $allColumn = $currentSheet->getHighestColumn();
        //  获取总行数
        $allRow = $currentSheet->getHighestRow();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        for ($currentRow = 1; $currentRow <= $allRow ; $currentRow++) { 
            // 从哪列开始，A表示第一列
            for ($currentColumn = 'A'; $currentColumn <= $allColumn ; $currentColumn++) { 
                // 数据坐标
                $address = $currentColumn . $currentRow;
                // 读取到的数据，保存到数组$data中
                $cell = $currentSheet->getCell($address)->getValue();
                if(is_object($cell))  $cell= $cell->__toString();
                $data[$currentRow - 1][$currentColumn] = $cell;
            }
        }
        // 写入数据库操作
        return $dbRes = $this->insert_data($data,$adder);
    }

    // 导入数据库
    function insert_data($data,$adder)
    {
        // 获取年份和区域
        $title = explode('-',$data[0]['A']);
        $city_name = $title[3].$title[4];
        $region = BaseDb::regionCode($city_name,43,2);
        if(!empty($region)){
            $area_name = $title['5'];
            $info['region'] = BaseDb::regionCode($area_name,$region,3);
            $info['region_name'] = BaseDb::areaName($info['region']);
        }
        // 产地检疫
        $info['place_tree_should'] = $data[4]['B'];
        $info['place_tree_real'] = $data[5]['B'];

        $info['seed_breed_should'] = $data[4]['C'];
        $info['seed_breed_real'] = $data[5]['C'];

        $info['flowers_base_real'] = $data[4]['D'];
        $info['flowers_base_should'] = $data[5]['D'];

        $info['economic_forest_should'] = $data[4]['E'];
        $info['economic_forest_real'] = $data[5]['E'];

        $info['chinese_medicine_base_should'] = $data[4]['F'];
        $info['chinese_medicine_base_real'] = $data[5]['F'];

        $info['timber_forest_should'] = $data[4]['G'];
        $info['timber_forest_real'] = $data[5]['G'];

        $info['wood_should'] = $data[4]['H'];
        $info['wood_real'] = $data[5]['H'];

        $info['bamboo_should'] = $data[4]['I'];
        $info['bamboo_real'] = $data[5]['I'];

        $info['fruit_should'] = $data[4]['G'];
        $info['fruit_real'] = $data[5]['G'];

        $info['chinese_medicine_should'] = $data[4]['K'];
        $info['chinese_medicine_real'] = $data[5]['K'];

        $info['flowers_should'] = $data[4]['L'];
        $info['flowers_real'] = $data[5]['L'];

        // 调运检疫
        $info['dispatch_tree_should'] = $data[4]['N'];
        $info['dispatch_tree_real'] = $data[4]['N'];

        $info['dispatch_breed_should'] = $data[4]['O'];
        $info['dispatch_breed_real'] = $data[4]['O'];

        $info['dispatch_bamboo_should'] = $data[4]['P'];
        $info['dispatch_bamboo_real'] = $data[4]['P'];

        $info['dispatch_fruit_should'] = $data[4]['Q'];
        $info['dispatch_fruit_real'] = $data[4]['Q'];

        $info['dispatch_should'] = $data[4]['R'];
        $info['dispatch_real'] = $data[4]['R'];

        $info['dispatch_medicine_should'] = $data[4]['S'];
        $info['dispatch_medicine_real'] = $data[4]['S'];

        // 检疫情况统计
        $info['epidemic_rate'] = $data[6]['B'];
        $info['epidemic_number'] = $data[6]['N'];
        $info['quarantine_rate'] = $data[7]['B'];
        $info['quarantine_treatment'] = $data[7]['N'];
        $info['quarantine_treatment_rate'] = $data[8]['B'];
        $info['fee'] = $data[9]['I'];
        $info['frequency'] = $data[11]['K'];
        $info['fine'] = $data[12]['K'];   
        
        // 无检疫对象苗圃
        $info['country_number'] = $data[9]['E'];
        $info['country_area'] = $data[10]['E'];
        $info['province_number'] = $data[11]['E'];
        $info['province_area'] = $data[12]['E'];

        // 其他信息
        $create_time = date('Y-m-d H:i:s');
        $info['create_date'] = $create_time;
        $info['update_date'] = $create_time;
        $info['remark'] = $data[9]['N'];
        $info['year'] = $title['0'];
        $info['status'] = 1;   
        $info['adder'] = $adder;
        $info['adder_name'] = BaseDb::name($adder);
        // 插入数据
        $result = QuarantineCaseDb::insert_add($info);
        return ['code'=> 's_ok','var'=> 1];  
    }

    
    //导出
    function recordExcel(){
        $data = $_GET;
        // $condition=[];
        // if(!empty($data['condition'])){
        //     $condition = $data['condition'];//检索条件
        //     unset($data['condition']);
        // }else{
        //     $condition['region'] = cookie('s_region'); 
        // }
        $res = QuarantineCaseDb::exportls($data);
        if ($res[0]){
            $dataRes = $res[1];
            if(empty($dataRes)){
                return json_encode(["code" => 'error',"var" => ['未找到数据']]); 
            }
            $name = $dataRes['year'].'年'.$dataRes['region_name'].'森林植物检疫情况汇总表';
            $rowTotal = 13;
            $columnTotal = 19;
            customExport($name,$rowTotal,$columnTotal,$dataRes);
        }
    }

    //检疫员统计 (已改)
    /**
     * 修改日期：2019.03.06
     * 修改人：余思渡
    */
    function statistics(){
        $data=Communal::getPostJson();
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $validate = new BaseValidate([
            'region|区域' => 'require|max:20',
            'year|年份' => 'require|max:4'
        ]);
        if (!$validate->check($data)){
            $error = Error::error($validate->getError());
            return Communal::return_Json($error);
        }
        $regionVerify=Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        $region_name = BaseDb::regionName($data['region']);
        $result = QuarantineCaseDb::statisticsList($data,$region_name);
        return Communal::return_Json($result);
    }

}

?>