<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\validate;
use think\Validate;


class Personnel  extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'name' => 'require|max:10',
        'job' => 'require|max:20',
        'tel' => 'require|tel|max:11',
        'sex' => 'require|in:1,2',
        'birthday' => 'dateFormat:Y',
        'unit' => 'max:20',
        'technical' => 'max:20',
        'education' => 'max:20',
        'entryday' => 'max:2|number',
        'academy' => 'max:20',
        'guard' => 'require|in:1,2',
        'ids' => 'require'
    ];

    protected $message = [
        'name.require' => '姓名必填',
        'name.max' => '姓名长度不能超过10个字符',
        'job.require' => '岗位必填',
        'job.max' => '岗位长度不能超过20个字符',
        'tel.require' => '手机号必填',
        'tel.max' => '手机号长度不能超过11个字符',
        'sex.require' => '性别必填',
        'sex.in' => '性别选择范围错误',
        'birthday.dateFormat' => '出生年月格式错误',
        'unit.max' => '所在单位长度不能超过20个字符',
        'technical.max' => '职称长度不能超过20个字符',
        'education.max' => '学历长度不能超过20个字符',
        'entryday.max' => '从事森防时间长度不能超过2个字符',     
        'entryday.number' => '从事森防时间必须为数字',
        'academy.max' => '毕业院校长度不能超过20个字符',
        'guard.require' => '在岗情况必填',
        'guard.in' => '在岗情况选择范围错误',
    ];

    protected $scene = [
        'add' => [
            'name',
            'job',
            'tel',
            'sex',
            'birthday',
            'unit',
            'technical',
            'education',
            'academy',
            'entryday',
            'guard'
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
            'job',
            'tel',
            'sex',
            'birthday',
            'unit',
            'technical',
            'education',
            'academy',
            'entryday',
            'guard'
        ],
    ];
}