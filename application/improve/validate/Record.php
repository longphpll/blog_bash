<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/5 0005
 * Time: 14:29
 */

namespace app\improve\validate;

class Record extends BaseValidate
{
    protected $rule = [
        'name' => 'require|max:80',
        'status' => 'require|in:2,3',
        'id' => 'require',
    ];

    protected $message = [
        'name.require' => '生物必填',
        'survey_time.require' => '采集时间必填',
        'adder' => '采集人必填'
    ];

    protected $scene = [
        'query' => ['id'],
        'examine' => ['id','name','status']
    ];
    
}