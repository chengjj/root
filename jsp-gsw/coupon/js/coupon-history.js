(function ($) {

"use strict";

Drupal.coupon_history = {
  /**
   * Marks a coupon as read, store the last read timestamp in client-side storage.
   *
   * @param Number|String couponID
   *   A coupon ID.
   */
  markAsRead: function (couponID) {
    $.ajax({
      url: Drupal.url('coupon/history/' + couponID + '/read'),
      type: 'POST',
      dataType: 'json',
      success: function (timestamp) {
        //storage.setItem('Drupal.history.' + currentUserID + '.' + nodeID, timestamp);
      }
    });
  },

}

})(jQuery);
