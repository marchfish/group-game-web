@extends('admin/layout')

@section('content')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-4">
                                <a type="button" class="btn btn-success" href="{!! URL::to('admin/sys-notice/new') !!}">
                                    <i class="fa fa-plus"></i>
                                    新增
                                </a>
                            </div>
                        </div>
                    </div>
                    @if (count($paginate->items()) > 0)
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-striped table-bordered table-hover text-center vertical-align-middle">
                                <colgroup>
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="20%">
                                    <col width="20%">
                                    <col width="15%">
                                    <col width="15%">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>城市</th>
                                    <th>标题</th>
                                    <th>开始时间</th>
                                    <th>截止时间</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($paginate->items() as $row)
                                    <tr>
                                        <td>{!! $row->id !!}</td>
                                        <td>{!! $row->city == '' ? '不限' : $row->city !!}</td>
                                        <td>{!! $row->title !!}</td>
                                        <td>{!! $row->start_at !!}</td>
                                        <td>{!! $row->end_at !!}</td>
                                        <td>{!! $row->status1 !!}</td>
                                        <td>
                                            <a href="/admin/sys-notice/edit?sys_notice_id={!! $row->id !!}">编辑</a>
                                            |
                                            <a class="js-a-sys-notice" data-sys-notice-id="{!! $row->id !!}" href="javascript:;">删除</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer">
                            <div class="row">
                                <div class="col-xs-6">
                                    <p>从第 <b>{!! $paginate->firstItem() !!}</b> 条到第 <b>{!! $paginate->lastItem() !!}</b> 条，共 <b>{!! $paginate->total() !!}</b> 条</p>
                                </div>
                                <div class="col-xs-6">
                                    {!! $paginate->appends(Request::all())->links('admin/pagination') !!}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <script>
        $(function () {
            // 通过/驳回
            $('.js-a-sys-notice').on('click', function (ev) {
                ev.preventDefault();
                $.csrf({
                    method: 'delete',
                    url: $.buildURL('admin/sys-notice'),
                    data: {
                        sys_notice_id:$(this).data('sysNoticeId'),
                    },
                }, function (res) {
                    $.alertSuccess(res.message, function () {
                        window.location.reload();
                    });
                });
            });

            // 禁用/解禁
            $('.js-a-pp-user-disabled').on('click', function (ev) {
                ev.preventDefault();
                if ( $(this).html() === '禁用' )
                {
                    var method = 'delete';
                }else {
                    var method = 'put';
                }

                $.csrf({
                    method: method,
                    url: $.buildURL('admin/pp-user/disabled'),
                    data: {
                        user_id:$(this).data('userId'),
                    },
                }, function (res) {
                    $.alertSuccess(res.message, function () {
                        window.location.reload();
                    });
                });
            });
        })
    </script>

@endsection