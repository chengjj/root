/**
 * usage: <input type="text" hint-text="xxx">
 */
(function ($) {

"use strict";

Drupal.behaviors.inputTextHint = {
  attach: function (context, settings) {
    $(context).find('input[hint-text]').once('input-text-hint', function() {
      var hint = $(this).attr('hint-text');
      $(this).blur();
      $(this).val(hint);
      $(this).focus(function () {
        if ($(this).val() == hint) {
          $(this).val('');
        }
      });
      $(this).blur(function () {
        if ($(this).val() == '') {
          $(this).val(hint);
        }
      });
    });
  }
};

})(jQuery);
