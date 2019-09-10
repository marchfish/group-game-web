<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <title>公司总结</title>
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css');; ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/public/wr-1.0.0/css/wr-css.css');; ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo URL::asset('forestage/stat/css/stat.css'); ?>">
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/jquery-2.2.4/jquery.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/echarts-2.0.0/echarts.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo URL::asset('forestage/public/blueimp-Templates-3.11.0/tmpl.js'); ?>"></script>
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
        color: #333333;
        overflow-x: hidden;
        background: #FEFEFE;
    }
    .datetimepicker-days {
        color: #333;
    }
    .wr-bg-01 {
        background: #515884;
        margin: 0 5%;
        position: relative;
        padding-top: 40px;
    }
    .caret {
        border-top: 10px dashed #333333;
        border-top: 10px solid\9 #333333;
        border-right: 6px solid transparent;
        border-left: 6px solid transparent;
    }
    .wr-square {
        height: 13px;
        width: 5px;
        background: #E9868B;
        display: block;
        float: left;
        margin-right: 10px;
        margin-top: 3px;
    }

    .wr-pie-h3 {
        font-size: 11pt;
        font-weight: bold;
    }
    .wr-pie-span {
        color: #B2B2B3;
        font-size: 11pt;
    }
    .kailong{
        width:0;
        height:0;
        border-top:5px solid transparent;
        border-bottom:5px solid transparent;
        border-left:8px solid #333333;
    }
    .wr-line-03 {
        width: 100%;
        border-right: 1px solid #F1F1F1;
    }
    /* 收入趋势 */
    .wr-income-01 {
        width: 50%;
        height: 110px;
        background: #F7F8F9;
        margin-right: 3px;
        position: relative;
    }
    .wr-income-01 h3 {
        color: #E9868B;
        font-size: 23pt;
    }
    .wr-income-01 span {
        color: #353537;
        font-size: 12pt;
        font-weight: bold;
    }
    .wr-income-02 {
        width: 50%;
        height: 110px;
        background: #F7F8F9;
        margin-left: 3px;
        position: relative;
    }
    .wr-income-02 h3 {
        color: #515884;
        font-size: 23pt;
    }
    .wr-income-02 span {
        color: #353537;
        font-size: 12pt;
        font-weight: bold;
    }
    .wr-income-echarts {
        height: 230px;
        background: #F7F8F9;
        margin-top: 6px;
    }

    /* 跑腿数据 */
    .wr-run-01 h3{
        color: #515884;
        font-size: 22pt;
    }
    .wr-run-01 span {
        color: #353537;
        font-size: 9pt;
        font-weight: bold;
    }

    /* 即时数据 */
    .wr-immediate-01 h3{
        color: #515884;
        font-size: 11pt;
        font-weight: bold;
    }
    .wr-immediate-01 span {
        color: #353537;
        font-size: 10pt;
    }

    /* 业务数据 */
    .wr-business-01 {
        position:relative;
        height: 100px;
        padding: 10px;
    }

    /* 公共部分 */
    .wr-border-radius {
        border-radius: 8px;
    }
    .wr-stat-header {
        height: 110px;
        background: #F7F8F9;
        position: relative;
    }
    .wr-stat-echarts {
        height: 230px;
        background: #F7F8F9;
        margin-top: 6px;
    }
    .wr-color-515{
        color: #515884;
    }
    .wr-color-E98{
        color: #E9868B;
    }
