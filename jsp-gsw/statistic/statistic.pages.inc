<?php
use Symfony\Component\HttpFoundation\JsonResponse;
/** 
 * Access api/statistic
 */
function statistic_create_page() {
  $json_data = file_get_contents('php://input');
  $array = json_decode($json_data, TRUE);
  $status = 404;
  if (isset($json_data) && $json_data) {
    $date_raw = isset($array['date']) ? $array['date'] : REQUEST_TIME;
    $deviceId = isset($array['deviceId']) ? $array['deviceId'] : '';
    $deviceName = isset($array['deviceName']) ? $array['deviceName'] : '';
    $osVersion = isset($array['osVersion']) ? $array['osVersion'] : '';
    $appVersion = isset($array['appVersion']) ? $array['appVersion'] : '';
    $lunchCount = isset($array['lunchCount']) ? $array['lunchCount'] : 0;
    $advertClicks = isset($array['advertClicks']) ? $array['advertClicks'] : array();

    $date = date('Y-m-d', strtotime($date_raw));
    $data_statistic = array(
      'date' => $date,
      'device_id' => $deviceId,
      'device_name' => $deviceName,
      'os_version' => $osVersion,
      'app_version' => $appVersion,
      'lunch_count' => $lunchCount,
    );
    $query = db_select('statistic', 's')
      ->fields('s', array('statistic_id'))
      ->condition('date', $date)
      ->condition('device_id', $deviceId)
      ->condition('device_name', $deviceName)
      ->condition('os_version', $osVersion)
      ->condition('app_version', $appVersion)
      ->orderBy('statistic_id', 'DESC')
      ->execute()
      ->fetchCol();
    if (!empty($query)) {
      foreach ($query as $row) {
        db_update('statistic')
          ->fields(array(
            'lunch_count' => $lunchCount,
          ))
          ->condition('statistic_id', $row)
          ->execute();
      }
    } 
    else 
      drupal_write_record('statistic', $data_statistic);
    foreach ($advertClicks as $advertID => $clicks) {
      $data_advert_count = array(
        'date' => $date,
        'device_id' => $deviceId,
        'advert_id' => $advertID,
        'count' => $clicks,
      );
      $query = db_select('advert_count', 'a')
        ->fields('a', array('aid'))
        ->condition('date', $date)
        ->condition('device_id', $deviceId)
        ->condition('advert_id', $advertID)
        ->execute()
        ->fetchCol();
      if (!empty($query)) {
        foreach ($query as $row) {
          db_update('advert_count')
            ->fields(array(
              'count' => $clicks,
            ))
            ->condition('aid', $row)
            ->execute();
        }
      }
      else 
        drupal_write_record('advert_count', $data_advert_count);
    }
    $status = 204;
    return new JsonResponse(array('message' => '成功添加统计信息'), $status);
  }
}

/**
 * Access admin/statistic
 */
