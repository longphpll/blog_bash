<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 14:34
 */
namespace app\improve\model;
use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;
use tool\Communal;
use tool\Error;

class RegularlyDb extends BaseDb
{
    /** 固定标准地信息模型-新增(已改)
     * 修改人：余思渡
     * 修改时间：2019-03-09
     * 修改内容：将DB_name  b_regularly  改为  improve_regularly
    */
    static function add($data)
    {
        unset($data['did']);
        try {
            $data['create_time'] =  date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $data['status'] = 1;
            unset($data['id']);
            $result = Db::table('improve_regularly')->insertGetId($data);
            return is_numeric($result) ? Communal::success('添加成功') : Error::error('添加失败');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /** 固定标准地信息模型-列表(已改)
     * 修改人：余思渡
     * 修改时间：2019-03-09
     * 修改内容：将DB_name  b_regularly  改为  improve_regularly
     *          将DB_name  u_user  改为  frame_base_staff
     *          将字段 ：tel 修改为 cellphone
    */
	static function ls($data,$sample = false)
    {
        try {
            $query = Db::table('improve_regularly')->alias('plp')->join('frame_base_staff u', 'u.uid = plp.adder', 'left')->where('plp.status',1);
            if (Helper::lsWhere($data, 'region')) $query->whereLike('plp.region', $data['region'] . '%');
            if (Helper::lsWhere($data, 'pests')) $query->where('plp.pests', $data['pests']);
            if (Helper::lsWhere($data, 'plant')) $query->where('plp.plant', $data['plant']);
            if (Helper::lsWhere($data, 'number')) $query->whereLike('plp.id', $data['number']);
            if (Helper::lsWhere($data, 'cellphone')) $query->where('u.cellphone', $data['cellphone']);
            if ($sample) {
                $query->field('plp.id,plp.number,plp.positions,plp.location_name,plp.region_name,plp.type,plp.pests_name,plp.plant_name,plp.regularly_area,plp.create_time');
            } else {
                $query->field('plp.id,plp.number,plp.region, plp.region_name, plp.type, plp.pests, plp.pests_name,
                plp.plant, plp.plant_name, plp.positions, plp.position_type,plp.location_name, plp.regularly_area,
                plp.stand_area, plp.stand_composition, plp.forest_age, plp.coverage,u.name adder,u.cellphone,plp.adder uid,plp.create_time');
            }
            $query->order('plp.create_time', 'desc');
            $dataRes = $query->paginate($data['per_page'], false, ['page' => $data['current_page']])->toArray();
            $result = Communal::removeEmpty($dataRes);
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /** 固定标准地信息模型-详情()
     * 修改人：余思渡
     * 修改时间：2019-03-11
     * 修改内容：将DB_name  b_regularly  改为  improve_regularly
     *          将DB_name  u_user  改为  frame_base_staff
     *          将字段 ：tel 修改为 cellphone
    */
    static function query($id)
    {
        try {
            $dbRes = Db::table('improve_regularly')->alias('plp')->where('plp.id', $id)
                ->where('plp.status',1)
                ->join('frame_base_staff u', 'u.uid = plp.adder', 'left')
                ->field('plp.id, plp.number, plp.region, plp.region_name, plp.type, plp.positions,plp.location_name, plp.pests,plp.plant, plp.pests_name, plp.plant_name, 
                plp.regularly_area, plp.stand_area, plp.position_type, plp.stand_composition, plp.forest_age, plp.coverage,u.name adder,u.cellphone,plp.create_time')
                 ->find();
            return is_array($dbRes) ? Communal::successData($dbRes) : Error::error('未找到数据');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /** 固定标准地信息模型-编辑(已改)
     * 修改人：余思渡
     * 修改时间：2019-03-11
     * 修改内容：将DB_name  b_regularly  改为  improve_regularly
    */
    static function edit($data)
    {
        unset($data['did']);
        try {
            if (!static::query($data['id'])[0]) return Error::error('未找到数据');
            $data['update_time'] =  date('Y-m-d H:i:s');
            $dbRes = Db::table('improve_regularly')->field('region,region_name,type,positions,position_type,number,pests,plant,regularly_area,
            stand_area,stand_composition,forest_age,coverage,update_time,location_name,adder')->update($data);
            return $dbRes == 1 ? Communal::success('修改成功') : Error::error('修改失败');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

	//标准地历史对比图
    static function history($data)
    {
        try {
            $dbRes = Db::table('b_regularly')->alias('r')
                ->join('b_sample_plot_survey v','v.sample_plot_number = r.id')
                ->field('SUM(v.happen_area) AS area, YEAR (v.create_time) AS year')
                ->where('r.status',1)
                ->whereLike('r.region',$data['region']. '%')
                ->where("YEAR (v.create_time) >='".$data['start_time']."'")
                ->where("YEAR (v.create_time) <='".$data['end_time']."'")
                ->where('r.pests',$data['pest'])
                ->group('year')
                ->select();
            if (empty($dbRes)) {
                $res['title'] = '';
            }else{
                $region_name = BaseDb::areaName($data['region']);
                $pest_name  = BaseDb::pest($data['pest']);
                $res['title'] = $region_name.''.$data['start_time'].'年与'.$data['end_time'].'年'.$pest_name.'发生面积历史对比图';
            }
            $res['data'] = $dbRes;
            return [true, $res];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    /** 固定标准地信息模型-已出现生物种类查询列表(已改)
     * 修改人：余思渡
     * 修改时间：2019-03-11
     * 修改内容：将DB_name  b_regularly  改为  improve_regularly
    */
    static function pestList($data)
    {
        try{
            $where = 'status = 1';
            $field = 'pests_name label,pests value';
            $order = 'pests desc';
            if (!empty($data['name'])) $where.=" and pests_name like '%".$data['name']."%'";
            $dataRes = Db::table('improve_regularly')
                ->field($field)
                ->where($where)
                ->whereLike('region',$data['region']. '%')//这一条where条件语句需要修改
                ->group('pests_name')
                ->order($order)->select();
            //return [true, $dataRes];
            return empty($dataRes) ? Error::error('未找到数据') : Communal::successData($dataRes);
        }catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /** 固定标准地信息模型-已出现寄生查询列表()
     * 修改人：余思渡
     * 修改时间：2019-03-11
     * 修改内容：将DB_name  b_regularly  改为  improve_regularly
    */
    static function plantList($data)
    {
        try{
            $where = 'status = 1';
            $field = 'plant value,plant_name label';
            $order = 'value desc';
            if (!empty($data['name'])) $where.=" and plant_name like '%".$data['name']."%'";
            $dataRes = Db::table('improve_regularly')
                ->where($where)
                ->where('pests',$data['id'])
                ->whereLike('region',$data['region']. '%')//此处为追加条件 || 这一条where条件语句需要修改
                ->field($field)
                ->group('plant_name')
                ->order($order)->select();
            return empty($dataRes) ? Error::error('未找到数据') : Communal::successData($dataRes);
        }catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /** 固定标准地信息模型-删除()
     * 修改人：余思渡
     * 修改时间：2019-03-11
     * 修改内容：将DB_name  b_regularly  改为  improve_regularly
    */
    static function delete($ids){
        try {
            $dataRes = Db::table('improve_regularly')->whereIn('id', $ids)->update(['status'=> 2]);
            return empty($dataRes) ? Error::error('修改失败') : Communal::success('修改成功');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    // 数据导出
    /** 固定标准地信息模型-数据导出()
     * 修改人：余思渡
     * 修改时间：2019-03-11
     * 修改内容：将DB_name  b_regularly  改为  improve_regularly
    */
    static function exportls($data,$field,$condition){
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            if (!empty($condition['region'])) $where.=" and region like '".$condition['region']."%'";
            if (!empty($condition['pests'])) $where.=" and pests_name like '%".$condition['name']."%'";
            if (!empty($condition['plant'])) $where.=" and plant_name like '%".$condition['person']."%'";
            if (!empty($condition['number'])) $where.=" and number like'%".$condition['begin_time']."%'";
            $dataRes = Db::table('improve_regularly')->field($field)->where($where)->order($order)->select();
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
}