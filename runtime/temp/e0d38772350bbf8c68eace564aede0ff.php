<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:67:"D:\web\zijiBlog\public/../application/admin\view\comment\index.html";i:1551420033;s:55:"D:\web\zijiBlog\application\admin\view\public\base.html";i:1536672898;s:57:"D:\web\zijiBlog\application\admin\view\public\topnav.html";i:1551332686;s:58:"D:\web\zijiBlog\application\admin\view\public\leftnav.html";i:1551419811;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>新闻世界管理后台</title>
    <link rel="stylesheet" type="text/css" href="/public/static/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="/public/static/css/dashboard.css" />
    
</head>

<body>

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo url('admin/index/index'); ?>">新闻世界管理后台</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#">Profile</a></li>
                <li><a href="#">Help</a></li>
                <li class="dropdown">
                    <a href="#" data-toggle="dropdown">
                        <span class="glyphicon glyphicon-user"></span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="#">
                                <span class="text-primary"><?php echo \think\Session::get('admin.username'); ?></span>
                            </a>
                        </li>
                        <li><a href="<?php echo url('admin/admin/logout'); ?>">退出</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar" style="padding:0">
            ﻿<div class="panel-group" id="pgroup">
    <div class="panel <?php if(\think\Request::instance()->controller() == " Blog
    "): ?>panel-success<?php else: ?>panel-info<?php endif; ?>">
    <div class="panel-heading">
        <a href="#blog" data-toggle="collapse" data-parent="#pgroup">
            <h3 class="panel-title">博客管理</h3>
        </a>
    </div>
    <div id="blog" class="collapse <?php if(\think\Request::instance()->controller() == " Blog
    "): ?>in<?php endif; ?>">
    <div class="panel-body" style="padding:0;">
        <ul class="list-group" style="margin-bottom:0;">
            <li class="list-group-item">
                <a href="<?php echo url('admin/blog/index'); ?>">博客列表</a>
            </li>
            <!--<li class="list-group-item">-->
            <!--<a href="<?php echo url('index/blog/add'); ?>">添加博客</a>-->
            <!--</li>-->
        </ul>
    </div>
</div>
</div>
<div class="panel <?php if(\think\Request::instance()->controller() == " User"): ?>panel-success<?php else: ?>panel-info<?php endif; ?>">
<div class="panel-heading">
    <a href="#user" data-toggle="collapse" data-parent="#pgroup">
        <h3 class="panel-title">用户管理</h3>
    </a>
</div>
<div id="user" class="collapse <?php if(\think\Request::instance()->controller() == " Admin"): ?>in<?php endif; ?>">
<div class="panel-body" style="padding:0;">
    <ul class="list-group" style="margin-bottom:0;">
        <li class="list-group-item">
            <a href="<?php echo url('admin/admin/index'); ?>" onclick="getNew()">用户列表</a>
        </li>
        <li class="list-group-item">
            <a href="<?php echo url('admin/admin/register'); ?>">添加管理员</a>
        </li>
    </ul>
</div>
</div>
</div>

<div class="panel <?php if(\think\Request::instance()->controller() == " Comment"): ?>panel-success<?php else: ?>panel-info<?php endif; ?>">
<div class="panel-heading">
    <a href="#comment" data-toggle="collapse" data-parent="#pgroup">
        <h3 class="panel-title">评论管理</h3>
    </a>
</div>
<div id="comment" class="collapse <?php if(\think\Request::instance()->controller() == " Comment"): ?>in<?php endif; ?>">
<div class="panel-body" style="padding:0;">
    <ul class="list-group" style="margin-bottom:0;">
        <li class="list-group-item">
            <a href="<?php echo url('admin/comment/index'); ?>">评论列表</a>
        </li>
    </ul>
</div>
</div>
</div>

<div class="panel <?php if(\think\Request::instance()->controller() == " Category"): ?>panel-success<?php else: ?>panel-info<?php endif; ?>">
<div class="panel-heading">
    <a href="#category" data-toggle="collapse" data-parent="#pgroup">
        <h3 class="panel-title">分类管理</h3>
    </a>
</div>
<div id="category" class="collapse <?php if(\think\Request::instance()->controller() == " Category"): ?>in<?php endif; ?>">
<div class="panel-body" style="padding:0;">
    <ul class="list-group" style="margin-bottom:0;">
        <li class="list-group-item">
            <a href="<?php echo url('admin/category/index'); ?>">分类列表</a>
        </li>
        <li class="list-group-item">
            <a href="<?php echo url('admin/category/add'); ?>">添加分类</a>
        </li>
    </ul>
