<?php $__env->startSection('content'); ?>
<section class="content-header">
  <h1>编辑账号</h1>
</section>
<section class="content">
  <div class="row">
    <div class="col-lg-offset-3 col-lg-6">
      <div class="box box-primary">
        <div class="box-header with-border">
          <a class="btn btn-default" href="<?php echo URL::to('admin/admin_account'); ?>">
            <i class="fa fa-reply"></i>
            返回
          </a>
        </div>
        <div class="box-body">
          <form id="js-form" class="form-horizontal" action="<?php echo URL::to('admin/admin_account'); ?>">
            <input name="id" type="hidden" value="<?php echo $account->id; ?>">
            <div class="form-group">
              <label for="username" class="col-lg-2 control-label">用户名</label>
              <div class="col-lg-9">
                <input id="username" name="username" type="text" class="form-control" value="<?php echo e($account->username); ?>" disabled>
              </div>
            </div>
            <div class="form-group">
              <label for="password"class="col-lg-2 control-label">密码</label>
              <div class="col-lg-9">
                <input id="password" name="password" type="password" class="form-control" autocomplete="off">
              </div>
            </div>
            <div class="form-group">
              <label for="repassword" class="col-lg-2 control-label">重复密码</label>
              <div class="col-lg-9">
                <input id="repassword" name="repassword" type="password" class="form-control" autocomplete="off">
              </div>
            </div>
            <div class="form-group">
              <label for="nickname" class="col-lg-2 control-label">昵称</label>
              <div class="col-lg-9">
                <input id="nickname" name="nickname" type="text" class="form-control" value="<?php echo e($account->nickname); ?>" autocomplete="off">
              </div>
            </div>
            <div class="form-group">
              <label class="col-lg-2 control-label">角色</label>
              <div class="col-lg-9">
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="checkbox">
                  <label>
                    <input name="roles[]" type="checkbox" value="<?php echo $role->id; ?>" <?php echo isset($account->roles[$role->id]) ? 'checked' : ''; ?>>
                    <?php echo $role->name; ?>

                  </label>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
            </div>
          </form>
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
                  window.location='<?php echo URL::to('admin/admin_account'); ?>';
                });
              });
            });
          });
          </script>
        </div>
        <div class="box-footer">
          <button type="submit" class="btn btn-primary pull-right" form="js-form"><i class="fa fa-save"></i> 保存</button>
        </div>
      </div>
    </div>
  </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin/layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/admin-account/edit.blade.php ENDPATH**/ ?>