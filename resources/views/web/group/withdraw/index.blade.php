<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>公司提现</title>
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
    .salary {
        width: 20px;
        height: 18px;
        line-height: 18px;
        background: #AFAFAF;
        color: #fff;
        border-radius: 50px;
        margin-left: 2px;
    }
    .layui-layer-content {
        color: #222222;
        padding: 20px;
    }
    .form-control {
        background-color: #fff !important;
        border: 1px solid #ccc !important;
        color: #222222;
    }
    .btn-primary {
        background-color: #515884;
        border-color: #515884;
    }
</style>
<body>
<div style="background: #515884;height: 195px;">
    <div id="withdraw" class="row wr-bg-01">
        <div class="text-left">
            <h3 class="wr-h3" style="color: #fff;margin-right: 2px;"><span class="wr-square"></span>{!! $data['name'] !!}</h3>
        </div>
    </div>
    <div class="text-center wr-top-20 single wr-padding-bottom-30 wr-padding-top-20">
        <h2 class="wr-top-30">￥{!! rmb($data['deposit']) !!}<span class="wr-meta">元</span></h2>
        <span style="color: #B2B2B3;font-size: 13pt">当前余额</span>
        <div class="row wr-top-20 wr-flex">
            <span class="spot wr-item-01">上</span>
            <span class="wr-item-01">次</span>
            <span class="wr-item-01">提</span>
            <span class="wr-item-01">现</span>
            <span class="wr-item-01">额</span>
            <hr class="wr-hr" />
            <span class="wr-item-03">{!! rmb($data['deposit_last']) !!}</span>
            <span class="wr-item-03">元</span>
        </div>
        <div class="row wr-top-20 wr-flex ">
            <span class="spot wr-item-01">已</span>
            <span class="wr-item-01">提</span>
            <span class="wr-item-01">现</span>
            <span class="wr-item-01">总</span>
            <span class="wr-item-01">额</span>
            <hr class="wr-hr" />
            <span class="wr-item-03">{!! rmb($data['withdrawed_deposit']) !!}</span>
            <span class="wr-item-03">元</span>
        </div>
        <div class="row wr-top-20 wr-flex">
            <span class="spot wr-item-01">不</span>
            <span class="wr-item-01">可</span>
            <span class="wr-item-01">提</span>
            <span class="wr-item-01">现</span>
            <span class="wr-item-01">金</span>
            <span class="wr-item-01">额</span>
            <hr class="wr-hr" />
            <span class="wr-item-03">{!! rmb($data['salary']) !!}</span>
            <span class="wr-item-03">元</span>
            @if($data['salary'] > 0)
            <span class="wr-item-03">
                <div class="salary text-center">?</div>
            </span>
            @endif
        </div>
    </div>
</div>
<div class="row wr-top-210" style="vertical-align: middle">
    <form>
        <input  type="hidden" name="_token" value="{!! csrf_token() !!}">
    </form>
    @if( $data['bank_card_no'] !='' )
    <div class="text-center update-bank wr-top-5 wr-bottom-5" style="color: #B2B2B3;font-size: 10pt">{!! mb_substr($data['bank_address'], 0, 10) !!}...<img style="margin-left: 6px;" src="{!! URL::asset('forestage/group/img/chevron.png?v=0.0.1') !!}" alt="" width="5px"></div>
    @endif
    <div class="wr-button-01 text-center {!! ($data['deposit'] - $data['salary']) >= $data['cash_min'] ? '' : 'wr-bg-color-afa' !!}">
        {!! ($data['deposit'] - $data['salary']) >= $data['cash_min'] ? '申请提现' : '满' . bcdiv($data['cash_min'], 100, 0) . '元可提现' !!}
    </div>
    <div class="text-center wr-top-25 history-link" style="color: #B2B2B3;font-size: 12pt">查看提现历史<img style="margin-left: 6px;" src="{!! URL::asset('forestage/group/img/chevron.png?v=0.0.1') !!}" alt="" width="5px"></div>
    {{--<div style="position: absolute;bottom: 30px;margin: 0 5%;width: 90%">--}}
        {{--<div class="text-center" style="font-size: 9pt;color: #B2B2B3;">由于微信商户限制，每人每天提现金额上限为5000元，</div>--}}
        {{--<div class="text-center" style="font-size: 9pt;color: #B2B2B3;">提现金额超过5000元将会分批转入微信钱包中。</div>--}}
    {{--</div>--}}
