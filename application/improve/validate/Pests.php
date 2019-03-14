<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

use think\Validate;


class Pests extends Validate
{
    protected $rule = [
        'name' => 'require|max:16',
        'is_localed' => 'require|in:-1,1',
        'ids' => 'require|array',
        'id' => 'require|number',
        'danger_attributes' =>'max:16',
        'introduce' => 'max:255',
        'attach' => 'require|in:-1,1'
    ];

    protected $message = [
        'name.require' => '树种名称必填',
        'name.max' => '树种名称长度不能超过16个字符',
        'is_localed.require' => '是否本地化必填',
        'is_localed.in' => '是否本地化选择范围错误',
        'danger_attributes.max' => '危险属性长度不能超过16个字符',
        'introduce.max' => '简介长度不能超过255个字符',
    ];

    protected $scene = [
        'local'=>['ids'],
        'id'=>['id'],
        'imageId'=>['imageId'],
        'edit'=>['id','danger_attributes','introduce','attach'],
    ];
}