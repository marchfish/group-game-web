@extends('admin/layout')

@section('content')
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box box-primary">
          <div class="box-header">
            <div class="row">
              <div class="col-md-4">
                <a type="button" class="btn btn-success" href="{{ URL::to('admin/map/new'). '?_ref=' . json_encode(Request::all()) }}">
                  <i class="fa fa-plus"></i>
                  新增
                </a>
              </div>
              <div class="col-md-4">
                <div id="js-search" class="input-group">
                  <div class="input-group-btn">
                    <button
                      id="js-search-btn-dropdown"
                      class="btn btn-default dropdown-toggle"
                      type="button"
                      data-toggle="dropdown"
                      data-name="name"
                    >
                      地图名称 <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                      <li><a href="javascript:;" data-name="name">地图名称</a></li>
                      <li><a href="javascript:;" data-name="area">区域名称</a></li>
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

                      var query = _.omit($.parseQS(), ['page', 'name', 'area']);

                      window.location.href = $.buildURL('admin/map/index', query, _.set({}, $btn_dropdown.data('name'), $input.val()));
                    });

                    // 回车触发搜索
                    $input.on('keydown', function (ev) {
                      if (ev.keyCode == 13) {
                        $btn_submit.click();
                      }
                    });

                    // 保留搜索记录
                    _.forIn($.parseQS(), function (v, k) {
                      if (_.includes(['name', 'area'], k)) {
                        $('a[data-name="' + k + '"]').click();

                        $input.val(v);
                      }
                    });
                  });
                </script>
              </div>
            </div>
            <div class="row wr-top-10">
              <div class="col-xs-12">
                <ul class="nav nav-tabs">
                  <li><a href="?status=&{{ build_qs(Request::except(['status', 'page'])) }}">全部</a></li>
                  {{--<li><a href="?status=150&{{ build_qs(Request::except(['status', 'page'])) }}">认证中</a></li>--}}
                </ul>
                <script>
                  $(function () {
                    var query = $.parseQS();

                    var $tabs = $('.nav-tabs');

                    if (query.status == 150) {
                      $tabs.children()[1].className = 'active';
                    } else {
                      $tabs.children()[0].className = 'active';
                    }
                  });
                </script>
              </div>
            </div>
          </div>
          @if (count($paginate->items()) > 0)
            <div class="box-body table-responsive no-padding">
              <table class="table table-striped table-bordered table-hover text-center vertical-align-middle">
                <colgroup>
                  {{--<col width="4%">--}}
                  {{--<col width="7%">--}}
                  {{--<col width="7%">--}}
                  {{--<col width="7%">--}}
                  {{--<col width="7%">--}}
                  {{--<col width="7%">--}}
                  {{--<col width="7%">--}}
                  {{--<col width="7%">--}}
                  {{--<col width="7%">--}}
                  {{--<col width="7%">--}}
                  {{--<col width="7%">--}}
                  {{--<col width="7%">--}}
                  {{--<col width="5%">--}}
                  {{--<col width="6%">--}}
                </colgroup>
                <thead>
                <tr>
                  <th>ID</th>
                  <th>名称</th>
                  <th>介绍</th>
                  <th>npc</th>
                  <th>怪物</th>
                  <th>上</th>
                  <th>下</th>
                  <th>左</th>
                  <th>右</th>
                  <th>前</th>
                  <th>后</th>
                  <th>区域</th>
                  <th>是否活动</th>
                  <th>操作</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($paginate->items() as $row)
                  <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->description }}</td>
                    <td>{{ $row->npc_name }}</td>
                    <td>{{ $row->enemy_name }}</td>
                    <td>{{ $row->up_name }}</td>
                    <td>{{ $row->down_name }}</td>
                    <td>{{ $row->left_name }}</td>
                    <td>{{ $row->right_name }}</td>
                    <td>{{ $row->forward_name }}</td>
                    <td>{{ $row->behind_name }}</td>
                    <td>{{ $row->area_name }}</td>
                    <td>{{ $row->is_activity == 1 ? '是' : '否' }}</td>
                    <td>
                      <a href='/admin/map/edit?map_id={{ $row->id . '&_ref=' . json_encode(Request::all()) }}'>编辑</a>
                      |
                      <a class="js-delete" data-map-id="{{ $row->id  }}" href='javascript;;'>删除</a>
                    </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            <div class="box-footer">
              <div class="row">
                <div class="col-xs-6">
                  <p>从第 <b>{{ $paginate->firstItem() }}</b> 条到第 <b>{{ $paginate->lastItem() }}</b> 条，共
                    <b>{{ $paginate->total() }}</b> 条</p>
                </div>
                <div class="col-md-6">
                  {{ $paginate->appends(Request::all())->links('admin/pagination') }}
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </section>

  <script>
    $('.js-delete').on('click', function (ev) {
      ev.preventDefault();
      var mapId = $(this).data("mapId");
      layer.confirm('您确定要删除该地图吗？', {
        title: '删除地图',
        btn: ['确定', '取消'] //按钮
      }, function () {
        $.csrf({
          method: 'delete',
          url: $.buildURL('admin/map'),
          data: {
            map_id: mapId,
          }
        }, function (res) {
          if (res.code === 200) {
            layer.msg(res.message, {
              time: 1000,
            });
            window.location.reload();
          } else {
            $.alertError(res.message, function () {
            });
          }
        });
      }, function () {

      });
    });
  </script>

@endsection
