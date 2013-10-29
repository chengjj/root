(function ($) {
  Drupal.behaviors.account = {
    attach: function (context, settings) {
      $('#edit-name').blur(function(){
        var val = $(this).val();
        var reg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/; //验证邮箱的正则表达式
        if(reg.test(val)){
          $('#edit-mail').val(val);
        }
      });
    }
  };
})(jQuery);
