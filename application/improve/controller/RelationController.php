<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2018/7/3
 * Time: 14:20
 */

namespace app\improve\controller;

use think\Controller;
use app\improve\model\BaseDb;
use app\improve\model\RelationDb;
use app\improve\validate\BaseValidate;
use tool\Communal;
use tool\Error;
use tool\BaseDb as ToolBaseDb;
use base_frame\RedisBase;

/**
 * 物种数据库
 */
 
class RelationController extends RedisBase
{
    /** 物种数据库-添加(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function add(){
        //只有管理员可以执行添加操作
//        $auth = Helper::auth([1]);
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did'],1);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        if ($data['attribute'] == 1){
            $result = $this->validate($data, 'Relation.add');
            if ($result !== true) return Communal::reJson(Errors::Error($result));
        }else{
            $result = $this->validate($data, 'Relation.enemy_add');
            if ($result !== true) return Helper::reJson(Errors::Error($result));
        }
        $images = request()->file("images");
        $dbRes = RelationDb::add($data,$images);
        return Helper::reJson($dbRes);
    }

    //生物别称
    /** 物种数据库-生物别称(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function biologicalName(){
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        $result = RelationDb::biologicalName($data);
//        return Helper::reJson($result);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $result = RelationDb::biologicalName($data);
        return Communal::return_Json($result);
    }

    //生物别称-本地化
    /** 物种数据库-生物别称本地化(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function biologicalNameLocal(){
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        $result = RelationDb::biologicalNameLocal($data);
//        return Helper::reJson($result);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $result = RelationDb::biologicalNameLocal($data);
        return Communal::return_Json($result);
    }
        
    
    // 一级分类
    /** 物种数据库-一级分类(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function firstLevel(){
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        $result = RelationDb::firstLevel($data);
//        return Helper::reJson($result);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $result = RelationDb::firstLevel($data);
        return Communal::return_Json($result);
    }

    // 生物类型,如病害，虫害，有害植物
    /** 物种数据库-二级分类(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function level(){
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        $validate = new BaseValidate([
//            'parentId|父级id' => 'require',
//            'type|类型' => 'require',
//        ]);
//        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
//        $result = RelationDb::level($data);
//        return Helper::reJson($result);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        //参数验证
        $validate = new BaseValidate([
            'parentId|父级id' => 'require',
            'type|类型' => 'require',
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));
        $result = RelationDb::level($data);
        return Communal::return_Json($result);
    }

    // 生物类型-获取
    /** 物种数据库-生物类型-获取(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function typeList(){
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $result = RelationDb::typeList();
//        return Helper::reJson($result);
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $result = RelationDb::typeList();
        return Communal::return_Json($result);
    }

    //生物名称查询(原)
    //生物种类是否存在查询(来自接口文档)
    /** 物种数据库-生物类型-获取(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function findName(){
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        $validate = new BaseValidate([
//            'name|生物名称' => 'require',
//        ]);
//        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
//        $result = RelationDb::findName($data);
//        return Helper::reJson($result);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $validate = new BaseValidate([
            'name|生物名称' => 'require',
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));
        $result = RelationDb::findName($data);
        return Helper::reJson($result);
    }

    /** 物种数据库-列表(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function ls()
    {
        //所有用户都可以进行查询操作
//        $auth = Helper::auth();
//        if (!$auth[0]) return Helper::reJson($auth);
//        $data = Helper::getPostJson();
//        $validate = new BaseValidate([
//            'per_page' =>'require|number|max:50|min:1',
//            'current_page' =>'require|number|min:1',
//           // 'name|生物名称' => 'number|min:1',
//            'plant|寄主树种' => 'number|min:1',
//            'category|生物类别' => 'number',
//            'local|是否本地化' => 'number|in:1,2'
//        ]);
//        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
//        $result = RelationDb::ls($data);
//        return Helper::reJson($result);
        //参数抓取
        $data=Communal::getPostJson();
        //DID登录验证
        $checkout= $this->checkout($data['did']);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $validate = new BaseValidate([
            'per_page' =>'require|number|between:1,50',
            'current_page' =>'require|number|min:1',
            // 'name|生物名称' => 'number|min:1',
            'plant|寄主树种' => 'number|min:1',
            'category|生物类别' => 'number',
            'local|是否本地化' => 'number|in:1,2'
        ]);
        if (!$validate->check($data)) return Communal::return_Json(Error::error($validate->getError()));
        $result = RelationDb::ls($data);
        return Communal::return_Json($result);
    }

    /** 物种数据库-详情()
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：权限重写
    */
    function query()
    {
        //所有用户都可以查看详情
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Relation.query');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = RelationDb::query($data['id']);
        return Helper::reJson($dbRes);
    }

