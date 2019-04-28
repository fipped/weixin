<?php 
session_start();

#管理员账号，密码
define('ADMIN_ID','admin');
define('ADMIN_PASSWORD','123456');
//define('PICSNUM',10);
//网站根目录
define('ROOT_PATH',substr(dirname(__FILE__),0,-8));
define('WX',true);

//数据库连接
define('DB_HOST','localhost');
define('DB_NAME','weixin');
define('DB_USER','root');
define('DB_PWD' ,'');
date_default_timezone_set("Asia/Shanghai");
//引入函数库
require(ROOT_PATH."includes/function.php");
//连接数据库
pdo_connect();
