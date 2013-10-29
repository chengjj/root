(function ($) {

"use strict";

Drupal.behaviors.guike_user = {
  attach: function (context, settings) {
    $(context).find('.tab-share a').once('tab-share', function() {
      $(this).click(function () {
        $('.tab-share').addClass('seclect');
        $('div.browse_salelist').show();
        $('.tab-store').removeClass('seclect');
        $('div.browse_splist').hide();
        return false;
      });
    });
    $(context).find('.tab-store a').once('tab-store', function() {
      $(this).click(function () {
        $('.tab-share').removeClass('seclect');
        $('div.browse_salelist').hide();
        $('.tab-store').addClass('seclect');
        $('div.browse_splist').show();
        return false;
      });
    });
  }
}

})(jQuery);
