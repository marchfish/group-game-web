@extends('admin/layout')

@section('content')
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box box-primary">
        <div class="box-header">
          <div class="row">
            <div class="col-xs-4">
              <a type="button" class="btn btn-success" href="{!! URL::to('admin/admin_account/new') !!}">
                <i class="fa fa-plus"></i>
                新增
              </a>
            </div>
          </div>
        </div>
        @if ($accounts->count() > 0)
        <div class="box-body table-responsive no-padding">
          <table class="table table-striped table-bordered table-hover text-center">
            <colgroup>
              <col width="20%">
              <col width="20%">
              <col width="20%">
              <col width="20%">
              <col width="20%">
            </colgroup>
            <thead>
              <tr>
                <th>账号</th>
                <th>昵称</th>
                <th>角色</th>
                <th>创建时间</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($accounts as $account)
              <tr>
                <td>{{ $account->username }}</td>
                <td>{{ $account->nickname }}</td>
                <td>
                @foreach ($account->roles as $role)
                {{ $role->name }}
                @endforeach
                </td>
                <td>{!! $account->created_at !!}</td>
                <td>
                  <a href="{!! URL::to('admin/admin_account/edit') . '?id=' . $account->id !!}">编辑</a>
                  <a class="js-a-delete" href="{!! URL::to('admin/admin_account') . '?id=' . $account->id !!}">删除</a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <script>
          $(function () {
            $('.js-a-delete').on('click', function (ev) {
              ev.preventDefault();

              if (window.confirm('确认删除吗？')) {
                $.csrf({
                  method: 'delete',
                  url: this.href,
                }, function (res) {
                  $.alertSuccess(res.message, function () {
                    window.location.reload();
                  });
                });
              }
            });
          });
          </script>
        </div>
        <div class="box-footer">
          <div class="row">
            <div class="col-xs-6">
              <p>从第 <b>{!! $accounts->firstItem() !!}</b> 条到第 <b>{!! $accounts->lastItem() !!}</b> 条，共 <b>{!! $accounts->total() !!}</b> 条</p>
            </div>
            <div class="col-xs-6">
              {!! $accounts->appends(Request::all())->links('admin/pagination') !!}
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
