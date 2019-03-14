<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 15:24
 */

namespace app\improve\validate;

class User extends BaseValidate
{
    protected $rule = [
        'pwd' => 'require|max:16|min:6',
        'pwds' => 'length:6,16|different:account|alphaDash',
        'region' => 'require|max:20|region',
        'name' => 'require|max:16',
        'status'=>"in:-1,0",
        'examine'=>"in:1,-1",
        'rid'=>"require",
        'user_level' => 'require|in:1,2,3',
        'mids'=>"require|array",
        'uid'=>"require|length:32",
		'oldpwd' => 'require|length:6,16',
        'client'=>'require|in:1,2,3',
		'job' => 'max:10',
        'dept' => 'require|max:10',
		'reason' => 'require|max:50',
        'verity_code' =>'max:4',
        'imgHead' => 'require',
        'tel'=>'require|tel',
    ];

    protected $message = [
	    'pwd.require' => '密码必填',
        'pwd.length' => '密码长度需6到16',
		'pwd.max' => '密码长度不能超过16个字符',
		'pwd.min' => '密码长度至少为6个字符',
        'pwds.length' => '密码长度需6到16',
        'pwds.alphaDash' => '密码只能包含字母，下划线，数字',
        'user_level.require' => '用户级别必填',
        'user_level.in' => '用户级别选择范围错误',
        'region.require' => '区域必填',
        'region.max' => '区域最多20个字符',
        'name.require' => '名字必填',
        'name.max' => '名字最多16个字符',
        'verity_code.require' => '验证码必填',
		'oldpwd.require' => '旧密码必填',
        'oldpwd.length' => '旧密码长度6到16位',
        'verity_code.max' => '验证码长度最多为4位',
		'dept.require' => '机构必填',
        'dept.max' => '机构长度最多为10位',
        'job.max' => '职务长度不能超过10位',
        'reason.require' => '驳回原因不能为空',
		'reason.max' => '驳回原因长度不能超过50个字符',
		'mids.require' => '用户类型不能为空',
        'mids.array' => '用户类型格式错误',
        'mid.require' => '用户类型不能为空',
        'imgHead.require' => '请上传头像',
        'tel.require' => '手机号码必填'
    ];

    protected $scene = [
        'add'  =>  ['pwd','region','name','rid','mids','tel','type','job','dept'],
        'status'  =>  ['uid','status'],
        'edit'  =>  ['status','name','rid','mids','uid','job'],
        'query'  =>  ['uid'],
        'examines' => ['examine'],
        'register' => ['pwd','region','name','rid','tel','mid','dept','job'],
        'wxRegister' => ['pwd','region','name','tel','dept','job','imgHead'],
        'login'=>['tel','pwd','verity_code','client'],
        'plogin'=>['tel','pwd'],
        'loginOut'=>['client'],
    ];

}