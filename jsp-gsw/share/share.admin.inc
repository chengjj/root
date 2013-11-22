<?php

/**
 * page callback for: admin/share
 */
function share_admin_share_list($form, &$form_state) {
  $request=\Drupal::request();
  //TODO add share_catalog
  drupal_add_js(drupal_get_path('module', 'ec') . '/js/jquery-ui.custom.js', 'file');
  drupal_add_css(drupal_get_path('module', 'ec') . '/css/jquery-ui.css', 'file');
  drupal_add_js(drupal_get_path('module', 'ec') . '/js/jquery.ui.datepicker-zh-CN.js', 'file');

  drupal_add_js(drupal_get_path('module', 'share') . '/js/admin-share-list.js');
  global $base_url;

  $keyword = check_plain($request->query->get('keyword',''));
  $start = check_plain($request->query->get('start',''));
  $expire = check_plain($request->query->get('expire',''));

  $header = array(
    'title' => array('data' => '商品名称', 'field' => 'title'),
    'url' => array('data' => '商品url',),
    'image_url' => array('data' => '商品缩略图'),
    'user_name' => array('data' => '发布者',),
    'created' => array('data' => '创建时间', 'field' => 'created', 'sort' => 'desc'),
    'status' => array('data' => '状态', 'field' => 'status'),
    'operations' => array('data' => '操作',)
  );

  $query = db_select('shares', 's')
    ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
    ->extend('Drupal\Core\Database\Query\TableSortExtender')
    ->limit(30)
    ->fields('s', array('sid'))
    ->condition('title', '', '<>');
    
  if ($keyword) {
    $query->condition(db_or()
      ->condition('title', '%' . db_like($keyword) . '%', 'LIKE')
    );
  }
  if ($start && strtotime($start)) {
    $query->condition('created', strtotime($start), '>=');
  }
  if ($expire && strtotime($expire)) {
    $query->condition('created', strtotime($expire), '<');
  }
  $sids = $query->orderByHeader($header)->execute()->fetchCol(); 
  
  $shares = share_load_multiple($sids);
  $options = array();
  foreach ($shares as $share) {
    $user_name = '';
    if ($account = $share->getAuthor()) {
      $user_name = $account->getUsername();
    }
    $url = $url_title = urldecode($share->url->value);
    if (mb_strlen($url) > 40) {
      $url_title = truncate_utf8($url, 40);
    }
    $picture = $share->getPicture();
    $options[$share->id()] = array(
      'title' => mb_strlen($share->label(), 'UTF8') > 20 ? truncate_utf8(check_markup($share->label()), 20) . '...' : $share->label(),
      'url' => $share->url->value ? "<a href='$url'>$url_title</a>" : "",
      'image_url' => ($picture && $picture->id()) ? theme('image', array('uri' => $picture->getFileUri(), 'height' => '80px', 'width' => '80px')) : '无', 
      'user_name' => $user_name,
      'created' => date('Y-m-d', $share->created->value),
      'status' => $share->status->value ? '已发布' : '未发布',
      'operations' => l('编辑', 'share/' .$share->id() . '/edit') . ' | ' . l('删除', 'admin/share/delete/' . $share->id()),
    ); 
  }
   $form['filter']['keyword'] = array(
    '#title' => '关键字',
    '#type' => 'textfield',
    '#default_value' => $request->query->get('keyword',''),
  );
  $form['filter']['start'] = array(
    '#title' => '开始时间',
    '#type' => 'textfield',
    '#attributes' => array('autocomplete' => 'off'), 
    '#default_value' => ($start && strtotime($start)) ? $start : '',
    '#description' => '<font color="red"><b>Tips</b></font>: 使用商品发布时间查询',
  );  

  $form['filter']['expire'] = array(
    '#title' => '截止时间',
    '#type' => 'textfield',
    '#attributes' => array('autocomplete' => 'off'), 
    '#default_value' => ($expire && strtotime($expire)) ? $expire : '',
    '#description' => '<font color="red"><b>Tips</b></font>: 使用商品发布时间查询',
  );
  $form['filter']['search'] = array(
    '#type' => 'submit',
    '#value' => '搜索',
  );
  $form['shares'] = array(
    '#type' => 'tableselect',
    '#header' => $header,
    '#options' => $options,
    '#empty' => '没有分享商品信息',
  );

  if ($ids = adv_block_select_blocks('share')) {
    $form['actions']['recommend'] = array(
      '#type' => 'submit',
      '#value' => '推荐到',
    );
    $options = array();
    foreach ($ids as $bid) {
      $adv_block = adv_block_load($bid);
      $options[$adv_block->bid] = $adv_block->title;
    }
    
    $form['actions']['recommend_shares'] = array(
      '#type' => 'select',
      '#options' => $options,
    );
  }

  $form['pager'] = array(
    '#theme' => 'pager',
  );
  return $form;
}

