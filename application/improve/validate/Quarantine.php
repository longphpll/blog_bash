<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/5 0005
 * Time: 14:29
 */

namespace app\improve\validate;

class Quarantine extends BaseValidate
{
    protected $rule = [
        'positions' => 'positionReg',
        'position_type' => 'in:-1,0,1,2,3',
        'region' => 'max:20',
        'organization' => 'max:20',
        'fonud_time' => 'dateFormat:Y-m-d',
        'nature' => 'in:0,1,2',
        'tel'=>'tel|max:11',
        'administrator'=>'max:10',
        'id' => 'require|max:20',
        'ids' => 'require'
    ];

    protected $message = [
        'positions.require' => '地图位置必填',
        'positions.positionReg' => '地图位置格式错误',
        'position_type.require' => '地图位置类型必填',
        'position_type.in' => '地图位置类型错误',
        'location_name.require' => '地图位置名称必填',
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20个字符',
        'organization.require' => '检查站名称必填',
        'organization.max' => '检查站名称长度不能超过20个字符',
        'found_time.require' => '建站时间必填',
        'found_time.dateFormat' => '建站时间格式错误',
        'nature.require' => '建站性质必填',
        'nature.in' => '建站性质选择范围错误',
        'tel.require' => '手机号必填',
        'tel.max' => '手机号长度不能超过11个字符',
        'administrator.require' => '管理者必填',
        'administrator.max ' => '管理者长度不能超过55个字符',
    ];

    protected $scene = [
        'add' => ['positions','region','organization','location_name','found_time','nature','tel','administrator','position_type'],
        'query' => ['id'],
        'edit' => ['id', 'positions','region','organization','location_name','found_time','nature','tel','administrator','position_type'],
        'ids'=>['ids'],
    ];
    
}