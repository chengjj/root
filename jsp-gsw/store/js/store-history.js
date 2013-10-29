(function ($) {

"use strict";

Drupal.store_history = {
  /**
   * Marks a store as read, store the last read timestamp in client-side storage.
   *
   * @param Number|String storeID
   *   A store ID.
   */
  markAsRead: function (storeID) {
    $.ajax({
      url: Drupal.url('store/history/' + storeID + '/read'),
      type: 'POST',
      dataType: 'json',
      success: function (timestamp) {
        //storage.setItem('Drupal.history.' + currentUserID + '.' + nodeID, timestamp);
      }
    });
  },

}

})(jQuery);
