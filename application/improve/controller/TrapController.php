<?php
/**
 * Created by qiumu.
 * User: Administrator
 * Date: 2017/12/28 
 * Time: 11:32
 */
namespace app\improve\controller;

use think\Controller;
use app\improve\model\TrapDb;
use app\improve\model\BaseDb;
use app\improve\validate\BaseValidate;
use app\improve\controller\Share;
use tool\Communal;
use tool\Error;
use base_frame\RedisBase;
use tool\BaseDb as ToolBaseDb;

/*
 * 诱捕器管理
 */
 
class TrapController extends RedisBase
{
    public function add()
    {
        $data=Communal::getPostJson();
        $checkout= $this->checkout($data['did'],1);
        if($checkout[0] !== true) return  Communal::return_Json(Error::error($checkout[1],'',$checkout[2]));
        $result = $this->validate($data, 'Trap.add');
        if($result !== true) return  Communal::return_Json(Error::validateError($result));

        //区域检验 判断当前用户是否有权限操作
        $regionVerify=Communal::regionVerify($data,$checkout[1]);
        if(!$regionVerify){
            return  Communal::return_Json(Error::error(Error::Region_Verify_Error));
        }
        $data['adder'] = $checkout[1]->uid;
        //$data['region_name'] = BaseDb::areaName($data['region']);
        $data['region_name'] = ToolBaseDb::regionName($data['region']);
        $dbRes = TrapDb::add($data);
        return Communal::return_Json($dbRes);
    }

