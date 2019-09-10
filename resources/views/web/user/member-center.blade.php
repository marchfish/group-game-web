<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>功能展示</title>
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/wr-1.0.0/css/wr-css.css') !!}">
    <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-2.2.4/jquery.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js') !!}"></script>
</head>
<body>
<div class="row text-center">
    <span class="wr-top-20" style="font-size: 18pt;display: block">绑定专属团队可享受</span>
    <img class="wr-top-30" width="85%" src="{!! URL::asset('forestage/user/img/member-center-text.png?v=0.0.1') !!}" alt="">
    <img id="to-bind" class="wr-top-30 wr-bottom-20" width="55%" src="{!! URL::asset('forestage/user/img/member-center-button.png?v=0.0.1') !!}" alt="">
</div>
</body>
<script>
    $(function () {
        $("#to-bind").on("click",function () {
            $(this).attr("src", "{!! URL::asset('forestage/user/img/member-center-button-01.png?v=0.0.1') !!}");
        })
    })
</script>
</html>