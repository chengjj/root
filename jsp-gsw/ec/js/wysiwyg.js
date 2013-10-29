var kindeditor;
(function ($, Drupal, drupalSettings) {
Drupal.behaviors.wysiwyg = {
  attach: function (context, settings) {
    $('textarea.wysiwyg', context).once('wysiwyg', function() {
      KindEditor.ready(function(K) {
        kindeditor = K.create('textarea[name="' + $(self).attr('name') + '"]', {
		      cssPath : settings.basePath + 'sites/all/libraries/kindeditor/plugins/code/prettify.css',
          uploadJson : settings.basePath + 'sites/all/libraries/kindeditor/php/upload_json.php',
          fileManagerJson : settings.basePath + 'sites/all/libraries/kindeditor/php/file_manager_json.php',
          /*items: ['bold', 'fontsize', 'forecolor', '|', 'image', 'emoticons', 'link', 'unlink', '|', 'source']*/
          items: ['emoticons']
        });
      });
  	});
  	$('.ke-statusbar-right-icon').once(function(){
  	  $(this).hide();
  	});
	}
};
})(jQuery, Drupal, drupalSettings);
