<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>修改密码</title>
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/wr-1.0.0/css/wr-css.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/layer-3.1.1/layer.css') !!}">
    <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-2.2.4/jquery.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/layer-3.1.1/layer.js') !!}"></script>
</head>
<body>
<div class="row wr-top-20">
    <div style="margin-left: 10%">
        <form id="js-form" action="{!! URL::to('update') !!}">
            <ul>
                <input  type="hidden" name="_token" value="{!! csrf_token() !!}">
                <li>邮　　箱： <input type="email" name="email" placeholder="请输入邮箱"><button class="get-code">获取验证码</button></li>
                <li>
                    验 证 码： <input type="text" name="verify_code" placeholder="请输入验证码">必填
                </li>
                <li>新 密 码： <input type="password" name="password" placeholder="请输入密码">必填</li>
                <li>确认密码： <input type="password" name="password1" placeholder="请在输入一遍密码">必填</li>
                <li>
                    <input type="submit" value="确认修改">
                    <input type="reset" value="　重置　">
                </li>
            </ul>
        </form>
    </div>
</div>
<script>
    $(function () {
        var count = 0;
        // 获取验证码
        $(".get-code").on('click', function(e){
            e.preventDefault();
            if (count === 0) {
                var layer_div = layer.msg('正在处理中，请稍候...', {
                    icon: 16,
                    shade: 0.01,
                    time:false
                });
                $.ajax({
                    type:"post",
                    url:"/send-verify-code",
                    data:$('form').serialize(),
                    success:function(res){
                        layer.close(layer_div);
                        if (res.code === 200 || res.message === "成功") {
                            count = 60;
                            $('.get-code').prop('className', 'get-code');
                            settime();
                        }else {
                            var layer_div1 = layer.msg(res.message, {
                                icon: 0,
                                shade: 0.01,
                                time: 3000,
                            });
                        }
                    },
                    error:function(jqXHR){
                        layer.close(layer_div);
                        console.log("Error: "+jqXHR.status);
                    }
                });
            }
        });

        // 时间倒计时
        function settime() {

            $('.get-code').html("已发送 " + count + "s");

            if (count<=0) {
                count = 0;
                $('.get-code').prop('className', 'get-code');
                $('.get-code').html("获取验证码");
                return;
            }

            count -- ;

            setTimeout(function() {
                settime()
            }, 1000)
        }


        $('#js-img').on('click', function (ev) {
            this.src = $(this).data('src') + '?m=' + Math.random();
        }).click();

        // 登录
        $('#js-form').on('submit', function (ev) {
            ev.preventDefault();
            $.ajax({
                type:"post",
                url: this.action,
                data: $(this).serialize(),
                success:function(res){
                    alert(res.message);
                    if (res.redirect_url) {
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