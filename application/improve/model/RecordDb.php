<?php
/**
 * Created by PhpStorm.
 * User: qiumu
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\UploadHelper;
use app\improve\controller\Helper;
use think\Db;
use tool\Error;
use tool\Communal;

class RecordDb  extends BaseDb
{
    /** 物种数据库-采集记录-添加模型
     * 修改人：余思渡
     * 修改时间：2019-0-09
     * 修改内容：将Db_name b_cj_record 修改为 improve_cj_record
     *          将Db_name b_cj_record_image 修改为 improve_cj_record_image
    */
    static function add($data,$path){
        try {
            $data['create_time'] =  date('Y-m-d H:i:s');
            $data['status'] =  1;
            unset($data['did']);
            $result = Db::table('improve_cj_record')->field('name,adder,create_time,status')->strict(false)->insertGetId($data);
            $a = Db::table('improve_cj_record_image')->insert(['record_id' => $result , 'create_time' => $data['create_time'],'path' => $path]);
            if ($a < 0) return Error::error('图片添加失败');
            return is_numeric($result) ? Communal::success('添加成功') : Error::error('添加失败');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /** 物种数据库-采集记录-列表模型(已改)
     * 修改人：余思渡
     * 修改时间：2019-0-09
     * 修改内容：将Db_name b_cj_record 修改为 improve_cj_record
     *          将Db_name u_user 修改为 frame_base_staff
    */
    static function ls($data){
        try {
            $where = '1 = 1';
            $order = 'r.create_time desc';
            if (!empty($data['type'])) $where.=" and r.status = ".$data['type'];
            if (!empty($data['name'])) $where.=" and r.name like '%".$data['name']."%'";
            if (!empty($data['person'])) $where.=" and u.name like '%".$data['person']."%'";
            if (!empty($data['start_time'])) $where.=" and DATE(r.create_time) >='".$data['start_time']."'";
            if (!empty($data['end_time'])) $where.=" and DATE(r.create_time) <='".$data['end_time']."'";
            if (!empty($data['tel'])) $where.=" and u.tel =".$data['tel'];
            if (!empty($data['type'])){
                switch ($data['type']) {
                    case '1':
                        $field = 'r.id,r.name,u.name adder,u.cellphone,r.status,r.create_time survey_time';
                        break;
                    case '2':
                        $field = 'r.id,r.name,u.name adder,u.cellphone,r.status,r.create_time survey_time';
                        break;
                    case '3':
                        $field = 'r.id,r.name,u.name adder,r.status,r.create_time survey_time,u1.name auditor,u.cellphone,r.examine_time';
                        break;
                }
            }
            $dataRes = Db::table('improve_cj_record')->alias('r')->join('frame_base_staff u','u.uid = r.adder');
            if ($data['type'] != 1) {
                $dataRes->join('frame_base_staff u1','u1.uid = r.auditor');
            }
            $result = $dataRes->field($field)->where($where)->order($order)
                ->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /** 物种数据库-采集记录-详情模型(已改)
     * 修改人：余思渡
     * 修改时间：2019-0-09
     * 修改内容：将Db_name b_cj_record 修改为 improve_cj_record
     *          将Db_name u_user 修改为 frame_base_staff
     *          将Db_name b_cj_record_image 修改为 improve_cj_record_image
    */
    static function query($id){
        try {
            $res = Db::table('improve_cj_record')->field('status')->where('id',$id)->find();
            if ($res['status'] === 1){
                $field = 'r.id,r.name,u.name surveyer,u.cellphone,r.status,r.create_time survey_time';
            }else{
                $field = 'r.id,r.name,u.name surveyer,u.cellphone,r.status,r.create_time survey_time,u1.name auditor,r.examine_time';
            }
            $dbRes = Db::table('improve_cj_record')->alias('r')->join('frame_base_staff u','u.uid = r.adder');
            if ($res['status'] != 1){
                $dbRes->join('frame_base_staff u1','u1.uid = r.auditor');
            }
            $result = $dbRes->field($field)->where('r.id', $id)->find();
            $result['images'] = Db::table('improve_cj_record_image')->field('id,path')->where('record_id', $id)->select();
            return  Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /** 物种数据库-采集记录-审核模型(已改)
     * 修改人：余思渡
     * 修改时间：2019-0-09
     * 修改内容：将Db_name b_cj_record 修改为 improve_cj_record
     *          将Db_name u_user 修改为 frame_base_staff
     *          将Db_name b_cj_record_image 修改为 improve_cj_record_image
    */
    static function examine($data){
        unset($data['did']);
        try {
            $res = Db::table('improve_cj_record')->field('status')->where('id',$data['id'])->find();
            if ($res['status'] != 1) return Error::error('该记录已审核');
            $data['examine_time'] =  date('Y-m-d H:i:s');
            $result = Db::table('improve_cj_record')->field('name,auditor,status,auditor_name,examine_time')->update($data);
            return $result == 1 ? Communal::success('审核成功') : Error::error('审核失败');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    // 数据导出
    static function exportls($data,$field,$img,$condition){
        try {
            $where = ' 1= 1 ';
            $order = 'create_time desc';
            $field.=',id';
            if (!empty($condition['name'])) $where.=" and name like '%".$condition['name']."%'";
            if (!empty($condition['person'])) $where.=" and report like '%".$condition['person']."%'";
            if (!empty($condition['start_time'])) $where.=" and DATE(create_time) >='".$condition['start_time']."'";
            if (!empty($condition['end_time'])) $where.=" and DATE(create_time) <='".$condition['end_time']."'";
            $dataRes = Db::table('b_cj_record')->field($field)->where($where)->order($order)->select();
            // 获取图片
            if ($img){
                foreach ($dataRes as $key => $val) {
                    $dataRes[$key]['img'] = Db::table('b_cj_record_image')->where('record_id', $val['id'])->field('id,path')->select();
                }
            }
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
}