<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\validate;
use think\Validate;


class Emergency  extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'name' => 'require|max:6',
        'region' => 'require|max:20|region',
        'duty' => 'require|max:20',
        'eduty' => 'require|max:20',
        'tel' => 'require|tel|max:11',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'location_name' => 'require',
        'ename' => 'require|max:20',
        'level' => 'require|in:1,2,3',
        'begintime' => 'require|dateFormat:Y-m-d|<=:overtime',
        'overtime' => 'require|dateFormat:Y-m-d|>=:begintime',
        'beginunit' => 'require|max:20',
        'emeasure' => 'max:255',
        'elog' => 'max:255',
        'esummarize' => 'max:255',
        'ids' => 'require'
    ];

    protected $message  =   [
        'name.require' => '负责人必填',
        'name.max' => '负责人长度不能超过6位字符',
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20位字符',
        'duty.require' => '职务必填',
        'duty.max' => '职务长度不能超过20位字符',
        'tel.require' => '联系方式必填',
        'tel.max' => '手机号长度不能超过11位',
        'positions.require' => '地图位置必填',
        'positions.positionReg' => '地图位置格式错误',
        'position_type.require' => '地图位置类型必填',
        'position_type.in' => '地图位置类型错误',
        'location_name.require' => '地理位置名称必填',
        'ename.require' => '事件名称必填',
        'ename.max' => '事件名称长度不能超过20位字符',
        'level.require' => '灾害等级必填',
        'level.in' => '灾害等级选择范围错误',
        'begintime.require' => '事件启动时间必填',
        'begintime.dateFormat' => '事件启动时间格式错误',
        'overtime.require' => '事件结束时间必填',
        'overtime.dateFormat' => '事件结束时间格式错误',
        'beginunit.require'   => '启动单位不能为空',  
        'beginunit.max'   => '启动单位长度不能超过20位字符',  
        'emeasure.max'   => '应急处理措施长度不能超过255位字符',
        'elog.max'   => '应急日志长度不能超过255位字符',
        'esummarize.max' => '应急工作总结长度不能超过255位字符', 
    ];

    protected $scene = [
        'add' => [
            'username',
            'region',
            'eduty',
            'location_name',
            'tel',
            'positions',
            'position_type',
            'ename',
            'level',
            'begintime',
            'overtime',
            'beginunit',
            'emeasure',
            'elog',
            'esummarize'
        ],
        'query' => [
            'id',
        ],
        'ids' => [
            'ids',
        ],
        'edit' => [
            'id',
            'username',
            'region',
            'location_name',
            'eduty',
            'tel',
            'positions',
            'position_type',
            'ename',
            'level',
            'begintime',
            'overtime',
            'beginunit',
            'emeasure',
            'elog',
            'esummarize'
        ],
    ];
}