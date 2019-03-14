<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15
 * Time: 17:10
 */

namespace app\improve\validate;
use think\Validate;


class SpeciesType  extends BaseValidate
{
    protected $rule = [
        'parentId' => 'require',
        'name' => 'require',
        'ids' => 'require',
        'id' => 'require'
    ];

    protected $message = [
        'parentId.require' => '生物类类型必填',
        'id.require' => 'id必填',
        'name.require' => '生物类型名称必填'
    ];

    protected $scene = [
        'add' => [
            'parentId',
            'name',
        ],
        'ids' => [
            'ids',
        ],
        'id' => [
            'id',
        ],
        'edit' => [
            'id',
            'name'
        ],
    ];
}