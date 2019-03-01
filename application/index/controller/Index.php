<?php

namespace app\index\controller;

use app\common\controller\IndexBase;
use think\Db;

//前台首页控制器
class Index extends IndexBase
{
    //首页
    public function index()
    {
        //测试使用
        //Hmset($key, $hashKeys)
        /**同时将多个 field-value (域-值)对设置到哈希表 key 中。
         * ** @param   string $key
         * @param   array $hashKeys key → value array
         * @return  bool
         * */
        //halt($this->redis);//object(think\cache\driver\Redis)[10]....
//        $this->redis->set('info', 'tom');
//        echo $this->redis->get('info');//tom
//        die();

        //以下是DID操作添加的代码****************************************************2019/2/27
        /**
         * 接口-申请DID 用户访问网站首页时就访问这个接口，将申请的did返给前端
         * 思路：验证提交的三个参数，生成32位DID，确保frame_client_device表中该DID是唯一的，返回该DID
         * 提示：
         * ●ask_for_did(...)并不会写frame_client_device中的last_access字段，放到analyze_did()中写last_access字段了，
         * 类似我打算在 analyze_right() 中写log.txt文件一样，
         * ，所以，当该字段为空时表时还没有访问，只是创建了
         * ●code只有 0失败 和 1成功 两种可能
         *
         * @param string $program_name //定义详见frame_base_program_controller表
         * @param string $env_string
         * @param string $internet_ip
         * @return string //返回DID，定义详见frame_base_program_controller表
         */
//        $program_name前端程序类型英文名front_web_user  $env_string个性化环境串00：01：6C：06：A6：29  $internet_ip外网IP地址 192.168.6.3 前端提供
//        ask_for_did
        try {
            //先验证前端数据
            //用父类控制器自带的 validate() 方法进行验证
            $validateRes = $this->validate($this->datas, 'BaseValidate.askForDid');
            if ($validateRes !== true) return error($validateRes);

            //检测前端提交的 program_name 的合法性(从表 FRAME_BASE_PROGRAM_STANDARD 查询是否有该program_name)
            $program_name_check = $this->check_program_name($this->datas['program_name']);
            if ($program_name_check !== true) return error($program_name_check);

            // env_string 校验 前端pc机web用户端	front_web_user	取网卡（任意一块）的MAC地址，17位（最长60位），如 00：01：6C：06：A6：29
            $env_string_trim = cut_invisible($this->datas['env_string']);//调用 公共函数 cut_invisible($string) 去掉空格
            if ($env_string_trim !== $this->datas['env_string']) return error('提交的env_string存在不可见字符');

            //查询redis中IP是否存在
//            dump($this->redis->rm('TRF_IP_' . $this->datas['internet_ip']));
            $DID = $this->redis->get('TRF_IP_' . $this->datas['internet_ip']);//TRF_20190227093023_yAidMhbSnmUEV


            if (!$DID) { // redis中对应的IP没有DID，新建DID
                //生成DID，格式：DEA（前端程序形式简写，长度3）_（日期时间，长度14）_（随机字符串，长度13）
                $program_short              = 'TRF';
                $DID                        = $program_short . '_' . date('YmdHis') . '_' . random_string(13);
                $insertData['did']          = $DID;
                $insertData['program_name'] = $this->datas['program_name'];
                $insertData['env_string']   = $this->datas['env_string'];
                $insertData['internet_ip']  = $this->datas['internet_ip'];
                $insertData['status']       = 1;
                $insertData['last_access']  = date('Y-m-d H:i:s');
                $insertData['access_count'] = 1;
                $insertData['create_time']  = date('Y-m-d H:i:s');
                $insertData['update_time']  = date('Y-m-d H:i:s');

                //将每次新生成的did插入到表 frame_client_device 中
                $dbRes = Db::table('frame_client_device')->insert($insertData);

                if ($dbRes) {
                    // 用户设备表DID插入成功，将对应的key为 TRF_IP_.(id地址) :value为DID 插入redis
//                    $this->redis->set('TRF_IP_' . $this->datas['internet_ip'], $DID, 3); //有效期3s
                    $this->redis->set('TRF_IP_' . $this->datas['internet_ip'], $DID, 1800); //有效期30分钟
                    //return success(['DID' => $DID], 1, 'DID插入用户设备表及redis缓存插入成功');
                }
            } else {  // reids中对应的IP有DID，刷新key为TRF_DID_IP的过期时间
//                $this->redis->set('TRF_IP_' . $this->datas['internet_ip'], $DID, 3); //过了3s后会从新生成 did
                $this->redis->set('TRF_IP_' . $this->datas['internet_ip'], $DID, 1800); //过了30分钟后会从新生成 did
                //return success(['DID' => $DID], 1, 'redis缓存中DID过期时间刷新成功');
            }
        } catch (\Exception $e) {
            return error($e->getMessage());
        }

        //获取轮播图
        $slide = model('news')->getSlide();
        //获取推荐新闻
        $recommend = model('news')->getRecommend();

        //获取一级分类
        $subs = model('category')->getSub();
        //print_r($subs);//[0] => app\index\model\Category Object...

        //查询一级分类下的新闻
        $news = [];
        foreach ($subs as $sub) {
            //$sub id  新闻1 财经4
            //第一次循环 新闻1
            $cids   = [];
            $cids   = model('category')->getChildrenIds($sub->id);
            $cids[] = $sub->id; //新闻的 id
//print_r($cids);
//Array ( [0] => 2 [1] => 7 [2] => 8 [3] => 3 [4] => 9 [5] => 10 [6] => 1 )
// Array ( [0] => 5 [1] => 6 [2] => 4 )
            // 根据分类id数组,查询最新的新闻
            $news[$sub->id] = model('news')->getNewsByCids($cids);
            //第二次循环 财经4
        }
//        print_r($news);
        $this->assign(['recommend' => $recommend,
                       'slide'     => $slide,
                       'subs'      => $subs,
                       'news'      => $news,
                       'did'       => $DID,
        ]);
        return $this->fetch();
    }

