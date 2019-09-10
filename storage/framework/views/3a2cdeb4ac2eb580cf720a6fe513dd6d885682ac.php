<?php $__env->startSection('content'); ?>
    <style>
        .form-group a {
            width: 100%;
        }
        .form-group div img {
            max-width: 180px;
        }
    </style>
    <section class="content-header">
        <h1>查看订单申述详情</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-lg-offset-3 col-lg-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <a class="btn btn-default" href="<?php echo URL::to('admin/order/report').'?'. build_qs(json_decode(Request::input('_ref'), true)); ?>">
                            <i class="fa fa-reply"></i>
                            返回
                        </a>
                    </div>
                    <div class="box-body">
                        <div id="js-form" action="<?php echo URL::to('admin/order/report'); ?>" >
                            <input type="hidden" name="user_id" value="<?php echo $row->id; ?>">
                            <div class="form-group">
                                <label for="title">订单ID：</label>
                                <input type="text" class="form-control" name="order_id"  value="<?php echo $row->order_id; ?>">
                            </div>
                            <div class="form-group">
                                <label for="subtitle">申述人：</label>
                                <input type="text" class="form-control" name="reporter_user" value="<?php echo $row->reporter_user_id == $row->pp_user_id ? $row->realname : $row->nickname; ?>">
                            </div>
                            <div class="form-group">
                                <label for="url">被申述人：</label>
                                <input class="form-control" name="reportee_user" type="text" value="<?php echo $row->reportee_user_id == $row->pp_user_id ? $row->realname : $row->nickname; ?>">
                            </div>
                            <div class="form-group">
                                <label for="litpic">申述内容：</label>
                                <textarea class="form-control" name="content" rows="3" autocomplete="off"><?php echo $row->content; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>证明图片：</label>
                                <div>
                                    <a href="<?php echo $row->pic; ?>" target="_blank">
                                        <img src="<?php echo $row->pic; ?>" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>申述时间：</label>
                                <input class="form-control" name="contact_man" type="text" value="<?php echo $row->created_at; ?>">
                            </div>
                            <div class="form-group">
                                <label>状态：</label>
                                <input class="form-control" name="contact_man" type="text" value="<?php echo $row->status == 200 ? '已处理' : '处理中'; ?>">
                            </div>
                            <div class="form-group">
                                <label>处理结果：</label>
                                <input class="form-control" name="contact_man" type="text" value="<?php echo $row->result; ?>">
                            </div>
                        </div>
                        </form>
                        <a href="/admin/order/check?order_id=<?php echo $row->order_id; ?>" target="_blank" class="btn btn-primary pull-left">查看交易订单</a>
                        <?php if($row->status == 150): ?>
                            <div class="form-group">
                                <button order-report-id="<?php echo $row->id; ?>" class="btn btn-primary pull-right js-a-order-report-success">已处理</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row js-form-disabled hide" style="position: fixed;top: 40%;left: 50%;">
            <div class="box box-primary">
                <div class="box-body">
                    <form id="js-form-a" action="<?php echo URL::to('admin/order/report'); ?>" >
                        <div class="form-group">
                            <label>请填写处理结果:</label>
                            <input type="hidden" name="order_report_id" value="<?php echo $row->id; ?>" />
                            <textarea class="form-control" name="result" rows="3" autocomplete="off"></textarea>
                        </div>
                    </form>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary success" form="js-form-a"> 确 定 </button>
                        <button type="submit" class="btn btn-primary pull-right cancel"> 取 消 </button>
                    </div>
                </div>
            </div>
            <script>
                $(function () {
                    // 显示驳回提现理由窗口
                    $('.js-a-order-report-success').on('click', function (ev) {
                        ev.preventDefault();
                        $('.js-form-disabled').prop('className', 'row js-form-disabled');
                    });

                    // 处理完成
                    $('#js-form-a').on('submit', function (ev) {
                        ev.preventDefault();
                        $.csrf({
                            method: 'post',
                            url: this.action,
                            data: $(this).serialize(),
                        }, function (res) {
                            $.alertSuccess(res.message, function () {
                                window.location.reload();
                            });
                        });
                    });

                    //取消按钮
                    $('.cancel').on('click', function (ev) {
                        ev.preventDefault();
                        $('.js-form-disabled').prop('className', 'row js-form-disabled hide');
                    });
                })
            </script>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin/layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/order/report/check.blade.php ENDPATH**/ ?>