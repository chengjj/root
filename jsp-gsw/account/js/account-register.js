(function($){
  Drupal.behaviors.account_register = {
    attach: function (context, settings) {
      function time(wait) {  
        if (wait == 0) {  
          $('span.time-waiting').text("");
          $('.cf').show();
          wait = 90;  
        } else {  
          if (wait > 0) {
            $('span.time-waiting').text("点击重发(" + wait + ")");
          }
          wait--;  
          setTimeout(function() {  
              time(wait)  
          },  
          1000); 
        }
      }
      $('a.sj_yzm', context).click(function() {
        var phone = $('#edit-phone').val();
        var ab = /^(13[0-9]|15[0|3|6|7|8|9]|18[8|9])\d{8}$/;
        if (phone.length != 11) {
          alert('请输入正确的手机号!');
          return false;
        }
        if (!ab.test(phone)) {
          alert('请输入正确的手机号!');
          $('#edit-phone').focus();
          return false;
        }
        $(this).hide();
        $.ajax({
          type: 'POST',
          url: location.protocol + '//' + location.host + settings.basePath + 'account/js/get_verification_code',
          dataType: 'json',
          data: {phone: phone},
          success: function(data) {
            if (data.status > 0) {
             time(90);
            } else {
              alert(data.error);
            }
          }
        });

        return false;
      });

      $('a.cf').click(function() {
        var phone = $('#edit-phone').val();
        var ab = /^(13[0-9]|15[0|3|6|7|8|9]|18[8|9])\d{8}$/;
        if (phone.length != 11) {
          alert('请输入正确的手机号!');
          return false;
        }
        if (!ab.test(phone)) {
          alert('请输入正确的手机号!');
          $('#edit-phone').focus();
          return false;
        }

        $.ajax({
          type: 'POST',
          url: location.protocol + '//' + location.host + settings.basePath + 'account/js/get_verification_code',
          dataType: 'json',
          data: {phone: phone},
          success: function(data) {
            if (data.status > 0) {
             $(this).hide();
             time(90);
            } else {
              alert(data.error);
            }
          }
        });

        return false;
      });
    }
  };
})(jQuery);
