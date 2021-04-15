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
                      昵称 <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                      <li><a href="javascript:;" data-name="name">昵称</a></li>
                      <li><a href="javascript:;" data-name="level">等级</a></li>
                      <li><a href="javascript:;" data-name="user_id">用户id</a></li>
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

                      var query = _.omit($.parseQS(), ['page', 'name', 'level', 'user_id']);

                      window.location.href = $.buildURL('admin/user/role', query, _.set({}, $btn_dropdown.data('name'), $input.val()));
                    });

                    // 回车触发搜索
                    $input.on('keydown', function (ev) {
                      if (ev.keyCode == 13) {
                        $btn_submit.click();
                      }
                    });

                    // 保留搜索记录
                    _.forIn($.parseQS(), function (v, k) {
                      if (_.includes(['name', 'level', 'user_id'], k)) {
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
                  <th>用户ID</th>
                  <th>昵称</th>
                  <th>等级</th>
                  <th>当前血量</th>
                  <th>血量上限</th>
                  <th>当前蓝量</th>
                  <th>蓝量上限</th>
                  <th>攻击力</th>
                  <th>魔法力</th>
                  <th>暴击</th>
                  <th>闪避</th>
                  <th>防御</th>
                  <th>经验值</th>
                  <th>金币数</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($paginate->items() as $row)
                  <tr>
                    <td>{{ $row->user_id }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->level }}</td>
                    <td>{{ $row->hp }}</td>
                    <td>{{ $row->max_hp }}</td>
                    <td>{{ $row->mp }}</td>
                    <td>{{ $row->max_mp }}</td>
                    <td>{{ $row->attack }}</td>
                    <td>{{ $row->magic }}</td>
                    <td>{{ $row->crit }}</td>
                    <td>{{ $row->dodge }}</td>
                    <td>{{ $row->defense }}</td>
                    <td>{{ $row->exp }}</td>
                    <td>{{ $row->coin }}</td>
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
