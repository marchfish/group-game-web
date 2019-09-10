@extends('admin/layout')

@section('content')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-4 col-xs-offset-4">
                                <div id="js-search" class="input-group">
                                    <div class="input-group-btn">
                                        <button
                                                id="js-search-btn-dropdown"
                                                class="btn btn-default dropdown-toggle"
                                                type="button"
                                                data-toggle="dropdown"
                                                data-name="realname"
                                        >
                                            跑跑姓名 <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="javascript:;" data-name="realname">跑跑姓名</a></li>
                                            <li><a href="javascript:;" data-name="tel">跑跑手机号</a></li>
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

                                            var query = _.omit($.parseQS(), ['page', 'realname', 'tel']);

                                            window.location.href = $.buildURL('admin/pp-user', query, _.set({}, $btn_dropdown.data('name'), $input.val()));
                                        });

                                        // 回车触发搜索
                                        $input.on('keydown', function (ev) {
                                            if (ev.keyCode == 13) {
                                                $btn_submit.click();
                                            }
                                        });

                                        // 保留搜索记录
                                        _.forIn($.parseQS(), function (v, k) {
                                            if (_.includes(['realname', 'tel'], k)) {
                                                $('a[data-name="' + k + '"]').click();

                                                $input.val(v);
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                    @if (count($paginate->items()) > 0)
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-striped table-bordered table-hover text-center vertical-align-middle">
                                <colgroup>
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="15%">
                                    <col width="15%">
                                    <col width="20%">
                                    <col width="15%">
                                    <col width="15%">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>分类</th>
                                    <th>UID</th>
                                    <th>姓名</th>
                                    <th>归属地</th>
                                    <th>申请时间</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($paginate->items() as $row)
                                    <tr>
                                        <td>{!! $row->is_quick == 0 ? "普通骑手" : "加急骑手" !!}</td>
                                        <td>{!! $row->user_id !!}</td>
                                        <td>{!! $row->realname !!}</td>
                                        <td>{!! $row->city !!}</td>
                                        <td>{!! $row->created_at !!}</td>
                                        <td>{!! $row->status1 !!}</td>
                                        <td>
                                            @if($row->status == 0 || $row->status == 200)
                                            <a class="js-a-pp-user-disabled" data-user-id="{!! $row->user_id !!}" href="javascript:;">禁用</a>
                                            |
                                            <a class="js-a-pp-user-disabled" data-user-id="{!! $row->user_id !!}" href="javascript:;">解禁</a>
                                            |
                                            @endif
                                            <a href='/admin/pp-user/check?user_id={!! $row->user_id . '&_ref=' . json_encode(Request::all()) !!}'>查看</a>
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
            $('.js-a-pp-user-verify').on('click', function (ev) {
                ev.preventDefault();
                if ( $(this).html() === '通过' )
                {
                    var method = 'post';
                }else {
                    var method = 'put';
                }

                $.csrf({
                    method: method,
                    url: $.buildURL('admin/pp-user'),
                    data: {
                        user_id:$(this).data('userId'),
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