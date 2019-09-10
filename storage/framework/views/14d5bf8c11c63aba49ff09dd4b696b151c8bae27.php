

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
                                                data-name="no"
                                        >
                                            订单编号 <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="javascript:;" data-name="no">订单编号</a></li>
                                            <li><a href="javascript:;" data-name="zpp_tel">顾客电话</a></li>
                                            <li><a href="javascript:;" data-name="pp_tel">跑跑电话</a></li>
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

                                            var query = _.omit($.parseQS(), ['page', 'no', 'zpp_tel', 'pp_tel', 'city']);

                                            window.location.href = $.buildURL('admin/order', query, _.set({}, $btn_dropdown.data('name'), $input.val()));
                                        });

                                        // 回车触发搜索
                                        $input.on('keydown', function (ev) {
                                            if (ev.keyCode == 13) {
                                                $btn_submit.click();
                                            }
                                        });

                                        // 保留搜索记录
                                        _.forIn($.parseQS(), function (v, k) {
                                            if (_.includes(['name', 'no', 'zpp_tel', 'pp_tel', 'city'], k)) {
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
                                    <col width="8%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="5%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="5%">
                                    <col width="7%">
                                    <col width="10%">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>订单ID</th>
                                    <th>订单编号</th>
                                    <th>顾客</th>
                                    <th>顾客电话</th>
                                    <th>跑跑</th>
                                    <th>跑跑电话</th>
                                    <th>下单时间</th>
                                    <th>区域</th>
                                    <th>金额</th>
                                    <th>闪送</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $paginate->items(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo $row->id; ?></td>
                                        <td><?php echo $row->no; ?></td>
                                        <td><?php echo $row->zpp_name!="" ? $row->zpp_name : ""; ?></td>
                                        <td><?php echo $row->zpp_tel; ?></td>
                                        <td><?php echo $row->pp_name!="" ? $row->pp_name : ""; ?></td>
                                        <td><?php echo $row->pp_tel; ?></td>
                                        <td><?php echo $row->created_at; ?></td>
                                        <td><?php echo $row->city; ?></td>
                                        <td><?php echo rmb($row->pay_fee); ?></td>
                                        <td><?php echo $row->is_quick?'是':""; ?></td>
                                        <td>
                                            <span><?php echo $row->order_status; ?></span>
                                        </td>
                                        <td>
                                            <a href="/admin/order/check?order_id=<?php echo $row->id; ?>" target="_blank">详情</a>
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

    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin/layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/order/index.blade.php ENDPATH**/ ?>