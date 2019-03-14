<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\validate;
use think\Validate;


class Material  extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'unit' => 'require|max:50',
        'name' => 'require|max:50',
        'version' => 'require|max:20',
        'measure' => 'require|max:20',
        'amount' => 'require|max:9|number',
        'price' => 'require|max:9|float',
        'ids' => 'require'
    ];

    protected $message = [
        'unit.require' => '行政单位必填',
        'unit.max' => '行政单位长度不能超过50个字符',
        'name.require' => '设备名称必填',
        'name.max' => '设备名称长度不能超过50个字符',
        'version.require' => '型号必填',
        'version.max' => '型号长度不能超过20个字符',
        'measure.require' => '计量单位必填',
        'measure.max' => '计量单位长度不能超过20个字符',
        'amount.require' => '数量必填',
        'amount.max' => '数量长度不能超过9个字符',
        'amount.number' => '数量必须填数字',
        'price.require' => '单价必填',
        'price.max' => '单价长度不能超过9个字符',
        'price.float' => '单价数据格式错误'
    ];

    protected $scene = [
        'add' => [
            'unit',
            'name',
            'version',
            'measure',
            'amount',
            'price',
        ],
        'query' => [
            'id',
        ],
        'ids' => [
            'ids',
        ],
        'edit' => [
            'id',
            'unit',
            'name',
            'version',
            'measure',
            'amount',
            'price',
        ],
    ];
}