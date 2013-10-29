(function ($) {

"use strict";

Drupal.share_history = {
  /**
   * Marks a share as read, store the last read timestamp in client-side storage.
   *
   * @param Number|String shareID
   *   A share ID.
   */
  markAsRead: function (shareID) {
    $.ajax({
      url: Drupal.url('share/history/' + shareID + '/read'),
      type: 'POST',
      dataType: 'json',
      success: function (timestamp) {
        //storage.setItem('Drupal.history.' + currentUserID + '.' + nodeID, timestamp);
      }
    });
  },

}

})(jQuery);