</div>

<div class="js-form-disabled hide" style="position: absolute; top: 0; height: 100%; width: 100%; z-index: 100; background: rgba(0, 0, 0, .3)">
    <div class="row" style="background: #fff;z-index: 999; color: #222; padding: 30px;margin: 20% 5%">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title wr-bottom-5">绑定银行卡</h3>
            </div>
            <form id="js-form">
                <div class="box-body">
                    <div class="form-group">
                        <label for="bank_account_name">银行开户名：</label>
                        <input type="text" class="form-control" minlength="2" maxlength="10" name="bank_account_name" id="bank_account_name" placeholder="输入银行开户名" value="{!! $data['bank_account_name'] !!}">
                    </div>
                    <div class="form-group">
                        <label for="bank_card_no">银行卡号：</label>
                        <input type="text" class="form-control" minlength="10" maxlength="30" name="bank_card_no" id="bank_card_no" placeholder="输入银行卡号" value="{!! $data['bank_card_no'] !!}">
                    </div>
                    <div class="form-group">
                        <label for="bank_address">开户银行：</label>
                        <input type="text" class="form-control" minlength="4" maxlength="100" name="bank_address" id="bank_address" placeholder="输入开户银行详细地址" value="{!! $data['bank_address'] !!}">
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" form="js-form"> 确 定 </button>
                    <button type="submit" class="btn btn-primary pull-right cancel"> 取 消 </button>
                </div>
            </form>
        </div>
        <script>
            $(function () {
                // 提交银行卡信息
                $('#js-form').on('submit', function (ev) {
                    ev.preventDefault();
                    var layer_div_bank = layer.msg('正在处理中，请稍候...', {
                        icon: 16,
                        shade: 0.01,
                        time:false
                    });

                    $.ajax({
                        type:"post",
                        url:"/web/pp/bank",
                        data:$('form').serialize(),
                        success:function(data){
                            layer.close(layer_div_bank);
                            alert(data.message);
                            if (data.message === "成功") {
                                window.location.reload();
                            }
                        },
                        error:function(jqXHR){
                            layer.close(layer_div_bank);
                            console.log("Error: "+jqXHR.status);
                        }
                    });
                });

                //取消按钮
                $('.cancel').on('click', function (ev) {
                    ev.preventDefault();
                    $('.js-form-disabled').prop('className', 'js-form-disabled hide');
                });
            })
        </script>
    </div>
</div>
</body>
<script>
    $(function () {
        var url = "{!! build_qs( Request::all() ) !!}";
        $('.history-link').on('click',function () {
            window.location.href="/web/group/withdraw/history?" + url;
        })

        // 申请提现
        $(".wr-button-01").on('click', function(e){
            e.preventDefault();
            if ($(this).hasClass("wr-bg-color-afa")){
                return;
            }

            if ($('input[name="bank_card_no"]').val() === ""){
                $('.js-form-disabled').prop('className', 'js-form-disabled');
                return;
            };

            var layer_div = layer.msg('正在处理中，请稍候...', {
                icon: 16,
                shade: 0.01,
                time:false
            });

            $.ajax({
                type:"post",
                url:"/web/group/withdraw?" + url,
                data:$('form').serialize(),
                success:function(data){
                    layer.close(layer_div);
                    alert(data.message);
                    window.location.href="/web/group/withdraw?" + url;
                },
                error:function(jqXHR){
                    layer.close(layer_div);
                    console.log("Error: "+jqXHR.status);
                }
            });
        });

        // 不可提现说明
        $(".salary").on('click', function(e){
            layer.open({
                type: 1,
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 0, //不显示关闭按钮
                anim: 2,
                shadeClose: true, //开启遮罩关闭
                content: '由于公司入职成员被禁用，故暂时冻结相关资金！',
            });
        });

        // 修改银行卡
        $(".update-bank").on('click', function(e){
            $('.js-form-disabled').prop('className', 'js-form-disabled');
        });
    })
</script>
</html>