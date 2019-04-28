<?php
require_once("includes/init.php");
if (!isAdmin()) {
  location("非法进入", "adminlogin.php");
  exit();
}
$sth=$pdo->prepare("SELECT * FROM user");
$sth->execute(array());
$users = $sth->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache">
  <title>管理员</title>
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
  <link href="https://cdn.bootcss.com/bootstrap-select/2.0.0-beta1/css/bootstrap-select.css" rel="stylesheet">
  <script src="https://cdn.bootcss.com/bootstrap-select/2.0.0-beta1/js/bootstrap-select.js"></script>
  <script src="https://cdn.bootcss.com/bootstrap-select/2.0.0-beta1/js/bootstrap-select.min.js"></script><!--显示中文-->

  <link href="https://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
  <link href="../css/style.min.css?v=4.1.0" rel="stylesheet">
  <link href="../css/customer_info.css?v=4.1.0" rel="stylesheet"/>

  <link rel="stylesheet" href="css/style.css">
    <script src="../js/plugins/layer/laydate/laydate.js"></script>
  <!--  <script src="/js/info.js"></script>-->
  <style>
    .wrapper .wrapper-content {
      margin: 0 auto;
    }
    .form-item:hover {
      background: rgb(247, 247, 247);
    }
    .table .table-hover:hover {
      background: rgb(247, 247, 247);
    }
    .comment-text:hover {
      background: rgb(247, 247, 247);
    }
  </style>
  <script src="js/admin.js"></script>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content" id="userLogin">
  <div class="row" id="infoArea">
    <div class="col-sm-12" style="padding: 0 10px;">
      <ul class="nav nav-tabs" id="navList">
        <li data-name="loginLogTab" class="active"><a data-toggle="tab" href="#loginLogTab"><i class="fa fa-user"></i>用户信息</a>
        </li>
      </ul>
      <div class="tab-content" id="tabContent">
        <div id="toolbar" class="btn-group">
          <button id="btn_search" type="button" class="btn btn-info btn-sm rightSize">
            <span class="glyphicon glyphicon-search" aria-hidden="true"></span> Ta 的好友
          </button>
          <button id="btn_edit" type="button" class="btn btn-info btn-sm rightSize">
            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>修改
          </button>
          <button id="btn_delete" type="button" class="btn btn-info btn-sm rightSize">
            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> 删除
          </button>
          <button id="btn_add" type="button" class="btn btn-info btn-sm rightSize">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 增加
          </button>
          <a  type="button" class="btn btn-info btn-sm rightSize" href="includes/do.php?action=adminlogout">
            <span  class="glyphicon glyphicon-plus" aria-hidden="true"></span> 注销
          </a>
        </div>
        <div id="loginLogTab" class="tab-pane active">
          <div class="table table-hover">
            <table class="table" id="showUserTable">
              <thead>
              <tr>
                <th></th>
                <th>头像</th>
                <th>微信</th>
                <th>昵称</th>
                <th>性别</th>
                <th>密码</th>
                <th>个性签名</th>
              </tr>
              </thead>
              <tbody>

<!--              测试表项-->
<!--              测试表项结束-->
        <?php
        foreach ($users as $u) { ?>
            <tr class="form-item" onclick="">
          <td><input type="radio" value=" " name="select" uid="<?php echo $u['id']; ?>"/></td>
          <td><img onclick="return false;" src='img/head<?php echo $u['head']; ?>.jpg'
                   width="40px" alt=""></td>
          <td><?php echo $u['weixin']; ?></td>
          <td><?php echo $u['nickname']; ?></td>
          <td><img width='10px' src="img/<?php echo $u['sex']; ?>.png"></td>
          <td><?php echo $u['password']; ?></td>
          <td><?php echo $u['geqian']; ?></td>
          </tr>

        <?php } ?>
      <script >
          $('.form-item').click(function () {
            $(this).find("[name='select']").prop("checked",true);
          })
      </script>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- 点击弹出对应好友列表 -->
<div class="modal fade" id="friendlist" tabindex="-1" role="dialog" aria-labelledby="settingLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                  class="sr-only">取消</span></button>
        <h4 class="modal-title" id="settingLabel">ta的好友</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" role="form">
          <!--好友信息表格-->
          <table class="table">
            <thead>
            <tr>
              <th>头像</th>
              <th>微信</th>
              <th>昵称</th>
              <th>性别</th>
              <th>个性签名</th>
              <th>操作</th>
            </tr>
            </thead>
            <tbody id="friendsTable">
            <!--表的内容需要从数据库获取-->
            </tbody>
          </table>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
