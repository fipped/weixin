<?php 
	require_once("includes/init.php");
	if(!isLogin()) {
    location("非法进入","index.php");
    exit();
  }
  if(!isset($_GET['token'])){
    unset($_SESSION['weixin']);
    location(0,"index.php");
  }
	$user=getUser($_SESSION['weixin']);
  $friend=getFriend($_SESSION['weixin']);
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
  <script src="js/web.js"></script>
    <script>   
    //连接
    var so=connect(<?php echo ('"'.$_SESSION['weixin'].'","'.$_GET['token'].'"');?>);
  </script>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
<div class="panel panel-default">
	<div class="panel-body">
	<div class="row">
		<div class="col-xs-12 col-sm-4">
			<div class="my-info panel panel-default">
  			<div class="panel-body">			
          <img onclick="return false;" id="myhead" src="img/head<?php echo $user['head'];?>.jpg">
  				<div class="info">
  					<span class="nickname" style="font-weight: 500;"><?php echo $user['nickname'];?></span>
            <span style='font-size:12px'>「<?php echo $user['weixin'] ?>」</span>
  					<div class="geqian"  style='font-size:12px'>
  						<?php echo $user['geqian'];?>
  					</div>
  					<a href="circle.php" class="setbtn">朋友圈</a>
  					<a href="includes/do.php?action=logout">注销</a>
  				</div>
  			</div>
			</div>
			<button id="addBtn" onClick=" $('.modal-footer').show();$('#addf').modal('show');" class="btn btn-default btn-block">加好友</button>
			<p>
				<ul class="list-group">
					<li class="msg hide list-group-item list-group-item-info"> </li>
          <?php 
          foreach($friend as $f){ ?>
            <li class='friends list-group-item' data-weixin="<?php echo $f['weixin']; ?>" data-id="<?php echo $f['id']; ?>">
            <span class="msgnum hide">0</span>
            <img class='head offline' width='32px' src='img/head<?php echo $f['head']; ?>.jpg'>
            <span class="nickname">
              <?php echo $f['nickname']; ?>
            </span>
            <img width='10px' src="img/<?php echo $f['sex'];?>.png">
            <button type='button' class='close'>
              <span aria-hidden='true'>&times;</span>
              <span class='sr-only'>Close</span>
            </button>
            </li>
          <?php } ?>
				</ul>
			</p>	
		</div>
		<div id="wxboard" class="col-xs-12 col-sm-8">
			<div class="panel panel-default">
			  <div class="panel-heading">
			    <h3 class="panel-title">寂寞的面板</h3>
			  </div>
			  <div class="panel-body">
			    点击左侧好友栏里的好友开始聊天吧！
			  </div>
			</div>
		</div>
	</div>
	</div>
</div>
</div>

<!-- Begin 个人设置 -->
<div class="modal fade" id="setting" tabindex="-1" role="dialog" aria-labelledby="settingLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">取消</span></button>
        <h4 class="modal-title" id="settingLabel">个人设置</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" role="form">
        	<div class="form-group">
        		<label class="col-xs-3 control-label" for="weixin">微信号</label>
        		 <div class="col-xs-8">
        		<input type="text" id="weixin" name="weixin" value="<?php echo $user['weixin']?>" class="form-control" readonly>
        		 </div>
       		</div>

			<div class="form-group">
        		<label class="col-xs-3 control-label" for="head">头像</label>
        		 <div class="col-xs-8">
        		<select name="head" class="form-control" id="head">
      			<?php 
        			for($i=0;$i<=10;$i++){
        				echo "<option value=".$i."
        				".($i==$user['head']?"selected":"").">头像".$i."</option>";
        			}
        			 ?>
        			}
        		</select>
        		</div>
       		</div>       		
			<div class="form-group">
        		<label class="col-xs-3 control-label" for="nickname">昵称</label>
        		 <div class="col-xs-8">
        		<input type="text" id="nickname" name="nickname" value="<?php echo $user['nickname']?>" maxlength="12" class="form-control">
        		</div>
       		</div>
			<div class="form-group">
        		<label class="col-xs-3 control-label" for="nickname">性别</label>
        		 <div class="col-xs-2">
        		 <input name="sex" class="control-form" type="radio" value="boy" <?php if($user['sex']=="boy")echo "checked"; ?> > <img width="16px" src="img/boy.png">
        		 </div>
        		 <div class="col-xs-2">
        		 <input name="sex" class="control-form" type="radio" value="girl" <?php if($user['sex']!="boy")echo "checked";?>> <img width="16px" src="img/girl.png">
        		</div>
       		</div>   		
			<div class="form-group">
        		<label for="geqian" class="col-xs-3 control-label">个性签名</label>

        		 <div class="col-xs-8">
        		<input type="text" id="geqian" name="geqian" value="<?php echo $user['geqian']?>" maxlength="70" class="form-control">
        		</div>
    		</div>
			<div class="form-group">
        		<label class="col-xs-3 control-label" for="password">密码</label>
        		 <div class="col-xs-8">
        		 	<input type="password" id="password" name="password" value="" class="form-control" maxlength="15" placeholder="不修改请留空" >
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
<!-- END 个人设置 -->


<!-- Begin 添加好友 -->
<div class="modal fade" id="addf" tabindex="-1" role="dialog" aria-labelledby="addfLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">取消</span></button>
        <h4 class="modal-title" id="addfLabel">查找/添加好友</h4>
      </div>
      <div class="modal-body">
      	<input type="text" class="form-control" placeholder="请输入微信号" id="findWeixin" maxlength="12">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" id="findBtn" onClick="findWx($('#findWeixin').val());" class="btn btn-primary">查找</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="userinfo" tabindex="-1" role="dialog" aria-labelledby="userinfoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">取消</span></button>
        <h4 class="modal-title" id="userinfoLabel">用户信息</h4>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" id="addfriend" class="btn btn-primary">添加</button>
      </div>
    </div>
  </div>
</div>
<!-- END 添加好友 -->

<!--好友请求模板 -->
<li class='hide haoyouqq list-group-item'>
  <div class='row'>
    <div class='col-xs-12 col-sm-3'>
      <img class="img-thumbnail head">
    </div>
    <div class='col-xs-12 col-sm-5'>
      <h4><span class="nickname"></span><span style='font-size:12px'>「<span class="weixin"></span>」</span>
      <img width="16px" class="sex"></h4>
      <p class="gq"></p>
    </div>
    <div class="col-xs-12 col-sm-4">
      <button class="okbtn btn btn-success">
        同意
      </button>
      <button class="nobtn btn btn-danger">
        拒绝
      </button>
    </div>
  </div>
</li>
<!-- 好友请求模板结束-->

<!--聊天 面板模板-->
<div class="talk hide">
<div class="dialog"></div>
<div class='input-group'>
  <textarea maxlength="3000" type='text' class='form-control'></textarea>
    <span class='input-group-btn'>
      <button class='btn btn-default' type='button'>发送<br>Ctrl+Enter</button>
    </span>
</div>
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

<script src="js/main.js"></script>
<footer class="footer">
	<hr>
	<p  class="text-center">
	<img src="img/logo.png" width="48px" alt="Weixin">
	<span class="title">简化版微信</span>
	</p>
	<p class="text-center">
		Copyright © 2016. All rights reserved.
	</p>
</footer>
</body>
</html>
