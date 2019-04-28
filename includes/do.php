<?php
require_once("init.php");
$action = '';
if (isset($_GET['action']))
  $action = $_GET['action'];

//注销
if ($action == "logout") {
  if (isset($_SESSION['weixin'])) {
    session_unset();
    session_destroy();
  }
  location(0, '../index.php');
  exit();
}

//获得用户所有朋友
if ($action == 'getFriends')
{
  $sth = $pdo->prepare("SELECT * from friend join user on friend.f_id=user.id where u_id=? and status='good'");
  $sth->execute(array($_GET['uid']));
  $f=$sth->fetchAll();
  echo json_encode(array("success" => true, "info" => $f));
}

//获得用户信息
if ($action == 'getUser')
{
  $sth = $pdo->prepare("SELECT * from user where id=? ");
  $sth->execute(array($_GET['uid']));
  if($sth->rowCount())
    echo json_encode(array("success" => true, "info" => $sth->fetch()));
  else
    echo json_encode(array("success" => false, "info" => '请求失败'));
}

//删除用户
if ($action == 'delUser')
{
  $sth = $pdo->prepare("delete from user where id=? ");
  $sth->execute(array($_GET['uid']));
  if($sth->rowCount())
    echo json_encode(array("success" => true, "info" => '删除成功'));
  else
    echo json_encode(array("success" => false, "info" => '删除失败'));
}

//登录
if ($action == 'login') {
  $weixin = $_POST['weixin'];
  $pwd = $_POST['pwd'];

  $sth = $pdo->prepare("select * from user where weixin=? and password=?");
  $sth->execute(array($weixin, md5($pwd)));
  $user = $sth->fetch();
  if ($user) {
    $_SESSION["weixin"] = $user["weixin"];
    $token = md5($user['weixin'] . date('Y-m-d', time()) . "websocket");
    echo json_encode(array("success" => true, "info" => "登录成功", "token" => $token));
  } else {
    if (isset($_SESSION['weixin'])) {
      unset($_SESSION['weixin']);
      session_destroy();
    }
    echo json_encode(array("success" => false, "info" => "登录失败"));
  }
  exit();
}

//管理员登录
if ($action == 'adminlogin') {
  $id = $_POST['id'];
  $pwd = $_POST['pwd'];

  if ($id==ADMIN_ID and $pwd ==ADMIN_PASSWORD) {
    $_SESSION["admin"] = ADMIN_ID;
    echo json_encode(array("success" => true, "info" => "登录成功"));
  } else {
    if (isset($_SESSION['admin'])) {
      unset($_SESSION['admin']);
    }
    echo json_encode(array("success" => false, "info" => "登录失败"));
  }
  exit();
}

//管理员登出
if ($action == "adminlogout") {
  if (isset($_SESSION['admin'])) {
    unset($_SESSION['admin']);
  }

  location(0, '../adminlogin.php');
  exit();
}


//注册
if ($action == 'signin') {
  $weixin = $_POST['weixin'];
  $pwd = $_POST['pwd'];
  $sth = $pdo->prepare("select * from user where weixin=?");
  $sth->execute(array($weixin));

  if ($sth->fetch()) {
    echo json_encode(array('success' => false, 'info' => '该微信号已被注册'));
  } else {
    $sth = $pdo->prepare("INSERT INTO user(weixin,password) VALUE (?,?)");
    $sth->execute(array($weixin, md5($pwd)));
    if ($sth->rowCount())
      echo json_encode(array('success' => true, 'info' => '注册成功'));
    else
      echo json_encode(array('success' => false, 'info' => '注册失败'));
  }
}

