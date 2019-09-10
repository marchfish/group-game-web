<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
  <title>找跑跑 | 后台管理</title>
  <link rel="stylesheet" href="<?php echo URL::asset('backstage/css/vendor.css?v=000'); ?>">
  <script src="<?php echo URL::asset('backstage/js/vendor_hd.js?v=000'); ?>"></script>
</head>

<body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-box-body">
      <p class="login-box-msg"><b>找跑跑</b></p>
      <form id="js-form" action="<?php echo URL::to('admin/login'); ?>">
        <div class="form-group has-feedback">
          <input type="text" name="username" class="form-control" placeholder="用户名">
          <span class="glyphicon glyphicon-user form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
          <input type="password" name="password" class="form-control" placeholder="密码">
          <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <div class="form-group">
          <div class="row">
            <div class="col-xs-6">
              <input type="text" name="captcha" class="form-control" placeholder="验证码">
            </div>
            <div class="col-xs-6">
              <img id="js-img" style="cursor: pointer;" data-src="<?php echo URL::to('admin/captcha'); ?>">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-offset-8 col-xs-4">
            <button type="submit" class="btn btn-primary btn-block btn-flat">登录</button>
          </div>
        </div>
      </form>
      <script>
      $(function () {
        $('#js-img').on('click', function (ev) {
          this.src = $(this).data('src') + '?m=' + Math.random();
        }).click();

        $('#js-form').on('submit', function (ev) {
          ev.preventDefault();

          $.csrf({
            method: 'post',
            url: this.action,
            data: $(this).serialize(),
          }, function (res) {
            if (res.redirect_url) {
              window.location.href = res.redirect_url;
            }
          });
        });
      });
      </script>
    </div>
  </div>
  <script src="<?php echo URL::asset('backstage/js/vendor_bd.js?v=000'); ?>"></script>
</body>

</html>
<?php /**PATH E:\phpStudy\WWW\group-g\resources\views/admin/public/login.blade.php ENDPATH**/ ?>