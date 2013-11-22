(function ($, Drupal, drupalSettings) {

"use strict";

/**
 * Attaches the follow behavior to all required fields.
 */
Drupal.behaviors.follow = {
  attach: function (context, settings) {
    $(context).find('a.follow').once('follow', function () {
      new Drupal.follow($(this));
    });
  }
};

/**
 * An Follow object.
 */
Drupal.follow = function($a) {
  var follow = this;
  this.a = $a;

  var uri = drupalSettings.basePath + 'js/user/' + $a.attr('uid') + '/followed/';
  $.ajax({
    type: 'GET',
    url: uri,
    dataType: 'json',
    success: function (data) {
      follow.setFollowed(data.followed);
    }
  });

  $a.click(function () { return follow.click(); });
};

Drupal.follow.prototype.setFollowed = function (followed) {
  this.followed = followed;
  if (followed) {
    this.a.html('已关注');
    this.a.attr('title', '点击取消关注');
  }
  else {
    this.a.html('+关注');
    this.a.attr('title', '点击关注该会员');
  }
};

Drupal.follow.prototype.click = function () {
  var follow = this;

  if (this.followed) {
    var uri = drupalSettings.basePath + 'js/user/' + this.a.attr('uid') + '/unfollow';
  }
  else {
    var uri = drupalSettings.basePath + 'js/user/' + this.a.attr('uid') + '/follow';
  }
  $.ajax({
    type: 'GET',
    url: uri,
    dataType: 'json',
    success: function(data) {
      follow.setFollowed(data.followed);
    }
  });

  return false;
};

})(jQuery, Drupal, drupalSettings);
