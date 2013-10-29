(function ($) {

"use strict";

/**
 * Attaches the follow behavior to all required fields.
 */
Drupal.behaviors.store_follow = {
  attach: function (context, settings) {
    $(context).find('a.store_follow').once('store_follow', function () {
      new Drupal.store_follow($(this));
    });
  }
};

/**
 * An Follow object.
 */
Drupal.store_follow = function($a) {
  var follow = this;
  this.a = $a;

  var uri = '/js/store/' + $a.attr('sid') + '/followed/';
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

Drupal.store_follow.prototype.setFollowed = function (followed) {
  this.followed = followed;
  if (followed) {
    this.a.html('已关注');
  }
  else {
    this.a.html('+关注');
  }
};

Drupal.store_follow.prototype.click = function () {
  var follow = this;

  if (this.followed) {
    var uri = '/js/store/' + this.a.attr('sid') + '/unfollow';
  }
  else {
    var uri = '/js/store/' + this.a.attr('sid') + '/follow';
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

})(jQuery);
