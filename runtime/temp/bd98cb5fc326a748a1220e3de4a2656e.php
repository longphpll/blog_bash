<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:64:"D:\web\zijiBlog\public/../application/index\view\user\login.html";i:1551419005;s:55:"D:\web\zijiBlog\application\index\view\public\base.html";i:1546248022;s:57:"D:\web\zijiBlog\application\index\view\public\header.html";i:1551268041;s:57:"D:\web\zijiBlog\application\index\view\public\footer.html";i:1546247272;}*/ ?>
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
    
<link rel="stylesheet" type="text/css" href="/public/static/css/Login.css" />



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
        

        
<div class="panel panel-info" style="margin:0 15px;">
    <div class="panel-heading">
        <h1 class="panel-title">用户登陆</h1>
    </div>
    
    <div class="panel-body">
        <form name="form1" id="logoin" class="form-horizontal">
            <div class="form-group">
                <div class="col-md-2">
                    <label class="control-label">手机号码:</label>
                </div>
                <div class="col-md-5">
                    <input type="text" name="phone" id="phone" value="" class="form-control" placeholder="请输入手机号"
                           autofocus autocomplete="off">
                </div>
                <div class="col-md-5">
                    <span class="help-block"></span>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-2">
                    <label class="control-label">图形验证码:</label>
                </div>
                <div class="col-md-5">
                    <input autocomplete="off" type="text" name="captcha" id="captchad" value="" class="form-control"
                           placeholder="请输入验证码">
                </div>
                <div class="col-md-5">
                    <img src="<?php echo url('index/user/verify'); ?>" width="140" alt="captcha" id="captcha">
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-2">
                    <label class="control-label">登录密码:</label>
                </div>
                <div class="col-md-5">
                    <input type="password" name="password" id="password" value="" class="form-control"
                           placeholder="请输入登录密码" disabled>
                </div>
                <div class="col-md-5">
                    <input type="button" id="smsVerificationCode" class="clickVerificationCode" value="短信获取登录密码"
                           disabled>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-2">
                    <label class="control-label"></label>
                </div>
                <div class="col-md-5">
                    <div class="message" id="message" style="visibility: hidden; ">121212</div>
                </div>
                <div class="col-md-5">
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-10 col-md-offset-2">
                    
                    <input type="button" id="logoin_btn" value="登录" class="btn btn-primary" onclick="check()" disabled>
                </div>
            </div>
        </form>
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


<!--<script type="text/javascript">-->
<!--//1.获取对象-->
<!--var c = document.getElementById('captcha');-->
<!--//2. 刷新图形验证码 操作对象(点击事件触发)-->
<!--c.onclick = function () {-->
<!--this.src = "<?php echo url('index/user/verify'); ?>?rand=" + Math.random();-->
<!--}-->

<!--function check() {-->
<!--console.log('check');-->
<!--$.ajax({-->
<!--type: 'post',-->
<!--url: "<?php echo url('index/user/doLogin'); ?>",-->
<!--data: $('#logoin').serialize(),-->
<!--success: function (res) {-->
<!--window.alert(res.msg);-->
<!--},-->
<!--error: function () {-->
<!--alert('非常抱歉,服务器出现异常情况,暂时无法登录,给您带来不便,敬请谅解!');-->
<!--}-->
<!--});-->
<!--}-->
<!--</script>-->


