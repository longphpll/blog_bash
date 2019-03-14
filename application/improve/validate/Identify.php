<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\validate;
use think\Validate;


class Identify  extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'name' => 'require|max:20',
        'alias' => 'require|max:50',
		'latin' => 'require|max:50',
        'subjects' => 'require|max:20',
        'major_hazard' => 'require|max:255',
        'prevention_methods' => 'require|max:255',
        'life_habits' => 'require|max:255',
        'form_feature' => 'require|max:255',
        'distribution_area' => 'require|max:255',
        'location' => 'require|max:50',
		'similarity' => 'require',
		'positions' => 'require',
        'ids' => 'require|array'
    ];

    protected $message = [
        'name.require' => '识别名称不能为空',
        'name.max' => '识别长度不能超过20个字符',
		'alias.require' => '别名不能为空',
        'alias.max' => '别名不能超过50个字符',
		'latin.require' => '拉丁名不能为空',
        'latin.max' => '拉丁名长度不能超过50个字符',
		'subjects.require' => '科目不能为空',
        'subjects.max' => '科目不能超过20个字符',
		'major_hazard.require' => '主要危害不能为空',
        'major_hazard.max' => '主要危害长度不能超过255个字符',
		'prevention_methods.require' => '防治方法不能为空',
        'prevention_methods.max' => '防治方法长度不能超过255个字符',
		'life_habits.require' => '生活习性不能为空',
        'life_habits.max' => '生活习性长度不能超过255个字符',
		'form_feature.require' => '形态特征不能为空',
        'form_feature.max' => '形态特征长度不能超过255个字符',
		'distribution_area.require' => '分布区域不能为空',
        'distribution_area.max' => '分布区域长度不能超过255个字符',
        'location.require' => '识别位置不能为空',
        'location.max' => '识别位置不能超过50个字符',
		'similarity.require' =>'相似度不能为空',
		'positions.require' => '地图位置必填'
    ];

    protected $scene = [
        'add' => [
            'name',
			'similarity',
            'identify_location',
			'positions',
        ],
        'entry' => [
            'name',
            'alias',
			'latin',
            'subjects',
            'major_hazard',
            'prevention_methods',
            'life_habits',
            'form_feature',
            'distribution_area',
        ],
         'exist' => [
            'name',
            'subjects',
        ],
        'query' => [
            'id',
        ],
        'ids' => [
            'ids',
        ],
    ];
}