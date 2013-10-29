(function ($) {

Drupal.behaviors.ajax_dialog = {
  attach: function (context, settings) {
    if ($("#ajax-dialog-container").length == 0) {
      $('body').append('<div id="ajax-dialog-container" style="display:none;"></div>');
    }
    $("#ajax-dialog-container", context).dialog({
      autoOpen: false,
      modal: true,
      resizable: false,
      dialogClass: "no-close"
      /*buttons: [{text: "OK",click: function() { $( this ).dialog( "close" );}}]*/
    });
    
    $('.ajax-dialog', context).click(function () {
      var url = $(this).attr('data-url');
      var title = $(this).attr('data-title');
      var width = $(this).attr('dialog-width');
      var height = $(this).attr('dialog-height');
      
      if (title) {
        $('#ajax-dialog-container').dialog('option', 'title', title);
      }
      if (width) {
        $('#ajax-dialog-container').dialog('option', 'width', width);
      }
      else {
        $('#ajax-dialog-container').dialog('option', 'width', "auto");
      }
      if (height) {
        $('#ajax-dialog-container').dialog('option', 'height', height);
      }
      else {
        $('#ajax-dialog-container').dialog('option', 'height', "auto");
      }
      $('#ajax-dialog-container').html('<div class="ajax-dialog-ajax-loader"></div>');
      var type = 'GET';
      if ($(this).attr('dialog-type') != undefined) {
        type = $(this).attr('dialog-type');
      }
      $.ajax({
        type: type,
        url: url,
        success: function(data) {
          $('#ajax-dialog-container').html(data[0].data);
          Drupal.attachBehaviors($('#ajax-dialog-container'));
        }
      });
      $('.ui-button-icon-primary').html('<a href="javascript:void(0)">X</a>');
      $('#ajax-dialog-container').dialog('open');
      return false;
    });
    
    $('.ui-button-icon-primary', context).once('close-dialog', function() {
      $('#ajax-dialog-container').dialog('close');
    });

    // used by ajax form
    $('#please-close-dialog', context).once('close-dialog', function() {
      $('#ajax-dialog-container').dialog('close');
    });
    
    // used by event ajax form @see js_event_form_submit_js on event.pages.inc
    $('#event-close-dialog', context).once('close-dialog', function() {
    	location.reload();
      $('#ajax-dialog-container').dialog('close');
    });
  }
};

})(jQuery);