</div>

<!-- 点击弹出个人资料供管理员修改 -->
<div class="modal fade" id="personalInfo" tabindex="-1" role="dialog" aria-labelledby="settingLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                  class="sr-only">取消</span></button>
        <h4 class="modal-title" id="settingLabel">修改Ta的信息</h4>
      </div>
      <div class="modal-body">
        <!--
                  需要从数据库获取个人信息
                -->
        <form class="form-horizontal" role="form">

          <div class="form-group">
            <label class="col-xs-3 control-label" for="weixin">微信号</label>
            <div class="col-xs-8">
              <input type="text" id="weixin" name="weixin" value="微信号不能被修改略略略" class="form-control" readonly>
            </div>
          </div>
          <div class="form-group">
            <label class="col-xs-3 control-label" for="head">头像</label>
            <div class="col-xs-8">
<!--              <select name="head" class="form-control" >-->
<!---->
<!--              </select>-->
              <select class="selectpicker form-control" id="head">
                <?php
                for ($i = 0; $i <= 10; $i++) {
                  echo "<option data-content=\"<span><img width=25px src='img/head".$i.".jpg'>头像".$i."</span>\""."value=" . $i . "
        				" . ($i == $user['head'] ? "selected" : "") . ">头像" . $i . "</option>";
                }
                ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-xs-3 control-label" for="nickname">昵称</label>
            <div class="col-xs-8">
              <input type="text" id="nickname" name="nickname" value="<?php echo $user['nickname'] ?>" maxlength="12"
                     class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label class="col-xs-3 control-label" for="nickname">性别</label>
            <div class="col-xs-2">
              <input name="sex" class="control-form" type="radio" id="boy"
                     value="boy" <?php if ($user['sex'] == "boy") echo "checked"; ?> > <img width="16px"
                                                                                            src="img/boy.png">
            </div>
            <div class="col-xs-2">
              <input name="sex" class="control-form" type="radio" id="girl"
                     value="girl" <?php if ($user['sex'] != "boy") echo "checked"; ?>> <img width="16px"
                                                                                            src="img/girl.png">
            </div>
          </div>
          <div class="form-group">
            <label for="geqian" class="col-xs-3 control-label">个性签名</label>

            <div class="col-xs-8">
              <input type="text" id="geqian" name="geqian" value="<?php echo $user['geqian'] ?>" maxlength="70"
                     class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label class="col-xs-3 control-label" for="password">密码</label>
            <div class="col-xs-8">
              <input type="password" id="password" name="password" value="" class="form-control" maxlength="15"
                     placeholder="不修改请留空">
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" id="saveBtn" class="btn btn-primary">保存</button>
      </div>
    </div>
  </div>
</div>
<!--
	聊天记录
-->
<div class="modal fade" id="chathistory" tabindex="-1" role="dialog" aria-labelledby="settingLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                  class="sr-only">取消</span></button>
        <h4 class="modal-title" id="settingLabel" style="text-align: center;">聊天记录</h4>
      </div>
        <div class="modal-body">
            <div id="wxboard">
              <div class="panel panel-default">
                <div class="panel-body">
                  点击左侧好友栏里的好友开始聊天吧！
                </div>
              </div>
            </div>
        </div>
      <div class="modal-footer">
        <button type="button" id="btn_closeHis" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
</div>

<!--
    增加用户
  -->
<div class="modal fade" id="addUser" tabindex="-1" role="dialog" aria-labelledby="settingLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                  class="sr-only">取消</span></button>
        <h4 class="modal-title" id="settingLabel">添加新用户</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" role="form">
          <div class="form-group">
            <label class="col-xs-3 control-label" for="weixin">微信号</label>
            <div class="col-xs-8">
              <input type="text" id="weixin" name="weixin" value="" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label class="col-xs-3 control-label" for="password">密码</label>
            <div class="col-xs-8">
              <input type="password" id="password" name="password" value="" class="form-control" maxlength="15">
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" id="addBtn" class="btn btn-primary">添加</button>
      </div>
    </div>
  </div>
</div>
<!--聊天 面板模板-->
<div class="talk hide">
  <div class="dialog"></div>
</div>
<!--聊天面板模板结束-->

<!-- 对话模板开始-->
<div class="dia mydia hide row">
  <div class="log">...</div>
  <img class="head" src="img/head<?php echo $user['head']; ?>.jpg" alt="">
</div>

<div class="dia hedia hide row">
  <img class="head">
  <div class="log ">...</div>
</div>
<!-- 对话模板结束-->
</body>
</html>