</style>
<body>
<div class="row wr-content-header">
    <div class="wr-top-25 clearfix wr-padding-05">
        <div id="date-picker-stat-company" class="text-left">
            <h3 class="wr-h3 <?php echo $date_month != '' ? 'wr-color-c3c' : ''; ?>"><?php echo $data['date_show']; ?></h3>
            <span class="<?php echo $date_month != '' ? 'caret-c3c' : 'caret'; ?>"></span>
        </div>
        <div style="float: right;display: flex;align-items:center;">
            <span class="wr-date-month <?php echo $date_month == -2 ? 'wr-active-02' : ''; ?>" data-month="-2">今天</span>
            <span class="wr-line-02" style="margin: 0 8px"></span>
            <span class="wr-date-month <?php echo $date_month == -1 ? 'wr-active-02' : ''; ?>" data-month="-1">昨天</span>
            <span class="wr-line-02" style="margin: 0 8px"></span>
            <span class="wr-date-month <?php echo $date_month == 0 && $date_month != '' ? 'wr-active-02' : ''; ?>" data-month="0">本月</span>
            <span class="wr-line-02" style="margin: 0 8px"></span>
            <span class="wr-date-month <?php echo $date_month == 1 ? 'wr-active-02' : ''; ?>" data-month="1">上月</span>
            
            
        </div>
    </div>
    <div class="wr-line wr-top-15 wr-bottom-20"></div>
</div>
<div class="row wr-content-header wr-padding-05">
    <div class="clearfix">
        <h3 class="wr-h3"><span class="wr-square"></span>收入趋势</h3>
    </div>
    <div id="js-div-income" class="row wr-top-20" style="display: flex">
        <div class="wr-income-01 wr-border-radius">
            <div class="text-center" style="margin-top: 26px">
                <h3>- -</h3>
                <span>收入</span>
            </div>
        </div>
        <div class="wr-income-02 wr-border-radius">
            <div class="text-center" style="margin-top: 26px">
                <h3>- -</h3>
                <span>单数</span>
            </div>
        </div>
    </div>
    <noscript id="js-tmpl-income">
        <div class="wr-income-01 wr-border-radius">
            <div class="wr-center text-center">
                <h3>{%= (o.pay_fee / 100).toFixed(2) %}</h3>
                <span>收入</span>
            </div>
        </div>
        <div id="js-div-stat-total" class="wr-income-02 wr-border-radius">
            <div class="wr-center text-center">
                <h3>{%= o.count %}</h3>
                <span>单数</span>
            </div>
        </div>
    </noscript>
    <div id="income-echarts" class="row wr-income-echarts wr-border-radius"></div>
    <script>
        $(function () {
            var echart = echarts.init(document.getElementById('income-echarts'));

            $.ajax({
                type:"get",
                url:"/web/group/stat/income-trend?<?php echo build_qs( Request::except(['date_month','date_from','date_to']) ); ?>",
                data:{
                    date_from: '<?php echo $data['date_from']; ?>',
                    date_to: '<?php echo $data['date_to']; ?>',
                    date_month: '<?php echo $date_month; ?>',
                },
                success:function(res){
                    if (res.code == 200){
                        echart.setOption(res.data);
                        $('#js-div-income').html(tmpl($('#js-tmpl-income').text(), {
                            pay_fee: res.stats.pay_fee,
                            count: res.stats.count,
                        }));
                    }
                },
                error:function(jqXHR){
                    console.log("Error: "+jqXHR.status);
                }
            });
        })
    </script>
    <div class="wr-bottom-30"></div>
</div>

<div class="wr-line"></div>

