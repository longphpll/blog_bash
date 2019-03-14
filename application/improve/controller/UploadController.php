<?php
/**
 * Created by qiumu.
 * User: Administrator
 * Date: 2017/12/28 
 * Time: 11:32
 */
namespace app\improve\controller;

use think\Controller;
use think\Db;
use app\improve\validate\BaseValidate;
use tool\Error;
use tool\Communal;


/*
 * 文件上传接口
 */
 
class UploadController extends Controller
{

    public function upload(){
        // 获取表单上传文件
        $files = request()->file('images');
        if (empty($files)) return Communal::return_Json(Error::error('找不到文件'));
        if (count($files) > 6) return Communal::return_Json(Error::error('图片不能超过六张'));
        $url = [];
        foreach ($files as $key => $file) {
            $info = $file->move(Errors::FILE_ROOT_PATH. DS. 'uploads');
            if($info){
                // 成功上传后 获取上传信息
                $name = 'file'.DS .'uploads'. DS .$info->getSaveName();
                $data = [
                    'path' => $name,
                    'create_time' => date('Y-m-d H:i:s', time())
                ];
                $a = Db::table('improve_uploads')->insertGetId($data);
                if ($a < 0) return Communal::return_Json(Error::error('图片添加失败'));
                $url[$key] = Db::table('improve_uploads')->where('id',$a)->field('id,path')->find();
                if (empty($url[$key])) return Communal::return_Json(Error::error('上传的图片未找到'));
            }else{
                // 上传失败获取错误信息
                return Communal::return_Json(Error::error($info->getError()));
            }    
        }
        return Communal::return_Json(Communal::successData($url));
    }

    public function oneUpload(){
        // 获取表单上传文件
        $file = request()->file('image');
        if (empty($file)) return Communal::return_Json(Error::error('找不到文件'));
        $info = $file->move(Errors::FILE_ROOT_PATH. DS. 'uploads');
        if($info){
            // 成功上传后 获取上传信息
            $name = 'file'.DS .'uploads'. DS .$info->getSaveName();
            $data = [
                'path' => $name,
                'create_time' => date('Y-m-d H:i:s', time())
            ];
            $a = Db::table('improve_uploads')->insertGetId($data);
            if ($a < 0) return Communal::return_Json(Error::error('图片添加失败'));
            $url = Db::table('improve_uploads')->where('id',$a)->field('id,path')->find();
            if (empty($url))return Communal::return_Json(Error::error('上传的图片未找到'));
        }else{
            // 上传失败获取错误信息
            return Communal::return_Json(Error::error($info->getError()));
        }
        return Communal::return_Json(Communal::successData($url));
    }

}