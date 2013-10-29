(function($){
  Drupal.behaviors.adv_block_share_image_title_price_1cols = {
    attach: function (context, settings) {
      $('ol.share-image-title-price-1cols li', context).hover(function() {
         $(this).siblings('.open').removeClass('open');
         $(this).addClass('open');
        },
       function() {
       });
    }
  };
})(jQuery);
