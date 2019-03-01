<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/18
 * Time: 16:39
 */

namespace app\index\controller;


class LogController
{
    /**
     *该类主要用于生成日志信息，以便系统出现问题后进行查询
     *暂定主要参数说明如下：目前只能想到这些先进行
     *
     * $type_dir：类型。目前类型有：
     * “登录=login.txt”/
     * “导出=serch.txt”/
     * “申请”=apply.txt/
     * “上传重要文件”=important_files.txt
     *"上报确认"=confirm.txt
     *
     * $condition：操作内容。建议为字符串的形式
     *
     */

    private $pan_dir = "./log/"; //在 D:\web\zijiBlog\public\log 生成 login.txt日志文件

//.....形成日志文件
    function log($type_dir, $condition)
    {
        if (!is_dir($this->pan_dir)) {
            mkdir($this->pan_dir);
        }

        if (!is_file($this->pan_dir . $type_dir)) {
            $my_file = fopen($this->pan_dir . $type_dir, "w");
            if (!$my_file) {
                $result_array = [
                    'code' => 0,
                    'msg' => '打开日志文件失败',
                ];
                return $result_array;
            }
            $txt = '********************' . $type_dir . '********************' . "\r\n";
            $txt .= date('Y-m-d H:i:s') . "\r\n";
            $txt .= $condition . "\r\n";
            fwrite($my_file, $txt);
            fclose($my_file);
            $result_array = [
                'code' => 1,
                'msg' => '日志写入成功',
            ];
        } else {
            $my_file = fopen($this->pan_dir . $type_dir, "a+");
            if (!$my_file) {
                $result_array = [
                    'code' => 0,
                    'msg' => '打开日志文件失败',
                ];
                return $result_array;
            }
            $txt = '********************' . $type_dir . '********************' . "\r\n";
            $txt .= date('Y-m-d H:i:s') . "\r\n";
            $txt .= $condition . "\r\n";
            fwrite($my_file, $txt);
            fclose($my_file);
            $result_array = [
                'code' => 1,
                'msg' => '日志写入成功',
            ];
        }
        return $result_array;
    }

//.....日志文件用例
    function log_txt()
    {
        $log_txt=new LogController();
        $condition = ['a', 'b', 'c'];
        $type_dir = "login.txt";
        $log_txt->log($type_dir, $condition);
    }

//.....多为数组形成二维数组
    function rebuild_array($arr)
    {
        static $tmp = array();
        if (is_array($arr)) {
            foreach ($arr as $item) {
                if (is_array($item)) $this->rebuild_array($item);
                else $tmp[] = $item;
            }
        }
        return $tmp;
    }

//.....数组转原型字符串
    function array_iconv($arr)
    {
        $ret = var_export($arr, true) ;
        return $ret;
    }

}