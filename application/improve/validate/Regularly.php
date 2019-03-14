<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 14:18
 */
namespace app\improve\validate;

class Regularly extends BaseValidate{
    protected $rule = [
        'id' => 'require|max:20|number',
        'region' => 'require|max:6',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'type' => 'require|in:1,2,3,4',
        'number' => 'require',
        'pests' => 'require|number',
        'plant' => 'require|number',
        'regularly_area' => 'require|float',
        'stand_area' => 'require|float',
        'stand_composition' => 'require|max:20',
        'forest_age' => 'require',
        'coverage' => 'require',
        'location_name' => 'require',
        'ids' => 'require'
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'id.number' => 'id只能为数字',
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过6个字符',
        'positions.require' => '地图位置必填',
        'positions.positionReg' => '地图位置格式错误',
        'position_type.require' => '地图位置类型必填',
        'position_type.in' => '地图位置类型错误',
        'type.require' => '有害生物类型必填',
        'type.in' => '有害生物类型范围选择错误',
        'number.require' => '固定标准地编号必填',
        'pests.require' => '有害生物种类必填',
        'pests.number' => '有害生物种类错误',
        'plant.require' => '寄主树种必填',
        'plant.number' => '寄主树种类型错误',
        'regularly_area.require' => '标准地面积必填',
        'regularly_area.float' => '标准地面积数据格式错误',
        'stand_area.require' => '林分面积必填',
        'stand_area.float' => '林分面积必须为数字',
        'stand_composition.require' => '林分组成必填',
        'forest_age.require' => '林龄必填',
        'coverage.require' => '植被覆盖度必填',
        'ids.require' => 'ids不能为空'
    ];

    protected $scene = [
        'add' => [
            'region',
            'positions',
            'position_type',
            'type',
            'number',
            'coverage',
            'forest_age',
            'stand_area',
            'plant',
            'pests',
            'regularly_area',
            'stand_composition',
            'location_name',
            ],
        'id' => ['id'],
        'edit' => [
            'id',
            'region',
            'positions',
            'position_type',
            'type',
            'number',
            'coverage',
            'forest_age',
            'stand_area',
            'plant',
            'pests',
            'regularly_area',
            'stand_composition',
            'location_name',
        ],
         'ids' => [ 'ids'],
    ];
}