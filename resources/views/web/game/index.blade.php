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
    <p>　　<input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="上" />　　　　　<input type="button" class="action" data-url="{!! URL::to('game/attack') !!}" value="攻击">　<input type="button" class="action" data-url="{!! URL::to('user-knapsack') !!}" value="背包">　<input type="button" class="action" data-url="{!! URL::to('mission/user') !!}" value="任务">　<input type="button" class="action" data-url="{!! URL::to('equip') !!}" value="装备"></p>
    <p> <input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="左" /> 　　<input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="右" /> </p>
    <p>　　<input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="下" />　　　　　<input type="button" class="action" data-url="{!! URL::to('user/role') !!}" value="状态">　<input type="button" class="action" data-url="{!! URL::to('game/location') !!}" value="位置">　<input type="button" value="挂机1">　<input type="button" value="挂机2"></p>
    {{--<p> <input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="下" /></p>--}}
</div>
</body>
<script>
    $(function () {
        var timestamp = Date.parse(new Date());
        var token = "{!! csrf_token() !!}";
        // 动作
        $(document).on('click', '.action', function(e){
            e.preventDefault();
            var now_timestamp = Date.parse(new Date());

            if (now_timestamp - timestamp < 1000) {
                return ;
            };

            $.ajax({
                type:"get",
                url:$(this).data('url'),
                data:{
                    action : $(this).val(),
                },
                success:function(res){
                    if (res.message == "") {
                        return ;
                    }
                    $(".xianshiquyu").html(res.message);
                    timestamp = Date.parse(new Date());
                },
                error:function(jqXHR){
                    layer.close(layer_div);
                    console.log("Error: "+jqXHR.status);
                }
            });
        });

        // post
        $(document).on('click', '.action-post', function(e){
            e.preventDefault();
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
                    layer.close(layer_div);
                    console.log("Error: "+jqXHR.status);
                }
            });
        });
    });
</script>
</html>
{{--var token ="{!! csrf_token() !!}";--}}
