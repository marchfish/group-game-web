<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>我的成长</title>
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/swiper-4.5.0/css/swiper.min.css') !!}">
    <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-2.2.4/jquery.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js') !!}"></script>
    <script type="text/javascript" src="{!! URL::asset('forestage/public/swiper-4.5.0/js/swiper.min.js') !!}"></script>
</head>
<style>
h1,h2,h3,h4,h5,p {
    margin: 0;
    padding: 0;
}
html {
    overflow-x: hidden;
}
body {
    color: #fff;
    overflow-x: hidden;
}
.table>thead>tr>th {
    border: 0 !important;
}
.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
    border: 0 !important;
    vertical-align: middle !important;
}
.gradual-bg {
    background: -webkit-radial-gradient(#6C75A9, #445083); /* Safari 5.1 - 6.0 */
    background: -o-radial-gradient(#6C75A9, #445083); /* Opera 11.6 - 12.0 */
    background: -moz-radial-gradient(#6C75A9, #445083); /* Firefox 3.6 - 15 */
    background: radial-gradient(#6C75A9, #445083); /* 标准的语法（必须放在最后） */
}
.progress-bar {
    background-color: #F9808A;
}
.progress {
    height: 5px;
    background: #000;
    overflow: inherit;
    margin-left: 40px;
}
.origin-left{
    position: absolute;
    top:-15px;
    background: #F9808A;
    border-radius: 100px;
    font-size: 10pt;
    /*display:inline;*/
    /*padding: 8px 10px;*/
    text-align: center;
    width: 73px;
    height: 35px;
    line-height: 35px;
    -moz-box-shadow:0px 2px 10px rgba(0,0,0,.3);
    -webkit-box-shadow:0px 2px 10px rgba(0,0,0,.3);
    box-shadow:0px 2px 10px rgba(0,0,0,.3);
    z-index: 15;
}
.origin-right {
    position: absolute;
    top:-15px;
    border-radius: 100px;
    font-size: 10pt;
    text-align: center;
    width: 73px;
    height: 35px;
    line-height: 33px;
    -moz-box-shadow:0px 2px 10px rgba(0,0,0,.3);
    -webkit-box-shadow:0px 2px 10px rgba(0,0,0,.3);
    box-shadow:0px 2px 10px rgba(0,0,0,.3);
    right: 5%;
    background: #2E2D3C;
    border: 2px solid #222222;
    color: #6e6e6e;
    z-index: 15;
}
.bubble{
    z-index: 1;
    top: -93px;
    right: -33px;
    height: 45px;
    width: 78px;
    background: #F9808A;
    position: absolute;
    border-radius: 5px;
    font-size: 9pt;
}
.caret {
    margin-top: -15px;
    margin-left: -12px;
    border-top: 8px dashed;
    border-right: 2px solid transparent;
    border-left: 8px solid transparent;
    color: #F9808A;
}
/*向右*/
.triangle_border_right span{
    display:block;
    width:0;
    height:0;
    border-width:5px 0 5px 5px;
    border-style:solid;
    border-color:transparent transparent transparent #fff;/*透明 透明 透明 黄*/
    position:absolute;
    top:40%;
    right: 5px;
}
.line {
    height: 1px;
    width: 100%;
    background: #F2F2F2;
    margin-top: 10px;
}
.swiper-container {
    width: 100%;
    height: 100%;
}
.swiper-slide {
    text-align: center;
    font-size: 18px;
    margin-right: 0 !important;
    /*background: #fff;*/

    /* Center slide text vertically */
    display: -webkit-box;
    display: -ms-flexbox;
    display: -webkit-flex;
    display: flex;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    -webkit-justify-content: center;
    justify-content: center;
    -webkit-box-align: center;
    -ms-flex-align: center;
    -webkit-align-items: center;
    align-items: center;
}
.percentage {
    left: -74px !important;
    top:-74px !important;
}
.run-man {
    position: absolute;
    top: 53px;
    left: 20px;
}
</style>
<body>
<div class="gradual-bg">
    <div class="container" style="padding-top: 35px">
        <div class="row">
            <div class="col-xs-2">
                <img src="{!! $data['my_level']['icon'] !!}" alt="" width="80px" height="80px">
            </div>
            <div class="col-xs-9" style="margin-top: 22px;margin-left: 22px">
            <h3>{!! $data['my_level']['name'] !!}</h3>
                <div style="color: #fff;font-size: 13pt">打败<span style="color: #F9808A">{!! $data['my_level']['ko'] . '%'!!}</span>的骑手</div>
            </div>
        </div>
        <div class="row" style="position: relative;padding-left: 15px;margin-top: 140px;margin-bottom: 40px">
            <div class="col-xs-10 col-xs-offset-1" style="position: relative">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" aria-valuenow="60"
                         aria-valuemin="0" aria-valuemax="100" style="width: {!! $data['my_level']['percentage'] !!};position: relative">
                        <div class="bubble {!! $data['my_level']['percentage'] =='0%' ? 'percentage' : '' !!}">
                            <div style="font-size: 14pt;padding-top: 6px">{!! $data['my_level']['score'] !!}</div>
                            <div style="margin-top: -1px">经验值</div>
                            <span class="caret"></span>
                            <div class="triangle_border_right">
                                <span></span>
                            </div>
                            <div class="run-man {!! $data['my_level']['percentage'] =='0%' ? 'hide' : '' !!}">
                                <img src="{!! URL::asset('forestage/pp-user/img/runman.png?v=0.0.1') !!}" alt="" width="35px">
                            </div>
                        </div>
                    </div>
                    <div class="origin-left" style="margin-left: -73px">
                        {!! $data['my_level']['name'] !!}
                    </div>
                </div>
                <div style="height: 5px;width: 100px;position: absolute;right: -19%;top:0;background: #000"></div>
                <div class="origin-right">
                    {!! $data['my_level']['next_name'] !!}
                </div>
            </div>
        </div>
        <div class="row text-center" style="margin: 20px 0;">
            @if($data['my_level']['level'] != 'MAX')
                <h3 style="margin-bottom: 5px">再获得<span style="color: #F9808A">{!! $data['my_level']['next_score'] - $data['my_level']['score'] !!}</span>经验可升<span style="color: #F9808A">{!! $data['my_level']['next_name'] !!}</span></h3>
            @else
                <h3 style="margin-bottom: 5px">恭喜您！您目前达到最高等级！</h3>
            @endif
        </div>
    </div>
</div>
<div class="container" style="color: #585858">
    <div class="row text-center" style="padding-top: 20px;">
        <h4 style="font-size: 16pt;color: #4F5785;font-weight: 400;margin-bottom: 5px">升级说明</h4>
        <span style="color: #919191;">成功完成1单即可获得1点经验值</span>
        <div class="line"></div>
    </div>
    <div class="box-body no-padding" style="margin-left: 3%">
        <table class="table text-center">
            <colgroup>
                <col width="23%">
                <col width="43%">
                <col width="35%">
            </colgroup>
            <thead style="font-size: 15pt;color: #F9808A">
            <tr>
                <th>经验值</th>
                <th style="text-align: center">头衔</th>
                <th style="text-align: center">徽章</th>
            </tr>
            </thead>
            <tbody style="font-size: 13pt">
            @foreach ($data['level_standard'] as $row)
                <tr>
                    <td style="text-align: left;color: #4F5785">{!! $row->score !!}</td>
                    <td style="color: #585858">{!! $row->name !!}</td>
                    <td><img src="{!! $row->icon !!}" alt="" width="60px" height="60px"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
<script>
    var swiper = new Swiper('.swiper-container', {
        slidesPerView: 3,
        spaceBetween: 30,
        freeMode: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
    });
</script>
</html>
