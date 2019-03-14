<?php
/**
 * Created by PhpStorm.
 * User: XieLe
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\validate;
use think\Validate;


class Implement  extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'name' => 'require|max:20',
        'unit' => 'require|max:20',
        'person' => 'require|max:10',
        'nature' => 'require|in:1,2,3',
        'region' => 'require|max:20|region',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:1,2,3',
        'location_name' => 'require',
        'middle_price' => 'float|max:9',
        'province_price' => 'float|max:9',
        'place_price' => 'float|max:9',
        'sum_price' => 'float|max:9',
        'plan' => 'max:250',
        'content' => 'max:250',
        'note' => 'max:250',
        'ids' => 'require'
    ];

    protected $message = [
        'name.require' => '项目名称必填',
        'name.max' => '项目名称长度不能超过20位字符',
        'unit.require' => '项目实施单位必填',
        'unit.max' => '项目实施单位长度不能超过20位字符',
        'person.require' => '负责人必填',
        'person.max' => '负责人长度不能超过10位字符',
        'nature.require' => '项目性质必填',
        'nature.in' => '项目性质选择范围错误',
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20位字符',
        'positions.require' => '地图位置必填',
        'positions.positionReg' => '地图位置格式错误',
        'position_type.require' => '地图位置类型必填',
        'position_type.in' => '地图位置类型错误',
        'location_name.require' => '地图位置名称必填',
        'middle_price.float' => '中央投资数据格式错误',
        'middle_price.max' => '中央投资长度不能超过9位字符',
        'province_price.float' => '省投资数据格式错误',
        'province_price.max' => '省投资长度不能超过9位字符',
        'place_price.float' => '地方投资数据格式错误',
        'place_price.max' => '地方投资长度不能超过9位字符',
        'sum_price.float' => '总投资数据格式错误',
        'sum_price.max' => '总投资长度不能超过9位字符',
        'plan.max' => '项目建设进度长度不能超过255位字符',
        'content.max' => '项目建设内容长度不能超过9位字符',
        'note.max' => '备注长度不能超过9个字符'
    ];

    protected $scene = [
        'add' => [
            'name',
            'unit',
            'person',
            'nature',
            'region',
            'positions',
            'position_type',
            'location_name',
            'middle_price',
            'province_price',
            'place_price',
            'sum_price',
            'plan',
            'content',
            'note',
        ],
        'query' => [
            'id',
        ],
        'ids' => [
            'ids',
        ],
        'edit' => [
            'id',
            'name',
            'unit',
            'person',
            'nature',
            'region',
            'positions',
            'position_type',
            'location_name',
            'middle_price',
            'province_price',
            'place_price',
            'sum_price',
            'plan',
            'content',
            'note',
        ],
    ];
}