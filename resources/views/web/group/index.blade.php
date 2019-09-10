<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
  <title>公司主页</title>
  <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css') !!}">
  <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/wr-1.0.0/css/wr-css.css') !!}">
  <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/stat/css/stat.css') !!}">
  <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-2.2.4/jquery.min.js') !!}"></script>
  <script type="text/javascript" src="{!! URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js') !!}"></script>
  <script type="text/javascript" src="{!! URL::asset('forestage/public/qrcode-1.0.0/jquery.qrcode.min.js') !!}"></script>
</head>
<body style="background: #F7F7FA">
@if(isset($data))
  <div class="row wr-padding-bottom-20">
    <div class="text-center" style="background: url('{!! URL::asset('forestage/group/img/bg-01.png?v=0.0.1') !!}');background-size: cover;">
      <div class="wr-top-30 wr-bottom-20">
        <img style="border-radius: 148px" src="{!! empty($data->logo) ? URL::asset('forestage/group/img/default-logo.jpg?v=0.0.1') : $data->logo !!}" alt="" width="100px" height="100px">
      </div>
      <p class="text-center wr-font-20-515 wr-margin-05" style="font-weight: bold">{!! $data->name ?? '- -' !!}</p>
    </div>
  </div>
  <div class="wr-stat-header wr-top-20 wr-border-radius-15 text-center row wr-margin-05 wr-shadow" style="color: #222222;background: #fff;">
    <div class="col-xs-3" style="height: 110px;">
      <div class="wr-run-01 wr-line-03 wr-top-32">
        <div class="wr-font-13-515" style="margin-bottom: 4px">{!! $data->realname ?? '- -' !!}</div>
        <span class="wr-font-10-515">公司法人</span>
      </div>
    </div>
    <div class="col-xs-6" style="height: 110px; position: relative;">
      <div class="wr-run-01 wr-line-03 wr-top-32">
        <div class="wr-font-13-515" style="margin-bottom: 4px">{!! isset($data->created_at) ? substr($data->created_at, 0, 10 ) : '- -' !!}</div>
        <span class="wr-font-10-515">注册时间</span>
      </div>
    </div>
    <div class="col-xs-3" style="height: 110px">
      <div class="wr-run-01 wr-top-32">
        <div class="wr-font-13-515" style="margin-bottom: 4px">{!! $data->count ?? '- -' !!}</div>
        <span class="wr-font-10-515">公司人数</span>
      </div>
    </div>
  </div>
  <div class="wr-border-radius-15 text-left row wr-margin-05 wr-padding-05" style="color: #222222;background: #515884;margin-top: 20px;padding-bottom: 30px;">
    <div class="row wr-top-20">
      <div style="float: left">
        <img style="border-radius: 100px;" src="{!! empty($data->logo) ? URL::asset('forestage/group/img/default-logo.jpg?v=0.0.1') : $data->logo !!}" alt="" width="40px" height="40px">
      </div>
      <div style="height: 57px; float: left">
        <div style="margin-left:18px;">
          <h4 class="wr-content-bg-fff-h4 wr-font-14-fff" style="margin-bottom: 2px;font-weight: normal">公司介绍</h4>
          <span class="wr-content-bg-fff-span" style="color: rgba(255, 255, 255, 0.64 )">Introduction</span>
        </div>
      </div>
    </div>
    <div class="wr-line" style="background: rgba(255, 255, 255, 0.1)"></div>
    <div class="wr-top-15 wr-indent" style="color: rgba(255, 255, 255, 0.64 );">
      {!! $data->description ?? '- -' !!}
    </div>
  </div>
  <div class="row wr-padding-bottom-40 text-center">
    <div class="wr-top-20 wr-bottom-20">
      <img src="{!! URL::asset('forestage/group/img/logo.png?v=0.0.1') !!}" alt="" width="60px" height="60px">
    </div>
    <h3 class="wr-font-15-515 wr-top-20">您好，欢迎使用找跑跑！</h3>
  </div>
  <div style="margin: 10px auto; height: 0px; width:38%; background: rgba(85, 88, 105, .5);border-radius: 50px    "></div>
  <div id="wxCode" class="text-center" style="margin-bottom: 30px"></div>
@else
  <div class="wr-center" style="color: #6e6e6e;font-size: 20pt">
    暂无数据!
  </div>
@endif
</body>
<script>
    // 二维码
    $(function () {
        $('#wxCode').qrcode({width:120,height:120,correctLevel:0,text:window.location.href});
    })
</script>
</html>