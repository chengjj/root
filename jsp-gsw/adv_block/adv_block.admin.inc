<?php

/**
 * page callback: admin/adv_block/list
 */
function admin_adv_block_list_form() {
  $request = \Drupal::request();
  drupal_add_library('system', 'ui.dialog');
  //drupal_add_js('sites/all/misc/ajax-dialog.js');
  //drupal_add_js(drupal_get_path('module', 'adv') . '/js/admin-adv_blocks-list-form.js');
  
  $keywords = $request->query->get('keywords','');
  $type = $request->query->get('type','');
  
  $form['keywords'] = array(
    '#type' => 'textfield',
    '#title' => '广告位名称',
  );
  $form['type'] = array(
    '#type' => 'select',
    '#options' => array('' => '全部广告类型') + variable_get('adv_block_types', array()),
    '#default_value' => $type,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '搜索',
  );
  $form['adv_block_add'] = array(
    '#markup' => l('新增广告位', 'admin/adv_block/edit'),
  );
  

  $header = array(
    'title' => array('data' => '广告位名称'),
    'type' => array('data' => '广告位类型', 'field' => 'type'),
    'template' => array('data' => '展示样式', 'field' => 'template'),
    'operations' => array('data' => '操作'),
  );

  $query = db_select('adv_blocks', 'b')
    ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
    ->extend('Drupal\Core\Database\Query\TableSortExtender')
    ->fields('b', array('bid'))
    ->limit(50)
    ->orderByHeader($header);
  $keywords && $query->condition('title', '%' . db_like($keywords). '%', 'LIKE');
  $type && $query->condition('type', $type);
  $result = $query->execute();

  $options = array();
  $adv_block_types = variable_get('adv_block_types', array());
  foreach ($result as $row) {
    $adv_block = adv_block_load($row->bid);

    $adv_block_templates = variable_get('adv_block_' . $adv_block->type . '_templates', array());
    $options[$adv_block->bid] = array(
      'title' => $adv_block->title,
      'type' => $adv_block_types[$adv_block->type],
      'template' => array_search($adv_block->template, $adv_block_templates),
      'operations' => l('编辑/删除', 'admin/adv_block/edit/' . $adv_block->bid, array('query' => drupal_get_destination())) . ' | ' . l('管理广告位', 'admin/adv_block/' . $adv_block->bid),
    );
  }

  $form['blocks'] = array(
    '#type' => 'tableselect',
    '#header' => $header,
    '#options' => $options,
    '#empty' => '未找到广告位信息',
  );

  $form['pager'] = array('#markup' => theme('pager'));

  return $form;
}

function admin_adv_block_list_form_submit($form, &$form_state) {
  $request = \Drupal::request();
  $values = $form_state['values'];
  $page = $request->query->get('page',0);
	$form_state['redirect'] = array('admin/adv_block', array(
		'query' => array(
    	'keywords' => $values['keywords'],
      'type' => $values['type'],
      'page' => $page,
  	),
  ));
}

/**
 * page callback: admin/adv_blocks/edit admin/adv_blocks/edit/%adv_block
 */
