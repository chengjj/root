(function ($) {

"use strict";

Drupal.behaviors.hover_ajax = {
  attach: function (context, settings) {
    var db = [];
    var html;

    $(context).find('[hover-target].active').once('hover-target-active', function() {
      var target = $(this).attr('hover-target');
      html = $(target).html();
      $(target).show();
    });

    $(context).find('[hover-target]').once('hover-target', function() {
      if ($(this))
      $(this).hover(function () {
        var target = $(this).attr('hover-target');
        var target_data_uri = $(this).attr('target-data-uri');
        if (!db[target_data_uri]) {
          $.ajax({
            type: 'GET',
            url: target_data_uri,
            dataType: 'html',
            success: function (data) {
              db[target_data_uri] = data;
              $(target).html(data);
              $(target).show();
            }
          });
        }
        else {
          $(target).html(db[target_data_uri]);
          $(target).show();
        }
      });
    });

    $(context).find('[exit-target]').once('exit-target', function() {
      $(this).hover(
        function() {},
        function() {
          var target = $(this).attr('exit-target');
          if (html) {
            $(target).html(html);
          }
          else {
            $(target).hide();
          }
        }
      );
    })
  }
};

})(jQuery);
