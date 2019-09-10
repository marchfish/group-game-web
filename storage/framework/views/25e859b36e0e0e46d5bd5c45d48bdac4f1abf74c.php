<?php $__env->startSection('content'); ?>
  <style>
    .col-xs-6 img {
      max-width: 100%;
    }
  </style>
<section class="content-header">
  <h1>查看公司资料</h1>
</section>
<section class="content">
  <div class="row">
    <div class="col-lg-offset-3 col-lg-6">
      <div class="box box-primary">
        <div class="box-header with-border">
          <a class="btn btn-default" href="<?php echo URL::to('admin/group').'?'. build_qs(json_decode(Request::input('_ref'), true)); ?>">
            <i class="fa fa-reply"></i>
            返回
          </a>
        </div>
        <div class="box-body">
          <form id="js-form" action="<?php echo URL::to('admin/group'); ?>" >
            <input type="hidden" name="group_id" value="<?php echo $row->id; ?>">
            <div class="form-group">
              <label for="title">公司名称：</label>
              <input type="text" class="form-control" name="name" value="<?php echo $row->name; ?>">
            </div>
            <div class="form-group row">
                <div class="col-xs-6">
                  <label for="subtitle">公司logo：</label>
                    <a href="<?php echo $row->logo; ?>" target="_blank">
                        <img src="<?php echo $row->logo; ?>" alt="">
                    </a>
                </div>
            </div>
            <div class="form-group">
              <label for="url">公司地址：</label>
              <input class="form-control" name="address" type="text" value="<?php echo $row->address; ?>">
            </div>
            <div class="form-group">
              <label for="litpic">公司法人：</label>
              <input class="form-control" name="realname" type="text" value="<?php echo $row->realname; ?>">
            </div>
            <div class="form-group">
              <label for="litpic">法人手机号：</label>
              <input class="form-control" name="tel" type="text" value="<?php echo $row->tel; ?>">
            </div>
            <div class="form-group">
              <label for="litpic">法人身份证：</label>
              <input class="form-control" name="idcard" type="text" value="<?php echo $row->idcard; ?>">
            </div>
            <div class="form-group">
              <label>公司业务简介：</label>
              <textarea class="form-control" name="description" rows="3" autocomplete="off"><?php echo $row->description; ?></textarea>
            </div>
            <div class="form-group">
              <label for="litpic">营业执照代码：</label>
              <input class="form-control" name="certificate_code" type="text" value="<?php echo $row->certificate_code; ?>">
            </div>
            <div class="row">
              <div class="col-xs-6">
                <div class="form-group">
                  <label>公司营业执照：</label>
                    <a href="<?php echo $row->g_pic1; ?>" target="_blank">
                        <img src="<?php echo $row->g_pic1; ?>" alt="">
                    </a>
                </div>
              </div>
            </div>
          </form>
          <?php if($row->status == 150): ?>
          <div class="form-group">
            <button type="submit" data-group-id="<?php echo $row->id; ?>" class="btn btn-primary pull-right js-a-group-verify">驳回</button>
            <button type="submit" data-group-id="<?php echo $row->id; ?>" class="btn btn-primary pull-right js-a-group-verify" style="margin-right: 20px">通过</button>
          </div>
          <?php endif; ?>
        </div>
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

<?php echo $__env->make('admin/layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/group/check.blade.php ENDPATH**/ ?>