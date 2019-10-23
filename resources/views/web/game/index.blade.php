<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>界面</title>
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/wr-1.0.0/css/wr-css.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/layer-3.1.1/layer.css') !!}">
    <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-2.2.4/jquery.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/layer-3.1.1/layer.js') !!}"></script>
</head>
<style>
    html,body {
        height: 100%;
    }
    .xianshiquyu {
        width: 100%;
        max-width: 600px;
        height: 50%;
        border-width: 1px;
        border-color: pink;
        border-style: solid;
        overflow-y: auto;
        padding: 10px 10px;
        margin-bottom: 15px;
    }
    input {
        margin-bottom: 5px;
    }
</style>
<body>
<div class="row" style="height: 100%">
    <div class="xianshiquyu">
        欢迎来到开荒之路~<br>
        感谢开发者的共同努力:<br>
        marchfish<br>
        明天老子不上班<br>
        青酥<br>
        若伊<br>
        冤有头债有主<br>
        Lumina<br>
    </div>
    <div class="row">
        <p>　　<input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="上" />　　<input type="button" class="action" data-url="{!! URL::to('game/attack') !!}" value="攻击">　<input type="button" class="action" data-url="{!! URL::to('user-knapsack') !!}" value="背包">　<input type="button" class="action" data-url="{!! URL::to('mission/user') !!}" value="任务">　<input type="button" class="action" data-url="{!! URL::to('equip') !!}" value="装备"></p>
    </div>
    <div class="row">
        <p> <input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="左" /> 　　<input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="右" /> </p>
    </div>
    <div class="row">
        <p><input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="前" /> <input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="下" /> <input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="后" />　 <input type="button" class="action" data-url="{!! URL::to('user/role') !!}" value="状态">　<input type="button" class="action" data-url="{!! URL::to('game/location') !!}" value="位置">　<input type="button" class="action" data-url="{!! URL::to('item/recycle-show') !!}" value="回收" /></p>
    </div>
    <div class="row">
      会员功能：
      <p>
          <input type="button" class="action" data-url="{!! URL::to('vip-show') !!}" value="会员"/>
          <input type="button" class="action" data-url="{!! URL::to('shop-mall') !!}" value="商城"/>
          <input type="hidden" id="auto-attack" class="action" data-url="{!! URL::to('vip/auto-attack') !!}" value="攻击">
          <input type="button" class="auto-attack" value="自动攻击"/>
          <input type="button" class="action" data-url="{!! URL::to('vip/on-hook') !!}" value="挂机经验"/>　
          <input type="button" class="action" data-url="{!! URL::to('vip/on-hook') !!}" value="挂机金币"/>
          <input type="button" class="action" data-url="{!! URL::to('end-hook') !!}" value="结束挂机"/>
          <input type="button" class="action" data-url="{!! URL::to('warehouse') !!}" value="仓库"/>
          <input type="button" class="action" data-url="{!! URL::to('warehouse/user-knapsack-show') !!}" value="存入仓库"/>
      </p>
    </div>
    <div class="row">
        <a class="btn btn-default" href="{!! URL::to('logout') !!}">退出</a>
    </div>
</div>
</body>
<script>
    $(function () {
        var timestamp = Date.parse(new Date());
        var token = "{!! csrf_token() !!}";
        var autoAtt = null;
        // 动作
        $(document).on('click', '.action', function(e){
            e.preventDefault();
            if (autoAtt && $(this).val() != "攻击") {
                $(".xianshiquyu").html("请先结束自动攻击");
                return;
            }
            var now_timestamp = Date.parse(new Date());
            var actionName = $(this).val();
            var var_data = null;

            if (now_timestamp - timestamp < 1000 && actionName == "攻击") {
                return ;
            };

            if(actionName == "回收" || actionName == "购买" || actionName == "存入" || actionName == "取出") {
                var_data = $(this).parent().find(".js-num").val();
                if(var_data < 1) {
                    var_data = 1;
                }
            }

            $.ajax({
                type:"get",
                url:$(this).data('url'),
                data:{
                    action : $(this).val(),
                    var_data : var_data,
                },
                success:function(res){
                    if (res.message == "") {
                        return ;
                    }
                    $(".xianshiquyu").html(res.message);
                    timestamp = Date.parse(new Date());
                },
                error:function(jqXHR){
                    console.log("Error: "+jqXHR.status);
                }
            });
        });

        // post
        $(document).on('click', '.action-post', function(e){
            e.preventDefault();
            if (autoAtt) {
                $(".xianshiquyu").html("请先结束自动攻击");
                return;
            }
            var now_timestamp = Date.parse(new Date());

            if (now_timestamp - timestamp < 1000) {
                return ;
            };

            $.ajax({
                type:"post",
                url:$(this).data('url'),
                data:{
                    action : $(this).val(),
                    _token : token,
                },
                success:function(res){
                    if (res.message == "") {
                        return ;
                    }
                    $(".xianshiquyu").html(res.message);
                    timestamp = Date.parse(new Date());
                },
                error:function(jqXHR){
                    console.log("Error: "+jqXHR.status);
                }
            });
        });

        // 自动攻击
        $('.auto-attack').on('click', function (e) {
            e.preventDefault();
            if ($(this).val() === "自动攻击") {
                $(".xianshiquyu").html("自动攻击已开启...");
                $(this).val("结束自动攻击");
                autoAtt = setInterval(autoAttack, 2000);
            }else {
                $(".xianshiquyu").html("自动攻击关闭");
                $(this).val("自动攻击");
                clearInterval(autoAtt);
                autoAtt = null;
            }
        })
        function autoAttack() {
            $('#auto-attack').click();
        }
    });

    $(function(){
        // 数量加减
        $(document).on('click', '.add', function(e){
            var t = $(this).parent().find(".js-num");
            t.val(parseInt(t.val())+1);
            setTotal(t);
        });
        $(document).on('click', '.minus', function(e){
            var t = $(this).parent().find(".js-num");
            t.val(parseInt(t.val())-1);
            setTotal(t);
        });
        function setTotal(t){
            var tt = t.val();
            if(tt<=0){
                t.val(parseInt(t.val())+1)
            }
        };

        // 输入框限制
        var $numInput = $('#js-num');
        $numInput.on('input', function (ev) {
            $numInput.val($numInput.val().replace('+86', '').replace('-', '').replace(/[^0-9]/g, '').substring(0, 11));
        });
    })
</script>
</html>
{{--var token ="{!! csrf_token() !!}";--}}
