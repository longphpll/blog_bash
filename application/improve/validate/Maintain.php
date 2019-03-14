<?php
/**
 * Created by qiumu.
 * User: Administrator
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\validate;
use think\Validate;


class Maintain  extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'region' => 'require',
        'location_name' => 'require',
        'trap_number' => 'require|max:20',
        'positions' => 'require|positionReg',
        'maintenance_date' => 'require|dateFormat: Y-m-d',
        'female_number' => 'require|number',
        'male_number' => 'require|number',
        'total' => 'require|number',
        'drug_model' => 'require|max:25',
        'remarks' => 'max:30',
        'ids' => 'require|array',
        'region_name' => 'require|max:20'
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'trap_number.require' => '诱捕器编号必填',
        'trap_number.max' => '诱捕器编号不能超过20位字符',
        'positions.require' => '地图位置必填',
        'location_name.require' => '地图位置名称必填',
        'positions.positionReg' => '地图位置格式错误',
        'maintenance_date.require' => '维护时间必填',
        'maintenance_date.dateFormat' => '维护时间格式为年-月-日',
        'female_number.require' => '雌虫量必填',
        'female_number.number' => '雌虫量请填写数字',
        'male_number.require' => '雄虫量必填',
        'male_number.number' => '雄虫量请填写数字',
        'total.require' => '本期诱虫量必填',
        'total.number' => '本期诱虫量请填写数字',
        'drug_model.require' => '药剂型号必填',
        'drug_model.max' => '药剂型号不能超过25位字符',
        'device_status.require' => '设备状态必填',
        'remarks.max' => '备注不能超过30位字符',
        'ids.require' => 'ids不能为空',
        'ids.array' => 'ids为数组'
    ];

    protected $scene = [
        'add' => [
            'region',
            'location_name',
            'trap_number',
            'positions',
            'maintenance_date',
            'female_muber',
            'male_number',
            'total',
            'drug_model',
            'remarks',
            'device_status'
        ],
        'query' => [
            'id',
        ],
        'ids' => [
            'ids',
        ],
        'trap' => [
            'tnumber',
        ],
        'edit' => [
            'id',
            'maintenance_date',
            'female_number',
            'male_number',
            'total',
            'drug_model',
            'remarks',
            'device_status'
        ],
    ];
}