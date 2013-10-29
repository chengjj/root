(function($){
  Drupal.behaviors.share_comment = {
    attach: function (context, settings) {
      $('.share-comment-teaser .reply').find('a').click(function() {
         var username = $(this).attr('rel');
         var html = '回复@' + username + ":";
         if ($('iframe.ke-edit-iframe', context).length > 0) {
           kindeditor.html(html);
           kindeditor.focus();
           //$(document.getElementsByTagName('iframe')[0].contentWindow.document.body).html(html);
         } else {
           $("#edit-subject").html(html);
           $("#edit-subject")[0].focus();
         }
      });
    }
  };
})(jQuery);
