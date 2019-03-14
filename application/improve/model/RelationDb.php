<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2018/7/3
 * Time: 14:20
 */

namespace app\improve\model;

use app\improve\controller\Errors;
use think\Db;
use app\improve\model\BaseDb;
use app\improve\controller\Helper;
use app\improve\controller\UploadHelper;
use tool\Communal;
use tool\Error;


class RelationDb  extends BaseDb
{
    /** 物种数据库-新增模型(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：将 DB_name b_species 修改为 improve_species
     *          将 DB_name b_species_type 修改为 improve_species_type
     *          将 DB_name b_parts 修改为 improve_parts
     *          将 DB_name b_plant 修改为 improve_plant
     *          将 DB_name b_species_relation 修改为 improve_species_relation
     *          将 DB_name b_species_images 修改为 improve_species_images
    */
     static function add($data,$images){
        Db::startTrans();
        try {
            $data['create_time'] =  date('Y-m-d H:i:s');
            $data['update_time'] = $data['create_time'];
            $res = Db::table('improve_species')->whereLike('cn_name',$data['cn_name'].'%')->field('id')->where('status',1)->find();
            if (!empty($res)) return Error::error('该生物信息已录入');
            if (!empty($data['attribute'])) {
                $attribute = Db::table('improve_species_type')->where('id',$data['attribute'])->where('type',0)->field('name')->find();
                $data['attribute_name'] = $attribute['name'];
            }
            if (!empty($data['genre'])) {
                $genre = Db::table('improve_species_type')->where('id',$data['genre'])->where('type',2)->field('name')->find();
                if (!empty($genre['name'])) {
                    switch ($genre['name'])
                    {
                        case "虫害":$data['genre_type'] = 1;
                            break;
                        case "病害":$data['genre_type'] = 2;
                            break;
                        case "有害植物":$data['genre_type'] = 3;
                            break;
                    }
                }
                $data['genre_name'] = $genre['name'];
            }
            if ($data['attribute'] == 1){
                if (!empty($data['types'])) {
                    $type = Db::table('improve_species_type')->whereIn('id',$data['types'])->where('type',1)->field('name')->select();
                    $types = '';
                    foreach ($type as $key => $value) {
                        $types = $types .','. $value['name'];
                    }
                    $types = substr($types, 1);
                    $data['types_name'] = $types;
                }
            }
            if (Helper::lsWhere($data, 'images')){
                $images = $data['images'];
            }
            unset($data['images']);
            $data['status'] = 1;
            $data['local'] = 1;
            unset($data['images']);
            unset($data['id']);
            //Db::startTrans();
            if (!empty($data['harm_part'])) {
                $names = Db::table("improve_parts")
                ->whereIn('type',$data['harm_part'][0])
                ->field('name')
                ->select();
                $molds = '';
                foreach ($names as $key => $value) {
                    $molds = $molds .','. $value['name'];
                }
                $molds = substr($molds, 1);
                $data['harm_part'] = $data['harm_part'][0];
                $data['harm_part_name'] = $molds;
            }
            $dbRes = Db::table('improve_species')->strict(false)->insertGetId($data);
            if ($dbRes <= 0) return Error::error('添加错误');
            if (!empty($data['plants'])) {
                $ids = explode(',',$data['plants'][0]);
                $plant_res = Db::table('improve_plant')->whereIn('id',$data['plants'][0])->field('cn_name')->select();
                $plants_names = '';
                foreach ($plant_res as $key => $value) {
                    $plants_names = $plants_names .','. $value['cn_name'];
                }
                $plants_names = substr($plants_names, 1);
                foreach ($ids as $key => $val) {
                    $plant_name = BaseDb::plant($val);
                    $eng_name = BaseDb::ename($val);
                    $record = [
                        "pest_id" => $dbRes,
                        "plant_id" => $val,
                        'plant_name' => $plant_name,
                        'eng_name' => $eng_name,
                        'status' => 1,
                        'create_time' => $data['update_time']
                    ];
                    $result = Db::table('improve_species_relation')->strict(false)->insertGetId($record);
                }
                $plant_res = Db::table('improve_species')->where('id',$dbRes)->update(['plant_name' => $plants_names, 'plant_ids' => $data['plants'][0]]);
            }
            //图片上传
            if (!empty($images)) {
                if (count($images) > 6)  return Error::error('图片不能超过六张');
                foreach ($images as $image) {
                    $info = $image->move(Error::FILE_ROOT_PATH. DS. 'species');//将Errors修改为Error
                    if($info){
                        // 成功上传后 获取上传信息
                        $name = 'file'.DS .'species'. DS .$info->getSaveName();
                        $record = [
                            's_id' => $dbRes,
                            'path' => $name,
                            'type' => 2,
                            'status' => 1,
                            'create_time' => $data['create_time']
                        ];
                        // 保存
                        $b = Db::table('improve_species_images')->insert($record);
                        if ($b < 1) return Error::error('图片添加失败');
                    }
                }
            }
            Db::commit();
            return Communal::success('添加成功');
        } catch (\Exception $e) {
            Db::rollback();
            return Error::error($e->getMessage());
        }
    }

