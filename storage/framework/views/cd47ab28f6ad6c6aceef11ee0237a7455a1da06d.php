<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>我的流水</title>
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/wr-1.0.0/css/wr-css.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/stat/css/stat.css'); ?>">
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/jquery-2.2.4/jquery.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/jquery-ias-2.2.2/jquery-ias.min.js'); ?>"></script>
</head>
<body>
<div style="background: #515884;height: 320px;">
    <div class="row wr-bg-01" style="margin-bottom: 10px">
        <div id="date-picker" class="text-left">
            <h3 class="wr-h3 <?php echo $date_month != '' ? 'wr-color-c3c' : ''; ?>" style="color: #fff"><?php echo $data['date_show']; ?></h3>
            <span class="<?php echo $date_month != '' ? 'caret-c3c' : 'caret'; ?>"></span>
        </div>
        <div style="float: right;display: flex;align-items:center;">
            <span class="wr-date-month <?php echo $date_month == -2 ? 'wr-active-02' : ''; ?>" data-month="-2">今天</span>
            <span class="wr-line-02" style="margin: 0 8px"></span>
            <span class="wr-date-month <?php echo $date_month == -1 ? 'wr-active-02' : ''; ?>" data-month="-1">昨天</span>
            <span class="wr-line-02" style="margin: 0 8px"></span>
            <span class="wr-date-month <?php echo $date_month == 0 && $date_month != '' ? 'wr-active-02' : ''; ?>" data-month="0">本月</span>
            <span class="wr-line-02" style="margin: 0 8px"></span>
            <span class="wr-date-month <?php echo $date_month == 1 ? 'wr-active-02' : ''; ?>" data-month="1">上月</span>
            
            
        </div>
    </div>
    <div class="text-center single wr-top-50">
        <h2 class="wr-top-30">￥<?php echo rmb($data['group_pay_fee'] + $data['ordinary_pay_fee']  + $data['share_amount']); ?><span class="wr-meta">元</span></h2>
        <div class="row wr-top-40 wr-flex">
            <span class="spot wr-item-01">公</span>
            <span class="wr-item-01">司</span>
            <span class="wr-item-01">客</span>
            <span class="wr-item-01">户</span>
            <span class="wr-item-01">单</span>
            <hr class="wr-hr" />
            <span class="wr-item-03"><?php echo rmb($data['group_pay_fee']); ?></span>
            <span class="wr-item-03">元</span>
        </div>
        <div class="row wr-top-30 wr-flex">
            <span class="spot wr-item-01">普</span>
            <span class="wr-item-01">通</span>
            <span class="wr-item-01">用</span>
            <span class="wr-item-01">户</span>
            <span class="wr-item-01">单</span>
            <hr class="wr-hr" />
            <span class="wr-item-03"><?php echo rmb($data['ordinary_pay_fee']); ?></span>
            <span class="wr-item-03">元</span>
        </div>
        <div class="row wr-top-30 wr-flex wr-bottom-50">
            <span class="spot wr-item-01">分</span>
            <span class="wr-item-01">享</span>
            <span class="wr-item-01">奖</span>
            <span class="wr-item-01">励</span>
            <span class="wr-item-01">金</span>
            <hr class="wr-hr" />
            <span class="wr-item-03"><?php echo rmb($data['share_amount']); ?></span>
            <span class="wr-item-03">元</span>
        </div>
    </div>
    <div class="wr-table">
        <div id="account" class="text-center wr-top-10 wr-table-title wr-active-01">流水明细</div>
        <div id="account-analysis" class="text-center wr-top-10 wr-table-title" style="left: 110px">流水分析</div>
    </div>
</div>
<div class="row wr-top-160 wr-content-header">
   <div class="wr-top-20 clearfix" style="padding: 0 6%;">
       <h3 class="wr-h3"><span class="wr-square"></span>流水明细</h3>
       <span style="float: right;color: #B2B2B3;font-size: 12pt">共<?php echo $data['count']; ?>单</span>
   </div>
   <div class="wr-line wr-top-15 wr-bottom-20"></div>
    <div class="wr-bottom-60 wr-content" style="padding: 0 6%;color: #353537;">
        <?php $__currentLoopData = $paginate->items(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="row wr-top-10 wr-bottom-20 wr-item">
                <span class="col-xs-8 wr-content-left"><?php echo $item->nickname; ?>-跑腿费</span>
                <span class="col-xs-4 wr-content-right">￥<?php echo rmb($item->pay_fee); ?>元</span>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if( empty($paginate->items())): ?>
            <div class="text-center" style="color: #B2B2B3">暂无收益</div>
        <?php endif; ?>
    </div>
</div>
<div class="row wr-paginate" style="color: #222222">
    <?php echo paging($paginate->currentPage(), $paginate->lastPage(), URL::to('/web/pp/account'), Request::all()); ?>

</div>
</body>
<script>
    // 选择统计
    $(function () {
        var url = "<?php echo build_qs( Request::except(['date_month']) ); ?>";
        $('.wr-date-month').on('click', function () {
            window.location.href="/web/pp/account?date_month=" + $(this).data('month') + '&' + url;
        })
    })

    $(function () {
        var ias = $.ias({
            container: ".wr-content", //包含所有文章的元素
            item: ".wr-item", //文章元素
            pagination: ".wr-paginate", //分页元素
            next: ".wr-next", //下一页元素
        });
        ias.extension(new IASSpinnerExtension({
            text: '加载中，请稍候...', // 加载完成时的提示
            html: '<p style="text-align: center;margin: 10px auto 20px;color:#999;">加载中，请稍候...</p>',
        }));
        ias.extension(new IASTriggerExtension({
            text: '点击加载更多', //此选项为需要点击时的文字
            html: '<p style="text-align: center; cursor: pointer;"><a>{text}</a></p>',
            offset: !1, //设置此项，如 offset:2 页之后需要手动点击才能加载，offset:!1 则一直为无限加载
        }));
        ias.extension(new IASNoneLeftExtension({
            text: '数据已全部加载！', // 加载完成时的提示
            html: '<p style="text-align: center;margin: 10px auto 20px;color:#999;font-size:12px;">数据已全部加载！</p>',
        }));
    })

    $(function () {
        if ($(document.body).outerWidth(true) > 340){
            $('.wr-line-02').css('margin', '0 10px')
        };
    })
</script>
</html><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/web/pp-user/account/index.blade.php ENDPATH**/ ?>