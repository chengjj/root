<?php
/**
 * @file
 * User page callback file for the user module.
 */
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Plugin\Core\Entity\User;
use Drupal\taxonomy\Plugin\Core\Entity\Term;

/**
 * Access callback for path download/store.
 */
function download_store_page() {
  if (ob_get_level()) {
    ob_end_clean();
  }
  $apk_path = variable_get('gsw_store_mobile_filepath', '/sites/default/files/gsw_store.apk');
  $uri = $_SERVER['DOCUMENT_ROOT'] . $apk_path; 
  $content_length = filesize($uri);

  header('Content-type: application/vnd.android.package-archive');
  header('Content-Disposition: attachment; filename=' . variable_get('gsw_store_mobile_filename', 'gsw_store.apk'));
  // Transfer file in 1024 byte chunks to save memory usage.
  header('Content-Length: ' . $content_length);

  if ($fd = fopen($uri, 'rb')) {
    while (!feof($fd)) {
      print fread($fd, 1024);
    }
    fclose($fd);
  }
  else {
    drupal_not_found();
  }
  drupal_exit();
}

function download_store_android_page() {
  return theme('download_store_android_page');
}
/**
 * Access callback for path download/consumer.
 */
function download_consumer_page() {
  if (ob_get_level()) {
    ob_end_clean();
  }

  // Transfer file in 1024 byte chunks to save memory usage.
  $apk_path = variable_get('gsw_consumer_mobile_filepath', '/sites/default/files/gsw_consumer.apk');
  $uri = $_SERVER['DOCUMENT_ROOT'] . $apk_path; 
  $content_length = filesize($uri);
  
  header('Content-type: application/vnd.android.package-archive');
  header('Content-Disposition: attachment; filename=' . variable_get('gsw_consumer_mobile_filename', 'gsw_consumer.apk'));
  header('Content-Length: ' . $content_length);
  if ($fd = fopen($uri, 'rb')) {
    while (!feof($fd)) {
      print fread($fd, 1024);
    }
    fclose($fd);
  }
  else {
    drupal_not_found();
  }
  drupal_exit();
}
/**
 * Access download 
 * @description 
 *  For download page
 */
function download_page() {
  return theme('download_page');
}

/**
 * Access callback for path store/audit/list.
 */
function store_audit_list_form($form, &$form_state) {
	$header = array(
    'title_before' => array('data' => '以前商家名称'),
  	'title' => array('data' => '商家名称'),
    'discount_before' => array('data' => '以前折扣'),
    'discount' => array('data' => '折扣'),
    'address_before' => array('data' => '以前地址'),
    'address' => array('data' => '地址'),
  );

  $result = db_select('store_revision', 'sr')
      ->fields('sr', array('sid', 'uid', 'discount', 'address', 'name'))
      ->condition('status', 0)
      ->execute();

  $options = array();
  foreach ($result as $row) {
    $store = store_load($row->sid);
    $options[$row->sid] = array(
      'title_before' => $store->label(),
      'title' => $row->name,
      'discount_before' => $store->discount->value,
      'discount' => $row->discount,
      'address_before' => $store->address->value,
      'address' => $row->address,
    );
  }

  $form['audit'] = array(
    '#type' => 'tableselect',
    '#header' => $header,
    '#options' => $options,
    '#empty' => "没有审批商家！",
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '审批',
  );

  return $form;
}

function store_audit_list_form_submit($form, &$form_state) {
  $values = $form_state['values']['audit'];
  foreach ($values as $sid => $value) {
    if ($value) {
      $store = store_load($sid);
      $store_revision = store_revision_load($sid);
      /*$values['name'] = $store_revision->name;
      $values['discount'] = $store_revision->discount;
      $values['address'] = $store_revision->address;
      */
      $store->name->value = $store_revision->name;
      $store->discount->value = $store_revision->discount;
      $store->address->value = $store_revision->address;
      $store->save();

      db_update('store_revision')
        ->fields(array('status' => 1))
        ->condition('sid', $sid)
        ->execute();
    }
  }
}

/**
 * Access callback for path admin/store/list.
 */
