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
  $request = \Drupal::request();
  drupal_add_js(drupal_get_path('module', 'ec') . '/js/jquery-ui.custom.js', 'file');
  drupal_add_css(drupal_get_path('module', 'ec') . '/css/jquery-ui.css', 'file');
  drupal_add_js(drupal_get_path('module', 'ec') . '/js/jquery.ui.datepicker-zh-CN.js', 'file');

  drupal_add_js(drupal_get_path('module', 'store') . '/js/admin-store-list.js');
  global $base_url;
  $keyword = check_plain($request->query->get('keyword', ''));
  $account_name = check_plain($request->query->get('account', ''));
  $city_id = check_plain($request->query->get('city_id', 0));
  $start = check_plain($request->query->get('start', ''));
  $expire = check_plain($request->query->get('expire', ''));

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
    '#default_value' => $request->query->get('keyword', ''),
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
function store_js($action,Response $request) {
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
      $string = $request->query->get('q');
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

