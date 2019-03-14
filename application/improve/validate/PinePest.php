<?php
/**
 * Created by sevenlong.
 * User: Administrator
 * Date: 2017/12/13
 * Time: 11:34
 */

namespace app\improve\validate;

use think\Validate;


class PinePest extends BaseValidate
{
    protected $rule = [
        'id' => 'require|number',
        'region' => 'require|max:20|region',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'location_name' => 'require',
        'main_tree' => 'require|max:20',
        'forest_composition' => 'require|max:20',
        'number_of_plants' => 'require',
        'forest_age'  => 'require',
        'dbh' => 'require',
        'tree_height' => 'require',
        'accumulative_volume' => 'require',
        'slope_direction' => 'require|in:1,2,3',
        'canopy_density'  => 'require|between:0,1',
        'vegetation_type'  => 'require',
        'dead_pine_num'  => 'require',
        'dead_rate'  => 'require',
        'dead_area' => 'require',
        'dead_reason'  => 'require',
        'sample_number'  => 'max:50',
        'sampling_part' => 'max:50',
        'results' => 'max:50',
        'illness_number'  => 'max:50',
        'disease_rate' => 'max:50',
        'disease_area' => 'max:50',
        'ids' => 'require',
        'record' => 'array',
    ];

    protected $message = [
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20个字符',
        'positions.require' => '地图位置必填',
        'positions.positionReg' => '地图位置格式错误',
        'position_type.require' => '地图位置类型必填',
        'position_type.in' => '地图位置类型错误',
        'location_name.require' => '地图位置名称必填',
        'location_name.max' => '地图位置类型名称长度不能超过25位字符',
        'main_tree.require' => '主要树种必填',
        'main_tree.max' => '主要树种字段长度不能超过20个字符',
        'forest_composition.require' => '林木组成必填',
        'forest_composition.max' => '林木组成字段长度不能超过20个字符',
        'number_of_plants.require' => '每亩株数必填',
        'forest_age.require' => '树龄必填',
        'dbh.require' => '胸径必填',
        'tree_height.require' => '树高必填',
        'accumulative_volume.require' => '蓄积量必填',
        'slope_direction.require' => '坡向必填',
        'slope_direction.in' => '坡向选择范围错误',
        'canopy_density.require' => '郁闭度必填',
        'canopy_density.between' => '郁闭度必须在0到1之间',
        'vegetation_type.require' => '植被种类必填',
        'dead_pine_num.require' => '枯死株数必填',
        'dead_rate.require' => '枯死率必填',
        'dead_reason.require' => '枯死原因初步分析必填',
        'dead_area.require' => '枯死面积必填',
        'sample_number.max' => '小班号字段长度不能超过20个字符',
        'sampling_part.max' => '取样部位字段长度不能超过20个字符',
        'results.max' => '送检结果字段长度不能超过20个字符',
        'illness_number.max' => '感病株数字段长度不能超过20个字符',
        'disease_rate.max' => '感病率字段长度不能超过20个字符',
        'disease_area.max' => '感病面积字段长度不能超过20个字符',
        'ids.require' => 'ids不能为空',
        'record.array' => '上传记录数据格式错误',
    ];

    protected $scene = [
        'id'=>['id'],
        'ids'=>['ids'],
        'add' => [
            'region',
            'positions',
            'position_type',
            'location_name',
            'class_number',
            'forest_class_area',
            'forest_composition',
            'main_tree',
            'number_of_plants',
            'forest_age',
            'dbh',
            'tree_height',
            'accumulative_volume',
            'slope_direction',
            'canopy_density',
            'vegetation_type',
            'dead_pine_num',
            'dead_rate',
            'dead_area',
            'dead_reason',
            'sample_number',
            'sampling_part',
            'results',
            'illness_number',
            'disease_rate',
            'disease_area',
            'record'
        ],
        'edit' => [
            'id',
            'region',
            'positions',
            'position_type',
            'location_name',
            'class_number',
            'forest_class_area',
            'forest_composition',
            'main_tree',
            'number_of_plants',
            'forest_age',
            'dbh',
            'tree_height',
            'accumulative_volume',
            'slope_direction',
            'canopy_density',
            'vegetation_type',
            'dead_pine_num',
            'dead_rate',
            'dead_area',
            'dead_reason',
            'sample_number',
            'sampling_part',
            'results',
            'illness_number',
            'disease_rate',
            'disease_area',
            'record',
        ],
    ];

}