

<?php $__env->startSection('content'); ?>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <div class="row">
                        <div class="col-xs-4 col-xs-offset-4">
                            <div id="js-search" class="input-group">
                                <div class="input-group-btn">
                                    <button
                                            id="js-search-btn-dropdown"
                                            class="btn btn-default dropdown-toggle"
                                            type="button"
                                            data-toggle="dropdown"
                                            data-name="name"
                                    >
                                        公司名称 <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="javascript:;" data-name="name">公司名称</a></li>
                                        <li><a href="javascript:;" data-name="no">公司编号</a></li>
                                        <li><a href="javascript:;" data-name="tel">法人手机号</a></li>
                                        <li><a href="javascript:;" data-name="city">城市</a></li>
                                    </ul>
                                </div>
                                <input class="form-control" type="text">
                                <div class="input-group-btn">
                                    <button id="js-search-btn-submit" class="btn btn-default" type="button">搜索</button>
                                </div>
                            </div>
                            <script>
                                $(function () {
                                    var $btn_dropdown = $('#js-search-btn-dropdown');
                                    var $input = $('#js-search input');
                                    var $btn_submit = $('#js-search-btn-submit');

                                    // 切换搜索
                                    $('#js-search .dropdown-menu a').on('click', function (ev) {
                                        ev.preventDefault();

                                        var $a = $(this);

                                        $btn_dropdown
                                            .data('name', $a.data('name'))
                                            .html($a.text() + ' <span class="caret"></span>')
                                        ;
                                    });

                                    // 搜索
                                    $btn_submit.on('click', function (ev) {
                                        ev.preventDefault();

                                        var query = _.omit($.parseQS(), ['page', 'name', 'no', 'tel', 'city']);

                                        window.location.href = $.buildURL('admin/group', query, _.set({}, $btn_dropdown.data('name'), $input.val()));
                                    });

                                    // 回车触发搜索
                                    $input.on('keydown', function (ev) {
                                        if (ev.keyCode == 13) {
                                            $btn_submit.click();
                                        }
                                    });

                                    // 保留搜索记录
                                    _.forIn($.parseQS(), function (v, k) {
                                        if (_.includes(['name', 'no', 'tel', 'city'], k)) {
                                            $('a[data-name="' + k + '"]').click();

                                            $input.val(v);
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>
                <?php if(count($paginate->items()) > 0): ?>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-striped table-bordered table-hover text-center vertical-align-middle">
                            <colgroup>
                                <col width="5%">
                                <col width="10%">
                                <col width="10%">
                                <col width="10%">
                                <col width="10%">
                                <col width="10%">
                                <col width="10%">
                                <col width="15%">
                                <col width="10%">
                                <col width="10%">
                            </colgroup>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>公司名称</th>
                                <th>公司logo</th>
                                <th>城市</th>
                                <th>公司负责人</th>
                                <th>成员数</th>
                                <th>商家数</th>
                                <th>创建时间</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__currentLoopData = $paginate->items(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo $row->id; ?></td>
                                    <td><?php echo $row->name; ?></td>
                                    <td>
                                        <img style="max-width: 50px;max-height: 50px;" src="<?php echo $row->logo; ?>" alt="">
                                    </td>
                                    <td><?php echo $row->city; ?></td>
                                    <td><?php echo $row->realname; ?></td>
                                    <td><?php echo $row->gu_count; ?></td>
                                    <td><?php echo $row->ug_count; ?></td>
                                    <td><?php echo $row->created_at; ?></td>
                                    <td><?php echo $row->status1; ?></td>
                                    <td>
                                        <?php if($row->status == 0 || $row->status == 200): ?>
                                        <a class="js-a-group-disabled" data-group-id="<?php echo $row->id; ?>" href="javascript:;">禁用</a>
                                        |
                                        <a class="js-a-group-disabled" data-group-id="<?php echo $row->id; ?>" href="javascript:;">解禁</a>
                                        |
                                        <?php endif; ?>
                                        <a href='/admin/group/check?group_id=<?php echo $row->id . '&_ref=' . json_encode(Request::all()); ?>'>查看</a>
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
        $('.js-a-group-verify').on('click', function (ev) {
            ev.preventDefault();
            if ( $(this).html() === '通过' )
            {
                var method = 'post';
            }else {
                var method = 'put';
            }

            $.csrf({
                method: method,
                url: $.buildURL('admin/group'),
                data: {
                    group_id:$(this).data('groupId'),
                },
            }, function (res) {
                $.alertSuccess(res.message, function () {
                    window.location.reload();
                });
            });
        });

        // 禁用/解禁
        $('.js-a-group-disabled').on('click', function (ev) {
            ev.preventDefault();
            if ( $(this).html() === '禁用' )
            {
                var method = 'delete';
            }else {
                var method = 'put';
            }

            $.csrf({
                method: method,
                url: $.buildURL('admin/group/disabled'),
                data: {
                    group_id:$(this).data('groupId'),
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
<?php echo $__env->make('admin/layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/group/index.blade.php ENDPATH**/ ?>