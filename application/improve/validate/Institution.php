<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/10
 * Time: 10:40
 */

namespace app\improve\validate;
use think\Validate;


class Institution extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'designation' => 'require|max:20',
        'nature' => 'require|in:1,2',
        'totality'=> 'number|max:5',
        'working' => 'number|max:5',
        'region' => 'require|max:20|region',
        'name' => 'require|max:20',
        'location_name' => 'require',
        'tel' => 'require|max:11|tel',
        'level' => 'require|in:1,2,3',
        'remark' => 'max:30',
        'ids' => 'require'
    ];

    protected $message  =   [
        'designation.require' => '单位名称必填',
        'designation.max' => '单位名称长度不能超过20个字符',
        'nature.require' => '机构性质必填',
        'nature.in' => '机构性质选择范围错误',
        'totality.require' => '编制人数必填',
        'totality.number' => '编制人数必须填数字',
        'totality.max' => '编制人数长度不能超过5个字符',
        'working.require' => '在岗人数必填',
        'working.number' => '在岗人数长度必须填数字',
        'working.max' => '在岗人数长度不能超过5个字符',
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20个字符',
        'name.require' => '负责人必填',
        'name.max' => '负责人长度不能超过20个字符',
        'duty.require' => '职务必填',
        'duty.max' => '职务长度不能超过20个字符',
        'tel.require' => '手机号必填',
        'tel.max' => '手机号长度不能超过11位',
        'level.require' => '机构级别必填',
        'level.in' => '机构级别选择范围错误',
        'remark.max'   => '备注长度不能超过30位字符',
        'positions.require' => '地图位置必填',
        'positions.positionReg' => '地图位置格式错误',
        'position_type.require' => '地图位置类型必填',
        'position_type.in' => '地图位置类型错误',
        'location_name.require' => '地图位置名称必填'
    ];

    protected $scene = [
        'add' => [
            'designation',
            'location_name',
            'nature',
            'totality',
            'working',
            'region',
            'name',
            'tel',
            'level',
            'remark'
        ],
        'query' => [
            'id',
        ],
        'ids' => [
            'ids',
        ],
        'edit' => [
            'id',
            'location_name',
            'designation',
            'nature',
            'totality',
            'working',
            'region',
            'name',
            'tel',
            'level',
            'remark',
        ],
    ];
}