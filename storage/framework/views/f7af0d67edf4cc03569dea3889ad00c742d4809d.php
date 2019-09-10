<?php $__env->startSection('content'); ?>
<section class="content-header">
  <h1>编辑通知</h1>
</section>
<section class="content">
  <div class="row">
    <div class="col-lg-offset-3 col-lg-6">
      <div class="box box-primary">
        <div class="box-header with-border">
          <a class="btn btn-default" href="<?php echo URL::to('admin/notice'); ?>">
            <i class="fa fa-reply"></i>
            返回
          </a>
        </div>
        <div class="box-body">
          <form id="js-form" action="<?php echo URL::to('admin/notice'); ?>" >
            <input type="hidden" name="id" value="<?php echo ''; ?>">
            <div class="form-group">
              <label for="title">标题*：</label>
              <input type="text" class="form-control" name="title" maxlength="30" placeholder="输入标题" value="<?php echo ''; ?>">
            </div>
            <div class="form-group">
              <label for="subtitle">内容*：</label>
              <input type="text" class="form-control" name="subtitle" maxlength="90" placeholder="输入内容" value="<?php echo ''; ?>">
            </div>
            <div class="form-group">
              <label for="url">开始通知时间*：</label>
              <input class="form-control" id="start_at" name="start_at" type="text">
            </div>
            <div class="form-group">
              <label for="litpic">结束通知时间*：</label>
              <div class='input-group date' id='datetimepicker7'>
                <input type='text' name="end_at" class="form-control" />
                  <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                  </span>
              </div>
            </div>
            <div class="form-group">
              <label for="litpic">选择通知时段：</label>
              <br>
              <label class="checkbox-inline">
                <input type="checkbox" id="inlineCheckbox1" value="option1"> 1
              </label>
              <label class="checkbox-inline">
                <input type="checkbox" id="inlineCheckbox2" value="option2"> 2
              </label>
              <label class="checkbox-inline">
                <input type="checkbox" id="inlineCheckbox3" value="option3"> 3
              </label>
            </div>
            <div class="form-group">
              <label for="litpic">选择接收通知的APP端：</label>
              <input type="text" class="form-control" name="litpic" value="<?php echo ''; ?>">
            </div>
            <div class="form-group">
              <label for="litpic">选择接收通的城市：</label>
              <input type="text" class="form-control" name="litpic" value="<?php echo ''; ?>">
            </div>
            <div class="form-group">
              <label for="litpic">是否立即推送消息：</label>
              <input type="text" class="form-control" name="litpic" value="<?php echo ''; ?>">
            </div>
          </form>
          <div class="form-group">
            <button type="submit" class="btn btn-primary pull-right" form="js-form"> 保存文章</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
$(function () {
  // 保存
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

// 初始化时间控件
$(function () {
    $('#datetimepicker6').datetimepicker();
    $('#datetimepicker7').datetimepicker({
        useCurrent: false //Important! See issue #1075
    });
    $("#datetimepicker6").on("dp.change", function (e) {
        $('#datetimepicker7').data("DateTimePicker").minDate(e.date);
    });
    $("#datetimepicker7").on("dp.change", function (e) {
        $('#datetimepicker6').data("DateTimePicker").maxDate(e.date);
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin/layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/notice/edit.blade.php ENDPATH**/ ?>