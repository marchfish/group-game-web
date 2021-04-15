<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
  <meta name="token" content="{!! csrf_token() !!}">
  <meta name="message" content="{!! $message !!}">
  <title>开荒之路</title>
  <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/bootstrap-3.4.1/css/bootstrap.min.css') !!}">
  <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/wr-1.0.0/css/wr-css.css') !!}">
  <link rel="stylesheet" type="text/css" href="{!! URL::asset('forestage/public/layer-3.1.1/layer.css') !!}">
  <script src="{{ URL::asset('forestage/public/vue-bundle-2.6.10/vue-bundle.js') }}"></script>
  <script type="text/javascript" src="{!! URL::asset('forestage/public/jquery-3.4.1/jquery.min.js') !!}"></script>
  <script type="text/javascript" src="{!! URL::asset('forestage/public/bootstrap-3.4.1/js/bootstrap.min.js') !!}"></script>
  <script type="text/javascript" src="{!! URL::asset('forestage/public/layer-3.1.1/layer.js') !!}"></script>
  <script type="text/javascript" src="{!! URL::asset('forestage/public/axios-0.19.0/axios.min.js') !!}"></script>
</head>
<style>
  html,body {
    height: 100%;
  }
  .xianshiquyu {
    width: 100%;
    max-width: 600px;
    height: 50%;
    border-width: 1px;
    border-color: pink;
    border-style: solid;
    overflow-y: auto;
    padding: 10px 10px;
    margin-bottom: 15px;
  }
  input {
    margin-bottom: 5px;
  }
  .row {
    padding: 0;
    margin: 0;
  }
  .skill-select {
    height: 26px;
    width: 80px;
  }
  .main {
    height: 100%;
    width: 100%;
    position: relative;
  }
  .message-popup {
    width: 100%;
    position: absolute;
    top: 0;
    z-index: 80;
    overflow: hidden;
  }
  .message-area {
    width: 100%;
    max-width: 100% !important;
    background: #000000;
    opacity: 0.7;
    height: 150px;
    color: #ffffff;
    z-index: 81;
    padding: 15px 15px 0;
    resize: vertical;
  }
  .message-full {
    height: 100% !important;
  }
  .message-popup.message-full .message-area {
    height: 100% !important;
  }
  .message-button {
    width: 50px;
    height: 50px;
    position: absolute;
    top: 0;
    right: 0;
    z-index: 83;
    padding-top: 5px;
  }
  .message-button span{
    position: absolute;
    top: 5px;
    right: 15px;
    font-size: 14px;
    color: #ffffff;
    padding: 1px 5px;
    border-radius: 100px;
    background: red;
  }
  .message-button img {
    width: 36px;
  }
  .full-button {
    position: relative;
    z-index: 82;
  }
  .full-button span {
    text-align: center;
    line-height: 35px;
    height: 35px;
    width: 50px;
    position: absolute;
    top: -55px;
    right: 20px;
    color: #ffffff;
    border: 1px solid #ffffff;
    border-radius: 5px;
  }
  .write-message {
    margin-top: -5px;
  }
  .write-message input {
    width: 80%;
  }
