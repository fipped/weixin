<?php 	
	require_once("init.php");
	$action='';
	if(isset($_GET['action']))
		$action=$_GET['action'];

	//注销
	if($action=="logout"){
		if(isset($_SESSION['weixin'])){
			session_unset();
			session_destroy();
		}
		location(0,'../index.php');
		exit();
	}
	
	//登录
	if($action=='login'){
		$weixin=$_POST['weixin'];
		$pwd=$_POST['pwd'];

		$sth=$pdo->prepare("select * from user where weixin=? and password=?");
		$sth->execute(array($weixin,md5($pwd)));
		$user=$sth->fetch();
		if($user){
			$_SESSION["weixin"]=$user["weixin"];
			$token=md5($user['weixin'].date('Y-m-d', time())."websocket");
			echo json_encode(array("success"=>true,"info"=>"登录成功","token"=>$token));
		}else{
			if(isset($_SESSION['weixin'])){
				unset($_SESSION['weixin']);
				session_destroy();
			}
			echo json_encode(array("success"=>false,"info"=>"登录失败"));
		}
		exit();
	}

	//注册
	if($action=='signin'){
		$weixin=$_POST['weixin'];
		$pwd=$_POST['pwd'];
		$sth=$pdo->prepare("select * from user where weixin=?");
		$sth->execute(array($weixin));

		if($sth->fetch()){
			echo json_encode(array('success'=>false,'info'=>'该微信号已被注册'));
		}else{
			$sth=$pdo->prepare("INSERT INTO user(weixin,password) VALUE (?,?)");
			$sth->execute(array($weixin,md5($pwd)));
			if($sth->rowCount())
				echo json_encode(array('success'=>true,'info'=>'注册成功'));
			else
				echo json_encode(array('success'=>false,'info'=>'注册失败'));
		}
	}

	//设置
	if($action=='set'){
		if(!isset($_SESSION['weixin'])){
			echo json_encode(array('success'=>false,'info'=>'非法请求'));
			exit();
		}
		$gq=$_POST['gq'];	//个性签名
		$nn=$_POST['nick'];	//昵称
		$pw=$_POST['pw'];	//密码
		$sex=$_POST['sex'];	//性别
		$hd=$_POST['hd'];	//头像

		$sth=$pdo->prepare("UPDATE user SET geqian=?,nickname=?,sex=?,head=? WHERE weixin=?");
		$sth->execute(array($gq,$nn,$sex,$hd,$_SESSION['weixin']));

		//修改密码
		if($pw){
			$sth=$pdo->prepare("UPDATE user SET password=? WHERE weixin=?");
			$sth->execute(array(md5($pw),$_SESSION['weixin']));
		}

		if($sth->rowCount()){
			echo json_encode(array('success'=>true,'info'=>'修改成功'));
			return;
		}
		echo json_encode(array('success'=>false,'info'=>'修改失败'));
	}

	//查找好友
	if($action=='find'){
		$user=getUser($_GET['weixin']);
		if($user){
			echo json_encode(array("success"=>true,"info"=>"查找成功","user"=>$user));
			exit();
		}
		echo json_encode(array("success"=>false,"info"=>"查无此用户"));
	}

	//添加好友
	if($action=='add'){
		$u=getUser($_SESSION['weixin']);
		$f=getUser($_GET['friend']);
		if($u==$f){
			echo json_encode(array("success"=>false,"info"=>"不能请求添加自己为好友"));
			exit();
		}
		if(!$u||!$f){
			echo json_encode(array("success"=>false,"info"=>"用户不存在"));
			exit();
		}

		$sth=$pdo->prepare("SELECT * FROM friend WHERE u_id=? and f_id=?");
		$sth->execute(array($u['id'],$f['id']));
		$relation=$sth->fetch();
		if($relation){
			
			if($relation['status']=='good')$info="你们已经是好友了";
			else if($relation['status']=='request'){
				$info="请求已发送过，请耐心等待对方回应~";
			}else{
				$sth=$pdo->prepare("UPDATE friend SET status='request' WHERE u_id=? and f_id=?");
				$sth->execute(array($u['id'],$f['id']));

				$info="虽然对方拒绝过你，但是我们重新为你发送了请求";
			}
			echo json_encode(array("success"=>true,"info"=>$info));
			exit();
		}

		$sth=$pdo->prepare("INSERT INTO friend(u_id,f_id) VALUE(?,?)");
		$sth->execute(array($u['id'],$f['id']));

		if($sth->rowCount())
			echo json_encode(array("success"=>true,"info"=>"请求成功"));
		else 
			echo json_encode(array("success"=>false,"info"=>"请求失败"));
	}

	//获取请求好友信息数量
	if($action=='get'){
		$me=getUser($_SESSION['weixin']);
		$sth=$pdo->prepare("SELECT u_id FROM friend WHERE f_id=? and status='request'");
		$sth->execute(array($me['id']));
		$row=$sth->fetchAll();

		$sth=$pdo->prepare("SELECT * FROM user WHERE id=?");
		if($row)
			echo json_encode(array("success"=>true,"info"=>"新的好友请求","num"=>count($row)));
		else
			echo json_encode(array("success"=>false,"info"=>"没有用户请求添加您为好友"));
	}
	//获取请求好友信息
	if($action=='get2'){
		$me=getUser($_SESSION['weixin']);
		$sth=$pdo->prepare("SELECT u_id FROM friend WHERE f_id=? and status='request'");
		$sth->execute(array($me['id']));
		$row=$sth->fetchAll();

		$sth=$pdo->prepare("SELECT * FROM user WHERE id=?");
		$users = array();
		foreach($row as $u ){
			$sth->execute(array($u['u_id']));
			$users[]=$sth->fetch();
		}
		if($row)
			echo json_encode(array("success"=>true,"info"=>"新的好友请求","users"=>$users));
		else
			echo json_encode(array("success"=>false,"info"=>"没有用户请求添加您为好友"));
	}
	//接受好友
	if($action=='accept'){
		$sth=$pdo->prepare("UPDATE friend SET status='good' WHERE u_id=? and f_id=? and status='request'");
		$me=getUser($_SESSION['weixin'])['id'];
		$he=getUser($_GET['weixin'])['id'];
		$sth->execute(array($he,$me));

		$sth=$pdo->prepare("SELECT * FROM friend WHERE u_id=? and f_id=?");
		$sth->execute(array($me,$he));
		if($sth->rowCount()){
			$sth=$pdo->prepare("UPDATE friend SET status='good' WHERE u_id=? and f_id=?");
			$sth->execute(array($me,$he));
		}else{
			$sth=$pdo->prepare("INSERT INTO friend (u_id,f_id,status) VALUE (?,?,'good')");
			$sth->execute(array($me,$he));
		}
		
		if($sth->rowCount())
			echo json_encode(array("success"=>true,"info"=>"已添加该好友"));
		else
			echo json_encode(array("success"=>false,"info"=>"操作失败"));
	}

	//拒绝
	if($action=='reject'){
		$sth=$pdo->prepare("UPDATE friend SET status='bad' WHERE u_id=? and f_id=? and status='request'");
		$sth->execute(array(getUser($_GET['weixin'])['id'],getUser($_SESSION['weixin'])['id']));
		if($sth->rowCount())
			echo json_encode(array("success"=>true,"info"=>"已拒绝该好友"));
		else
			echo json_encode(array("success"=>false,"info"=>"数据库请求错误"));
	}

	//删除好友
	if($action=='del'){
		$myid=getUser($_SESSION['weixin'])['id'];
		$sth=$pdo->prepare("DELETE FROM friend WHERE u_id=? and f_id=?");
		$sth->execute(array($_GET['id'],$myid));
		$row=$sth->rowCount();
		$sth->execute(array($myid,$_GET['id']));
		if($row||$sth->rowCount())
			echo json_encode(array("success"=>true,"info"=>"删除成功"));
		else
			echo json_encode(array("success"=>false,"info"=>"数据库请求错误"));
	}

	//发送消息
	if($action=='send'){
		$myid=getUser($_SESSION['weixin'])['id'];
		$fid=$_POST['id'];
		$msg=$_POST['msg'];
		$sth=$pdo->prepare("INSERT INTO chatlog(u_id,f_id,content,time) VALUE (?,?,?,?)");
		$sth->execute(array($myid,$fid,$msg,date("Y-m-d h:i:s")));
		if($sth->rowCount())
			echo json_encode(array("success"=>true,"info"=>"消息发送成功"));
		else
			echo json_encode(array("success"=>false,"info"=>"消息发送失败"));
	}

	//聊天记录
	if($action=='log'){
		$myid=getUser($_SESSION['weixin'])['id'];
		$fid=$_POST['id'];
		$sth=$pdo->prepare("SELECT * FROM chatlog WHERE (u_id=? AND f_id=?) OR (u_id=? AND f_id=?) order by id desc LIMIT 100");
		$sth->execute(array($fid,$myid,$myid,$fid));

		if($sth->rowCount())
			echo json_encode(array("success"=>true,"info"=>"消息接收成功","rec"=>$sth->fetchAll()));
		else
			echo json_encode(array("success"=>false,"info"=>"消息接收失败"));
	}

	//给websocket返回认证的用户
	if($action=='user'){
		echo $_SESSION['weixin'];
	}

	//上传图片
	if($action=='upload'){
        $targetFile = $_SESSION['weixin']. time().".jpg";    
        //将图片已json形式返回给js处理页面  ，这里大家可以改成自己的json返回处理代码
        echo json_encode(array(
            'success' => move_uploaded_file($_FILES['file']['tmp_name'], "../upload/".$targetFile) ? true : false,
            'url' => $targetFile,
        ));
    }

    if($action=='publish'){
    	$myid=getUser($_SESSION['weixin'])['id'];
		$saying=$_POST['saying'];
		//<img width="80px" src="upload/20152114571482765493.jpg">
		substr($saying,31);
		$pics=$_POST['pics'];
    	$sth=$pdo->prepare("INSERT INTO circle(u_id,saying,pics,time) VALUE (?,?,?,?)");
    	$sth->execute(array($myid,$saying,$pics,date("Y-m-d h:i:s")));
		if($sth->rowCount())
			echo json_encode(array("success"=>true,"info"=>"发表成功"));
		else
			echo json_encode(array("success"=>false,"info"=>"发表失败"));
    }

    if($action=='circle'){
    	$friend=getFriend($_SESSION['weixin']);
    	;
    	$sth=$pdo->prepare("SELECT * FROM circle where u_id=?");
    	$sayings=array();
    	$sth->execute(array(getUser($_SESSION['weixin'])['id']));
    	if($sth->rowCount()){
    		$me=getUser($_SESSION['weixin']);
    		foreach ($sth->fetchAll() as $value) {
    			$value['head']=$me['head'];
    			$value['name']=$me['nickname'];
    			$sayings[]=$value;
    		}
    	}
    	foreach ($friend as $f) {
    		$sth->execute(array($f['id']));
    		if($sth->rowCount()){
	    		foreach ($sth->fetchAll() as $t) {
	    			$t['head']=$f['head'];
	    			$t['name']=$f['nickname'];
	    			$sayings[]=$t;
	    		}
    		}
    	}
		$datetime = array();
		foreach ($sayings as $v) {
			$datetime[] = date("YmdHis",strtotime($v['datetime']));
		}
		array_multisort($datetime,SORT_DESC,$sayings);

		echo json_encode(array("success"=>true,"info"=>$sayings));
    }
?>