<div class="row wr-top-20 wr-content-header wr-padding-05">
    <div class="clearfix wr-bottom-20">
        <h3 class="wr-h3"><span class="wr-square"></span>业务数据</h3>
    </div>
    <div id="js-div-business" class="wr-stat-echarts wr-border-radius row" style="height: 155px">
        <div class="wr-top-20 clearfix">
            <div class="col-xs-4 text-center wr-business-01">
                <div id="business-echarts-01" style="width: 100%;height:100%"></div>
                <span class="wr-font-10pt">公司单占比</span>
                <div class="wr-mask">
                    <span id="business-stat-01" class="wr-center wr-color-E98" style="font-size: 8pt">- -</span>
                </div>
            </div>
            <div class="col-xs-4 text-center wr-business-01">
                <div id="business-echarts-02" style="width: 100%;height:100%"></div>
                <span class="wr-font-10pt">散单占比</span>
                <div class="wr-mask">
                    <span id="business-stat-02" class="wr-center wr-color-E98" style="font-size: 8pt">- -</span>
                </div>
            </div>
            <div class="col-xs-4 text-center wr-business-01">
                <div id="business-echarts-03" style="width: 100%;height:100%"></div>
                <span class="wr-font-10pt">超时率</span>
                <div class="wr-mask">
                    <span id="business-stat-03" class="wr-center wr-color-E98" style="font-size: 8pt">- -</span>
                </div>
            </div>
        </div>
    </div>
    <div id="js-div-king">
        <div class="wr-stat-header wr-top-10 wr-border-radius text-center row">
            <div class="col-xs-4" style="height: 100%">
                <div class="wr-center wr-immediate-01 wr-line-03">
                    <h3 style="color: #E9868B">跑单王</h3>
                    <div class="wr-top-5">
                        - -
                    </div>
                </div>
            </div>
            <div class="col-xs-4" style="height: 100%">
                <div class="wr-center wr-immediate-01 wr-line-03">
                    <h3 style="color: #E9868B">跑单王单量</h3>
                    <div class="wr-top-5">
                        - -
                    </div>
                </div>
            </div>
            <div class="col-xs-4" style="height: 100%">
                <div class="wr-center wr-immediate-01" style="width: 100%">
                    <h3 style="color: #E9868B">跑单王跑费</h3>
                    <div class="wr-top-5">
                        - -
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<noscript id="js-tmpl-king">
    <div class="wr-stat-header wr-top-10 wr-border-radius text-center row">
        <div class="col-xs-4" style="height: 100%">
            <div class="wr-center wr-immediate-01 wr-line-03">
                <h3 style="color: #E9868B">跑单王</h3>
                <div class="wr-top-5">
                    <span>{%= o.stats.stat4.realname %}</span>
                </div>
            </div>
        </div>
        <div class="col-xs-4" style="height: 100%">
            <div class="wr-center wr-immediate-01 wr-line-03">
                <h3 style="color: #E9868B">跑单王单量</h3>
                <div class="wr-top-5">
                    <span>{%= o.stats.stat4.count %}</span>
                </div>
            </div>
        </div>
        <div class="col-xs-4" style="height: 100%">
            <div class="wr-center wr-immediate-01" style="width: 100%">
                <h3 style="color: #E9868B">跑单王跑费</h3>
                <div class="wr-top-5">
                    <span>{%= o.stats.stat4.pay_fee != '- -' ? (o.stats.stat4.pay_fee/ 100).toFixed(2) : o.stats.stat4.pay_fee %}</span>
                </div>
            </div>
        </div>
    </div>
</noscript>
<script>
    $(function () {
        var echart1 = echarts.init(document.getElementById('business-echarts-01'));
        var echart2 = echarts.init(document.getElementById('business-echarts-02'));
        var echart3 = echarts.init(document.getElementById('business-echarts-03'));

        $.ajax({
            type:"get",
            url:"/web/group/stat/order?<?php echo build_qs( Request::except(['date_month','date_from','date_to']) ); ?>",
            data:{
                date_from: '<?php echo $data['date_from']; ?>',
                date_to: '<?php echo $data['date_to']; ?>',
                date_month: '<?php echo $date_month; ?>',
            },
            success:function(res){
                if (res.code == 200){
                    echart1.setOption(res.data.option1);
                    echart2.setOption(res.data.option2);
                    echart3.setOption(res.data.option3);

                    $('#business-stat-01').html(res.stats.stat1 + "%" + "</br>" + res.stats.count1 + "单");
                    $('#business-stat-02').html(res.stats.stat2 + "%" + "</br>" + res.stats.count2 + "单");
                    $('#business-stat-03').html(res.stats.stat3 + "%");

                    $('#js-div-king').html(tmpl($('#js-tmpl-king').text(), {
                        stats: res.stats,
                    }));
                }
                else if (res.code == 400){
                    var option = {
                        series: [
                            {
                                name:'访问来源',
                                type:'pie',
                                radius: ['73%', '100%'],
                                avoidLabelOverlap: false,
                                legend: {
                                    selectedMode: false,
                                },
                                label: {
                                    normal: {
                                        show: false,
                                        position: 'center'
                                    },
                                },
                                color:['#E9868B', '#F1F1F2'],
                                labelLine: {
                                    normal: {
                                        show: false
                                    }
                                },
                                data:[
                                    {value:0, name:'请先加入跑团'},
                                    {value:0, name:'无法查看'},
                                ]
                            }
                        ]
                    };
                    echart1.setOption(option);
                    echart2.setOption(option);
                    echart3.setOption(option);
                }
            },
            error:function(jqXHR){
                console.log("Error: "+jqXHR.status);
            }
        });
    })
