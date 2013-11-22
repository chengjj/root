<?php
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * form for admin/adv/edit and admin/adv/edit/{adv_id}
 */
function admin_adv_edit_form($form, &$form_state, $adv = NULL) {
  drupal_add_js(drupal_get_path('module', 'ec') . '/js/jquery-ui.custom.js', 'file');
  drupal_add_css(drupal_get_path('module', 'ec') . '/css/jquery-ui.css', 'file');
  drupal_add_js(drupal_get_path('module', 'ec') . '/js/jquery.ui.datepicker-zh-CN.js', 'file');

  drupal_add_js(drupal_get_path('module', 'adv') . '/js/admin_adv_edit_form.js');

  $form['adv'] = array('#type' => 'value', '#value' => isset($adv) ? $adv->id() : 0);
  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => '广告名称',
    '#default_value' => isset($adv) ? $adv->label() : '',
    '#required' => TRUE,
  ); 
  $form['picture'] = array(
    '#type' => 'fieldset',
    '#title' => '图片',
  );
  $picture = isset($adv) ? $adv->getPicture() : FALSE;
  $form['picture']['picture'] = array(
    '#type' => 'value',
    '#value' => $picture ? $picture->id() : 0,
  );
  $form['picture']['picture_current'] = array(
    '#markup' => isset($adv) ? theme('adv_picture', array('adv' => $adv)) : '',
  );
  $form['picture']['picture_upload'] = array(
    '#type' => 'file',
    '#title' => '广告图片',
  );
  $form['redirect'] = array(
    '#type' => 'textfield',
    '#title' => '广告跳转地址',
    '#default_value' => isset($adv) ? $adv->redirect->value : '',
    '#description' => '例如: http://www.google.com',
    '#weight' => 3,
  );
  $adv_type = _adv_type();
  $form['type'] = array(
    '#type' => 'select',
    '#title' => '广告类型',
    '#options' => $adv_type,
    '#default_value' => isset($adv) ? $adv->type->value : 0,
    '#weight' => 7,
  );  
  if (isset($adv) && $adv->sid->value) {
    $store = store_load($adv->sid->value);;
  }  
  $form['sid'] = array(
    '#title' => '商家名称',
    '#type' => 'textfield',
    '#default_value' => (isset($store) && $store) ? $store->label() : '',
  );  
  $form['store_wrapper'] = array(
    '#prefix' => '<div id="store-wrapper">',
    '#suffix' => '</div>',
  );  
  $result = db_select('cities', 'c')
    ->fields('c')
    ->execute();
  $cities = array(0 => '无');
  foreach ($result as $row) {
    $cities[$row->cid] = $row->name;
  }
  $form['cid'] = array(
    '#type' => 'select',
    '#title' => '广告地区',
    '#options' => $cities,
    '#default_value' => isset($adv) ? $adv->cid->value : 0,
    '#weight' => 8,
  );  
  $form['start'] = array(
    '#type' => 'textfield',
    '#title' => '广告开始时间',
    '#default_value' => isset($adv) ? date('Y-m-d', $adv->start->value) : '',
    '#description' => '例如: 2013-04-04, 2013/12/15',
    '#required' => TRUE,
    '#attributes' => array('autocomplete' => 'off'), 
    '#weight' => 9,
  );
  $form['expire'] = array(
    '#type' => 'textfield',
    '#title' => '广告结束时间',
    '#default_value' => isset($adv) ? date('Y-m-d', $adv->expire->value) : '',
    '#description' => '例如: 2013-04-04, 2013/12/15',
    '#required' => TRUE,
    '#attributes' => array('autocomplete' => 'off'), 
    '#weight' => 10,
  );  
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '保存',
    '#weight' => 11,
  );
  return $form;
}

function admin_adv_edit_form_validate($form, &$form_state) {
  $user = \Drupal::currentUser();
  $adv = FALSE;
  if ($aid = $form_state['values']['adv']) {
    $adv = entity_load('adv', $aid);
  }
  $start = strtotime($form_state['values']['start']);
  $expire = strtotime($form_state['values']['expire']);
  $picture = $form_state['values']['picture'];
  $sid = $form_state['values']['sid'];
  $type = $form_state['values']['type'];
  $changed = REQUEST_TIME;

  $form_state['values']['start'] = $start;
  $form_state['values']['expire'] = $expire;
  $form_state['values']['changed'] = $changed;
  if (!$type) {
    //TODO
    if ($stores = entity_load_multiple_by_properties('store', array('name' => $sid))) {
      $stores = array_values($stores);
      $store = $stores[0];
      $form_state['values']['sid'] = $store->id();
    } else {
      form_set_error('sid', '该商家不存在,确认后再添加');
    } 
  } else {
    $form_state['values']['sid'] = 0;
  }
  if ($expire < time()) {
    form_set_error('expire', '截止时间必须大于今天');
  }
  if ($adv) {
    $form_state['values']['uid'] = $adv->uid->value;
  } 
  else {
    $form_state['values']['uid'] = $user->id();
  }

  // 上传图片
  $validators = array(
    'file_validate_is_image' => array(),
    /*'file_validate_image_resolution' => array(variable_get('adv_picture_dimensions', '450x220')),*/
    'file_validate_size' => array(variable_get('adv_picture_file_size', '300') * 1024),
  );
  $file = file_save_upload('picture_upload', $validators);

  if ($file === FALSE) {
    form_set_error('picture_upload', '保存图片失败。');
  }
  elseif ($file !== NULL) {
    $form_state['values']['picture_upload'] = $file[0];
  }
}

