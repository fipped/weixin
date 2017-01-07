$(document).ready(function(){
	function check(user,pwd){
		if(!user||!pwd){
			alert("请检查输入!");
			return false;
		}
		return true;
	}
	$('input').on('keydown',function(e){
	    if(e.which == 13)
	    	$('#login').click();
	});
	$('#login').click(function(){
		weixin=$("#weixin").val();
		pwd=$("#pwd").val();
		if(check(weixin,pwd)){
			$.ajax({
				url:"includes/do.php?action=login",
				method:'post',
				data:{"weixin":weixin,"pwd":pwd},
				dataType:'json', 
				success:function(result){
					//alert(result.info);
					if(!result.success)
						alert(result.info);
					else
						location.href="wx.php"+"?token="+result.token;
				},
				error:function(jqXHR, textStatus, errorThrown) {
	                alert('出错啦：'+ jqXHR.status + errorThrown);
	            }
			});
		}
	});
	$('#signin').click(function(){
		weixin=$("#weixin").val();
		pwd=$("#pwd").val();
		if(check(weixin,pwd)){
			$.ajax({
				url:"includes/do.php?action=signin",
				method:'post',
				data:{"weixin":weixin,"pwd":pwd},
				dataType:'json', 
				success:function(result){
					alert(result.info);
				},
				error:function(jqXHR, textStatus, errorThrown) {
	                alert('出错啦：'+ jqXHR.status + errorThrown);
	            }
			});
		}
	});
});