<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\validate;
use think\Validate;


class Item  extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'name' => 'require|max:20',
        'unit' => 'require|max:20',
        'person' => 'require|max:10',
        'nature' => 'require|in:1,2,3',
        'reply' => 'max:50',
        'begin_time' => 'require|dateFormat:Y-m-d',
        'end_time' => 'require|dateFormat:Y-m-d',
        'region' => 'require|max:20|region',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:1,2,3',
        'location_name' => 'require',
        'reply_time' => 'dateFormat:Y-m-d',
        'work' => 'max:10',
        'work_time' => 'dateFormat:Y-m-d',
        'middle_price' => 'float|max:9',
        'province_price' => 'float|max:9',
        'place_price' => 'float|max:9',
        'sum_price' => 'float|max:9',
        'content' => 'max:255',
        'note' => 'max:255',
        'ids' => 'require'
    ];

    protected $message = [
        'name.require' => '项目名称必填',
        'name.max' => '项目名称长度不能超过20个字符',
        'unit.require' => '项目建设单位必填',
        'unit.max' => '项目建设单位长度不能超过20个字符',
        'person.require' => '项目法人必填',
        'person.max' => '项目法人长度不能超过10个字符',
        'nature.require' => '项目性质必填',
        'nature.in' => '项目性质选择范围错误',
        'reply.max' => '项目立项批复长度不能超过50个字符',
        'begin_time.require' => '项目建设开始期限必填',
        'begin_time.dateFormat' => '项目建设开始期限格式错误',
        'end_time.require' => '项目建设结束期限必填',
        'end_time.dateFormat' => '项目建设结束期限格式错误',
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20个字符',
        'location_name.require' => '地图位置名称必填',
        'positions.require' => '地图位置必填',
        'positions.positionReg' => '地图位置格式错误',
        'position_type.require' => '地图位置类型必填',
        'position_type.in' => '地图位置类型错误',
        'reply_time.dateFormat' => '项目立项批复时间格式错误',
        'work.max' => '作业设计立项批复长度不能超过9个字符',
        'work_time.dateFormat' => '作业设计批复时间格式错误',
        'middle_price.float' => '中央投资数据格式错误',
        'middle_price.max' => '中央投资长度不能超过9个字符',
        'province_price.float' => '省投资数据格式错误',
        'province_price.max' => '省投资长度不能超过9个字符',
        'place_price.float' => '地方投资数据格式错误',
        'place_price.max' => '地方投资长度不能超过9个字符',
        'sum_price.float' => '总投资数据格式错误',
        'sum_price.max' => '总投资长度不能超过9个字符',
        'content.max' => '地方投资长度不能超过9个字符',
        'note.max' => '备注长度不能超过9个字符'
    ];

    protected $scene = [
        'add' => [
            'name',
            'unit',
            'person',
            'nature',
            'reply',
            'begin_time',
            'end_time',
            'region',
            'positions',
            'position_type',
            'location_name',
            'reply_time',
            'work',
            'work_time',
            'middle_price',
            'province_price',
            'place_price',
            'sum_price',
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
            'location_name',
            'reply',
            'begin_time',
            'end_time',
            'region',
            'positions',
            'position_type',
            'reply_time',
            'work',
            'work_time',
            'middle_price',
            'province_price',
            'place_price',
            'sum_price',
            'content',
            'note',
        ],
    ];
}