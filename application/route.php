<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//方式三:注册路由
use think\Route;

//    路由表达式  地址表达式
//    /         index/index/index

Route::rule('/', 'index/index/index', 'get', ['ext' => 'pdf']);

//   user/login  index/user/login
Route::rule('login', 'index/user/login', 'get');

//用户注册
Route::rule('register', 'index/user/register', 'get', ['ext' => 'pdf']);

//5.2路由的调整和改进
//2019 年 1 月 16 日 发布
//ThinkPHP5.2的路由部分，也和其它组件一样，做了精简和优化，主要包括如下方面：
//
//5.2版本目前尚未正式发布，在正式发布之前可能仍然会存在变化。
//
//取消路由定义的返回数组形式（return [........];)
//因为不利于路由缓存生成，路由定义文件取消了返回数组的方式定义路由，必须采用路由方法注册路由。Route::get(....);
Route::get('news/:id', 'index/news/view', ['ext' => 'htttt'], ['id' => '\d+']);
//以上替代返回的数组形式
//新闻详情的路由地址
// 'news/:id'    => ['index/news/view', ['method' => 'get'], ['id' => '\d+']],

//--------------------------------给后台定义路由----------------------------------

//请求缓存仅对GET请求有效，有两种方式可以设置请求缓存：
// 定义GET请求路由规则 并设置3600秒的缓存
//新闻添加
//方法参数绑定 第一种情况 把路由地址中的变量作为操作方法的参数直接传入。
Route::get('newadd/:id/:year', 'admin/news/add', ['ext' => 'pdff', 'cache' => 3600], ['id' => '\d+', 'year' => '\d+']);
//http://blog/public/newadd/1/2019.pdff

//方法参数绑定 第二种情况 是把URL地址中的变量作为操作方法的参数直接传入。
//Route::get('newadd', 'admin/news/add', ['ext' => 'pdff','cache'=>3600]);
//http://blog/public/newadd/id/1/year/2019.pdff

//请求类型伪装(其实是post) <input type="hidden" name="_method" value="PUT" >
//如果你需要改变伪装请求的变量名，可以修改应用配置文件：
// 表单请求类型伪装变量
//'var_method'             => '_method',
//Route::put('newdoadd', 'admin/news/doadd');
Route::post('newdoadd', 'admin/news/doadd');
//-----------------------------------------------------------------------------

return [
    //请求类型伪装(其实是post) <input type="hidden" name="_method" value="PUT" >
//    'newdoadd' => ['admin/news/doadd', ['method' => 'put']],
//    'newdoadd' => ['admin/news/doadd', ['method' => 'post']],

    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    //方式一:直接声明
    //路由表达式  地址表达式
    //blog      index/blog/index
    //blog/数字  index/blog/view/id/数字
//    'blog/:id'    => ['index/blog/view', ['method' => 'get'], ['id' => '\d+']],
//    'blog'        => ['index/blog/index', ['method' => 'get']],

    //方式二:路由分组
    '[blog]'      => [
        ':id' => ['index/blog/view', ['method' => 'get'], ['id' => '\d+']],
        'add' => ['index/blog/add', ['method' => 'get']],
//        'doAdd' => ['index/blog/doAdd', ['method' => 'post']],
        '/'   => ['index/blog/index', ['method' => 'get']],
    ],

    //新闻详情的路由地址
//    'news/:id'    => ['index/news/view', ['method' => 'get'], ['id' => '\d+']],
    //分类详情的路由
    '[category]'  => [
        ':id' => ['index/category/view', ['method' => 'get'], ['id' => '\d+']],
    ],

];