function share_admin_share_list_validate($form, &$form_state) {
  $shares = isset($form_state['input']['shares']) ? $form_state['input']['shares'] : array();
  $sids = array();
  foreach ($shares as $sid => $share) {
    if (!empty($share)) {
      $sids[] = $sid;
    }
  }
  $op = $form_state['values']['op'];
  if (in_array($op, array('推荐到')) && !count($sids)) {
    form_set_error('shares', '请选择商品信息');
  } else {
    $form_state['sids'] = $sids;
  }
}

function share_admin_share_list_submit($form, &$form_state) {
  $sids = $form_state['sids'];
	$op = $form_state['values']['op'];
  switch ($op) {
    case '搜索':
      $keyword = $form_state['values']['keyword'];
      $start = $form_state['values']['start'];
      $expire = $form_state['values']['expire'];

      $form_state['redirect'] = array('admin/store', array(
        'query' => array(
            'keyword' => $keyword,
            'start' => $start,
            'expire' => $expire,
          ),
        ),
      );
      break;
    case '推荐到':
      $bid = $form_state['values']['recommend_shares'];
      foreach ($sids as $sid) {
        $iid = db_select('adv_block_items', 'i')
    			->fields('i', array('iid'))
    			->condition('bid', $bid)
    			->condition('type', 'share')
    			->condition('entity_id', $sid)
    			->execute()
    			->fetchObject();
        if (!$iid) {
          adv_block_item_save(NULL, array(
            'bid' => $bid,
            'entity_id' => $sid,
            'type' => 'share',
            'status' => 1,
            )
          );
        }
      }
      drupal_set_message('促销信息推荐成功');
      break;
  }
}

function share_admin_edit_form_catalog_js($form, &$form_state) {
  return $form['template_wrapper'];
}

/**
 * page callback: admin/share/delete/%share
 */
function share_admin_delete_form($form, &$form_state, $share) {
  $form['sid'] = array('#type' => 'value', '#value' => $share->id());
  $form['title'] = array('#type' => 'value', '#value' => $share->label());
  return confirm_form($form,
    t('Are you sure you want to delete %title?', array('%title' => $share->label())),
    'share/' . $share->id() . '/edit',
    t('This action cannot be undone.'),
    t('Delete'),
    t('Cancel')
  );
}

function share_admin_delete_form_submit($form, &$form_state) {
  if ($form_state['values']['confirm']) {
    share_delete($form_state['values']['sid']);
    drupal_set_message(t('商品 %title has been deleted.', array('%title' => $form_state['values']['title'])));
  }
  $form_state['redirect'] = 'admin/share/list';
}

/**
 * page callback: admin/taobao/shares
 */
function share_admin_get_form_taobao_form($form, &$form_state) {
  $form['title'] = array('#markup' => '<p>按分类抓取淘宝商品</p>');
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '抓取',
    '#suffix' => '<span style="color:red">抓取过程比较缓慢，请耐心等待，不要重复点击抓取按钮!</span>',
  );
  return $form;
}

function share_admin_get_form_taobao_form_submit($form, &$form_state) {
  //TODO 获取抓取清单
  share_get_shares_from_taobao();
  drupal_set_message('数据抓取成功!');
}

/**
 * get shares from taobao by share_catalog
 */
