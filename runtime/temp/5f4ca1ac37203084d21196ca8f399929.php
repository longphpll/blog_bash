<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:63:"D:\web\zijiBlog\public/../application/index\view\news\view.html";i:1551340131;s:55:"D:\web\zijiBlog\application\index\view\public\base.html";i:1546248022;s:57:"D:\web\zijiBlog\application\index\view\public\header.html";i:1551268041;s:57:"D:\web\zijiBlog\application\index\view\public\footer.html";i:1546247272;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    
    <meta HTTP-EQUIV="pragma" CONTENT="no-cache">
    <meta HTTP-EQUIV="Cache-Control" CONTENT="no-store, must-revalidate">
    <meta HTTP-EQUIV="expires" CONTENT="Wed, 26 Feb 1997 08:21:57 GMT">
    <meta HTTP-EQUIV="expires" CONTENT="0">

    <title></title>
    <link rel="stylesheet" type="text/css" href="/public/static/bootstrap/css/bootstrap.css" />
    
</head>

<body>

<div class="container">
    <div class="navbar navbar-inverse">
    <div class="navbar-header">
        <!-- 汉堡包菜单 -->
        <button data-toggle="collapse" data-target="#navbar-menu" class="navbar-toggle collapsed">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a href="<?php echo url('index/index/index'); ?>" class="navbar-brand">新闻视界</a>
    </div>
    <!-- 左侧菜单 -->
    <div id="navbar-menu" class="collapse navbar-collapse ">
        <ul class="nav navbar-nav">
            <li><a href="<?php echo url('index/index/index'); ?>">首页</a></li>
            <?php if(is_array($cmenu) || $cmenu instanceof \think\Collection || $cmenu instanceof \think\Paginator): $i = 0; $__LIST__ = $cmenu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
            <li><a href="<?php echo url('index/category/view',['id'=>$vo['id']]); ?>"><?php echo $vo['title']; ?></a></li>
            <?php endforeach; endif; else: echo "" ;endif; ?>
            <li class="dropdown">
                <a href="#" data-toggle="dropdown">
                    博客
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo url('index/blog/index'); ?>">博客列表</a></li>
                    <li><a href="<?php echo url('index/blog/add'); ?>">添加博客</a></li>
                </ul>
            </li>
        </ul>
        <!-- 右侧菜单 -->
        <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
                <a href="#" data-toggle="dropdown">
                    <span class="glyphicon glyphicon-user"></span>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <?php if(\think\Session::get('user.username')): ?>
                    <li>
                        <a href="<?php echo url('index/user/center',['id'=>\think\Session::get('user.id')]); ?>">
                        <span class="text-primary"><?php echo \think\Session::get('user.username'); ?></span>
                        </a>

                        <!--<a href="#">-->
                            <!--<span class="text-primary"><?php echo \think\Session::get('user.username'); ?></span>-->
                        <!--</a>-->
                    </li>
                    <li><a href="<?php echo url('index/user/logout'); ?>">退出</a></li>

                    <?php else: ?>
                    
                    <!--<li><a href="<?php echo url('index/user/register'); ?>">注册</a></li>-->
                    <li><a href="<?php echo url('index/user/login'); ?>">登陆</a></li>
                    <?php endif; ?>
                </ul>
            </li>
        </ul>
    </div>
</div>


    <div class="row">
        

        
<div class="col-md-8">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">
                <?php echo $data->title; ?>
            </h3>
        </div>
        <div class="panel-body">
            <?php if(!empty($data['image'])): ?>
            <img src="/public/static/upload/<?php echo $data['image']; ?>" class="img-responsive" alt="<?php echo $data['title']; ?>"
                 style="width:100%;height:300px;">
            <?php endif; ?>
            <?php echo nl2br($data['content']); ?>
        </div>
        <div class="panel-footer">
            作者:<?php echo $data->author->username; ?> 
            创建时间:<?php echo $data->created; ?>
            所属分类:<?php echo $data->category->title; ?>
            浏览量:(<?php echo $view; ?>)
        </div>
    </div>

    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">添加评论</h3>
        </div>
        <div class="panel-body">
            <?php if(\think\Session::get('user.username')): ?>
            <form action="<?php echo url('index/comment/add'); ?>" method="post" class="form-inline">
                <div class="form-group">
                    <label class="sr-only">内容:</label>
                    <input type="text" name="content" class="form-control"
                           placeholder="请输入评论内容">
                </div>
                <div class="form-group">
                    <input type="hidden" name="comment_id" value="<?php echo $data['id']; ?>">
                    <input type="hidden" name="comment_type" value="News">
                    <input type="hidden" name="uid" value="<?php echo \think\Session::get('user.id'); ?>">
                    <input type="submit" value="添加" class="btn btn-primary">
                    <input type="reset" vale="重置" class="btn btn-default">
                </div>
            </form>
            <?php else: ?>
            <a href="<?php echo url('index/user/login'); ?>" class="btn btn-success">登陆</a>
            <a href="<?php echo url('index/user/register'); ?>" class="btn btn-info">注册</a>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if(is_array($comments) || $comments instanceof \think\Collection || $comments instanceof \think\Paginator): $i = 0; $__LIST__ = $comments;if( count($__LIST__)==0 ) : echo "暂时没有评论" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <div class="panel panel-info">
        <div class="panel-body">
            <?php echo $vo['content']; ?>
        </div>
        <div class="panel-footer">
            评论作者:<?php echo $vo->author->phone; ?>
            评论时间:<?php echo $vo['created']; ?>
        </div>
    </div>
    <?php endforeach; endif; else: echo "暂时没有评论" ;endif; ?>
    <?php echo $comments->render(); ?>
