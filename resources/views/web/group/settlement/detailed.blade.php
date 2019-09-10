<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>账目结算-历史查看-详细</title>
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/wr-1.0.0/css/wr-css.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/stat/css/stat.css') !!}">
    <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-2.2.4/jquery.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-ias-2.2.2/jquery-ias.min.js') !!}"></script>
</head>
<body style="background: #fff">
<div style="background: #515884;">
    <div class="row wr-bg-01">
        <div class="text-left">
            <h3 class="wr-h3" style="margin-right: 2px;color: #fff"><span class="wr-square"></span>本次结算金额</h3>
            <span style="float: right;font-size: 12pt">{!! $data['date_from'] !!}</span>
        </div>
    </div>
    <div class="row wr-padding-top-20 wr-padding-bottom-20" style="position: relative;margin-left: 4%">
        <div class="wr-flex-center">
            <img src="{!! URL::asset('forestage/stat/img/man.png?v=0.0.1') !!}" alt="" width="18px" height="18px">
            <span class="wr-content-title-fff">人数：{!! $data['count'] !!}人</span>
            <img style="margin-left: 10%;" src="{!! URL::asset('forestage/stat/img/money.png?v=0.0.1') !!}" alt="" width="18px" height="18px">
            <span class="wr-content-title-fff">金额：{!! $data['total_salary'] !!}元</span>
        </div>
    </div>
</div>
<div class="row wr-bottom-100" style="vertical-align: middle">
    @foreach ($data['rows'] as $row)
        <div class="wr-content-bg-fff">
            <div class="row wr-top-30">
                <div style="float: left">
                    <img style="border-radius: 10px;" src="{!! $row->avatar !!}" alt="" width="62px" height="62px">
                </div>
                <div style="height: 57px; float: left">
                    <div style="margin-left:18px;">
                        <h4 class="wr-content-bg-fff-h4" style="margin-bottom: 7px">{!! $row->realname !!}</h4>
                        <span class="wr-content-bg-fff-span">结算金额：￥{!! rmb($row->salary) !!}元</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="wr-line"></div>
    @endforeach
</div>
</body>
</html>