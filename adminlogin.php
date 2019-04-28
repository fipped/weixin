<?php
require_once("includes/init.php");
if (isAdmin()) {
  location(0, "admin.php");
  exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache">
  <title>管理员登录</title>

  <!-- 1、Jquery组件引用-->
  <script src="https://cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
  <!-- 2、bootstrap组件引用-->
  <link href="https://cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.bootcss.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
  <!-- 3、bootstrap-table组件引用-->
  <link href="https://cdn.bootcss.com/bootstrap-table/1.11.1/bootstrap-table.min.css" rel="stylesheet">
  <script src="https://cdn.bootcss.com/bootstrap-table/1.11.0/bootstrap-table.min.js"></script>
  <script src="https://cdn.bootcss.com/bootstrap-table/1.11.0/locale/bootstrap-table-zh-CN.min.js"></script><!--显示中文-->
  <!-- 4、其他-->
  <link href="https://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

  <link rel="stylesheet" href="css/style.css">
  <script>
    $(function () {
      $("#adminlogin").unbind('click').click(function () {
        id = $('#account').val();
        pwd = $('#password').val();
        // alert('管理员登陆 account: ' + id + ' password: '+pwd+' clicked');
        $.ajax({
          url:"includes/do.php?action=adminlogin",
          method:'post',
          data:{"id":id,"pwd":pwd},
          dataType:'json',
          success:function(result){
            if(!result.success)
              alert(result.info);
            else
              location.href="admin.php";
          },
          error:function(jqXHR, textStatus, errorThrown) {
            alert('出错啦：'+ jqXHR.status + errorThrown);
          }
        });
      });
    })
  </script>
  <style>
    .panel-default{
      width: 600px;
      text-align: center;
    }
    .form-control{
      width: 300px;
    }
    .btn .btn-info{
      width: 100px;
    }
    .title{
      margin-bottom: 32px;
      line-height: 31px;
      font-weight: 700;
      font-size: 32px;
    }
    .mycon{
      margin-top:20px;
    }
    .login{
      margin-top:50px;
    }
  </style>
</head>
<body>
<div class="logo container">
  <h1><img src="img/logo.png" alt="">
    <span class="title" style="font: 38px; font-family: 微软雅黑;">管理员登录</span></h1>
</div>
<div class="mycon container">
  <center><div class="row">
      <img src="img/admin.jpg" style="width: 400px;height: 300px;" class="col-xs-12 col-sm-7">
      <div class="login col-xs-12 col-sm-4">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h2 class="panel-title">
              管理员登录
            </h2>
          </div>
          <div class="panel-body">
            <center><form class="form-horizontal" role="form">
                <div class="form-group">
                  <label for="weixin" class="col-xs-3 control-label">账号</label>
                  <div class="col-xs-8">
                    <input style="width: 230px;" type="text" class="form-control" id="account" maxlength="12">
                  </div>
                </div>
                <div class="form-group">
                  <label for="pwd" class="col-xs-3 control-label">密码</label>
                  <div class="col-xs-8">
                    <input style="width: 230px;" type="password" class="form-control" id="password" maxlength="15">
                  </div>
                </div>
              </form></center>

            <center><button type="button" id="adminlogin" class="btn btn-info" style="width: 100px;">
                登录
              </button></center>
          </div>
        </div>
      </div>

    </div></center>

</div>
<footer class="footer">
  <hr>
  <p class="text-center">
    <img src="img/logo.png" width="48px" alt="Weixin">
    <span class="title">简化版微信</span>
  </p>
  <p class="text-center">
    Copyright © 2018. All rights reserved.
  </p>
</footer>
</body>
</html>