</script>

<div class="wr-line wr-top-30"></div>

<div id="js-div-square" class="row wr-top-20 wr-content-header wr-padding-05 wr-bottom-30">
    <div class="clearfix">
        <h3 class="wr-h3"><span class="wr-square"></span>即时数据</h3>
    </div>
    <div class="wr-stat-header wr-top-10 wr-border-radius text-center row">
        <div class="col-xs-4" style="height: 100%">
            <div class="wr-center wr-immediate-01 wr-line-03">
                <h3>当前配送数</h3>
                <div class="wr-top-5">
                    - -
                </div>
            </div>
        </div>
        <div class="col-xs-4" style="height: 100%">
            <div class="wr-center wr-immediate-01 wr-line-03">
                <h3>待验收订单数</h3>
                <div class="wr-top-5">
                    - -
                </div>
            </div>
        </div>
        <div class="col-xs-4" style="height: 100%">
            <div class="wr-center wr-immediate-01" style="width: 100%">
                <h3>上班中人数</h3>
                <div class="wr-top-5">
                    - -
                </div>
            </div>
        </div>
    </div>
</div>

<div class="wr-line"></div>
<noscript id="js-tmpl-square">
    <div class="clearfix">
        <h3 class="wr-h3"><span class="wr-square"></span>即时数据</h3>
    </div>
    <div class="wr-stat-header wr-top-10 wr-border-radius text-center row">
        <div class="col-xs-4" style="height: 100%">
            <div class="wr-center wr-immediate-01 wr-line-03">
                <h3>当前配送数</h3>
                <div class="wr-top-5">
                    <span>{%= o.stats.delivery %}</span>
                </div>
            </div>
        </div>
        <div class="col-xs-4" style="height: 100%">
            <div class="wr-center wr-immediate-01 wr-line-03">
                <h3>待验收订单数</h3>
                <div class="wr-top-5">
                    <span>{%= o.stats.check %}</span>
                </div>
            </div>
        </div>
        <div class="col-xs-4" style="height: 100%">
            <div class="wr-center wr-immediate-01" style="width: 100%">
                <h3>上班中人数</h3>
                <div class="wr-top-5">
                    <span>{%= o.stats.work %}</span>
                </div>
            </div>
        </div>
    </div>
</noscript>

<script>
    $(function () {
        $.ajax({
            type:"get",
            url:"/web/group/stat/order1?<?php echo build_qs( Request::except(['date_month','date_from','date_to']) ); ?>",
            data:{
                date_from: '<?php echo $data['date_from']; ?>',
                date_to: '<?php echo $data['date_to']; ?>',
                date_month: '<?php echo $date_month; ?>',
            },
            success:function(res){
                if (res.code == 200){
                    $('#js-div-square').html(tmpl($('#js-tmpl-square').text(), {
                        stats: res.data,
                    }));
                }
            },
            error:function(jqXHR){
                console.log("Error: "+jqXHR.status);
            }
        });
    })
</script>

