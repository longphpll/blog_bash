<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/10
 * Time: 10:40
 */

namespace app\improve\validate;
use think\Validate;


class QuarantineInspector extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'type' => 'require|in:1,2',
        'name' => 'require|max:10',
        'region' => 'require|max:20|region',
        'unit' => 'require|max:20',
        'sex' => 'require|in:1,2',
        'birthday' => 'require|dateFormat:Y',
        'job' => 'require|max:20',
        'technical' => 'require|max:20',
        'education' => 'require|max:20',
        'academy' => 'require|max:20',
        'tel' => 'require|tel|max:11',
        'entryday' => 'require|max:2',
        'guard' => 'require|in:1,2',
        'ids' => 'require',
    ];

    protected $message = [
        'type.require' => '检疫员类型必填',
        'type.in' => '检疫员类型选择范围错误',
        'name.require' => '姓名必填',
        'name.max' => '姓名长度最大不能超过10个字符',
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20个字符',
        'unit.require' => '所在单位必填',
        'unit.max' => '所在单位字段长度不能超过20个字符',
        'sex.require' => '性别必填',
        'sex.in' => '性别选择范围错误',
        'birthday.require' => '出生年份必填',
        'birthday.dateFormat' => '出生年份格式错误',
        'job.require' => '岗位必填',
        'job.max' => '岗位字段长度不能超过20个字符',
        'technical.require' => '职称必填',
        'technical.max' => '职称长度不能超过20个字符',
        'education.require' => '学历必填',
        'education.max' => '学历长度不能超过20个字符',
        'academy.require' => '毕业院校必填',
        'academy.max' => '毕业院校长度不能超过20个字符',
        'tel.require' => '手机号必填',
        'tel.max' => '手机号长度不能超过11个字符',
        'entryday.max' => '从事时长必填',
        'entryday.max' => '从事时长长度不能超过2个字符',
        'guard.require' => '在岗情况必填',
        'guard.in' => '在岗情况选择范围错误',

    ];

    protected $scene = [
        'add' => [
            'type',
            'name',
            'region',
            'unit',
            'sex',
            'birthday',
            'job',
            'technical',
            'education',
            'academy',
            'tel',
            'entryday',
            'guard',
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
            'region',
            'unit',
            'sex',
            'birthday',
            'job',
            'technical',
            'education',
            'academy',
            'representative_area',
            'tel',
            'entryday',
            'guard',
        ],
    ];
}