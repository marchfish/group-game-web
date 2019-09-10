<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>邀请好友</title>
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/layer-3.1.1/layer.css'); ?>">
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/jquery-2.2.4/jquery.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/layer-3.1.1/layer.js'); ?>"></script>
</head>

<style>
    ul li{
        width: 50%;
        list-style-type:none;
    }
    a {
        color: #8C8C8C;
        font-size: 12pt;
    }
    img{ pointer-events: none !important; }
    .nav-tabs {
        border-bottom: 0;
    }
    .nav-tabs>li>a {
        border: 0;
    }
    .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover {
        border: 0;
    }
    ul .active a {
        color: #FF6501 !important;
    }
    ul .active .rider:before {
        display:block;
        position: absolute;
        content:"";
        background: #FF6501;
        left: 20%;
        width: 5px;
        height: 5px;
        top: 45%;
        border-radius: 45px;
    }
    ul .active .rider:after {
        display:block;
        position: absolute;
        content:"";
        background: #FF6501;
        right: 20%;
        width: 5px;
        height: 5px;
        top: 45%;
        border-radius: 45px;
    }
    ul .active .friend {
        color: #642AF9 !important;
    }
    ul .active .friend:before {
        display:block;
        position: absolute;
        content:"";
        background: #642AF9;
        left: 20%;
        width: 5px;
        height: 5px;
        top: 45%;
        border-radius: 45px;
    }
    ul .active .friend:after {
        display:block;
        position: absolute;
        content:"";
        background: #642AF9;
        right: 20%;
        width: 5px;
        height: 5px;
        top: 45%;
        border-radius: 45px;
    }
    /*ul .active:after{*/
        /*display:block;*/
        /*content:"";*/
        /*background: #1490f7;*/
        /*width: 100%;*/
        /*height: 4px;*/
    /*}*/
    .row {
        margin: 0;
    }
    .share {
        margin: 15px 0;
    }
    .nav-tabs>li>a {

    }
    .share ul li{
        float: left;
        width: 30%;
    }

    .share ul li img{
        max-width: 40px;
        margin-bottom: 8px;
    }

    .share ul li p{
        font-size: 9pt;
    }
    .success-invitation {
        background: #FEF5E4;
        height: 45px;
        line-height: 45px;
        font-size: 12pt;
        color: #FF6501;
        border-radius:20px 20px 0px 0px;
    }
    .success-content {
        background: #FEF5E4;
        padding: 20px 0;
        margin-bottom: 30px !important;
    }
    .success-content ul,li{
        padding: 0;
        margin: 0;
    }
    .success-content ul li {
        width: 100%;
        padding: 0 12%;
    }
    .success-content ul li span{
        color: #333;
    }
    .popup-button {
        position: absolute;
        height: 50px;
        width: 25%;
        /*background: #1E9FFF;*/
        right: 0;
        top: 1%;
    }
    .shelter {
        max-height: 270px;
        overflow: hidden;
    }
    .more {
        width: 100%;
        display: inline-block;
        text-align: center;
        margin-top: 10px;
    }
</style>
<body>
<!-- Nav tabs -->
<ul class="nav nav-tabs text-center" role="tablist" style="width: 100%">
    <?php if($type == 'pp'): ?>
        <li class="nav-item active">
            <a class="nav-link rider" data-toggle="tab" href="#rider">推荐骑手</a>
        </li>
        <li class="nav-item">
            <a class="nav-link friend" data-toggle="tab" href="#friend">邀请好友</a>
        </li>
    <?php else: ?>
        <li class="nav-item active">
            <a class="nav-link friend" data-toggle="tab" href="#friend">邀请好友</a>
        </li>
        <li class="nav-item rider">
            <a class="nav-link rider" data-toggle="tab" href="#rider">推荐骑手</a>
        </li>
    <?php endif; ?>
