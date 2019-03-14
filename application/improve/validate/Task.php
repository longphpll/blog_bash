<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

class Task extends BaseValidate
{
    protected $rule = [
        'name' => 'require|max:32',
        'type' => 'require|in:1,2',
        'region' => 'require|max:20',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'location_name' => 'require',
        'deadline' =>'require|dateFormat:Y-m-d H:i:s',
        'content' =>'require|max:255',
        'result' => 'require|max:255',
        'assigner' => 'require|array',
        'id' =>'require|number',
        'ids' => 'require|array',
        'image_id' =>'require|number',
        'image_use' =>'require|in:1,2',
        'images' => 'array|max:6',
        'reason'=>'require'
    ];

     protected $message = [
        'name.require' => '任务名称必填',
        'name.max' => '任务名称不能超过32位字符',
        'type.require' => '任务类型必填',
        'type.in' => '任务类型选择范围错误',   
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20位字符',
        'positions.require' => '地图位置必填',
        'positions.positionReg' => '地图位置格式错误',
        'position_type.require' => '地图位置类型必填',
        'position_type.in' => '地图位置类型错误',
        'position_type.require' => '地图位置类型必填',
        'location_name.require' => '地图位置名称必填',
        'deadline.dateFormat' => '任务截止时间格式错误',
        'content.require' => '任务内容必填',
        'content.max' => '任务内容长度不能超过255位字符',
        'result.require' => '任务反馈结果必填',
        'result.max' => '任务反馈结果长度不能超过255位字符',
        'assigner.require' => '指派人不能为空',
        'assigner.array' => '指派人数据格式错误',
        'reason.require'=>'拒绝理由必填'

    ];

    protected $scene = [
        'add'  =>  ['name','type','region','positions','position_type','location_name','deadline','content','assigner'],
        'id'=>['id'],
        'refuse'=>['id','reason'],
        'ids'=>['ids'],
        'edit'=>['id','danger_attributes','harm_part','introduce','attach','images'],
        'republishs' => ['id','name','type','region','positions','positions_type','location_name','deadline','content','assigner'],
        'deleteAssgin' => ['tid','uid'],
        'deleteImage' => ['id', 'image_use','image_id'],
    ];
}