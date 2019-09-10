<?php $__env->startSection('content'); ?>
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box box-primary">
        <div class="box-header">
          <div class="row">
            <div class="col-xs-4">
              <a type="button" class="btn btn-success" href="<?php echo URL::to('admin/admin_role/new'); ?>"><i class="fa fa-plus"></i> 新增</a>
            </div>
          </div>
        </div>
        <?php if($roles->count() > 0): ?>
        <div class="box-body table-responsive no-padding">
          <table class="table table-striped table-bordered table-hover text-center">
            <colgroup>
              <col width="50%">
              <col width="50%">
            </colgroup>
            <thead>
              <tr>
                <th>角色名称</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td><?php echo e($role->name); ?></td>
                <td>
                  <a href="<?php echo URL::to('admin/admin_role/edit') . '?id=' . $role->id; ?>">编辑</a>
                  <a class="js-a-delete" href="<?php echo URL::to('admin/admin_role') . '?id=' . $role->id; ?>">删除</a>
                </td>
              </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
          </table>
          <script>
          $(function () {
            $('.js-a-delete').on('click', function (ev) {
              ev.preventDefault();

              if (confirm('确认删除吗？')) {
                $.csrf({
                  method: 'delete',
                  url: this.href,
                }, function (res) {
                  $.alertSuccess(res.message, function () {
                    location.reload();
                  });
                });
              }
            });
          });
          </script>
        </div>
        <div class="box-footer">
          <div class="row">
            <div class="col-xs-6">
              <p>从第 <b><?php echo $roles->firstItem(); ?></b> 条到第 <b><?php echo $roles->lastItem(); ?></b> 条，共 <b><?php echo $roles->total(); ?></b> 条</p>
            </div>
            <div class="col-xs-6">
              <?php echo $roles->appends(Request::all())->links('admin/pagination'); ?>

            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin/layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/admin-role/index.blade.php ENDPATH**/ ?>