//设置
if ($action == 'set') {
  if (isset($_POST['weixin']))
  {
    $wx=$_POST['weixin'];
  }
  else if (!isset($_SESSION['weixin'])) {
//    if($_POST['weixin'])
    echo json_encode(array('success' => false, 'info' => '非法请求'));
    exit();
  }
  else
    $wx=$_SESSION['weixin'];

  $gq = $_POST['gq'];  //个性签名
  $nn = $_POST['nick'];  //昵称
  $pw = $_POST['pw'];  //密码
  $sex = $_POST['sex'];  //性别
  $hd = $_POST['hd'];  //头像

  $sth = $pdo->prepare("UPDATE user SET geqian=?,nickname=?,sex=?,head=? WHERE weixin=?");
  $sth->execute(array($gq, $nn, $sex, $hd, $wx));

  //修改密码
  if ($pw) {
    $sth = $pdo->prepare("UPDATE user SET password=? WHERE weixin=?");
    $sth->execute(array(md5($pw), $wx));
  }

  if ($sth->rowCount()) {
    echo json_encode(array('success' => true, 'info' => '修改成功'));
    return;
  }
  echo json_encode(array('success' => false, 'info' => '修改失败'));
}

//查找好友
if ($action == 'find') {
  $user = getUser($_GET['weixin']);
  if ($user) {
    echo json_encode(array("success" => true, "info" => "查找成功", "user" => $user));
    exit();
  }
  echo json_encode(array("success" => false, "info" => "查无此用户"));
}

//添加好友
if ($action == 'add') {
  $u = getUser($_SESSION['weixin']);
  $f = getUser($_GET['friend']);
  if ($u == $f) {
    echo json_encode(array("success" => false, "info" => "不能请求添加自己为好友"));
    exit();
  }
  if (!$u || !$f) {
    echo json_encode(array("success" => false, "info" => "用户不存在"));
    exit();
  }

  $sth = $pdo->prepare("SELECT * FROM friend WHERE u_id=? and f_id=?");
  $sth->execute(array($u['id'], $f['id']));
  $relation = $sth->fetch();
  if ($relation) {

    if ($relation['status'] == 'good') $info = "你们已经是好友了";
    else if ($relation['status'] == 'request') {
      $info = "请求已发送过，请耐心等待对方回应~";
    } else {
      $sth = $pdo->prepare("UPDATE friend SET status='request' WHERE u_id=? and f_id=?");
      $sth->execute(array($u['id'], $f['id']));

      $info = "虽然对方拒绝过你，但是我们重新为你发送了请求";
    }
    echo json_encode(array("success" => true, "info" => $info));
    exit();
  }

  $sth = $pdo->prepare("INSERT INTO friend(u_id,f_id) VALUE(?,?)");
  $sth->execute(array($u['id'], $f['id']));

  if ($sth->rowCount())
    echo json_encode(array("success" => true, "info" => "请求成功"));
  else
    echo json_encode(array("success" => false, "info" => "请求失败"));
}

//获取请求好友信息数量
if ($action == 'get') {
  $me = getUser($_SESSION['weixin']);
  $sth = $pdo->prepare("SELECT u_id FROM friend WHERE f_id=? and status='request'");
  $sth->execute(array($me['id']));
  $row = $sth->fetchAll();

  $sth = $pdo->prepare("SELECT * FROM user WHERE id=?");
  if ($row)
    echo json_encode(array("success" => true, "info" => "新的好友请求", "num" => count($row)));
  else
    echo json_encode(array("success" => false, "info" => "没有用户请求添加您为好友"));
}
//获取请求好友信息
if ($action == 'get2') {
  $me = getUser($_SESSION['weixin']);
  $sth = $pdo->prepare("SELECT u_id FROM friend WHERE f_id=? and status='request'");
  $sth->execute(array($me['id']));
  $row = $sth->fetchAll();

  $sth = $pdo->prepare("SELECT * FROM user WHERE id=?");
  $users = array();
  foreach ($row as $u) {
    $sth->execute(array($u['u_id']));
    $users[] = $sth->fetch();
  }
  if ($row)
    echo json_encode(array("success" => true, "info" => "新的好友请求", "users" => $users));
  else
    echo json_encode(array("success" => false, "info" => "没有用户请求添加您为好友"));
}
//接受好友
if ($action == 'accept') {
  $sth = $pdo->prepare("UPDATE friend SET status='good' WHERE u_id=? and f_id=? and status='request'");
  $me = getUser($_SESSION['weixin'])['id'];
  $he = getUser($_GET['weixin'])['id'];
  $sth->execute(array($he, $me));

  $sth = $pdo->prepare("SELECT * FROM friend WHERE u_id=? and f_id=?");
  $sth->execute(array($me, $he));
  if ($sth->rowCount()) {
    $sth = $pdo->prepare("UPDATE friend SET status='good' WHERE u_id=? and f_id=?");
    $sth->execute(array($me, $he));
  } else {
    $sth = $pdo->prepare("INSERT INTO friend (u_id,f_id,status) VALUE (?,?,'good')");
    $sth->execute(array($me, $he));
  }

  if ($sth->rowCount())
    echo json_encode(array("success" => true, "info" => "已添加该好友"));
  else
    echo json_encode(array("success" => false, "info" => "操作失败"));
}

