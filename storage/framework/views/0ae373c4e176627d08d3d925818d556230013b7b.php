<?php $__env->startSection('content'); ?>
<section class="content-header">
  <h1>编辑通知</h1>
</section>
<section class="content">
  <div class="row">
    <div class="col-lg-offset-3 col-lg-6">
      <div class="box box-primary">
        <div class="box-header with-border">
          <a class="btn btn-default" href="<?php echo URL::to('admin/sys-notice'); ?>">
            <i class="fa fa-reply"></i>
            返回
          </a>
        </div>
        <div class="box-body">
          <form id="js-form" action="<?php echo URL::to('admin/sys-notice'); ?>" >
            <input type="hidden" name="sys_notice_id" value="<?php echo $row->id; ?>">
            <div class="form-group">
              <label for="title">标题*：</label>
              <input type="text" class="form-control" name="title" maxlength="30" placeholder="输入标题" value="<?php echo $row->title; ?>">
            </div>
            <div class="form-group">
              <label for="subtitle">内容*：</label>
              <input type="text" class="form-control" name="content" maxlength="90" placeholder="输入内容" value="<?php echo $row->content; ?>">
            </div>
            <div class="form-group">
              <label for="url">开始通知时间*：</label>
              <input class="form-control" id="start-at" name="start_at" type="text" value="<?php echo $row->start_at; ?>">
            </div>
            <div class="form-group">
              <label for="litpic">结束通知时间*：</label>
              <input class="form-control" id="end-at" name="end_at" type="text" value="<?php echo $row->end_at; ?>">
            </div>
            <div class="form-group">
              <label for="litpic">选择通知时段：</label>
              <br>
              <?php for($i = 0; $i < 24; $i++): ?>
                <label class="checkbox-inline" style="margin-left: 0px; margin-right: 5px;">
                  <input type="checkbox" <?php echo strpos($row->hour, sprintf("%02d",$i)) !== false ? 'checked="checked"' : ''; ?>  name="hour[]" value="<?php echo sprintf("%02d",$i); ?>"><?php echo sprintf("%02d",$i); ?>

                </label>
              <?php endfor; ?>
            </div>
            <div class="form-group">
              <label for="client">选择接收通知的APP端：</label>
              <br>
              <label class="radio-inline">
                <input type="radio" name="client" <?php echo $row->client == '' ? 'checked="checked"' : ''; ?> value="">不限
              </label>
              <label class="radio-inline">
                <input type="radio" name="client" <?php echo $row->client == 'zpp' ? 'checked="checked"' : ''; ?> value="zpp">《找跑跑版》
              </label>
              <label class="radio-inline">
                <input type="radio" name="client" <?php echo $row->client == 'pp' ? 'checked="checked"' : ''; ?> value="pp">《跑跑骑手版》
              </label>
            </div>
            <div class="form-group">
              <label for="area">选择接收通的城市：</label>
              <select class="form-control" name="area">
                <option value="">全部城市</option>
                <?php $__currentLoopData = $row->citys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <optgroup label="<?php echo $k; ?>">
                  <?php $__currentLoopData = $v; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kk=>$vv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo $kk; ?>" <?php echo $kk == $row->area ? 'selected="selected"' : ''; ?>><?php echo $vv; ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </optgroup>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="isUrgent">是否立即推送消息：</label>
              <br>
              <label class="radio-inline">
                <input type="radio" name="isUrgent" value="1">是
              </label>
              <label class="radio-inline">
                <input type="radio" name="isUrgent" checked="checked" value="0">否
              </label>
            </div>
          </form>
          <div class="form-group">
            <button type="submit" class="btn btn-primary pull-right" form="js-form"> 保存</button>
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

// 开始时间
$(function () {
    $('#start-at').datetimepicker({
        locale: 'zh-cn',
        format: 'YYYY-MM-DD HH:mm:ss',
    });
});

// 结束时间
$(function () {
    $('#end-at').datetimepicker({
        locale: 'zh-cn',
        format: 'YYYY-MM-DD HH:mm:ss',
    });
});

</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin/layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/sys-notice/edit.blade.php ENDPATH**/ ?>