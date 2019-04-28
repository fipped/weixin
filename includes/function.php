<?php 

$pdo;

function pdo_connect(){
	global $pdo;
	try {
		$dsn="mysql:host=".DB_HOST.";dbname=".DB_NAME;
		$pdo = new PDO($dsn,DB_USER,DB_PWD);
		$pdo->query("SET NAMES utf8");
	} catch (PDOException $e) {
		exit("数据库连接失败".$e->getMessage()."<br/>" );
	}
}

function alert($msg){
	echo"<script>alert('$msg');</script>";
	exit();
}

function location($info,$url){
	if(!empty($info)){
		echo "<script>alert('$info');location.href='$url';</script>";
	}else{
		header('Location:'.$url);
	}
	exit();
}

function isLogin(){
	return isset($_SESSION["weixin"])&&$_SESSION["weixin"];
}

function isAdmin(){
  return isset($_SESSION["admin"])&&$_SESSION["admin"];
}

function getUser($weixin)//通过微信查找用户所有信息
{
	global $pdo;
	$sth=$pdo->prepare("SELECT * FROM user WHERE weixin=?");
	$sth->execute(array($weixin));
	return $sth->fetch();
}

function getFriend($weixin){
	global $pdo;
	$sth=$pdo->prepare("SELECT f_id FROM friend WHERE u_id=? and status='good'");
	$sth->execute(array(getUser($weixin)['id']));
	$users=array();
	$sth2=$pdo->prepare("SELECT id,weixin,nickname,head,sex,geqian FROM user WHERE id=?");
	foreach($sth->fetchAll() as $r){
		$sth2->execute(array($r['f_id']));
		$users[]=$sth2->fetch();
	}
	return $users;
}
