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
use app\improve\model\BaseDb;
use think\Db;
use tool\Communal;
use tool\Error;
use tool\BaseDb as ToolBaseDb;

class TrapDb extends BaseDb
{
    static function add($data)
    {
        try {
            $data['create_time'] =  date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $data['status'] = 1;
            $data['device_status'] = 1;
            $data['server_status'] = 1;
            $data['label'] = 1;
            unset($data['id']);
            unset($data['did']);
            for ($i=0; $i < $data['amount'] ; $i++) { 
                $data['number'] = BaseDb::prNum('improve_trap',$data['trap_number']);
                $result = Db::table('improve_trap')->insertGetId($data);
            }
            return is_numeric($result) ? Communal::success('添加成功') : Error::error('添加失败');
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    static function codePath($id,$path)
    {
        try {
            $data['trap_id'] = $id;
            $data['path'] = $path;
            $data['status'] = 1;
            $result = Db::table('b_trap_code')->insertGetId($data);
            return is_numeric($result) ? [true ,$result] : Errors::ADD_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function ls($data,$sample = false)
    {
        try {
            $where = 'b.status = 1';
            $order = 'b.update_time desc';
            if(!empty($data['region'])) $where.=" and b.region like '%".$data['region']."%'";
            if(!empty($data['number'])) $where.=" and b.number ='".$data['number']."'";
            if(!empty($data['unit']))  $where.=" and b.unit ='".$data['unit']."'";
            if(!empty($data['state']))  $where.=" and b.server_status =".$data['state'];
            if(!empty($data['type']))  $where.=" and b.device_status =".$data['type'];
            if(!empty($data['label']))  $where.=" and b.label =".$data['label'];
            if ($sample) {
                $field = 'b.id,b.number,b.region,b.region_name,tm.positions,b.unit,b.relation_name,b.device_status,b.server_status,b.relation_tel,b.create_time';
                $res = Db::table('b_trap b')
                    ->join('b_trap_maintain tm',' b.number=tm.trap_number')
                    ->where($where)
                    ->group('b.id')
                    ->order($order)
                    ->field($field)->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
                foreach ($res['data'] as $key => $value) {
                    if(!$value['positions']){
                        unset($res['data'][$key]);
                    }
                }
                $dataRes =$res;
            } else {
                $field = 'b.id,b.number,b.region,b.region_name,b.unit,b.purpose,b.company,b.drug_model,b.drug_batch,b.device_status,b.server_status,b.label,b.create_time';
                $dataRes = Db::table('b_trap')->alias('b')->where($where)->field($field)
                ->order($order)->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            }
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function devicels($data,$adder)
    {
        try {
            $where = 'b.status = 1 and b.server_status = 2';
            $order = 'b.update_time desc';
            if(!empty($data['region'])) $where.=" and b.region like '%".$data['region']."%'";
            if(!empty($data['number'])) $where.=" and b.number ='".$data['number']."'";
            if(!empty($data['unit']))  $where.=" and b.unit ='".$data['unit']."'";
            if(!empty($data['state']))  $where.=" and b.server_status =".$data['state'];
            if(!empty($data['type']))  $where.=" and b.device_status =".$data['type'];
            if(!empty($data['label']))  $where.=" and b.label =".$data['label'];
                $field = 'b.id,b.number,b.region_name,count(tm.id) total_batch,b.create_time';
                $dataRes = Db::table('b_trap b')
                    ->join('b_trap_maintain tm',' b.number=tm.trap_number')
                    ->where($where)
                    ->where('tm.adder',$adder)
                    ->group('b.id')
                    ->order($order)
                    ->field($field)->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }


    // 诱捕器编号查询
    static function trapls($data)
    {
        try {
            $where ='status = 1';
            if (!empty($data['number'])) $where.=" and number like '%". $data['number']."%'";
            $result = Db::table('b_trap')->where($where)->field('distinct number')->select();
            return !empty($result) ? [true, $result] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // 所属单位查询
    static function unitls($data)
    {
        try {
            $where ='status = 1';
            if (!empty($data['unit'])) $where.=" and unit like '%". $data['unit']."%'";
            $result = Db::table('b_trap')->where($where)->field('distinct unit')->select();
            return !empty($result) ? [true, $result] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    static function query($id)
    {
        try {
            $dbRes = Db::table('b_trap')
                ->field('id,number,region,region_name,unit,purpose,company,relation_name,relation_tel,amount,drug_model,drug_batch,device_status,server_status,create_time')
                ->where('status',1)
                ->where('id', $id)
                ->find();
            $dbRes['code'] = Db::table('b_trap_code')->field('id,path')->where('trap_id',$id)->select();
            return is_array($dbRes) ? [true, $dbRes] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function label($ids)
    {
        try {
            $dbRes = Db::table('b_trap')
                ->field('id,number,unit,purpose,company,label,drug_model')
                ->whereIn('id', $ids)
                ->where('status',1)
                ->select();
            return  [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //标签信息查询
    static function info($ids)
    {
        try {
            $dbRes = Db::table('b_trap')->field('number,unit,purpose,company,label')
                ->whereIn('id', $ids)
                ->where('status',1)
                ->select();
            return  [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //诱捕器编号查询--生成
    static function toTrap($ids)
    {
        try {
            $dbRes = Db::table('b_trap')->whereIn('id', $ids)
                ->where('status',1)
                ->field('number,label')
                ->select();
            foreach ($dbRes as $key => $val) {
                if($val['label'] != 1) return [false];
            }
            return !empty($dbRes) ? [true, $dbRes] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //诱捕器编号查询--导出
    static function trap($ids)
    {
        try {
            $dbRes = Db::table('b_trap')->whereIn('id', $ids)
                ->where('status',1)
                ->field('number,label')
                ->select();
            foreach ($dbRes as $key => $val) {
                if($val['label'] !== 2) return [false];
            }
            return !empty($dbRes) ? [true, $dbRes] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }


    static function modifyLabel($id)
    {
        try {
            $dbRes = Db::table('b_trap')->where('id', $id)
                ->where('status',1)
                ->field('label')
                ->update(['label' => 2]);
            return  [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function edit($data)
    {
        try {
            if (!static::query($data['id'])[0]) return Errors::DATA_NOT_FIND;
            $dbRes = Db::table('b_trap_code')->where('trap_id',$data['id'])->field('id,status')->delete();
            $data['update_time'] =  date('Y-m-d H:i:s');
            $data['label'] = 1;
            $dbRes = Db::table('b_trap')->field('unit,purpose,device_status,company,relation_name,relation_tel,drug_model,drug_batch,label,update_time')->update($data);
            return $dbRes == 1 ? [true, $dbRes] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 数据导出
    static function exportls($data,$field,$condition){
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            if(!empty($condition['region'])) $where.=" and region like '%".$condition['region']."%'";
            if(!empty($condition['number'])) $where.=" and number ='".$condition['number']."'";
            if(!empty($condition['unit']))  $where.=" and unit ='".$condition['unit']."'";
            $dataRes = Db::table('b_trap')->where($where)->field($field)
                ->order($order)->select();
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
}