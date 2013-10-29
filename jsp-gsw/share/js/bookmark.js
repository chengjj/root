(function ($) {

"use strict";

Drupal.behaviors.bookmark = {
  attach: function (context, settings) {
    $(context).find('a.bookmark').once('bookmark', function () {
      new Drupal.bookmark($(this));
    });
  }
};

/**
 * An Bookmark object.
 */
Drupal.bookmark = function($a) {
  var bookmark = this;
  this.a = $a;

  var uri = '/js/share/' + $a.attr('sid') + '/bookmarked';
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

Drupal.bookmark.prototype.setBookmarked = function (bookmarked) {
  this.bookmarked = bookmarked;
  if (bookmarked) {
    this.a.html('喜欢');
    this.a.addClass('like');
  }
  else {
    this.a.html('喜欢');
    this.a.removeClass('like');
  }
};

Drupal.bookmark.prototype.click = function () {
  var bookmark = this;

  if (this.bookmarked) {
    var uri = '/js/share/' + this.a.attr('sid') + '/unbookmark';
  }
  else {
    var uri = '/js/share/' + this.a.attr('sid') + '/bookmark';
  }
  $.ajax({
    type: 'GET',
    url: uri,
    dataType: 'json',
    success: function (data) {
      bookmark.setBookmarked(data.bookmarked);

      var offset = bookmark.a.offset();
      var bookmarks=$('#share-like-bookmark').find('span').text();
      if (data.bookmarked) {
        bookmarks++; 
        Drupal.popup_message('<div style="display:block;" class="collect_ok"><span>收藏成功</span></div>', offset.left, offset.top - 40);
      }
      else {
        bookmarks--;
        Drupal.popup_message('<div style="display:block;" class="collect_del"><span>取消收藏</span></div>', offset.left, offset.top - 40);
      }
      $('#share-like-bookmark').find('span').html(bookmarks);
    }
  });

  return false;
};

})(jQuery);