    //以下是DID操作添加的代码****************************************************2019/2/27
    /**
     * 接口-申请DID 用户访问网站首页时就访问这个接口，将申请的did返给前端
     * 思路：验证提交的三个参数，生成32位DID，确保frame_client_device表中该DID是唯一的，返回该DID
     * 提示：
     * ●ask_for_did(...)并不会写frame_client_device中的last_access字段，放到analyze_did()中写last_access字段了，
     * 类似我打算在 analyze_right() 中写log.txt文件一样，
     * ，所以，当该字段为空时表时还没有访问，只是创建了
     * ●code只有 0失败 和 1成功 两种可能
     *
     * @param string $program_name //定义详见frame_base_program_controller表
     * @param string $env_string
     * @param string $internet_ip
     * @return string //返回DID，定义详见frame_base_program_controller表
     */
    //$program_name前端程序类型英文名front_web_user  $env_string个性化环境串00：01：6C：06：A6：29  $internet_ip外网IP地址 192.168.6.3 前端提供

    public function ask_for_did()
    {
        try {
            //先验证前端数据
            //用父类控制器自带的 validate() 方法进行验证
            $validateRes = $this->validate($this->datas, 'BaseValidate.askForDid');
            if ($validateRes !== true) return error($validateRes);

            //检测前端提交的 program_name 的合法性(从表 FRAME_BASE_PROGRAM_STANDARD 查询是否有该program_name)
            $program_name_check = $this->check_program_name($this->datas['program_name']);
            if ($program_name_check !== true) return error($program_name_check);

            // env_string 校验 前端pc机web用户端	front_web_user	取网卡（任意一块）的MAC地址，17位（最长60位），如 00：01：6C：06：A6：29
            $env_string_trim = cut_invisible($this->datas['env_string']);//调用 公共函数 cut_invisible($string) 去掉空格
            if ($env_string_trim !== $this->datas['env_string']) return error('提交的env_string存在不可见字符');

            //查询redis中IP是否存在
//            dump($this->redis->rm('TRF_IP_' . $this->datas['internet_ip']));
            $DID = $this->redis->get('TRF_IP_' . $this->datas['internet_ip']);//TRF_20190227093023_yAidMhbSnmUEV


            if (!$DID) { // redis中对应的IP没有DID，新建DID
                //生成DID，格式：DEA（前端程序形式简写，长度3）_（日期时间，长度14）_（随机字符串，长度13）
                $program_short              = 'TRF';
                $DID                        = $program_short . '_' . date('YmdHis') . '_' . random_string(13);
                $insertData['did']          = $DID;
                $insertData['program_name'] = $this->datas['program_name'];
                $insertData['env_string']   = $this->datas['env_string'];
                $insertData['internet_ip']  = $this->datas['internet_ip'];
                $insertData['status']       = 1;
                $insertData['last_access']  = date('Y-m-d H:i:s');
                $insertData['access_count'] = 1;
                $insertData['create_time']  = date('Y-m-d H:i:s');
                $insertData['update_time']  = date('Y-m-d H:i:s');

                //将每次新生成的did插入到表 frame_client_device 中
                $dbRes = Db::table('frame_client_device')->insert($insertData);

                if ($dbRes) {
                    // 用户设备表DID插入成功，将对应的key为 TRF_IP_.(id地址) :value为DID 插入redis
//                    $this->redis->set('TRF_IP_' . $this->datas['internet_ip'], $DID, 3); //有效期3s
                    $this->redis->set('TRF_IP_' . $this->datas['internet_ip'], $DID, 1800); //有效期30分钟
                    return success(['DID' => $DID], 1, 'DID插入用户设备表及redis缓存插入成功');
                }
            } else {  // reids中对应的IP有DID，刷新key为TRF_DID_IP的过期时间
//                $this->redis->set('TRF_IP_' . $this->datas['internet_ip'], $DID, 3); //过了3s后会从新生成 did
                $this->redis->set('TRF_IP_' . $this->datas['internet_ip'], $DID, 1800); //过了30分钟后会从新生成 did
                return success(['DID' => $DID], 1, 'redis缓存中DID过期时间刷新成功');
            }
        } catch (\Exception $e) {
            return error($e->getMessage());
        }
    }

//    public function get_user_info()
//    {
//        // 验证
//        $validateRes = $this->validate($this->datas, 'BaseValidate.getUserInfo');
//        if ($validateRes !== true) return error($validateRes);
//
//        // 查询Redis
//        $user_info = $this->redis->get('TRF_DID_' . $this->datas['DID']);
//        if (!$user_info) {
//            return error('DID已过期!');
//        }
//        return success($user_info, 1, '用户信息查询成功!');
//    }

    /**
     * 检测前端提交的program_name的合法性
     * @param $program_name
     * @return bool
     */
    private function check_program_name($program_name)
    {
        try {
            $dbRes = Db::table('frame_base_program_controller')->where('program_name', $program_name)->find();
            if (empty($dbRes)) return '提交的program_name错误!'; //找不到则返回false
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
