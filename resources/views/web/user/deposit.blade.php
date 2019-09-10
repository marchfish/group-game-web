<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>收支明细</title>
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/wr-1.0.0/css/wr-css.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/stat/css/stat.css') !!}">
    <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-2.2.4/jquery.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-ias-2.2.2/jquery-ias.min.js') !!}"></script>
</head>
<body>
<div style="background: #515884;height: 195px;">
    <div class="row wr-bg-01">
        <div id="date-picker-income-zpp" class="text-left" style="float: left;">
            <h3 class="wr-h3 {!! $date_month != '' ? 'wr-color-c3c' : '' !!}" style="color: #fff">{!!  $data['date_show'] !!}</h3>
            <span class="{!! $date_month != '' ? 'caret-c3c' : 'caret' !!}"></span>
        </div>
        <div style="float: right;display: flex;align-items:center;">
            <span class="wr-date-month {!! $date_month == -2 ? 'wr-active-02' : '' !!}" data-month="-2">今天</span>
            <span class="wr-line-02" style="margin: 0 10px"></span>
            <span class="wr-date-month {!! $date_month == -1 ? 'wr-active-02' : '' !!}" data-month="-1">昨天</span>
            <span class="wr-line-02" style="margin: 0 10px"></span>
            <span class="wr-date-month {!! $date_month == 0 && $date_month != '' ? 'wr-active-02' : '' !!}" data-month="0">本月</span>
            <span class="wr-line-02" style="margin: 0 10px"></span>
            <span class="wr-date-month {!! $date_month == 1 ? 'wr-active-02' : '' !!}" data-month="1">上月</span>
            {{--<span class="wr-line-02" style="margin: 0 10px"></span>--}}
            {{--<span class="wr-date-month {!! $date_month == 6 ? 'wr-active-02' : '' !!}" data-month="6">近半年</span>--}}
        </div>
    </div>
    <div class="text-center wr-top-20 single wr-padding-bottom-50 wr-padding-top-20">
        <h2 class="wr-top-30">￥{!! rmb($data['amount']) !!}<span class="wr-meta">元</span></h2>
        <span style="color: #353537;font-size: 13pt">当前余额</span>
    </div>
</div>
<div class="row wr-top-115">
    <div class="wr-table-title-div {!! $table == 1 ? 'wr-bg-color-fff' : 'wr-color-b8b' !!}" data-table="1">收支明细</div>
    <div class="wr-table-title-div {!! $table == 2 ? 'wr-bg-color-fff' : 'wr-color-b8b' !!}" data-table="2">收入明细</div>
    <div class="wr-table-title-div {!! $table == 3 ? 'wr-bg-color-fff' : 'wr-color-b8b' !!}" data-table="3">支出明细</div>
</div>
<div class="row" style="background: #fff;vertical-align: middle">
    <div class="wr-top-20 clearfix" style="padding: 0 6%;">
        <h3 class="wr-h3"><span class="wr-square"></span>明细数据</h3>
        <span style="float: right;color: #B2B2B3;font-size: 12pt">共{!! $data['count'] !!}单</span>
    </div>
    <div class="wr-line wr-top-15 wr-bottom-20"></div>
    <div class="wr-bottom-60 wr-content" style="padding: 0 6%;color: #353537;">
        @foreach ($paginate['data'] as $row)
            <div class="row wr-top-10 wr-bottom-20 wr-item">
                <span class="col-xs-8 wr-content-left">{!! $row->title !!}</span>
                <span class="col-xs-4 wr-content-right">￥{!! rmb($row->amount) !!}元</span>
            </div>
        @endforeach
        @if(!isset($paginate['data']) || $paginate['data'] == [])
            <div class="text-center" style="color: #B2B2B3">暂无数据</div>
        @endif
    </div>
</div>
<div class="row wr-paginate" style="color: #222222">
    {!! paging($paginate['current_page'], $paginate['last_page'], URL::to('/web/zpp/deposit'), Request::all()) !!}
</div>
</body>
<script>
    // 选择统计
    $(function () {
        var url = "{!! build_qs( Request::except(['date_month']) ) !!}";
        $('.wr-date-month').on('click', function () {
            window.location.href="/web/zpp/deposit?date_month=" + $(this).data('month') + '&' + url;
        })
    })

    $(function () {
        var ias = $.ias({
            container: ".wr-content", //包含所有文章的元素
            item: ".wr-item", //文章元素
            pagination: ".wr-paginate", //分页元素
            next: ".wr-next", //下一页元素
        });
        ias.extension(new IASSpinnerExtension({
            text: '加载中，请稍候...', // 加载完成时的提示
            html: '<p style="text-align: center;margin: 10px auto 20px;color:#999;">加载中，请稍候...</p>',
        }));
        ias.extension(new IASTriggerExtension({
            text: '点击加载更多', //此选项为需要点击时的文字
            html: '<p style="text-align: center; cursor: pointer;"><a>{text}</a></p>',
            offset: !1, //设置此项，如 offset:2 页之后需要手动点击才能加载，offset:!1 则一直为无限加载
        }));
        ias.extension(new IASNoneLeftExtension({
            text: '数据已全部加载！', // 加载完成时的提示
            html: '<p style="text-align: center;margin: 10px auto 20px;color:#999;font-size:12px;">数据已全部加载！</p>',
        }));
    })

    //间距
    $(function () {
        if ($(document.body).outerWidth(true) <= 340){
            $('.wr-line-02').css('margin', '0 8px')
        };
    })

    $(function () {
        var url = "{!! build_qs( Request::except(['table']) ) !!}";
        $('.wr-table-title-div').on('click', function () {
            window.location.href="/web/zpp/deposit?table=" + $(this).data('table') + '&' + url;
        })
    })
</script>
</html>