function admin_adv_block_edit_form($form, &$form_state, $adv_block = NULL) {
  if (isset($adv_block)) {
    drupal_set_title('编辑广告位');
  } else {
    drupal_set_title('新增广告位');
  }
  $form['adv_block'] = array(
    '#type' => 'value',
    '#value' => $adv_block,
  );
  $form['type'] = array(
    '#type' => 'select',
    '#title' => '类型',
    '#options' => array('' => '选择广告类型') + variable_get('adv_block_types', array()),
    '#default_value' => $adv_block ? $adv_block->type : '',
    '#required' => TRUE,
    '#ajax' => array(
      'callback' => 'admin_adv_block_edit_form_type_js',
      'wrapper' => 'template-wrapper',
      'effect' => 'fade',
    ),
  );
  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => '广告位名称',
    '#default_value' => $adv_block ? $adv_block->title : '',
    '#required' => TRUE,
  );
  $form['template_wrapper'] = array(
    '#prefix' => '<div id="template-wrapper">',
    '#suffix' => '</div>',
  );
  if ($adv_block || isset($form_state['values']['type'])) {
    $type = isset($form_state['values']['type']) ? $form_state['values']['type'] : ($adv_block ? $adv_block->type : '');
    $form['template_wrapper']['template'] = array(
      '#type' => 'select',
      '#title' => t('展示样式'),
      '#options' => $type ? array_flip(variable_get('adv_block_' . $type . '_templates', array())) : array(),
      '#default_value' => $adv_block ? $adv_block->template : '',
      '#required' => TRUE,
    );
  }
  
  $form['body'] = array(
    '#type' => 'textarea',
    '#title' => '缺省内容',
    '#default_value' => $adv_block ? $adv_block->body : '',
  );
  $form['picture_dimentions'] = array(
    '#type' => 'textfield',
    '#title' => t('图片尺寸'),
    '#description' => t('格式为: 宽度x高度 例如: 85x85'),
    '#default_value' => $adv_block ? $adv_block->picture_dimentions : '',
  );
  
  $form['picture'] = array(
    '#type' => 'fieldset',
    '#title' => '图片',
  );
  $form['picture']['picture'] = array(
    '#type' => 'value',
    '#value' => $adv_block ? $adv_block->picture : NULL,
  );
  $form['picture']['picture_current'] = array(
    '#markup' => ($adv_block && $adv_block->picture) ? theme('image', array('uri' => $adv_block->picture->getFileUri())) : '',
  );
  $form['picture']['picture_upload'] = array(
    '#type' => 'file',
    '#title' => '广告位默认图片',
    '#description' => '大于' . variable_get('adv_block_picture_dimentions', '277x1000') . '的图片将被自动缩小。',
  );  


  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '保存',
  );
  if (isset($adv_block)) {
    $form['delete'] = array(
      '#type' => 'submit',
      '#value' => '删除',
    );
  }

  return $form;
}

function admin_adv_block_edit_form_type_js($form, &$form_state) {
  return $form['template_wrapper'];
}

function admin_adv_block_edit_form_validate($form, &$form_state) {
   // 上传图片
  $validators = array(
    'file_validate_is_image' => array(),
    'file_validate_image_resolution' => array(variable_get('adv_block_picture_dimensions', '230x270')),
    'file_validate_size' => array(variable_get('adv_block_picture_file_size', '256') * 1024),
  );
  $file = file_save_upload('picture_upload', $validators, FALSE, 0);
  if ($file === FALSE) {
    form_set_error('picture_upload', t("Failed to upload the picture image; the %directory directory doesn't exist or is not writable.", array('%directory' => variable_get('adv_block_picture_path', 'adv_blocks'))));
   	return ;
  }
  elseif ($file !== NULL) {
    $form_state['values']['picture'] = $file;
  }
  elseif ($file == NULL && $form_state['values']['adv_block'] == NULL) {
  	form_set_error('picture', '未选择上传的广告位图片!');
  	return ;
  }
}

function admin_adv_block_edit_form_submit($form, &$form_state) {
  $op = $form_state['values']['op'];
  switch ($op) {
    case '保存':
      adv_block_save($form_state['values']['adv_block'], $form_state['values']);
      drupal_set_message('广告位已保存。');
      break;
    case '删除':
      $adv_block = $form_state['values']['adv_block'];
      error_log('adv_block:' . print_r($adv_block,1));
      $form_state['redirect'] = 'admin/adv_block/' . $adv_block->bid . '/delete';
      break;
  }
}

/**
 * page callback: admin/adv_blocks/%adv_block/delete
 */
function admin_adv_block_delete_form($form, &$form_state, $adv_block) {
  $form['bid'] = array('#type' => 'value', '#value' => $adv_block->bid);
  $form['title'] = array('#type' => 'value', '#value' => $adv_block->title);
  return confirm_form($form,
    t('Are you sure you want to delete %title?', array('%title' => $adv_block->title)),
    'admin/adv_block/edit/' . $adv_block->bid,
    t('This action cannot be undone.'),
    t('Delete'),
    t('Cancel')
  );
}