    /** 物种数据库-编辑模型()
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：
    */
     static function edit($data,$images){
        try {
            $path = [];
            $data['update_time'] =  date('Y-m-d H:i:s');
            unset($data['images']);
            Db::startTrans();
            //删除图片处理
            if(!empty($data['del_images'])){
                $delRes = Db::table('b_species_images')->whereIn('id',$data['del_images'])->update(['status' => 2]);
            }
            unset($data['del_images']);
            if (!empty($data['attribute'])) {
                $attribute = Db::table('b_species_type')->where('id',$data['attribute'])->where('type',0)->field('name')->find();
                $data['attribute_name'] = $attribute['name'];
            }
            if (!empty($data['genre'])) {
                $genre = Db::table('b_species_type')->where('id',$data['genre'])->where('type',2)->field('name')->find();
                if (!empty($genre['name'])) {
                    switch ($genre['name'])
                    {
                        case "虫害":$data['genre_type'] = 1;
                            break;
                        case "病害":$data['genre_type'] = 2;
                            break;
                        case "有害植物":$data['genre_type'] = 3;
                            break;
                    }
                }
                $data['genre_name'] = $genre['name'];
            }
            if ($data['attribute'] == 1){
                if (!empty($data['types'])) {
                    $type = Db::table('b_species_type')->whereIn('id',$data['types'])->field('name')->select();
                    $types = '';
                    foreach ($type as $key => $value) {
                        $types = $types .','. $value['name'];
                    }
                    $types = substr($types, 1);
                    $data['types_name'] = $types;
                }
            }
            if ($data['attribute'] == 2){
                    $data['types_name'] = '';
            }
            if(!empty($data['plants'])){
                $ids = explode(',',$data['plants'][0]);
                $plants = Db::table('b_species_relation')->field('pest_id')->where('pest_id', $data['id'])->delete();
                $plant_res = Db::table('b_plant')->whereIn('id',$data['plants'][0])->field('cn_name')->select();
                $plants_names = '';
                foreach ($plant_res as $key => $value) {
                    $plants_names = $plants_names .','. $value['cn_name'];
                }
                $plants_names = substr($plants_names, 1);
                foreach ($ids as $key => $val) {
                    $plant_name = BaseDb::plant($val);
                    $eng_name = BaseDb::ename($val);
                    $record = [
                        "pest_id" => $data['id'],
                        "plant_id" => $val,
                        'plant_name' => $plant_name,
                        'eng_name' => $eng_name,
                        'status' => 1,
                        'create_time' => $data['update_time']
                    ];
                    $result = Db::table('b_species_relation')->insertGetId($record);
                }
                $plant_res = Db::table('b_species')->where('id',$data['id'])->update(['plant_name' => $plants_names, 'plant_ids' => $data['plants'][0]]);
            }
            unset($data['plants']);
            //有害生物类型
            if (!empty($data['harm_part'])) {
                $harm_part = explode(',',$data['harm_part'][0]);
                $names = Db::table("b_parts")
                ->whereIn('type',$harm_part)
                ->field('name')
                ->select();
                $molds = '';
                foreach ($names as $key => $value) {
                    $molds = $molds .','. $value['name'];
                }
                $molds = substr($molds, 1);
                $data['harm_part_name'] = $molds;
                $data['harm_part'] = $data['harm_part'][0];
            }
            $field = 'cn_name,alias,types,types_name,eng_name,attribute,attribute_name,genre,genre_type,genre_name,section,order,genus,harm_part,harm_part_name,prevention_way,living_habits,
            shape_character,region,update_time';
            $dbRes = Db::table('b_species')->field($field)->update($data);
            if($dbRes != 1) return  Errors::UPDATE_ERROR; 
            if (!empty($images)) {
                if (count($images) > 6)  return Errors::IMAGE_COUNT_ERROR;
                foreach ($images as $image) {
                    $info = $image->move(Errors::FILE_ROOT_PATH. DS. 'species');
                    if($info){
                        // 成功上传后 获取上传信息
                        $name = 'file'.DS .'species'. DS .$info->getSaveName();
                        $record = [
                            's_id' => $data['id'],
                            'path' => $name,
                            'type' => 2,
                            'status' => 1,
                            'create_time' => $data['update_time']
                        ];
                        // 保存
                        $b = Db::table('b_species_images')->insert($record);
                        if ($b < 1) return Errors::IMAGES_INSERT_ERROR;
                    }
                }
            }

            Db::commit();
            return [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 导入
     static function insert_add($data){
        try {
            unset($data['id']);
            Db::startTrans();
            $res = Db::table('b_species')->where('cn_name',$data['cn_name'])->field('id')->find();
            if (!empty($res)) return Errors::HAS_EXIT_PEST;
            //有害生物类型
            $dbRes = Db::table('b_species')->strict(false)->insertGetId($data);
            if ($dbRes <= 0) return Errors::ADD_ERROR;
            //寄主数据
            if ($data['attribute'] == '1') {
                if (!empty($data['plant_ids'])) {
                    $ids = explode(',',$data['plant_ids']);
                    foreach ($ids as $key => $val) {
                        $plant_name = BaseDb::plant($val);
                        $eng_name = BaseDb::ename($val);
                        $record = [
                            "pest_id" => $dbRes,
                            "plant_id" => $val,
                            'plant_name' => $plant_name,
                            'eng_name' => $eng_name,
                            'status' => 1,
                            'create_time' => $data['create_time']
                        ];
                        $result = Db::table('b_species_relation')->strict(false)->insertGetId($record);
                    }
                }
            }
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    /** 物种数据库-生物别称(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：将 DB_name b_species 修改为 improve_species
    */
     static function ls($data){
        try {
            $where = 'status = 1';
            $field = 'id,cn_name,eng_name,attribute_name,genre_name,order,section,genus,plant_name,local,create_time';
            $order = 'create_time desc';
            if(!empty($data['name'])) $where.=" and cn_name like '%".$data['name']."%'";
            if(!empty($data['type'])) {
                $where.=" and (attribute = ".$data['type']." or genre = ".$data['type']." or ".$data['type']." in (types))";
            }
            $dataRes = Db::table('improve_species')->field($field)->where($where)->order($order)->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            $result = Helper::transFormation($dataRes);
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

     static function appls($data){
        try {
            $where = 'status = 1';
            $order = 'update_time desc';
            if (!empty($data['name'])) $where.=" and cn_name like". "'%".$data['name']."%'"."or eng_name like"."'%".$data['name']."%'";
            if(!empty($data['type'])) {
                $where.=" and (attribute = ".$data['type']." or genre = ".$data['type']." or ".$data['type']." in (types))";
            }else{
                $where.=" and 3 in (types)";
            }
            if(!empty($data['type'])) {
                $where.=" and (attribute = ".$data['type']." or genre = ".$data['type']." or ".$data['type']." in (types))";
            }
            $field = 'id,cn_name,eng_name,attribute_name,types_name,genre_name';
            $dataRes = Db::table('b_species')->field($field)->where($where)->order($order)->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            $result = Helper::transFormation($dataRes);
            return [true, $result];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //本地化列表
     static function localList($data){
        try {
            $where = 'local = 2 and status = 1';
            $field = 'id,cn_name,eng_name,attribute_name,genre_name,order,section,genus,plant_name,create_time';
            $order = 'create_time desc';
            if(!empty($data['type'])) $where.=" and attribute = ".$data['type'];
            if(!empty($data['name'])) $where.=" and cn_name like '%".$data['name']."%'";
            $dataRes = Db::table('b_species')->field($field)->where($where)->order($order)->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            $result = Helper::transFormation($dataRes);
            return [true, $result];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

     static function query($id){
        try {
            $where = 'status = 1';
            $field = 'id, attribute,attribute_name, cn_name, alias, eng_name, genre, genre_name, order, section, genus, types, types_name, harm_part, harm_part_name, plant_ids, plant_name, prevention_way, living_habits, shape_character, region';
            $dbRes = Db::table('b_species')->field($field)->where($where)->where('id', $id)->find();
            if(empty($dbRes)) return Errors::DATA_NOT_FIND;
            $dbRes['images'] = Db::table('b_species_images')->where('s_id', $id)->where('type',2)->where($where)->field('id,path')->select(); 
            if (empty($dbRes['harm_part'])){
                $dbRes['harm_part'] = [];  
            }else{
                $dbRes['harm_part'] = explode(',',$dbRes['harm_part']);  
            }
            if (empty($dbRes['plant_ids'])){
                $dbRes['plants'] = [];  
            }else{
                $dbRes['plants'] = explode(',',$dbRes['plant_ids']); 
            }
            unset($dbRes['plant_ids']);    
            $result = Helper::transFormation($dbRes);
            return [true, $result];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //本地化操作
     static function local($ids){
        try {
            Db::startTrans();
            $result = Db::table('b_species')->where('status',1)->whereIn('id',$ids)->update(['local'=> 2]);
            if (empty($result)) return Errors::LOCAL_ERROR;
            Db::commit();
            return [true , $result];
        } catch (Exception $e) {
            Db::rollback();
            return Errors::Error($e->getMessage());
        }
    }

    //生物名称查询
    /** 物种数据库-生物别称(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：将 DB_name b_species 修改为 improve_species
    */
     static function findName($data){
        try {
            $dataRes = Db::table('improve_species')
                ->field('cn_name')
                ->where('status',1)
                ->whereLike('cn_name', $data['name'].'%')
                ->find();
            if(!empty($dataRes)) return Error::error('该生物信息已录入');
            if(empty($dataRes)) return Communal::success(1);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    //删除
     static function deleteChecked($ids){
        try {
            $dataRes = Db::table('b_species')->whereIn('id', $ids)->update(['status'=> 2]);
            $res = Db::table('b_species_relation')->whereIn('pest_id', $ids)->update(['status'=> 2]);
            return empty($dataRes) ? Errors::DELETE_ERROR : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //本地化记录删除
     static function localDelete($ids){
        try {
            $dataRes = Db::table('b_species')->where('local',2)->whereIn('id', $ids)->where('status',1)->update(['local'=> 1]);
            return empty($dataRes) ? Errors::DELETE_ERROR : [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //根据有害生物id查询其寄生植物--APP端
     static function relevance($data){
        try {
            $where = 'bs.status = 1 and bf.status = 1 and bf.local = 2';
            $field = 'bs.plant_id value,bs.plant_name label,bs.eng_name';
            $order = 'bs.create_time desc';
            if (!empty($data['name'])) $where.=" and bs.plant_name like". "'%".$data['name']."%'"."or eng_name like"."'%".$data['name']."%'";
            $dbRes = Db::table('b_species_relation')->alias('bs')->join('b_species bf','bs.pest_id = bf.id')
                ->field($field)
                ->where($where)
                ->where('bs.pest_id', $data['id'])
                ->paginate($data['per_page'],false,['page' => $data['current_page']])->toArray();
            return [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //根据有害生物id查询其寄生植物--web端
     static function plantWeb($data){
        try {
            $where = 'bs.status = 1 and bf.status = 1 and bf.local = 2';
            $field = 'bs.plant_id value,bs.plant_name label';
            $order = 'bs.create_time desc';
            if (!empty($data['name'])) $where.=" and bs.plant_name like". "'%".$data['name']."%'";
            $dbRes = Db::table('b_species_relation')->alias('bs')->join('b_species bf','bs.pest_id = bf.id')
                ->field($field)
                ->where($where)
                ->where('bs.pest_id', $data['id'])
                ->group('value')
                ->select();
            return empty($dbRes) ? Errors::DATA_NOT_FIND : [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    //生物别称
    /** 物种数据库-生物别称(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：将 DB_name b_species 修改为 improve_species
    */
     static function biologicalName($data){
        try {
            $where = 'status = 1';
            if(!empty($data['name'])) $where.=" and cn_name like '%".$data['name']."%'";
            if (!empty($data['type'])) $where.=" and attribute = ".$data['type'];
            $field = 'id,cn_name';
            $dataRes = Db::table('improve_species')->field($field)
                ->where($where)
                ->group('cn_name')
                ->order('id')
                ->select();
            $result = Helper::transFormation($dataRes);
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    //本地化列表 生物别称
    /** 物种数据库-生物别称本地(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：将 DB_name b_species 修改为 improve_species
    */
     static function biologicalNameLocal($data){
        try {
            $where = 'local = 2 and status = 1';
            if(!empty($data['name'])) $where.=" and cn_name like '%".$data['name']."%'";
            if (!empty($data['type'])) $where.=" and attribute = ".$data['type'];
            $field = 'id ,cn_name';
            $dataRes = Db::table('improve_species')
                ->field($field)
                ->where($where)
                ->group('cn_name')
                ->order('id')
                ->select();
            $result = Helper::transFormation($dataRes);
            return Communal::successData($result);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    // 一级分类
    /** 物种数据库-一级分类(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：将 DB_name b_species_type 修改为 improve_species_type
    */
     static function firstLevel($data){
        try {
            $where = '1 = 1';
            if(!empty($data['name'])) $where.=" and name like '%".$data['name']."%'";
            if(!empty($data['parentId'])) {
                $where.=" and parentId = ".$data['parentId'];
            }else{
                $where.=" and parentId = 0";
            }
            $field = 'id value,name label,type';
            $dataRes = Db::table('improve_species_type')
                ->field($field)
                ->where($where)
                ->order('id')
                ->select();
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    /** 物种数据库-二级分类(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：将 DB_name b_species_type 修改为 improve_species_type
    */
     static function level($data){
        try {
            $where = '1 = 1';
            if(!empty($data['name'])) $where.=" and name like '%".$data['name']."%'";
            if(!empty($data['parentId'])) $where.=" and parentId = ".$data['parentId'];
            if(!empty($data['type'])) $where.=" and type = ".$data['type'];
            $field = 'id value,name label';
            $dataRes = Db::table('improve_species_type')
                ->field($field)
                ->where($where)
                ->order('id')
                ->select();
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    // 获取生物类型
    /** 物种数据库-获取生物类型(已改)
     * 修改人：余思渡
     * 修改时间：2019.03.11
     * 修改内容：将 DB_name b_species_type 修改为 improve_species_type
    */
    static function typeList(){
        try {
            $where = '1 = 1';
            if(!empty($data['name'])) $where.=" and name like '%".$data['name']."%'";
            $field = 'id value,name label';
            $dataRes = Db::table('improve_species_type')
                ->field($field)
                ->where($where)
                ->order('id')
                ->select();
            return Communal::successData($dataRes);
        } catch (\Exception $e) {
            return Error::error($e->getMessage());
        }
    }

    // 导入execl--危害部位查询
    static function part($data){
        try {
            $where = 'status = 1';
            $dbRes =  Db::table("b_parts")->whereIn('name',$data)->field('type')->select();
            return [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 导入execl--寄主查询
    static function plant($data){
        try {
            $dbRes =  Db::table("b_plant")->whereIn('cn_name',$data)->field('id')->select();
            return [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 导入execl--寄主查询
    static function plantName($name){
        try {
            $dbRes =  Db::table("b_plant")->where('cn_name',$name)->field('id')->find();
            return $dbRes;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 导入execl--录入寄主数据
    static function addPlant($name){
        try {
            $record = [
                'cn_name' => $name,
                'update_time' => date('Y-m-d H:i:s')
            ];
            $dbRes = Db::table('b_plant')->insert($record);
            return $dbRes;
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 导入execl--二级类型查询
    static function type($data){
        try {
            $dbRes =  Db::table("b_species_type")->whereIn('name',$data)->field('id')->select();
            return [true, $dbRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }

    // 数据导出
    static function exportls($data,$field,$img,$condition){
        try {
            $where = 'status = 1';
            $order = 'create_time desc';
            $field.=',id';
            if(!empty($condition['name'])) $where.=" and cn_name like '%".$condition['name']."%'";
            if(!empty($condition['type'])) $where.=" and attribute = ".$condition['type'];
            $dataRes = Db::table('b_species')->field($field)->where($where)->order($order)->select();
            // 获取图片
            if ($img){
                foreach ($dataRes as $key => $val) {
                    $dataRes[$key]['img'] = Db::table('b_species_images')->where('s_id', $val['id'])->where('status',1)->field('path')->select();
                }
            }
            return [true, $dataRes];
        } catch (Exception $e) {
            return Errors::Error($e->getMessage());
        }
    }


}