<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/10
 * Time: 10:40
 */

namespace app\improve\validate;
use think\Validate;


class Pesticide extends BaseValidate
{

    protected $rule = [
        'id' => 'require',
        'region' => 'require|max:20|region',
        'unit' => 'require|max:50',
        'naturals' => 'require|max:20|float',
        'biochemistry' => 'require|max:20|float',
        'chemistry' => 'require|max:20|float',
        'germ' => 'require|max:20|float',
        'years' => 'require|dateFormat:Y',
        'ids' => 'require',
    ];

    protected $message = [
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20个字符',
        'unit.require' => '行政单位必填',
        'unit.max' => '行政单位长度不能超过50个字符',
        'naturals.require' => '天敌生物必填',
        'naturals.max' => '天敌生物不能超过20个字符',
        'naturals.float' => '天敌生物数据格式错误',
        'biochemistry.require' => '生物化学农药必填',
        'biochemistry.max' => '生物化学农药长度不能超过20个字符',
        'biochemistry.float' => '生物化学农药数据格式错误',
        'chemistry.require' => '化学农药必填',
        'chemistry.max' => '化学农药长度不能超过20个字符',
        'chemistry.float' => '化学农药数据格式错误',
        'germ.require' => '微生物农药必填',
        'germ.max' => '微生物农药数据格式错误',
        'germ.float' => '微生物农药必须填数字',
        'years.require' => '年度必填',
        'years.dateFormat' => '年度日期格式错误'
    ];

    protected $scene = [
        'add' => [
            'region',
            'unit',
            'naturals',
            'biochemistry',
            'chemistry',
            'germ',
            'years'
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
            'unit',
            'naturals',
            'biochemistry',
            'chemistry',
            'germ',
            'years'
        ],
    ];
}