//拒绝
if ($action == 'reject') {
  $sth = $pdo->prepare("UPDATE friend SET status='bad' WHERE u_id=? and f_id=? and status='request'");
  $sth->execute(array(getUser($_GET['weixin'])['id'], getUser($_SESSION['weixin'])['id']));
  if ($sth->rowCount())
    echo json_encode(array("success" => true, "info" => "已拒绝该好友"));
  else
    echo json_encode(array("success" => false, "info" => "数据库请求错误"));
}

//删除好友
if ($action == 'del') {
  $myid = getUser($_SESSION['weixin'])['id'];
  $sth = $pdo->prepare("DELETE FROM friend WHERE u_id=? and f_id=?");
  $sth->execute(array($_GET['id'], $myid));
  $row = $sth->rowCount();
  $sth->execute(array($myid, $_GET['id']));
  if ($row || $sth->rowCount())
    echo json_encode(array("success" => true, "info" => "删除成功"));
  else
    echo json_encode(array("success" => false, "info" => "数据库请求错误"));
}

//发送消息
if ($action == 'send') {
  $myid = getUser($_SESSION['weixin'])['id'];
  $fid = $_POST['id'];
  $msg = $_POST['msg'];
  $sth = $pdo->prepare("INSERT INTO chatlog(u_id,f_id,content,time) VALUE (?,?,?,?)");
  $sth->execute(array($myid, $fid, $msg, date("Y-m-d H:i:s")));
  if ($sth->rowCount())
    echo json_encode(array("success" => true, "info" => "消息发送成功"));
  else
    echo json_encode(array("success" => false, "info" => "消息发送失败"));
}

//聊天记录
if ($action == 'log') {
  if(isset($_POST['uid']))
    $myid = $_POST['uid'];
  else if(isset($_SESSION['weixin']))
    $myid = getUser($_SESSION['weixin'])['id'];
  else
  {
    echo json_encode(array('success' => false, 'info' => '非法请求'));
    exit();
  }
  $fid = $_POST['id'];
  $sth = $pdo->prepare("SELECT * FROM chatlog WHERE (u_id=? AND f_id=?) OR (u_id=? AND f_id=?) order by id desc LIMIT 100");
  $sth->execute(array($fid, $myid, $myid, $fid));
  if ($sth->rowCount())
  {
    $hth=$pdo->prepare("SELECT * FROM user WHERE id=?");
    $hth->execute(array($myid));
    $uhead=$hth->fetch()['head'];
    $hth->execute(array($fid));
    $fhead=$hth->fetch()['head'];
    echo json_encode(array("success" => true, "info" => "消息接收成功", "uhead"=>$uhead,"fhead"=>$fhead,"rec" => $sth->fetchAll()));
  }
  else
    echo json_encode(array("success" => false, "info" => "消息接收失败"));
}

//给websocket返回认证的用户
if ($action == 'user') {
  echo $_SESSION['weixin'];
}

//上传图片
if ($action == 'upload') {
  $targetFile = $_SESSION['weixin'] . time() . ".jpg";
//  将图片已json形式返回给js处理页面
  echo json_encode(array(
      'success' => move_uploaded_file($_FILES['file']['tmp_name'], '../upload/' . $targetFile) ? true : false,
      'url' => $targetFile,
  ));
}

