<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 10:08
 */

namespace app\improve\validate;


use think\Validate;

class BaseValidate extends Validate
{

    // 自定义验证规则
    //并且需要注意的是，自定义的验证规则方法名不能和已有的规则冲突。
    protected function region($value)
    {
        return strpos($value, '1') === 0 ? true : '区域必须在湖南省内';
        //return strpos($value, '35') === 0 ? true : '区域必须在湖南省内';
    }

    protected function tel($value)
    {
        $regex = "/^(13[0-9]|15[0-9]|17[0-9]|18[0-9]|14[0-9]|19[0-9]|16[0-9])[0-9]{8}$/";
        return preg_match($regex, $value) ? true : '手机号格式错误';
    }

    protected function positionReg($value)
    {
        // $regex = '/^-?((0|1?[0-7]?[0-9]?)(([.][0-9]{1,15})?)|180(([.][0],{1,15})?))\,-?((0|[1-8]?[0-9]?)(([.][0-9]{1,15})?)|90(([.][0]{1,15})?))\;*$/';  //不加括号的地图坐标
        $regex = '/^(\(-?((0|1?[0-7]?[0-9]?)(([.][0-9]{1,15})?)|180(([.][0],{1,15})?))\,-?((0|[1-8]?[0-9]?)(([.][0-9]{1,15})?)|90(([.][0]{1,15})?))\)\;)*$/'; //加括号的地图坐标
        return preg_match($regex, $value) ? true : '地图位置格式错误，格式为(112.9378464818001,28.34338595516919);';
    }

    protected function per($value)
    {
        ///^(100|[1-9]?\d(\.\d\d?\d?)?)%$|0$/
        $reg = "/^(0|100|[1-9]?\d)%$/";
        return preg_match($reg, $value) ? true : '防治效果必须在0%-100%';
    }

    function end($s1, $end)
    {
        return substr($s1, -1, strlen($end)) === $end;
    }

}