</style>
<body>
<div  style="height: 100%; margin:0 auto; max-width:600px;">
  <div id="app-main" class="row main">
    <div class="xianshiquyu">
      欢迎来到开荒之路~
    </div>
    <div class="row">
      <p>　　<input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="上" />　　<input type="button" class="action" data-url="{!! URL::to('game/attack') !!}" value="攻击">　<input type="button" class="action" data-url="{!! URL::to('user-knapsack') !!}" value="背包">　<input type="button" class="action" data-url="{!! URL::to('mission/user') !!}" value="任务">　<input type="button" class="action" data-url="{!! URL::to('equip') !!}" value="装备"></p>
    </div>
    <div class="row">
      <p> <input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="左" /> 　　<input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="右" />
        <input type="button" class="action" data-url="{!! URL::to('game/location') !!}" value="位置">
        <select id="drugs" class="skill-select" name="item_id">
          @if(count($drugs) > 0)
            @foreach($drugs as $drug)
              <option value="{!! $drug->id !!}">{!! $drug->name !!}</option>
            @endforeach
          @else
            <option value="3">小血瓶</option>
          @endif
        </select>
        <input type="button" class="action" data-url="{!! URL::to('item/use-drugs') !!}" value="使用药品"/>
      </p>
    </div>
    <div class="row">
      <p><input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="前" /> <input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="下" /> <input type="button" class="action" data-url="{!! URL::to('game/move') !!}" value="后" />
        @if(count($rows) > 0)
          <select id="skill" class="skill-select" name="skill_id">
            @foreach($rows as $row)
              <option value="{!! $row->id !!}">{!! $row->name !!}</option>
            @endforeach
          </select>
          <input type="button" class="action" data-url="{!! URL::to('skill/use') !!}" value="技能"/>
        @endif
        <input type="button" class="action" data-url="{!! URL::to('user/role') !!}" value="状态">
        <input type="button" class="action" data-url="{!! URL::to('item/recycle-show') !!}" value="回收" />
        <input type="button" class="action" data-url="{!! URL::to('ranking') !!}" value="排行榜" />
        <input type="button" class="action" data-url="{!! URL::to('shop-business/sell-show') !!}" value="出售物品" />
        <input type="button" class="action" data-url="{!! URL::to('shop-business') !!}" value="拍卖行" />
        <input type="button" class="action" data-url="{!! URL::to('rank') !!}" value="排位" />
        <input type="button" class="action" data-url="{!! URL::to('revive') !!}" value="复活"/>
        <input type="button" class="action" data-url="{!! URL::to('notice') !!}" value="告示"/>
      </p>
    </div>
    <div class="row">
      会员功能：
      <p>
        <input type="button" class="action" data-url="{!! URL::to('vip-show') !!}" value="会员"/>
        <input type="button" class="action" data-url="{!! URL::to('shop-mall') !!}" value="商城"/>
        <input type="hidden" id="auto-attack" class="action" data-url="{!! URL::to('vip/auto-attack') !!}" value="攻击">
        <input type="button" class="auto-attack" value="自动攻击"/>
        <input type="button" class="action" data-url="{!! URL::to('vip/on-hook') !!}" value="挂机经验"/>　
        <input type="button" class="action" data-url="{!! URL::to('vip/on-hook') !!}" value="挂机金币"/>
        <input type="button" class="action" data-url="{!! URL::to('end-hook') !!}" value="结束挂机"/>
        <input type="button" class="action" data-url="{!! URL::to('warehouse') !!}" value="仓库"/>
        <input type="button" class="action" data-url="{!! URL::to('warehouse/user-knapsack-show') !!}" value="存入仓库"/>
      </p>
    </div>
    <div class="row">
      其它：
      <p>
        <input type="button" class="action" data-url="{!! URL::to('lottery') !!}" value="搏一搏"/>
        <input type="button" class="action" data-url="{!! URL::to('map/activity') !!}" value="活动地图"/>
        <input type="button" class="action" data-url="{!! URL::to('tower') !!}" value="镇妖塔"/>
      </p>
    </div>
    <div class="row">
      <a class="btn btn-default" href="{!! URL::to('logout') !!}">退出</a>
    </div>

    {{--  信息窗口--}}
    <div class="message-button">
      <span v-if="count == 0 ? false : true">{% count %}</span>
      <img src="{!! URL::asset('forestage/img/icon_message.png') !!}" alt="">
    </div>
    <div class="message-popup hide">
      <textarea id="message-area" readonly="readonly" class="message-area" v-text="messages"></textarea>
      <div class="full-button js-full-button"><span>全屏</span></div>
      <div class="write-message">
        <input v-model="content" @keyup.enter="sendMessage">
        <button @click.prevent="sendMessage">发送</button>
      </div>
    </div>

  </div>
</div>
</body>
<script type="text/javascript" src="{!! URL::asset('forestage/public/game/main.js') !!}"></script>
<script>
</script>
</html>
