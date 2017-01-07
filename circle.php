<?php 
	require_once("includes/init.php");
	if(!isLogin()) {
    location("非法进入","index.php");
    exit();
  }
	$user=getUser($_SESSION['weixin']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <title>朋友圈-简化版微信</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">  
  <!-- 新 Bootstrap 核心 CSS 文件 -->
  <link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.0/css/bootstrap.min.css">
  <!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
  <script src="http://cdn.bootcss.com/jquery/1.11.1/jquery.min.js"></script>
  <!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
  <script src="http://cdn.bootcss.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
  <style>
    .pics img{
      margin:5px;
    }
    .panel-body img{
      cursor: pointer;
    }
    .asaying{
      border: 1px solid #eee;
      margin:10px 0;
    }
    .poster{
      position: relative;
      height: 60px;
    }
    .poster .head{
      position: absolute;
      margin: 10px;
      top:10px;
      left: 10px;
    }
    .poster .head img{
      width:36px;
    }
    .poster .name{
      position: absolute;
      top:20px;
      left: 60px;
      font-size: 16px;
      font-weight: 400;
    }
    .poster .time{
      position: absolute;
      top:40px;
      left: 60px;
      font-size: 12px;
      color:#eee;
    }
    .spics img{
      max-width: 230px;
    }
    .ss{
      margin:10px 20px;
    }
    .spics{
      max-width:800px;
      margin:10px 20px;
    }

  </style>
  <script>
    $(function () {
        $.ajax({
              url:'includes/do.php?action=circle',
              type:'get',
              contentType: false,
              processData : false, 
              dataType : "json", 
              success:function(result){
                for(u in result.info){
                  data=result.info[u];
                  s=$(".asaying.hide").clone();
                  s.find('.poster>.head>img').attr("src","img/head"+data['head']+".jpg");
                  pics=data['pics'].split(",");
                  var q=s.find('.spics');
                  for(var i=0;i<pics.length-1;i++){
                    q.append("<img src='upload/"+pics[i]+"'>");
                  }
                  s.find('.poster>.name').html(data['name']);
                  s.find('.poster>.time').html(data['time']);
                  s.find('.ss').html(data['saying']);
                  
                  s.removeClass('hide');
                  $('.panel-body').append(s);
                }
              }
        });
        $("#toPost").click(function(){
          $("#say").modal('show');
        });
        $("#file1").change(function () {
          var data = new FormData();
          data.append('file', $("#file1")[0].files[0]);
            $.ajax({
              url:'includes/do.php?action=upload',
              type:'post',
              contentType: false,
              data:data,
              processData : false, 
              dataType : "json", 
              success:function(result){
                $(".pics").append("<img width='80px' alt="+result.url+" src='upload/"+result.url+"' >");
                $(".pics").attr("data-num",Number($(".pics").attr("data-num"))+1);
                if($(".pics").attr("data-num")>='3'){//最多上传3张
                  $("#imghead").attr("disabled","true");
                }
              },
              error:function(result){
                console.log(result);
              }
            });
        });
        $("#publish").click(function(){
          var pics="";
          for(var i=0;i<$(".pics>img").length;i++){
            data=$(".pics>img")[i];
            pics+=data.alt+',';
          }
          $.ajax({
            url: "includes/do.php?action=publish",
            type:'post',
            dataType:"json",
            data:{saying: $("#saying").val(),pics:pics},
            success:function(result){
              alert(result.info);
              if(result.success){
                $('#say').modal('hide');
                location.reload();
              }
            }
          });
        });
    })
  </script>
</head>
<body>
  <div class="container">
  <div class="logo">
    <h1><img src="img/logo.png" alt=""> 朋友圈</h1>
  </div>
    <div class="panel panel-default">
      <div class="panel-body">
      <a href="" onclick="history.back()">返回聊天</a>
      <a href="" onclick="return false;" id="toPost">发表动态</a>
      </div>
    </div>
  </div>

  <div class="asaying hide">
    <div class="poster">
      <div class="head"><img src="" alt=""></div>
      <div class="name"></div>
      <div class="time"></div>
    </div>
    <div class="ss"></div>
    <div class="spics"></div>
    
  </div>

  <div class="modal fade" id="say" tabindex="-1" role="dialog" aria-labelledby="sayLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">取消</span></button>
          <h4 class="modal-title" id="userinfoLabel">发表朋友圈</h4>
        </div>
        <div class="modal-body">
              <textarea maxlength="139" type='text' class='form-control' name="saying" id="saying" placeholder="说说你正在干嘛"></textarea>
              <button type="button" id="imghead" onclick="$('#file1').click();" class="btn btn-default">
                <span class="glyphicon glyphicon-plus"></span>上传图片
              </button>
              <input type="file" accept="image/*" style="display: none;" id="file1" name="file" >
              <p class="pics" data-num="0"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
          <button type="submit" id="publish" class="btn btn-primary">发表</button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>