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

<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">
    <header class="main-header">
      <a href="javascript:;" class="logo">
        <span class="logo-mini">找跑跑</span>
        <span class="logo-lg"><b>找跑跑</b> 后台管理</span>
      </a>
      <nav class="navbar navbar-static-top">
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button"></a>
        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <li><a><?php echo e(Session::get('admin.account.nickname')); ?></a></li>
            <li><a href="<?php echo URL::to('admin/logout'); ?>">退出登录</a></li>
          </ul>
        </div>
      </nav>
    </header>
    <aside class="main-sidebar">
      <section class="sidebar">
        <ul class="sidebar-menu">
          <?php $__currentLoopData = Config::get('admin_menu'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(Session::has('admin.account.controllers.' . $menu['controller'])): ?>
              <?php if(isset($menu['children'])): ?>
              <li class="treeview">
                <a href="javascript:;">
                  <i class="fa <?php echo $menu['icon']; ?>"></i>
                  <span><?php echo $menu['name']; ?></span>
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
                  <?php $__currentLoopData = $menu['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(Session::has('admin.account.controllers.' . $child['controller'])): ?>
                    <li>
                      <a href="<?php echo URL::to($child['path']) . $child['qs']; ?>">
                        <i class="fa fa-circle-o"></i>
                        <?php echo $child['name']; ?>

                      </a>
                    </li>
                    <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
              </li>
              <?php else: ?>
              <li>
                <a href="<?php echo URL::to($menu['path']) . $menu['qs']; ?>">
                  <i class="fa <?php echo $menu['icon']; ?>"></i>
                  <span><?php echo $menu['name']; ?></span>
                </a>
              </li>
              <?php endif; ?>
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      </section>
    </aside>
    <div class="content-wrapper">
      <?php echo $__env->yieldContent('content'); ?>
    </div>
  </div>
  <script src="<?php echo URL::asset('backstage/js/vendor_bd.js?v=000'); ?>"></script>
  <script>
  $(function () {
    // 保持侧边栏选中状态
    $('.sidebar-menu a').each((k, el) => {
      var url = el.href;

      if (url.indexOf('?') > 0) {
        url = url.substr(0, url.indexOf('?'));
      }

      if ($.buildURL(window.location.pathname) == url) {
        $(el).parents('li').addClass('active');
      }
    });
  });
  </script>
</body>

</html>
<?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/layout.blade.php ENDPATH**/ ?>