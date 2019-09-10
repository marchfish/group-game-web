@extends('admin/layout')

@section('content')
<section class="content-header">
  <h1>新增账号</h1>
</section>
<section class="content">
  <div class="row">
    <div class="col-lg-offset-3 col-lg-6">
      <div class="box box-primary">
        <div class="box-header with-border">
          <a class="btn btn-default" href="{!! URL::to('admin/admin_account') !!}">
            <i class="fa fa-reply"></i>
            返回
          </a>
        </div>
        <div class="box-body">
          <form id="js-form" class="form-horizontal" action="{!! URL::to('admin/admin_account') !!}">
            <div class="form-group">
              <label for="username" class="col-lg-2 control-label">用户名</label>
              <div class="col-lg-9">
                <input id="username" name="username" type="text" class="form-control" autocomplete="off">
              </div>
            </div>
            <div class="form-group">
              <label for="password"class="col-lg-2 control-label">密码</label>
              <div class="col-lg-9">
                <input id="password" name="password" type="password" class="form-control" autocomplete="off">
              </div>
            </div>
            <div class="form-group">
              <label for="repassword" class="col-lg-2 control-label">重复密码</label>
              <div class="col-lg-9">
                <input id="repassword" name="repassword" type="password" class="form-control" autocomplete="off">
              </div>
            </div>
            <div class="form-group">
              <label for="nickname" class="col-lg-2 control-label">昵称</label>
              <div class="col-lg-9">
                <input id="nickname" name="nickname" type="text" class="form-control" autocomplete="off">
              </div>
            </div>
            <div class="form-group">
              <label class="col-lg-2 control-label">角色</label>
              <div class="col-lg-9">
                @foreach ($roles as $role)
                <div class="checkbox">
                  <label>
                    <input name="roles[]" type="checkbox" value="{!! $role->id !!}">
                    {!! $role->name !!}
                  </label>
                </div>
                @endforeach
              </div>
            </div>
          </form>
          <script>
          $(function () {
            $('#js-form').on('submit', function (ev) {
              ev.preventDefault();

              $.csrf({
                method: 'post',
                url: this.action,
                data: $(this).serialize(),
              }, function (res) {
                $.alertSuccess(res.message, function () {
                  window.location='{!!URL::to('admin/admin_account') !!}';
                });
              });
            });
          });
          </script>
        </div>
        <div class="box-footer">
          <button type="submit" class="btn btn-primary pull-right" form="js-form"><i class="fa fa-save"></i> 保存</button>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