    function edit()
    {
        //只有管理员可以执行编辑操作
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        if ($data['attribute'] == 1){
            $result = $this->validate($data, 'Relation.add');
            if ($result !== true) return Helper::reJson(Errors::Error($result));
        }else{
            $result = $this->validate($data, 'Relation.enemy_add');
            if ($result !== true) return Helper::reJson(Errors::Error($result));
        }
        $images = request()->file("images");
        $dbRes = RelationDb::edit($data,$images);
        return Helper::reJson($dbRes);
    }

    function deleteChecked()
    {
        //只有管理员可以执行删除操作
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Relation.ids');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = RelationDb::deleteChecked($data['ids']);
        return Helper::reJson($dbRes);
    }

    function appls()
    {
        //所有用户都可以进行查询操作
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' =>'require|number|max:50|min:1',
            'current_page' =>'require|number|min:1',
            'name|生物名称' => 'max:20',
            'category|生物类别' => 'number|in:1,2'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        if(!array_key_exists("region", $data))
        {
            $data['region'] = cookie('s_region');   
        }
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $result = RelationDb::appls($data);
        return Helper::reJson($result);
    }

    //本地化信息列表
    function localList()
    {
        //所有用户都可以进行查询操作
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' =>'require|number|max:50|min:1',
            'current_page' =>'require|number|min:1',
            //'pest|生物名称' => 'number|min:1',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $result = RelationDb::localList($data);
        return Helper::reJson($result);
    }

    function localDelete()
    {
        //只有管理员可以执行删除操作
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Relation.ids');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = RelationDb::localDelete($data['ids']);
        return Helper::reJson($dbRes);
    }

    //本地化
    function local()
    {
        //只有管理员可以执行本地化操作
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Relation.ids');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = RelationDb::local($data['ids']);
        return Helper::reJson($dbRes);
    }

    //根据有害生物id查询其寄生植物--APP端
	function relevance()
    {
        //所有用户都可以查看详情
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' =>'require|number|max:30|min:1',
            'current_page' =>'require|number|min:1',
            'name|寄主' => 'max:8',
            'id|有害生物种类' => 'require|number'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $dbRes = RelationDb::relevance($data);
        return Helper::reJson($dbRes);
    }

     //根据有害生物id查询其寄生植物--web端
	function plantWeb()
    {
        //所有用户都可以查看详情
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'name|寄生树种' => 'max:8',
            'id|有害生物种类' => 'require|number'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $dbRes = RelationDb::plantWeb($data);
        return Helper::reJson($dbRes);
    }

