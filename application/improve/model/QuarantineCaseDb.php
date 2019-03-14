<?php
namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use think\Db;
use tool\Communal;
use tool\Error;
use tool\BaseDb as ToolBaseDb;

class QuarantineCaseDb extends BaseDb
{
    /**
     * 检疫站模型
     * 修改人：余思渡
     * 修改时间：2019.03.05
     * 修改内容：将db_name b_quarantine_base 改为 improve_quarantine_base
     */
    static function add($data){
        try {
            //设置创建时间
            $data['create_date'] =  date('Y-m-d H:i:s');
            //设置初始修改时间为创建时间
            $data['update_date'] = $data['create_date'];
            $data['status'] = 1;
            //销毁变量id，防止数据库出现重复id
            unset($data['id']);
            unset($data['did']);
            //插入记录并获得新增id
            $result = Db::table('improve_quarantine_base')->insertGetId($data);
            //判断id是否为数字，如果是返回id，如果不是返回ERROR
            return is_numeric($result) ? Communal::success('添加成功') : Error::error('添加失败');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /**
     * 检疫站模型
     * 修改人：余思渡
     * 修改时间：2019.03.06
     * 修改内容：将db_name b_quarantine_base 改为 improve_quarantine_base
     */
    static function insert_add($info){
        try {
            $result = Db::table('improve_quarantine_base')->insertGetId($info);
            //判断id是否为数字，如果是返回id，如果不是返回ERROR
            return is_numeric($result) ? [true ,$result] : Errors::ADD_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    /**
     * 检疫站模型
     * 修改人：余思渡
     * 修改时间：2019.03.06
     * 修改内容：将db_name b_quarantine_base 改为 improve_quarantine_base
     */
    static function ls($data,$rid,$adder){
        try {
            $where = 'status = 1';
            $field = 'id,region_name,year,epidemic_rate,quarantine_rate,quarantine_treatment_rate,fee,adder_name,create_date';
            $order = 'create_date desc';
            if($rid == 2) $where.=" and adder='".$adder."'";
            if (!empty($data['region'])) $where.=" and region like '%".$data['region']."%'";//条件逻辑需要重写
            if (!empty($data['year'])) $where.=" and year = ".$data['year'];
            $dataRes = Db::table('improve_quarantine_base')->field($field)->where($where)->order($order)
                ->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            return count($dataRes['data']) !==0 ? Communal::successData($dataRes) : Error::error('未找到数据') ;
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    // 数据导出
    /**
     * 检疫站模型
     * 修改人：余思渡
     * 修改时间：2019.03.06
     * 修改内容：将db_name b_quarantine_base 改为 improve_quarantine_base
     */
    static function exportls($data){
        try {
            $where = 'status = 1';
            $field = 'id,create_date,update_date,adder,adder_name,yearValid,status,region';
            $dataRes = Db::table('improve_quarantine_base')->field($field,true)->where($where)->where('id',$data['id'])->find();
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
    /**
     * 检疫员统计模型
     * 修改人：余思渡
     * 修改时间：2019.03.05
     * 修改内容：将db_name b_quarantine_base 改为 improve_quarantine_base
    */
    static function statisticsList($data,$region_name){
        $where = 'status = 1';
        $a = 'sum(place_tree_should) place_tree_should,sum(place_tree_real) place_tree_real,sum(seed_breed_should) seed_breed_should,sum(seed_breed_real) seed_breed_real,
         sum(flowers_base_real) flowers_base_real,sum(flowers_base_should) flowers_base_should,
         sum(economic_forest_should) economic_forest_should,sum(economic_forest_real) economic_forest_real,
         sum(chinese_medicine_base_should) chinese_medicine_base_should,sum(chinese_medicine_base_real) chinese_medicine_base_real,
         sum(timber_forest_should) timber_forest_should,sum(timber_forest_real) timber_forest_real,
         sum(wood_should) wood_should,sum(wood_real) wood_real,sum(bamboo_should) bamboo_should,
         sum(bamboo_real) bamboo_real,sum(fruit_should) fruit_should,sum(fruit_real) fruit_real,sum(flowers_should) flowers_should,
         sum(flowers_real) flowers_real,sum(chinese_medicine_should) chinese_medicine_should,
         sum(chinese_medicine_real) chinese_medicine_real,sum(dispatch_tree_should) dispatch_tree_should,
         sum(dispatch_tree_real) dispatch_tree_real,sum(dispatch_breed_should) dispatch_breed_should,
         sum(dispatch_breed_real) dispatch_breed_real,sum(dispatch_bamboo_should) dispatch_bamboo_should,
         sum(dispatch_bamboo_real) dispatch_bamboo_real,sum(dispatch_fruit_should) dispatch_fruit_should,
         sum(dispatch_fruit_real) dispatch_fruit_real,sum(dispatch_should) dispatch_should,
         sum(dispatch_real) dispatch_real,sum(dispatch_medicine_should) dispatch_medicine_should,
         sum(dispatch_medicine_real) dispatch_medicine_real,sum(epidemic_number) epidemic_number,
         sum(epidemic_rate) epidemic_rate,sum(quarantine_rate) quarantine_rate,
         sum(quarantine_treatment) quarantine_treatment,sum(quarantine_treatment_rate) quarantine_treatment_rate,
         sum(fee) fee,sum(frequency) frequency,
         sum(fine) fine,sum(country_number) country_number,
         sum(country_area) country_area,sum(province_number) province_number,
         sum(province_area) province_area';
         if (!empty($data['region'])) $where.=" and region like '%".$data['region']."%'";
         if (!empty($data['year'])) $where.=" and year = ".$data['year'];
        $db = Db::table('improve_quarantine_base')
            ->field($a)
            ->where($where)
            ->find();
        if (empty($db)){
            $db = "";
        }else{
            $db['region_name'] = $region_name;
            $res = Db::table('improve_quarantine_base')
            ->field('region_name,remark')
            ->where($where)
            ->group('region_name,remark')
            ->select();
            $remarks = '';
            foreach ($res as $key => $value) {
                $remarks = $remarks .','. $value['region_name'].':'.$value['remark'];
            }
            $db['remark'] = substr($remarks, 1);
        }
        return Communal::successData($db);
    }

    /**
     * 检疫站模型
     * 修改人：余思渡
     * 修改时间：2019.03.05
     * 修改内容: 将db_name b_quarantine_base 改为 improve_quarantine_base
     *          将db_name c_region 改为 sys_district
     *          将 join() 内 parentId 改为 parent_id
     *          2019.03.08 追加是否为数组判断逻辑 修改数据返回格式，错误执行抛出
    */
    static function query($id){
        try {
            $dbRes = Db::table('improve_quarantine_base')
                ->field('adder',true)
                ->where('id', $id)
                ->where('status',1)
                ->find();
            $region_id = Db::table('improve_quarantine_base')->alias('bt')
				->where('bt.id', $id)
				->join('sys_district r', 'r.id = bt.region', 'left')
				->join('sys_district r2', 'r.parent_id = r2.id', 'left')
				->join('sys_district r3', 'r2.parent_id = r3.id', 'left')
				->join('sys_district r4', 'r3.parent_id = r4.id', 'left')
				->field('r4.id r4,r3.id r3,r2.id r2,r.id r1')
                ->find();
            //追加是否为数组的判断
            if(is_array($region_id) && !is_null($dbRes)){
                $dbRes['region_number'] = array_values(array_filter($region_id));
            }else{
                $dbRes = [];
            }
            $result = Communal::removeEmpty($dbRes);
            return empty($result) ? Error::error('未找到数据'): Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
    /**
     * 检疫站模型
     * 修改人：余思渡
     * 修改时间：2019.03.06  2019.03.07
     * 修改内容: 将db_name b_quarantine_base 改为 improve_quarantine_base
     *           重写数据检测逻辑
     *          2019.03.08 修改数据返回格式，错误执行抛出
    */
    static function edit($data)
    {
        unset($data['did']);
        try {
            //自行查询
            $check = Db::table('improve_quarantine_base')->where('id', $data['id'])->where('status',1)->value('id');
            if ($check <= 0) {
                return  Error::error('未找到数据');
            } else {
                $data['update_date'] = date('Y-m-d H:i:s');
                $dbRes = Db::table('improve_quarantine_base')
                    ->field('create_date', true)->update($data);
                return $dbRes == 1 ? Communal::success('修改成功')  : Error::error('修改失败');
            }
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /**
     * 检疫站模型
     * 修改人：余思渡
     * 修改时间：2019.03.07
     * 修改内容: 将db_name b_quarantine_base 改为 improve_quarantine_base
     *           重写数据检测逻辑
     *          2019.03.08 修改数据返回格式，错误执行抛出
    */
    static function deleteChecked($ids){
        try {
            $dataRes = Db::table('improve_quarantine_base')->whereIn('id', $ids)->update(['status'=> 2]);
            //return $dataRes;
            return $dataRes > 0 ? Communal::success('删除成功') : Error::error('删除失败');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

}