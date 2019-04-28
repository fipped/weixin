<?php
require_once("includes/init.php");
//已经登录，跳转到微信页面
if(isLogin()) location(0,"wx.php");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <title>简化版微信</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- 新 Bootstrap 核心 CSS 文件 -->
  <link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.0/css/bootstrap.min.css">
  <!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
  <script src="http://cdn.bootcss.com/jquery/1.11.1/jquery.min.js"></script>
  <!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
  <script src="http://cdn.bootcss.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
  <style>
    .discription{
      margin:20px 0 10px 0;
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
  <script src="js/login.js"></script>
</head>
<body>
<div class="mycon container">
  <p>
    <img src="img/logo.png" alt="Weixin">
    <span class="title">简化版微信</span>
  </p>
  <div class="row">
    <img src="img/pic1.png"  class="col-xs-12 col-sm-7">
    <div class="login col-xs-12 col-sm-4">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">
            异世界的传送门
          </h2>
        </div>
        <div class="panel-body">
          <form class="form-horizontal" role="form">
            <div class="form-group">
              <label for="weixin" class="col-xs-3 control-label">微信号</label>
              <div class="col-xs-8">
                <input type="text" class="form-control" name="weixin" id="weixin" maxlength="12">
              </div>
            </div>
            <div class="form-group">
              <label for="pwd" class="col-xs-3 control-label">密码</label>
              <div class="col-xs-8">
                <input type="password" class="form-control" name="pwd" id="pwd" maxlength="15">
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9">
                <button type="button" id="login" class="btn btn-default">
                  登录
                </button>
                <button type="button" id="signin" class="btn btn-info">注册</button>
              </div>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>
  <div class="discription">
    <div class="row">
      <h3>基本功能</h3>
      <div class="col-sm-6">
        <h4>用户和消息管理</h4>
        <ul class="list-group">
          <li class="list-group-item">用户注册、登录</li>
          <li class="list-group-item">添加、删除和查看好友</li>
          <li class="list-group-item">向好友发消息、查看别人发的消息</li>
        </ul>
      </div>
      <div class="col-sm-6">
        <h4>发布和阅读</h4>
        <ul class="list-group">
          <li class="list-group-item">删除、发布朋友圈文章</li>
          <li class="list-group-item">查看朋友圈里别人的文章</li>
          <li class="list-group-item">评论、点赞别人的文章</li>
        </ul>
      </div>
    </div>
  </div>
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