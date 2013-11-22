<?php
/**
 * @file
 * User page callback file for the user module.
 */
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Plugin\Core\Entity\User;
use Drupal\taxonomy\Plugin\Core\Entity\Term;
use Drupal\node\Plugin\Core\Entity\Node;

/**
 * access callback api/coupons/pending
 */
function coupons_pending_form($form, &$form_state) {
  $header = array(
    'title' => array('data' => '促销标题'), 
    'store' => array('data' => '商家'),
    'created' => array('data' => '发布时间'),
    'status' => array('data' => '促销状态'),
    'opers'  => array('data' => '操作'),
  );
  $query = db_select('coupons', 'c')
            ->fields('c')
            ->condition('c.status', 0);
  $result = $query->execute();
  $coupons = array();
  foreach ($result as $row) {
    $coupons[] = coupon_load($row->cid);
  }
  $options = array();
  foreach ($coupons as $coupon) {
    $store = $coupon->getStore();
    $options[$coupon->id()] = array(
      'title' => $coupon->label(),
      'store' =>  isset($store) ? $store->label() : '未知',
      'created' => date('Y-m-d H:i:s', $coupon->created->value),
      'status' => '未发布', 
      'opers' => l('编辑', 'coupon/' . $coupon->id() . '/edit', array('query' =>  drupal_get_destination())),
    );
  }
  $form['pending'] = array(
    '#type' => 'tableselect',
    '#header' => $header,
    '#options' => $options,
    '#empty' => "没有待审促销",
  ); 
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '审核',
  );
  return $form;
}

function coupons_pending_form_submit($form, &$form_state) {
  $values = $form_state['values']['pending'];
  foreach ($values as $cid => $value) {
    if ($value) {
      $coupon = coupon_load($cid);
      $coupon->status = COUPON_STATUS_PUBLISH;
      $coupon->save();
    }
  }
  drupal_set_message('操作已保存!');  
}

/**
 * Page callback: coupon/js/%
 */
function coupon_js($action,  Response $request) {
  switch ($action) {
    case 'store':
      $matches = array();
      $string = $request->query->get('q');
      if (!empty($string)) {
        $result = db_select('stores', 's')
          ->fields('s', array('name'))
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
