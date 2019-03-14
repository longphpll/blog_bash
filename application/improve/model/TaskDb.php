<?php

namespace app\improve\model;

use app\improve\controller\Errors;
use app\improve\controller\Helper;
use app\improve\controller\UploadHelper;
use app\improve\model\UserDb;
use think\Db;

/**
 * 任务管理
 * 任务状态，0：已发布 1：执行中 2：已完成，-1：已取消，-2：已过期，-3：已拒绝
 */
class TaskDb
{

    static function add($data, $images)
    {
        try {
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $data['status'] = 3;
            $assigners = $data['assigner'];
            unset($data['assigner']);
            Db::startTrans();
            $dbRes = Db::table('b_task')->insertGetId($data);
            if ($dbRes < 1)  return Errors::ADD_ERROR;
            if (!empty($images)) {
                if (count($images) > 6) return Errors::IMAGE_COUNT_ERROR;
                foreach ($images as $image) {
                    $info = $image->move(Errors::FILE_ROOT_PATH. DS. 'task');
                    if($info){
                        // 成功上传后 获取上传信息
                        $name = 'file'.DS .'task'. DS .$info->getSaveName();
                        $record = [
                            'tid' => $dbRes,
                            'path' => $name,
                            'use' => 1
                        ];
                        // 保存
                        $b = Db::table('b_task_image')->insert($record);
                        if ($b < 1) return Errors::IMAGES_INSERT_ERROR;
                    }
                }
            }
			foreach ($assigners as $a) {
                $r = Db::table('b_task_assigner')->insert(['tid' => $dbRes, 'uid' => $a,'status' => 2,'state' => 1]);
                if ($r < 1) return Errors::ADD_ERROR;
            }
            Db::commit();
            return [true, $dbRes];
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    /**
     * 过期处理
     */
    private static function whereStatus($data, $query)
    {
        switch ($data['status']) {
            case -2:
                $query->where('t.deadline', '<', date('Y-m-d H:i:s'));
                $query->where('t.status', '<>', 2);
                break;
            case -3:
                $query->where('t.deadline', '<', date('Y-m-d H:i:s'));
                $query->whereOr('t.status', 2);
                break;
            default:
                $query->where('t.deadline', '>=', date('Y-m-d H:i:s'));
                $query->where('t.status', $data['status']);
                break;
        }
    }

    //更新数据库中已经过期任务的状态
    static function expired()
    {
        try {
            Db::startTrans();
            $nowdate = time();
            $dbRes = Db::table('b_task')
                        ->where('deadline','< time',$nowdate)
                        ->where('status','in',[3,1])->update(['status' => '-2']);
            if ($dbRes !== false)
            {
                Db::commit();
            }else{
                return Errors::FAILURE_OF_TASK_STATE_UPDATE;
            }               
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    static function taskNameList($data){
        $where = '1 = 1';
        $order = 't.create_time desc';
        if (!empty($data['region'])) $where.=" and t.region like '%".$data['region']."%'";
        if (!empty($data['name'])) $where.=" and t.name like '%".$data['name']."%'";
        $dataRes = Db::table('b_task')->alias('t')->field('name')
            ->group('t.id')
            ->order($order)->select();
        if(empty($dataRes)) return Errors::DATA_NOT_FIND;
        $result = Helper::transFormation($dataRes);
        return [true,$result];
    }

    //列表
    static function ls($data,$rid,$user,$auth){
        try {
            $where = '1 = 1';
            $order = 't.create_time desc';
            if (!empty($data['name'])) $where.=" and t.name like '%".$data['name']."%'";
            if (!empty($data['type'])) $where.=" and t.type =".$data['type'];
            if (!empty($data['status'])) $where.=" and t.status =".$data['status'];
            if (!empty($data['tel'])) $where.=" and u.tel =".$data['tel'];
            $field = 't.id, t.region_name, t.name, t.type, t.founder, u.name founder_name,u.tel founder_tel, t.recevier, u1.name recevier_name,u1.tel recevier_tel, t.create_time, t.deadline, t.status';
            if(($rid == 1 and strlen($auth['s_region'])==2) or ($rid==3 and strlen($auth['s_region'])==2)){//省管理员、超级用户
                $where.=" and t.region like '".$data['region']."%'";
                $dataRes = Db::table('b_task')->alias('t')->field($field)
                    ->join('b_task_assigner ta', 'ta.tid= t.id', 'left')
                    ->join('u_user u', 'u.uid = t.founder', 'left')
                    ->join('u_user u1', 'u1.uid = t.recevier', 'left')->where($where)
                    ->group('t.id')
                    ->order($order)->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            }else if($rid==2){//普通
                $where.="  and ta.uid ='$user'";
                $dataRes = Db::table('b_task')->alias('t')->field($field)
                    ->join('b_task_assigner ta', 'ta.tid= t.id', 'left')
                    ->join('u_user u', 'u.uid = t.founder', 'left')
                    ->join('u_user u1', 'u1.uid = t.recevier', 'left')->where($where)
                    ->group('t.id')
                    ->order($order)->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            }else{// 市县管理员
                $where.=" and ( (t.region like '".$data['region']."%' AND t.founder='$user') OR (ta.uid='$user' and t.region like '".$data['region']."%'))";
                $dataRes = Db::table('b_task')->alias('t')->field($field)
                    ->join('b_task_assigner ta', 'ta.tid= t.id', 'left')
                    ->join('u_user u', 'u.uid = t.founder', 'left')
                    ->join('u_user u1', 'u1.uid = t.recevier', 'left')->where($where)
                    ->group('t.id')
                    ->order($order)->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            }
            if(empty($dataRes)) return Errors::DATA_NOT_FIND;
            $result = Helper::transFormation($dataRes);  
            return [true,$result];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

	//接受任务
	static function receive($data)
    {
        try {
            Db::startTrans();
            $data['update_time'] = date('Y-m-d H:i:s');
            $dbRes = Db::table('b_task')->field('recevier,recevier_name,status,update_time')->update($data);
            if ($dbRes == 1){	
				Db::commit();
			}
            return $dbRes == 1 ? [true , $dbRes] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    //完成任务-反馈
    static function finishEdit($data, $images = null)
    {

        try {
            Db::startTrans();
			$data['update_time'] = date('Y-m-d H:i:s');
            $dbRes = Db::table('b_task')->update($data);
            if (!empty($images)) {
                if (count($images) > 6) return Errors::IMAGE_COUNT_ERROR;
                foreach ($images as $image) {
                    $info = $image->move(Errors::FILE_ROOT_PATH. DS. 'task');
                    if($info){
                        // 成功上传后 获取上传信息
                        $name = 'file'.DS .'task'. DS .$info->getSaveName();
                        $record = [
                            'tid' => $data['id'],
                            'path' => $name,
                            'use' => 2
                        ];
                        // 保存
                        $b = Db::table('b_task_image')->insert($record);
                        if ($b < 1) return Errors::IMAGES_INSERT_ERROR;
                    }
                }
            }
            Db::commit();
			return $dbRes == 1 ? [true , $dbRes] : Errors::UPDATE_ERROR;
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    //取消任务
    static function cancelTask($data)
    {
        try {
            Db::startTrans();
            $data['update_time'] = date('Y-m-d H:i:s');
            $dbRes = Db::table('b_task')->where('id',$data['id'])->update($data);
            if ($dbRes !== 1) return Errors::CANCEL_ERROR;
            Db::commit();
            return [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //拒绝任务
    static function refuseTask($data)
    {
        try {
            Db::startTrans();
            $data['update_time'] = date('Y-m-d H:i:s');
            $data['refuse_time'] = date('Y-m-d');
            $dbRes = Db::table('b_task')->where('id',$data['id'])->field('id,status,reason,update_time,refuse_time')->update($data);
            if ($dbRes != 1) return Errors::REFUSE_ERROR;
            Db::commit();
            return [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //详情
    static function query($id)
    {
        try {
            $task = Db::table('b_task')->where('id', $id)->find();
            if (empty($task)) return Errors::DATA_NOT_FIND;
            $task['founder_name'] = Db::table('u_user')->where('uid', $task['founder'])->column('name')[0];
            $task['founder_tel'] = Db::table('u_user')->where('uid', $task['founder'])->column('tel')[0];
            if (!empty($task['recevier'])) $task['recevier_name'] = Db::table('u_user')->where('uid', $task['recevier'])->column('name')[0];
            $task['task_images'] = Db::table('b_task_image')->where('tid', $id)->where('use',1)->field('id,path')->select();
            $task['fank_images'] = Db::table('b_task_image')->where('tid', $id)->where('use',2)->field('id,path')->select();
            $task['assigner'] = Db::table('b_task_assigner')->alias('ta')
                ->join('u_user u', 'u.uid = ta.uid')
                ->field('u.uid,u.name,u.tel')
                ->where('ta.tid', $id)
                ->where('ta.state', 1)
                ->select();
			$result = Helper::transFormation($task);  
            return is_array($result) ? [true, $result] : Errors::DATA_NOT_FIND;			
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //任务重新发布详情
    static function republishQuery($id)
    {
        try {
            $task = Db::table('b_task')->where('id', $id)->find();
            if (empty($task)) return Errors::DATA_NOT_FIND;
            $task['founder_name'] = Db::table('u_user')->where('uid', $task['founder'])->column('name')[0];
            if (!empty($task['recevier'])) $task['recevier_name'] = Db::table('u_user')->where('uid', $task['recevier'])->column('name')[0];
            $task['images'] = Db::table('b_task_image')->where('tid', $id)->where('use',1)->select();
            $task['del_images'] = Db::table('b_task_image')->where('tid', $id)->where('use',1)->field('id')->select();
            $task['assigner'] = Db::table('b_task_assigner')->alias('ta')
                ->where('ta.tid', $id)
                ->join('u_user u', 'u.uid = ta.uid')
                ->field('u.uid,u.name,u.tel')
                ->select();
			$result = Helper::transFormation($task);  
            return is_array($result) ? [true, $result] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //重新发布任务
    static function republish($data, $images,$status)
    {
        try {
            $assigners = $data['assigner'];
            unset($data['assigner']);
            $path = [];
            Db::startTrans();
            if(Helper::lsWhere($data,'del_images')){
                $del_images = $data['del_images'];
                $paths = Db::table('b_task_image')->field('path')->where('tid', $data['id'])->whereIn('id',$del_images)->select();
                if (count($paths) !== count($del_images)) return Errors::NO_IMAGES_DELETED;
                $delRes = Db::table('b_task_image')->whereIn('id',$del_images)->delete();
                if ($delRes !== count($del_images)) return Errors::DELETE_ERROR;
            }
            unset($data['del_images']);
            $data['update_time'] = date('Y-m-d H:i:s');
            $data['status'] = 3;
            if($status == -2){
                $field = 'name,type,region,region_name,positions,position_type,location_name,deadline,content,
                founder,create_time,update_time,status';
            }
            if($status == -3){
                $data['reason'] = '';
                $field = 'name,type,region,region_name,positions,position_type,location_name,deadline,content,
                founder,create_time,update_time,status,reason';
            }
            $dbRes = Db::table('b_task')->field($field)->update($data);
            if (!empty($images)) {
                $haveCount = Db::table('b_task_image')->where('tid',$data['id'])->count('*');
                if ($haveCount + count($images) > 6) return Errors::IMAGE_COUNT_ERROR;
                foreach ($images as $image) {
                    $info = $image->move(Errors::FILE_ROOT_PATH. DS. 'task');
                    if($info){
                        // 成功上传后 获取上传信息
                        $name = 'file'.DS .'task'. DS .$info->getSaveName();
                        $record = [
                            'tid' => $data['id'],
                            'path' => $name,
                            'use' => 1
                        ];
                        // 保存
                        $b = Db::table('b_task_image')->insert($record);
                        if ($b < 1) return Errors::IMAGES_INSERT_ERROR;
                    }
                }
            }
            $del=Db::table('b_task_assigner')->where("tid",$data['id'])->delete();
            if(empty($del)) {
                Db::rollback();
                return Errors::UPDATE_ERROR;
            }
            foreach ($assigners as $a) {
                $r = Db::table('b_task_assigner')->insert(['tid' => $data['id'], 'uid' => $a, 'state' => 1, 'status' => 2]);
                if ($r <= 0){
                    Db::rollback();
                    return Errors::ADD_ERROR;
                }
            }
            Db::commit();
            if (!empty($paths)) foreach ($paths as $path) Helper::deleteFile($path['path']);
            return $dbRes == 1 ? [true , $dbRes] : Errors::UPDATE_ERROR; 
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    static function queryPeople($id)
    {
        try {
            $task = Db::table('b_task')->where('id', $id)->field('founder, recevier')->find();
            if (empty($task)) return Errors::DATA_NOT_FIND;
            return $task;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    static function queryImageCount($id, $use)
    {
        try {
            return Db::table('b_task_image')
                ->where('tid', $id)
                ->where('use', $use)
                ->count('*');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    static function deleteImage($tid, $use, $id)
    {
        try {
            $dbRes = Db::table('b_task_image')
                ->where('tid', $tid)
                ->where('use', $use)
                ->where('id', $id)
                ->delete();
            return $dbRes;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    static function saveImage($path)
    {
        try {
            $data = [
                'path' => $path,
            ];
            $image = Db::table('b_task_image')->insertGetId($data);
            return $image > 0 ? [$image] : Errors::INSERT_ERROR;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    //查询指派人
    static function findAssgin($data,$user)
    {
        try {
            $query = Db::table("u_user")->alias('u');
            if(Helper::lsWhere($data, 'region')) {
                $query = $query->join('c_region r', 'r.id = u.region', 'left')
                    ->where('u.status','0')->where('u.examine','1')
                    ->whereLike('u.region', $data['region'] . '%')->field('u.uid,u.name')->select();
            }
            if(Helper::lsWhere($data, 'assigner')) {
                $query = $query->where('u.status', '0')
                    ->where('u.examine', '1')
                    ->whereLike('u.name', "%" . $data['assigner'] . "%")
                    ->whereLike('u.region', $user[1]['s_region'] . "%")//改
                    ->field('u.uid,u.name')->select();
            }
            //return [true, $query];
            return !empty($query) ? [true, $query] : Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //删除指派人--app端
    static function deleteAssgin($data){
        try{
            Db::startTrans();
            $dbRes = Db::table('b_task_assigner')->where('tid',$data['tid'])->where('uid',$data['uid'])->delete();
            if($dbRes<=0) return Errors::DELETE_ERROR;
            Db::commit();
            return [true,$dbRes];
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }
    

	// 查询是否任务发布人
    static function queryAssgin($id)
    {
        try {
            $dbRes = Db::table('b_task')->where('id', $id)
                ->field('founder')->find();
            return !empty($dbRes) ? [true ,$dbRes ] :Errors::DATA_NOT_FIND;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }
	
    static function taskOverview($data, $uid){
        try {
            $query = Db::table("b_task")->alias('t');
            if (Helper::lsWhere($data, 'region')) $query->whereLike('t.region', '%'.$data['region'].'%');
            if (Helper::lsWhere($data, 'type')) $query->where('t.type', $data['type']);
            if (Helper::lsWhere($data, 'status')) $query->where('t.status', $data['status']);
            if (Helper::lsWhere($data, 'name')) $query->whereLike('t.name', '%'.$data['name'].'%');
            //判断是否是管理员
            $auth = Helper::auth([1]);
            if (!is_array($auth)) $query->join('b_task_assigner ta', "t.id = ta.tid")->where('ta.uid', $uid);
            $query->join('u_user u', 'u.uid = t.recevier', 'left');
            $query->join('u_user u1', 'u1.uid = t.founder', 'left');
            if($data['app'] == 1){
                $a ='t.id, t.type, t.status, u1.name founder_name,u1.tel, t.create_time, t.positions,t.location_name';
                $b = '100';
            }else{
                $a = 't.id, t.name, t.type, t.status, u1.name founder_name,u1.tel, u.name recevier_name, t.create_time, t.deadline, t.positions,t.location_name';
                $b = '100';
            }
            $dataRes = $query->field($a)->order('t.update_time', 'desc')->limit($b)->select();
            $result = Helper::transFormation($dataRes);
            return empty($result) ? Errors::DATA_NOT_FIND : [true, $result];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    static function listPort(){
        try{
            $dataRes['type'] =  Db::table('b_task')->field('type')->group('type')->select();
            $dataRes['position_type']=  Db::table('b_task')->field('position_type')->group('position_type')->select();
            return empty($dataRes) ? Errors::DATA_NOT_FIND : [true, $dataRes];
        }catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 数据导出
    static function exportls($data,$field,$img,$condition){
        try {
            $where = '1 = 1 ';
            $order = 'create_time desc';
            $field.=',id';
            if (!empty($condition['region'])) $where.=" and region like '%".$condition['region']."%'";
            if (!empty($condition['type'])) $where.=" and type ='".$condition['type']."'";
            if (!empty($condition['status'])) $where.=" and status ='".$condition['status']."'";
            $dataRes = Db::table('b_task')->field($field)->where($where)->order($order)->select();
            // 获取图片
            if ($img){
                foreach ($dataRes as $key => $val) {
                    $dataRes[$key]['img'] = Db::table('b_task_image')->where('tid', $val['id'])->field('id,tid,use',true)->select();
                }
            }
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

}