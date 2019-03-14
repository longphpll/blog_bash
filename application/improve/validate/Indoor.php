<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\validate;
use think\Validate;


class Indoor  extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'number' => 'require|max:20',
        'region' => 'require|max:20|region',
        'positions' => 'require|positionReg',
        'position_type' => 'require|in:-1,1,2,3',
        'sampling_part' => 'require|in:1,2,3|number',
        'results' => 'require|max:25',
        'appraiser' => 'require|max:6',
        'reviewer' => 'require|max:6',
        'ids' => 'require'
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'number.require' => '样本编号必填',
        'number.max' => '样本编号不能超过20个字符',
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20个字符',
        'positions.require' => '地图位置必填',
        'positions.positionReg' => '地图位置格式错误',
        'position_type.require' => '地图位置类型必填',
        'position_type.in' => '地图位置类型错误',
        'sampling_part.require' => '取样部位必填',
        'sampling_part.in' => '取样部位选择范围错误',
        'sampling_part.number' => '取样部位选择值错误',
        'results.require' => '鉴定结果必填',
        'results.max' => '鉴定结果不能超过25个字符',
        'appraiser.require' => '鉴定人必填',
        'appraiser.max' => '鉴定人不能超过6个字符',
        'reviewer.require' => '复检人必填',
        'reviewer.max' => '复检人不能超过6个字符',
        'ids.require' => 'ids不能为空',
        'ids.array' => 'ids为数组'
    ];

    protected $scene = [
        'add' => [
            'number',
            'region',
            'positions',
            'position_type',
            'sampling_part',
            'results',
            'appraiser',
            'reviewer'
        ],
        'query' => [
            'id',
        ],
        'ids' => [
            'ids',
        ],
        'edit' => [
            'id',
            'number',
            'region',
            'positions',
            'position_type',
            'sampling_part',
            'results',
            'appraiser',
            'reviewer'
        ],
    ];
}