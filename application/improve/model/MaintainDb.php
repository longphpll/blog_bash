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
 * 诱捕器维护数据
 */

class MaintainDb extends BaseDb
{
    /*已改 Lxl*/
    // 添加记录
    static function add($data, $images)
    {
        try {
            //诱捕器信息表 improve_trap
            $number = Db::table('improve_trap')->where('number', $data['trap_number'])->field('number')->find();
            if (empty($number)) return Error::error('未找到相应数据');

            $data['create_time'] = date('Y-m-d H:i:s', time());
            $data['update_time'] = $data['create_time'];
            $data['status']      = 1;
            //诱捕器维护数据表 improve_trap_maintain
            $rs = Db::table('improve_trap_maintain')->where('trap_number', $data['trap_number'])->field(' COUNT(trap_number) as code')->find();

            if (empty($rs['code'])) {
                $data['maintain_number'] = $number['number'] . '_' . '1';
                $data['maintain_batch']  = 1;
                //录入第一次维护的诱捕器gps经纬度
                $res = Db::table('improve_trap')->where('number', $data['trap_number'])->update(['positions' => $data['positions'], 'device_status' => $data['device_status'], 'server_status' => 2]);
            } else {
                $data['maintain_number'] = $number['number'] . '_' . str_pad($rs['code'] + 1, 0);
                $data['maintain_batch']  = $rs['code'] + 1;
                $trap_res                = Db::table('improve_trap')->where('number', $data['trap_number'])->update(['device_status' => $data['device_status']]);
            }
            unset($data['images']);
            $pres               = Db::table('improve_trap')->where('number', $data['trap_number'])->field('relation_name')->find();
            $data['adder_name'] = $pres['relation_name'];

            Db::startTrans();
            $dbRes = Db::table('improve_trap_maintain')->field('trap_number,position_type,maintain_number,region,region_name,location_name,positions,maintain_batch,maintenance_date,
            female_number,male_number,total,drug_model,remarks,create_time,update_time,device_status,status,adder,adder_name')->insertGetId($data);
            if ($dbRes < 1) return Error::error('添加错误');

            if (!empty($images)) {
                if (count($images) > 6) return Error::error('图片不能超过六张');
                foreach ($images as $image) {
                    $info = $image->move(Error::FILE_ROOT_PATH . DS . 'maintain_file');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $name = 'file' . DS . 'maintain_file' . DS . $info->getSaveName();
                        // 保存
                        //诱捕器维护数据文件表 improve_maintain_file
                        $b = Db::table('improve_maintain_file')->insert(['maintain_id' => $dbRes, 'path' => $name, 'status' => 1, 'create_time' => $data['create_time']]);
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
    // web列表
    static function ls($data, $rid, $adder)
    {
        try {
            $where = 'status = 1';
            $order = 'update_time desc';
            if ($rid == 2) $where .= " and adder ='" . $adder . "'";
            if (!empty($data['region'])) $where .= " and region like '" . $data['region'] . "%'";
            if (!empty($data['trap_number'])) $where .= " and trap_number ='" . $data['trap_number'] . "'";
            if (!empty($data['date'])) $where .= " and maintenance_date ='" . $data['date'] . "'";
            if (!empty($data['name'])) $where .= " and adder_name = '" . $data['name'] . "'";
            $dataRes = Db::table('improve_trap_maintain')->field('id,trap_number,maintain_number,region_name,maintain_batch,maintenance_date,
                female_number,male_number,total,device_status,adder_name adder,create_time')->where($where)
                ->order($order)->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();

            //诱捕器维护数据表
            $female_total = Db::table('improve_trap_maintain')->where($where)->field(' SUM(female_number) as total')->find();
            $male_total   = Db::table('improve_trap_maintain')->where($where)->field(' SUM(male_number) as total')->find();

            $insect_total            = $female_total['total'] + $male_total['total'];
            $dataRes['female_total'] = intval($female_total['total']);
            $dataRes['male_total']   = intval($male_total['total']);
            $dataRes['insect_total'] = $insect_total;

            return is_array($dataRes) ? Communal::successData($dataRes) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    // app列表
    static function maintainls($data, $rid, $adder)
    {
        try {
            $where = 'status = 1';
            $order = 'update_time desc';
            if ($rid == 2) $where .= " and adder ='" . $adder . "'";
            if (!empty($data['region'])) $where .= " and region like '" . $data['region'] . "%'";
            if (!empty($data['trap_number'])) $where .= " and trap_number ='" . $data['trap_number'] . "'";
            if (!empty($data['date'])) $where .= " and maintenance_date ='" . $data['date'] . "'";
            if (!empty($data['name'])) $where .= " and adder_name = '" . $data['name'] . "'";

            //诱捕器维护数据表
            $dataRes = Db::table('improve_trap_maintain')->field('id,trap_number,maintain_number,region_name,maintain_batch,maintenance_date,
                female_number,male_number,total,device_status,adder_name adder,create_time')->where($where)
                ->order($order)->select();
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
            $where = 'm.status = 1';
            $field = 'm.id,m.trap_number,m.maintain_number,m.region,m.region_name,m.location_name,m.positions,m.position_type,
            m.maintain_batch,m.maintenance_date,
            m.female_number,m.male_number,m.total,m.drug_model,m.remarks,m.device_status,m.adder_name adder,u.cellphone adder_tel,m.create_time';
            $dbRes = Db::table('improve_trap_maintain')->alias('m')->join('frame_base_staff u', 'u.uid = m.adder')->where('m.id', $data['id'])
                ->field($field)
                ->find();
            if (empty($dbRes)) return Error::error('未找到相应数据');
            $dbRes['images'] = Db::table('improve_maintain_file')->where('maintain_id', $data['id'])->where('status', 1)->field('maintain_id,create_time,status', true)->select();
            $result          = Communal::removeEmpty($dbRes);
            return is_array($result) ? Communal::successData($result) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /*已改*/
    // 已有录入人查询
    static function userls($data)
    {
        try {
            $where = 'status = 1';
            if (!empty($data['name'])) $where .= " and adder_name like '%" . $data['name'] . "%'";
            $result = Db::table('improve_trap_maintain')->where($where)->field('distinct adder_name')->select();
            return is_array($result) ? Communal::successData($result) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /*已改*/
    // 已有使用的诱捕器编号查询-管理员权限
    static function trapls($data)
    {
        try {
            $where = 'status=1';
            if (!empty($data['number'])) $where .= " and trap_number like '%" . $data['number'] . "%'";
            $result = Db::table('improve_trap_maintain')->where($where)->field('distinct trap_number')->select();
            return !empty($result) ? Communal::successData($result) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /*已改*/
    // 诱捕器设备信息查询
    static function tarpQuery($number)
    {
        try {
            $dbRes = Db::table('improve_trap')->where('number', $number)
                ->where('status', 1)
                ->field('number trap_number,region_name,unit,purpose,company,relation_name,relation_tel,amount,drug_model,drug_batch,device_status,server_status,create_time')
                ->find();

            return is_array($dbRes) ? Communal::successData($dbRes) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /*已改*/
    //历史维护记录列表
    static function historyls($data)
    {
        try {
            $where = 'maintain_id =' . $data['id'];
            $order = 'update_time desc';
            if (!empty($data['date'])) $where .= " and DATE(update_time) = '" . $data['date'] . "'";
            //诱捕器维护历史记录表
            $result = Db::table('improve_maintain_history')
                ->where($where)
                ->field('id,trap_number,maintain_number,region,region_name,location_name,positions,
                        position_type,maintain_batch,maintenance_date,female_number,male_number,total,drug_model,
                        remarks,device_status,update_time create_time,adder_name adder')
                ->order($order)
                ->select();
            foreach ($result as $key => $id) {
                $image                  = Db::table("improve_history_file")->where('history_id', $id['id'])
                    ->field('id,path')
                    ->select();
                $result[$key]['images'] = $image;
            }

            return is_array($result) ? Communal::successData($result) : Error::error('未找到相应数据');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /*已改*/
    // 编辑-普通用户权限 编辑-移动端
    static function edit($data, $images, $adder)
    {
        try {
            unset($data['images']);
            $res = Db::table('improve_trap_maintain')->where('id', $data['id'])->where('status', 1)->field('id,trap_number,maintain_number,maintain_batch,adder,adder_name')->find();
            if (empty($res)) return Errors::DATA_NOT_FIND;
            if ($res['adder'] != $adder) return Errors::AUTH_PREMISSION_EMPTY;

            Db::startTrans();
            $data['update_time'] = date('Y-m-d H:i:s');
            if (Helper::lsWhere($data, 'del_images')) {
                $del_images = $data['del_images'];
            } else {
                $del_images = '';
            }
            unset($data['del_images']);

            $dbRes = Db::table('improve_trap_maintain')->field('region,positions,position_type,region_name,location_name,region_name,maintenance_date,
            female_number,male_number,total,drug_model,remarks,device_status,adder,adder_name,update_time')->update($data);

            // 维护数据
            $record = [
                'maintain_id'      => $data['id'],
                'trap_number'      => $res['trap_number'],
                'maintain_number'  => $res['maintain_number'],
                'maintain_batch'   => $res['maintain_batch'],
                'region'           => $data['region'],
                'region_name'      => $data['region_name'],
                'location_name'    => $data['location_name'],
                'positions'        => $data['positions'],
                'position_type'    => $data['position_type'],
                'maintenance_date' => $data['maintenance_date'],
                'female_number'    => $data['female_number'],
                'male_number'      => $data['male_number'],
                'total'            => $data['total'],
                'drug_model'       => $data['drug_model'],
                'remarks'          => $data['remarks'],
                'device_status'    => $data['device_status'],
                'adder'            => $adder,
                'adder_name'       => $res['adder_name'],
                'update_time'      => $data['update_time']
            ];
            $result = Db::table('improve_maintain_history')->insertGetId($record);

            // 图片上传
            if (!empty($images)) {
                if (count($images) > 6) return Error::error('图片不能超过六张');
                foreach ($images as $image) {
                    $info = $image->move(Error::FILE_ROOT_PATH . DS . 'maintain_file');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $name = 'file' . DS . 'maintain_file' . DS . $info->getSaveName();
                        // 保存
                        $b = Db::table('improve_maintain_file')->insert(['maintain_id' => $data['id'], 'path' => $name, 'status' => 1, 'create_time' => $data['update_time']]);
                        if ($b < 1) return Error::error('图片添加失败');
                    }
                }
                if (!empty($del_images)) {
                    $del_res = Db::table('improve_maintain_file')->whereIn('id', $del_images)->where('status', 1)->delete();
                    if ($del_res < 1) return Error::error('修改错误');
                }
                // 插入历史记录
                $images_paths = Db::table('improve_maintain_file')->where('maintain_id', $data['id'])->where('status', 1)->field('path')->select();
                if (!empty($images_paths)) {
                    foreach ($images_paths as $key => $path) {
                        $history = Db::table('improve_history_file')->insertGetId(['history_id' => $result, 'path' => $path['path'], 'create_time' => $data['update_time']]);
                    }
                }
            } else {

                if (!empty($del_images)) {
                    //历史维护数据记录文件表
                    $del_res = Db::table('improve_maintain_file')->whereIn('id', $del_images)->where('status', 1)->delete();
                    if ($del_res < 1) return Error::error('历史维护数据记录文件表 删除错误');
                    $images_paths = Db::table('improve_maintain_file')->where('maintain_id', $data['id'])->where('status', 1)->field('path')->select();
                    if (!empty($images_paths)) {
                        foreach ($images_paths as $key => $path) {
                            $history = Db::table('improve_history_file')->insertGetId(['history_id' => $result, 'path' => $path['path'], 'create_time' => $data['update_time']]);
                        }
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
    // 数据导出
    static function exportls($data, $field, $condition)
    {
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            if (!empty($condition['region_name'])) $where .= " and region_name like '%" . $condition['region_name'] . "%'";
            if (!empty($condition['trap_number'])) $where .= " and trap_number ='" . $condition['trap_number'] . "'";
            if (!empty($condition['date'])) $where .= " and maintenance_date ='" . $condition['date'] . "'";
            $dataRes = Db::table('improve_trap_maintain')->field($field)->where($where)->order($order)->select();

            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }


}