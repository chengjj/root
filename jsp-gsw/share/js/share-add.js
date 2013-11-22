(function($, Drupal, drupalSettings){
  Drupal.behaviors.store_add = {
    attach: function (context, settings) {
      //catalog
      $('#share-catalog-parent', context).change(function() {
        var cid = $(this).val();
        $(this).after('<img class="waiting" src="' + drupalSettings.share.theme_path + '/images/waiting.gif">');
        $('#share-catalog').hide();
        $.ajax({
          type: 'GET',
          url: location.protocol + '//' + location.host + drupalSettings.basePath + 'catalog/js/get_share_catalog_children',
          dataType: 'json',
          data: 'cid=' + cid,
          success: function(data) {
            var html = '';
            $.each(data, function(i, item) {
              html += '<option value="' + item.cid + '">' + item.name + '</option>';
            });
            $('.waiting', $('#share-catalog').parent()).remove();
            $('#share-catalog').html(html).show();
          }
        });
      });
      //attch url
      $('#add-goods-submit', context).click(function() {
        var url = $('#add-goods-url').val();
        if (url == '' || url == '请将商品网址粘贴到这里') {
          $('#add-goods-url').focus();
          return;
        }
        var Expression=/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
        var objExp=new RegExp(Expression);
        if (!objExp.test(url)) {
          alert('请输入有效的url');
          $('#add-goods-url').focus();
        } else {
          //TODO 
          //$('#share-mode').removeClass('is-attach-mode');
          //$('#share-mode').addClass('is-loading-mode');
          $(this).after('<img class="waiting" src="' + drupalSettings.share.theme_path + '/images/waiting.gif">');
          $.ajax({
            type: 'POST',
            url: location.protocol + '//' + location.host + drupalSettings.basePath + 'share/js/link_view',
            dataType: 'json',
            data: 'link=' + encodeURIComponent(url),
            success: function(data) {
              if (data.type == 'none') {
                alert(data.description);
              } else {
                //var share_content = $('div.show_pd').html(Drupal.theme('share_object_content', data));
                //Drupal.attachBehaviors(share_content);
                var hidden_data = '<input id = "share-goods-data" type="hidden" data-link-image="' + data.link_image 
                  + '" data-url="' + data.link + '" data-price="' + data.price + '" data-title="'
                  + data.title + '" data-sold-count="' + data.sold_count + '" data-item-id="' + data.item_id + '">';
                var html = '<div class="pic"><img src="' + data.link_image + '"></div>'
                   + '<div class="pic_txt">'
                   + '<h2>' + data.title + '</h2>'
                   + '<p class="price">￥' + data.price +'</p>' + hidden_data + '</div>';
                var share_content = $('div.show_pd').html(html);
                Drupal.attachBehaviors(share_content);
              }
            }
          });
        }
      });
      //share goods submit
      $('#share-goods-submit', context).click(function() {
        if ($('#share-goods-data').length > 0) {
          var goods_data = $('#share-goods-data');
          var link_image = goods_data.attr('data-link-image');
          var url = encodeURIComponent(goods_data.attr('data-url'));
          var price = goods_data.attr('data-price');
          var title = goods_data.attr('data-title');
          var sold_count = goods_data.attr('data-sold-count');
          var item_id = goods_data.attr('data-item-id');
          var cid = $('#share-catalog').val();
          var tags = $('#goods-tag-values').val();
          var description = $('#goods-description').val();
          if (description == '这件分享的商品什么打动了你？') {
            description = '';
          }
          $.ajax({
            type: 'POST',
            url: location.protocol + '//' + location.host + drupalSettings.basePath + 'share/js/submit_share',
            dataType: 'json',
            data: {cid: cid, link_image: link_image, url: url, price: price, title: title, tags: tags, description: description, sold_count: sold_count, item_id: item_id},
            success: function(data) {
              if (data.status) {
                var html = '<div class="share"><p class="note">分享成功！</p><p>'
                  + '<a target="_blank" href="' + data.link + '">去分享的商品页看看 &gt;&gt;</a></p></div>';
                  $('.ui-dialog-title').text('我要分享');
                  $('.pw_con').html(html);
              }
            }
          });
          
        } else {
          alert('请输入要分享商品的url');
          $('#add-goods-url').focus();
        }
      });
      $("#goods-description").keyup(function(){
          //获取文本框数量
          var count = $(this).val().length;
          if (201 > count) {
            var html='还可以输入' + (200-count) + '个字';
            $(".field .left-char").html(html); 
          } else {
              var html = $(this).val();
              html = html.substring(0,200);
              $(this).val(html);
          }
      });
    }
  };

  /*Drupal.theme.prototype.share_object_content = function(data) {
    var html = '<div class="pic"><img src="' + data.link_image + '"></div>'
        + '<div class="pic_txt">'
        + '<h2>' + data.title + '</h2>'
        + '<p class="price">￥' + data.price +'</p></div>';
        alert(html);
    return html;
  };*/
})(jQuery, Drupal, drupalSettings);
