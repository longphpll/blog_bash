<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/23
 * Time: 13:51
 */

namespace app\improve\controller;


use app\improve\model\CommonDb;
use app\improve\model\UserDb;
use think\Controller;
use think\Cookie;
use think\Db;

class  CommonController extends Controller
{
    public function queryRegion()
    {
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Region.query');
        if (true !== $result) return Helper::reJson(Errors::validateError($result));
        $region = $data['parentId'];
        $dbRes = CommonDb::queryRegion($region);
        if (is_array($dbRes)) return Helper::reJson([true, $dbRes]);
        return Helper::reJson(Errors::Error($dbRes));
    }

    public function ListRegion()
    {
        $auth = Helper::auth();
        if (!$auth[0]) return Helper::reJson($auth);
        $data = Helper::getPostJson();
        $result = $this->validate($data, 'Region.query');
        if (true !== $result) return Helper::reJson(Errors::validateError($result));
        $region = cookie('s_region');
        $dbRes=Db::table('c_region')->field("id as  value, name as label , parentId ")
            ->whereLike('id',$data['parentId'].'%')->select();
        return json($this->list_to_tree($dbRes,$region));
    }
	
   public function list_to_tree($list,$region, $pk='value',$pid = 'parentId',$child = 'children',$root=0) {
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {

                if(strlen($data['value']) <= strlen($region)){
                    if ($data['value'] != substr($region,0,strlen($data['value']))){
                        unset($list[$key]);
                        //$list[$key]['disabled'] = true;
                    }
                }else{
                    if ($region != substr($data['value'],0,strlen($region))){
                        unset($list[$key]);
                        //$list[$key]['disabled'] = true;
                    }
                }

                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
                unset($list[$key]['parentId']);
            }
        }
        $tree = array_filter($tree);
        return $tree;
    }

    private function addApk()
    {
        return Helper::reJson($this . $this->addApkImpl());
    }

   private function addApkImpl()
    {
        $apk = request()->file('apk');
        if (empty($apk)) return Errors::ATTACH_NOT_FIND;
        if (!$apk->checkSize(100 * 1024 * 1024)) return Errors::MAX_FILE_SIZE;
        $preName = DS . $apk->getInfo()['name'];
        $uploadRes = Helper::upload($apk, $preName);
        return [is_array($uploadRes), is_array($uploadRes) ? $uploadRes[0] : $uploadRes];
    }

}

?>