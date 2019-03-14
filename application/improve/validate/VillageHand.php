<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

class VillageHand extends BaseValidate
{
    protected $rule = [
        'id' => 'require|number',
        'region' => 'require|max:20',
        'type' => 'require|in:1,2,3,4',
        'hand_time' => 'require|dateFormat:Y-m-d',
        'pest_id' => 'require|number',
        'hand_method' => 'require|number|in:1,2,3,4',
        'drug_amount' => 'require|min:0',
        'hand_cost' => 'require|float|min:0',
        'hand_area' => 'require|float|min:0',
        'happen_area' => 'require|float|min:0',
        'hand_effect' => 'require|per|max:16',
        'save_pest_area' => 'require|float|min:0',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'location_name' => 'require',
        'ids' => 'require',
        'del_images' => 'max:6',
        'hand_one_class'=>'require|number',
        'hand_two_class'=>'require|number',
        'drug_name'=>'require|number',
        'drug_amount'=>'require',
        'drug_unit'=>'require|in:1,2,3'
    ];

    protected $message = [
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20个字符',
        'type.require' => '有害生物类型必填',
        'type.in' => '有害生物类型选择范围错误',
        'hander.require' => '防治人必填',
        'hander.max' => '防治人长度不能超过6位字符',
        'hand_time.require' => '防治时间必填',
        'hand_time.dateFormat' => '防治时间格式错误',
        'pest_id.require' => '有害生物种类必填',
        'pest_id.number' => '有害生物种类必须为数字',
        'drug_amount.require' => '用药数量必填',
        'drug_amount.min' => '用药数量最小为0',
        'hand_cost.require' => '防治费用必填',
        'hand_cost.float' => '防治费用数据格式错误',
        'hand_cost.min' => '防治费用最小为0',
        'hand_area.require' => '防治面积必填',
        'hand_area.float' => '防治面积数据格式错误',
        'hand_area.min' => '防治面积最小为0',
		'happen_area.require' => '发生面积必填',
        'happen_area.float' => '发生面积数据格式错误',
		'happen_area.min' => '发生面积最小为0',
        'hand_effect.require' => '防治效果必填',
        'hand_effect.max' => '防治效果长度不能超过16个字符',
        'location_name.require' => '地理位置名称必填',
        'save_pest_area.require' => '挽回灾害面积必填',
		'save_pest_area.float' => '挽回灾害面积数据格式错误',
        'save_pest_area.min' => '挽回灾害面积最小为0',
        'positions.require' => '地图位置必填',
        'positions.positionReg' => '地图位置格式错误',
        'position_type.require' => '地图位置类型必填',
        'position_type.in' => '地图位置类型错误',
        'hand_one_class.require' => '防治措施一级必填',
        'hand_one_class.number' => '防治措施一级格式错误',
        'hand_two_class.require' => '防治措施二级必填',
        'hand_two_class.number' => '防治措施二级格式错误',
        'drug_name.require' => '防治药剂必填',
        'drug_name.number' => '防治药剂格式错误',
        'drug_amount.require' => '用药量必填',
        'drug_unit.require' => '用药单位选择范围必填',
        'drug_unit.in' => '用药单位选择范围错误'
    ];

    protected $scene = [
        'id' => ['id'],
        'ids' => ['ids'],
        'add' => [
            'region',
            'positions',
            'position_type',
            'location_name',
            'type',
            'pest_id', 
            'hand_time',
            'hander', 
            'hand_cost', 
            'happen_area',
            'hand_area',
            'save_pest_area'
            ],
        'edit' => [
            'id',
            'region',
            'positions',
            'position_type',
            'location_name',
            'type',
            'pest_id', 
            'hand_time',
            'hander', 
            'hand_cost', 
            'happen_area',
            'hand_area',
            'save_pest_area'
        ],
        'record' => [
            'hand_one_class',
            'hand_two_class',
            'drug_name',
            'drug_amount',
            'drug_unit'
        ],
    ];

}