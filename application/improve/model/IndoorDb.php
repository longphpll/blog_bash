<?php
/**
 * Created by qiumu.
 * User: Administrator
 * Date: 2017/12/13 0013
 * Time: 11:35
 */

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use app\improve\controller\UploadHelper;
use think\Db;
use think\Exception;


use tool\Error;
use tool\Communal;

/*
 * 松材线虫病调查--室内监测
 */

class IndoorDb extends BaseDb
{
    /*已改 Lxl*/
    // 添加记录
    static function add($data, $images)
    {
        try {
            $data['create_time'] = date('Y-m-d H:i:s', time());
            $data['update_time'] = $data['create_time'];
            $data['status']      = 1;
            Db::startTrans();
            $dbRes = Db::table('improve_pineline_indoor')->insertGetId($data);
            if ($dbRes < 1) return Error::error('添加错误');
            if (!empty($images)) {
                if (count($images) > 6) return Error::error('图片不能超过六张');
                foreach ($images as $image) {
                    $info = $image->move(Error::FILE_ROOT_PATH . DS . 'pineline_pest_indoor');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $name   = 'file' . DS . 'pineline_pest_indoor' . DS . $info->getSaveName();
                        $record = [
                            'record_id'   => $dbRes,
                            'path'        => $name,
                            'create_time' => $data['create_time'],
                            'status'      => 1
                        ];
                        // 保存
                        $b = Db::table('improve_pineline_indoor_image')->insert($record);
                        if ($b < 1) return Error::error('图片添加失败');
                    }
                }
            }
            Db::commit();
            return Communal::success('添加成功,记录id为：' . $dbRes);
        } catch (\Exception $e) {
            Db::rollback();
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //列表
    static function ls($data)
    {
        try {
            $where = 'p.status = 1';
            $order = 'p.create_time desc';
            if (!empty($data['region'])) $where .= " and p.region like '%" . $data['region'] . "%'";
            if (!empty($data['surveyer'])) $where .= " and p.appraiser like '%" . $data['surveyer'] . "%'";
            if (!empty($data['begin_time'])) $where .= " and DATE(p.create_time) >='" . $data['begin_time'] . "'";
            if (!empty($data['end_time'])) $where .= " and DATE(p.create_time) <='" . $data['end_time'] . "'";
            if (!empty($data['tel'])) $where .= " and u.cellphone = " . $data['tel'];
            $dataRes = Db::table('improve_pineline_indoor')->alias('p')->join('frame_base_staff u', 'u.uid = p.adder', 'left')
                ->field('p.id, p.number, p.region, p.region_name,p.location_name, p.sampling_part, p.appraiser, p.reviewer,p.adder uid, u.name adder, u.cellphone, p.create_time')->where($where)->order($order)
                ->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            return is_array($dataRes) ? Communal::successData($dataRes) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    // 根据id查询
    static function query($data)
    {
        try {
            $dbRes = Db::table('improve_pineline_indoor')->alias('p')->join('frame_base_staff u', 'u.uid = p.adder', 'left')->field('p.id,p.number,p.region,p.region_name,
            p.positions,p.position_type,p.location_name,p.sampling_part,p.results,p.appraiser,p.reviewer,p.create_time,p.update_time,u.name adder,u.cellphone')
                ->where('p.id', $data['id'])->where('p.status', 1)->find();
            if (empty($dbRes)) return Error::error('未找到相应数据');
            $dbRes['images'] = Db::table('improve_pineline_indoor_image')->where('record_id', $data['id'])->where('status', 1)->field('record_id,create_time,status', true)->select();
            return is_array($dbRes) ? Communal::successData($dbRes) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /*已改*/
    // 查询添加人
    static function adder($id)
    {
        try {
            $dbRes = Db::table('improve_pineline_indoor')->field('adder')->where('id', $id)
                ->find();
            return !empty($dbRes) ? Communal::successData($dbRes) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /*已改*/
    //编辑
    static function edit($data, $images)
    {
        try {
            // if (!static::query($data['id'])[0]) return Errors::DATA_NOT_FIND;
            Db::startTrans();
            $data['update_time'] = date('Y-m-d H:i:s');
            if (Helper::lsWhere($data, 'del_images')) {
                //如果要删除图片的数量和表里根据id查询出来的数量不一致就返回 没有找到
                $del_images = $data['del_images'];
                $paths      = Db::table('improve_pineline_indoor_image')->field('path')->where('record_id', $data['id'])->whereIn('id', $del_images)->select();
                if (count($paths) !== count($del_images)) return Error::error('删除的图片没有找到');
                //如果受影响的行数和参数数组的数量不一致返回删除错误
                $delRes = Db::table('improve_pineline_indoor_image')->whereIn('id', $del_images)->update(['status' => 2]);
                if ($delRes !== count($del_images)) return Error::error('删除错误');
            };
            unset($data['images']);
            unset($data['del_images']);

            $dbRes = Db::table('improve_pineline_indoor')->field('create_time,status', true)->update($data);

            if (!empty($images)) {
                if (count($images) > 6) return Error::error('图片不能超过六张');
                foreach ($images as $image) {
                    $info = $image->move(Error::FILE_ROOT_PATH . DS . 'pineline_pest_indoor');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $name   = 'file' . DS . 'pineline_pest_indoor' . DS . $info->getSaveName();
                        $record = [
                            'record_id'   => $data['id'],
                            'path'        => $name,
                            'create_time' => $data['update_time'],
                            'status'      => 1
                        ];
                        // 保存
                        $b = Db::table('improve_pineline_indoor_image')->insert($record);
                        if ($b < 1) return Error::error('图片添加失败');
                    }
                }
            }
            Db::commit();
            return $dbRes == 1 ? Communal::success('编辑信息成功') : Error::error('修改错误');
        } catch (\Exception $e) {
            Db::rollback();
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //删除选中
    static function deleteChecked($ids)
    {
        try {
            $dataRes = Db::table('improve_pineline_indoor')->whereIn('id', $ids)->update(['status' => 2]);
            return empty($dataRes) ? Error::error('删除错误') : Communal::success('删除成功');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    // 数据导出
    static function exportls($data, $field, $img, $condition)
    {
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            $field .= ',id';
            if (!empty($condition['region'])) $where .= " and region like '%" . $condition['region'] . "%'";
            if (!empty($condition['name'])) $where .= " and appraiser like '%" . $condition['name'] . "%'";
            if (!empty($condition['begin_time'])) $where .= " and DATE(create_time) >='" . $condition['begin_time'] . "'";
            if (!empty($condition['end_time'])) $where .= " and DATE(create_time) <='" . $condition['end_time'] . "'";
            $dataRes = Db::table('improve_pineline_indoor')->field($field)->where($where)->order($order)->select();
            // 获取图片
            if ($img) {
                foreach ($dataRes as $key => $val) {
                    $dataRes[$key]['img'] = Db::table('improve_pineline_indoor_image')->where('record_id', $val['id'])->where('status', 1)->field('id,record_id,create_time,status', true)->select();
                }
            }
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

}