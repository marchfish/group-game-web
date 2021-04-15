@extends('admin/layout')

@section('content')
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box box-primary">
          <div class="box-header">
            <div class="row">
              <div class="col-md-4 col-md-offset-4">
                <div id="js-search" class="input-group">
                  <div class="input-group-btn">
                    <button
                      id="js-search-btn-dropdown"
                      class="btn btn-default dropdown-toggle"
                      type="button"
                      data-toggle="dropdown"
                      data-name="name"
                    >
                      名称 <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                      <li><a href="javascript:;" data-name="name">名称</a></li>
                      <li><a href="javascript:;" data-name="type_name">属性</a></li>
                      <li><a href="javascript:;" data-name="level">等级</a></li>
                      <li><a href="javascript:;" data-name="item_id">id</a></li>
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

                      var query = _.omit($.parseQS(), ['page', 'name', 'type_name', 'item_id', 'level']);

                      window.location.href = $.buildURL('admin/item', query, _.set({}, $btn_dropdown.data('name'), $input.val()));
                    });

                    // 回车触发搜索
                    $input.on('keydown', function (ev) {
                      if (ev.keyCode == 13) {
                        $btn_submit.click();
                      }
                    });

                    // 保留搜索记录
                    _.forIn($.parseQS(), function (v, k) {
                      if (_.includes(['name', 'type_name', 'item_id', 'level'], k)) {
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
                </colgroup>
                <thead>
                <tr>
                  <th>ID</th>
                  <th>名称</th>
                  <th>描述</th>
                  <th>属性</th>
                  <th>等级</th>
                  <th>功能</th>
                  <th>回收金币</th>
                  <th>回收经验</th>
                  <th>创建时间</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($paginate->items() as $row)
                  <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->description }}</td>
                    <td>{{ $row->type_name }}</td>
                    <td>{{ $row->level }}</td>
                    <td>{{ $row->content }}</td>
                    <td>{{ $row->recycle_coin }}</td>
                    <td>{{ $row->recycle_exp }}</td>
                    <td>{{ $row->created_at }}</td>
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
@endsection
