(function ($, Drupal, drupalSettings) {

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

  var uri = drupalSettings.basePath + 'js/share/' + $a.attr('sid') + '/bookmarked';
  $.ajax({
    type: 'GET',
    url: uri,
    dataType: 'json',
    success: function (data) {
      bookmark.setBookmarked(data.bookmarked);
    }
  });

  $a.click(function () { return bookmark.click($(this)); });
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

Drupal.bookmark.prototype.click = function ($a) {
  var bookmark = this;

  if (this.bookmarked) {
    var uri = drupalSettings.basePath + 'js/share/' + this.a.attr('sid') + '/unbookmark';
  }
  else {
    var uri = drupalSettings.basePath + 'js/share/' + this.a.attr('sid') + '/bookmark';
  }
  $.ajax({
    type: 'GET',
    url: uri,
    dataType: 'json',
    success: function (data) {
      bookmark.setBookmarked(data.bookmarked);

      var offset = bookmark.a.offset();
      var bookmarks = $a.parents('article.share').find('span.view_count');
      var bookmark_count = bookmarks.text();
      if (data.bookmarked) {
        bookmark_count++; 
        Drupal.popup_message('<div style="display:block;" class="collect_ok"><span>收藏成功</span></div>', offset.left, offset.top - 40);
      }
      else {
        bookmark_count--;
        Drupal.popup_message('<div style="display:block;" class="collect_del"><span>取消收藏</span></div>', offset.left, offset.top - 40);
      }
      bookmarks.html(bookmark_count);
    }
  });

  return false;
};

})(jQuery, Drupal, drupalSettings);