    function ls($sample = false)
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' =>'require|number|max:50|min:1',
            'current_page' =>'require|number|min:1',
            'region|区域' => 'max:20|region',
            'number|诱捕器编号' => 'max:20',
            'unit|所属单位' => 'max:12',
            'type|设备状态' => 'in:1,2,3',
            'state|维护状态' => 'in:1,2',
            'label|标签状态' => 'in:1,2'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        if(!array_key_exists("region", $data))
        {
            $data['region'] = cookie('s_region');   
        }
        $region_result = Helper::authRegion($data);
        if(!$region_result) return Helper::reJson(Errors::AUTH_PREMISSION_REJECTED);
        $result = TrapDb::ls($data,$sample);
        return Helper::reJson($result);
    }
    
    
    function devicels()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' =>'require|number|max:50|min:1',
            'current_page' =>'require|number|min:1',
            'region|区域' => 'max:20|region',
            'number|诱捕器编号' => 'max:20',
            'unit|所属单位' => 'max:12',
            'type|设备状态' => 'in:1,2,3',
            'state|维护状态' => 'in:1,2',
            'label|标签状态' => 'in:1,2'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $adder = $auth[1]['s_uid'];
        $result = TrapDb::devicels($data,$adder);
        return Helper::reJson($result);
    }

	//总体概况-诱捕器分布图
    function sampleMap($sample = true)
    {
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'per_page' =>'require|number|max:50|min:1',
            'current_page' =>'require|number|min:1',
            'region|区域' => 'max:20',
            'number|诱捕器编号' => 'max:20',
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        if(!array_key_exists("region", $data))
        {
            $data['region'] = cookie('s_region');   
        }
        $result = TrapDb::ls($data,$sample);
        return Helper::reJson($result);
    }

    function query()
    {
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Trap.id');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = TrapDb::query($data['id']);
        return Helper::reJson($dbRes);
    }

    function edit()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Trap.edit');
        if ($result !== true) return Helper::reJson(Errors::Error($result));
        $dbRes = TrapDb::edit($data);
        return Helper::reJson($dbRes);
    }

    // 诱捕器编号查询
    function trapls()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'number|诱捕器编号' => 'max:20'
        ]);
        $result = TrapDb::trapls($data);
        return Helper::reJson($result);
    }

    // 所属单位查询
    function unitls()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'unit|所属单位' => 'max:10'
        ]);
        $result = TrapDb::unitls($data);
        return Helper::reJson($result);
    }

    //生成标签
    function info()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return Helper::reJson($auth);
        require_once '../vendor/phpqrcode/phpqrcode.php';
        $data = Helper::getPostJson();
        $validate = new BaseValidate([
            'ids' =>'require'
        ]);
        if (!$validate->check($data)) return Helper::reJson(Errors::Error($validate->getError()));
        $requestUrl = $_SERVER['HTTP_HOST'];
        $infos = TrapDb::label($data['ids']);
        if (empty($infos)) return  Helper::reJson($info);
        $result = TrapDb::toTrap($data['ids']);
        if(!$result[0]) return Helper::reJson(Errors::LABEL_NO_EXIT);
        foreach ($infos[1] as $key => $val) {
            $url = $val['number'].','.$val['drug_model'];
            $path = "file/code/";//生成的二维码所在目录
            if(!file_exists($path)){ //自动生成二维码保存目录  
                mkdir($path, 0700,true);
            }
            $time = $val['number'].'_code'.'.png';//生成的二维码文件名
            $fileName = $path.$time;//1.拼装生成的二维码文件路径
            $level = 'H' ;//容错级别
            $size = 5;//生成图片大小
            //实例化qrcode方法
            $object = new \QRcode();
            //生成二维码。第二个参数是false，代表不保存路径
            $object->png($url,$fileName, $level, $size, 3);
            $file_name = iconv("utf-8","gb2312",$time);
            //保存二维码地址到数据库
            $res = TrapDb::codePath($val['id'],$fileName);
            $molabel = TrapDb::modifyLabel($val['id']);
            //生成诱捕器信息
            $width = 170;
            $height = 283;
            $img = imagecreatetruecolor($width,$height);//创建真彩色的图像
            $white = imagecolorallocate($img, 255, 255, 255);//设置颜色 白色
            // 填充颜色
            imagefill($img,0,0,$white); 
            //原图图像写入新建真彩位图中
            // imagecopymerge($img,$src_im,0,0,0,0,$width,$height,100);
            $black = imagecolorallocate($img, 0, 0, 0);//设置字体颜色  黑色
            $dist = imagecolorallocate($img, 0, 0, 0); //设置线颜色   黑色
            imageline($img, 260, 40, 7, 40, $dist);//绘制线
            //绘制文本
            imagettftext($img, 12, 0, 5, 30, $black, "fonts/simkai.ttf", "诱捕器编号:".$val['number']."\n");
            imagettftext($img, 12, 0, 6, 30, $black, "fonts/simkai.ttf", "诱捕器编号:".$val['number']."\n");
            imagettftext($img, 10, 0, 5, 60, $black, "fonts/simkai.ttf", "单位名称:".$val['unit']."\n");
            imagettftext($img, 10, 0, 6, 60, $black, "fonts/simkai.ttf", "单位名称:".$val['unit']."\n");
            imagettftext($img, 10, 0, 5, 75, $black, "fonts/simkai.ttf", "项目用途:".$val['purpose']."\n");
            imagettftext($img, 10, 0, 6, 75, $black, "fonts/simkai.ttf", "项目用途:".$val['purpose']."\n");
            imagettftext($img, 10, 0, 5, 90, $black, "fonts/simkai.ttf", "维护公司:".$val['company']);
            imagettftext($img, 10, 0, 6, 90, $black, "fonts/simkai.ttf", "维护公司:".$val['company']);
            imagettftext($img, 10, 0, 5, 109, $black, "fonts/simkai.ttf", "扫描二维码获取诱捕器信息");
            imagettftext($img, 10, 0, 6, 109, $black, "fonts/simkai.ttf", "扫描二维码获取诱捕器信息");
            //诱捕器信息图片保存地址
            $info_path = "file/info/";//生成的诱捕器信息所在目录
            if(!file_exists($info_path)){ //自动生成诱捕器信息保存目录  
                mkdir($info_path, 0700,true);
            }
            $name = $val['number'].'_info'.'.png';//生成的诱捕器信息文件名
            $infoName = $info_path.$name;//拼装生成的诱捕器信息文件路径
            //保存标签
            imagepng($img,$infoName);
            //选择生成标签的二维码图片和信息图片
            $label_path = 'file/info/'.$val['number'].'_info.png';//获取诱捕器信息图片地址
            $info_image = imagecreatefrompng($label_path);//创建一个新图像
            $code_path = 'file/code/'.$val['number'].'_code.png';//获取二维码图片地址
            $code_image = imagecreatefrompng($code_path);//创建一个新图像
            //合成图片
            //imagecopymerge ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h , int $pct )---拷贝并合并图像的一部分
            //将 src_im 图像中坐标从 src_x，src_y 开始，宽度为 src_w，高度为 src_h 的一部分拷贝到 dst_im 图像中坐标为 dst_x 和 dst_y 的位置上。
            //两图像将根据 pct 来决定合并程度，其值范围从 0 到 100。当 pct = 0 时，实际上什么也没做，当为 100 时对于调色板图像本函数和 imagecopy() 完全一样，它对真彩色图像实现了 alpha 透明。
            imagecopymerge($info_image, $code_image, -5, 110, 0, 0, imagesx($code_image), imagesy($code_image), 100);//拷贝并合并图像
            //设置标签图片保存地址
            $source_path = "file/label/";//生成的标签所在目录
            if(!file_exists($source_path)){ //自动生成标签保存目录  
                mkdir($source_path, 0700,true);
            }
            $label_file = $val['number'].'_label'.'.png';//生成的标签文件名
            $labelName = $source_path.$label_file;//拼装生成的标签文件路径
            //保存标签
            imagepng($info_image,$labelName);//输出图片
            ob_clean();
        }
        return json(["code" => 's_ok',"var" => 1]);
    }
    

    //标签导出
    function label()
    {
        if(is_array($_GET)&&count($_GET)>0){//判断是否有Get参数 
            if(isset($_GET["ids"])){ //判断所需要的参数是否存在
                $ids = $_GET["ids"];
                $requestUrl = $_SERVER['HTTP_HOST'];
                $res = TrapDb::trap($ids);
                if(!$res[0]) return Helper::reJson(Errors::LABEL_NO_EXIT);
                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $numbers = $res[1];
                // 新建默认页面
                $section = $phpWord->createSection();
                $imageStyle = array(
                    'wrappingStyle' => 'square',
                    'positioning' => 'absolute',
                    'posHorizontalRel' => 'margin',
                    'posVerticalRel' => 'line',
                );
                foreach ($numbers as $key => $val) {
                    $source = file_get_contents('file/label/'.$val['number'].'_label.png');
                    $section->addImage($source,$imageStyle);
                }
                $fileName = "诱捕器标签表_".date('_YmdHis').".doc";
                header('pragma:public');
                header('Content-Type: application/vnd.ms-word; charset=UTF-8');
                header("Content-Disposition: attachment;filename='$fileName'");
                ob_clean();//清除缓存
                flush();
                $objWrite = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
                $objWrite->save('php://output');
                exit;
            }else{
               return Helper::reJson(Errors::SELECT_LABEL_DATA);
            }
        } 
    }

    // 导出字段显示
    function exportList(){
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = [
            "region_name"=>"区域名称",
            "number"=>"诱捕器编号",
            "unit"=>"所属单位",
            "purpose"=>"项目用途",
            "company"=>"维护公司",
            "relation_name"=>"维护公司联系人",
            "relation_tel"=>"维护公司联系人电话",
            "amount"=>"挂设数量",
            "drug_model"=>"药剂型号",
            "drug_batch"=>"计划用药批次",
            "create_time"=>"创建时间"
        ];
        return json(["code" => 's_ok',"var" => [$data]]);
    }

    //导出
    function exportExcel(){
        $data = $_GET;
        $condition=[];
        if(!empty($data['condition'])){
            $condition=$data['condition'];//检索条件
            unset($data['condition']);
        }
        $keys = implode(',',array_keys($data));
        $field = substr($keys,26);
        $title = array_values($data);
        array_splice($title, 0, 1);
        $res = TrapDb::exportls($data,$field,$condition);
        if ($res[0]){
            $dataRes = $res[1];
            if(empty($dataRes)){
                return json(["code" => 'error',"var" => ['未找到数据']]); 
            }
            foreach ($dataRes as $key => $val) {
                $result[] = $val;
            }
            $name = '诱捕器记录表';
            excelExport($name, $title, $result);
        }
    }

}