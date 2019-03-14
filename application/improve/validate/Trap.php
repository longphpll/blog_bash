<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 14:18
 */
namespace app\improve\validate;

class Trap extends BaseValidate{
    protected $rule = [
        'id' => 'require|number',
        'region' => 'require|max:20|region',
        'trap_number' => 'require',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'unit' => 'require|max:12',
        'purpose' => 'require|max:12',
        'company' => 'require|max:12',
        'relation_name' => 'require|chs|max:6',
        'relation_tel' => 'require|tel',
        'amount' => 'require|number',
        'drug_model' => 'require|max:20',
        'drug_batch' => 'require|number'
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'region.require' => '区域必填',
        'trap_number.require' => '诱捕器必填',
        'region.max' => '区域长度不能超过20位字符',
        'positions.require' => '地图位置必填',
        'positions.positionReg' => '地图位置格式错误',
        'position_type.require' => '地图位置类型必填',
        'position_type.in' => '地图位置类型错误',
        'unit.require' => '所属单位编号必填',
        'unit.max' => '所属单位长度不能超过12位字符',
        'purpose.require' => '项目用途必填',
        'purpose.max' => '项目用途长度不能超过12位字符',
        'company.require' => '维护公司必填',
        'company.max' => '维护公司长度不能超过12位字符',
        'relation_name.require' => '维护公司联系人必填',
        'relation_name.chs' => '维护公司联系人请填写中文',
        'relation_name.max' => '维护公司联系人长度不能超过6位字符',
        'relation_tel.require' => '维护公司联系人电话必填',
        'amount.require' => '挂设数量必填',
        'amount.number' => '挂设数量请填写数字',
        'drug_model.require' => '药剂型号必填',
        'drug_model.max' => '药剂型号长度不能超过20位字符',
        'drug_batch.require' => '计划用药批次必填',
        'drug_batch.number' => '计划用药批次请填写数字',  
    ];

    protected $scene = [
        'add' => [
            'trap_number',
            'unit',
            'purpose',
            'company',
            'relation_name',
            'relation_tel',
            'amount',
            'drug_model',
            'drug_batch'
        ],
        'id' => ['id'],
        'edit' => [
            'id',
            'unit',
            'purpose',
            'company',
            'relation_name',
            'relation_tel',
            'drug_model',
            'drug_batch'
        ]
    ];
}