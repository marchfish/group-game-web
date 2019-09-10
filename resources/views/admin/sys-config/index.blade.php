@extends('admin/layout')

@section('content')
    <section class="content-header">
        <h1>系统设置</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-lg-offset-3 col-lg-6">
                <div class="box box-primary">
                    @if (!empty($rows))
                        <div class="box-body">
                            <form id="js-form" action="{!! URL::to('admin/sys-config') !!}" >
                                @foreach ($rows as $row)
                                    <div class="form-group">
                                        <p>{!! $row->key !!}</p>
                                        <label>{!! $row->description !!}</label>
                                        <textarea class="form-control" name="settings[{!! $row->id !!}]" rows="3" autocomplete="off">{!! $row->value !!}</textarea>
                                    </div>
                                @endforeach
                            </form>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary pull-right" form="js-form"><i class="fa fa-save"></i> 保存</button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <script>
        $(function () {
            $('#js-form').on('submit', function (ev) {
                ev.preventDefault();

                $.csrf({
                    method: 'put',
                    url: this.action,
                    data: $(this).serialize(),
                }, function (res) {
                    $.alertSuccess(res.message, function () {
                        window.location.reload();
                    });
                });
            });
        });
    </script>
@endsection
