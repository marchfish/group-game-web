@extends('admin/layout')

@section('content')
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box box-primary">
        <div class="box-header">
          <div class="row">
            <div class="col-xs-4">
              <a type="button" class="btn btn-success" href="{!! URL::to('admin/admin_role/new') !!}"><i class="fa fa-plus"></i> 新增</a>
            </div>
          </div>
        </div>
        @if ($roles->count() > 0)
        <div class="box-body table-responsive no-padding">
          <table class="table table-striped table-bordered table-hover text-center">
            <colgroup>
              <col width="50%">
              <col width="50%">
            </colgroup>
            <thead>
              <tr>
                <th>角色名称</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($roles as $role)
              <tr>
                <td>{{ $role->name }}</td>
                <td>
                  <a href="{!! URL::to('admin/admin_role/edit') . '?id=' . $role->id !!}">编辑</a>
                  <a class="js-a-delete" href="{!! URL::to('admin/admin_role') . '?id=' . $role->id !!}">删除</a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <script>
          $(function () {
            $('.js-a-delete').on('click', function (ev) {
              ev.preventDefault();

              if (confirm('确认删除吗？')) {
                $.csrf({
                  method: 'delete',
                  url: this.href,
                }, function (res) {
                  $.alertSuccess(res.message, function () {
                    location.reload();
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
              <p>从第 <b>{!! $roles->firstItem() !!}</b> 条到第 <b>{!! $roles->lastItem() !!}</b> 条，共 <b>{!! $roles->total() !!}</b> 条</p>
            </div>
            <div class="col-xs-6">
              {!! $roles->appends(Request::all())->links('admin/pagination') !!}
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
