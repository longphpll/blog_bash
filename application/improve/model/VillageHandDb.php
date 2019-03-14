<?php

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use app\improve\model\BaseDb;
use app\improve\controller\UploadHelper;
use think\Db;
use tool\Error;
use tool\Communal;
use tool\BaseDb as BaseDbs;
/**
 * 病虫害防治
 */
class VillageHandDb extends BaseDb
{

    static function add($data){
        unset($data['did']);
        try {
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $data['status'] = 1 ;
            Db::startTrans();
            if (Helper::lsWhere($data, 'images')){
                $images = $data['images'];
            }
            unset($data['images']);
            if (Helper::lsWhere($data, 'record')){

                $record = $data['record'];
                unset($data['record']);
                $dbRes = Db::table('improve_village_hand')->insertGetId($data);
                foreach ($record as $key => $val) {
                    unset($val['check']);
                    unset($val['id']);
                    $one_class_name = Db::table('improve_hand_info')->where('id',$val['hand_one_class'])->field('name')->find();
                    $val['one_class_name'] = $one_class_name['name'];
                    $two_class_name = Db::table('improve_hand_info')->where('id',$val['hand_two_class'])->field('name')->find();
                    $val['two_class_name'] = $two_class_name['name'];
                    if($val['drug_name'] == 0){
                        $val['drug_chs_name'] = '无';
                    }else{
                        $drug_chs_name = Db::table('improve_hand_drug')->where('id',$val['drug_name'])->field('name')->find();
                        $val['drug_chs_name'] = $drug_chs_name['name'];
                    }
                    $res = Db::table('improve_village_hand_record')->insertGetId($val);
                    $record_data = [
                        'vh_id' => $dbRes,
                        'create_time' => $data['create_time'],
                        'update_time' => $data['create_time'],
                        'status' => 1
                    ];
                    $abroad_dbRes = Db::table('improve_village_hand_record')->where('id',$res)->update($record_data);
                }    
            }else{
                unset($data['record']);
                $dbRes = Db::table('improve_village_hand')->insertGetId($data);
            }
            if ($dbRes < 1) return Error::error('添加失败');
            if (!empty($images)){
                $path = Db::table('improve_uploads')->whereIn('id',$images)->field('path')->select();
                if (empty($path)) return Error::error('上次文件未找到');
                foreach ($path as $val) {
                    $record = [
                        'hand_id' => $dbRes,
                        'path' => $val['path'],
                        'create_time' => $data['create_time'],
                        'status' => 1
                    ];
                    $a = Db::table('improve_village_hand_image')->insert($record);
                    if ($a < 1) return Error::error('图片添加失败');
                }
            }
            Db::commit();
            return Communal::success('新增信息成功');
        } catch (\Exception $e) {
            Db::rollback();
            return Error::error($e->getMessage());
        }
    }

    static function info($data){
        $dbRes = Db::table('improve_hand_drug')->insertGetId($data);
        if ($dbRes < 0) return Error::error('添加失败');
        return Communal::success('添加成功');
    }
    
    //防治措施信息
    static function handWay($data){
        if(empty($data['id'])) {
            $id = 0;
        }else{
            $id = $data['id'];
        }
        $order = 'id asc';
        // if(!empty($data['label'])) $where.=" and vh.region like '%".$data['region']."%'";
        $dbRes = Db::table('improve_hand_info')->field('id value,name label')->where('fatherId',$id)->order($order)->select();
        return empty($dbRes) ? Error::error('未找到对应数据') : Communal::successData($dbRes) ;
    }

     //防治药剂信息
     static function drugInfo($data){
        $order = 'id asc';
        // if(!empty($data['label'])) $where.=" and vh.region like '%".$data['region']."%'";
        $dbRes = Db::table('improve_hand_drug')->field('id value,name label')->where('info_id',$data['id'])->order($order)->select();
         return empty($dbRes) ? Error::error('未找到对应数据') : Communal::successData($dbRes);
    }