function admin_statistic_form($form, &$form_state) {
  drupal_add_js('core/misc/ui/ui/jquery-ui.custom.js', 'file');
  drupal_add_css('core/misc/ui/themes/base/jquery-ui.css', 'file');
  drupal_add_js('core/misc/ui/ui/i18n/jquery.ui.datepicker-zh-CN.js', 'file');

  drupal_add_js(drupal_get_path('module', 'statistic') . '/admin_statistic_form.js');
  $query_statistic = db_select('statistic', 's')
    ->fields('s')
    ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
    ->limit(10)
    ->orderBy('statistic_id', 'DESC');

  $header_statistic = array(
    'date' => array('data' => '日期', 'field' => 'date', 'sort' => 'desc'),
    'device_id' => array('data' => '设备号'),
    'device_name' => array('data' => '设备名'),
    'os_version' => array('data' => '操作系统版本'),
    'app_version' => array('data' => 'APP版本'),
    'lunch_count' => array('data' => '启动次数'),
  );
  $header_total = array(
    'date' => array('data' => '日期', 'field' => 'date', 'sort' => 'desc'),
    'city' => array('data' => '城市'),
    'device_id' => array('data' => '设备号'),
    'advert_id' => array('data' => '广告名称'),
    'count' => array('data' => '广告总点击数'),
  );

  $form['filter_statistic'] = array(
    '#type' => 'fieldset',
    '#title' => '贵客统计信息过滤',
    '#collapsible' => TRUE,
    '#collased' => TRUE,
  );
  $form['filter_statistic']['statistic_date_start'] = array(
    '#type' => 'textfield',
    '#title' => '开始日期',
    '#default_value' => isset($_GET['statistic_date_start']) ? $_GET['statistic_date_start'] : '',
    '#description' => '时间格式如:2013-07-08, 2013/07/08, 2013/7/8',
  );
  $form['filter_statistic']['statistic_date_expire'] = array(
    '#type' => 'textfield',
    '#title' => '截止日期',
    '#default_value' => isset($_GET['statistic_date_expire']) ? $_GET['statistic_date_expire'] : '',
    '#description' => '时间格式如:2013-07-08, 2013/07/08, 2013/7/8',
  );  
  $form['filter_statistic']['statistic_submit'] = array(
    '#type' => 'submit',
    '#value' => '搜索',
  );  
  $statistic_date_start = isset($_GET['statistic_date_start']) ? $_GET['statistic_date_start'] : '';
  $statistic_date_expire = isset($_GET['statistic_date_expire']) ? $_GET['statistic_date_expire'] : '';

  $options_statistic = array();
  if (!empty($statistic_date_start)) 
    $query_statistic->condition('date', date('Y-m-d', strtotime($statistic_date_start)), '>');
  if (!empty($statistic_date_expire)) 
    $query_statistic->condition('date', date('Y-m-d', strtotime($statistic_date_expire)), '<');
  $result_statistic = $query_statistic->execute();
  foreach ($result_statistic as $row) {
    $options_statistic[$row->statistic_id] = array(
      'date' => $row->date,
      'device_id' => $row->device_id,
      'device_name' => $row->device_name,
      'os_version' => $row->os_version,
      'app_version' => $row->app_version,
      'lunch_count' => $row->lunch_count,
    ); 
  }  
  $form['filter_statistic']['statistic'] = array(
    '#type' => 'tableselect',
    '#header' => $header_statistic,
    '#options' => $options_statistic,
    '#empty' => '没有统计信息',
  );
  $current_device_query = db_select('statistic', 's')
    ->fields('s')
    ->groupBy('device_id')
    ->orderBy('statistic_id', 'DESC');
  if (!empty($statistic_date_start)) 
    $current_device_query->condition('date', date('Y-m-d', strtotime($statistic_date_start)), '>');
  if (!empty($statistic_date_expire)) 
    $current_device_query->condition('date', date('Y-m-d', strtotime($statistic_date_expire)), '<');    
  $current_device = $current_device_query->execute();
  $current_device_count =  $current_device_lunch_count = 0;
  foreach ($current_device as $row) { 
    $current_device_count += 1;
    $current_device_lunch_count += $row->lunch_count;
  }
  $form['filter_statistic']['device_count'] = array(
    '#markup' => '设备使用数: <b><font color="red">' . $current_device_count . '</font></b>;  设备总启动次数: <b><font color="red">' . $current_device_lunch_count . '</font></b>;',
  );

  $query_advert = db_select('advert_count', 'a')
    ->fields('a')
    ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
    ->limit(10)
    ->orderBy('aid', 'DESC');
  $options_advert = array(); 
  $form['filter_advert'] = array(
    '#type' => 'fieldset',
    '#title' => '贵客广告信息过滤',
    '#collapsible' => TRUE,
    '#collased' => TRUE,
  );
  if (isset($_GET['advert_aid'])) {
    $adv = adv_load($_GET['advert_aid']);
  }
  $form['filter_advert']['advert_aid'] = array(
    '#type' => 'textfield',
    '#title' => '广告名称',
    '#default_value' => isset($adv) ? $adv->title : '',
    '#autocomplete_path' => 'adv/js/adv',
    '#ajax' => array(
      'callback' => 'statistic_adv_js',
      'wrapper'  => 'statistic-wrapper',
      'effect'   => 'fade',
    ),    
  );
  $form['statistic_wrapper'] = array(
    '#prefix' => '<div id="statistic-wrapper">',
    '#suffix' => '</div>',
  );  
  $form['filter_advert']['advert_date_start'] = array(
    '#type' => 'textfield',
    '#title' => '开始日期',
    '#default_value' => isset($_GET['advert_date_start']) ? $_GET['advert_date_start'] : '',
    '#description' => '时间格式如:2013-07-08, 2013/07/08, 2013/7/8',
  );  
  $form['filter_advert']['advert_date_expire'] = array(
    '#type' => 'textfield',
    '#title' => '截止日期',
    '#default_value' => isset($_GET['advert_date_expire']) ? $_GET['advert_date_expire'] : '',
    '#description' => '时间格式如:2013-07-08, 2013/07/08, 2013/7/8',
  ); 
  $form['filter_advert']['advert_submit'] = array(
    '#type' => 'submit',
    '#value' => '广告过滤',
  );
  $advert_date_start = isset($_GET['advert_date_start']) ? $_GET['advert_date_start'] : '';
  $advert_date_expire = isset($_GET['advert_date_expire']) ? $_GET['advert_date_expire'] : '';
  $advert_aid = isset($_GET['advert_aid']) ? $_GET['advert_aid'] : '';
  if (!empty($advert_date_start)) {
    $query_advert->condition('date', date('Y-m-d', strtotime($advert_date_start)), '>');
  }
  if (!empty($advert_date_expire)) {
    $query_advert->condition('date', date('Y-m-d', strtotime($advert_date_expire)), '<');
  }
  if (!empty($advert_aid)) {
    $query_advert->condition('advert_id', $advert_aid);
  }
  $result_advert = $query_advert->execute();
  foreach ($result_advert as $row) {
    $advert = adv_load($row->advert_id);
    $city = taxonomy_term_load($advert->cid);
    $options_advert[$row->aid] = array(
      'date' => $row->date,
      'city' => $city->name,
      'device_id' => $row->device_id,
      'advert_id' => l($advert->title, 'adv/edit/' . $advert->aid),
      'count' => $row->count,
    );
  }
  $form['filter_advert']['total'] = array(
    '#type' => 'tableselect',
    '#header' => $header_total,
    '#options' => $options_advert,
    '#empty' => '没有统计信息',
  );
  $current_adv_query = db_select('advert_count', 'a')
    ->fields('a', array('count'));
  if (!empty($advert_aid)) {
    $current_adv_query->condition('advert_id', $advert_aid);
  }
  if (!empty($advert_date_start)) {
    $current_adv_query->condition('date', date('Y-m-d', strtotime($advert_date_start)), '>');
  }
  if (!empty($advert_date_expire)) {
    $current_adv_query->condition('date', date('Y-m-d', strtotime($advert_date_expire)), '<');
  }  
  $current_adv = $current_adv_query->execute();
  $current_adv_count = 0;
  foreach ($current_adv as $row) {
    $current_adv_count += $row->count;
  }
  $form['filter_advert']['general'] = array(
    '#markup' => '当前广告总点击数: <b><font color="red">' . $current_adv_count . '</font></b>;',
  );
  return $form;
}

