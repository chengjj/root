(function ($) {

"use strict";

var $positive = ['环境非常好', '好吃', '商品质量非常好', '售后服务很好', '价格便宜', '还想来', '优惠多多'];
var $negative = ['环境太差了', '难吃', '商品质量有问题', '售后服务不好', '价格贵得离谱', '再也不会来第二次了', '根本没有优惠'];

Drupal.behaviors.guike_user = {
  attach: function (context, settings) {
    $(context).find('.tab-comments a').once('tab-comments', function() {
      $(this).click(function () {
        $('.tab-comments').addClass('seclect');
        $('div.user_pj_con').show();
        $('div.pj_number').show();
        $('.tab-comment-add').removeClass('seclect');
        $('div#wypj').hide();
        return false;
      });
    });


      $(context).find('div.user_pj_con').once('user_pj_con', function() {
        var error = '暂时还无用户评价';
	if($(this).text().indexOf(error) >= 0) {
	  $(this).addClass('p14h100');
	}
      });


    $(context).find('.tab-comment-add a[href=]').once('tab-commtab-comment-addent-add', function() {
      $(this).click(function () {
        $('.tab-comments').removeClass('seclect');
        $('div.user_pj_con').hide();
        $('div.pj_number').hide();
        $('.tab-comment-add').addClass('seclect');
        $('div#wypj').show();
	var $sgt = '';
	$(context).find('div#edit-rank').once('edit-rank', function() {
		var checked = $(":radio:checked").val();
		//console.log(checked);
		if(checked == 1) {
		  var i = 0;
		  $.each($positive, function() {
		    $sgt +='<span>' + $positive[i] + '</span>';
		    i++;
		  });
		}
		else {
		  var i = 0;
		  $.each($negative, function() {
		    $sgt +='<span>' + $negative[i] + '</span>';
		    i++;
		  });
		  
		}
	   $('div#edit-rank').after('<div class = "suggestions">' + $sgt + '</div>');
        });
       $(context).find('div.suggestions').once('suggestions', function () {
         $(this).children().click(function() {
	     var suggest = $(this).text();	 
	     $('textarea#edit-subject').text(suggest);
	  });
        });

        return false;
      });
    });


    $(context).find("input#edit-rank-1").once("edit-rank-1", function() {
      $(this).click(function () {
	var i = 0;      
        $("div.suggestions").children().each(function () {
	  $(this).text($positive[i])
	  i++;	
	});
      });
      $("input#edit-rank-0").click(function () {
	 var i = 0;     
         $("div.suggestions").children().each(function () {
	  $(this).text($negative[i])
	  i++;	
	});
      });
    });
   
    

    $(context).find('.annew a').once('folder', function() {
      if ($(this).hasClass('down')) {
        $(this).parent().parent().siblings('.sale_con').hide();
      }
      $(this).click(function () {
        if ($(this).hasClass('up')) {
          $(this).html('详情');
          $(this).removeClass('up');
          $(this).addClass('down');
          $(this).parent().parent().siblings('.sale_con').hide();
        }
        else {
          $(this).html('收起');
          $(this).removeClass('down');
          $(this).addClass('up');
          $(this).parent().parent().siblings('.sale_con').show();
        }
        return false;
      });
    });

    /*$(context).find('.store-picture').once('store-picture', function() {
      $(this).hover(
        function () {
          $('#defaultImg').attr('src', $(this).attr('src'));
          $('.sjpic_big').show();
        },
        function () {
          $('.sjpic_big').hide();
        });
    });*/
  }
}

 

})(jQuery);