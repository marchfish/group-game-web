@extends('admin/layout')

@section('content')
<section class="content-header">
  <h1>新增角色</h1>
</section>
<section class="content">
  <div class="row">
    <div class="col-lg-offset-3 col-lg-6">
      <div class="box box-primary">
        <div class="box-header with-border">
          <a class="btn btn-default" href="{!! URL::to('admin/admin_role') !!}">
            <i class="fa fa-reply"></i>
            返回
          </a>
        </div>
        <div class="box-body">
          <form id="js-form" class="form-horizontal" action="{!! URL::to('admin/admin_role') !!}">
            <div class="form-group">
              <label for="name" class="col-lg-2 control-label">角色名称</label>
              <div class="col-lg-9">
                <input id="name" name="name" type="text" class="form-control" autocomplete="off">
              </div>
            </div>
            <div class="form-group">
              <label class="col-lg-2 control-label">权限</label>
              <div class="col-lg-9">
                <div class="checkbox">
                  <ul>
                    @foreach ($permissions as $permission)
                    <li>
                      <label>
                        <input class="js-parent" name="permissions[]" type="checkbox" value="{!! $permission['id'] !!}">
                        {!! $permission['name'] !!}
                      </label>
                      @if (isset($permission['children']))
                      <ul>
                        @foreach ($permission['children'] as $child)
                        <li>
                          <label>
                            <input class="js-child" name="permissions[]" type="checkbox" value="{!! $child['id'] !!}" data-parent-id="{!! $child['parent_id'] !!}">
                            {!! $child['name'] !!}
                          </label>
                        </li>
                        @endforeach
                      </ul>
                      @endif
                    </li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
          </form>
          <script>
          $(function () {
            $('.js-parent').on('change', function (ev) {
              var $parent = $(this);

              $parent
                .closest('li')
                .find('input')
                .prop('checked', $parent.prop('checked'))
              ;
            });

            $('.js-child').on('change', function (ev) {
              var $child = $(this);
              var $parent = $('input[value="' + $child.data('parentId') +'"]');

              if ($child.prop('checked')) {
                $parent.prop('checked', true);
              } else {
                var state = false;

                $child.closest('ul').find('input').each(function (k, el) {
                  state = (state || $(el).prop('checked'));
                });

                $parent.prop('checked', state);
              }
            });

            $('#js-form').on('submit', function (ev) {
              ev.preventDefault();

              $.csrf({
                method: 'post',
                url: this.action,
                data: $(this).serialize(),
              }, function (res) {
                $.alertSuccess(res.message, function () {
                  window.location='{!!URL::to('admin/admin_role') !!}';
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
