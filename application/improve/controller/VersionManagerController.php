<?php
/**
 * Created by PhpStorm.
 * User: LiuTao
 * Date: 2017/12/5/005
 * Time: 16:20
 */

namespace app\improve\controller;


use app\improve\model\CommonDb;
use think\Validate;
use tool\Communal;
use tool\Error;
use base_frame\RedisBase;
use app\improve\controller\UploadHelper;
class VersionManagerController extends RedisBase
{
    public $communal;
    public  function __construct()
    {
        parent::__construct();
        $this->communal=new Communal();
    }

    /**
     * 版本更新
     */
    function version_update()
    {
        return Communal::return_Json($this->version_update_imple());
    }

    function version_update_imple()
    {
        $data=$this->communal->getPostJson();
        $validate = new Validate([
            'version_code' => 'require|number',
        ]);
        if (!$validate->check($data)) return Error::error($validate->getError());
        $maxVersion = CommonDb::getMaxVersion();
        if (!is_array($maxVersion)) return Error::error($maxVersion);
        if ($data['version_code'] >= $maxVersion[0]) return Error::error('new version not find');
        $versionInfo = CommonDb::getVersionInfo($maxVersion[0]);
        return $versionInfo;
    }

    function addApk()
    {
        return Communal::return_Json($this->addApkImpl());
    }

    private function addApkImpl()
    {
        $data=Communal::getPostJson();
        $checkout= $this->checkout($data['did'],1);
        if($checkout[0] !== true) return  Error::error($checkout[1],'',$checkout[2]);
        $validate = new Validate([
            'version_num' => 'require|max:32',
            'content' => 'require|max:255',
            'force' => 'in:0,1',
        ]);
        if (!$validate->check($data)) return  Error::validateError($validate->getError());
        $apk = request()->file('apk');
        if (empty($apk)) return Error::error('找不到附件');
        if (!$apk->checkSize(100 * 1024 * 1024)) return Error::error('文件大小不能超过100M');
        $preName = DS . 'apk' . DS . $apk->getInfo()['name'];
        $uploadRes = UploadHelper::upload($apk, $preName);
        if (!$uploadRes[0]) return $uploadRes;
        //数据库修改
        $data['update_person'] = $checkout[1]->uid;
        $data['down_url'] = request()->host() . DS . 'file' . DS . $uploadRes[1];
        $dbRes = CommonDb::addVersion($data);
        return $dbRes;
    }

    function editVersioin()
    {
        return Helper::reJson($this->addApkImpl());
    }

    // web 显示 最新app信息
    function appVersioin()
    {
        return Helper::reJson($this->appVersioinInfo());
    }

    private function appVersioinInfo()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return $auth;
        $dbRes = CommonDb::appVersioinInfo();
        return [is_array($dbRes), $dbRes];
    }


    private function editVersioinImpl()
    {
        $auth = Helper::auth([1]);
        if (!$auth[0]) return $auth;
        $data = $_POST;
        $validate = new Validate([
            'version_code' => 'require|number',
            'version_num' => 'require|max:32',
            'content' => 'require|max:255',
            'force' => 'in:0,1',
            'down_url' => 'length:1-100',
        ]);
        if (!$validate->check($data)) return Errors::validateError($validate->getError());
        //是否存在version信息
        $vf = CommonDb::getVersionInfo($data['version_code']);
        if (!is_array($vf)) return [false, $vf];
        $apk = request()->file('apk');
        if (!empty($apk)) {
            if (!$apk->checkSize(100 * 1024 * 1024)) return Errors::MAX_FILE_SIZE;
            $preName = DS . 'apk' . DS . $apk->getInfo()['name'];
            $uploadRes = UploadHelper::upload($apk, $preName);
            if (!$uploadRes[0]) return $uploadRes;
            //删除原文件
            Helper::deleteFile($vf['down_url']);
            $data['down_url'] = request()->host() . DS . 'file' . DS . $uploadRes[1];
        }
        $data['update_person'] = $auth['s_uid'];
        //更新数据库
        $dbRes = CommonDb::updateVersion($data);
        return [is_array($dbRes), $dbRes];
    }

}