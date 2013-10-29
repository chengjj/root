<?php

/**
 * @file
 * Definition of Drupal\account\AccountFormController.
 */


namespace Drupal\account;

use Drupal\Core\Entity\ContentEntityFormController;

/**
 * Base for controller for account forms.
 */
class AccountFormController extends ContentEntityFormController {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   */
  public function form(array $form, array &$form_state) {
    $account = $this->entity;
    $user = user_load($account->id());

    $form['#theme'] = array('account_admin');
    $form['#title'] = '基本信息';

    $form['nickname'] = array(
      '#type' => 'textfield',
      '#title' => '昵称:',
      '#maxlength' => 64,
      '#default_value' => $account->nickname->value,
    );
    $form['sex'] = array(
      '#type' => 'radios',
      '#title' => '性别',
      '#default_value' => $account->sex->value,
      '#options' => array(
        0 => '男',
        1 => '女',
      ),
    );
    $form['district'] = array(
      '#type' => 'select',
      '#title' => '常居地',
      '#default_value' => $account->district->value,
      '#options' => city_district_names(),
    );
    $form['signature'] = array(
      '#type' => 'textarea',
      '#title' => '自我介绍',
      '#default_value' => $user->signature->value,
    );

    // Add internal account properties.
    foreach (array('uid') as $key) {
      $form[$key] = array('#type' => 'value', '#value' => $account->$key->value);
    }

    return parent::form($form, $form_state, $account);
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::actions().
   */
  protected function actions(array $form, array &$form_state) {
    $element = parent::actions($form, $form_state);

    $element['submit']['#value'] = '保存';
    unset($element['delete']);

    return $element;
  }

  public function validate(array $form, array &$form_state) {
    parent::validate($form, $form_state);

  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, array &$form_state) {
    $account = $this->entity;

    $account->save();
    $form_state['values']['uid'] = $account->id();

    $user = entity_load('user', $account->id());
    $user->signature->value = $form_state['values']['signature'];
    $user->save();

    drupal_set_message('基本信息已保存');

    // Clear the block and page caches so that anonymous users see the account
    // they have posted.
    cache_invalidate_tags(array('content' => TRUE));
  }
}

