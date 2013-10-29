<?php

namespace Drupal\account\Form;

use Drupal\Core\Form\FormBase;

class EditStoreForm extends FormBase {

  public function getFormId() {
    return 'account_edit_store_form';
  }

  public function buildForm(array $form, array &$form_state) {
    $form['#theme'] = array('account_admin');
    $form['#title'] = '商家基本信息';


    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => '商户名称：',
      '#size' => 60,
      '#value'=>'如“大虎老火锅，不带地名”',
      '#maxlength' => NAME_MAX_LENGTH,
      '#required' => TRUE,
      '#attributes' => array(
        'autocorrect' => 'off',
        'autocapitalize' => 'off',
        'spellcheck' => 'false',
        'autofocus' => 'autofocus',
        'class' => array('text', 'r3'),
      ),
    );
    $parent_cid = 0;
    if (isset($form_state['values']['parent_cid'])) {
      $parent_cid = $form_state['values']['parent_cid'];
    }
    else if (isset($store->cid) && $store->cid->value) {
      $parent_catalog = store_catalog_get_top($store->cid->value);
      $parent_cid = $parent_catalog->id();
    }
    $form['parent_cid'] = array(
      '#title' => '经营类别：',
      '#type' => 'select',
      '#options' => store_catalog_names(0),
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
    );
    
    $form['address'] = array(
      '#type' => 'textfield',
      '#title' => '门店地址：',
      '#size' => 60,
      '#value'=>'请输入完整准确的地址',
      '#maxlength' => NAME_MAX_LENGTH,
      '#required' => TRUE,
      '#attributes' => array(
        'autocorrect' => 'off',
        'autocapitalize' => 'off',
        'spellcheck' => 'false',
        'autofocus' => 'autofocus',
        'class' => array('text', 'r3'),
      ),
    );
    $form['phone'] = array(
      '#type' => 'textfield',
      '#title' => '联系电话：',
      '#size' => 60,
      '#value'=>'便于顾客预订服务',
      '#maxlength' => NAME_MAX_LENGTH,
      '#required' => TRUE,
      '#attributes' => array(
        'autocorrect' => 'off',
        'autocapitalize' => 'off',
        'spellcheck' => 'false',
        'autofocus' => 'autofocus',
        'class' => array('text', 'r3'),
      ),
    );
    $form['hours'] = array(
      '#type' => 'textfield',
      '#title' => '营业时间：',
      '#size' => 15,
      '#maxlength' => NAME_MAX_LENGTH,
      '#required' => TRUE,
      '#attributes' => array(
        'autocorrect' => 'off',
        'autocapitalize' => 'off',
        'spellcheck' => 'false',
        'autofocus' => 'autofocus',
        'class' => array('text', 'r3'),
      ),
    );
    $form['discount'] = array(
      '#type' => 'textfield',
      '#title' => '基础折扣：',
      '#size' => 15,
      '#maxlength' => NAME_MAX_LENGTH,
      '#required' => TRUE,
      '#attributes' => array(
        'autocorrect' => 'off',
        'autocapitalize' => 'off',
        'spellcheck' => 'false',
        'autofocus' => 'autofocus',
        'class' => array('text', 'r3'),
      ),
    );
    $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '保存',
    '#attributes' => array(
      'class' => array('sub'),
    ),
    '#prefix' => '<div class="ipt_sub">',
    '#suffix' => '</div>',
  );
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
    $phone = $form_state['values']['phone'];
  if (strlen($phone) != 11 || !preg_match('/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/', $phone)) {
    form_set_error('phone', '手机号码存在问题!');
    return;
  }
  $check_code = $form_state['values']['check_code'];
  if (strlen($check_code) != 6) {
    form_set_error('check_code', '请输入6位验证码!');
    return;
  }
  $code = account_generate_code($phone);
  if ($code != $check_code) {
    form_set_error('check_code', '输入的验证码不正确!');
    return;
  }
  }
  public function submitForm(array &$form, array &$form_state) {
    $user = \Drupal::currentUser();
    $account = entity_load('store', $user->id());
    $account->phone->value = $form_state['values']['new_phone'];
    $account->save();
    drupal_set_message("手机已绑定");
  }
}
