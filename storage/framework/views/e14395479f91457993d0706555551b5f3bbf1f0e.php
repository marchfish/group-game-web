

<?php $__env->startSection('content'); ?>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <?php if(count($paginate->items()) > 0): ?>
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-striped table-bordered table-hover text-center vertical-align-middle">
                                <colgroup>
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="10%">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>UID</th>
                                    <th>头像</th>
                                    <th>姓名</th>
                                    <th>手机号</th>
                                    <th>上传时间</th>
                                    <th>处理人</th>
                                    <th>备注信息</th>
                                    <th>处理时间</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $paginate->items(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo $row->user_id; ?></td>
                                        <td>
                                            <a href="<?php echo $row->avatar; ?>" target="_blank">
                                                <img src="<?php echo $row->avatar; ?>" alt="" style="max-width: 50px; max-height: 50px">
                                            </a>
                                        </td>
                                        <td><?php echo $row->realname; ?></td>
                                        <td><?php echo $row->tel; ?></td>
                                        <td><?php echo $row->created_at; ?></td>
                                        <td><?php echo $row->replay_nickname; ?></td>
                                        <td><?php echo $row->replay; ?></td>
                                        <td><?php echo $row->replay_at ?? ''; ?></td>
                                        <td><?php echo $row->verify_status; ?></td>
                                        <td>
                                            <?php if($row->status == 150): ?>
                                                <a class="js-a-avatar-pass" data-user-id="<?php echo $row->user_id; ?>" href="javascript:;">通过</a>
                                                |
                                                <a class="js-a-avatar-disabled" data-user-id="<?php echo $row->user_id; ?>" href="javascript:;">驳回</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="row js-form-disabled hide" style="position: fixed;top: 40%;left: 50%;">
            <div class="box box-primary">
                <div class="box-body">
                    <form id="js-form" action="<?php echo URL::to('admin/pp-user/verify/avatar'); ?>" >
                        <div class="form-group">
                            <label>请填原因:</label>
                            <input type="hidden" name="user_id" value="" />
                            <textarea class="form-control" name="replay" rows="3" autocomplete="off"></textarea>
                        </div>
                    </form>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" form="js-form"> 确 定 </button>
                        <button type="submit" class="btn btn-primary pull-right cancel"> 取 消 </button>
                    </div>
                </div>
            </div>
            <script>
                $(function () {
                    // 显示驳回提现理由窗口
                    $('.js-a-avatar-disabled').on('click', function (ev) {
                        ev.preventDefault();
                        $('input[name="user_id"]').val($(this).data('userId'));
                        $('.js-form-disabled').prop('className', 'row js-form-disabled');
                    });

                    // 提交驳回信息
                    $('#js-form').on('submit', function (ev) {
                        ev.preventDefault();
                        $.csrf({
                            method: 'put',
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
    <script>
        $(function () {
            // 通过/驳回
            $('.js-a-avatar-pass').on('click', function (ev) {
                ev.preventDefault();
                $.csrf({
                    method: 'post',
                    url: $.buildURL('admin/pp-user/verify/avatar'),
                    data: {
                        user_id:$(this).data('userId'),
                    },
                }, function (res) {
                    $.alertSuccess(res.message, function () {
                        window.location.reload();
                    });
                });
            });
        })
    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin/layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/pp-user/verify/avatar.blade.php ENDPATH**/ ?>