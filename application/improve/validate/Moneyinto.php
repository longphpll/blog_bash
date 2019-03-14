<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/10
 * Time: 10:40
 */

namespace app\improve\validate;
use think\Validate;


class Moneyinto extends BaseValidate
{

    protected $rule = [
        'id' => 'require',
        'region' => 'require|max:20|region',
        'unit' => 'require|max:50',
        'financial' => 'require|max:20|float',
        'society' => 'require|max:20|float',
        'budget' => 'require|max:20|float',
        'years' => 'require|dateFormat:Y',
        'ids' => 'require'
    ];

    protected $message = [
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20个字符',
        'unit.require' => '行政单位必填',
        'unit.max' => '行政单位长度不能超过50个字符',
        'financial.require' => '财政资金必填',
        'financial.max' => '财政资金长度不能超过20个字符',
        'financial.float' => '财政资金数据格式错误',
        'society.require' => '社会投入必填',
        'society.max' => '社会投入长度不能超过20个字符',
        'society.float' => '社会投入数据格式错误',
        'budget.require' => '预算内投入必填',
        'budget.max' => '预算内投入长度不能超过20个字符',
        'budget.float' => '预算内投入数据格式错误',     
        'years.require' => '年度必填',
        'years.dateFormat' => '年度日期格式错误',
        'record.dateFormat' => '记录日期格式错误'
    ];

    protected $scene = [
        'add' => [
            'region',
            'unit',
            'financial',
            'society',
            'budget',
            'years',
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
            'financial',
            'society',
            'budget',
            'years',
        ],
    ];
}