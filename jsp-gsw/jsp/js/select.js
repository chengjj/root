(function ($) {

"use strict";

Drupal.behaviors.select = {
  attach: function (context, settings) {
    $(context).find('select.js-select').once('js-select', function() {
      new Drupal.jsSelect(this);
    });
    $(context).find('div.search_form input.form-text').once('form-text', function() {
      $(this).val("输入关键词，找到你所想！");
      $(this).focus(function() {
        if ($(this).val() == '输入关键词，找到你所想！') {
          $(this).val("");
        }
      });
      $(this).blur(function() {
        if ($(this).val() == '' || $(this).val().length == 0) {
          $(this).val("输入关键词，找到你所想！");
        }
      });
    });
    /*******************about us help中展开与隐藏***********************************************/
    $(context).find('div.about_help_con ol li a').once('a', function() {
      $(this).click(function() {
        if($(this).attr('class') == 'colse a-processed') {
	  $(this).siblings().attr('style', 'display:block');
	  $(this).attr('class', 'open');
	}
	else{
	  $(this).siblings().attr('style', 'display:none');	
	  $(this).attr('class', 'colse a-processed');
	}
      });
    });
    /*******************about us help中about_help_menu的浮动***********************************************/
    $(context).find('ul.about_help_menu').once('about_help_menu', function() {
      var $this = $(this);
      $(window).scroll(function() { 
        var offsetTop = $(window).scrollTop() + 350 +"px";
	//$(this).css("top", top + "px");
	$this.animate({top : offsetTop },{ duration:300 , queue:false });
      });
    });
    /*******************find password中edit-check-code的清空***********************************************/
    $(context).find('input#edit-check-code').once('edit-check-code', function() {
      $(this).val("");
    });
    /*******************个人中心的商品收藏中全选按钮***********************************************/
    $(context).find('input#edit-all').once('edit-all', function() {
      var $group = $(this).parent().siblings('div.waterfall').find('input.form-checkbox');
      $(this).click(function() {
        if($(this).is(':checked')) {
	  $group.each(function() {
	    this.checked = true;
	  })
	}
	else {
	  $group.each(function() {
	    this.checked = false;
	  });
	}
      });
    });

   $(context).find('div.waterfall').find('input.form-checkbox').once('form-checkbox', function() {
     $(this).click(function() {
       var $all = $(this).parents().find('input#edit-all');
       if($('div.waterfall input.form-checkbox').filter(':checked').length === 6) {
	 $all.each(function() {
	   this.checked = true;
	 });
       }
       else {
         $all.each(function() {
	   this.checked = false;
	 })
       }
     })
   });
    /****************回顶部按钮************************************************/
    $(context).find('div#floating').once('floating', function() {
      $(this).click(function() {
        $('body,html').animate({scrollTop:0},1000);
	return false;
      });
    });
  }
};

Drupal.jsSelect = function(select) {
  var jsSelect = this;
  this.select = $(select);

  this.select.hide();

  var options = '';
  this.select.children().each(function() {
    options += '<li value="' + $(this).attr('value') + '">' + $(this).html() + '</li>';
  });

  var $selected_option = $(select).children('[selected=selected]');
  var selected_text = $selected_option.text();
  var selected_value = $selected_option.attr('value');

  this.select.before('<div class="js-select"><div class = "click_wrapper"><span class = "selected">'+ selected_text +'</span><span class="icon"></span></div><ul class = "list">' + options + '</ul></div>');

  var $list = $(select).siblings('div.js-select').children('ul.list');
  var $click_wrapper = $(select).siblings('div.js-select').children('div.click_wrapper');
  $click_wrapper.click(function () {
		  if ($list.is(":hidden")){
			  $list.show();     
		  } else {
			  $list.hide();
		  }
  });
 
  $list.children('li').click(function() {
        var $selected = $(this).parents('div.js-select').children('div.click_wrapper').children('span.selected');
        $selected.text($(this).text());

        $list.hide();

        $selected_option.removeAttr('selected');
	$selected_option.attr('value', $(this).attr('value'));
        $selected_option.attr('selected', 'selected');
  });
};

})(jQuery);
