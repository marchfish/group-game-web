<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>界面</title>
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/wr-1.0.0/css/wr-css.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/layer-3.1.1/layer.css'); ?>">
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/jquery-2.2.4/jquery.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/layer-3.1.1/layer.js'); ?>"></script>
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
        欢迎来到开荒之路~
    </div>
    <p>　　<input type="button" class="action" data-url="<?php echo URL::to('game/up'); ?>" value="上" />　　　　　<input type="button" value="攻击">　<input type="button" value="背包">　<input type="button" value="任务">　<input type="button" value="拍卖行"></p>
    <p> <input type="button" value="左" /> 　　<input type="button" value="右" /> </p>
    <p>　　<input type="button" class="action" data-url="<?php echo URL::to('game/down'); ?>" value="下" />　　　　　<input type="button" value="状态">　<input type="button" class="action" data-url="<?php echo URL::to('game/location'); ?>" value="位置">　<input type="button" value="挂机1">　<input type="button" value="挂机2"></p>
</div>
</body>
<script>
    $(function () {
        // 动作
        $(".action").on('click', function(e){
            e.preventDefault();
            $.ajax({
                type:"get",
                url:$(this).data('url'),
                data:$(this).val(),
                success:function(res){
                    $(".xianshiquyu").html(res.message);
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
<?php /**PATH /mnt/hgfs/group/server/resources/views/web/game/index.blade.php ENDPATH**/ ?>