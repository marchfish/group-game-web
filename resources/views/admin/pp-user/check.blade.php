@extends('admin/layout')

@section('content')
  <style>
    .form-group a {
        width: 100%;
    }
    .form-group div img {
        max-width: 180px;
    }
  </style>
<section class="content-header">
  <h1>查看跑跑资料</h1>
</section>
<section class="content">
  <div class="row">
    <div class="col-lg-offset-3 col-lg-6">
      <div class="box box-primary">
        <div class="box-header with-border">
          <a class="btn btn-default" href="{!! URL::to('admin/pp-user').'?'. build_qs(json_decode(Request::input('_ref'), true)) !!}">
            <i class="fa fa-reply"></i>
            返回
          </a>
        </div>
        <div class="box-body">
          <div id="js-form" action="{!! URL::to('admin/pp-user') !!}" >
            <input type="hidden" name="user_id" value="{!! $row->user_id !!}">
            <div class="form-group">
              <label for="title">姓名：</label>
              <input type="text" class="form-control" name="realname" maxlength="30" value="{!! $row->realname !!}">
            </div>
            <div class="form-group">
              <label for="subtitle">手机号：</label>
              <input type="text" class="form-control" name="tel" maxlength="12" value="{!! $row->tel !!}">
            </div>
            <div class="form-group">
              <label for="url">身份证号：</label>
              <input class="form-control" name="idcard" type="text" value="{!! $row->idcard !!}">
            </div>
            <div class="form-group">
              <label for="litpic">紧急联系人姓名：</label>
              <input class="form-control" name="contact_man" type="text" value="{!! $row->contact_man !!}">
            </div>
            <div class="form-group">
              <label for="litpic">紧急联系人手机号：</label>
              <input class="form-control" name="contact_tel" type="text" value="{!! $row->contact_tel !!}">
            </div>
            <div class="form-group">
              <label>跑跑手持身份证照：</label>
                <div>
                    <a href="{!! $row->pp_pic1 !!}" target="_blank">
                        <img src="{!! $row->pp_pic1 !!}" alt="">
                    </a>
                </div>
            </div>
            <div class="form-group">
              <label>跑跑身份证正面：</label>
                <div>
                    <a href="{!! $row->pp_pic2 !!}" target="_blank">
                        <img src="{!! $row->pp_pic2 !!}" alt="">
                    </a>
                </div>
            </div>
            <div class="form-group">
              <label>跑跑身份证背面：</label>
                <div>
                    <a href="{!! $row->pp_pic3 !!}" target="_blank">
                        <img src="{!! $row->pp_pic3 !!}" alt="">
                    </a>
                </div>
            </div>
            <div class="form-group">
              <label>跑跑正面大头照：</label>
                <div>
                    <a href="{!! $row->pp_pic4 !!}" target="_blank">
                        <img src="{!! $row->pp_pic4 !!}" alt="">
                    </a>
                </div>
            </div>
            @if($row->pp_pic5 != '')
            <div class="form-group">
              <label>跑跑驾驶证正面：</label>
                <div>
                    <a href="{!! $row->pp_pic5 !!}" target="_blank">
                        <img src="{!! $row->pp_pic5 !!}" alt="">
                    </a>
                </div>
            </div>
            @endif
            </div>
          </form>
          @if($row->status == 150)
          <div class="form-group">
            <button type="submit" data-user-id="{!! $row->user_id !!}" class="btn btn-primary pull-right js-a-pp-user-verify">驳回</button>
            <button type="submit" data-user-id="{!! $row->user_id !!}" class="btn btn-primary pull-right js-a-pp-user-verify" style="margin-right: 20px">通过</button>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
<script>
$(function () {
    // 通过/驳回
    $('.js-a-pp-user-verify').on('click', function (ev) {
        ev.preventDefault();
        if ($(this).html() === "通过" )
        {
            var method = 'post';
        }else {
            var method = 'put';
        }
        $.csrf({
            method: method,
            url: $.buildURL('admin/pp-user'),
            data: {
                user_id:$(this).data('userId'),
            },
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
