<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{!! csrf_token() !!}">
  <title>KHZL | 后台管理</title>
  <link rel="stylesheet" type="text/css" href="{{ URL::asset('forestage/public/layer-3.1.1/layer.css') }}">
  <link rel="stylesheet" href="{!! URL::asset('backstage/css/vendor.css?v=000') !!}">
  <link rel="stylesheet" href="{!! URL::asset('forestage/public/wr-1.0.0/css/wr-css.css?v=000') !!}">
  <script src="{!! URL::asset('backstage/js/vendor_hd.js?v=000') !!}"></script>
  <script type="text/javascript" src="{{ URL::asset('forestage/public/layer-3.1.1/layer.js') }}"></script>
  <script type="text/javascript" src="{{ URL::asset('forestage/public/vue-bundle-2.6.10/vue-bundle.js') }}"></script>
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">
    <header class="main-header">
      <a href="javascript:;" class="logo">
        <span class="logo-mini">KHZL</span>
        <span class="logo-lg"><b>KHZL</b> 后台管理</span>
      </a>
      <nav class="navbar navbar-static-top">
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button"></a>
        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <li><a>{{ Session::get('admin.account.nickname') }}</a></li>
            <li><a href="{!! URL::to('admin/logout') !!}">退出登录</a></li>
          </ul>
        </div>
      </nav>
    </header>
    <aside class="main-sidebar">
      <section class="sidebar">
        <ul class="sidebar-menu">
          @foreach (Config::get('admin_menu') as $menu)
            @if (Session::has('admin.account.controllers.' . $menu['controller']))
              @if (isset($menu['children']))
              <li class="treeview">
                <a href="javascript:;">
                  <i class="fa {!! $menu['icon'] !!}"></i>
                  <span>{!! $menu['name'] !!}</span>
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
                  @foreach ($menu['children'] as $child)
                    @if (Session::has('admin.account.controllers.' . $child['controller']))
                    <li>
                      <a href="{!! URL::to($child['path']) . $child['qs'] !!}">
                        <i class="fa fa-circle-o"></i>
                        {!! $child['name'] !!}
                      </a>
                    </li>
                    @endif
                  @endforeach
                </ul>
              </li>
              @else
              <li>
                <a href="{!! URL::to($menu['path']) . $menu['qs'] !!}">
                  <i class="fa {!! $menu['icon'] !!}"></i>
                  <span>{!! $menu['name'] !!}</span>
                </a>
              </li>
              @endif
            @endif
          @endforeach
        </ul>
      </section>
    </aside>
    <div class="content-wrapper">
      @yield('content')
    </div>
  </div>
  <script src="{!! URL::asset('backstage/js/vendor_bd.js?v=000') !!}"></script>
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
