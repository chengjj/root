(function ($) {

"use strict";

Drupal.behaviors.guike_user = {
  attach: function (context, settings) {
    $(context).find('.tab-comments a').once('tab-comments', function() {
      $(this).click(function () {
        $('.tab-comments').addClass('seclect');
        $('div.user_pj_con').show();
        $('div.pj_number').show();
        $('.tab-comment-add').removeClass('seclect');
        $('div#wypj').hide();
        return false;
      });
    });
    $(context).find('.tab-comment-add a[href=]').once('tab-comment-add', function() {
      $(this).click(function () {
        $('.tab-comments').removeClass('seclect');
        $('div.user_pj_con').hide();
        $('div.pj_number').hide();
        $('.tab-comment-add').addClass('seclect');
        $('div#wypj').show();
        return false;
      });
    });

    $(context).find('.annew a').once('folder', function() {
      if ($(this).hasClass('down')) {
        $(this).parent().parent().siblings('.sale_con').hide();
      }
      $(this).click(function () {
        if ($(this).hasClass('up')) {
          $(this).html('详情');
          $(this).removeClass('up');
          $(this).addClass('down');
          $(this).parent().parent().siblings('.sale_con').hide();
        }
        else {
          $(this).html('收起');
          $(this).removeClass('down');
          $(this).addClass('up');
          $(this).parent().parent().siblings('.sale_con').show();
        }
        return false;
      });
    });

    $(context).find('.store-picture').once('store-picture', function() {
      $(this).hover(
        function () {
          $('#defaultImg').attr('src', $(this).attr('src'));
          $('.sjpic_big').show();
        },
        function () {
          $('.sjpic_big').hide();
        });
    });
  }
}

})(jQuery);
