<?php
/**
 * @file
 * Contains \Drupal\account\Form\UserForm.
 */

namespace Drupal\account\Form;

/**
 * Temporary form controller for account module.
 */
class UserForm {

  /**
   * Wraps account_pass_reset().
   *
   * @todo Remove account_pass_reset().
   */
  public function resetPass($uid, $timestamp, $hash, $operation) {
    module_load_include('pages.inc', 'account');
    return drupal_get_form('account_pass_reset', $uid, $timestamp, $hash, $operation);
  }

}
