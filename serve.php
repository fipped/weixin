<?php
error_reporting(E_ALL ^ E_NOTICE);
ob_implicit_flush();
include('includes/init.php');
//地址与接口，即创建socket时需要服务器的IP和端口
$sk=new Sock('10.122.7.30',8000);

//对创建的socket循环进行监听，处理数据
$sk->run();
  
//sock类
class Sock{
  public $sockets; //socket的连接池，即client连接进来的socket标志
  public $users;  //所有client连接进来的信息，包括socket、client名字等
  public $master; //socket的resource，即前期初始化socket时返回的socket资源
    
  private $sda=array();  //已接收的数据
  private $slen=array(); //数据总长度
  private $sjen=array(); //接收数据的长度
  private $ar=array();  //加密key
  private $n=array();
    
  public function __construct($address, $port){
    $this->master=$this->WebSocket($address, $port);
    $this->sockets=array($this->master);
  }

  function run(){
    while(true){
      $changes=$this->sockets;//连接池
      $write=$except=NULL;
      socket_select($changes,$write,$except,NULL);
      foreach($changes as $sock){
          //新的client连接进来
          if($sock==$this->master){
              $client=socket_accept($this->master);
              $key=uniqid();

              $this->e("new key:".$key);
              $this->sockets[]=$client; //将新连接进来的socket存进连接池
              $this->users[$key]=array(
                'socket'=>$client, //记录新连接进来client的socket信息
                'shou'=>false    //标志该socket资源没有完成握手
              );
          }else{
              $len=0;
              $buffer='';
              //读取该socket的信息，注意：第二个参数是引用传参即接收数据，第三个参数是接收数据的长度
              do{
                $l=socket_recv($sock,$buf,1000,0);
                $len+=$l;
                $buffer.=$buf;
              }while($l==1000);
              //$k：根据socket在user池里面查找相应的健ID
              $k=$this->search($sock);
      
              //如果接收的信息长度小于7，则该client的socket为断开连接
              if($len<7){
                $this->send2($k);
                continue;
              }
              if(!$this->users[$k]['shou']){
                //如果没有握手，则进行握手处理
                $this->woshou($k,$buffer);
              }else{
                //发送信息
                $buffer = $this->uncode($buffer,$k);
                if($buffer)
                  $this->send($k,$buffer);
              }
          }
      }  
    }  
  } 
  //指定关闭$k对应的socket
  function close($k){
    //断开相应socket
    socket_close($this->users[$k]['socket']);
    //删除相应的user信息
    unset($this->users[$k]);
    //重新定义sockets连接池
    $this->sockets=array($this->master);
    foreach($this->users as $v){
      $this->sockets[]=$v['socket'];
    }
    //输出日志
    $this->e("key:$k close");
  }
  //根据sock在users里面查找相应的$k
  function search($sock){
    foreach ($this->users as $k=>$v){
      if($sock==$v['socket'])
      return $k;
    }
    return false;
  }
  //传相应的IP与端口进行创建socket操作
  function WebSocket($address,$port){
    $server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);//1表示接受所有的数据包
    socket_bind($server, $address, $port);
    socket_listen($server);
    $this->e('Server Started : '.date('Y-m-d H:i:s'));
    $this->e('Listening on  : '.$address.' port '.$port);
    return $server;
  }
  /*
  * 函数说明：对client的请求进行回应，即握手操作
  * @$k clien的socket对应的健，即每个用户有唯一$k并对应socket
  * @$buffer 接收client请求的所有信息
  */
  function woshou($k,$buffer){
    //截取Sec-WebSocket-Key的值并加密，其中$key后面的一部分258EAFA5-E914-47DA-95CA-C5AB0DC85B11字符串应该是固定的
    $buf = substr($buffer,strpos($buffer,'Sec-WebSocket-Key:')+18);
    $key = trim(substr($buf,0,strpos($buf,"\r\n")));
    $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
      
    //按照协议组合信息进行返回
    $new_message = "HTTP/1.1 101 Switching Protocols\r\n";
    $new_message .= "Upgrade: websocket\r\n";
    $new_message .= "Sec-WebSocket-Version: 13\r\n";
    $new_message .= "Connection: Upgrade\r\n";
    $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
    socket_write($this->users[$k]['socket'],$new_message,strlen($new_message));
  
    //对已经握手的client做标志
    $this->users[$k]['shou']=true;
    return true;   
  }
    
  //解码函数
  function uncode($str,$key){
    $mask = array(); 
    $data = ''; 
    $msg = unpack('H*',$str);
    $head = substr($msg[1],0,2); 
    if ($head == '81' && !isset($this->slen[$key])) { 
      $len=substr($msg[1],2,2);
      $len=hexdec($len);//把十六进制的转换为十进制
      if(substr($msg[1],2,2)=='fe'){
        $len=substr($msg[1],4,4);
        $len=hexdec($len);
        $msg[1]=substr($msg[1],4);
      }else if(substr($msg[1],2,2)=='ff'){
        $len=substr($msg[1],4,16);
        $len=hexdec($len);
        $msg[1]=substr($msg[1],16);
      }
      $mask[] = hexdec(substr($msg[1],4,2)); 
      $mask[] = hexdec(substr($msg[1],6,2)); 
      $mask[] = hexdec(substr($msg[1],8,2)); 
      $mask[] = hexdec(substr($msg[1],10,2));
      $s = 12;
      $n=0;
    }else if($this->slen[$key] > 0){
      $len=$this->slen[$key];
      $mask=$this->ar[$key];
      $n=$this->n[$key];
      $s = 0;
    }
      
    $e = strlen($msg[1])-2;
    for ($i=$s; $i<= $e; $i+= 2) { 
      $data .= chr($mask[$n%4]^hexdec(substr($msg[1],$i,2))); 
      $n++; 
    } 
    $dlen=strlen($data);
      
    if($len > 255 && $len > $dlen+intval($this->sjen[$key])){
      $this->ar[$key]=$mask;
      $this->slen[$key]=$len;
      $this->sjen[$key]=$dlen+intval($this->sjen[$key]);
      $this->sda[$key]=$this->sda[$key].$data;
      $this->n[$key]=$n;
      return false;
    }else{
      unset($this->ar[$key],$this->slen[$key],$this->sjen[$key],$this->n[$key]);
      $data=$this->sda[$key].$data;
      unset($this->sda[$key]);
      return $data;
    }    
  }
    
  //与uncode相对
  function code($msg){
    $frame = array(); 
    $frame[0] = '81'; 
    $len = strlen($msg);
    if($len < 126){
      $frame[1] = $len<16?'0'.dechex($len):dechex($len);
    }else if($len < 65025){
      $s=dechex($len);
      $frame[1]='7e'.str_repeat('0',4-strlen($s)).$s;
    }else{
      $s=dechex($len);
      $frame[1]='7f'.str_repeat('0',16-strlen($s)).$s;
    }
    $frame[2] = $this->ord_hex($msg);
    $data = implode('',$frame); 
    return pack("H*", $data); 
  }
    
  function ord_hex($data) { 
    $msg = ''; 
    $l = strlen($data); 
    for ($i= 0; $i<$l; $i++) { 
      $msg .= dechex(ord($data{$i})); 
    } 
    return $msg; 
  }
    
  //服务器发送了信息
  function send($k,$msg){
    //将查询字符串解析到第二个参数变量中，以数组的形式保存如：parse_str("name=Bill&age=60",$arr)
    parse_str($msg,$g);
    $ar=array();
    //上线
    if($g['type']=='online'){

      if($g['token']!=md5($g['weixin'].date('Y-m-d', time())."websocket"))
        //token验证，防止csrf攻击
        $ar['msg']='wrong token!';
      else if($he=$this->getKey($g['weixin']))
        //禁止一个微信号再次连接
        $ar['msg']='same weixin!'; 

      if($ar['msg']){
        //返回错误信息并关闭该socket
        $ar['type']='forbidden';
        $str=$this->code(json_encode($ar));
        socket_write($this->users[$he]['socket'],$str,strlen($str));
        if(!$he)$he=$k;
        $this->e("close :".$he." for ".$ar['msg']);
        $this->close($he);
        return;
      }

      //正常上线
      $this->users[$k]['name']=$g['weixin'];
      $ar['type']='online';
      $key='all';
      $this->e("online:".$g['weixin']);
    }else if($g['type']=='add'){
      //加好友
      $ar['type']='newadd';
      $key=$this->getKey($g['weixin']);
    }else if($g['type']=='send'){
      //发消息
      $ar['type']='chat';
      $ar['msg']=$g['msg'];
      $key=$this->getKey($g['weixin']);
    }else if($g['type']=='reject'){
      //拒绝好友
      $ar['type']='alert';
      $ar['msg']="对方拒绝了您的请求";
      $key=$this->getKey($g['weixin']);
    }else if($g['type']=='accept'){
      //接收好友
      $ar['type']='alert';
      $ar['msg']="对方同意了您的请求";
      $key=$this->getKey($g['weixin']);
    }else if($g['type']=='delete'){
      //删除好友
      $ar['type']='alert';
      $ar['msg']="对方删除了与您的好友关系";
      $key=$this->getKey($g['weixin']);
    }
    $ar['weixin']=$this->users[$k]['name'];
    //对方在线则推送信息
    if($key)
      $this->send1($k,$ar,$key);
  }

  //对新加入的client推送已经在线的client
  function getusers(){
    $ar=array();
    foreach($this->users as $k=>$v){
      $ar[]=array('code'=>$k,'weixin'=>$v['name']);
    }
    return $ar;
  }
  //返回name对应的k
  function getKey($name){
    foreach($this->users as $k=>$v)
      if($v['name']==$name)
        return $k;
    return null;
  }
  //$k 我的socketID $key他的 socketID 查找相应的client进行消息推送，即指定client进行发送
  function send1($k,$ar,$key='all'){
    $ar['time']=date('Y-m-d H:i:s');
    //对发送信息进行编码处理
    $str = $this->code(json_encode($ar));
    //面对大家即所有在线者发送信息
    if($key=='all'){
      $users=$this->users;
      //上线
      if($ar['type']=='online'){
        $ar['users']=$this->getusers();//取出所有在线者，用于更新在线信息
        $str1 = $this->code(json_encode($ar)); 
        //给刚上线的人发送在线列表
        socket_write($users[$k]['socket'],$str1,strlen($str1));
        //上面已经对client自己单独发送的，后面就无需再次发送，故unset
        unset($users[$k]);
      }
      //除了新client外，对其他client进行发送信息。数据量大时，就要考虑延时等问题了
      foreach($users as $v){
        socket_write($v['socket'],$str,strlen($str));
      }
    }else{
      //单独对个人发送信息，即双方聊天
      socket_write($this->users[$key]['socket'],$str,strlen($str));
    }
  }
    
  //用户下线了向其它client推送信息
  function send2($k){
    $this->e("offline:".$this->users[$k]['name']);
    $ar['type']='offline';
    $ar['weixin']=$this->users[$k]['name'];
    $this->close($k);
    $this->send1(false,$ar,'all');
  }

  //记录日志
  function e($str){
    //$path=dirname(__FILE__).'/log.txt';
    $str=$str."\n";
    //error_log($str,3,$path);
    //编码处理
    echo iconv('utf-8','gbk//IGNORE',$str);
  }
}
?>