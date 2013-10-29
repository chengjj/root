(function($){
  Drupal.behaviors.admin_share_list = {
    attach: function (context, settings) {
      // 加载时间控件
      $( "#edit-start" ).datepicker({
        showOtherMonths: true,
        selectOtherMonths: true
      });
      $( "#edit-expire" ).datepicker({
        showOtherMonths: true,
        selectOtherMonths: true
      });
    }
  };
})(jQuery);