//发表朋友圈
if ($action == 'publish') {
  $myid = getUser($_SESSION['weixin'])['id'];
  $saying = $_POST['saying'];
//  substr($saying, 31);
  $pics = $_POST['pics'];
  $sth = $pdo->prepare("INSERT INTO circle(u_id,saying,pics,time) VALUE (?,?,?,?)");
  $sth->execute(array($myid, $saying, $pics, date("Y-m-d H:i:s")));
  if ($sth->rowCount())
    echo json_encode(array("success" => true, "info" => "发表成功"));
  else
    echo json_encode(array("success" => false, "info" => "发表失败"));
}

//删除朋友圈
if ($action == 'delcircle') {
  $myid = getUser($_SESSION['weixin'])['id'];
  $cid = $_POST['cid'];
  $sth = $pdo->prepare("DELETE FROM circle WHERE u_id=? and id=?");
  $sth->execute(array($myid, $cid));
  if ($sth->rowCount())
    echo json_encode(array("success" => true, "info" => "删除成功"));
  else
    echo json_encode(array("success" => false, "info" => "删除失败"));
}

//点赞
if ($action == 'like') {
  $myid = getUser($_SESSION['weixin'])['id'];
  $cid = $_POST['cid'];
  $sth = $pdo->prepare("INSERT INTO `like`(u_id,c_id) VALUE (?,?)");
  $sth->execute(array($myid, $cid));
  if ($sth->rowCount())
    echo json_encode(array("success" => true, "info" => "点赞成功"));
  else
    echo json_encode(array("success" => false, "info" => "点赞失败"));
}
if ($action == 'comment') {
  $myid = getUser($_SESSION['weixin'])['id'];
  $saying = $_POST['saying'];
  $cid = $_POST['cid'];
  $sth = $pdo->prepare("INSERT INTO comment(u_id,c_id,saying,time) VALUE (?,?,?,?)");
  $sth->execute(array($myid, $cid, $saying, date("Y-m-d H:i:s")));
  if ($sth->rowCount())
    echo json_encode(array("success" => true, "info" => "评论成功"));
  else
    echo json_encode(array("success" => false, "info" => "评论失败"));
}

//得到朋友圈的内容，评论，点赞信息
if ($action == 'circle') {
  $friend = getFriend($_SESSION['weixin']);;
  $sth = $pdo->prepare("SELECT * FROM circle where u_id=?");
  $cth = $pdo->prepare("SELECT * FROM comment JOIN user on comment.u_id=user.id where c_id=? order by comment.time asc"); //通过动态id 查找评论
  $pth = $pdo->prepare("SELECT *  FROM `like` where c_id=?"); //通过动态id 查找点赞数
  $pth2 = $pdo->prepare("SELECT * FROM `like` where c_id=? and u_id= ?"); //通过动态id 查找点赞数
  $sayings = array();
  $myid = getUser($_SESSION['weixin'])['id'];
  $sth->execute(array($myid));
  if ($sth->rowCount()) {
    $me = getUser($_SESSION['weixin']);
    foreach ($sth->fetchAll() as $value) {
      $value['head'] = $me['head'];
      $value['name'] = $me['nickname'];
      $cth->execute(array($value['id']));
      $value['comments'] = $cth->fetchAll();
      $pth->execute(array($value['id']));
      $value['pnum'] = $pth->rowCount();
      $pth2->execute(array($value['id'], $myid));
      $value['praised'] = $pth2->rowCount() ? true : false;
      $sayings[] = $value;
    }
  }
  foreach ($friend as $f) {
    $sth->execute(array($f['id']));
    if ($sth->rowCount()) {
      foreach ($sth->fetchAll() as $t) {
        $t['head'] = $f['head'];
        $t['name'] = $f['nickname'];
        $cth->execute(array($t['id']));
        $t['comments'] = $cth->fetchAll();
        $pth->execute(array($t['id']));
        $t['pnum'] = $pth->rowCount();
        $pth2->execute(array($t['id'], $myid));
        $t['praised'] = $pth2->rowCount() ? true : false;
        $sayings[] = $t;
      }
    }
  }
  $datetime = array();
  foreach ($sayings as $v) {
    $datetime[] = date("YmdHis", strtotime($v['time']));
  }
  array_multisort($datetime, SORT_DESC, $sayings);

  echo json_encode(array("success" => true, "info" => $sayings));
}
?>