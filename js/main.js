//html转义符
function html_encode(str) {
  var s = "";
  if (str.length == 0) return "";
  s = str.replace(/&/g, "&gt;");
  s = s.replace(/</g, "&lt;");
  s = s.replace(/>/g, "&gt;");
  s = s.replace(/ /g, "&nbsp;");
  s = s.replace(/\'/g, "&#39;");
  s = s.replace(/\"/g, "&quot;");
  s = s.replace(/\n/g, "<br>");
  return s;
};

//查找微信号
function findWx(weixin) {
  $.ajax({
    url: "includes/do.php?action=find",
    method: 'get',
    data: {"weixin": weixin},
    dataType: 'json',
    success: function (result) {
      if (result.success) {
        bd = $("#userinfo .modal-body");
        bd.html("\
					<div class='row'>\
			      	<div class='col-xs-3 col-sm-2'>\
			      		<img src='img/head" + result.user.head + ".jpg' width='80px'>\
			       	</div>\
			      	<div class='col-xs-9 col-sm-10'>\
			  			<h4>" + result.user.nickname + " <span style='font-size:12px'>\
			  		「" + result.user.weixin + "」</span>\
			  		<img width='16px' src=" + (result.user.sex == "boy" ? "'img/boy.png'" : "'img/girl.png'") + "></h4>\
					<p>\
						" + result.user.geqian + "\
					</p>\
			      	</div>\
      				</div>");
        $('#userinfo').modal('show');
        $('#addf').modal('hide');
        $('#addfriend').attr('data-weixin', $("#findWeixin").val());
        $("#findWeixin").val("");
      } else
        alert(result.info);
    },
    error: function (jqXHR, textStatus, errorThrown) {
      alert('出错啦：' + jqXHR.status + errorThrown);
    }
  });
};

//获取好友请求
function getReq() {
  $.ajax({
    url: "includes/do.php?action=get",
    method: 'get',
    dataType: 'json',
    success: function (result) {
      if (result.success) {
        //发送好友请求的人
        $('.msg').html(result.info + "<span class='badge'>" + result.num + "</span>");
        $('.msg').removeClass("hide");
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      alert('不能获取好友请求：' + jqXHR.status + errorThrown);
    }
  });
}

//显示消息在对话框
function showMsg(wxbody, msg, isme, head) {

  fa = wxbody.find('.talk');
  var dia;
  if (isme) {
    dia = $('.mydia.hide').clone(true);//克隆对话框
  } else {
    dia = $('.hedia.hide').clone(true, true);
    dia.find('.head').attr('src', head);
  }
  var log = dia.find('.log');
  var bd = fa.find('.dialog');

  log.html(html_encode(msg));//编码html标签符号
  dia.removeClass('hide');
  bd.append(dia);//显示对话框
  // alert(log.height());
  dia.animate({height: log.height() + 20}, 100);//设置父元素高度
  setTimeout(function () {
    bd.scrollTop(999999)
  }, 100);//滚动条到最下面
};

$(document).ready(function () {
  var wxtitle = $('#wxboard .panel-title');
  var wxbody = $('#wxboard .panel-body');
  //加载页面后就获取请求
  getReq();

//点击头像查看对方信息
  $('.hedia .head').on('click', function () {
    $.ajax({
      url: "includes/do.php?action=find",
      method: 'get',
      data: {"weixin": $(".talk").attr('data-weixin')},
      dataType: 'json',
      success: function (result) {
        if (result.success) {
          bd = $("#userinfo .modal-body");
          bd.html("\
					<div class='row'>\
			      	<div class='col-xs-3 col-sm-2'>\
			      		<img src='img/head" + result.user.head + ".jpg' width='80px'>\
			       	</div>\
			      	<div class='col-xs-9 col-sm-10'>\
			  			<h4>" + result.user.nickname + " <span style='font-size:12px'>\
			  		「" + result.user.weixin + "」</span>\
			  		<img width='16px' src=" + (result.user.sex == "boy" ? "'img/boy.png'" : "'img/girl.png'") + "></h4>\
					<p>\
						" + result.user.geqian + "\
					</p>\
			      	</div>\
      				</div>");
          $('.modal-footer').hide();
          $('#userinfo').modal('show');
        } else
          alert(result.info);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        alert('出错啦：' + jqXHR.status + errorThrown);
      }
    });
  });
//点击自己的头像，设置
  $('#myhead').click(function () {
    $('#setting').modal('show');
  });
//保存设置
  $('#saveBtn').click(function () {
    nick = $("#nickname").val();
    gq = $("#geqian").val();
    pw = $("#password").val();
    sex = $("input[name='sex']:checked").val();
    hd = $("#head").val();
    $.ajax({
      url: "includes/do.php?action=set",
      method: 'post',
      data: {"nick": nick, "gq": gq, "pw": pw, "sex": sex, "hd": hd},
      dataType: 'json',
      success: function (result) {
        $('#setting').modal('hide');
        location.reload();
      },
      error: function (jqXHR, textStatus, errorThrown) {
        alert('出错啦：' + jqXHR.status + errorThrown);
      }
    });
  });
//获取请求
  $('.msg').click(function () {
    wxtitle.html("新的好友请求");
    location.href = "#wxboard";
    wxbody.html("");
    $.ajax({
      url: "includes/do.php?action=get2",
      method: 'get',
      dataType: 'json',
      success: function (result) {
        if (result.success) {
          for (u in result.users) {
            he = result.users[u];
            var $s = $('.hide.haoyouqq').clone(true);
            $s.find('.head').attr('src', "img/head" + he['head'] + ".jpg");
            $s.find('.nickname').html(he['nickname']);
            $s.find('.weixin').html(he['weixin']);
            $s.find('.sex').attr('src', (he['sex'] == 'boy' ? "img/boy.png" : "img/girl.png"))
            $s.find('.gq').html(he['geqian']);
            $s.removeClass("hide");
            wxbody.append($s);
          }
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        alert('不能获取好友请求：' + jqXHR.status + errorThrown);
      }
    });
  });
//添加
  $('#addfriend').on('click', function () {
    weixin = $(this).attr('data-weixin');
    $.ajax({
      url: "includes/do.php?action=add",
      method: 'get',
      data: {"friend": weixin},
      dataType: 'json',
      success: function (result) {
        //if(result.success)
        alert(result.info);
        so.send("type=add&weixin=" + weixin);
        location.reload();
      },
      error: function (jqXHR, textStatus, errorThrown) {
        alert('出错啦：' + jqXHR.status + errorThrown);
      }
    });
  });

//接受
  $('.okbtn').on("click", function () {
    weixin = $(this).parent().parent().find('.weixin').html();
    $.ajax({
      url: "includes/do.php?action=accept",
      method: "get",
      dataType: 'json',
      data: {"weixin": weixin},
      success: function (result) {
        alert(result.info);
        so.send("type=accept&weixin=" + weixin);
        location.reload();
      },
      error: function (jqXHR, textStatus, errorThrown) {
        alert('出错啦：' + jqXHR.status + errorThrown);
      }
    });
  })

//拒绝
  $('.nobtn').click(function () {
    weixin = $(this).parent().parent().find('.weixin').html();
    $.ajax({
      url: "includes/do.php?action=reject",
      method: "get",
      dataType: 'json',
      data: {"weixin": weixin},
      success: function (result) {
        alert(result.info);
        so.send("type=reject&weixin=" + weixin);
        location.reload();
      },
      error: function (jqXHR, textStatus, errorThrown) {
        alert('出错啦：' + jqXHR.status + errorThrown);
      }
    });
  })

//删除好友
  $('.friends .close').click(function () {
    if (confirm("确认删除该好友？")) {
      delid = $(this).parent().attr('data-id');
      weixin = $(this).parent().attr('data-weixin');
      $.ajax({
        url: "includes/do.php?action=del",
        method: "get",
        dataType: 'json',
        data: {"id": delid},
        success: function (result) {
          alert(result.info);
          so.send("type=delete&weixin=" + weixin);
          location.reload();
        },
        error: function (jqXHR, textStatus, errorThrown) {
          alert('出错啦：' + jqXHR.status + errorThrown);
        }
      });
    }
  })

//点击好友聊天
  $('.friends>img').click(function () {
    chat($(this).parent().find('.nickname').html(),
        $(this).parent().attr('data-id'),
        $(this).parent().attr('data-weixin'));
  });
  $('.friends>.nickname').click(function () {
    chat($(this).html(),
        $(this).parent().attr('data-id'),
        $(this).parent().attr('data-weixin'));
  });

  function chat(friend, id, weixin) {
    location.href = "#wxboard";
    wxtitle.html("与 " + friend + " 聊天");
    var s = $('.talk.hide').clone(true);
    s.attr('data-id', id);
    s.attr('data-weixin', weixin);
    s.removeClass('hide');
    wxbody.html(s);
    wxbody.css('padding', 0);
    getlog();
  }

//发送消息
  function sendMsg(msg) {
    fa = wxbody.find('.talk');
    words = msg.val();
    wx = fa.attr('data-weixin');
    id = fa.attr('data-id');
    $.ajax({
      url: "includes/do.php?action=send",
      method: 'post',
      dataType: 'json',
      data: {"id": id, "msg": words},
      success: function (result) {
        if (result.success) {
          msg.val("");// 清空输入框
          showMsg(wxbody, words, 1);
          if (!$("li.friends[data-id=" + id + "]>.head").is('offline'))
            so.send("type=send&msg=" + words + "&weixin=" + wx);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        alert('出错啦：' + jqXHR.status + errorThrown);
      }
    });
  };

//点击发送信息
  $('.talk .btn').on('click', function () {
    sendMsg($(this).parent().parent().find('textarea'));
  });

//绑定快捷键Enter+ctrl发送
  $('textarea').on('keydown', function (e) {
    if (e.which == 13 && e.ctrlKey)
      sendMsg($(this));
  });

//读取聊天记录
  function getlog() {
    fa = wxbody.find('.talk');
    id = fa.attr('data-id');
    $.ajax({
      url: "includes/do.php?action=log",
      method: 'post',
      dataType: 'json',
      data: {"id": id},
      success: function (result) {
        if (result.success) {
          s = result.rec;
          mn = $("li.friends[data-id='" + id + "']>.msgnum");
          mn.html(0);
          mn.addClass("hide");
          head = $('.friends[data-id="' + id + '"]').find('.head').attr('src');
          for (i = s.length - 1; i >= 0; i--)
            showMsg(wxbody, s[i]['content'], s[i]['u_id'] != id, head);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        alert('出错啦：' + jqXHR.status + errorThrown);
      }
    });
  }
});


