<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>跑跑提现-历史查看</title>
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/wr-1.0.0/css/wr-css.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/stat/css/stat.css'); ?>">
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/jquery-2.2.4/jquery.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/jquery-ias-2.2.2/jquery-ias.min.js'); ?>"></script>
</head>
<body>
<div>
    <div class="row wr-bg-01" style="background: #F6F6F6">
        <div class="text-left">
            <h3 class="wr-h3" style="margin-right: 2px;"><span class="wr-square"></span>提现记录</h3>
        </div>
    </div>
</div>
<div class="row wr-top-5 wr-padding-top-15" style="vertical-align: middle">
    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="wr-content-bg-fff detailed-link" style="padding-bottom: 0px;margin-bottom: 2px;">
        <div class="row wr-padding-top-20" style="position: relative">
            <div class="wr-flex-center">
                <img src="<?php echo URL::asset('forestage/group/img/money1.png?v=0.0.1'); ?>" alt="" width="18px" height="18px">
                <span class="wr-content-title-515">金额：<?php echo rmb($row->amount); ?>元</span>
            </div>
            <div class="wr-top-15 wr-bottom-20">
                <span class="wr-font-11-afa"><?php echo $row->created_at; ?></span>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($data->isEmpty()): ?>
        <div class="wr-center" style="color: #818181;font-size: 13pt">
            暂无提现记录
        </div>
    <?php endif; ?>
</div>
</body>
</html><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/web/pp-user/withdraw/history.blade.php ENDPATH**/ ?>