<script type="text/javascript">
    //刷新图形验证码
    //1.获取对象
    var c = document.getElementById('captcha');
    //2. 刷新图形验证码 操作对象(点击事件触发)
    c.onclick = function () {
// console.log('刷新图形验证码');
        this.src = "<?php echo url('index/user/verify'); ?>?rand=" + Math.random();
    }

    //以下为验证用户输入的 手机号码 图形验证码 短信验证码

    //获取信息提示框div对象
    var messageEle = document.getElementById('message');
    //手机号码 正则
    var phoneReg = /^(\+86 )?1(3\d|4[57]|5[0-357-9]|7[0367]|8[0-35-9])\d{8}$/;
    var isPhone = 0;//发送短信的按钮是否解除禁用
    var isCaptcha = 0;//发送短信的按钮是否解除禁用

    //当手机号码输入框获取焦点时,提示用户 请输入手机号码
    var phoneFirst = 0;
    $('#phone').focus(function () {
        phoneFirst = 1;
        console.log("手机号码输入框 获取焦点了");
        // var phoneVal = document.getElementById('phone').value;
        if ($('#phone').val() == '') {
            messageEle.style.visibility = 'visible';
            messageEle.innerHTML = '请输入手机号码';
        }
    });

    //当手机号码输入框 失去焦点时
    $('#phone').blur(function () {
        phone();
    });

    //当图形验证码输入框 获取焦点时
    $('#captchad').focus(function () {
        console.log('图形验证码获取焦点');
        // var captchaVal = document.getElementById('captchad').value;
        if ($('#captchad').val() == '') {
            phone();
        }
    });

    //当图形验证码输入框 失去焦点时,通过ajax验证码用户输入的图形验证码是否正确
    $('#captchad').blur(function () {
        console.log('图形验证码失去焦点');
        checkCaptcha();
    });

    //通过 ajax 验证用户输入的 图形验证码是否正确
    function checkCaptcha() {
        var captchaVal = $('#captchad').val();
        $.ajax({
            type: 'post',
            url: "<?php echo url('index/user/checkCaptcha'); ?>",
            data: {'captchaVal': captchaVal},
            success: function (res) {
                var obj = $.parseJSON(res); //由JSON字符串转换为JSON对象
                console.log(captchaVal);
                console.log(obj);
                // {code: 0, msg: "验证码非法,请重新输入"}
                if (obj.code == 0) { //当为0代表图形验证码输入错误
                    $('#message').html(obj.msg);
                    isCaptcha = 0;
                    if (!(isCaptcha && isPhone)) {
                        $('#smsVerificationCode').prop('disabled', true);
                        $('#smsVerificationCode').css({'background': '#ddd', 'color': '#666'})//背景颜色改为灰色
                    }
                } else if (obj.code == 1) {
                    isCaptcha = 1;
                    //如果手机号码和图形验证码都正确将短信发送按钮禁用 解除
                    if (isPhone && isCaptcha) {
                        $('#smsVerificationCode').removeAttr('disabled');
                        $('#smsVerificationCode').css({'background': '#1d8063', 'color': '#661c10'})//背景颜色改为绿色
                        messageEle.innerHTML = '请获取登录密码';
                    }
                }
            },
            error: function () {
                alert('非常抱歉,服务器出现异常情况,暂时无法登录,给您带来不便,敬请谅解!');
            }
        });
    }

    function phone() {
        console.log("手机号码输入框 失去焦点了");
        var phoneVal = document.getElementById('phone').value;
        // console.log(phoneVal);
        var res = phoneReg.test(phoneVal.trim());//去掉空格
        // console.log(res);
        //如果正则不通过说明用户输入的手机号码不正确,提示用户
        if (!res) {
            $('#message').html('请输入正确的手机号码');
            isPhone = 0;
        } else {
            isPhone = 1;
            //如果正则通过说明用户输入的手机号码正确,提示用户 输入下面的图形验证码
            $('#message').css('visibility', 'visible');
            $('#message').html('请输入图形验证码');
        }
    }

    //发送短信验证码 调用Api控制器下的 smsSend() 方法, 该方法返回json字符串

    // $(function () {
    //     $('#smsVerificationCode').click(function () {
    //         $.ajax({
    //             type: 'post',
    //             url: "<?php echo url('api/api/smsSend'); ?>",
    //             data: {'phone': $('#phone').val()},
    //             success: function (res) {
    //                 //后端返回的json字符串在js里要转换,否则 msg 的值看不懂
    //                 // console.log(res);
    //                 //{"SubmitResult":{"code":"403","msg":"\u624b\u673a\u53f7\u7801\u4e0d\u80fd\u4e3a\u7a7a","smsid":"0"}}
    //
    //                 var obj = $.parseJSON(res); //由JSON字符串转换为JSON对象
    //                 // console.log(obj);
    //                 //SubmitResult:
    //                 // code: "40722"
    //                 // msg: "变量内容超过指定的长度"
    //                 // smsid: "0"
    //
    //                 if (obj.code != 2) { //发送短信失败
    //                     //正式使用时注释掉,用下面的提示消息
    //                     window.alert(obj.msg); //如果 != 2 说明发送失败，obj.msg 可以看短信平台返回的失败信息
    //                     // window.alert('发送短信失败，请稍后再试！！！！');
    //                 } else {
    //                     //时间倒计时；方式一 setInterval()
    //                     var startTime = 300;
    //                     var timeId = setInterval(function () {
    //                         $('#smsVerificationCode').val('发送成功' + '(' + startTime + '秒)');
    //                         startTime--;
    //                         if (startTime < 0) {
    //                             clearInterval(timeId); //停止定时器
    //                             $('#smsVerificationCode').val('请重新发送短信验证码');
    //                             $('#smsVerificationCode').removeAttr('disabled');//发送短信的按钮解除禁用
    //                             $('#smsVerificationCode').css({'background': '#1d8063', 'color': '#661c10'})//背景颜色改为绿色
    //
    //                             $('#logoin_btn').attr('disabled', true);//禁用登录按钮
    //                         } else {
    //                             $('#smsVerificationCode').attr('disabled', 'true');//发送成功将按钮设为禁用，避免用户再次发送
    //                             $('#smsVerificationCode').css({'background': '#ddd', 'color': '#666'})//背景颜色改为灰色
    //                             $('#password').removeAttr('disabled');//短信发送成功后,输入框 解除禁用
    //                             $('#logoin_btn').removeAttr('disabled');//将登录按钮 解除禁用
    //                         }
    //                     }, 1000);
    //                 }
    //
    //             }
    //         });
    //     });
    // });

    //测试使用,正式使用时注释掉
    $(function () {
        $('#smsVerificationCode').click(function () {
            if (2 != 2) { //发送短信失败
// window.alert('发送失败'); //如果 != 2 说明发送失败，obj.msg 可以看短信平台返回的失败信息
                window.alert('发送短信失败，请稍后再试！！！！');
            } else {
//时间倒计时；方式一 setInterval()
                var startTime = 300;
                var timeId = setInterval(function () {

//获取 button 按钮的值 两种方式 js和jquery
//方式一：js
// var smsVerificationCodeEle = document.getElementById('smsVerificationCode');
// smsVerificationCodeEle.value = startTime + '后重新获取';

//方式二：jquery
                    $('#smsVerificationCode').val('发送成功' + '(' + startTime + '秒)');
                    startTime--;
                    if (startTime < 0) {
                        clearInterval(timeId); //停止定时器
                        $('#smsVerificationCode').val('请重新发送短信验证码');
                        $('#smsVerificationCode').removeAttr('disabled');//发送短信的按钮解除禁用
                        $('#smsVerificationCode').css({'background': '#1d8063', 'color': '#661c10'})//背景颜色改为绿色

                        $('#logoin_btn').attr('disabled', true);//禁用登录按钮
                    } else {
                        $('#smsVerificationCode').attr('disabled', 'true');//发送成功将按钮设为禁用，避免用户再次发送
                        $('#smsVerificationCode').css({'background': '#ddd', 'color': '#666'})//背景颜色改为灰色
                        $('#password').removeAttr('disabled');//短信发送成功后,输入框 解除禁用
                        $('#logoin_btn').removeAttr('disabled');//将登录按钮 解除禁用
                    }
                }, 1000);
            }
        });
    });

    //通过ajax在后台验证用户输入的信息是否正确,正确的话进入首页
    function check() {
        console.log('check');
        $.ajax({
            type: 'post',
            url: "<?php echo url('index/user/doLogin'); ?>",
            data: $('#logoin').serialize(),
            success: function (res) {
                // console.log(res);
                window.alert(res.msg);//登录成功
                if (res.code == 1) { //如果验证都通过,进入首页
                    window.location.href = "<?php echo url('index/index/index'); ?>";
                }
            },
            error: function () {
                alert('非常抱歉,服务器出现异常情况,暂时无法登录,给您带来不便,敬请谅解!');
            }
        });
    }

</script>




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