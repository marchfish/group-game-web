@extends('admin/layout')

@section('content')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    @if (count($paginate->items()) > 0)
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-striped table-bordered table-hover text-center vertical-align-middle">
                                <colgroup>
                                    <col width="5%">
                                    <col width="5%">
                                    <col width="8%">
                                    <col width="5%">
                                    <col width="8%">
                                    <col width="5%">
                                    <col width="10%">
                                    <col width="11%">
                                    <col width="8%">
                                    <col width="8%">
                                    <col width="6%">
                                    <col width="8%">
                                    <col width="5%">
                                    <col width="8%">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>UID</th>
                                    <th>姓名</th>
                                    <th>手机号</th>
                                    <th>归属地</th>
                                    <th>提现金额</th>
                                    <th>开户名</th>
                                    <th>银行账号</th>
                                    <th>开户银行</th>
                                    <th>提现时间</th>
                                    <th>处理人</th>
                                    <th>备注信息</th>
                                    <th>处理时间</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($paginate->items() as $row)
                                    <tr>
                                        <td>{!! $row->user_id !!}</td>
                                        <td>{!! $row->realname !!}</td>
                                        <td>{!! $row->tel !!}</td>
                                        <td>{!! $row->city !!}</td>
                                        <td>{!! rmb($row->amount) !!}</td>
                                        <td>{!! $row->bank_account_name !!}</td>
                                        <td>{!! $row->bank_card_no !!}</td>
                                        <td>{!! $row->bank_address !!}</td>
                                        <td>{!! $row->created_at !!}</td>
                                        <td>{!! $row->reply_nickname !!}</td>
                                        <td>{!! $row->reply !!}</td>
                                        <td>{!! $row->replay_at ?? '' !!}</td>
                                        <td>{!! $row->withdraw_status !!}</td>
                                        <td>
                                            @if($row->status == 150)
                                            <a class="js-a-withdraw-pass" data-user-withdraw-id="{!! $row->id !!}" href="javascript:;">通过</a>
                                            |
                                            <a class="js-a-withdraw-disabled" data-user-withdraw-id="{!! $row->id !!}" href="javascript:;">驳回</a>
                                            @endif
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
        {{--驳回--}}
        <div class="row js-form-disabled hide" style="position: fixed;top: 40%;left: 50%;">
            <div class="box box-primary">
                <div class="box-body">
                    <form id="js-form" action="{!! URL::to('admin/withdraw/pp-user') !!}" >
                        <div class="form-group">
                            <label>请填写禁用原因:</label>
                            <input type="hidden" name="user_withdraw_id" value="" />
                            <textarea class="form-control" name="reply" rows="3" autocomplete="off"></textarea>
                        </div>
                    </form>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" form="js-form"> 确 定 </button>
                        <button type="submit" class="btn btn-primary pull-right cancel"> 取 消 </button>
                    </div>
                </div>
            </div>
            <script>
                $(function () {
                    // 显示驳回提现理由窗口
                    $('.js-a-withdraw-disabled').on('click', function (ev) {
                        ev.preventDefault();
                        $('input[name="user_withdraw_id"]').val($(this).data('userWithdrawId'));
                        $('.js-form-disabled').prop('className', 'row js-form-disabled');
                    });

                    // 提交驳回信息
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

                    //取消按钮
                    $('.cancel').on('click', function (ev) {
                        ev.preventDefault();
                        $('.js-form-disabled').prop('className', 'row js-form-disabled hide');
                    });
                })
            </script>
        </div>
    </section>
    <script>
        $(function () {
            // 通过/驳回
            $('.js-a-withdraw-pass').on('click', function (ev) {
                ev.preventDefault();
                $.csrf({
                    method: 'post',
                    url: $.buildURL('admin/withdraw/pp-user'),
                    data: {
                        user_withdraw_id:$(this).data('userWithdrawId'),
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