</div>


        
<div class="col-md-4">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">阅读排行榜</h3>
        </div>
        <div class="panel-body">
            <ul class="list-group">
                <?php if(is_array($view) || $view instanceof \think\Collection || $view instanceof \think\Paginator): $i = 0; $__LIST__ = $view;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <li class="list-group-item">
                    <a href="<?php echo url('index/news/view',['id'=>$vo['id']]); ?>"><?php echo mb_substr($vo['title'],0,15); ?></a>
                    <span class="badge"><?php echo $vo['view']; ?></span>
                </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
    </div>
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">最新排行榜</h3>
        </div>
        <div class="panel-body">
            <ul class="list-group">
                <?php if(is_array($new) || $new instanceof \think\Collection || $new instanceof \think\Paginator): $i = 0; $__LIST__ = $new;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <li class="list-group-item">
                    <a href="<?php echo url('index/news/view','id='.$vo['id']); ?>">
                        <?php echo mb_substr($vo['title'],0,18); ?>
                    </a>
                </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
    </div>
</div>


    </div>
    <footer class="row footer">
    <div class="container">
        <!-- 页脚头 -->
        <div class="row footer-top">
            <div class="col-md-6">
                <h4>站点介绍</h4>
                <p>ThinkPHP 是一个免费开源的，快速、简单的面向对象的 轻量级PHP开发框架
                    ，创立于2006年初，遵循Apache2开源协议发布，是为了敏捷WEB应用开发和简化企业应用开发而诞生的。ThinkPHP从诞生以来一直秉承简洁实用的设计原则，在保持出色的性能和至简的代码的同时，也注重易用性。并且拥有众多的原创功能和特性，在社区团队的积极参与下，在易用性、扩展性和性能方面不断优化和改进，已经成长为国内最领先和最具影响力的WEB应用开发框架，众多的典型案例确保可以稳定用于商业以及门户级的开发。
                </p>
            </div>
            <div class="col-md-6">
                <div class="col-md-4">
                    <h4>我们</h4>
                    <ul class="list-unstyled">
                        <li><a href="">关于我们</a></li>
                        <li><a href="">公司动态</a></li>
                        <li><a href="">联系我们</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h4>合作</h4>
                    <ul class="list-unstyled">
                        <li><a href="">技术培训</a></li>
                        <li><a href="">广告合作</a></li>
                        <li><a href="">项目合作</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h4>网站</h4>
                    <ul class="list-unstyled">
                        <li><a href="">RSS订阅</a></li>
                        <li><a href="">投稿指南</a></li>
                        <li><a href="">网站帮助</a></li>
                    </ul>
                </div>
                <div class="panel">
                    <form class="form-inline" name="form">
                        <h2 class="form-signin-heading text-center">域名查询</h2>
                        <div class="form-group">
                            <div>
                                <label class="control-label">请输入要查询的域名:</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="sr-only"></label>
                            <input type="text" name="domain" id="domain" value="" class="form-control" placeholder="域名"
                                   autocomplete="off">
                        </div>
                        <div class="form-group">
                            <!--<input type="button" id="doSearch" value="搜索" class="btn btn-primary">-->
                            <!--<input type="submit" value="查询" class="btn btn-primary">-->
                            <input type="button" id="checkDomain" value="查询" class="btn btn-primary">
                        </div>
                    </form>
                </div>
                <!-- 页脚低 -->
                <div class="row footer-bottom">
                    <ul class="list-inline text-center">
                        <li>备案号:京10000xxxxx</li>
                        <li>技术支持:达内PSD1805</li>
                    </ul>
                </div>
            </div>
</footer>

</div>

<script type="text/javascript" src="/public/static/js/jquery-3.3.1.js"></script>
<script type="text/javascript" src="/public/static/bootstrap/js/bootstrap.js"></script>



<script type="text/javascript">
    //当用户点击 查询按钮时
    $('#checkDomain').click(function () {
        var domainVal = document.getElementById('domain').value;

        $.ajax({
            type: 'post',
            url: "<?php echo url('api/api/domain'); ?>",
            data: $('form').serialize(),//表单序列化
            success: function (res) {
                var obj = $.parseJSON(res); //由JSON字符串转换为JSON对象

                if (obj.code == 1) {
                    alert('域名' + ' ' + obj.key + ' ' + obj.message);
                } else if (obj.code == 0) {
                    alert('域名' + ' ' + obj.key + ' ' + obj.message);
                }
            },
            error: function () {
                alert('非常抱歉,服务器出现异常情况,暂时无法登录,给您带来不便,敬请谅解!');
            }
        });
    });

</script>
</body>

</html>