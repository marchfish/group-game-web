
<link rel="stylesheet" href="<?php echo URL::asset('wechat/css/amazeui.min.css'); ?>">

<?php $__env->startSection('content'); ?>

<div class="content" style="overflow-y:scroll;height: 100%;overflow-x: hidden;">
    <div class="row">
        <div class="col-xs-12">
            <div class="am-cf am-padding am-padding-bottom-0">
                <div class="am-fl am-cf"><strong class="am-text-primary am-text-lg"><?php if(isset($isreport)): ?>举报管理<?php else: ?> 微圈管理  <?php endif; ?></strong> </div>
            </div>
            <hr>
            <div class="am-g">
                <form class="am-form-inline" role="form" action="?" method="get">
                    <div class="am-form-group am-input-group-sm am-u-sm-4">
                        <input type="text" style="width: 120px;" class="am-form-field" name="search_uid" value="<?php echo e(isset($search_uid)?$search_uid:''); ?>" placeholder="请输入UID">
                    </div>
                    <div class="am-form-group am-input-group-sm am-u-sm-4">
                        <input type="text" style="width: 120px;" class="am-form-field" name="search_text" value="<?php echo e(isset($search_text)?$search_text:''); ?>" placeholder="请输入关键字">
                    </div>
                    <div class="am-form-group am-input-group-sm am-u-sm-4">
                        <button class="am-btn am-btn-default am-btn-sm" type="submit">搜索</button>
                    </div>
                </form>
            </div>
            <div class="am-g am-margin-top">
            <div class="am-u-sm-12 am-scrollable-horizontal">
                <div class="am-comments-list am-comments-list-flip" id="pagelist">
                    <?php $__currentLoopData = $paginate->items(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="am-comment">
                        <a href="#">
                            <img src="<?php echo e($v->avatar); ?>" alt="" class="am-comment-avatar" width="48" height="48"/>
                        </a>
                        <div class="am-comment-main">
                            <header class="am-comment-hd">
                                <div class="am-comment-meta">
                                    <a href="#" class="am-comment-author"><?php echo e($v->nickname); ?>(UID:<?php echo e($v->user_id); ?>)</a> 发表于 <time><?php echo e($v->showdate); ?></time>
                                </div>
                                <?php if(isset($isreport)): ?>
                                    <div class="am-comment-meta" >
                                        <strong style="color: red">被举报 </strong>
                                        :<?php echo e($v->content); ?>

                                    </div>
                                <?php endif; ?>
                            </header>
                            <div class="am-comment-bd">
                                <?php echo e($v->text); ?><br/>
                                <?php if(isset($v->ext['image'])): ?>
                                <?php $__currentLoopData = $v->ext['image']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kk=>$vv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(isset($vv['thumb'])): ?>
                                                 <img src="<?php echo e($vv['thumb']); ?>" style="width:50px"/>
                                        <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </div>
                            <div class="am-comment-footer">
                                <div class="am-comment-actions">
                                    <a href="#"><i class="am-icon-heart"></i>(<?php echo e($v->agree_count); ?>)</a>
                                    <a href="#"><i class="am-icon-comment"></i>(<?php echo e($v->comment_count); ?>)</a>
                                    <a href="/wechar/topic?id=<?php echo e($v->id); ?>"><i class="am-icon-reply"></i></a>
                                </div>
                            </div>
                            <div class="am-comment-footer">
                                <a class="am-btn am-btn-danger am-btn-xs" onclick="delTopic(<?php echo e($v->id); ?>)" href="javascript:;">删除</a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div class="box-footer">
                <div class="row">
                    <div class="col-xs-6">
                        <p>从第 <b><?php echo $paginate->firstItem(); ?></b> 条到第 <b><?php echo $paginate->lastItem(); ?></b> 条，共 <b><?php echo $paginate->total(); ?></b> 条</p>
                    </div>
                    <div class="col-xs-6">
                        <?php echo $paginate->appends(Request::all())->links('admin/pagination'); ?>

                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>


<script type="text/javascript">

    function delTopic(id){

            $.csrf({
                type:'POST',
                url: $.buildURL('/admin/topic/delete'),
                data: {
                    tid:id,
                    <?php if(isset($isreport)): ?>
                    isreport:1,
                    <?php endif; ?>
                },
            }, function (res) {
                $.alertSuccess(res.message, function () {
                    window.location.reload();
                });
            });
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin/layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/topic/index.blade.php ENDPATH**/ ?>