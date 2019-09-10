<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>公司流水分析</title>
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css');  !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/wr-1.0.0/css/wr-css.css');  !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/stat/css/stat.css') !!}">
    <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-2.2.4/jquery.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/echarts-2.0.0/echarts.js') !!}"></script>
</head>
<style>
    .spot:before {
        display: block;
        position: absolute;
        content: "";
        height: 6px;
        width: 6px;
        background: #E9868B;
        border-radius: 10px;
        top: 12px;
        left: -11px;
    }
    .spot1:before {
        display: block;
        position: absolute;
        content: "";
        height: 10px;
        width: 10px;
        background: #E9868B;
        border-radius: 10px;
        top: 5px;
        left: -16px;
    }
    .spot2:before {
        display: block;
        position: absolute;
        content: "";
        height: 10px;
        width: 10px;
        background: #515884;
        border-radius: 10px;
        top: 5px;
        left: -16px;
    }
    .spot3:before {
        display: block;
        position: absolute;
        content: "";
        height: 10px;
        width: 10px;
        background: #1490F7;
        border-radius: 10px;
        top: 5px;
        left: -16px;
    }
</style>
<body>
<div style="background: #515884;height: 220px;">
    <div class="row wr-bg-01" style="margin-bottom: 10px">
        <div id="date-picker-company" class="text-left">
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
    <div class="text-center wr-top-50 single wr-padding-bottom-50 wr-padding-top-20">
        <h2 class="wr-top-30">￥{!! rmb($data['group_pay_fee'] + $data['ordinary_pay_fee']) !!}<span class="wr-meta">元</span></h2>
        <span style="color: #B2B2B3;font-size: 15pt">共{!! $data['count'] !!}单</span>
    </div>
    <div class="wr-table">
        <div id="account-company" class="text-center wr-top-10 wr-table-title">流水明细</div>
        <div id="account-analysis-company" class="text-center wr-top-10 wr-table-title wr-active-01" style="left: 110px">流水分析</div>
    </div>
</div>
<div class="row wr-top-125 wr-content-header">
   <div class="wr-top-20 clearfix" style="padding: 0 6%;">
       <h3 class="wr-h3"><span class="wr-square"></span>流水占比</h3>
       <span style="float: right;color: #B2B2B3;font-size: 12pt">共{!! $data['count'] !!}单</span>
   </div>
   <div class="wr-line wr-top-15 wr-bottom-20"></div>
    <div class="clearfix" style="padding: 0 6%;color: #353537;">
        <div class="col-xs-6" style="position: relative;height: 200px;">
            <div id="echarts-pie" style="width: 100%;height:100%"></div>
            <div class="wr-mask">
                <span class="wr-center" style="color: #B2B2B3;font-size: 11pt">￥{!! rmb($data['group_pay_fee'] + $data['ordinary_pay_fee']) !!}</span>
            </div>
        </div>
        <div class="col-xs-4" style="float: right;height: 200px; padding-top: 15%">
            <div class="text-left spot1 wr-bottom-10" style="position: relative;">
                <h3 class="wr-pie-h3">公司客户单</h3>
                <span class="wr-pie-span">￥{!! rmb($data['group_pay_fee']) !!}</span>
            </div>
            <div class="text-left spot2 wr-bottom-10" style="position: relative;">
                <h3 class="wr-pie-h3">普通用户单</h3>
                <span class="wr-pie-span">￥{!! rmb($data['ordinary_pay_fee']) !!}</span>
            </div>
        </div>
    </div>
    <div class="wr-bottom-30"></div>
</div>
<div class="row wr-top-15 wr-content-header">
    <div class="wr-top-20 clearfix" style="padding: 0 6%;">
        <h3 class="wr-h3"><span class="wr-square"></span>流水趋势</h3>
        <span style="float: right;color: #B2B2B3;font-size: 12pt">单位：{!! $date_month != '' && $date_month > 0 ? '月' : '日' !!}</span>
    </div>
    <div class="wr-line wr-top-15 wr-bottom-20"></div>
    <div class="wr-bottom-60" style="padding: 0 2%;color: #353537;">
        <div id="echarts-line" style="width: 100%;height: 300px"></div>
    </div>
</div>
</body>
<script>
    // 选择统计
    $(function () {
        var url = "{!! build_qs( Request::except(['date_month']) ) !!}";
        $('.wr-date-month').on('click', function () {
            window.location.href="/web/group/account/analysis?date_month=" + $(this).data('month') + '&' + url;
        })
    })

    //饼图
    $(function () {
        var chart = echarts.init(document.getElementById("echarts-pie"));

        chart.setOption({!! json_encode($echarts1) !!});
    });

    //折线图
    $(function () {
        var chart = echarts.init(document.getElementById("echarts-line"));

        chart.setOption({!! json_encode($echarts2) !!});
    });

    //间距
    $(function () {
        if ($(document.body).outerWidth(true) <= 340){
            $('.wr-line-02').css('margin', '0 8px')
        };
    })
</script>
</html>