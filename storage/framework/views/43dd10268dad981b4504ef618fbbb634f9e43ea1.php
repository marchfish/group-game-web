<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
  <title>邀请好友</title>
  <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css'); ?>">
  <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/layer-3.1.1/layer.css'); ?>">
  <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/wr-1.0.0/css/wr-css.css');; ?>">
  <script type="text/javascript" src="<?php echo URL::asset('forestage/public/jquery-2.2.4/jquery.min.js'); ?>"></script>
  <script type="text/javascript" src="<?php echo URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js'); ?>"></script>
  <script type="text/javascript" src="<?php echo URL::asset('forestage/public/layer-3.1.1/layer.js'); ?>"></script>
</head>
<style>
  html,body {
    height: 100%;
    width: 100%;
  }
  ul li{
    list-style-type:none;
  }
  img{ pointer-events: none !important; }
  .row {
    margin: 0;
  }
  .input-group .form-control {
    height: 50px;
  }
  .input-group-addon {
    border-radius: 40px;
    background-color:#fff;
  }
  .form-control {
    border: 0;
    border-top: 1px solid #ccc;
    border-bottom: 1px solid #ccc;
    -webkit-box-shadow: none;
    box-shadow: none;
    -webkit-transition: none;
    -o-transition: none;
    transition: none;
    box-shadow: none;
  }
  input:focus {
    outline:none !important;
    border-color: #ccc !important;
    box-shadow: 0 0 0px rgba(255, 255, 255, 0) !important;
  }

  input{
    outline: none !important;
  }
  input.custom {
    caret-color: #fff !important;
  }

  input[type="text"] {
    -webkit-appearance: none;
    outline:none !important;
  }
  textarea{
    -webkit-appearance: none;
    outline:none !important;
  }
  .line:after {
    display: block;
    content: "";
    position: absolute;
    top: 12px;
    left: 80px;
    height: 26px;
    width: 1px;
    background: #ccc;
    z-index: 99;
  }
  .get-code {
    background: #FEE400;
    color: #804E03;
  }
  .form-button {
    height: 42px;
    line-height: 42px;
    background: #1490F7;
    font-size: 12pt;
    color: #fff;
    border-radius: 21px;
    margin-top: 20px;
    margin-bottom: 50px;
  }
  .download-button {
    height: 42px;
    line-height: 42px;
    background: #1490F7;
    font-size: 12pt;
    color: #fff;
    border-radius: 21px;
    margin-top: 20px;
    margin-bottom: 50px;
  }
  .check-code {
    background: #d3d3d3 !important;
    color: #4c4c4c !important;
  }
</style>
<body>
<div class="row" style="position: relative">
  <?php if($type == 'pp'): ?>
    <img width="100%" src="<?php echo URL::asset('forestage/invite/img/banner03.png?v=0.0.1'); ?>" alt="">
    <img style="width: 30%; position: absolute;top:5px;right: 10px;" src="<?php echo URL::asset('forestage/invite/img/logo.png?v=0.0.1'); ?>" alt="">
    <img style="width: 70%; position: absolute;top:45px;left: 8%;" src="<?php echo URL::asset('forestage/invite/img/text.png?v=0.0.1'); ?>" alt="">
  <?php else: ?>
    <img width="100%" src="<?php echo URL::asset('forestage/invite/img/banner04.png?v=0.0.1'); ?>" alt="">
  <?php endif; ?>
</div>
<form style="background: url('<?php echo URL::asset('forestage/invite/img/bg-01.png?v=0.0.1'); ?>');background-size: cover;">
  <div class="share">
    <input  type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
    <input type="hidden" name="code" value="<?php echo $code; ?>">
    <div class="row">
      <div class="input-group col-xs-10 col-xs-offset-1" style="margin-top: 20px">
        <span class="input-group-addon line">手机号码</span>
        <input type="text" name="tel" maxlength="11" class="form-control" placeholder="请输入手机号码">
        <span  class="input-group-addon get-code">获取验证码</span>
      </div>
    </div>
    <div class="row">
      <div class="input-group col-xs-10 col-xs-offset-1" style="margin-top: 10px">
        <span class="input-group-addon line">验证码</span>
        <input type="text" maxlength="4" class="form-control" style="padding-left: 26px" name="verify_code" placeholder="请输入短信验证码">
        <span class="input-group-addon"></span>
      </div>
      <div class="col-xs-10 col-xs-offset-1 form-button text-center">立即注册</div>
    </div>
  </div>
  <div class="row">
    <div class="download hide">
      <div class="col-xs-10 col-xs-offset-1 download-button text-center">点击下载App</div>
    </div>
  </div>
</form>
<footer>
</footer>
</body>
<script>
    $(function () {
        var count = 0;
        // 获取验证码
        $(".get-code").on('click', function(e){
            e.preventDefault();
            if (count == 0) {
                var layer_div = layer.msg('正在处理中，请稍候...', {
                    icon: 16,
                    shade: 0.01,
                    time:false
                });
                $.ajax({
                    type:"post",
                    url:"/api/app/v0/verify-code",
                    data:$('form').serialize(),
                    success:function(data){
                        layer.close(layer_div);
                        if (data.message === "发送成功" || data.message === "成功") {
                            count = 60;
                            $('.get-code').prop('className', 'input-group-addon get-code check-code');
                            settime();
                        }else {
                            var layer_div1 = layer.msg(data.message, {
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
                $('.get-code').prop('className', 'input-group-addon get-code');
                $('.get-code').html("获取验证码");
                return;
            }

            count -- ;

            setTimeout(function() {
                settime()
            }, 1000)
        }

        // 立即注册
        $(".form-button").on('click', function(e){
            e.preventDefault();
            var layer_div = layer.msg('正在处理中，请稍候...', {
                icon: 16,
                shade: 0.01,
                time:false
            });
            $.ajax({
                type:"post",
                url:"/invite-share",
                data: $('form').serialize(),
                success:function(data){
                    layer.close(layer_div);
                    var layer_div1 = layer.msg(data.message, {
                        icon: data.code === 200 ? 1 : 0,
                        shade: 0.01,
                        time: 3000,
                    });

                    if (data.code === 200) {
                        $('.share').prop('className', 'share hide');
                        $('.download').prop('className', 'download wr-top-30');
                    }
                },
                error:function(jqXHR){
                    layer.close(layer_div);
                    console.log("Error: "+jqXHR.status);
                }
            });
        });

        // 跳转下载页面
        $(".download").on('click', function(e){
            window.location.href = "https://www.zhaopaopao.net";
        });
    })

</script>
</html><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/web/public/invite/share.blade.php ENDPATH**/ ?>