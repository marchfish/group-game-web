@extends('admin/layout')

@section('content')
  <style>
    .control-label{
      line-height: 33px;
      padding: 0;
    }
  </style>
<section class="content-header">
  <h1>新增</h1>
</section>
<section class="content">
  <div class="row">
    <div class="col-lg-offset-3 col-lg-6">
      <div class="box box-primary">
        <div class="box-header with-border">
          <a class="btn btn-default" href="{{ URL::to('admin/map/index').'?'. build_qs(json_decode(Request::input('_ref'), true)) }}">
            <i class="fa fa-reply"></i>
            返回
          </a>
        </div>
        <div id="app" class="box-body">
          <form id="js-form" action="{{ URL::to('admin/sys-notice') }}" >
            <div class="form-group">
              <label for="">名称*：</label>
              <input type="text" class="form-control" name="name" maxlength="30" v-model="name" placeholder="输入名称" value="">
            </div>
            <div class="form-group">
              <label for="">介绍：</label>
              <input type="text" class="form-control" name="description" maxlength="90" v-model="description" placeholder="输入介绍" value="">
            </div>
            <div v-for="(item, index) in items">
              <div class="form-group clearfix">
                <label for="map-name" class="col-sm-1 control-label">{% item.name %}：</label>
                <div class="col-sm-5">
                  <select v-model="item.selected1" class="form-control" name="area" @change='getMap(index)'>
                    <option value="">请选择</option>
                    <option v-for="option in item.options1" :value="option.id">{% option.name %}</option>
                  </select>
                </div>
                <div class="col-sm-5">
                  <select v-model="item.selected2" class="form-control" name="map">
                    <option value="">请选择</option>
                    <option v-for="option in item.options2" :value="option.id">{% option.name %}</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="">是否活动地图：</label>
              <br>
              <label class="radio-inline">
                <input name="is_activity" type="radio" v-model="activity" value="1">是
              </label>
              <label class="radio-inline">
                <input name="is_activity" type="radio" v-model="activity" value="0">否
              </label>
            </div>
          </form>
          <div class="form-group">
            <button @click="save()" class="btn btn-primary pull-right"> 保存</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
$(function () {
  var npcType = {!! json_encode($npc_type, JSON_UNESCAPED_UNICODE) !!};
  var enemyLevel = {!! json_encode($enemy_level, JSON_UNESCAPED_UNICODE) !!};
  var mapArea = {!! json_encode($map_area, JSON_UNESCAPED_UNICODE) !!};

  let app = new Vue({
    el:"#app",
    data:{
      items:{
        npc: {
          name: 'npc',
          url: '/admin/map/npc',
          selected1 : '',
          options1 : npcType,
          selected2 : '',
          options2 : [],
        },
        enemy: {
          name: '怪物',
          url: '/admin/map/enemy',
          selected1 : '',
          options1 : enemyLevel,
          selected2 : '',
          options2 : [],
        },
        up: {
          name: '上',
          url: '/admin/map',
          selected1 : '',
          options1 : mapArea,
          selected2 : '',
          options2 : [],
        },
        down: {
          name: '下',
          url: '/admin/map',
          selected1 : '',
          options1 : mapArea,
          selected2 : '',
          options2 : [],
        },
        left: {
          name: '左',
          url: '/admin/map',
          selected1 : '',
          options1 : mapArea,
          selected2 : '',
          options2 : [],
        },
        right: {
          name: '右',
          url: '/admin/map',
          selected1 : '',
          options1 : mapArea,
          selected2 : '',
          options2 : [],
        },
        forward: {
          name: '前',
          url: '/admin/map',
          selected1 : '',
          options1 : mapArea,
          selected2 : '',
          options2 : [],
        },
        behind: {
          name: '后',
          url: '/admin/map',
          selected1 : '',
          options1 : mapArea,
          selected2 : '',
          options2 : [],
        },
        area: {
          name: '区域',
          url: '',
          selected1 : '',
          options1 : [],
          selected2 : '',
          options2 : mapArea,
        }
      },
      name : '',
      description : '',
      activity : 0,
    },
    methods : {
      getMap : function(index){
        if (this.items[index].url == '') {
          return ;
        }

        $.ajax({
          method: 'get',
          url: this.items[index].url,
          data: {
            search: this.items[index].selected1,
          }
        }).then(function (res) {
          app.items[index].options2 = res.data;
          app.items[index].selected2 = '';
        });

      },
      save : function () {
        $.csrf({
          method: 'post',
          url: '/admin/map',
          data: {
            name: this.name,
            npc_id: this.items['npc'].selected2,
            enemy_id: this.items['enemy'].selected2,
            description: this.description,
            up: this.items['up'].selected2,
            down: this.items['down'].selected2,
            left: this.items['left'].selected2,
            right: this.items['right'].selected2,
            forward: this.items['forward'].selected2,
            behind: this.items['behind'].selected2,
            area_id: this.items['area'].selected2,
            is_activity: this.activity,
          }
        }, function (res) {
          if (res.code == 200)  {
            $.alertSuccess(res.message, function () {
              window.location.reload();
            });
          }else {
            $.alertError(res.message, function () {
            });
          }
        });
      }
    },
    // mounted:function () {
    //   console.log('页面完成加载');
    // }
  });

});

</script>
@endsection
