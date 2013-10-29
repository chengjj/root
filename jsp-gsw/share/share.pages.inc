<?php

use Drupal\share\Entity\Share;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * page callback: share/add
 */
function share_js($option) {
  switch ($option) {
    case 'add':
      return theme('share_add');
      break;
    // 分析 link 返回 title、summary 等
    case 'link_view':
      return share_js_link_view();
      break;
    case 'submit_share':
      return share_js_submit_share();
      break;
  }
}

/**
 * page callback: share/edit
 */
function share_edit_form($form, &$form_state, $share = NULL) {

}
/**
 * share link view
 */
function share_js_link_view() {
  $url = $_POST['link'];
  if (!preg_match('/http:\/\//i', $url)) {
    $url = 'http://' . $url;
  }

  if (preg_match('/http:\/\/(www\.)?([^\/]*)/i', $url, $matches)) {
    $source = $matches[2];
  }
  if (!in_array($source , array('item.taobao.com', 'detail.tmall.com'))) {
    return new JsonResponse(array('link' => $url, 'source' => $source, 'type' => 'none', 'title' => '', 'description' => '对不起!目前只能支持淘宝', 'link_image' => '')); 
  }

  if ($content = @file_get_contents($url)) {
    foreach($http_response_header as $header) {
      if (substr_compare($header, 'Content-Type', 0, 12, true) == 0) {
        $content_type = $header;
        break;
      }
    }
    preg_match('#Content-Type:\s([^\s;]*)#is', $content_type, $matches);
    $mime = $matches[1];
    switch($mime) {
      case 'text/html':
        $type = SHARE_DATA_TYPE_HTML;
        break;
      case 'image/jpeg':
      case 'image/png':
      case 'image/gif':
        $type = SHARE_DATA_TYPE_IMAGE;
        break;
      case 'text/plain':
        $type = SHARE_DATA_TYPE_TEXT;
        break;
      case 'application/vnd.ms-word':
        $type = SHARE_DATA_TYPE_WORD;
        break;
      case 'application/vnd.ms-excel':
      case 'application/csv':
        $type = SHARE_DATA_TYPE_EXCEL;
        break;
      case 'application/vnd.ms-powerpoint':
        $type = SHARE_DATA_TYPE_POWERPOINT;
        break;
      case 'application/pdf':
        $type = SHARE_DATA_TYPE_PDF;
        break;
      case 'application/zip':
      default:
        $type = SHARE_DATA_TYPE_FILE;
        break;
    }

    if ($type == SHARE_DATA_TYPE_HTML) {
      //调用taobao接口
      $has_load_item = FALSE;
      $pattern = '/id=(\d+)*/is';
      if (preg_match_all($pattern, $url, $matches)) {
        if (isset($matches[1][0])) {
          $num_iid = $matches[1][0];
          if ($item = share_get_taobao_item($num_iid)) {
            $item = (array)$item->item;
            $has_load_item = TRUE;
            $response = array('link' => $item['detail_url'], 'source' => $source, 'type' => $type, 'title' => $item['title'], 'description' => '', 'link_image' => $item['pic_url'], 'price' => $item['price'], 'item_id' => $item['num_iid'], 'sold_count' => 0);
          }
        } 
      }

      if (!$has_load_item) {
        $response = array('link' => $url, 'source' => $source, 'type' => 'none', 'title' => '', 'description' => '对不起!您输入的url不正确,请重新输入', 'link_image' => '');
      }
      //字符串解析
      /*if (preg_match('/charset="?gb2312"?/i', $content)) {
        $content = iconv("GB2312", "UTF-8//IGNORE", $content);
      }
      else if (preg_match('/charset="?gbk"?/i', $content)) {
        $content = iconv("GBK", "UTF-8//IGNORE", $content);
      }

      if (preg_match('/<title>([^<]*)<\/title>/is', $content, $matches)) {
        $title = $matches[1];
      }
      $description = '';
      if (preg_match('/<meta[^>]*name="description"[^>]*>/is', $content, $matches)) {
        $meta = $matches[0];
        if (preg_match('/content="([^"]*)"/', $meta, $matches)) {
          $description = $matches[1];
        }
      }*/
      
      /*if ($source == 'item.taobao.com') {
        $link_image = array();
        $pattern = '/<img[^>]*id="J_ImgBooth"[^>]*src="([^"]*)"/is';
        $count = 0;
        if (preg_match_all($pattern, $content, $matches)) {
          foreach($matches[1] as $key => $match) {
            $image = $matches[1][$key];
            $size = getimagesize($image);
            if ($size[0] > 40) {
              $link_image[] = $image;
              $count ++;
            }
            if($count >= 10) {
              break;
            }
          }
        }

        $pattern = '/<em[^>]*class="tb-rmb-num"*>(\d+?\.\d{2})<\/em>/is';
        $price = 0;
        if (preg_match_all($pattern, $content, $matches)) {
          if (isset($matches[1][0])) {
            $price = $matches[1][0];
          }
        }

        $sold_count = 0;
        //$pattern = '/<em[^>]*class="J_OrdersPaid"*>(\d+?)<\/em>/is';
        //if (preg_match_all($pattern, $content, $matches)) {
        //  drupal_debug($matches);
        //  if (isset($matches[1][0])) {
        //    $sold_count = $matches[1][0];
        //  }
        //}
      } else if ($source == 'detail.tmall.com') {
        //去掉后缀 -tmall.com 天猫
        $title_array = explode('-tmall.com', $title); 
        if ($title_array) {
          $title = $title_array[0];
        }

        $link_image = array();
        $pattern = '/<span[^>]*id="J_ImgBooth"[^>]*src="([^"]*)"/is';
        $count = 0;
        if (preg_match_all($pattern, $content, $matches)) {
          foreach($matches[1] as $key => $match) {
            $image = $matches[1][$key];
            $size = getimagesize($image);
            if ($size[0] > 40) {
              $link_image[] = $image;
              $count ++;
            }
            if($count >= 10) {
              break;
            }
          }
        }

        $pattern = '/<strong[^>]*class="J_originalPrice"*>[^\d]*(\d+\.\d{2})[^\d]*<\/strong>/is';
        $price = 0;
        if (preg_match_all($pattern, $content, $matches)) {
          if (isset($matches[1][0])) {
            $price = $matches[1][0];
          }
        } 
        if (!$price) {
          $pattern = '/"skuMap"/is';
          if (preg_match_all($pattern, $content, $matches)) {
            if (isset($matches[1][0])) {
              $price = $matches[1][0];
            }
          }
        }

        $sold_count = 0;
      }*/
      
      return new JsonResponse($response); 
    }
    else if ($type == SHARE_DATA_TYPE_IMAGE) {
      /*
      $size = getimagesize($url);
      return drupal_json(array('link' => $url, 'source' => $source, 'type' => $type, 'image_height' => $size[1], 'image_width' => $size[0])); */
    }
    else {
      return new JsonResponse(array('link' => $url, 'source' => $source, 'type' => $type)); 
    }
  }
  else {
    return new JsonResponse(array('link' => $url, 'source' => $source, 'type' => 'none', 'title' => '', 'description' => '不能访问该网址，请确认网址是否正确。', 'link_image' => '')); 
  }
}
/**
 * submit share
 */
