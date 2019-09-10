<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>用戶登录</title>
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/wr-1.0.0/css/wr-css.css') !!}">
    <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-2.2.4/jquery.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js') !!}"></script>
</head>
<body>
<div class="row text-center wr-top-20">
    <form id="js-form" action="{!! URL::to('login') !!}">
        <ul>
            <input  type="hidden" name="_token" value="{!! csrf_token() !!}">
            <li>账户：<input type="text" name="username" required="required" placeholder="请输入账号"></li>
            <li>密码：<input type="password" name="password" placeholder="请输入密码"></li>
            <li>
                验证码：<input type="text" name="captcha" placeholder="请输入验证码">
            </li>
            <li>
                <img id="js-img" style="cursor: pointer;" data-src="{!! URL::to('captcha') !!}">
            </li>
            <li><input type="submit" value="登录"> <a href="注册.html">注册用戶</a></li>
        </ul>
    </form>
</div>
<script>
    $(function () {
        $('#js-img').on('click', function (ev) {
            this.src = $(this).data('src') + '?m=' + Math.random();
        }).click();

        $('#js-form').on('submit', function (ev) {
            ev.preventDefault();
            $.ajax({
                type:"post",
                url: this.action,
                data: $(this).serialize(),
                success:function(data){
                    if (data.code !== 200) {
                        alert(data.message);
                    }
                    if (data.redirect_url) {
                        window.location.href = res.redirect_url;
                    }
                },
                error:function(jqXHR){
                    console.log("Error: "+jqXHR.status);
                }
            });
        });
    });
</script>
</body>
</html>