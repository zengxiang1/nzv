$(function() {
	$('#sms_send').click(function() {
        if($("#phone").val().length == 11){
            var ajaxurl =ApiUrl+'/index.php?act=connect_sms&op=get_captcha&nchash=1&type=2';//2为登录
            ajaxurl +='&phone='+$('#phone').val()+'&client=wap';
			$.ajax({
				type: "GET",
				url: ajaxurl,
				async: false,
				success: function(rs){
                    if(rs == 'true') {
                    	$("#sms_info").html('短信动态码已发出').show();
                    } else {
                       $("#sms_info").html(rs).show();
                    }
			    }
			});
    	}
	});
	$('#sms_login').click(function() {
        if($("#phone").val().length == 11){
            var ajaxurl =ApiUrl+'/index.php?act=connect_sms&op=login';//2为登录
      
			$.ajax({
				type: "POST",
				url: ajaxurl,
				async: false,
				data: {phone:$('#phone').val(),client:'wap',sms_captcha:$('#sms_captcha').val()},
				success: function(rs){
                    if(rs == 'true') {
                    	var m = /^(https?:\/\/.+)\/wap/i.exec(location.href)
                    	window.location.href = m[1]+'/wap/tmpl/member/member.html?act=member'
                    } else {
                       $("#sms_info").html(rs).show();
                    }
			    }
			});
    	} else {
			$("#sms_info").html('手机号+动态码，必须填写！').show();
		}
	});
});