function share_js_submit_share() {
  $user = \Drupal::currentUser();
  $cid = $_POST['cid'];
  $link_image = $_POST['link_image'];
  $url = urldecode($_POST['url']);
  $item_id = $_POST['item_id'];
  $price = $_POST['price'];
  $title = $_POST['title'];
  $tags = $_POST['tags'];
  $description = $_POST['description'];
  $sold_count = $_POST['sold_count'];

  if (!is_numeric($cid) || !$url || !$price || !$item_id) {
    return new JsonResponse(array('status' => FALSE));
  }
   
  $source = '';
  if (preg_match('/http:\/\/(www\.)?([^\/]*)/i', $url, $matches)) {
    $source = $matches[2];
  }
  $picture = 0;
  if ($link_image) {
    if ($img_data = @file_get_contents($link_image)) {
      $file = file_save_data($img_data);
      $file->setTemporary();
      $file->save();

      $picture = $file->id();
    }
  }

  /*$share = share_save(NULL, array(
    'cid' => $cid,
    'title' => $title,
    'price' => $price,
    'description' => $description,
    'sold_count' => $sold_count,
    'picture' => $file,
    'url' => $url,
    'item_id' => $item_id,
    'source' => $source,
    'uid' => $user->id(),
    'created' => time(),
    'status' => 1,
  ));*/

  $array = array(
    'cid' => $cid,
    'title' => $title,
    'price' => $price,
    'description' => $description,
    'sold_count' => $sold_count,
    'url' => $url,
    'item_id' => $item_id,
    'source' => $source,
    'uid' => $user->id(),
    'created' => time(),
    'status' => 1,
  );

  $share = entity_create('share', $array);
  $share->enforceIsNew();
  $share->save();
  
  if ($picture) {
    $share->setPicture($file);
    $share->save();
  }
  return new JsonResponse(array('status' => TRUE, 'link' => url('share/' . $share->id())));


}

/**
 * get taobao item 
 */
function share_get_taobao_item($num_iid) {
  include_once(drupal_get_path('module', 'share') . '/lib/Taobao/TopSdk.php'); 
  $c = new TopClient;
  $c->appkey = variable_get('taobao_appkey', '21611094');
  $c->secretKey = variable_get('taobao_secret', 'f6d6055607fc0f8f18ac5ef781ad53b7'); 
  $req = new ItemGetRequest;
  $req->setFields("num_iid,title,price,pic_url,detail_url");
  $req->setNumIid($num_iid);
  return $c->execute($req, $sessionKey);
}