</div>
</div>
</div>

<div class="panel <?php if(\think\Request::instance()->controller() == " News"): ?>panel-success<?php else: ?>panel-info<?php endif; ?>">
<div class="panel-heading">
    <a href="#news" data-toggle="collapse" data-parent="#pgroup">
        <h3 class="panel-title">新闻管理</h3>
    </a>
</div>
<div id="news" class="collapse <?php if(\think\Request::instance()->controller() == " News"): ?>in<?php endif; ?>">
<div class="panel-body" style="padding:0;">
    <ul class="list-group" style="margin-bottom:0;">
        <li class="list-group-item">
            <a href="<?php echo url('admin/news/index'); ?>">新闻列表</a>
        </li>
        <li class="list-group-item">
            <a href="<?php echo url('admin/news/add',['id'=>1,'year'=>'2019']); ?>">添加新闻</a>
        </li>
    </ul>
</div>
</div>
</div>
</div>

        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            
<h1 class="bg-success text-center text-primary">评论列表</h1>

<form id="searchForm" class="form-inline">
    <div class="form-group">
        <label class="sr-only">标题:</label>
        <input type="text" name="title" value="<?php echo input('param.title'); ?>" class="form-control" placeholder="标题">
    </div>
    <div class="form-group">
        <label class="sr-only">评论内容:</label>
        <input type="text" name="content" value="<?php echo input('param.content'); ?>" class="form-control" placeholder="评论内容">
    </div>
    <div class="form-group">
        <!--<input type="button" id="doSearch" value="搜索" class="btn btn-primary">-->
        <input type="submit" id="doSearch" value="搜索" class="btn btn-primary">
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-condensed table-hover table-striped ">
        <tr>
            <th class="text-center">ID</th>
            <th class="text-center">作者</th>
            <th class="text-center">所属类型</th>
            <th class="text-center">标题</th>
            <th class="text-center">评论内容</th>
            <th class="text-center">创建时间</th>
            <th class="text-center">操作菜单</th>
        </tr>
        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
        <tr>
            <td><?php echo $vo['id']; ?></td>
            <td><?php echo $vo->author->phone; ?></td>
            <td><?php echo $vo['comment_type']; ?></td>
            <td><?php echo $vo->target->title; ?></td>
            <td><?php echo mb_substr($vo['content'],0,10); ?></td>
            <td><?php echo $vo['created']; ?></td>
            <td>
                <a href="<?php echo url('admin/comment/delete',['id'=>$vo['id']]); ?>" class="btn btn-danger"
                   onclick="return confirm('请确认删除')">
                    删除
                </a>
            </td>
        </tr>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </table>
    <?php echo $list->render(); ?>
</div>

        </div>
    </div>
</div>

<script type="text/javascript" src="/public/static/js/jquery-3.3.1.js"></script>
<script type="text/javascript" src="/public/static/bootstrap/js/bootstrap.js"></script>


<script type="text/javascript">

    //方式二：通过框架自带 paginate() 方法实现
    //注意表单提交方式为 submit

//     $('document').ready(function () {
//         $('#doSearch').click(function () {
//             var data = $('#searchForm').serialize();
//             //console.log(data);
//             //comment_id=username&content=tom
//
//             var arr = data.split('&');
//             //["comment_id=username", "content=tom"]
//             var target = '';
//             $.each(arr, function (index, value) {
//                 // console.log(index);//0 1
//                 //console.log(value);
//                 //comment_id=username
//                 //content=tom
//
//                 var args = value.split('=');
//                 //console.log(args);
//                 //["comment_id", "username"]
//                 //["content", "tom"]
//
//                 if (args[1] != '') {
//                     target += '/' + args[0] + '/' + args[1];
//                 }
//             })
//             //console.log(target);
//             // /comment_id/username/content/tom
//
//             var url = "<?php echo url('admin/comment/index'); ?>" + target;
//             console.log(url);
// // /18_news/tp5/public/admin/comment/index.html/comment_id/username/content/tom
//
//             //去掉 .html
//             var newUrl = url.replace('.html', '');
//             console.log(newUrl);
//             location.href = newUrl;
//         });
//     });
</script>

</body>
</html>
