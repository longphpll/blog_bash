<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\model;

use app\improve\controller\UploadHelper;
use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;
use think\Exception;

use tool\Error;
use tool\Communal;

/*智能识别*/

class IdentifyDb extends BaseDb
{
    /*已改 Lxl*/
    //历史记录添加
    static function add($data, $images)
    {
        try {
            //设置识别时间
            $data['identity_time'] = date('Y-m-d');
            $data['create_time']   = date('Y-m-d H:i:s');
            $data['update_time']   = $data['create_time'];
            $data['status']        = 2;
            $data['state']         = 1;
            Db::startTrans();
            $dbRes = Db::table('improve_identify_history')->insertGetId($data);
            //图片上传
            if (!empty($images)) {
                if (count($images) > 6) return Error::error('图片不能超过六张');
                foreach ($images as $image) {
                    $path = Helper::upload($image, DS . 'Identify' . DS . 'image_' . $dbRes);
                    if ($path[0] !== true) return $path;
                    $a = Db::table('improve_identify_history')->update(['id' => $dbRes, 'image' => $path[1][1]]);
                    if ($a < 0) return Error::error('图片添加失败');
                }
            }
            Db::commit();
            //判断id是否为数字，如果是返回id，如果不是返回ERROR
            return is_numeric($dbRes) ? Communal::success('添加成功,记录id为：' . $dbRes) : Error::error('添加错误');
        } catch (\Exception $e) {
            Db::rollback();
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //智能识别数据首次录入
    static function baseAdd($data, $images)
    {
        try {
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            Db::startTrans();
            //插入记录并获得新增id 智能识别数据表
            $dbRes = Db::table('improve_identify')->insertGetId($data);
            if (empty($images)) return Error::error('请上传图片');
            //图片上传
            if (!empty($images)) {
                if (count($images) > 6) return Error::error('图片不能超过六张');
                foreach ($images as $image) {
                    $path = Helper::upload($image, DS . 'base' . DS . 'image_' . $dbRes);
                    if ($path['status'] !== true) return $path;
                    $a = Db::table('improve_identify_images')->insert(['sid' => $dbRes, 'image' => $path['getSaveName']]);
                    if ($a < 0) return Error::error('图片添加失败');
                }
            }
            Db::commit();
            //判断id是否为数字，如果是返回id，如果不是返回ERROR
            return is_numeric($dbRes) ? Communal::success('添加成功,记录id为：' . $dbRes) : Error::error('添加错误');
        } catch (\Exception $e) {
            Db::rollback();
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //智能识别已有数据录入
    static function baseEdit($data, $images)
    {
        try {
            if (empty($images)) return Error::error('请上传图片');
            Db::startTrans();
            //图片上传
            if (!empty($images)) {
                if (count($images) > 6) return Error::error('图片不能超过六张');
                foreach ($images as $image) {
                    $path = Helper::upload($image, DS . 'base' . DS . 'image_' . $data['sid']);
                    //if ($path[0] !== true) return $path;
                    if ($path['status'] !== true) return $path;
                    $a = Db::table('improve_identify_images')->insert(['sid' => $data['sid'], 'image' => $path['getSaveName']]);
                    if ($a < 0) return Error::error('图片添加失败');
                }
            }
            Db::commit();
            //判断id是否为数字，如果是返回id，如果不是返回ERROR
            return is_numeric($a) ? Communal::success('添加成功') : Error::error('添加错误');
        } catch (\Exception $e) {
            Db::rollback();
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    static function ls($data, $sample = false)
    {
        try {
            //从 历史记录表 improve_identify_history 查询
            $query = Db::table('improve_identify_history')->alias('bh')->where('bh.uid', $data['uid'])->where('bh.state', '1')
                ->join('improve_identify bf', 'bf.id = bh.sid')
                ->field('bh.id, bf.name, bh.location, bh.status, bh.identity_time, bh.image');
            if ($sample) {
                $query->where('bh.status', '1')->field('bh.id,bh.image,bf.name,bf.alias,bh.status,bh.identity_time');
            } else {
                if (Helper::lsWhere($data, 'start_time')) $query->where('bh.identity_time', $data['start_time']);
            }
            $query->order('bh.identity_time', 'desc');
            $dataRes = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();

            return empty($dataRes) ? Error::error('未找到相应数据') : Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //昆虫基础数据列表
    static function insectls($data)
    {
        try {
            //improve_identify 智能识别数据表
            $query = Db::table('improve_identify')->alias('bf')
                ->join('frame_base_staff ur', 'ur.uid = bf.adder')
                ->field('bf.id,bf.name,bf.subjects,ur.name adder,bf.create_time');
            if (Helper::lsWhere($data, 'name')) $query = $query->whereLike('bf.name', '%' . $data['name'] . '%');
            if (Helper::lsWhere($data, 'subjects')) $query = $query->whereLike('bf.subjects', '%' . $data['subjects'] . '%');
            if (Helper::lsWhere($data, 'adder')) $query = $query->whereLike('ur.name', '%' . $data['adder'] . '%');
            $query->order('bf.create_time', 'desc');
            $dataRes = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            $ids     = $dataRes['data'];
            foreach ($ids as $num => $id) {
                $img                             = Db::table("improve_identify_images")->alias("bi")
                    ->where('bi.sid', $id['id'])
                    ->column('bi.image');
                $images                          = '';
                $dataRes['data'][$num]['images'] = $img;
            }

            $result = Communal::removeEmpty($dataRes);

            return empty($result) ? Error::error('未找到相应数据') : Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //昆虫基础数据详情
    static function insectQuery($id)
    {
        try {
            //智能识别数据表
            $dbRes          = Db::table('improve_identify')->alias('bf')
                ->where('bf.id', $id)->join('frame_base_staff ur', 'ur.uid = bf.adder')
                ->field('bf.id,bf.name, bf.alias, bf.latin, bf.subjects, bf.major_hazard, bf.prevention_methods, bf.life_habits, bf.form_feature, bf.distribution_area, bf.create_time, ur.name adder')->find();
            $dbRes['image'] = Db::table('improve_identify_images')->where('sid', $id)->field('id,image')->select();
            return empty($dbRes) ? Error::error('未找到相应数据') : Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //历史记录地图概况
    static function historyMap($data)
    {
        try {
            $data['nowTime'] = date('Y-m-d');
            $query           = Db::table('improve_identify_history')->alias('bh')
                ->join('improve_identify bf', 'bf.id = bh.sid')
                ->field('bh.id, bf.name, bh.positions,bh.location');
            if (Helper::lsWhere($data, 'start_time')) {
                $query->where('bh.identity_time', $data['start_time']);
            } else {
                $query->where('bh.identity_time', $data['nowTime']);
            }
            if (Helper::lsWhere($data, 'per_page')) $query->limit($data['per_page']);
            $dataRes = $query->select();
            return !empty($dataRes) ? Communal::successData($dataRes) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    //匹配是否存在该数据
    static function Identify($name)
    {
        try {
            $dbRes = Db::table('improve_identify')
                ->where('name', $name)
                ->field('id')
                ->find();
            return empty($dbRes) ? Error::error('未找到相应数据') : Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //详情
    static function query($id)
    {
        try {
            //历史记录表
            $dbRes = Db::table('improve_identify_history')->alias('bh')->where('bh.id', $id)
                ->join('improve_identify bf', 'bf.id = bh.sid')
                ->field('bh.id, bf.name, bf.alias,bh.status, bh.similarity, bf.subjects, bf.major_hazard, bf.prevention_methods, bf.life_habits, bf.form_feature, bf.distribution_area, bh.image')
                ->find();
            return empty($dbRes) ? Error::error('未找到相应数据') : Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //是否收藏
    static function collect($data)
    {
        try {
            $data['collect_time'] = date('Y-m-d H:i:s');
            //历史记录表
            $dataRes = Db::table('improve_identify_history')->where('id', $data['id'])->update(['status' => $data['status'], 'collect_time' => $data['collect_time']]);
            return $dataRes == 1 ? Communal::success('编辑信息成功') : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    // 删除选中
    static function deleteChecked($ids, $suid)
    {
        try {
            $ret = [];
            foreach ($ids as $id) {
                //通过记录的id,去查询uid
                $adder = self::findAdderr($id, "improve_identify_history");
                if (!$adder[0]) {
                    $adder[1][3] = $id;
                    array_push($ret, $adder[1]);
                    continue;
                }
                $res = Db::table('improve_identify_history')->where('id', $id)->update(['state' => '2']);
                array_push($ret, $res === 1 ? ['id' => $id, 'res' => 'delete success'] : ['删除错误', $id]);
            }
            return Communal::successData($ret);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*上面的方法调用此方法*/
    // 查询数据添加者
    static function findAdderr($id, $db_name)
    {
        try {
            $dbRes = Db::table($db_name)->where('id', $id)->field('uid')->find();
            return !empty($dbRes) ? Communal::successData($dbRes) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
}