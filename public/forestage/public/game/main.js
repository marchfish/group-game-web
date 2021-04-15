$(function () {
  var ws = new WebSocket("ws://218.201.212.143:19926");

  var app = new Vue({
    el: '#app-main',
    data: {
      messages: $('meta[name="message"]').attr('content').replace(/@A@/g, '\n'),
      content: "",
      client_id: "",
      count: 0,
    },
    created: function(){
      ws.onmessage = function(e) {
        var res = JSON.parse(e.data);

        var type = res.type || '';

        switch(type){
          case 'init':
            axios.post('/api/road2d/login',{
              client_id: res.client_id,
            }).then(function(res){
              app.client_id = res.data.client_id;
            });
            break;
          case 'logout':
            console.log(res);
            break;
          default :
            this.messages += res.message + '\n';
            updateScroll();
            this.count += updateCount();
            if (this.count >= 99) {
              this.count = 99;
            }
        }
      }.bind(this);
    },
    methods: {
      sendMessage: function() {
        axios.post('/api/road2d/message',{
          message: this.content,
          client_id: this.client_id
        }).then(function(res){
          app.content = '';
        })
      },
      fullScreen: function() {
        if (this.isFull) {

        }
      },
      logout: function() {
        axios.get('/api/road2d/logout',{}).then(function(res){
          console.log(res.data);
        })
      }
    }
  });

  // 移动到底部
  function updateScroll() {
    var $messageArea = $('#message-area');
    $messageArea.scrollTop($messageArea[0].scrollHeight);
  }

  function updateCount() {
    if ($('.message-popup').hasClass('hide')){
      return 1;
    }

    return 0;
  }

  updateScroll();

  $('.message-button').on('click', function (e,v) {
    e.preventDefault();
    var $messagePopup = $('.message-popup');

    if ($messagePopup.hasClass('hide')){
      $messagePopup.removeClass('hide');
      app.count = 0;
      updateScroll();
    }else {
      $messagePopup.addClass('hide');
    }
  });

  var $fullButton = $('.js-full-button');
  var $messagePopup = $('.message-popup');

  $fullButton.on('click', function (e, v) {
    e.preventDefault();
    if ($messagePopup.hasClass('message-full')){
      $messagePopup.removeClass('message-full');
    }else {
      $messagePopup.addClass('message-full');
    }
  })

})

$(function () {
  var timestamp = Date.parse(new Date());
  var token = $('meta[name="token"]').attr('content');
  var autoAtt = null;
  // 动作
  $(document).on('click', '.action', function(e){
    e.preventDefault();
    if (autoAtt && $(this).val() != "攻击") {
      $(".xianshiquyu").html("请先结束自动攻击");
      return;
    }
    var now_timestamp = Date.parse(new Date());
    var actionName = $(this).val();
    var var_data = null;
    var var_data1 = null;

    if (now_timestamp - timestamp < 1000 && actionName == "攻击") {
      return ;
    };

    if(actionName == "回收" || actionName == "购买" || actionName == "存入" || actionName == "取出" || actionName == "出售" || actionName == "下架" || actionName == "设置") {
      var_data = $(this).parent().find(".js-num").val();
      if(var_data < 1) {
        var_data = 1;
      }
      var_data1 = $(this).parent().find(".sell-item").val();
    }

    if(actionName == "购买号码"){
      var_data = $(this).parent().find(".js-num").val();
      if(var_data.length < 3){
        alert("号码必须3位数");
        return;
      }
    }

    if(actionName == "技能"){
      var_data = $('#skill option:selected').val();
    }

    if(actionName == "使用药品"){
      var_data = $('#drugs option:selected').val();
    }

    $.ajax({
      type:"get",
      url:$(this).data('url'),
      data:{
        action : $(this).val(),
        var_data : var_data,
        var_data1 : var_data1,
      },
      success:function(res){
        if (res.message == "") {
          return ;
        }
        $(".xianshiquyu").html(res.message);
        timestamp = Date.parse(new Date());
      },
      error:function(jqXHR){
        console.log("Error: "+jqXHR.status);
      }
    });
  });

  // post
  $(document).on('click', '.action-post', function(e){
    e.preventDefault();
    if (autoAtt) {
      $(".xianshiquyu").html("请先结束自动攻击");
      return;
    }
    var now_timestamp = Date.parse(new Date());

    if (now_timestamp - timestamp < 1000) {
      return ;
    };

    $.ajax({
      type:"post",
      url:$(this).data('url'),
      data:{
        action : $(this).val(),
        _token : token,
      },
      success:function(res){
        if (res.message == "") {
          return ;
        }
        $(".xianshiquyu").html(res.message);
        timestamp = Date.parse(new Date());
      },
      error:function(jqXHR){
        console.log("Error: "+jqXHR.status);
      }
    });
  });

  // 自动攻击
  $('.auto-attack').on('click', function (e) {
    e.preventDefault();
    if ($(this).val() === "自动攻击") {
      $(".xianshiquyu").html("自动攻击已开启...");
      $(this).val("结束自动攻击");
      autoAtt = setInterval(autoAttack, 2000);
    }else {
      $(".xianshiquyu").html("自动攻击关闭");
      $(this).val("自动攻击");
      clearInterval(autoAtt);
      autoAtt = null;
    }
  })
  function autoAttack() {
    $('#auto-attack').click();
  }
});

$(function(){
  // 数量加减
  $(document).on('click', '.add', function(e){
    var t = $(this).parent().find(".js-num");
    t.val(parseInt(t.val())+1);
    setTotal(t);
  });
  $(document).on('click', '.minus', function(e){
    var t = $(this).parent().find(".js-num");
    t.val(parseInt(t.val())-1);
    setTotal(t);
  });
  function setTotal(t){
    var tt = t.val();
    if(tt<=0){
      t.val(parseInt(t.val())+1)
    }
  };

  // 输入框限制
  var $numInput = $('#js-num');
  $numInput.on('input', function (ev) {
    $numInput.val($numInput.val().replace('+86', '').replace('-', '').replace(/[^0-9]/g, '').substring(0, 11));
  });
})
