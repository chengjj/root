(function ($) {
/**
 * Attaches the store_search_catalog behavior to all required fields.
 */
Drupal.behaviors.store_search_catalog = {
  attach: function (context, settings) {
    $('ul.store-catalog li', context).hover(function() {
       $("div[data-cid]").removeClass('open').hide();
      var cid = $('a', this).attr('data-cid');
      $("div[data-cid='" + cid + "']").addClass('open').show();
     },function() {
     });
    $('div[data-cid]', context).hover(function() {

     }, function() {
       $(this).removeClass('open').hide();
       $('div[data-cid]', context).each(function() {
         if ($(this).attr('data-expand') == 'open') {
           $(this).addClass('open').show();
         }
       });
     });
  }
};
})(jQuery);