    //列表
    static function ls($data,$sample = false){
        try {
            $where = 'vh.status = 1';
            $order = 'vh.update_time desc';
            if(!empty($data['region'])) $where.=" and vh.region like '%".$data['region']."%'";
            if(!empty($data['type'])) $where.=" and vh.type = ".$data['type'];
            if(!empty($data['pest'])) $where.=" and vh.pest_id = ".$data['pest'];
            if(!empty($data['start_time'])) $where.=" and vh.hand_time >='".$data['start_time']."'";
            if(!empty($data['end_time'])) $where.=" and vh.hand_time <='".$data['end_time']."'";
            if(!empty($data['tel'])) $where.=" and u.cellphone = ".$data['tel'];
            if ($sample) {
                $field = 'vh.id,vh.positions,vh.location_name,vh.region_name,vh.type,vh.pest_name,vh.hand_time,u.name surveyer,u.cellphone as tel';
            } else {
                $field = 'vh.id, vh.region_name, vh.type, vh.pest_name,vh.hand_time,vh.hander, vh.hand_cost,
                vh.hand_area,vh.hand_effect, u.name adder, vh.adder uid,u.cellphone as tel,vh.update_time';
            }
            $dbRes = Db::table('improve_village_hand')->alias('vh')->join('frame_base_staff u', 'u.uid = vh.adder', 'left')->field($field)->where($where)
                ->order($order)->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
           return Communal::successData($dbRes);
        } catch (\Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function query($id){
        try {
            $dataRes = Db::table('improve_village_hand')->alias('vh')
                ->join('frame_base_staff u', 'u.uid = vh.adder', 'left')
                ->field('vh.id, vh.region,vh.region_name,vh.positions,vh.position_type,vh.location_name,vh.type,vh.pest_id,vh.pest_name,vh.hand_time,
                vh.hander,vh.hand_cost,vh.happen_area,vh.hand_area,vh.hand_effect,vh.save_pest_area,vh.update_time, u.name adder,u.cellphone as tel')
                ->where('vh.status',1)
                ->where('vh.id', $id)
                ->find();
            if (empty($dataRes)) return Error::error('未找到相应数据');
            $dataRes['record'] = Db::table('improve_village_hand_record')->where('vh_id', $id)->where('status',1)->field('vh_id,create_time,update_time,status',true)->select();
            $dataRes['images'] = Db::table('improve_village_hand_image')->where('hand_id', $id)->where('status',1)->field('id,path')->select();
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    static function edit($data){
        unset($data['did']);
        try {
            if (!static::query($data['id'])) return Error::error('未找到相应数据');
            $paths = [];
            $data['update_time'] = date('Y-m-d H:i:s');
            Db::startTrans();
             //图片上传与修改处理
             if (Helper::lsWhere($data,'images')) {
                $images = $data['images'];
                unset($data['images']);
                $path = Db::table('improve_uploads')->whereIn('id',$images)->field('path')->select();
                if (empty($path)) return Error::error('未找到上传文件');
                foreach ($path as $val) {
                    $record = [
                        'hand_id' => $data['id'],
                        'path' => $val['path'],
                        'create_time' => $data['update_time'],
                        'status' => 1
                    ];
                    $a = Db::table('improve_village_hand_image')->insert($record);
                    if ($a < 1) return Error::error('图片添加失败');
                }
            }
            //图片删除处理
            if (Helper::lsWhere($data, 'del_images')) {
                $del_images = explode(',',$data['del_images']);
                $paths = Db::table('improve_village_hand_image')
                     ->field('path')->where('hand_id', $data['id'])->whereIn('id', $del_images)->select();
                if (count($paths) !== count($del_images)) return Error::error('删除图片未找到');
                $delRes = Db::table('improve_village_hand_image')->whereIn('id',$del_images)->update(['status' => 2]);
                if ($delRes !== count($del_images)) return Error::error('删除图片失败');
            }
            unset($data['del_images']);
            //样本记录--删除处理
            if(Helper::lsWhere($data,'del_records')){
                $del_ids = explode(',',$data['del_records']);
                $record_res = Db::table('improve_village_hand_record')->whereIn('id',$del_ids)->update(['status' => 2]);
                if ($record_res < 0) return Error::error('删除样本记录失败');
            }
            unset($data['del_records']);
            //样本记录--修改和添加处理
            if(Helper::lsWhere($data,'record')){
                $hand_record = $data['record'];
                foreach ($hand_record as $key => $val) {
                    $one_class_name = Db::table('improve_hand_info')->where('id',$val['hand_one_class'])->field('name')->find();
                    $val['one_class_name'] = $one_class_name['name'];
                    $two_class_name = Db::table('improve_hand_info')->where('id',$val['hand_two_class'])->field('name')->find();
                    $val['two_class_name'] = $two_class_name['name'];
                    if($val['drug_name'] == 0){
                        $val['drug_chs_name'] = '无';
                    }else{
                        $drug_chs_name = Db::table('improve_hand_drug')->where('id',$val['drug_name'])->field('name')->find();
                        $val['drug_chs_name'] = $drug_chs_name['name'];
                    }
                    if(isset($val['id']) && $val['id'] != 0 ){
                        unset($val['check']);
                        $val['update_time'] = $data['update_time'];
                        $dbRes = Db::table('improve_village_hand_record')->update($val);
                        if (empty($dbRes)) return Error::error('修改样本记录失败');
                    }else{
                        unset($val['check']);
                        unset($val['id']);
                        $val['vh_id'] = $data['id'];
                        $val['create_time'] = $data['update_time'];
                        $val['update_time'] = $data['update_time'];
                        $val['status'] = 1;
                        $record_dbRes = Db::table('improve_village_hand_record')->insertGetId($val);
                    }
                }
            }
            unset($data['record']);
            $dbRes = Db::table('improve_village_hand')->field("adder,create_time,status",true)->update($data);
            Db::commit();
            return $dbRes == 1 ? Communal::success('修改成功') : Error::error('修改失败');
        } catch (\Exception $e) {
            Db::rollback();
            return Error::error($e->getMessage());
        }
    }

     //删除
    static function deleteChecked($ids){
        try {
            $dataRes = Db::table('improve_village_hand')->whereIn('id', $ids)->update(['status'=> 2]);
            return empty($dataRes) ? Error::error('删除失败') : Communal::success('删除成功');
        } catch (\Exception $e) {
            return Errors::error($e->getMessage());
        }
    }

	//防治记录统计图
    static function messageChart($data){
        try {
			$res = [];
            $dbRes = Db::table('improve_village_hand')
                ->field("DATE_FORMAT(hand_time,'%Y-%m') time,SUM(happen_area) happen_area, SUM(hand_area) hand_area, SUM(save_pest_area) save_pest_area")
                ->where('status',1)
                ->where('pest_id', $data['pest'])
                ->whereLike('region', $data['region'] . '%')
                ->where("DATE_FORMAT(hand_time,'%Y-%m') >='".$data['start_time']."'")
                ->where("DATE_FORMAT(hand_time,'%Y-%m') <='".$data['end_time']."'")
                ->group('time')
                ->select();
            $total_res = Db::table('improve_village_hand')
                ->field('SUM(happen_area) total_happen_area, SUM(hand_area) total_hand_area, SUM(save_pest_area) total_save_area')
                ->where('pest_id', $data['pest'])
                ->whereLike('region', $data['region'] . '%')
                ->where('status',1)
                ->where("DATE_FORMAT(hand_time ,'%Y-%m') >='".$data['start_time']."'")
                ->where("DATE_FORMAT(hand_time ,'%Y-%m') <='".$data['end_time']."'")
                ->find();
            if (empty($total_res['total_happen_area'])) {
                $res['title'] = '';
            }else{
                $region_name = BaseDbs::regionName($data['region']);
                $pest_name  = BaseDb::pest($data['pest']);
                $begin_year = substr($data['start_time'],0,4);
                $begin_month = substr($data['start_time'],-2);
                $end_year = substr($data['end_time'],0,4);
                $end_month = substr($data['end_time'],-2);
                $res['title'] = $region_name.$pest_name.'--'.$begin_year.'年'.$begin_month.'月'.'到'.$end_year.'年'.$end_month.'月'.'总共发生面积'.$total_res['total_happen_area'].'亩,'.'防治面积'.$total_res['total_hand_area'].'亩,'.'挽回灾害面积'.$total_res['total_save_area'].'亩';
            }
            $res['data'] = $dbRes;
            return Communal::successData($res);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    //防治记录统计
    static function villagesList($data){
        try {
            //总数统计
            $dataResOne = VillageHandDb::villagesTon($data);
            //按月统计
            $dataResTwo = VillageHandDb::villagesListSon($data);
            $result = array('dataResOne'=>$dataResOne,'dataResTwo'=>$dataResTwo);
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::Error($e->getMessage());
        }
    }

    //防治记录统计导出
    static function villagesRecord($data){
        try {
            //总数统计
            $dataResOne = VillageHandDb::villagesTon($data);
            //按月统计
            $dataResTwo = VillageHandDb::villagesListRecord($data);
            $result = array('dataResOne'=>$dataResOne,'dataResTwo'=>$dataResTwo);
            return  Communal::successData($result);
        } catch (\Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

     // 总数统计
     static function villagesTon($data){
        $d = "count(distinct(region)) total_town,SUM(hand_cost) total_hand_cost,SUM(happen_area) total_happen_area,SUM(hand_area) total_hand_area,SUM(save_pest_area) total_save_pest_area, count(id) total_survey";
        $db = Db::table('improve_village_hand')
            ->field($d)
            ->where('status',1)
            ->where('pest_id', $data['pest'])
            ->whereLike('region', $data['region'] . '%')
            ->where("DATE_FORMAT(hand_time, '%Y-%m') >='". $data['start_time']."'")
            ->where("DATE_FORMAT(hand_time, '%Y-%m') <='". $data['end_time']."'")
            ->find();
        if(empty($db['total_hand_cost'])){
            $db['total_hand_cost'] = 0;
            $db['total_happen_area'] = 0;
            $db['total_hand_area'] = 0;
            $db['total_save_pest_area'] = 0;
        }
        return $db;
    }

    // 按月统计
    static function villagesListSon($data){
        $a = 'region,region_name,pest_name,DATE_FORMAT(hand_time, "%Y-%m") hand_time,SUM(hand_cost) hand_cost,SUM(happen_area) happen_area,SUM(hand_area) hand_area,SUM(save_pest_area) save_pest_area,count(id) sum_survey';
        $db = Db::table('improve_village_hand')
            ->field($a)
            ->where('status',1)
            ->where('pest_id', $data['pest'])
            ->whereLike('region', $data['region'] . '%')
            ->where("DATE_FORMAT(hand_time, '%Y-%m') >='". $data['start_time']."'")
            ->where("DATE_FORMAT(hand_time, '%Y-%m') <='". $data['end_time']."'")
            ->group('hand_time,region')
            ->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();  
        return $db;
    }

    // 按月统计--列表导出
    static function villagesListRecord($data){
        $a = 'region_name,pest_name,DATE_FORMAT(hand_time, "%Y-%m") hand_time,SUM(hand_cost) hand_cost,SUM(happen_area) happen_area,SUM(hand_area) hand_area,SUM(save_pest_area) save_pest_area,count(id) sum_survey';
        $db = Db::table('improve_village_hand')
            ->field($a)
            ->where('status',1)
            ->where('pest_id', $data['pest'])
            ->whereLike('region', $data['region'] . '%')
            ->where("DATE_FORMAT(hand_time, '%Y-%m') >='". $data['start_time']."'")
            ->where("DATE_FORMAT(hand_time, '%Y-%m') <='". $data['end_time']."'")
            ->group('hand_time,region_name')
            ->select();  
        return $db;
    }
    
    //web--统计--已有有害生物种类查询列表
    static function pestList($data){
        try {
            $where ='status = 1';
            $result['all'] =Db::table('improve_village_hand')->where($where)->field('pest_id value, pest_name label')->group('pest_name,pest_id')->select();
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    //已有有害生物种类查询列表
    static function typeWebList($data){
        try {
            $where ='status = 1';
            if (!empty($data['type'])) $where.=" and type = ". $data['type'];
            $result =Db::table('improve_village_hand')->where($where)->field('pest_id value, pest_name label')->group('pest_name,pest_id')->select();
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    //app--类型查询生物种类
    static function typeList($data){
        try {
            $where ='status = 1';
            if (!empty($data['type'])) $where.=" and type = ". $data['type'];
            if (!empty($data['name'])) $where.=" and pest_name like '%". $data['name']."%'";
            $result =Db::table('improve_village_hand')->where($where)->field('pest_id value, pest_name label')->group('pest_name,pest_id')->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            return empty($result) ? Error::error('未找到对应数据') : Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
    
    // 数据导出
    static function exportls($data,$field,$img,$record,$condition){
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            $field.=',id';
            if(!empty($condition['region'])) $where.=" and region like '%".$condition['region']."%'";
            if(!empty($condition['type'])) $where.=" and type = ".$condition['type'];
            if(!empty($condition['pest'])) $where.=" and pest_name like '%".$condition['pest']."%'";
            if(!empty($condition['start_time'])) $where.=" and hand_time >='".$condition['start_time']."'";
            if(!empty($condition['end_time'])) $where.=" and hand_time <='".$condition['end_time']."'";
            $dataRes = Db::table('improve_village_hand')->field($field)->where($where)->order($order)->select();
             // 获取图片
             if ($img){
                foreach ($dataRes as $key => $val) {
                    $dataRes[$key]['img'] = Db::table('improve_village_hand_image')->where('hand_id', $val['id'])->where('status',1)->field('path')->select();
                }
            }
            // 获取记录
            if ($record != false){
                foreach ($dataRes as $key => $val) {
                    $result = Db::table('improve_village_hand_record')->where('vh_id', $val['id'])->where('status',1)->field($record)->select();
                    if (!empty($result)){
                        foreach ($result as $ky => $vl) {
                            $val['record'][$ky] = array_values($vl);
                        }
                        $dataRes [$key] = $val;
                    }else{
                        $dataRes[$key]['record'] = [];
                    }
                }
            }
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }
}