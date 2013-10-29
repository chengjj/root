(function ($) {

"use strict";

Drupal.popup_message = function (html, left, top) {
  var popup = document.createElement('div');
  popup.id = 'popup-message';
  document.body.appendChild(popup);

  $('#popup-message').html(html);
  $('#popup-message').attr('style', 'position:absolute;left:' + left + 'px;top:' + top + 'px;');
  $('#popup-message').fadeOut(1000, function() {
    $(this).remove();
  });
}

})(jQuery);
