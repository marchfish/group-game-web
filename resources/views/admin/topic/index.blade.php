@extends('admin/layout')
<link rel="stylesheet" href="{!! URL::asset('wechat/css/amazeui.min.css') !!}">

@section('content')

<div class="content" style="overflow-y:scroll;height: 100%;overflow-x: hidden;">
    <div class="row">
        <div class="col-xs-12">
            <div class="am-cf am-padding am-padding-bottom-0">
                <div class="am-fl am-cf"><strong class="am-text-primary am-text-lg">@if(isset($isreport))举报管理@else 微圈管理  @endif</strong> </div>
            </div>
            <hr>
            <div class="am-g">
                <form class="am-form-inline" role="form" action="?" method="get">
                    <div class="am-form-group am-input-group-sm am-u-sm-4">
                        <input type="text" style="width: 120px;" class="am-form-field" name="search_uid" value="{{isset($search_uid)?$search_uid:''}}" placeholder="请输入UID">
                    </div>
                    <div class="am-form-group am-input-group-sm am-u-sm-4">
                        <input type="text" style="width: 120px;" class="am-form-field" name="search_text" value="{{isset($search_text)?$search_text:''}}" placeholder="请输入关键字">
                    </div>
                    <div class="am-form-group am-input-group-sm am-u-sm-4">
                        <button class="am-btn am-btn-default am-btn-sm" type="submit">搜索</button>
                    </div>
                </form>
            </div>
            <div class="am-g am-margin-top">
            <div class="am-u-sm-12 am-scrollable-horizontal">
                <div class="am-comments-list am-comments-list-flip" id="pagelist">
                    @foreach( $paginate->items() as $v)
                    <article class="am-comment">
                        <a href="#">
                            <img src="{{$v->avatar}}" alt="" class="am-comment-avatar" width="48" height="48"/>
                        </a>
                        <div class="am-comment-main">
                            <header class="am-comment-hd">
                                <div class="am-comment-meta">
                                    <a href="#" class="am-comment-author">{{$v->nickname}}(UID:{{$v->user_id}})</a> 发表于 <time>{{$v->showdate}}</time>
                                </div>
                                @if(isset($isreport))
                                    <div class="am-comment-meta" >
                                        <strong style="color: red">被举报 </strong>
                                        :{{$v->content}}
                                    </div>
                                @endif
                            </header>
                            <div class="am-comment-bd">
                                {{$v->text}}<br/>
                                @if(isset($v->ext['image']))
                                @foreach($v->ext['image'] as $kk=>$vv)
                                        @if(isset($vv['thumb']))
                                                 <img src="{{$vv['thumb']}}" style="width:50px"/>
                                        @endif
                                @endforeach
                                @endif
                            </div>
                            <div class="am-comment-footer">
                                <div class="am-comment-actions">
                                    <a href="#"><i class="am-icon-heart"></i>({{$v->agree_count}})</a>
                                    <a href="#"><i class="am-icon-comment"></i>({{$v->comment_count}})</a>
                                    <a href="/wechar/topic?id={{$v->id}}"><i class="am-icon-reply"></i></a>
                                </div>
                            </div>
                            <div class="am-comment-footer">
                                <a class="am-btn am-btn-danger am-btn-xs" onclick="delTopic({{$v->id}})" href="javascript:;">删除</a>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>
            <div class="box-footer">
                <div class="row">
                    <div class="col-xs-6">
                        <p>从第 <b>{!! $paginate->firstItem() !!}</b> 条到第 <b>{!! $paginate->lastItem() !!}</b> 条，共 <b>{!! $paginate->total() !!}</b> 条</p>
                    </div>
                    <div class="col-xs-6">
                        {!! $paginate->appends(Request::all())->links('admin/pagination') !!}
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>


<script type="text/javascript">

    function delTopic(id){

            $.csrf({
                type:'POST',
                url: $.buildURL('/admin/topic/delete'),
                data: {
                    tid:id,
                    @if(isset($isreport))
                    isreport:1,
                    @endif
                },
            }, function (res) {
                $.alertSuccess(res.message, function () {
                    window.location.reload();
                });
            });
    }
</script>
@endsection