/**
 * usage: <tag hover-class="xxx">
 */
(function ($) {

"use strict";

Drupal.behaviors.hover_class = {
  attach: function (context, settings) {
    $(context).find('[hover-class]').once('hover-class', function() {
      $(this).hover(function () {
        var cls = $(this).attr('hover-class');
        $(this).siblings('.'+cls).removeClass(cls);
        $(this).addClass(cls);
      });
    });
  }
};

})(jQuery);