function admin_adv_edit_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  $array = array(
    'title' => $values['title'],
    'redirect' => $values['redirect'],
    'start' => $values['start'],
    'expire' => $values['expire'],
    'cid' => $values['cid'],
    'type' => $values['type'],
    'sid' => $values['sid'],
  );
  if($values['adv']) {
    $adv = entity_load('adv', $form_state['values']['adv']);
    foreach ($array as $key => $value) {
      $adv->{$key} = $value;
    }
    $adv->changed = REQUEST_TIME;
  } else {
    $user = \Drupal::currentUser();

    $array['created'] = REQUEST_TIME;
    $array['changed'] = REQUEST_TIME;
    $array['uid'] = $user->id(); 
    $array['status'] = 1;
    $adv = entity_create('adv', $array);
  }

  $adv->save();

  if ($file = $values['picture_upload']) {
    $file->setTemporary();
    $adv->setPicture($file);
    $adv->save();
  }

  
  drupal_set_message('广告信息已保存!');
}

/**
 * form for admin/adv
 */
function admin_adv_list_form($form, &$form_state) {
  $status = array(
    '-1' => '过期',
    '0' => '未发布',
    '1'  => '正常',
  );
  $header = array(
    'title' => array('data' => '标题'),
    'type' => array('data' => '类型'),
    'store' => array('data' => '门店'),
    'city' => array('data' => '城市'),
    'start' => array('data' => '有效开始'),
    'expire' => array('data' => '有效结束'),
    'status' => array('data' => '状态'),
    'create' => array('data' => '创建时间'),
    'author' => array('data' => '作者'),
    'action' => array('data' => '操作'),
  );
  $query = db_select('advs', 'a')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit(30)
      ->fields('a');
  $query->orderBy('a.changed', 'DESC');
  $result = $query->execute();
  $advs = array();
  foreach ($result as $row) {
    $advs[] = $row;
  }
  $type = _adv_type();
  $options = array();
  foreach ($advs as $adv) {
    $user = user_load($adv->uid);
    $city = city_load($adv->cid);
    if ($adv->sid) 
      $store = store_load($adv->sid);
    $options[$adv->aid] = array(
      'title' => $adv->title, 
      'type' => $type[$adv->type],
      'store' => (isset($store) && $store) ? $store->label() : '未定义', 
      'city' => (isset($city) && $city) ? $city->label() : '无', 
      'start' => date('Y-m-d', $adv->start), 
      'expire' => date('Y-m-d', $adv->expire), 
      'status' => isset($adv->status) ? $status[$adv->status] : '',
      'create' => date('Y-m-d', $adv->created), 
      'author' => (isset($user) && $user) ? $user->getUsername() : '', 
      'action' => l('编辑', 'admin/adv/edit/' . $adv->aid),
    );
  }

  $form['adv'] = array(
    '#type' => 'tableselect',
    '#header' => $header,
    '#options' => $options,
    '#empty' => '没有找到广告',
  );
  $form['pager'] = array(
    '#markup' => theme('pager'),
  );
  $form['actions'] = array(
    '#type' => 'actions',
  );
  $form['actions']['delete']  = array(
    '#type' => 'submit',
    '#value' => '删除',
  );
  $bids = db_select('adv_blocks', 'a')
    ->fields('a', array('bid'))
    ->condition('type', 'adv')
    ->execute()
    ->fetchCol();
  if ($bids) {
    $form['actions']['recommend'] = array(
      '#type' => 'submit',
      '#value' => '推荐到',
    );
    $options = array();
    foreach ($bids as $bid) {
      $adv_block = adv_block_load($bid);
      $options[$adv_block->bid] = $adv_block->title;
    }
    
    $form['actions']['recommend_advs'] = array(
      '#type' => 'select',
      '#options' => $options,
    );
  }
  return $form;
}

function admin_adv_list_form_validate($form, &$form_state) {
  $advs = isset($form_state['input']['adv']) ? $form_state['input']['adv'] : array();
  $aids = array();
  foreach ($advs as $aid => $adv) {
    if (!empty($adv)) {
      $aids[] = $aid;
    }
  }

  if (!count($aids)) {
    form_set_error('adv', '请选择广告信息');
  } else {
    $form_state['aids'] = $aids;
  }

}

function admin_adv_list_form_submit($form, &$form_state) {
  $aids = $form_state['aids'];
  $op = $form_state['values']['op'];
  switch ($op) {
    case '删除':
      foreach (entity_load_multiple('adv', $aids) as $adv) {
        $adv->delete();
      }
      drupal_set_message('广告删除成功');
      break;
    case '推荐到':
      $bid = $form_state['values']['recommend_advs'];
      foreach ($aids as $aid) {
        $iid = db_select('adv_block_items', 'i')
    			->fields('i', array('iid'))
    			->condition('bid', $bid)
    			->condition('type', 'adv')
    			->condition('entity_id', $aid)
    			->execute()
    			->fetchObject();
        if (!$iid) {
          adv_block_item_save(NULL, array(
            'bid' => $bid,
            'entity_id' => $aid,
            'type' => 'adv',
            'status' => 1,
            )
          );
        }
      }
      drupal_set_message('广告推荐成功');
      break;
  }
}