    // 获取上传导入的execl文件
    function imports()
    {
        $file = request()->file('file');
        // 移动到框架应用根目录/public/file/execl 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'file' . DS . 'execl');
            if($info){
                // 获取文件完整路径
                $filename = ROOT_PATH . 'public' . DS . 'file' . DS . 'execl'. DS . $info->getSaveName();
                // 获取文件后缀名
                $exts = $info->getExtension();
                // 读取Execl 内容
                $result = $this->importExecl($filename, $exts, 3);
                return json_encode($result);
            }else{
                // 上传失败获取错误信息
                return Helper::reJson(Errors::Error($file->getError()));
            }
        }
    }

    // 读取Execl 内容
    function importExecl($filename, $exts = 'xls', $or)
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
        // $data = $currentSheet->toArray();
        // dump($data);die;
        // 获取总列数
        // $all = $currentSheet->getMergeCells();
        $allColumn = $currentSheet->getHighestColumn();
        //  获取总行数
        $allRow = $currentSheet->getHighestRow();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        for ($currentRow = 2; $currentRow <= $allRow ; $currentRow++) { 
            // 从哪列开始，A表示第一列
            for ($currentColumn = 'A'; $currentColumn <= $allColumn ; $currentColumn++) { 
                // 数据坐标
                $address = $currentColumn . $currentRow;
                // 读取到的数据，保存到数组$data中
                $cell = $currentSheet->getCell($address)->getValue();
                if ($cell instanceof PHPExcel_RichText) {
                    $cell = $cell->_toString();
                }
                $data[$currentRow - 1][$currentColumn] = $cell;
            }
        }
        // 写入数据库操作
        return $dbRes = $this->insert_data($data);
    }

    // 导入数据库
    function insert_data($data)
    {
        $create_time = date('Y-m-d H:i:s');
        foreach ($data as $k => $v) {
            if ($k != 0) {
                // 相关导入信息
                 //  生物类型，有益生物为1，有害生物为2
                 switch ($v['A'])
                 {
                     case "有害生物":$info['attribute'] = 1;$info['attribute_name'] = '有害生物';
                     break;
                     case "天敌":$info['attribute'] = 2;$info['attribute_name'] = '天敌';
                     break;
                 }
                 // 二级类型
                if (empty($v['B'])){
                    $info['types'] = '';
                    $info['types_name'] = '';
                }else{
                    switch ($v['B'])
                    {
                        case "寄生性天敌":$info['genre'] = 10;$info['genre_name'] = '寄生性天敌';
                            break;
                        case "生防菌":$info['genre'] = 12;$info['genre_name'] = '生防菌';
                            break;
                        case "生物病毒":$info['genre'] = 13;$info['genre_name'] = '生物病毒';
                            break;
                        case "捕食性天敌":$info['genre'] = 14;$info['genre_name'] = '捕食性天敌';
                            break;
                    }
                    $res = RelationDb::type($v['B']);
                    if(empty($res[1])){
                        return Errors::Error('系统中未找到:'.$v['B'].'类型信息');                    
                    }
                    $types = '';
                    foreach ($res[1] as $k => $val) {
                        $types = $types .','. $val['id'];
                    };
                    $info['types'] = substr($types, 1); 
                    $info['types_name'] = $v['B'];
                }
                 // 有害生物类型,有害生物如病害,虫害,有害植物，天敌如 捕食性天敌
                 if (empty($v['C'])){
                    $info['genre'] = '';
                    $info['genre_type'] = '';
                }else{
                    switch ($v['C'])
                    {
                        case "虫害":$info['genre'] = 7;$info['genre_type'] = 1;$info['genre_name'] = '虫害';
                            break;
                        case "病害":$info['genre'] = 8;$info['genre_type'] = 2;$info['genre_name'] = '病害';
                            break;
                        case "有害植物":$info['genre'] = 9;$info['genre_type'] = 3;$info['genre_name'] = '有害植物';
                            break;
                    }
                }
                // 生物名称
                $info['cn_name'] = $v['D'];
                // 别名 
                 if (empty($v['E'])){
                    $info['alias'] = '暂未填写';  
                }else{
                    $info['alias'] = $v['E'];  
                }
                // 拉丁名
                 if (empty($v['F'])){
                    $info['eng_name'] = '';  
                }else{
                    $info['eng_name'] = $v['F'];  
                }
                // 目
                if (empty($v['G'])){
                    $info['order'] = '暂未填写';  
                }else{
                    $info['order'] = $v['G'];  
                }
                // 科
                if (empty($v['H'])){
                    $info['section'] = '暂未填写';  
                }else{
                    $info['section'] = $v['H'];  
                }
                // 属
                if (empty($v['I'])){
                    $info['genus'] = '暂未填写';  
                }else{
                    $info['genus'] = $v['I'];  
                }
                 // 寄生
                if (empty($v['J'])){
                    $info['plant_ids'] = '';
                    $info['plant_name'] = '';  
                }else{
                    $plant = explode(',',$v['J']);
                    foreach ($plant as $kp => $vp) {
                        $plant_dbRes = RelationDb::plantName($vp);
                        if (empty($plant_dbRes)) {
                            $add_plant = RelationDb::addPlant($vp);
                        }
                    }
                    $plant_res = RelationDb::plant($v['J']);
                    if(empty($plant_res[1])){
                        return Errors::Error('系统中未找到'.$v['J'].'寄主信息');                    
                    }
                    $plants = '';
                    foreach ($plant_res[1] as $k => $val) {
                        $plants = $plants .','. $val['id'];
                    }
                    $info['plant_ids'] = substr($plants, 1);  
                    $info['plant_name'] = $v['J'];  
                }
                // 危害部位
                if (empty($v['K'])){
                    $info['harm_part'] = '';  
                    $info['harm_part_name'] = '';
                }else{
                    $part_res = RelationDb::part($v['K']);
                    if(empty($part_res[1])){
                        return Errors::Error('系统中未找到'.$v['K'].'危害部位信息');                    
                    }
                    $parts = '';
                    foreach ($part_res[1] as $k => $val) {
                        $parts = $parts .','. $val['type'];
                    }
                    $info['harm_part'] = substr($parts, 1);  
                    $info['harm_part_name'] = $v['K'];                    
                }
                // 防治方法
                if (empty($v['L'])){
                    $info['prevention_way'] = '';  
                }else{
                    $info['prevention_way'] = $v['L'];  
                }
                // 生活习性
                if (empty($v['M'])){
                    $info['living_habits'] = '';  
                }else{
                    $info['living_habits'] = $v['M'];  
                }
                // 形态特征
                if (empty($v['N'])){
                    $info['shape_character'] = '';  
                }else{
                    $info['shape_character'] = $v['N'];  
                }
                // 分布范围
                if (empty($v['O'])){
                    $info['region'] = '';
                }else{
                    $info['region'] = $v['O'];  
                }
                $info['create_time'] = $create_time;
                $info['update_time'] = $create_time;
                $info['status'] = 1;
                $info['local'] = 1;
                // 插入数据
                $result = RelationDb::insert_add($info);
            }
        }
        return ['code'=> 's_ok','var'=> 1];
    }

    // 导出字段显示
    function exportList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = [
            "attribute"=>"生物一级类型",
            "types_name"=>"生物二级类型",
            "genre"=>"物种类型",
            "cn_name"=>"中文名",
            "alias"=>"别名",
            "eng_name"=>"拉丁名",
            "order"=>"目",
            "section"=>"科",
            "genus"=>"属",
            "plant_name"=>"寄主",
            "harm_part_name"=>"危害部位",
            "prevention_way"=>"防治方法",
            "living_habits"=>"生活习性",
            "shape_character"=>"形态特征",
            "region"=>"分布范围",
            "local"=>"是否本地化",
            "img" => "图片"

        ];
        return json(["code" => 's_ok',"var" => [$data]]);
    }


    //导出
    function exportExcel(){
        $data = $_GET;
        $condition=[];
        if(!empty($data['condition'])){
            $condition = $data['condition'];//检索条件
            unset($data['condition']);
        }else{
            $condition['region'] = cookie('s_region'); 
        }
        // 是否导出图片
        if (isset($data['img'])){
            $img = true;
            unset($data['img']);
        }else{
            $img = false;
        }
        $keys = implode(',',array_keys($data));
        $field = substr($keys,30);
        $title = array_values($data);
        array_splice($title, 0, 1);
        $res = RelationDb::exportls($data,$field,$img,$condition);
        if ($res[0]){
            $dataRes = $res[1];
            if(empty($dataRes)){
                return json_encode(["code" => 'error',"var" => ['未找到数据']]); 
            }
            foreach ($dataRes as $key => $val) {
                unset($val['id']);
                if (!empty($val['attribute'])){
                    switch ($val['attribute'])
                    {
                        case "1":$val['attribute'] = "有害生物";
                            break;
                        case "2":$val['attribute'] = "天敌";
                            break;
                    }
                }
                if (!empty($val['genre'])){
                    switch ($val['genre'])
                    {
                        case "7":$val['genre'] = "虫害";
                            break;
                        case "8":$val['genre'] = "病害";
                            break;
                        case "9":$val['genre'] = "有害植物";
                            break;
                        case "10":$val['genre'] = "寄生性天敌";
                            break;
                        case "12":$val['genre'] = "生防菌";
                            break;
                        case "13":$val['genre'] = "生物病毒";
                            break;
                        case "14":$val['genre'] = "捕食性天敌";
                            break;
                    }
                }
                if (!empty($val['local'])){
                    switch ($val['local'])
                    {
                        case "1":$val['local'] = "否";
                            break;
                        case "2":$val['local'] = "是";
                            break;
                    }
                }
                $result[] = $val;
            }
            $name = '物种数据库记录表';

            excelExport($name, $title, $result);
        }
    }


}