<div class="row wr-top-20 wr-content-header wr-padding-05 wr-bottom-50">
    <div class="clearfix wr-bottom-20">
        <h3 class="wr-h3"><span class="wr-square"></span>成员列表</h3>
    </div>
    <div class="box-body table-responsive no-padding">
        <table id="js-div-table" class="table table-striped table-bordered table-hover">
            <thead>
            <tr>
                <th>姓名</th>
                <th>竞价率</th>
                <th>中单率</th>
                <th>背单</th>
                <th>总跑费</th>
                <th>最新位置</th>
                <th>定位时间</th>
                <th>地图</th>
                <th>上次工作时长</th>
                <th>上次听单时间</th>
                <th>上次获单时间</th>
                <th>上次竞价时间</th>
                <th>上次验收时间</th>
                <th>当前工作时长</th>
                <th>当前里程数</th>
                <th>当前总订单数</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<noscript id="js-tmpl-table">
    <thead>
    <tr>
        <th>姓名</th>
        <th>竞价率</th>
        <th>中单率</th>
        <th>背单</th>
        <th>总跑费</th>
        <th>最新位置</th>
        <th>定位时间</th>
        <th>地图</th>
        <th>上次工作时长</th>
        <th>上次听单时间</th>
        <th>上次获单时间</th>
        <th>上次竞价时间</th>
        <th>上次验收时间</th>
        <th>当前工作时长</th>
        <th>当前里程数</th>
        <th>当前总订单数</th>
    </tr>
    </thead>
    <tbody>
    {% for (var i = 0; i < o.stats.length; ++i) { %}
        <tr>
            <td>{%= o.stats[i].realname %}</td>
            <td>{%= o.stats[i].participate %}</td>
            <td>{%= o.stats[i].win %}</td>
            <td>{%= o.stats[i].back %}</td>
            <td>{%= o.stats[i].total_pay_fee %}</td>
            <td>{%= o.stats[i].address %}</td>
            <td>{%= o.stats[i].position_time %}</td>
            <td>
                <button class="map" lon="{%= o.stats[i].lon %}" lat="{%= o.stats[i].lat %}" address="{%= o.stats[i].address %}" time="{%= o.stats[i].position_time %}">查看</button>
            </td>
            <td>{%= o.stats[i].duration %}</td>
            <td>{%= o.stats[i].worktime %}</td>
            <td>{%= o.stats[i].order_ts %}</td>
            <td>{%= o.stats[i].check_ts %}</td>
            <td>{%= o.stats[i].actor_ts %}</td>
            <td>{%= o.stats[i].total_duration %}</td>
            <td>{%= o.stats[i].km %}</td>
            <td>{%= o.stats[i].order_count %}</td>
        </tr>
    {% } %}
    </tbody>
</noscript>
<script>
    $(function () {
        $.ajax({
            type:"get",
            url:"/web/group/stat/order2?<?php echo build_qs( Request::except(['date_month','date_from','date_to']) ); ?>",
            async:false, //异步
            data:{
                date_from: '<?php echo $data['date_from']; ?>',
                date_to: '<?php echo $data['date_to']; ?>',
                date_month: '<?php echo $date_month; ?>',
            },
            success:function(res){
                if (res.code == 200 && res.stats.length != 0){
                    $('#js-div-table').html(tmpl($('#js-tmpl-table').text(), {
                        stats: res.stats,
                    }));
                }
            },
            error:function(jqXHR){
                console.log("Error: "+jqXHR.status);
            }
        });
    })
</script>
</body>
<script>
    // 选择统计
    $(function () {
        var url = "<?php echo build_qs( Request::except(['date_month']) ); ?>";
        $('.wr-date-month').on('click', function () {
            window.location.href="/web/group/stat?date_month=" + $(this).data('month') + '&' + url;
        })
    });

    //间距
    $(function () {
        if ($(document.body).outerWidth(true) > 340){
            $('.wr-line-02').css('margin', '0 10px')
        };
    });

</script>
</html><?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/web/group/stat.blade.php ENDPATH**/ ?>