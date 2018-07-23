$(function () {
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
  function showMsg(wxbody, msg, isme, uhead,fhead) {
    fa = wxbody.find('.talk');
    var dia;
    if (isme) {
      dia = $('.mydia.hide').clone(true);//克隆对话框
      dia.find('.head').attr('src', uhead);
    } else {
      dia = $('.hedia.hide').clone(true, true);
      dia.find('.head').attr('src', fhead);
    }
    var log = dia.find('.log');
    var bd = fa.find('.dialog');

    log.html(html_encode(msg));//编码html标签符号
    dia.removeClass('hide');
    bd.append(dia);//显示对话框
    dia.animate({height: log.height() + 20}, 100);//设置父元素高度
    setTimeout(function () {
      bd.scrollTop(999999)
    }, 100);//滚动条到最下面
  };
  function chat(uid, fid) {
    var wxbody = $('#wxboard .panel-body');
    var s = $('.talk.hide').clone(true);
    wxbody.html(s);
    s.removeClass('hide');
    wxbody.css('padding', 0);
    //读取聊天记录
    $.ajax({
      url: "includes/do.php?action=log",
      method: 'post',
      dataType: 'json',
      data: {"id": fid,"uid": uid},
      success: function (result) {
        if (result.success) {
          s = result.rec;
          uhead = "img/head"+result.uhead+".jpg";
          fhead = "img/head"+result.fhead+".jpg";
          alert(uhead+' '+fhead);
          for (i = s.length - 1; i >= 0; i--)
          {
            showMsg(wxbody, s[i]['content'], s[i]['u_id'] == uid, uhead,fhead);
          }

        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        alert('出错啦：' + jqXHR.status + errorThrown);
      }
    });
  }
  $("#btn_search").click(function () {
    arr=$("[name='select']");
    var flag = -1;//是否选择用户
    for(var i=0;i<arr.length;i++)
    {
      if(arr[i].checked)
        {flag = i;}
    }
    if(flag != -1)
    {
      // alert('选择'+$(arr[flag]).attr('uid'));
      $('#friendlist').modal('show');
      $('#chathistory').modal('hide');
      uid=$(arr[flag]).attr('uid');
      $.ajax({
        url: "includes/do.php?action=getFriends",
        method: 'get',
        data: {uid: uid},
        dataType: 'json',
        success: function (result) {
          if (result.success) {
            bd = $("#friendsTable");
            bd.html('');
            for( u in result.info) {
              user = result.info[u];
              bd.append('            <tr>\n' +
                  '              <td><img onclick="return false;" src="img/head' + user['head'] + '.jpg" width="40px" alt=""></td>\n' +
                  '              <td>'+user['weixin']+'</td>\n' +
                  '              <td>'+user['nickname']+'</td>\n' +
                  '              <td><img width=\'10px\' src="img/'+user['sex']+'.png"></td>\n' +
                  '              <td>'+user['geqian']+'</td>\n' +
                  '              <td>\n' +
                  '                <button type="button" class="btn btn-default rightSize btn_chathistory" fid='+user['f_id']+'>\n' +
                  '                  <span class="glyphicon glyphicon-search" aria-hidden="true"></span> 聊天记录\n' +
                  '                </button>\n' +
                  '              </td>\n' +
                  '            </tr>');
            }
            $(".btn_chathistory").click(function(){
              fid=$(this).attr('fid');
              // alert('选择聊天记录uid= '+uid+' fid='+ fid);
              $('#friendlist').modal('hide');
              $('#chathistory').modal('show');
              chat(uid,fid);
            })
          } else
            alert(result.info);
        },
        error: function (jqXHR, textStatus, errorThrown) {
          alert('出错啦：' + jqXHR.status + errorThrown);
        }
      });
    }
    else
      alert("您未选中任何用户");
  })



  document.getElementById("btn_add").onclick = function(){
    $('#addUser').modal('show');
  }

  document.getElementById("btn_closeHis").onclick = function(){
    $('#friendlist').modal('show');
    $('#chathistory').modal('hide');
  }

  $("#btn_edit").click(function () {
    arr=$("[name='select']");
    var flag = -1;//是否选择用户
    for(var i=0;i<arr.length;i++)
    {
      if(arr[i].checked)
      {flag = i;}
    }
    if(flag != -1)
    {
      uid=$(arr[flag]).attr('uid');
      $.ajax({
        url: "includes/do.php?action=getUser",
        method: 'get',
        data: {uid: uid},
        dataType: 'json',
        success: function (result) {
          if (result.success) {
            $('#personalInfo').modal('show');
            data=result.info;
            $('#personalInfo').find('#weixin').val(data['weixin']);
            heads=$('#personalInfo').find('#head').html("");

            for (var i = 0; i <= 10; i++) {
              // alert(data['sex']+'  ');
              heads.append("<option data-content=\"<span><img width=25px src='img/head"+i+".jpg'>头像"+i+"</span>\" value="+i+(i == data["head"] ? " selected" : "")+">头像"+i+"</option>");
            }

            $('#personalInfo').find('#nickname').val(data['nickname']);
            // alert(data['sex']+'  '+$('#personalInfo').find('#boy').length);
            if(data['sex']=='boy')
            {
              $('#personalInfo').find('#boy').prop("checked",true);
              $('#personalInfo').find('#girl').removeProp("checked");
            }
            else
            {
              $('#personalInfo').find('#boy').removeProp("checked");
              $('#personalInfo').find('#girl').prop("checked",true);

            }
            $('#personalInfo').find('#geqian').val(data['geqian']);
            $('#personalInfo').find('#password').val('');
          } else
            alert(result.info);
        },
        error: function (jqXHR, textStatus, errorThrown) {
          alert('出错啦：' + jqXHR.status + errorThrown);
        }
      });
    }
    else {
      alert("您未选中任何用户");
    }
  })
  $('#saveBtn').click(function () {
    nick = $('#personalInfo').find("#nickname").val();
    gq = $('#personalInfo').find("#geqian").val();
    pw = $('#personalInfo').find("#password").val();
    sex = $('#personalInfo').find("input[name='sex']:checked").val();
    hd = $('#personalInfo').find("#head").val();
    weixin = $('#personalInfo').find("#weixin").val();
    if(!gq||!weixin||!nick){
      alert("请检查输入!");
      return ;
    }
    // uid = $("[name='select']:checked");
    // alert(nick+' '+gq+' '+pw+' '+sex+' '+hd+' '+weixin);
    $.ajax({
      url: "includes/do.php?action=set",
      method: 'post',
      data: {"nick": nick, "gq": gq, "pw": pw, "sex": sex, "hd": hd,"weixin": weixin},
      dataType: 'json',
      success: function (result) {
        alert(result.info);
        $('#setting').modal('hide');
        location.reload();
      },
      error: function (jqXHR, textStatus, errorThrown) {
        alert('出错啦：' + jqXHR.status + errorThrown);
      }
    });
  });
  $('#addBtn').click(function () {
    weixin = $('#addUser').find("#weixin").val();
    pwd = $('#addUser').find("#password").val();

    // });
    if(!weixin||!pwd){
      alert("请检查输入!");
      return ;
    }
    $.ajax({
      url:"includes/do.php?action=signin",
      method:'post',
      data:{"weixin":weixin,"pwd":pwd},
      dataType:'json',
      success:function(result){
        alert(result.info);
        location.reload();
      },
      error:function(jqXHR, textStatus, errorThrown) {
        alert('出错啦：'+ jqXHR.status + errorThrown);
      }
    });
  });
  document.getElementById("btn_delete").onclick = function(){
    arr=$("[name='select']");
    var flag = -1;//是否选择用户
    for(var i=0;i<arr.length;i++)
    {
      if(arr[i].checked)
      {flag = i;}
    }
    if(flag != -1) {
      if (confirm("确认删除该用户？")) {
        uid=$(arr[flag]).attr('uid');
        // alert(uid);
        $.ajax({
          url: "includes/do.php?action=delUser",
          method: "get",
          dataType: 'json',
          data: {"uid": uid},
          success: function (result) {
            alert(result.info);
            if(result.success)
              location.reload();
          },
          error: function (jqXHR, textStatus, errorThrown) {
            alert('出错啦：' + jqXHR.status + errorThrown);
          }
        });
      }
    }
    else
      alert("您未选中任何用户");
  }
  /*$(document).on('show.bs.modal', '.modal', function (event) {
      var zIndex = 1040 + (10 * $('.modal:visible').length);
      $(this).css('z-index', zIndex);
     }*/
  $("#closeHis").click(function(){
    $('#chathistory').modal('hide');
    $('#friendlist').modal('show');
  });
});
