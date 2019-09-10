@extends('admin/layout')

@section('content')
    <style>
        .bg-color-faf {
            background: #fafafa;
        }
        .time {
            color:#ccc;
        }
        .img-circle {
            width: 32px;
            height: 32px;
        }
    </style>
    <section class="content-header">
        <h1>订单详情</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <ul class="nav nav-tabs">
                        <li class="download-table-button active">
                            <a class="download-table-button" href="#order" role="tab" data-toggle="tab">
                                订单信息
                            </a>
                        </li>
                        <li class="download-table-button">
                            <a class="download-table-button" href="#pay" role="tab" data-toggle="tab">
                                支付记录
                            </a>
                        </li>
                        <li class="download-table-button">
                            <a class="download-table-button" href="#history" role="tab" data-toggle="tab">
                                历史记录
                            </a>
                        </li>
                        <li class="download-table-button">
                            <a class="download-table-button" href="#bid" role="tab" data-toggle="tab">
                                竞价记录
                            </a>
                        </li>
                    </ul>
                    <!-- 面板区 -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="order">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th class="bg-color-faf">订单信息</th>
                                    </tr>
                                    <tr>
                                        <td>订单ID：<span>{!! $data['order']->id !!}</span></td>
                                    </tr>
                                    <tr>
                                        <td>金额：<span>{!! rmb($data['order']->pay_fee) !!}</span></td>
                                    </tr>
                                    <tr>
                                        <td>下单时间：<span>{!! $data['order']->created_at !!}</span></td>
                                    </tr>
                                    <tr>
                                        <td>订单状态：<span>{!! $data['order']->order_status !!}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th class="bg-color-faf">顾客信息</th>
                                </tr>
                                <tr>
                                    <td>UID：<span>{!! $data['order']->zpp_user_id !!}</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="pull-left image">
                                            <img src="{!! $data['order']->zpp_avatar !!}" class="img-circle" alt="User Image">
                                            <span>：{!! $data['order']->nickname !!}</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>电话：<span>{!! $data['order']->zpp_tel !!}</span></td>
                                </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th class="bg-color-faf">跑跑信息</th>
                                </tr>
                                <tr>
                                    <td>UID：<span>{!! $data['order']->pp_user_id !!}</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="pull-left image">
                                            <img src="{!! $data['order']->pp_avatar !!}" class="img-circle" alt="User Image">
                                            <span>：{!! $data['order']->realname !!}</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>电话：<span>{!! $data['order']->pp_tel !!}</span></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="pay">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th class="bg-color-faf">交易记录</th>
                                </tr>
                                <tr>
                                    <td>支付金额：<span>{!! rmb($data['pay']->pay_fee ?? $data['order']->pay_fee ) !!}</span></td>
                                </tr>
                                <tr>
                                    <td>支付方式：<span>{!! $data['pay']->payway ?? '钱包付款' !!}</span></td>
                                </tr>
                                <tr>
                                    <td>支付时间：<span>{!! $data['pay']->created_at ?? $data['order']->payed_at !!}</span></td>
                                </tr>
                                <tr>
                                    <td>支付单号：<span>{!! $data['pay']->no ?? '' !!}</span></td>
                                </tr>
                                <tr>
                                    <td>交易状态：<span>{!! $data['pay']->sys_pay_status ?? '' !!}</span></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="history">
                            @foreach($data['history'] AS $row)
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th class="bg-color-faf">{!! $row->title !!}</th>
                                </tr>
                                <tr>
                                    <td>
                                        {!! $row->content !!}
                                        <div class="time">{!! $row->created_at !!}</div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            @endforeach
                        </div>
                        <div role="tabpanel" class="tab-pane" id="bid">
                            @foreach($data['bid'] AS $row)
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th class="bg-color-faf">竞价于：{!! $row->created_at !!}</th>
                                    </tr>
                                    <tr>
                                        <td>UID：<span>{!! $row->user_id !!}</span></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="pull-left image">
                                                <img src="{!! $row->avatar !!}" class="img-circle" alt="User Image">
                                                <span>：{!! $row->realname !!}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>位置：<span>{!! $row->label !!}</span></td>
                                    </tr>
                                    <tr>
                                        <td>报价：<span>{!! rmb($row->price) !!}</span></td>
                                    </tr>
                                    <tr>
                                        <td>承诺时间：<span>{!! secToTime($row->timelimit) !!}</span></td>
                                    </tr>
                                    <tr>
                                        <td>好评率：<span><img width="90" src="{!! $row->star_img !!}"></span></td>
                                    </tr>
                                </tbody>
                            </table>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>

    </script>
@endsection