function admin_statistic_form_validate($form, &$form_state) {
  $adv_title = $form_state['values']['advert_aid'];
  if (!empty($adv_title)) {
    $adv = adv_load_by_title($adv_title);
    $form_state['values']['advert_aid'] = $adv->aid;
  }
}

function admin_statistic_form_submit($form, &$form_state) {
  $op = $form_state['values']['op'];
  switch ($op) {
    case '搜索':
      $date_start = isset($form_state['values']['statistic_date_start']) ? $form_state['values']['statistic_date_start'] : '';
      $date_expire = isset($form_state['values']['statistic_date_expire']) ? $form_state['values']['statistic_date_expire'] : '';
      $form_state['redirect'] = array('admin/statistic', array(
        'query' => array(
          'statistic_date_start' => $date_start,
          'statistic_date_expire' => $date_expire,
        ),
      ));
      break;
    case '广告过滤':
      $aid = isset($form_state['values']['advert_aid']) ? check_plain($form_state['values']['advert_aid']) : '';
      $date_start  = isset($form_state['values']['advert_date_start']) ? check_plain($form_state['values']['advert_date_start']) : '';
      $date_expire  = isset($form_state['values']['advert_date_expire']) ? check_plain($form_state['values']['advert_date_expire']) : '';
      $form_state['redirect'] = array('admin/statistic', array(
        'query' => array(
          'advert_aid' => $aid, 
          'advert_date_start' => $date_start,
          'advert_date_expire' => $date_expire,
        ),
      ));
    break;
  }
}

function statistic_adv_js($form, &$form_state) {
    return $form['statistic_wrapper'];
}
