<?php

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;
use think\Exception;
use tool\Error;
use tool\Communal;
/**
 * 公共接口数据库操作
 * Created by xwpeng.
 */
class CommonDb
{
    static  function addRegion($data){
         try{
            return Db::table('c_region')->insert($data);
         }catch (Exception $e){
             return $e->getMessage();
         }
    }

    static  function queryRegion($region){
        try{
            return Db::table('c_region')
                ->where("parentId", $region)
                ->where("level",'in',[2,3,4])
                ->field('id as  value, name as lable ,level')->select();
        }catch (Exception $e){
            return $e->getMessage();
        }
    }

    static function getMaxVersion(){
        try{
            return Db::table('improve_version')->column('max(version_code)');
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    static function getVersionInfo($versionCode){
        try{
            $dbRes = Db::table('improve_version')
                ->where('version_code', $versionCode)->field('version_code, version_num, down_url, content, force')->find();
            return empty($dbRes) ? Error::error('未找到相应数据') : Communal::successData($dbRes);
        }catch (Exception $e){
            return Error::error($e->getMessage());
        }
    }
    
    static function appVersioinInfo(){
        try{
            $versionCode = Db::table('improve_version')->column('max(version_code)');
            $dbRes = Db::table('improve_version')
                ->where('version_code', $versionCode[0])->field('version_num, content, create_time')->find();
            return empty($dbRes) ? Errors::DATA_NOT_FIND : $dbRes;
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

      static function addVersion($data){
        try{
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $dbRes = Db::table('improve_version')->insertGetId($data);
            return $dbRes < 1 ? Error::error('上传失败') : Communal::success('上传成功');
        }catch (\Exception $e){
            return Error::error($e->getMessage());
        }
    }

    static function updateVersion($data){
        try{
            $data['update_time'] = date('Y-m-d H:i:s');
            $dbRes = Db::table('v_version')->update($data);
            return $dbRes < 1 ? Errors::UPDATE_ERROR : [$dbRes];
        }catch (Exception $e){
            return $e->getMessage();
        }
    }

}