function admin_store_list($form, &$form_state) {
  drupal_add_js(drupal_get_path('module', 'ec') . '/js/jquery-ui.custom.js', 'file');
  drupal_add_css(drupal_get_path('module', 'ec') . '/css/jquery-ui.css', 'file');
  drupal_add_js(drupal_get_path('module', 'ec') . '/js/jquery.ui.datepicker-zh-CN.js', 'file');

  drupal_add_js(drupal_get_path('module', 'store') . '/js/admin-store-list.js');
  global $base_url;
  $keyword = isset($_GET['keyword']) ? check_plain($_GET['keyword']) : '';
  $account_name = isset($_GET['account']) ? check_plain($_GET['account']) : '';
  $city_id = isset($_GET['city_id']) ? check_plain($_GET['city_id']) : 0;
  $start = isset($_GET['start']) ? check_plain($_GET['start']) : '';
  $expire = isset($_GET['expire']) ? check_plain($_GET['expire']) : '';

  $header = array(
    array('data' => '商店名称', 'field' => 'name'),
    array('data' => '城市',),
    array('data' => '用户名',),
    array('data' => '创建时间', 'field' => 'created'),
    array('data' => '更新时间', 'field' => 'update_at', 'sort' => 'desc'),
    array('data' => '是否为新商家',),
    array('data' => '操作')
  );

  $query = db_select('stores', 's')
    ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
    ->extend('Drupal\Core\Database\Query\TableSortExtender')
    ->limit(30)
    ->fields('s', array('sid', 'name', 'uid', 'update_at', 'city_id', 'created'))
    ->condition('s.name', '', '<>');
    
  if ($keyword) {
    $query->condition(db_or()
      ->condition('s.name', '%' . db_like($keyword) . '%', 'LIKE')
    );
  }
  if ($account_name) {
    $query->leftJoin('users', 'u', 'u.uid = s.uid');
    $query->condition(db_or()
      ->condition('u.name', '%' . db_like($account_name) . '%', 'LIKE')
     );
  }
  if ($city_id) {
    $query->innerJoin('cities', 'c', 'c.cid = s.city_id');
    $query->condition('c.cid', $city_id);
  }

  if ($start && strtotime($start)) {
    $query->condition('s.created', strtotime($start), '>=');
  }
  if ($expire && strtotime($expire)) {
    $query->condition('s.created', strtotime($expire), '<');
  }
    
  $result = $query->orderByHeader($header)->execute();
  $rows = array();
  foreach ($result as $row) {
    $account = user_load($row->uid);
    $city = city_load($row->city_id);
    $time = time() - $row->created;
    $dimension_url = store_get_store_qrcode($row->sid);    
    $city_name = isset($city) ? $city->label() : '';
    $operate = l('编辑', 'store/' . $row->sid . '/edit') . ' | '. l('删除', 'store/' . $row->sid . '/delete'). ' | ' . l('二维码', $base_url . $dimension_url);
    if ($row->uid == 0) {
      $operate .= ' | ' . l('商户认领', 'store/' . $row->sid . '/register');
    }
    $rows[$row->sid] = array($row->name, $city_name, $account->getUsername(), date('Y-m-d', $row->created),date('Y-m-d', $row->update_at), $time > 1209600 ? '否' : '是', $operate);
  }
  //$output = '';
  if (empty($rows)) {
    $rows[] = array(array('data' => t('没有商店'), 'colspan' => count($header)));
  }

  $form['filter']['keyword'] = array(
    '#title' => '关键字',
    '#type' => 'textfield',
    '#default_value' => isset($_GET['keyword']) ? $_GET['keyword'] : '',
  );
  $form['filter']['account_name'] = array(
    '#title' => '用户帐号',
    '#type' => 'textfield',
    '#default_value' => $account_name,
  );
  $form['filter']['start'] = array(
    '#title' => '开始时间',
    '#type' => 'textfield',
    '#attributes' => array('autocomplete' => 'off'), 
    '#default_value' => ($start && strtotime($start)) ? $start : '',
    '#description' => '<font color="red"><b>Tips</b></font>: 使用店铺的创建时间查询',
  );  

  $form['filter']['expire'] = array(
    '#title' => '截止时间',
    '#type' => 'textfield',
    '#attributes' => array('autocomplete' => 'off'), 
    '#default_value' => ($expire && strtotime($expire)) ? $expire : '',
    '#description' => '<font color="red"><b>Tips</b></font>: 使用店铺的创建时间查询',
  );
  $result = db_select('cities', 'c')
    ->fields('c')
    ->execute();
  $cities = array(0 => '所有城市');
  foreach ($result as $row) {
    $cities[$row->cid] = $row->name;
  }

  $form['filter']['city_id'] = array(
    '#title' => '城市',
    '#type' => 'select',
    '#options' => $cities,
    '#default_value' => $city_id,
  );
  $form['filter']['search'] = array(
    '#type' => 'submit',
    '#value' => '搜索',
  );

  $form['stores'] = array(
   	'#type' => 'tableselect',
   	'#header' => $header,
   	'#options' => $rows,
   	'#empty' => '没有会员!',
  );

  if ($ids = adv_block_select_blocks('store')) {
    $form['actions']['recommend'] = array(
      '#type' => 'submit',
      '#value' => '推荐到',
    );
    $options = array();
    foreach ($ids as $bid) {
      $adv_block = adv_block_load($bid);
      $options[$adv_block->bid] = $adv_block->title;
    }
    
    $form['actions']['recommend_stores'] = array(
      '#type' => 'select',
      '#options' => $options,
    );
  }

  $form['pager'] = array(
    '#theme' => 'pager',
  );
  /*
  $output .= theme('table', array('header' => $header, 'rows' => $rows));
  $output .= theme('pager');
  $output .= l('添加一个商店', 'store/add');
  return $output;
  */
  return $form;
}

