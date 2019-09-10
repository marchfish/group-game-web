<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>账目结算</title>
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/layer-3.1.1/layer.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/wr-1.0.0/css/wr-css.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/stat/css/stat.css') !!}">
    <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-2.2.4/jquery.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-ias-2.2.2/jquery-ias.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/layer-3.1.1/layer.js') !!}"></script>
</head>
<style>
    .layui-layer-dialog .layui-layer-padding
    {
        color: #333 !important;
    }
</style>
<body>
<div style="background: #515884;height: 195px;">
    <div id="settlement" class="row wr-bg-01">
        <div class="text-left">
            <h3 class="wr-h3" style="color: #fff;margin-right: 2px;"><span class="wr-square"></span>{!! $data['name'] !!}</h3>
        </div>
    </div>
    <div class="text-center wr-top-20 single wr-padding-bottom-50 wr-padding-top-20">
        <h2 class="wr-top-30">￥{!! rmb($data['salary']) !!}<span class="wr-meta">元</span></h2>
        <span style="color: #B2B2B3;font-size: 13pt">应发放账目</span>
    </div>
</div>
<div class="row wr-top-65" style="vertical-align: middle">
    <div class="wr-top-20 clearfix" style="padding: 0 6%;">
        <h3 class="wr-h3"><span class="wr-square"></span>成员结算列表</h3>
        <span class="history-link" style="float: right;color: #B2B2B3;font-size: 12pt">查看结算历史<img style="margin-left: 6px;" src="{!! URL::asset('forestage/group/img/chevron.png?v=0.0.1') !!}" alt="" width="5px"></span>
    </div>
    <div class="wr-top-15 wr-bottom-20 wr-content-bg-fff">
        @foreach ($data['rows'] as $row)
        <div class="row wr-padding-top-20">
            <div style="float: left">
                <img src="{!! $row->avatar !!}" alt="" width="57px" height="57px">
            </div>
            <div class="text-left" style="height: 57px;position: relative">
                <div class="wr-center-left text-left" style="left:75px;">
                    <h4 class="wr-content-bg-fff-h4">{!! $row->realname !!}</h4>
                    <span class="wr-content-bg-fff-span">应结算：￥{!! rmb($row->salary) !!}元</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="wr-button-01 text-center" style="margin-bottom: 100px">
        结算完成
    </div>
    <form>
        <input  type="hidden" name="_token" value="{!! csrf_token() !!}">
    </form>
</div>
</body>
<script>
    $(function () {
        var url = "{!! build_qs( Request::all() ) !!}";
        $('.history-link').on('click',function () {
            window.location.href="/web/group/settlement/history?" + url;
        })

        // 申请结算
        $(".wr-button-01").on('click', function(e){

            e.preventDefault();

            var layer_div = layer.msg('正在处理中，请稍候...', {
                icon: 16,
                shade: 0.01,
                time:false
            });

            $.ajax({
                type:"post",
                url:"/web/group/settlement?" + url,
                data:$('form').serialize(),
                success:function(data){
                    layer.close(layer_div);
                    alert(data.message);
                    window.location.href="/web/group/settlement?" + url;
                },
                error:function(jqXHR){
                    layer.close(layer_div);
                    console.log("Error: "+jqXHR.status);
                }
            });
        });
    })
</script>
</html>