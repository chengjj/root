(function($){
  Drupal.behaviors.admin_statistic_form = {
    attach: function (context, settings) {
      // 加载时间控件
      $( "#edit-statistic-date-start" ).datepicker({
        showOtherMonths: true,
        selectOtherMonths: true
      });
      $( "#edit-statistic-date-expire" ).datepicker({
        showOtherMonths: true,
        selectOtherMonths: true
      });
      $( "#edit-advert-date-start" ).datepicker({
        showOtherMonths: true,
        selectOtherMonths: true
      });
      $( "#edit-advert-date-expire" ).datepicker({
        showOtherMonths: true,
        selectOtherMonths: true
      });
    }
  };
})(jQuery);
