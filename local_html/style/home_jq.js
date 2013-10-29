// JavaScript Document


/**按钮提示start**/
$(function(){
		$(".btn_style1").hover(function(){
			if($(this).attr("name") === "login"){
					$("#log_tip").show("fast");
				}
			else{
					$("#reg_tip").show("fast");
				}			
		
			},
		function(){
			$(".btn_tips").hide("normal");
		});
		
	});
/**按钮提示end**/	
	
/**登录点击start**/	
$(function(){
		$(".btn_style1").click(function(){
			if($(this).attr("name") === "login"){
			$('#dan_logo').animate({left: '0'}, 2000,function(){
					$('#login_panel').show("normal");
				});
        	return false; 
			}
			});
	});

/**登录点击end**/

/**注册点击start**/	
$(function(){
		$(".btn_style1").click(function(){
				if($(this).attr("name") === "register"){
					$('#reg_box').css("display","block");
					}
			});
	});

/**注册点击end**/	

/**注册框关闭start**/	
$(function(){
		$("#close_icon").click(function(){
				$('#reg_box').css("display","none");
			});
	});
/**注册框关闭end**/	









