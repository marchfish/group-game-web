<?php $__env->startSection('content'); ?>
    <section class="content-header">
        <h1>系统设置</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-lg-offset-3 col-lg-6">
                <div class="box box-primary">
                    <?php if(!empty($rows)): ?>
                        <div class="box-body">
                            <form id="js-form" action="<?php echo URL::to('admin/sys-config'); ?>" >
                                <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="form-group">
                                        <p><?php echo $row->key; ?></p>
                                        <label><?php echo $row->description; ?></label>
                                        <textarea class="form-control" name="settings[<?php echo $row->id; ?>]" rows="3" autocomplete="off"><?php echo $row->value; ?></textarea>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </form>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary pull-right" form="js-form"><i class="fa fa-save"></i> 保存</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <script>
        $(function () {
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
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin/layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/sys-config/index.blade.php ENDPATH**/ ?>