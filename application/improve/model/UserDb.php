<?php

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use Exception;
use think\Error;
use think\Db;

use tool\Communal;

class UserDb
{

    static function add($data, $imgHead)
    {
        try {
            Db::startTrans();
            if (!empty(self::queryByTel($data['cellphone']))) return [false, ['手机号已被注册']];
            $data['create_time'] = date('Y-m-d H:i:s', time());
            $data['update_time'] = $data['create_time'];
            $rid                 = $data['rid'];
            switch ($rid) {
                case "1":
                    $data['user_role'] = "管理员";
                    break;
                case "2":
                    $data['user_role'] = "普通用户";
                    break;
            }
            $level = strlen($data['region']);
            switch ($level) {
                case "2":
                    $data['user_level'] = 1;
                    if ($rid == 1) {
                        $data['rid'] = 1;
                    } elseif ($rid == 2) {
                        $data['rid'] = 2;
                    } else {
                        $data['rid'] = 6;
                    }
                    break;
                case "4":
                    $data['user_level'] = 2;
                    if ($rid == 1) {
                        $data['rid'] = 3;
                    } elseif ($rid == 2) {
                        $data['rid'] = 4;
                    } else {
                        $data['rid'] = 6;
                    }
                    break;
                case "6":
                    $data['user_level'] = 3;
                    $data['rid']        = 5;
                    break;
            }
            $mids      = $data['mids'];
            $user_mold = implode(',', $mids);
            $mold_res  = Db::table('u_mold')->whereIn('mid', $user_mold)->field('describe')->select();
            $molds     = '';
            foreach ($mold_res as $key => $value) {
                $molds = $molds . ',' . $value['describe'];
            }
            $molds             = substr($molds, 1);
            $data['user_mold'] = $molds;
            unset($data['mids']);
            Db::table('frame_base_staff')->insertGetId($data);
            Db::name('u_user_role')->insert(["uid" => $data['uid'], "rid" => $rid]);
            foreach ($mids as $mid) {
                Db::name('u_user_mold')->insert(["uid" => $data['uid'], "mid" => $mid]);
            }
            if (!empty($imgHead)) {
                foreach ($imgHead as $image) {
                    $info = $image->move(Errors::FILE_ROOT_PATH . DS . 'user');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $name = 'file' . DS . 'user' . DS . $info->getSaveName();
                        // 保存
                        $a = Db::table('frame_base_staff')->where('uid', $data['uid'])->update(['imgHead' => $name]);
                        if ($a < 0) return Errors::IMAGES_INSERT_ERROR;
                    }
                }
            }
            Db::commit();
            return [true, $data['uid']];
        } catch (Exception  $e) {
            try {
                Db::rollback();
            } catch (Exception $e) {
                return Errors::Error($e->getMessage());
            }
            return Errors::Error($e->getMessage());
        }
    }

    static function center($data, $imgHead)
    {
        try {
            $data['update_time'] = date('Y-m-d H:i:s', time());
            $dbRes               = Db::table('frame_base_staff')->update($data);
            $result              = [];
            //图片上传
            if (!empty($imgHead)) {
                foreach ($imgHead as $image) {
                    $info = $image->move(Errors::FILE_ROOT_PATH . DS . 'user');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $name = 'file' . DS . 'user' . DS . $info->getSaveName();
                        // 保存
                        $a = Db::table('frame_base_staff')->where('uid', $data['uid'])->update(['imgHead' => $name, 'update_time' => $data['update_time']]);
                        if ($a < 0) return Errors::IMAGES_INSERT_ERROR;
                    }
                }
                $img = Db::table('frame_base_staff')->where('uid', $data['uid'])->field('imgHead')->find();
                if ($a > 0) {
                    $img['status'] = '2';
                    if (Helper::lsWhere($data, "pwd")) {
                        $img['status'] = '3';
                    }
                    return $dbRes == 1 ? [true, $img] : Errors::UPDATE_ERROR;
                }
            } else {
                $result['status'] = '1';
                return $dbRes == 1 ? [true, $result] : Errors::UPDATE_ERROR;
            }
        } catch (\think\Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function wxCenter($data)
    {
        try {
            $data['update_time'] = date('Y-m-d H:i:s', time());
            $result              = [];
            //图片上传
            if (!empty($data['imgHead'])) {
                $dbRes = Db::table('frame_base_staff')->where('uid', $data['uid'])->update($data);
                $img   = Db::table('frame_base_staff')->where('uid', $data['uid'])->field('imgHead')->find();
                if ($dbRes > 0) {
                    $img['status'] = '2';
                    if (Helper::lsWhere($data, "pwd")) {
                        $img['status'] = '3';
                    }
                    return $dbRes == 1 ? [true, $img] : Errors::UPDATE_ERROR;
                }
            } else {
                unset($data['imgHead']);
                $dbRes            = Db::table('frame_base_staff')->update($data);
                $result['status'] = '1';
                return $dbRes == 1 ? [true, $result] : Errors::UPDATE_ERROR;
            }
        } catch (\think\Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function login($data)
    {
        try {
            $user = Db::table("u_user")
                ->where("tel", $data['cellphone'])
                ->column('uid,pwd,cellphone,salt,rid,region,region_name area_name,imgHead,job,dept,origin,create_time,name,status,examine');
            if (empty($user)) return Errors::UNREGISTERED;
            else $user = array_values($user)[0];
            if ($user['cellphone'] != $data['cellphone']) return Errors::LOGIN_ERROR;
            if ($user['pwd'] !== md5($data['pwd'] . $user['salt'])) return Errors::LOGIN_ERROR;
            if ($user['status'] == '-1') return Errors::FORBIDDEN_STATUS;
            if ($user['examine'] == '0') return Errors::EXAMINE_STATUS;
            if ($user['examine'] == '-1') {
                $reason = Db::table('frame_base_staff')
                    ->where('uid', $user['uid'])
                    ->field('reason')->find();
                if (!empty($reason)) return [false, ['审核未通过,原因为：' . $reason['reason']]];
            }
            $mold                = Db::table("u_user_mold")->alias("um")
                ->where('um.uid', $user['uid'])
                ->join('u_mold m', 'm.mid = um.mid')
                ->field('m.mid,m.describe')
                ->select();
            $region_level        = Db::table('frame_base_staff')->alias('bt')
                ->where('bt.uid', $user['uid'])
                ->join('c_region r', 'r.id = bt.region')
                ->field('r.level')
                ->find();
            $user['user_level']  = $region_level['level'];
            $user["molds"]       = $mold;
            $roles               = Db::table("u_user_role")->alias('ur')
                ->where('ur.uid', $user['uid'])
                ->join('u_role r', 'r.rid = ur.rid')
                ->field('r.rid,r.name')
                ->select();
            $user["roles"]       = $roles;
            $region              = Db::table('frame_base_staff')->alias('ur')->where('ur.uid', $user['uid'])
                ->join('c_region r', 'r.id = ur.region', 'left')
                ->join('c_region r2', 'r.parentId = r2.id', 'left')
                ->join('c_region r3', 'r2.parentId = r3.id', 'left')
                ->join('c_region r4', 'r3.parentId = r4.id', 'left')
                ->field('r.name r1,r2.name r2,r3.name r3,r4.name r4')->find();
            $user['region_name'] = $region;
            $result              = Helper::transFormation($user);
            return [true, $result];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function plogin($data)
    {
        try {
            if ($data['pwd'] != '111111') return Errors::LOGIN_ERROR;
            $user = Db::table("b_trap")
                ->field('relation_tel cellphone,status')
                ->where("relation_tel", $data['cellphone'])
                ->where("status", 1)
                ->find();
            if (empty($user)) return Errors::UNREGISTERED;
            if ($user['cellphone'] != $data['cellphone']) return Errors::LOGIN_ERROR;
            if ($user['status'] == 2) return Errors::FORBIDDEN_STATUS;
            return [true, $user];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function register($data, $imgHead)
    {
        try {
            Db::startTrans();
            if (!empty(self::queryByTel($data['cellphone']))) return [false, '手机号已被注册'];
            if (empty($imgHead)) return [false, ['请上传头像']];
            $data['create_time'] = date('Y-m-d H:i:s', time());
            $data['update_time'] = $data['create_time'];
            $rid                 = $data['rid'];
            $mid                 = $data['mid'];
            if ($rid != 2) return [false, ['请联系管理员进行注册']];
            if ($mid != 2) return [false, ['请联系管理员进行注册']];
            // 用户级别
            $level = strlen($data['region']);
            switch ($level) {
                case "2":
                    $data['user_level'] = 1;
                    break;
                case "4":
                    $data['user_level'] = 2;
                    break;
                case "6":
                    $data['user_level'] = 3;
                    break;
            }
            $data['user_role'] = "普通用户";
            // 用户类型
            $mold_res          = Db::table('u_mold')->where('mid', $data['mid'])->field('describe')->find();
            $data['user_mold'] = $mold_res['describe'];
            unset($data['rid']);
            unset($data['mid']);
            // 插入数据
            Db::table('frame_base_staff')->insertGetId($data);
            Db::name('u_user_role')->insert(["uid" => $data['uid'], "rid" => $rid]);
            Db::name('u_user_mold')->insert(["uid" => $data['uid'], "mid" => $mid]);
            if (!empty($imgHead)) {
                foreach ($imgHead as $image) {
                    $info = $image->move(Errors::FILE_ROOT_PATH . DS . 'user');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $name = 'file' . DS . 'user' . DS . $info->getSaveName();
                        // 保存
                        $a = Db::table('frame_base_staff')->where('uid', $data['uid'])->update(['imgHead' => $name]);
                        if ($a < 0) return Errors::IMAGES_INSERT_ERROR;
                    }
                }
            }
            Db::commit();
            return [true, $data['uid']];
        } catch (Exception  $e) {
            try {
                Db::rollback();
            } catch (Exception $e) {
                return Errors::Error($e->getMessage());
            }
            return Errors::Error($e->getMessage());
        }
    }

    static function wxRegister($data)
    {
        try {
            Db::startTrans();
            if (!empty(self::queryByTel($data['cellphone']))) return [false, '手机号已被注册'];
            $data['create_time'] = date('Y-m-d H:i:s', time());
            $data['update_time'] = $data['create_time'];
            $level               = strlen($data['region']);
            switch ($level) {
                case "2":
                    $data['user_level'] = 1;
                    $data['rid']        = 2;
                    break;
                case "4":
                    $data['user_level'] = 2;
                    $data['rid']        = 4;
                    break;
                case "6":
                    $data['user_level'] = 3;
                    $data['rid']        = 5;
                    break;
            }
            $data['user_role'] = "普通用户";
            // 用户类型
            $mold_res          = Db::table('u_mold')->where('mid', 2)->field('describe')->find();
            $data['user_mold'] = $mold_res['describe'];
            // 插入数据
            Db::table('frame_base_staff')->insertGetId($data);
            Db::name('u_user_role')->insert(["uid" => $data['uid'], "rid" => 2]);
            Db::name('u_user_mold')->insert(["uid" => $data['uid'], "mid" => 2]);
            Db::commit();
            return [true, $data['uid']];
        } catch (Exception  $e) {
            try {
                Db::rollback();
            } catch (Exception $e) {
                return Errors::Error($e->getMessage());
            }
            return Errors::Error($e->getMessage());
        }
    }


    static function queryByTel($tel)
    {
        return Db::table('frame_base_staff')->where('cellphone', $tel)->find();
    }

    static function queryByPwd($uid)
    {
        return Db::table('frame_base_staff')->where('uid', $uid)->field('pwd,salt')->find();
    }

    static function updateStatus($uid, $status)
    {
        try {
            $db = Db::table('frame_base_staff')->where(["uid" => $uid])->update(['status' => $status]);
            return $db == 1 ? [true, $db] : Errors::UPDATE_ERROR;
        } catch (\think\Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function updatePwd($uid)
    {
        try {
            $salt   = Helper::getRandChar(6);
            $record = [
                'pwd'  => md5('123456' . $salt),
                'salt' => $salt
            ];
            $db     = Db::table('frame_base_staff')->where("uid", $uid)->update($record);
            return $db == 1 ? [true, $db] : Errors::UPDATE_ERROR;
        } catch (\think\Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function examineStatus($uid, $examine)
    {
        $res = Db::table('frame_base_staff')->where('uid', $uid)->update(['examine' => $examine]);
        return $res >= 0 ? true : false;
    }


    static function auditResult($uid, $reason)
    {
        $res = Db::table('frame_base_staff')->where('uid', $uid)->update(['reason' => $reason]);
        return $res >= 0 ? true : false;
    }

    static function edit($data, $imgHead)
    {
        try {
            Db::startTrans();
            Db::table('u_user_role')->where("uid", $data['uid'])->delete();
            Db::table('u_user_mold')->where("uid", $data['uid'])->delete();
            //insert
            if (isset($data['rid'])) {
                switch ($data['rid']) {
                    case "1":
                        $data['user_role'] = "管理员";
                        break;
                    case "2":
                        $data['user_role'] = "普通用户";
                        break;
                }
                $dbRes = Db::name('u_user_role')->insert(["uid" => $data['uid'], "rid" => $data['rid']]);
            }
            if (isset($data['mids'])) {
                foreach ($data['mids'] as $mid) {
                    Db::name('u_user_mold')->insert(["uid" => $data['uid'], "mid" => $mid]);
                }
                //update
                $mids      = $data['mids'];
                $user_mold = implode(',', $mids);
                $mold_res  = Db::table('u_mold')->whereIn('mid', $user_mold)->field('describe')->select();
                $molds     = '';
                foreach ($mold_res as $key => $value) {
                    $molds = $molds . ',' . $value['describe'];
                }
                $molds             = substr($molds, 1);
                $data['user_mold'] = $molds;
            }
            unset($data['rid']);
            unset($data['mids']);
            $data['update_time'] = date('Y-m-d H:i:s', time());
            $daRes               = Db::table('frame_base_staff')->update($data);
            //图片上传
            if (!empty($imgHead)) {
                $img_del = Db::table('frame_base_staff')->where('uid', $data['uid'])->field('imgHead')->find();
                if ($img_del < 0) return Errors::DELETE_ERROR;
                $res = Helper::deleteFile($img_del['imgHead']);
                foreach ($imgHead as $image) {
                    $info = $image->move(Errors::FILE_ROOT_PATH . DS . 'user');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $name = 'file' . DS . 'user' . DS . $info->getSaveName();
                        // 保存
                        $a = Db::table('frame_base_staff')->where('uid', $data['uid'])->update(['imgHead' => $name, 'update_time' => $data['update_time']]);
                        if ($a < 0) return Errors::IMAGES_INSERT_ERROR;
                    }
                }
            }
            if ($daRes > 0) {
                Db::commit();
                return [true, 1];
            }

        } catch (\think\Exception $e) {
            try {
                Db::rollback();
            } catch (\think\Exception $e) {
                return Errors::Error($e->getMessage());
            }
            return Errors::Error($e->getMessage());
        }
    }

    static function deleteChecked($uid)
    {
        try {
            $dataRes = Db::table('frame_base_staff')->whereIn('uid', $uid)->where('examine', '-1')->delete();
            return empty($dataRes) ? Errors::DELETE_ERROR : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function editExamineUser($data)
    {
        try {
            Db::startTrans();
            Db::table('u_user_role')->where("uid", $data['uid'])->delete();
            Db::table('u_user_mold')->where("uid", $data['uid'])->delete();
            $rid = $data['rid'];
            switch ($rid) {
                case "1":
                    $data['user_role'] = "管理员";
                    break;
                case "2":
                    $data['user_role'] = "普通用户";
                    break;
            }
            $dbRes = Db::name('u_user_role')->insert(["uid" => $data['uid'], "rid" => $rid]);
            $level = strlen($data['region']);
            switch ($level) {
                case "2":
                    $data['user_level'] = 1;
                    if ($rid == 1) {
                        $data['rid'] = 1;
                    } elseif ($rid == 2) {
                        $data['rid'] = 2;
                    } else {
                        $data['rid'] = 6;
                    }
                    break;
                case "4":
                    $data['user_level'] = 2;
                    if ($rid == 1) {
                        $data['rid'] = 3;
                    } elseif ($rid == 2) {
                        $data['rid'] = 4;
                    } else {
                        $data['rid'] = 6;
                    }
                    break;
                case "6":
                    $data['user_level'] = 3;
                    $data['rid']        = 5;
                    break;
            }
            if (isset($data['mids'])) {
                $user_molds = explode(',', $data['mids']);
                foreach ($user_molds as $mid) {
                    Db::name('u_user_mold')->insert(["uid" => $data['uid'], "mid" => $mid]);
                }
                $mold_res = Db::table('u_mold')->whereIn('mid', $data['mids'])->field('describe')->select();
                $molds    = '';
                foreach ($mold_res as $key => $value) {
                    $molds = $molds . ',' . $value['describe'];
                }
                $molds             = substr($molds, 1);
                $data['user_mold'] = $molds;
            }
            unset($data['mids']);
            $data['update_time'] = date('Y-m-d H:i:s', time());
            $daRes               = Db::table('frame_base_staff')->where('examine', '0')->where('uid', $data['uid'])->update($data);
            if ($daRes > 0) {
                Db::commit();
                return [true, 1];
            }
        } catch (\think\Exception $e) {
            try {
                Db::rollback();
            } catch (\think\Exception $e) {
                return Errors::Error($e->getMessage());
            }
            return Errors::Error($e->getMessage());
        }
    }

    static function query($uid)
    {
        try {
            $user = Db::table("u_user")->alias('nb')
                ->where("nb.uid", $uid)
                ->join('c_region q', 'q.id = nb.region', 'left')
                ->column('nb.uid,nb.region,nb.name,nb.dept,nb.origin,nb.status,nb.cellphone,nb.imgHead,nb.job,nb.reason,nb.create_time,nb.examine,q.level user_level');
            if (empty($user)) {
                return [false, ['找不到该用户']];
            } else {
                $user = array_values($user)[0];
            }
            $region_id           = Db::table('frame_base_staff')->alias('bt')
                ->where('bt.uid', $uid)
                ->join('c_region r', 'r.id = bt.region', 'left')
                ->join('c_region r2', 'r.parentId = r2.id', 'left')
                ->join('c_region r3', 'r2.parentId = r3.id', 'left')
                ->join('c_region r4', 'r3.parentId = r4.id', 'left')
                ->field('r4.id r4,r3.id r3,r2.id r2,r.id r1')
                ->find();
            $user['region_id']   = array_values($region_id);
            $region_name         = Db::table('frame_base_staff')->alias('bt')
                ->where('bt.uid', $uid)
                ->join('c_region r', 'r.id = bt.region', 'left')
                ->join('c_region r2', 'r.parentId = r2.id', 'left')
                ->join('c_region r3', 'r2.parentId = r3.id', 'left')
                ->join('c_region r4', 'r3.parentId = r4.id', 'left')
                ->field('r4.name r4,r3.name r3,r2.name r2,r.name r1')
                ->find();
            $user['region_name'] = array_values($region_name);
            $result              = Helper::transFormation($user);
            $roles               = Db::table("u_user_role")->alias('ur')
                ->where('ur.uid', $uid)
                ->join('u_role r', 'r.rid = ur.rid')
                ->field('r.rid,r.name')
                ->find();
            $result["roles"]     = "" . $roles['rid'] . "";
            $mold                = Db::table("u_user_mold")->alias("um")
                ->where('um.uid', $user['uid'])
                ->join('u_mold m', 'm.mid = um.mid')
                ->field('m.mid,m.describe')
                ->select();
            $result["molds"]     = $mold;
            return is_array($result) ? [true, $result] : Errors::DATA_NOT_FIND;
        } catch (\think\Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function ls($data, $user)
    {
        try {
            $query = DB::table("u_user")->alias('u')->whereLike("u.region", $data['region'] . '%');
            if (Helper::lsWhere($data, 'name')) $query = $query->whereLike("u.name", '%' . $data['name'] . '%');
            if (Helper::lsWhere($data, 'examine')) {
                if ($data['examine'] == 2) {
                    $query = $query->where('u.examine', 1)->where('u.origin', 2);
                } else {
                    $query = $query->where('u.examine', $data['examine']);
                }
            }
            if (Helper::lsWhere($data, 'cellphone')) $query = $query->whereLike("u.tel", $data['cellphone']);
            $query->join('u_user_role ur', 'ur.uid = u.uid');
            $query->field('u.uid,u.region,u.region_name,u.status,u.imgHead,u.dept,u.job,u.origin,u.name,u.examine,u.user_level,ur.rid rids,u.cellphone,u.create_time');
            $query->order('u.update_time', 'desc');
            $res  = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            $uids = $res['data'];
            foreach ($uids as $num => $uid) {
                $mold  = Db::table("u_user_mold")->alias("um")
                    ->where('um.uid', $uid['uid'])
                    ->join('u_mold m', 'm.mid = um.mid')
                    ->field('m.describe')
                    ->select();
                $molds = '';
                foreach ($mold as $key => $value) {
                    $molds = $molds . ',' . $value['describe'];
                }
                $molds                      = substr($molds, 1);
                $res['data'][$num]['molds'] = $molds;
            }
            if ($data['examine'] == '-1') {
                $result = Helper::transFormation($res);
                return [true, $result];
            }
            if ($data['examine'] == '2') {
                $result = Helper::transFormation($res);
                return [true, $result];
            }
            if ($data['examine'] == '0') {
                $result = Helper::transFormation($res);
                return [true, $result];
            }
            if ($data['examine'] == '1') {
                $result = Helper::transFormation($res);
                return [true, $result];
            }
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryVerify($account)
    {
        try {
            return Db::table("u_verify")
                ->where("account", $account)
                ->find();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    static function resetAuth($data)
    {
        try {
            $auth = Db::table('u_auth')->where('uid', $data['uid'])
                ->where('client', $data['client'])->column('uid');
            if (empty($auth)) {
                $data = Db::table('u_auth')->insert($data);
                return $data == 1 ? [true, $data] : Errors::DATA_NOT_FIND;
            }
            $data = Db::table('u_auth')->update($data);
            return $data !== 0 ? [true, $data] : Errors::DATA_NOT_FIND;
        } catch (Exception  $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function deleteAuth($uid, $client = null)
    {
        try {
            $query = Db::table('u_auth')->where('uid', $uid);
            if (!empty($client)) $query = $query->where('client', $client);
            $dbRes = $query->delete();
            return $dbRes == 1 ? [true, $dbRes] : Errors::AUT_LOGIN;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //从表 frame_client_device 查出最后的更新时间
    static function queryAuth($uid, $s_token, $client)
    {
        try {
//            $res = Db::table('u_auth')->where('uid', $uid)->where('client',$client)
//                ->where('s_token', $s_token)->column('s_update_time');
            $res = Db::table('frame_client_device')->where('uid', $uid)->where('client', $client)
                ->where('did', $s_token)->column('update_time');
//            return [true, $res];
            return Communal::successData($res);
        } catch (Exception  $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //查询用户权限id
    static function queryPids($uid)
    {
        try {
            $pids = Db::table("u_user_role")->alias('ur')
                ->where("ur.uid", $uid)
                ->join('u_role_premission rp', 'rp.rid = ur.rid')
                ->field('rp.pid')
                ->select();
            return is_array($pids) && !empty($pids) ? [true, $pids] : [false, null];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryDepts()
    {
        try {
            $db = Db::table("u_dept")->select();
            return is_array($db) ? [true, $db] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //查询区域下的用户
    static function queryRegionUser($data)
    {
        try {
            if (Helper::lsWhere($data, 'username')) {
                $region = Db::table('c_region')->where("parentId", $data['parentId'])->field('id, name')->select();
                if (empty($region)) return Errors::DATA_NOT_FIND;
                $res['region'] = $region;
                $username      = Db::table('frame_base_staff')->where('status', '0')
                    ->whereLike("name", '%' . $data['username'] . '%')
                    ->whereLike('region', $data['parentId'] . '%')
                    ->field('uid,name,tel')->select();
                if (empty($username)) return Errors::DATA_NOT_FIND;
                $res['user'] = $username;
                return [true, $res];
            }
            $region = Db::table('c_region')->where("parentId", $data['parentId'])->field('id, name')->select();
            if (empty($region)) return Errors::DATA_NOT_FIND;
            $res['region'] = $region;
            $users         = Db::table('frame_base_staff')->where('region', $data['parentId'] . '')->where('status', 0)->field('uid,name,tel')->select();
            $res['user']   = $users;
            return [true, $res];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryUser($uid)
    {
        try {
            $res = Db::table('frame_base_staff')->alias('u')
                ->where('u.uid', $uid)
                ->join('u_user_role ur', 'u.uid = ur.uid')
                ->join('c_region r', 'u.region = r.id')
                ->field('ur.rid,r.level,r.id')
                ->find();
            return is_array($res) ? [true, $res] : Errors::DATA_NOT_FIND;
        } catch (Exception  $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryRole($uid)
    {
        try {
            $res = Db::table('frame_base_staff')->alias('u')
                ->where('u.uid', $uid)
                ->join('u_user_role ur', 'u.uid = ur.uid')
                ->field('ur.rid')
                ->find();
            return is_array($res) ? [true, $res] : Errors::DATA_NOT_FIND;
        } catch (Exception  $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function queryRegionLevel($region)
    {
        try {
            $res = Db::table('c_region')
                ->where('id', $region)
                ->field('id value, name lable ,level')
                ->find();
            return is_array($res) ? [true, $res] : Errors::DATA_NOT_FIND;
        } catch (Exception  $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 数据导出
    static function exportls($data, $field, $img, $condition)
    {
        try {
            $where = '1 = 1';
            $order = 'create_time desc';
            $field .= ',tel';
            if (!empty($condition['region'])) $where .= " and region like '%" . $condition['region'] . "%'";
            if (!empty($condition['name'])) $where .= " and name like '%" . $condition['name'] . "%'";
            if (!empty($condition['cellphone'])) $where .= " and tel like '%" . $condition['cellphone'] . "%'";
            $dataRes = Db::table('frame_base_staff')->field($field)->where($where)->order($order)->select();
            // 获取图片
            if ($img) {
                foreach ($dataRes as $key => $val) {
                    $dataRes[$key]['img'] = Db::table('frame_base_staff')->where('cellphone', $val['cellphone'])->field('imgHead path')->select();
                }
            }
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 无人机上报人信息
    static function userInfo($tel)
    {
        try {
            $res = Db::table('frame_base_staff')
                //DB::table("u_user")
                ->field('cellphone,name,region,region_name')
//                ->where('status', 0)
                ->where('status', 1)
//                ->where('examine', 1)
                ->where('cellphone', $tel)
                ->find();

            return Communal::successData($res);

        } catch (Exception  $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 获取人员信息
    static function userLocal($uid)
    {
        try {
            $res = Db::table('frame_base_staff')
                ->field('name,uid,region_name,region,tel')
                ->where('status', 0)
                ->where('examine', 1)
                ->where('uid', $uid)
                ->find();
            return $res;
        } catch (Exception  $e) {
            return Errors::Error($e->getMessage());
        }
    }


    // 获取人员信息
    static function userFxjLocal($uid)
    {
        try {
            $res = Db::table('frame_base_staff')
                ->field('name,uid,region_name,region,cellphone,rid,job,imgHead')
                ->where('status', 0)
                ->where('examine', 1)
                ->where('uid', $uid)
                ->find();
            return $res;
        } catch (Exception  $e) {
            return Errors::Error($e->getMessage());
        }
    }

}