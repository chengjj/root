<?php

namespace Drupal\account\Form;

use Drupal\Core\Form\FormBase;

class EditAvatarForm extends FormBase {

  public function getFormId() {
    return 'account_edit_avatar';
  }

  public function buildForm(array $form, array &$form_state) {
    $user = \Drupal::currentUser();
    $account = user_load($user->id());

    $form['#theme'] = array('account_admin');
    $form['#title'] = '个人头像';

    $form['avatar'] = array(
      '#type' => 'managed_file',
      '#title' => '新头像',
      '#title_display' => 'invisible',
      '#theme' => 'account_avatar_upload',
      '#upload_location' => 'public://avatar/',
      '#default_value' => isset($form_state['values']['avatar']) ? $form_state['values']['avatar'] : null,
    );

    $form['submit'] = array('#type' => 'submit', '#value' => '保存');

    return $form;
  }

  public function submitForm(array &$form, array &$form_state) {
    $user = \Drupal::currentUser();
    if (isset($form_state['values']['avatar'][0])) {
      $file = file_load($form_state['values']['avatar'][0]);
      $account = account_load($user->id());

      if ($account->picture->value) {
        file_usage()->delete($account->picture->entity, 'account', 'picture', $account->id());
        $account->picture->value = 0;
      }

      $directory = 'public://account/';
      file_prepare_directory($directory, FILE_CREATE_DIRECTORY);
      $new_file = file_move($file, $directory . 'picture-' . $account->id());
      $account->picture = $new_file;
      $account->save();
      file_usage()->add($new_file, 'account', 'picture', $account->id());
    }
  }
}
