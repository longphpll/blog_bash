<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2018/7/3
 * Time: 14:10
 */

namespace app\improve\validate;
use think\Validate;


class Relation  extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'cn_name' => 'require',//生物名称
        'eng_name' => 'require',//拉丁学名
        'attribute' => 'require',//生物一级剩下种类
        'types' => 'require',//
        'genre' => 'require',//生物类型
        'section' => 'require',//科
        'order' => 'require',//目
        'genus' => 'require',//属
        'prevention_way' => 'max:5000', //防治方法
        'living_habits' => 'max:50000',//生活习性
        'shape_character' => 'max:5000',//形态特征
        'city_region' => 'max:5000',//分布区域-市级
        'area_region' => 'max:5000',//分布区域-县级
        'county_region' => 'max:5000',//分布区域-乡级
        'ids' => 'require',
    ];

    protected $message = [
        'cn_name.require' => '生物名称必填',
        'eng_name.require' => '拉丁学名必填',
        'attribute.require' => '生物类别必填',
        'types' => '生物二级类别必填',
        'genre.require' => '生物类型必填',
        'genre.in' => '生物类型选择范围错误',
        'genre.in' => '生物类型选择范围错误',
        'section.require' => '科必填',
        'order.require' => '目必填',
        'genus.require' => '属必填',
        'plants.array' => '寄主树种数据格式错误',
        'harm_part.array' => '危害部位格式错误',
        'prevention_way.max' => '防治方法限制为5000字',
        'living_habits.max' => '生活习性限制为5000字',
        'shape_character.max' => '形态特征限制为5000字',
        'city_region.max' => '市级--分布区域限制为5000字',
        'area_region.max' => '县级--分布区域限制为5000字',
        'county_region.max' => '县级--分布区域限制为5000字',
    ];

    protected $scene = [
        'add' => [
            'cn_name',
            'attribute',
            'types',
            'genre',
            'section',
            'order',
            'genus',
            'prevention_way',
            'living_habits',
            'shape_character',
            'city_region',
            'area_region',
            'county_region'
        ],
        'enemy_add' => [
            'cn_name',
            'attribute',
            'genre',
            'section',
            'order',
            'genus',
            'prevention_way',
            'living_habits',
            'shape_character',
            'city_region',
            'area_region',
            'county_region'
        ],
        'query' => [
            'id',
        ],
        'ids' => [
            'ids',
        ],
        'edit' => [
            'id',
            'cn_name',
            'attribute',
            'types',
            'genre',
            'section',
            'order',
            'genus',
            'prevention_way',
            'living_habits',
            'shape_character',
            'city_region',
            'area_region',
            'county_region'
        ],
        'enemy_edit' => [
            'id',
            'cn_name',
            'attribute',
            'genre',
            'section',
            'order',
            'genus',
            'prevention_way',
            'living_habits',
            'shape_character',
            'city_region',
            'area_region',
            'county_region'
        ]
    ];
}