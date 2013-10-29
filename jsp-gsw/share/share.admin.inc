<?php

/**
 * page callback for: admin/share
 */
function share_admin_share_list($form, &$form_state) {
  //TODO add share_catalog
  drupal_add_js(drupal_get_path('module', 'ec') . '/js/jquery-ui.custom.js', 'file');
  drupal_add_css(drupal_get_path('module', 'ec') . '/css/jquery-ui.css', 'file');
  drupal_add_js(drupal_get_path('module', 'ec') . '/js/jquery.ui.datepicker-zh-CN.js', 'file');

  drupal_add_js(drupal_get_path('module', 'share') . '/js/admin-share-list.js');
  global $base_url;

  $keyword = isset($_GET['keyword']) ? check_plain($_GET['keyword']) : '';
  $start = isset($_GET['start']) ? check_plain($_GET['start']) : '';
  $expire = isset($_GET['expire']) ? check_plain($_GET['expire']) : '';

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
      'operations' => l('编辑', 'admin/share/edit/' .$share->id()) . ' | ' . l('删除', 'admin/share/delete/' . $share->id()),
    ); 
  }
   $form['filter']['keyword'] = array(
    '#title' => '关键字',
    '#type' => 'textfield',
    '#default_value' => isset($_GET['keyword']) ? $_GET['keyword'] : '',
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

/**
 * page callback for: admin/share/edit/%share
 */
function share_admin_edit_form($form, &$form_state, $share = NULL) {
  $user = \Drupal::currentUser();
  $form['share'] = array(
    '#type' => 'value',
    '#value' => $share->id(),
  );
  $form['uid'] = array(
    '#type' => 'value',
    '#value' => $user->id(),
  );
  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => '标题',
    '#required' => TRUE,
    '#default_value' => $share->label(),
  );
  $result = db_query('SELECT * FROM {share_catalog} WHERE parent_cid=0 ORDER BY weight');
  $share_catalog_parents = array();
  $share_catalogs = array();

  foreach ($result as $row) {
    $share_catalog_parents[$row->cid] = $row->name;
  }
  $catalog_parent = db_query('SELECT parent_cid FROM {share_catalog} WHERE cid=:cid ', array(':cid' => $share->cid->value))->fetchField();
  $form['catalog_parent'] = array(
    '#type' => 'select',
    '#title' => '分类',
    '#options' => $share_catalog_parents,
    '#default_value' => $catalog_parent,
    '#required' => TRUE,
    '#ajax' => array(
      'callback' => 'share_admin_edit_form_catalog_js',
      'wrapper' => 'template-wrapper',
      'effect' => 'fade',
    ),
  );
  $form['template_wrapper'] = array(
    '#prefix' => '<div id="template-wrapper">',
    '#suffix' => '</div>',
  );
  if ($catalog_parent || isset($form_state['values']['catalog_parent'])) {
    $default_cid = isset($form_state['values']['catalog_parent']) ? $form_state['values']['catalog_parent'] : $catalog_parent;
    $result = db_query('SELECT * FROM {share_catalog} WHERE parent_cid=:parent_cid ORDER BY weight', array(':parent_cid' => $default_cid));
    foreach ($result as $row) {
      $share_catalogs[$row->cid] = $row->name;
    }
    $form['template_wrapper']['cid'] = array(
      '#type' => 'select',
      '#options' => $share_catalogs,
      '#default_value' => $share->cid->value ? $share->cid->value : '',
      '#required' => TRUE,
    );
  }
  $form['picture'] = array(
    '#type' => 'fieldset',
    '#title' => '图片',
  );
  $picture = $share->getPicture();
  $form['picture']['picture'] = array(
    '#type' => 'value',
    '#value' => $picture ? $picture->id() : NULL,
  );
  $form['picture']['picture_current'] = array(
    '#markup' => $picture ? theme('image', array('uri' => $picture->getFileUri())) : '',
  );
  $form['picture']['picture_upload'] = array(
    '#type' => 'file',
    '#title' => '上传图片',
    '#size' => '20',
  );
  $form['description'] = array(
    '#type' => 'textarea',
    '#title' => '发布者描述信息',
    '#default_value' => $share->description->value,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '保存',
  );
  $form['delete'] = array(
      '#type' => 'submit',
      '#value' => '删除',
    );
 return $form; 
}

function share_admin_edit_form_validate($form, &$form_state) {
  $validators = array(
    'file_validate_is_image' => array(),
    'file_validate_image_resolution' => array(variable_get('user_picture_dimentions', '85x85')),
    'file_validate_size' => array(variable_get('account_picture_file_size', '300') * 1024),
  ); 

  $file = file_save_upload('picture_upload', $validators);
  if ($file === FALSE) {
    form_set_error('picture_upload', '保存图片失败。');
  }
  elseif ($file !== NULL) {
    $form_state['values']['picture_upload'] = $file[0];
  }
}
function share_admin_edit_form_submit($form, &$form_state) {
  $sid = $form_state['values']['share'];
  $op = $form_state['values']['op'];
  switch ($op) {
    case '保存':
      //share_save(share_load($sid), $form_state['values']);
      drupal_set_message('商品信息已保存。');
      break;
    case '删除':
      $form_state['redirect'] = 'admin/share/delete/' . $sid;
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
    'admin/share/edit/' . $share->id(),
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