function share_get_shares_from_taobao() {
  set_time_limit(0);
  $user = \Drupal::currentUser();
  $pSize = 1000;
  //_input_charset=utf-8&json=on&callback=json
  $catalogs = array(
    '服装服饰' => array(
      '女装' => 'http://list.taobao.com/itemlist/nvzhuang2011a.htm?cat=16',
      '男装' => 'http://list.taobao.com/itemlist/nanzhuang2011a.htm?cat=30',
      '童装' => 'http://list.taobao.com/itemlist/market/sport2011a.htm?cat=50016756',
      '运动装' => 'http://list.taobao.com/itemlist/market/sport2011a.htm?cat=50016756',
      '内衣' => 'http://list.taobao.com/itemlist/market/neiyi2011a.htm?cat=1625',
      '服装配饰' => 'http://list.taobao.com/itemlist/market/peishi2011a.htm?cat=50010404',
    ),
    '鞋子箱包' => array(
      '女鞋' => 'http://list.taobao.com/itemlist/nvxieshichang2011a.htm?cat=50006843',
      '男鞋' => 'http://list.taobao.com/itemlist/nanxie2011a.htm?cat=50016853',
      '女包' => 'http://list.taobao.com/itemlist/market/nvbao2011a.htm?cat=50006842',
      '男包' => 'http://list.taobao.com/itemlist/market/nanbao2011a.htm?cat=50072686',
      '功能箱包' => 'http://list.taobao.com/itemlist/market/nvbao2011a.htm?cat=50072688', 
      '户外运动用品' => 'http://list.taobao.com/itemlist/market/sport2011a.htm?cat=2203',
    ),
    '食品饮料' => array(
      '休闲零食' => 'http://list.taobao.com/itemlist/market/food2011.htm?cat=50002766',
      '营养品' => 'http://list.taobao.com/itemlist/market/food2011.htm?cat=50008825', 
      '茶/饮料' => 'http://list.taobao.com/itemlist/market/food2011.htm?cat=50103359',
      '有机食品' => 'http://list.taobao.com/itemlist/market/nongye1.htm?cat=50107919&at=4673%2C11138',
    ),
    '生活家居' => array(
      '住宅家具' => 'http://list.taobao.com/itemlist/jiaju.htm?cat=50008164',
      '家居饰品' => 'http://list.taobao.com/itemlist/jiaju.htm?cat=50065206',
      '家纺布艺' => 'http://list.taobao.com/itemlist/jiaju.htm?cat=50065205',
      '五金电工' => 'http://list.taobao.com/itemlist/jiaju.htm?cat=50065355',
      '收纳整理' => 'http://list.taobao.com/itemlist/market/baihuoshichang.htm?cat=50051688',
      '居家日用' => 'http://list.taobao.com/itemlist/market/baihuoshichang.htm?cat=21', 
      '餐饮用具' => 'http://list.taobao.com/itemlist/market/baihuoshichang.htm?cat=50035867', 
      '洗护清洁' => 'http://list.taobao.com/itemlist/market/baihuoshichang.htm?cat=50035458',
    ),
    '数码家电' => array(
      '手机' => 'http://list.taobao.com/itemlist/shuma.htm?cat=1512',
      '相机/DV' => 'http://list.taobao.com/itemlist/shuma.htm?cat=14',
      '笔记本' => 'http://list.taobao.com/itemlist/shuma.htm?cat=1101',
      '平板电脑' => 'http://list.taobao.com/itemlist/shuma.htm?cat=50047310', 
      '电脑周边' => 'http://list.taobao.com/itemlist/shuma.htm?cat=11',
      '数码配件' => 'http://list.taobao.com/itemlist/shuma.htm?cat=50008090',
      '大家电' => 'http://list.taobao.com/itemlist/market/jiadiano.htm?cat=50035182', 
      '厨房电器' => 'http://list.taobao.com/itemlist/market/jiadiano.htm?cat=50018930',
      '生活电器' => 'http://list.taobao.com/itemlist/market/jiadiano.htm?cat=50018957',
      '影音电器' => 'http://list.taobao.com/itemlist/market/jiadiano.htm?cat=50018908',
      '护理按摩' => 'http://list.taobao.com/itemlist/market/jiadiano.htm?cat=50049318',
    ),
    '母婴亲子' => array(
      '孕妇用品' => 'http://list.taobao.com/itemlist/market/baby.htm?cat=50067081', 
      '新生儿' => 'http://list.taobao.com/itemlist/market/baby.htm?cat=50097448', 
      '宝宝食品' => 'http://list.taobao.com/itemlist/market/baby.htm?cat=35', 
      '宝宝用品' => 'http://list.taobao.com/itemlist/market/baby.htm?cat=50006004',
      '儿童玩具' => 'http://list.taobao.com/itemlist/market/baby.htm?cat=50005998',
    ),
    '个护化妆' => array(
      '美容护肤' => 'http://list.taobao.com/itemlist/beauty.htm?cat=1801',
      '彩妆香水' => 'http://list.taobao.com/itemlist/beauty.htm?cat=50010788', 
      '美发护发' => 'http://list.taobao.com/itemlist/beauty.htm?cat=50071436',
    ),
    '珠宝饰品' => array(
      '珠宝钻石' => 'http://list.taobao.com/itemlist/market/sp.htm?cat=50015926',
      '品牌手表' => 'http://list.taobao.com/itemlist/market/sp.htm?cat=50005700', 
      '流行饰品' => 'http://list.taobao.com/itemlist/market/sp.htm?cat=1075', 
      '其他配饰' => 'http://list.taobao.com/itemlist/market/sp.htm?cat=28',
    ),
  );
  //$pSize = 1000;
  //_input_charset=utf-8&json=on&callback=json
  $share_items = array();
  $item_ids = db_query('SELECT item_id FROM {shares}')->fetchCol();
  foreach ($catalogs as $key => $taobao_catalogs) {
    foreach ($taobao_catalogs as $catalog => $url) {
      $cid = db_query('SELECT cid FROM {share_catalog} WHERE name=:name', array(':name' => $catalog))->fetchField();
      $share_items[$cid] = array();
      $url .= '&pSize=300&_input_charset=utf-8&json=on&callback=json';
      if ($json_data = file_get_contents($url)) {
        $json_data = iconv('gbk', 'utf-8', $json_data);
        $json_data = trim($json_data);
        $json_data = substr($json_data, 4, strlen($json_data));//去除首位 json
        $json_data = substr($json_data, 0, strlen($json_data) - 1); //去除首位 )
        $json_data = trim($json_data);
        $json_data = str_replace('(', '', $json_data); //去除首位 (
        $json_data = trim($json_data);
        if ($array = json_decode($json_data, TRUE)) {
          //tmall
          foreach ($array['mallItemList'] as $item) {
            //TODO 判断评论数量 店铺信用 
            if (!array_key_exists($item['itemId'], $share_items[$cid]) && $item['commend'] > 30) {
              if (count($share_items[$cid]) < 100 && !in_array($item['itemId'], $item_ids)) {
                $share_items[$cid][$item['itemId']] = array(
                  'image' => $item['image'],
                  'title' => $item['title'],
                  'price' => $item['price'],
                  'item_id' => $item['itemId'],
                  'url' => $item['href'],
                );
                $item_ids[] = $item['itemId'];
              } else {
                break;
              }
            }
          }

          foreach ($array['itemList'] as $item) {
            //TODO 判断评论数量 店铺信用 
            if (!array_key_exists($item['itemId'], $share_items[$cid]) && $item['commend'] > 30) {
              if (count($share_items[$cid]) < 100 && !in_array($item['itemId'], $item_ids)) {
                $share_items[$cid][$item['itemId']] = array(
                  'image' => $item['image'],
                  'title' => $item['title'],
                  'price' => $item['price'],
                  'item_id' => $item['itemId'],
                  'url' => $item['href'],
                );
                $item_ids[] = $item['itemId'];
              } else {
                break;
              }
            }
          }
        }
      }
    }
  }
  $uid = \Drupal::config('share.settings')->get('default_taobao_share_uid');
  if (!$uid) {
    $uid = 1;
  }
  foreach ($share_items as $cid => $items) {
    foreach ($items as $item_id => $item) {
      $picture = 0;
      if ($item['image']) {
        if ($img_data = @file_get_contents($item['image'])) {
          $file = file_save_data($img_data);
          $file->setTemporary();
          $file->save();

          $picture = $file->id();
        }
      }
      $source = '';
      if (preg_match('/http:\/\/(www\.)?([^\/]*)/i', $item['url'], $matches)) {
        $source = $matches[2];
      }
      $array = array(
        'cid' => $cid,
        'title' => $item['title'],
        'price' => $item['price'],
        'description' => '',
        'sold_count' => 0,
        'url' => $item['url'],
        'item_id' => $item['item_id'],
        'source' => $source,
        'uid' => $uid,
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

    }
  }

}
