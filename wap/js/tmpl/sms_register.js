
//获取电话号码发送验证码
$(function() {
	$('#sms_send').click(function() {
        if($("#phone").val().length == 11){
            var ajaxurl =ApiUrl+'/index.php?act=connect_sms&op=get_captcha&nchash=1&type=1';//1为登录
            ajaxurl +='&phone='+$('#phone').val()+'&client=wap';
			$.ajax({
				type: "GET",
				url: ajaxurl,
				async: false,
				success: function(rs){
                    if(rs == 'true') {
                    	$("#sms_info").html('短信验证码已发出！').show();
                    	  $("#user_info").css("display","");
                    } else {
                       $("#sms_info").html(rs).show();
                     
                    }
			    }
			});
    	}
	});
	$('#sms_register').click(function() {

		if($('#password').val() != $('#password_confirm').val()){
			 $("#sms_info").html("请检查验证码是否正确！").show();
			return;
		}
        if(($("#phone").val().length ==11)&&($("#sms_captcha").val().length = 6)){
            var ajaxurl =ApiUrl+'/index.php?act=connect_sms&op=register';
			$.ajax({
				type: "POST",
				url: ajaxurl,
				async: false,
				data: {
				register_phone:$('#phone').val(),
				client:'wap',
				register_captcha:$('#sms_captcha').val(),
				member_name:$('#username').val(),
				password : $('#password').val(),
				email : $('#email').val()
				},
				success: function(rs){
                    if(rs == 'true') {
                    	var m = /^(https?:\/\/.+)\/wap/i.exec(location.href)
                    	window.location.href = m[1]+'/wap/tmpl/member/member.html?act=member'
                    } else {
                       $("#sms_info").html(rs).show();
                    }
			    }
			});
    	}
	});
});