function admin_store_list_validate($form, &$form_state) {
  $stores = isset($form_state['input']['stores']) ? $form_state['input']['stores'] : array();
  $sids = array();
  foreach ($stores as $sid => $store) {
    if (!empty($store)) {
      $sids[] = $sid;
    }
  }
  $op = $form_state['values']['op'];
  if (in_array($op, array('推荐到')) && !count($sids)) {
    form_set_error('stores', '请选择商品信息');
  } else {
    $form_state['sids'] = $sids;
  }

}

function admin_store_list_submit($form, &$form_state) {
  $sids = $form_state['sids'];
	$op = $form_state['values']['op'];
  switch ($op) {
    case '搜索':
      $keyword = $form_state['values']['keyword'];
      $account_name = $form_state['values']['account_name'];
      $city_id = $form_state['values']['city_id'];
      $start = $form_state['values']['start'];
      $expire = $form_state['values']['expire'];

      $form_state['redirect'] = array('admin/store/list', array(
        'query' => array(
          'keyword' => $keyword,
          'account' => $account_name,
          'city_id' => $city_id,
          'start' => $start,
          'expire' => $expire,
        ),
      ));
      break;
    case '推荐到':
      $bid = $form_state['values']['recommend_stores'];
      foreach ($sids as $sid) {
        $iid = db_select('adv_block_items', 'i')
    			->fields('i', array('iid'))
    			->condition('bid', $bid)
    			->condition('type', 'store')
    			->condition('entity_id', $sid)
    			->execute()
    			->fetchObject();
        if (!$iid) {
          adv_block_item_save(NULL, array(
            'bid' => $bid,
            'entity_id' => $sid,
            'type' => 'store',
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
 * Access callback for path store/add.
 */
function admin_store_edit_form($form, &$form_state, $store = NULL) {
  $default_city_id = \Drupal::config('city.settings')->get('default_city_id');
  if (isset($store->hours)) {
    $time = explode('-', $store->hours->value);
    $begin = $time[0];
    $over = $time[1];
  }

  $result = db_query('SELECT * FROM {store_catalog} WHERE parent_cid=0');;
  $store_catalog = array();
  $default_parent_cid = 0;
  foreach ($result as $row) {
    if (!$default_parent_cid) {
      $default_parent_cid = $row->cid;
    }
    $store_catalog[$row->cid] = $row->name;
  }

  $dis = db_select('districts', 'd')
    ->fields('d')
    ->execute();
  $districts = array();  
  foreach ($dis as $row) {
    $districts[$row->cid][$row->did] = $row->name;
  }

  $ci = db_select('cities', 'c')
    ->fields('c')
    ->execute();
  $cities = array();
  foreach ($ci as $row) {
    $cities[$row->cid] = $row->name;
  }
  $form['store_id'] = array(
    '#type' => 'value',
    '#value' => isset($store) ? $store->id() : 0,
  );
  $form['name'] = array(
    '#title' => '门店',
    '#type' => 'textfield',
    '#default_value' => isset($store) ? $store->label() : '',
  );
  $account = isset($store) ? $store->getAuthor() : NULL;
  $form['uid'] = array(
    '#title' => '用户帐号',
    '#type' => 'textfield',
    '#default_value' => isset($account) ? $account->getUsername() : '',
    '#description' => '创建商户不指定用户则输入0',
    /*'#autocomplete_path' => 'store/js/user',
    '#ajax' => array(
      'callback' => 'admin_store_edit_form_js',
      'wrapper'  => 'store-wrapper',
      'effect'   => 'fade',
    ),*/
  );
  /*$form['store_wrapper'] = array(
    '#prefix' => '<div id="store-wrapper">',
    '#suffix' => '</div>',
  );*/
  $form['picture'] = array(
    '#type' => 'fieldset',
    '#title' => '图片',
  );
  $form['picture']['picture'] = array(
    '#type' => 'value',
    '#value' => isset($store->image_url) ? $store->image_url->value : NULL,
  );
  $form['picture']['picture_current'] = array(
    '#markup' => isset($store) ? theme('store_picture', array('store' => $store)) : '',
  );
  $form['picture']['picture_upload'] = array(
    '#type' => 'file',
    '#title' => '上传logo',
    '#size' => '20',
  );
  $form['latitude'] = array(
    '#title' => '纬度',
    '#type' => 'textfield',
    '#default_value' => isset($store->latitude) ? $store->latitude->value : '',
  );
  $form['longitude'] = array(
    '#title' => '经度',
    '#type' => 'textfield',
    '#default_value' => isset($store->longitude) ? $store->longitude->value : '',
  );
  $form['address'] = array(
    '#title' => '地址',
    '#type' => 'textfield',
    '#default_value' => isset($store->address) ? $store->address->value : '',
  );
  $form['phone'] = array(
    '#title' => '电话',
    '#type' => 'textfield',
    '#default_value' => isset($store->phone) ? $store->phone->value : '',
  );
  $form['discount'] = array(
    '#title' => '折扣',
    '#type' => 'textfield',
    '#default_value' => isset($store->discount) ? number_format($store->discount->value, 1) : '',
  );  
  //hours 拆分成 begin  over
  $form['begin'] = array(
    '#title' => '营业开始时间',
    '#type' => 'textfield',
    '#default_value' => isset($begin) ? $begin : '',
    '#description' => '营业开始时间,如8:00',
  );  
  $form['over'] = array(
    '#title' => '营业结束时间',
    '#type' => 'textfield',
    '#default_value' => isset($over) ? $over : '',
    '#description' => '营业结束时间,如22:00',
  );  
  if (isset($store->cid)) {
    $parent_catalog = store_catalog_get_top($store->cid->value);
    $default_parent_cid = $parent_catalog->id();
  }
  $form['parent_cid'] = array(
    '#title' => '分类',
    '#type' => 'select',
    '#options' => $store_catalog,
    '#default_value' => isset($parent_catalog) ? $parent_catalog->id() : 0,
    '#ajax' => array(
      'callback' => 'admin_store_edit_form_catalog_js',
      'wrapper' => 'catalog-template-wrapper',
      'effect' => 'fade',
    ),
  );
  $form['catalog_template_wrapper'] = array(
    '#prefix' => '<div id="catalog-template-wrapper">',
    '#suffix' => '</div>',
  );
  $parent_cid = isset($form_state['values']['parent_cid']) ? $form_state['values']['parent_cid'] : $default_parent_cid;
  $children_catalog = array();
  foreach (store_catalog_load_children($parent_cid) as $catalog) {
    $children_catalog[$catalog->id()] = $catalog->label();
  }
  $form['catalog_template_wrapper']['cid'] = array(
    '#type' => 'select',
    '#options' => $children_catalog,
    '#default_value' => isset($store->cid) ? $store->cid->value : 0,
    '#required' => TRUE,
  );
  $form['city_id'] = array(
    '#title' => '城市',
    '#type' => 'select',
    '#options' => $cities,
    '#default_value' => isset($store->city_id) ? $store->city_id->value : $default_city_id,
    '#ajax' => array(
      'callback' => 'admin_store_edit_form_district_js',
      'wrapper' => 'template-wrapper',
      'effect' => 'fade',
    ),
  );  
  $form['template_wrapper'] = array(
    '#prefix' => '<div id="template-wrapper">',
    '#suffix' => '</div>',
  );
  $city_id_def = isset($store->city_id) ? $store->city_id->value : $default_city_id;
  $city_id =  isset($form_state['values']['city_id']) ? $form_state['values']['city_id'] : $city_id_def;
  $form['template_wrapper']['district_id'] = array(
    '#type' => 'select',
    '#title' => t('区域'),
    '#options' => $districts[$city_id],
    '#default_value' => isset($store->district_id) ? $store->district_id->value : 0,
    '#required' => TRUE,
  );
  $form['follow_count'] = array(
    '#title' => '关注总数',
    '#type' => 'textfield',
    '#default_value' => isset($store->follow_count) ? $store->follow_count->value : '',
  );  
  $form['user_num'] = array(
    '#title' => '会员总数',
    '#type' => 'textfield',
    '#default_value' => isset($store->user_num) ? $store->user_num->value : '',
  );  
  $form['deal_count'] = array(
    '#title' => '消费总数',
    '#type' => 'textfield',
    '#default_value' => isset($store->deal_count) ? $store->deal_count->value : '',
  );  
  //$cities = variable_get('host_cities', array());
  //$default_city_id = $cities[$_SERVER['HTTP_HOST']]; 
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '保存',
  );
  return $form;
}

function admin_store_edit_form_js($form, &$form_state) {
  return $form['store-wrapper'];
}

function admin_store_edit_form_district_js($form, &$form_state) {
  return $form['template_wrapper'];
}

function admin_store_edit_form_catalog_js($form, &$form_state) {
  return $form['catalog_template_wrapper'];
}

function admin_store_edit_form_validate($form, &$form_state) {
  $sid = $form_state['values']['store_id'];
  $store_name = $form_state['values']['name'];
  $name = $form_state['values']['uid'];
  $discount = $form_state['values']['discount'];
  $follow_count = $form_state['values']['follow_count'];
  $user_num = $form_state['values']['user_num'];
  $deal_count = $form_state['values']['deal_count'];
  $district_id = $form_state['values']['district_id'];
  
  if ($store_name == '') {
    form_set_error('name', '请填写商家名称！');
    return;
  }
  if (store_name_repeat($store_name, $sid)) {
    form_set_error('name', '商家名已存在！');
  }
  if ($name == '0') {
    $form_state['values']['uid'] = 0;
  } else {
    $account = user_load_by_name($name);
    if (!$account) {
      form_set_error('uid', '用户帐号不存在！');
      return;
    } else {
      $form_state['values']['uid'] = $account->id();
    }
  }
  if ($discount >= 0 && $discount <= 10) {
    $form_state['values']['discount'] = number_format($discount, 1);
  } else {
    form_set_error('discount', '折扣请用大于0，小于10的小数!');
  }
  if (!is_numeric($follow_count) || (int)$follow_count < 0) {
    form_set_error('follow_count', '请填写大于0的整数');
  }
  if (!is_numeric($user_num) || (int)$user_num < 0) {
    form_set_error('user_num', '请填写大于0的整数');
  }
  if (!is_numeric($deal_count) || (int)$deal_count < 0) {
    form_set_error('deal_count', '请填写大于0的整数');
  }

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

function admin_store_edit_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  $sid = $form_state['values']['store_id'];
  if ($values['begin'] && $values['over']) {
    $values['hours'] = $values['begin'] . '-' . $values['over'];
  }
  $array = array(
    'name' => $values['name'], 
    'uid' => $values['uid'], 
    'latitude' => $values['latitude'], 
    'longitude' => $values['longitude'], 
    'address' => $values['address'], 
    'phone' => $values['phone'], 
    'discount' => $values['discount'], 
    'hours' => $values['hours'], 
    'cid' => $values['cid'], 
    'city_id' => $values['city_id'], 
    'district_id' => $values['district_id'], 
    'follow_count' => $values['follow_count'], 
    'user_num' => $values['user_num'], 
    'deal_count' => $values['deal_count'], 
    'update_at' => time(),
  );

  if ($sid) {
    $store = store_load($sid);
    foreach ($array as $key => $value) {
      $store->{$key} = $value;
    }
    $store->save();
  } else {
    $array['created'] = time();
  
    $store = entity_create('store', $array);
    $store->enforceIsNew();
    $store->save();
  }

  $file = $form_state['values']['picture_upload'];
  if ($file) {
    $file->setTemporary();
    $store->setPicture($file);
    $store->save();
  }
}

function admin_store_delete($store) {
  db_delete('store_account_follow')
    ->condition('sid', $store->id())
    ->execute();
  db_delete('store_revision')
    ->condition('sid', $store->id())
    ->execute();
  db_delete('stores')
    ->condition('sid', $store->id())
    ->execute();
  /*if ($cache = cache('Backend.Memcache')->get('Memcache')) { 
    $sid = $_SERVER['SERVER_NAME'] . ':store:id:' . $store->id();
    $cache->delete($sid);
  }*/
  drupal_goto('admin/store/list');
}

/**
 * store_js ajax
 */
function store_js($action) {
  switch ($action) {
    case 'store_follow':    
      $user = \Drupal::currentUser();
      $store_id = $_POST['store_id'];
      $user_id = $user->id();
      $followed = $_POST['option'];
      if ($followed) {
        store_account_follow_delete($user_id, $store_id);
      } else {
        store_account_follow_save($user_id, $store_id);
      }
      return new JsonResponse($followed); 
      break;
    case 'user':
      $matches = array();
      $string = $_GET['q'];
      if (!empty($string)) {
        $result = db_select('users', 'u')
          ->fields('u', array('uid', 'name'))
          ->condition('name', db_like($string) . '%', 'LIKE')
          ->range(0,10)
          ->execute();
         foreach ($result as $row) {
           $matches[$row->name] = check_plain($row->name);
         } 
      }
      return new JsonResponse($matches);
      break;
  }
}

/**
 * page callback for: store/%store/register
 */
function admin_store_register_user_form($form, &$form_state, $store) {
  $form['store_id'] = array(
    '#type' => 'value',
    '#value' => isset($store) ? $store->id() : 0,
  );
  $form['name'] = array(
    '#title' => '商户名称',
    '#type' => 'textfield',
    '#attributes' => array('disabled' => 'disabled'),
    '#default_value' => isset($store) ? $store->label() : '',
  );
  $form['user_name'] = array(
    '#title' => '用户账户',
    '#type' => 'textfield',
  );
  $form['pass'] = array(
    '#title' => '用户密码',
    '#type' => 'textfield',
  );
  $form['send_msg'] = array(
    '#title' => '是否发送密码',
    '#type' => 'checkbox',
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '创建用户',
  );
  return $form;
}

function admin_store_register_user_form_validate($form, &$form_state) {
  $sid = $form_state['values']['store_id'];
  $store = store_load($sid);
  if ($store->uid->value) {
    form_set_error('user_name', '该商户已经关联用户,不可重新创建!');
    return;
  }
  $name = $form_state['values']['user_name'];
  if (!$name) {
    form_set_error('user_name', '请输入用户名!');
    return;
  }
  if (!preg_match('/^1[3458][0-9]{9}$/', $name)) {
    form_set_error('user_name', '请输入正确的用户名(手机号)!');
    return;
  }
  if (!$form_state['values']['pass']) {
    form_set_error('pass', '请输入密码!');
    return;
  }
  if (!preg_match('/^\d{6,12}$/',$form_state['values']['pass'])) {
    form_set_error('pass', '请输入6到12位数字型密码!');
    return;
  }
  $account = user_load_by_name($name);
  if (is_object($account) && $account->id()) {
    form_set_error('user_name', '用户名已存在!');
  }
}

function admin_store_register_user_form_submit($form, &$form_state) {
  $store = store_load($form_state['values']['store_id']);
  $name = $form_state['values']['user_name'];
  $pass = $form_state['values']['pass'];
  $array = array(
    'name' => $name,
    'pass' => $pass,
    'status' => 1,
    'created' => time(),
  );
  $account = entity_create('user', $array);
  $account->enforceIsNew();
  //$account = new User($array, 'user');
  $account->save();
  
  $store->uid = $account->id();
  $store->save();
  //store_save($store, array('uid' => $account->id()));

  if ($form_state['values']['send_msg']) {
    $content = "尊敬的商家您好,您的贵客登录账号为此手机号,登录密码为$pass.贵客商户版下载:http://is.gd/VOlBmu [贵客]";
    account_send_msg($content, $name);
  }
  drupal_set_message(t('商户 %title 认领成功!', array('%title' => $store->label())));
  $form_state['redirect'] = 'store/' . $store->id() . '/edit';

}

