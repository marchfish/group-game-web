<!DOCTYPE html>
<html lang="zh-Hans">
<head>
  <meta charset="utf-8">
  <title>vuejs</title>
  <script src="{{ URL::asset('forestage/public/vue-bundle-2.6.10/vue-bundle.js') }}"></script>
  <script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdn.bootcss.com/axios/0.19.0/axios.min.js"></script>
</head>
<body>

<div id="app-9">
  <ol>
    <li v-for="message in messages">
      {% message %}
    </li>
  </ol>
  <input v-model="content">
  <button @click.prevent="sendMessage">发送</button>
</div>

<script>
  $(function() {
    var ws = new WebSocket("ws://10.0.0.131:8282");

    var app9 = new Vue({
      el: '#app-9',
      data: {
        messages: [],
        content: "",
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

              });
              break;
            default :
              this.messages.push(res.message);
          }
        }.bind(this);
      },
      methods: {
        sendMessage: function() {
          axios.post('/api/road2d/message',{
            message: this.content,
          }).then(function(res){
            console.log(res.data);
          })
        },
        logout: function() {
          axios.get('/api/road2d/logout',{}).then(function(res){
            console.log(res.data);
          })
        }
      }
    });
  })

</script>
</body>
</html>
