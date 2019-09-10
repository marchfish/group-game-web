

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
                                    <col width="20%">
                                    <col width="20%">
                                    <col width="15%">
                                    <col width="15%">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>城市</th>
                                    <th>标题</th>
                                    <th>开始时间</th>
                                    <th>截止时间</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $paginate->items(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo $row->id; ?></td>
                                        <td><?php echo $row->city == '' ? '不限' : $row->city; ?></td>
                                        <td><?php echo $row->title; ?></td>
                                        <td><?php echo $row->start_at; ?></td>
                                        <td><?php echo $row->end_at; ?></td>
                                        <td><?php echo $row->status1; ?></td>
                                        <td>
                                            <a href="/admin/notice/edit?notice_id=<?php echo $row->id; ?>">编辑</a>
                                            |
                                            <a class="js-a-pp-user-verify" data-user-id="<?php echo $row->id; ?>" href="javascript:;">删除</a>
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
    </section>

    <script>
        $(function () {
            // 通过/驳回
            $('.js-a-pp-user-verify').on('click', function (ev) {
                ev.preventDefault();
                if ( $(this).html() === '通过' )
                {
                    var method = 'post';
                }else {
                    var method = 'put';
                }

                $.csrf({
                    method: method,
                    url: $.buildURL('admin/pp-user'),
                    data: {
                        user_id:$(this).data('userId'),
                    },
                }, function (res) {
                    $.alertSuccess(res.message, function () {
                        window.location.reload();
                    });
                });
            });

            // 禁用/解禁
            $('.js-a-pp-user-disabled').on('click', function (ev) {
                ev.preventDefault();
                if ( $(this).html() === '禁用' )
                {
                    var method = 'delete';
                }else {
                    var method = 'put';
                }

                $.csrf({
                    method: method,
                    url: $.buildURL('admin/pp-user/disabled'),
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
<?php echo $__env->make('admin/layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/notice/index.blade.php ENDPATH**/ ?>