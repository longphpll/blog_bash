<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5/005
 * Time: 18:01
 */

namespace app\improve\validate;


class QuarantineCase extends BaseValidate
{
    /**
     * 检疫员统计验证器
     * 创建人：余思渡
     * 创建时间：2019.03.06
     */
    protected $rule = [
        'id' => 'require',
        'region' => 'require|max:20',
        'year' => 'require|dateFormat:Y',
        'ids'  => 'require'
    ];

    protected $message = [
        'region.require' => '区域必填',
        'region.max' => '区域长度不能超过20个字符',
        'year.require' => '年度必填',
        'year.dateFormat' => '年度日期格式错误',
        'ids.require' => '年度必填',
    ];

    protected $scene = [
        'add' => [
            'region',
            'year'
        ],
        'query' => [
            'id',
        ],
        'deleteChecked' => [
            'ids',
        ],
        'edit' => [
            'id',
            'region',
            'year'
        ],
    ];
}