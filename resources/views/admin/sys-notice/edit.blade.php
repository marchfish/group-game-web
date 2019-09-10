@extends('admin/layout')

@section('content')
<section class="content-header">
  <h1>编辑通知</h1>
</section>
<section class="content">
  <div class="row">
    <div class="col-lg-offset-3 col-lg-6">
      <div class="box box-primary">
        <div class="box-header with-border">
          <a class="btn btn-default" href="{!! URL::to('admin/sys-notice') !!}">
            <i class="fa fa-reply"></i>
            返回
          </a>
        </div>
        <div class="box-body">
          <form id="js-form" action="{!! URL::to('admin/sys-notice') !!}" >
            <input type="hidden" name="sys_notice_id" value="{!! $row->id !!}">
            <div class="form-group">
              <label for="title">标题*：</label>
              <input type="text" class="form-control" name="title" maxlength="30" placeholder="输入标题" value="{!! $row->title !!}">
            </div>
            <div class="form-group">
              <label for="subtitle">内容*：</label>
              <input type="text" class="form-control" name="content" maxlength="90" placeholder="输入内容" value="{!! $row->content !!}">
            </div>
            <div class="form-group">
              <label for="url">开始通知时间*：</label>
              <input class="form-control" id="start-at" name="start_at" type="text" value="{!! $row->start_at !!}">
            </div>
            <div class="form-group">
              <label for="litpic">结束通知时间*：</label>
              <input class="form-control" id="end-at" name="end_at" type="text" value="{!! $row->end_at !!}">
            </div>
            <div class="form-group">
              <label for="litpic">选择通知时段：</label>
              <br>
              @for($i = 0; $i < 24; $i++)
                <label class="checkbox-inline" style="margin-left: 0px; margin-right: 5px;">
                  <input type="checkbox" {!! strpos($row->hour, sprintf("%02d",$i)) !== false ? 'checked="checked"' : '' !!}  name="hour[]" value="{!! sprintf("%02d",$i) !!}">{!! sprintf("%02d",$i) !!}
                </label>
              @endfor
            </div>
            <div class="form-group">
              <label for="client">选择接收通知的APP端：</label>
              <br>
              <label class="radio-inline">
                <input type="radio" name="client" {!! $row->client == '' ? 'checked="checked"' : '' !!} value="">不限
              </label>
              <label class="radio-inline">
                <input type="radio" name="client" {!! $row->client == 'zpp' ? 'checked="checked"' : '' !!} value="zpp">《找跑跑版》
              </label>
              <label class="radio-inline">
                <input type="radio" name="client" {!! $row->client == 'pp' ? 'checked="checked"' : '' !!} value="pp">《跑跑骑手版》
              </label>
            </div>
            <div class="form-group">
              <label for="area">选择接收通的城市：</label>
              <select class="form-control" name="area">
                <option value="">全部城市</option>
                @foreach($row->citys as $k=>$v)
                <optgroup label="{!! $k !!}">
                  @foreach($v as $kk=>$vv)
                  <option value="{!! $kk !!}" {!! $kk == $row->area ? 'selected="selected"' : '' !!}>{!! $vv !!}</option>
                  @endforeach
                </optgroup>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label for="isUrgent">是否立即推送消息：</label>
              <br>
              <label class="radio-inline">
                <input type="radio" name="isUrgent" value="1">是
              </label>
              <label class="radio-inline">
                <input type="radio" name="isUrgent" checked="checked" value="0">否
              </label>
            </div>
          </form>
          <div class="form-group">
            <button type="submit" class="btn btn-primary pull-right" form="js-form"> 保存</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
$(function () {
  // 保存
  $('#js-form').on('submit', function (ev) {
    ev.preventDefault();

    $.csrf({
      method: 'put',
      url: this.action,
      data: $(this).serialize(),
    }, function (res) {
      $.alertSuccess(res.message, function () {
        window.location.reload();
      });
    });
  });
});

// 开始时间
$(function () {
    $('#start-at').datetimepicker({
        locale: 'zh-cn',
        format: 'YYYY-MM-DD HH:mm:ss',
    });
});

// 结束时间
$(function () {
    $('#end-at').datetimepicker({
        locale: 'zh-cn',
        format: 'YYYY-MM-DD HH:mm:ss',
    });
});

</script>
@endsection
