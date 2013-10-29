<?php

namespace Drupal\account\Form;

use Drupal\Core\Form\FormBase;

class EditStoreImageForm extends FormBase {

  public function getFormId() {
    return 'account_edit_storeimage_form';
  }

  public function buildForm(array $form, array &$form_state) {
    $form['#theme'] = array('account_admin');
    $form['#title'] = '商家形象';	


    $form['phone'] = array(
      '#type' => 'textfield',
      '#title' => '我的手机号：',
      '#size' => 60,
      '#maxlength' => USERNAME_MAX_LENGTH,
      '#required' => TRUE,
      '#attributes' => array(
        'autocorrect' => 'off',
        'autocapitalize' => 'off',
        'spellcheck' => 'false',
        'autofocus' => 'autofocus',
        'class' => array('text', 'r3'),
      ),
      '#field_suffix' => '<a href="javascript:void();" class="sj_yzm">点击获取验证码</a>',
    );
    $form['check_code'] = array(
      '#type' => 'textfield',
      '#title' => '手机验证：',
      '#size' => 6,
      '#maxlength' => USERNAME_MAX_LENGTH,
      '#required' => TRUE,
      '#attributes' => array(
        'class' => array('text', 'r3', 'yz'),
      ),
      '#field_suffix' => '<span class="time-waiting"></span><a href="javascript:void();" class="cf" style="display:none;">点击重发</a>',
    );
      $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '立即绑定',
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
    $account = entity_load('user', $user->id());
    $account->phone->value = $form_state['values']['new_phone'];
    $account->save();
    drupal_set_message("手机已绑定");
  }
}
