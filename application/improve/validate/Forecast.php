<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\validate;
use think\Validate;


class Forecast  extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'region' => 'require|max:20|region',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'pest' => 'require|number|max:11',
        'generation' => 'require|in:1,2,3,4,5,6,7,8',
        'plant' => 'require|number|max:11',
        'parasitism_area' => 'require|float|max:8',
        'begin_time' => 'require|dateFormat:Y-m-d',
        'end_time' => 'require|dateFormat:Y-m-d|egt:begin_time',
        'disaster_area' => 'require|float|max:8',
        'happen_area' => 'float|max:10',
        'mild_area' => 'float|max:10',
        'moderate_area' => 'float|max:10',
        'severe_area' => 'float|max:10',
        'ids' => 'require|array'
    ];

    protected $message = [
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20个字符',
        'positions.require' => '地图位置必填',
        'positions.positionReg' => '地图位置格式错误',
        'position_type.require' => '地图位置类型必填',
        'position_type.in' => '地图位置类型错误',
        'pest_id.require' => '预测对象必填',
        'pest_id.number' => '预测对象格式错误',
        'generation.require'   => '世代必填',
        'generation.in'   => '世代选择范围错误',
        'plant_id.require'   => '寄主树种必填',
        'plant_id.number'   => '寄主树种格式错误',
        'parasitism_area.require'   => '寄主面积必填',
        'parasitism_area.float'   => '寄主面积数据格式错误',
        'parasitism_area.max'   => '寄主面积长度不能超过8位字符',  
        'begin_time.require' => '预测开始时间必填',
        'begin_time.dateFormat' => '预测开始时间格式错误',
        'end_time.require' => '预测结束时间必填',
        'end_time.dateFormat' => '预测结束时间格式错误',
        'end_time.egt' => '预测开始时间不能超过预测结束时间',
        'disaster_area.require'   => '预计成灾面积必填',
        'disaster_area.float'   => '预计成灾面积数据格式错误',
        'disaster_area.max'   => '预计成灾面积长度不能超过8位字符',
        'happen_area.float' => '预计发生面积数据格式错误',
        'happen_area.max' => '预计发生面积必须填',
        'mild_area.float' => '发生轻度面积数据格式错误',
        'mild_area.max' => '发生轻度面积长度不能超过10个字符',
        'moderate_area.float' => '发生中度面积数据格式错误',
        'moderate_area.max' => '发生中度面积长度不能超过10个字符',
        'severe_area.float' => '发生重度面积数据格式错误',
        'severe_area.max' => '发生重度面积长度不能超过10个字符'
    ];

    protected $scene = [
        'add' => [
            'region',
            'positions',
            'position_type',
            'pest_id',
            'generation',
            'plant_id',
            'parasitism_area',
            'begin_time',
            'end_time',
            'disaster_area',
            'happen_area',
            'mild_area',
            'moderate_area',
            'severe_area'
        ],
        'query' => [
            'id',
        ],
        'ids' => [
            'ids',
        ],
        'edit' => [
            'id',
            'region',
            'positions',
            'position_type',
            'pest_id',
            'generation',
            'plant_id',
            'parasitism_area',
            'begin_time',
            'end_time',
            'disaster_area',
            'happen_area',
            'mild_area',
            'moderate_area',
            'severe_area'
        ],
    ];
}