<?php

/**
 * @file
 * Definition of Drupal\store\StoreFormController.
 */

namespace Drupal\store;

use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityFormController;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\FieldInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base for controller for store forms.
 */
class StoreFormController extends ContentEntityFormController {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   */
  public function form(array $form, array &$form_state) {
    $store = $this->entity;

    if ($store->isNew()) {
      $store->city_id->value = $default_city_id;
    }

    $form['name'] = array(
      '#title' => '商家名称',
      '#type' => 'textfield',
      '#default_value' => $store->label(),
    );
    $form['uid'] = array(
      '#title' => '用户ID',
      '#type' => 'textfield',
      '#default_value' => $store->uid->value,
      '#description' => '创建商户不指定用户则输入0',
    );
    $picture = $store->getPicture();
    $form['image_url'] = array(
      '#type' => 'managed_file',
      '#title' => '图片',
      '#upload_location' => 'public://store/',
      '#default_value' => $picture ? array($picture->id()) : NULL,
    );
    $form['latitude'] = array(
      '#title' => '纬度',
      '#type' => 'textfield',
      '#default_value' => $store->latitude->value,
    );
    $form['longitude'] = array(
      '#title' => '经度',
      '#type' => 'textfield',
      '#default_value' => $store->longitude->value,
    );
    $form['address'] = array(
      '#title' => '地址',
      '#type' => 'textfield',
      '#default_value' => $store->address->value,
    );
    $form['phone'] = array(
      '#title' => '电话',
      '#type' => 'textfield',
      '#default_value' => $store->phone->value,
    );
    $form['discount'] = array(
      '#title' => '折扣',
      '#type' => 'textfield',
      '#default_value' => $store->discount->value,
    ); 
    $form['hours'] = array(
      '#title' => '营业时间',
      '#type' => 'textfield',
      '#default_value' => $store->hours->value,
    ); 

    $parent_cid = 0;
    if (isset($form_state['values']['parent_cid'])) {
      $parent_cid = $form_state['values']['parent_cid'];
    }
    else if (isset($store->cid) && $store->cid->value) {
      $parent_catalog = store_catalog_get_top($store->cid->value);
      $parent_cid = $parent_catalog->id();
    }
    $parent_catalogs = store_catalog_names(0);
    if (!$parent_cid) {
      $parent_catalog_keys = array_keys($parent_catalogs);
      $parent_cid = $parent_catalog_keys[0];
    }
    $form['parent_cid'] = array(
      '#title' => '分类',
      '#type' => 'select',
      '#options' => $parent_catalogs,
      '#default_value' => isset($parent_cid) ? $parent_cid : 0,
      '#ajax' => array(
        'callback' => array($this, 'catalogSwitch'),
        'wrapper' => 'edit-store-catalog-wrapper',
      ),
    );
    $form['cid'] = array(
      '#type' => 'select',
      '#default_value' => $store->cid->value,
      '#options' => store_catalog_names($parent_cid),
      '#prefix' => '<div id="edit-store-catalog-wrapper">',
      '#suffix' => '</div>',
    );

    $city_id = city_get_current_city_id();
    if (isset($form_state['values']['city_id'])) {
      $city_id = $form_state['values']['city_id'];
    }
    else if ($store->city_id->value) {
      $city_id = $store->city_id->value;
    }
    $form['city_id'] = array(
      '#title' => '城市',
      '#type' => 'select',
      '#options' => city_names(),
      '#default_value' => $city_id,
      '#ajax' => array(
        'callback' => array($this, 'citySwitch'),
        'wrapper' => 'edit-store-district-wrapper',
      ),
    ); 
    $form['district_id'] = array(
      '#type' => 'select',
      '#title' => '区域',
      '#default_value' => $store->district_id->value,
      '#options' => city_district_names($city_id),
      '#prefix' => '<div id="edit-store-district-wrapper">',
      '#suffix' => '</div>',
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

    $form['#id'] = drupal_html_id('store_form');
    $form['#theme'] = array('store_form');

    foreach (array('sid') as $key) {
      $form[$key] = array('#type' => 'value', '#value' => $store->$key->value);
    }
    $form['#validate'][] = array($this, 'validateForm');

    return parent::form($form, $form_state, $store);
  }

  public function validateForm(array &$form, array &$form_state) {
    $sid = $form_state['values']['sid'];
    $store_name = $form_state['values']['name'];
    $uid = $form_state['values']['uid'];
    $discount = $form_state['values']['discount'];
    $follow_count = $form_state['values']['follow_count'];
    $user_num = $form_state['values']['user_num'];
    $deal_count = $form_state['values']['deal_count'];
    $district_id = $form_state['values']['district_id'];
    
    if ($store_name == '') {
      form_set_error('name', '请填写商家名称！');
      return;
    }
    if (!$form_state['values']['sid'] && store_name_repeat($store_name, $sid)) {
      form_set_error('name', '商家名已存在！');
    }
    if ($uid != '0') {
      $account = user_load($uid);
      if (!$account) {
        form_set_error('uid', '用户帐号不存在！');
        return;
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
  }

  public function citySwitch($form, &$form_state) {
    $form['district_id']['#options'] = city_district_names($form_state['values']['city_id']);
    return $form['district_id'];
  }

  public function catalogSwitch($form, &$form_state) {
    $form['cid']['#options'] = store_catalog_names($form_state['values']['parent_cid']);
    return $form['cid'];
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::actions().
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = '保存';
    return $actions;
  }

  /**
   * Overrides EntityFormController::buildEntity().
   */
  public function buildEntity(array $form, array &$form_state) {
    $store = parent::buildEntity($form, $form_state);
    if ($store->isNew()) {
      $user = \Drupal::currentUser();
      
      $store->uid->value = $user->id();
      $store->created->value = REQUEST_TIME;
    }
    $store->update_at->value = REQUEST_TIME;
    return $store;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, array &$form_state) {
    $store = $this->entity;

    $store->save();
    $form_state['values']['sid'] = $store->id();

    drupal_set_message('商家信息已保存.');
  }
}
