<?php
/**
 * Access coupon/edit
 *        coupon/edit/%coupon
 *
function admin_coupon_edit_form($form, &$form_state, $coupon = NULL) {
  if (isset($coupon)) {
    drupal_set_title('编辑促销信息');
  } else {
    drupal_set_title('添加促销信息');
  }
  drupal_add_js(drupal_get_path('module', 'ec') . '/js/jquery-ui.custom.js', 'file');
  drupal_add_css(drupal_get_path('module', 'ec') . '/css/jquery-ui.css', 'file');
  drupal_add_js(drupal_get_path('module', 'ec') . '/js/jquery.ui.datepicker-zh-CN.js', 'file');

  drupal_add_js(drupal_get_path('module', 'coupon') . '/js/admin_coupon_edit_form.js');
  $form['coupon'] = array('#type' => 'value', '#value' => isset($coupon) ? $coupon->id() : 0);
  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => '促销名称',
    '#default_value' => isset($coupon) ? $coupon->label() : '',
    '#required' => TRUE,
  );
  $form['picture'] = array(
    '#type' => 'fieldset',
    '#title' => '图片',
  );
  $form['picture']['picture'] = array(
    '#type' => 'value',
    '#default_value' => isset($coupon) ? $coupon->fid->value : 0,
  );
  $form['picture']['picture_current'] = array(
    '#markup' => isset($coupon) ? theme('coupon_picture', array('coupon' => $coupon)) : '',
  );
  $form['picture']['picture_upload'] = array(
    '#type' => 'file',
    '#title' => '广告图片',
  );

  $store = isset($coupon) ? $coupon->getStore() : NULL;
  $form['store_name'] = array(
    '#title' => '商家名称',
    '#type' => 'textfield',
    '#required' => TRUE,
    '#default_value' => isset($store) ? $store->label() : '',
    '#autocomplete_path' => 'coupon/js/store',
    '#ajax' => array(
      'callback' => 'store_coupon_js',
      'wrapper'  => 'store-wrapper',
      'effect'   => 'fade',
    ),
  );
  $form['store_wrapper'] = array(
    '#prefix' => '<div id="store-wrapper">',
    '#suffix' => '</div>',
  );  
  $form['body'] = array(
    '#title' => '促销内容',
    '#type' => 'textarea',
    '#default_value' => isset($coupon->body) ? $coupon->body->value : '',
    '#required' => TRUE,
    '#resizable' => FALSE,
  );
  $form['note'] = array(
    '#title' => '促销限制',
    '#type' => 'textarea',
    '#maxlength' => 255,
    '#default_value' => isset($coupon->note) ? $coupon->note->value : '',
    '#description' => '最多255个字符',
  );
  $form['start'] = array(
    '#title' => '促销开始时间',
    '#type' => 'textfield',
    '#required' => TRUE,
    '#attributes' => array('autocomplete' => 'off'), 
    '#default_value' => isset($coupon->start) ? date('Y-m-d', $coupon->start->value) : '',
  );
  $form['expire'] = array(
    '#title' => '促销截止时间',
    '#type' => 'textfield',
    '#required' => TRUE,
    '#attributes' => array('autocomplete' => 'off'), 
    '#default_value' => isset($coupon->expire) ? date('Y-m-d', $coupon->expire->value) : '',
  );
    $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '保存',
  );

  return $form;
}

function admin_coupon_edit_form_validate($form, &$form_state) {
  $start = strtotime($form_state['values']['start']);
  $expire = strtotime($form_state['values']['expire']);
  $picture = $form_state['values']['picture'];

  $store_name = $form_state['values']['store_name'];
  $changed = REQUEST_TIME;

  $form_state['values']['start'] = $start;
  $form_state['values']['expire'] = $expire;
  $form_state['values']['changed'] = $changed;
  $store = store_load_by_name($store_name);
  if (!isset($store) && !is_object($store)) {
    form_set_error('sid', '商家ID不存在,确认后再添加');
  } 
  else {
    $form_state['values']['sid'] = $store->sid;
    $form_state['values']['uid'] = $store->uid;    
  }
  if ($expire < time()) {
    form_set_error('expire', '截止时间必须大于今天');
  }
  $form_state['values']['status'] = 0;
  $validators = array(
    'file_validate_is_image' => array(),
    'file_validate_image_resolution' => array(variable_get('user_picture_dimentions', '450x220')),
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

function admin_coupon_edit_form_submit($form, &$form_state) {
 if ($form_state['values']['coupon']) {
   $coupon = coupon_load($form_state['values']['coupon']);
 }
 $values = $form_state['values'];
 $array = array(
   'title' => $values['title'],
   'body' => $values['body'],
   'uid' => $values['uid'],
   'sid' => $values['sid'],
   'note' => $values['note'],
   'start' => $values['start'],
   'expire' => $values['expire'],
   'changed' => $values['changed'],
   'status' => $values['status'],
 );
 if (isset($coupon)) {
   foreach ($array as $key => $value) {
     $coupon->{$key} = $value;
   }
   $coupon->save();
 } else {
   $array['created'] = REQUEST_TIME; 
   $coupon = entity_create('coupon', $array);
   $coupon->enforceIsNew();
   $coupon->save();
 }
  
 $file = $values['picture_upload'];
 if ($file) {
   $file->setTemporary();
   $coupon->setPicture($file);
   $coupon->save();
 }
 //TODO
 //$coupon = coupon_save($coupon, $form_state['values']);

  drupal_set_message('促销信息已保存!');  
}

function store_coupon_js($form, &$form_state) {
  return $form['store-wrapper'];
}
**/