function admin_adv_block_delete_form_submit($form, &$form_state) {
  if ($form_state['values']['confirm']) {
    adv_block_delete($form_state['values']['bid']);
    drupal_set_message(t('广告位 %title has been deleted.', array('%title' => $form_state['values']['title'])));
  }
  $form_state['redirect'] = 'admin/adv_block/list';
}

/**
 * page callback: admin/adv_block/item/%adv_block_item/edit
 */
function admin_adv_block_item_edit_form($form, &$form_state, $adv_block_item) {
  $adv_block = adv_block_load($adv_block_item->bid);
  $form['iid'] = array(
    '#type' => 'value',
    '#value' => $adv_block_item->iid,
  );
  $form['picture_dimentions'] = array(
    '#type' => 'value',
    '#value' => $adv_block->picture_dimentions,
  );
  switch ($adv_block_item->type) {
    case 'adv':
      $adv = adv_load($adv_block_item->entity_id);
      $title = $adv_block_item->title ? $adv_block_item->title : $adv->title;
      $picture = $adv_block_item->picture ? $adv_block_item->picture : $adv->picture;
      $description = '如果名称为空则默认显示广告标题';
      break;
    case 'coupon':
      $coupon = coupon_load($adv_block_item->entity_id);
      $title = $adv_block_item->title ? $adv_block_item->title : $coupon->title;
      $picture = $adv_block_item->picture ? $adv_block_item->picture : $coupon->picture;
      $description = '如果名称为空则默认显示促销信息标题';
      break;
    case 'share':
      $share = share_load($adv_block_item->entity_id);
      $title = $adv_block_item->title ? $adv_block_item->title : $share->getTitle();
      $picture = $adv_block_item->picture ? $adv_block_item->picture : $share->getPicture();
      $description = '如果名称为空则默认显示分享商品标题';
  }
  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => '广告位名称',
    '#default_value' => $title,
    '#description' => $description,
  );
  $form['picture'] = array(
    '#type' => 'fieldset',
    '#title' => '图片',
  );
  $form['picture']['picture'] = array(
    '#type' => 'value',
    '#value' => $picture ? $picture : NULL,
  );
  $form['picture']['picture_current'] = array(
    '#markup' => $picture ? theme('image', array('uri' => $picture->getFileUri())) : '',
  );
  if ($adv_block->picture_dimentions) {
    $upload_description = '大于' . $adv_block->picture_dimentions . '的图片将被自动缩小。';
  }
  $form['picture']['picture_upload'] = array(
    '#type' => 'file',
    '#title' => '广告位默认图片',
    '#description' => $upload_description,
  ); 

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '保存',
  );
  return $form;
}

function admin_adv_block_item_edit_form_validate($form, &$form_state) {
  // 上传图片
  $validators = array(
    'file_validate_is_image' => array(),
    'file_validate_size' => array(variable_get('adv_block_picture_file_size', '256') * 1024),
  );
  if ($form_status['values']['picture_dimentions']) {
    $validators['file_validate_image_resolution'] = array($form_status['values']['picture_dimentions']);
  }

  $file = file_save_upload('picture_upload', $validators, FALSE, 0);
  if ($file === FALSE) {
    form_set_error('picture_upload', t("Failed to upload the picture image; the %directory directory doesn't exist or is not writable.", array('%directory' => variable_get('adv_block_picture_path', 'adv_blocks'))));
   	return ;
  }
  elseif ($file !== NULL) {
    $form_state['values']['picture'] = $file;
  }
}

function admin_adv_block_item_edit_form_submit($form, &$form_state) {
  $adv_block_item = adv_block_item_load($form_state['values']['iid']);
  $edit = array(
    'title' => $form_state['values']['title'],
  );
  if (isset($form_state['values']['picture'])) {
    $edit['picture'] = $form_state['values']['picture'];
  }
  adv_block_item_save($adv_block_item, $edit);
  drupal_set_message('设置已保存');
}
