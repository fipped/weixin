
/*=======================================
* websocket，实现全双工通信(full-duplex)，达成即时通讯的功能
* 以及消息推送（好友添加请求）
*=======================================*/
	function connect(wx,token){
		//创建socket
	    ws=new WebSocket('ws://10.122.7.30:8000');
		ws.onopen=function(){
		   //状态为1证明握手成功，然后把client自定义的名字发送过去
		  if(ws.readyState==1){
		     //握手成功后对服务器发送信息
		     console.log('type=online&weixin='+wx+'&token='+token);
		   	ws.send('type=online&weixin='+wx+'&token='+token);
		  }
		}
		//错误返回信息函数
		ws.onerror = function(){
		  console.log("error");
		};
		//监听服务器端推送的消息
		ws.onmessage = function (msg){
			//将返回的数据声明到da中
		  	eval('var da='+msg.data);
			//出现非法访问
			if(da.type=='forbidden'){
				alert(da.msg);
				location.href="includes/do.php?action=logout";
			}else if(da.type=='online'){
				//获取上线用户
				for(u in da.users){
					head=$("li.friends[data-weixin='"+da.users[u]['weixin']+"']>.head");
					if(head.length)
						head.removeClass('offline');
				}
				head=$("li.friends[data-weixin='"+da.weixin+"']>.head");
					if(head.length)
						head.removeClass('offline');
			}else if(da.type=='chat'){
				var wxbody=$('#wxboard .panel-body');
				//我正在与之聊天的weixin
				var wx=wxbody.find('.talk').attr('data-weixin');
				if(da.weixin!=wx){
					//如果收到的消息不来自他则显示红色的消息数量
					var mn=$("li.friends[data-weixin='"+da.weixin+"']>.msgnum");
					mn.html(Number(mn.html())+1);
					if(mn.hasClass('hide'))mn.removeClass('hide');
				}else{
					//否则渲染消息在窗口中
					head=$('.friends[data-weixin="'+da.weixin+'"]').find('.head').attr('src');
					showMsg(wxbody,da.msg,0,head);
				}
			}else if(da.type=='newadd'){
				//新的好友请求
				getReq();
			}else if(da.type=='offline'){
				//有用户下线
				head=$("li.friends[data-weixin='"+da.weixin+"']>.head");
					if(head.length)
						head.addClass('offline');
			}else if(da.type=='alert'){
				//有系统消息
				alert(da.weixin+" 回应："+da.msg);
				location.reload();
			}
		}
		//断开WebSocket连接
		ws.onclose = function(){
		  ws = false;
		}
		return ws;
	}