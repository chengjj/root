<?php

namespace Drupal\account\Form;

use Drupal\Core\Form\FormBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EditPasswordForm extends FormBase {

  public function getFormId() {
    return 'account_edit_password';
  }

  public function buildForm(array $form, array &$form_state) {
    $form['#theme'] = array('account_admin');
    $form['#title'] = '修改密码';

    $form['current_pass'] = array(
      '#type' => 'password',
      '#title' => '密码：',
      '#size' => 25,
      // Do not let web browsers remember this password, since we are
      // trying to confirm that the person submitting the form actually
      // knows the current one.
      '#attributes' => array('autocomplete' => 'off'),
    );
    $form['new_pass'] = array(
      '#type' => 'password',
      '#title' => '新密码：',
      '#size' => 25,
      '#attributes' => array('autocomplete' => 'off'),
    );
    $form['confirm_pass'] = array(
      '#type' => 'password',
      '#title' => '确认密码：',
      '#size' => 25,
      '#attributes' => array('autocomplete' => 'off'),
    );

    $form['submit'] = array('#type' => 'submit', '#value' => '保存');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
    $user = \Drupal::currentUser();
    $account = entity_load('user', $user->id());

    $password_hasher = \Drupal::service('password');
    if (!$password_hasher->check($form_state['values']['current_pass'], $account)) {
      form_set_error('current_pass', '请输入正确的旧密码');
    }
    if (empty($form_state['values']['new_pass'])) {
      form_set_error('new_pass', '请输入新的密码');
    }
    if ($form_state['values']['new_pass'] != $form_state['values']['confirm_pass']) {
      form_set_error('confirm_pass', '新密码和确认密码需要一致');
    }
  }

  public function submitForm(array &$form, array &$form_state) {
    $user = \Drupal::currentUser();
    $account = entity_load('user', $user->id());
    $account->pass->value = $form_state['values']['new_pass'];
    $account->save();
    drupal_set_message("密码已修改");
    $form_state['redirect'] = 'user/' . $user->id();
  }
}
