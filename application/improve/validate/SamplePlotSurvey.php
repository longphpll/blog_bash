<?php
/**
 * Created by qiu.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 17:45
 */

namespace app\improve\validate;

class SamplePlotSurvey extends BaseValidate
{
    protected $rule = [
        'id' => 'require|number',//记录id
        'sample_plot_number' => 'require|number',//固定标准地编号
        'hazard_type'	=> 'require|in:1,2,3',//危害类型
        'small_place_name'	=> 'require|max:30',//小地名
        'altitude' => 'require|float',//海拔
        'canopy_density' => 'require',//郁闭度（0.1-1.0）
        'strain_rate' => 'require',//有虫株率%
        'average_dbh' => 'require',//平均胸径（cm）
        'average_tree_height' => 'require',//平均树高（m)
        'growth_trend' => 'require',//生长势
        'terrain' => 'require',//地形地势
        'happen_area' => 'require|float',//发生面积 (亩)
        'harm_level' => 'require',//危害程度,1表示轻，2表示中，3表示高
        'distribution' => 'require',//病株分布情况，害草分布状况
        'ids' => 'require'
    ];

    protected $message  =   [
        'sample_plot_number.require'   => '固定标准地编号必填',
        'sample_plot_number.number'   => '固定标准地编号格式错误',
        'hazard_type.require'   => '危害类型必填',
        'hazard_type.in'   => '危害类型选择范围错误',
        'hazard_level.require'   => '危害程度必填',
        'hazard_level.in'   => '危害程度类型选择范围错误',
        'happen_level.require' => '发生程度必填',
        'happen_level.number' => '发生程度必须为数字',
        'happen_level.in' => '发生程度范围选择错误',
        'happen_area.require' => '发生面积必填',
        'happen_area.float' => '发生面积数据格式错误',
        'altitude.require' => '海拔必填',
        'altitude.float' => '海拔数据格式错误'
    ];

    protected $scene = [
        'insect' => [
            'sample_plot_number',
            'hazard_type',
            'small_place_name',
            'altitude',
            'canopy_density',
            'strain_rate'
        ],
        'disease' => [
            'sample_plot_number',
            'hazard_type',
            'small_place_name',
            'average_dbh',
            'average_tree_height',
            'canopy_density',
            'growth_trend',
            'terrain',
            'happen_area',
            'harm_level',
            'distribution'
        ],
        'plant' => [
            'sample_plot_number',
            'hazard_type',
            'small_place_name',
            'average_dbh',
            'canopy_density',
            'growth_trend',
            'terrain',
            'happen_area',
            'harm_level',
            'distribution'
        ],
        'insect_edit' => [
            'id',
            'sample_plot_number',
            'hazard_type',
            'small_place_name',
            'altitude',
            'canopy_density',
            'strain_rate'
        ],
        'disease_edit' => [
            'id',
            'sample_plot_number',
            'hazard_type',
            'small_place_name',
            'average_dbh',
            'average_tree_height',
            'canopy_density',
            'growth_trend',
            'terrain',
            'happen_area',
            'harm_level',
            'distribution'
        ],
        'plant_edit' => [
            'id',
            'sample_plot_number',
            'hazard_type',
            'small_place_name',
            'average_dbh',
            'canopy_density',
            'growth_trend',
            'terrain',
            'happen_area',
            'harm_level',
            'distribution'
        ],
        'id'=>['id'],
        'ids' => ['ids']
    ];
}