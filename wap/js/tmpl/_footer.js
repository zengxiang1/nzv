﻿$(function (){
    var memberHtml = '<a class="btn mr5" href="'+WapSiteUrl+'/tmpl/member/member.html?act=member">个人中心</a> <a class="btn mr5" href="'+WapSiteUrl+'//tmpl/member/login.html">我有帐号，登录</a>';
    var act = GetQueryString("act");
    if(act && act == "member"){
        memberHtml = '<a class="btn mr5" id="logoutbtn" href="javascript:void(0);">注销账号</a>';
    }
    var tmpl = '<div class="footer">'
        +'<div class="footer-top">'
            +'<div class="footer-tleft">'+ memberHtml +'</div>'
            +'<a href="javascript:void(0);"class="gotop">'
                +'<span class="gotop-icon"></span>'
                +'<p>回顶部</p>'
            +'</a>'
        +'</div>'
        +'<div class="footer-content">'
            +'<p class="link">'
                +'<a href="'+SiteUrl+'" class="standard">电脑版</a>'
                +'<a href="'+IOSSiteUrl+'" class="standard">苹果客户端</a> '
		+'  <a href="'+AndroidSiteUrl+'"> 安卓客户端</a>'
            +'</p>'
            +'<p class="copyright">'
                +'版权所有 2015 © 广东客商网络科技有限公司'
            +'</p>'
        +'</div>'
    +'</div>';

//var _hmt = _hmt || [];
//(function() {
  //var hm = document.createElement("script");
  //hm.src = "//hm.baidu.com/hm.js?77a4445b7a9e2499b351ed9ee7e2107f";
  //var s = document.getElementsByTagName("script")[0];
  //s.parentNode.insertBefore(hm, s);
//})();

	var render = template.compile(tmpl);
	var html = render();
	$("#footer").html(html);
    //回到顶部
    $(".gotop").click(function (){
        $(window).scrollTop(0);
    });
	
    var key = getcookie('key');
	$('#logoutbtn').click(function(){
		var username = getcookie('username');
		var key = getcookie('key');
		var client = 'wap';
		$.ajax({
			type:'get',
			url:ApiUrl+'/index.php?act=logout',
			data:{username:username,key:key,client:client},
			success:function(result){
				if(result){
					delCookie('username');
					delCookie('key');
					location.href = WapSiteUrl+'/tmpl/member/login.html';
				}
			}
		});
	});
	
	$(".weibo").click(function(){location.href=ApiUrl+"/api.php?act=connect&op=get_sina_oauth2"});
	$(".qq").click(function(){location.href=ApiUrl+"/api.php?act=toqq"})
});