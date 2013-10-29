(function($){
  Drupal.behaviors.adv_block_coupon_image_title_desc = {
    attach: function (context, settings) {
      $('ol.coupon-image-title-desc li', context).hover(function() {
         $(this).siblings('.open').removeClass('open');
         $(this).addClass('open');
        },
       function() {
       });
    }
  };
})(jQuery);
