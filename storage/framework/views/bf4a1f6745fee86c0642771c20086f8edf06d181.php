<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>公司提现</title>
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('asset/public/bootstrap-3.4.1/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('asset/invite/css/bootstrap-datetimepicker.min.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('asset/public/layer-3.1.1/layer.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('asset/public/wr-1.0.0/css/wr-css.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('asset/stat/css/stat.css'); ?>">
    <script type="text/javascript" src="<?php echo URL::asset('asset/public/jquery-2.2.4/jquery.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('asset/public/bootstrap-3.4.1/js/bootstrap.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('asset/invite/js/bootstrap-datetimepicker.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('asset/invite/js/bootstrap-datetimepicker.zh-CN.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('asset/public/jquery-ias-2.2.2/jquery-ias.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('asset/public/layer-3.1.1/layer.js'); ?>"></script>
</head>
<style>
    .layui-layer-dialog .layui-layer-padding
    {
        color: #333 !important;
    }
</style>
<body>
<div style="background: #515884;height: 195px;">
    <div id="withdraw" class="row wr-bg-01">
        <div id="date-picker-income" class="text-left">
            <h3 class="wr-h3" style="color: #fff;margin-right: 2px;"><span class="wr-square"></span><?php echo $data['name']; ?></h3>
            
        </div>
    </div>
    <div class="text-center wr-top-20 single wr-padding-bottom-30 wr-padding-top-20">
        <h2 class="wr-top-30">￥<?php echo rmb($data['store_money']); ?><span class="wr-meta">元</span></h2>
        <span style="color: #B2B2B3;font-size: 13pt">可提现金额</span>
        <div class="row wr-top-20 wr-flex">
            <span class="spot wr-item-01">上</span>
            <span class="wr-item-01">次</span>
            <span class="wr-item-01">提</span>
            <span class="wr-item-01">现</span>
            <span class="wr-item-01">额</span>
            <hr class="wr-hr" />
            <span class="wr-item-03"><?php echo rmb($data['cash_last']); ?></span>
            <span class="wr-item-03">元</span>
        </div>
        <div class="row wr-top-20 wr-flex ">
            <span class="spot wr-item-01">已</span>
            <span class="wr-item-01">提</span>
            <span class="wr-item-01">现</span>
            <span class="wr-item-01">总</span>
            <span class="wr-item-01">额</span>
            <hr class="wr-hr" />
            <span class="wr-item-03"><?php echo rmb($data['store_tobank']); ?></span>
            <span class="wr-item-03">元</span>
        </div>
    </div>
</div>
<div class="row wr-top-160" style="vertical-align: middle">
    <form>
        <input  type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
    </form>
    <div class="wr-button-01 text-center <?php echo $data['store_money']>=10000 ? '' : 'wr-bg-color-afa'; ?>">
        <?php echo $data['store_money']>=10000 ? '申请提现' : '满100元可提现'; ?>

    </div>
    <div class="text-center wr-top-25 history-link" style="color: #B2B2B3;font-size: 12pt">查看提现历史<img style="margin-left: 6px;" src="<?php echo URL::asset('asset/stat/img/chevron.png?v=0.0.1'); ?>" alt="" width="5px"></div>
</div>
</body>
<script>
    $(function () {
        var url = "<?php echo build_qs( Request::all() ); ?>";
        $('.history-link').on('click',function () {
            window.location.href="/account-company/withdraw/history?" + url;
        })

        // 申请提现
        $(".wr-button-01").on('click', function(e){

            e.preventDefault();

            if ($(this).hasClass("wr-bg-color-afa")){
                return;
            }

            var layer_div = layer.msg('正在处理中，请稍候...', {
                icon: 16,
                shade: 0.01,
                time:false
            });

            $.ajax({
                type:"post",
                url:"/account-company/withdraw?" + url,
                data:$('form').serialize(),
                success:function(data){
                    layer.close(layer_div);
                    alert(data.message);
                    window.location.href="/account-company/withdraw?" + url;
                },
                error:function(jqXHR){
                    layer.close(layer_div);
                    console.log("Error: "+jqXHR.status);
                }
            });
        });
    })
</script>
</html><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/web/group/withdraw.blade.php ENDPATH**/ ?>