</ul>
<!-- Tab panes -->
<div class="tab-content">
    <div id="friend" class="row tab-pane <?php echo $type == 'pp' ? 'fade' : 'active'; ?>" style="position: relative">
        <div class="popup-button" data-name="活动规则"></div>
        <img width="100%" src="<?php echo URL::asset('forestage/invite/img/banner01.png?v=0.0.1'); ?>" alt="">
        <div class="row text-center share">
            <ul>
                <li id="share-wx" class="share-button" data-name="微信" style="margin-left: -1%">
                    <img src="<?php echo URL::asset('forestage/invite/img/wx.png?v=0.0.1'); ?>" alt="">
                    <p>分享到微信</p>
                </li>
                <li id="share-pyq" class="share-button" data-name="朋友圈">
                    <img src="<?php echo URL::asset('forestage/invite/img/pyq.png?v=0.0.1'); ?>" alt="">
                    <p>分享到朋友圈</p>
                </li>
                <li id="share-code" class="share-button" data-name="扫码">
                    <img src="<?php echo URL::asset('forestage/invite/img/code.png?v=0.0.1'); ?>" alt="">
                    <p>扫码邀请</p>
                </li>
            </ul>
        </div>
    </div>
    <div id="rider" class="row tab-pane <?php echo $type == 'pp' ? 'active' : 'fade'; ?>" style="position: relative">
        <div class="popup-button" data-name="奖励规则"></div>
        <img width="100%" src="<?php echo URL::asset('forestage/invite/img/banner02.png?v=0.0.1'); ?>" alt="">
        <div class="row text-center share">
            <ul>
                <li id="share-wx1" class="share-button" data-name="微信" style="margin-left: -1%">
                    <img src="<?php echo URL::asset('forestage/invite/img/wx.png?v=0.0.1'); ?>" alt="">
                    <p>分享到微信</p>
                </li>
                <li id="share-pyq1" class="share-button" data-name="朋友圈">
                    <img src="<?php echo URL::asset('forestage/invite/img/pyq.png?v=0.0.1'); ?>" alt="">
                    <p>分享到朋友圈</p>
                </li>
                <li id="share-code1" class="share-button" data-name="扫码">
                    <img src="<?php echo URL::asset('forestage/invite/img/code.png?v=0.0.1'); ?>" alt="">
                    <p>扫码邀请</p>
                </li>
            </ul>
        </div>
    </div>

    <div class="row" style="margin: 0 5%">
        <div class="col-xs-5 success-invitation text-center">成功推荐</div>
    </div>
    <div class="row success-content" style="margin: 0 5%">
        <ul class="show-info <?php echo count($rows) > 10 ? 'shelter' : ''; ?>">
            <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="row" style="margin-bottom: 8px">
                    <span style="float: left"><?php echo $row->tel; ?></span>
                    <span style="float: right"><?php echo $row->created_at; ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($rows->isEmpty()): ?>
                <li class="row text-center" style="margin-bottom: 8px;margin-top: 5px">
                    <span>还没推荐到新人哦，赶紧分享吧~</span>
                </li>
            <?php endif; ?>
        </ul>
        <span class="more <?php echo count($rows) <= 10 ? 'hide' : ''; ?>"><u>点击查看更多</u></span>
    </div>
</div>
</body>
<script>
    $(function () {
        // $('.share-button').on('click', function (e) {
        //     e.preventDefault();
        //     alert($(this).data('name'));
        // })

        $('.popup-button').on('click', function (e) {
            e.preventDefault();
            var rule1 =  '<p class="text-center">规则说明:</p>';
            var rule2 =  '<p class="text-center">规则说明:</p>';
            //页面层
            layer.open({
                type: 1,
                title:$(this).data('name'),
                skin: 'layui-layer-rim', //加上边框
                area: ['100%', '50%'], //宽高
                content: $(this).data('name') == "活动规则" ? rule1 : rule2,
            });
        })

        // 显示更多
        $('.more').on('click', function (e) {
            e.preventDefault();
            $('.show-info').prop('className', 'show-info');
            $('.more').prop('className', 'more hide');
        })
    })
</script>
</html>
<?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/web/public/invite/index.blade.php ENDPATH**/ ?>