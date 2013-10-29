(function ($) {

"use strict";

/**
 * Attaches the bookmark behavior to all required fields.
 */
Drupal.behaviors.coupon_bookmark = {
  attach: function (context, settings) {
    $(context).find('a.coupon_bookmark').once('coupon_bookmark', function () {
      new Drupal.coupon_bookmark($(this));
    });
  }
};

/**
 * An Follow object.
 */
Drupal.coupon_bookmark = function($a) {
  var bookmark = this;
  this.a = $a;

  var uri = '/js/coupon/' + $a.attr('cid') + '/bookmarked/';
  $.ajax({
    type: 'GET',
    url: uri,
    dataType: 'json',
    success: function (data) {
      bookmark.setBookmarked(data.bookmarked);
    }
  });

  $a.click(function () { return bookmark.click(); });
};

Drupal.coupon_bookmark.prototype.setBookmarked = function (bookmarked) {
  this.bookmarked = bookmarked;
  if (bookmarked) {
    this.a.html('已收藏');
    this.a.addClass('bookmarked');
  }
  else {
    this.a.html('+收藏');
    this.a.removeClass('bookmarked');
  }
};

Drupal.coupon_bookmark.prototype.click = function () {
  var bookmark = this;
  if (this.bookmarked) {
    var uri = '/js/coupon/' + this.a.attr('cid') + '/unbookmark';
  }
  else {
    var uri = '/js/coupon/' + this.a.attr('cid') + '/bookmark';
  }
  $.ajax({
    type: 'GET',
    url: uri,
    dataType: 'json',
    success: function(data) {
      bookmark.setBookmarked(data.bookmarked);
      var offset = bookmark.a.offset();
      if (data.bookmarked) {
        Drupal.popup_message('<div style="display:block;" class="collect_ok"><span>收藏成功</span></div>', offset.left, offset.top - 40);
      }
      else {
        Drupal.popup_message('<div style="display:block;" class="collect_del"><span>取消收藏</span></div>', offset.left, offset.top - 40);
      }
    }
  });

  return false;
};

})(jQuery);
