<?php

/**
 * @file
 * Definition of Drupal\share\ShareCommentFormController.
 */

namespace Drupal\share;

use Drupal\Core\Entity\ContentEntityFormController;

/**
 * Base for controller for comment forms.
 */
class ShareCommentFormController extends ContentEntityFormController {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   */
  public function form(array $form, array &$form_state) {
    $user = \Drupal::currentUser();
    $comment = $this->entity;

    $form['subject'] = array(
      '#type' => 'textarea',
      '#title' => '评论',
      '#attributes' => array('class' => array('wysiwyg')),
      '#default_value' => $comment->label(),
    );

    // Add internal comment properties.
    foreach (array('cid', 'sid') as $key) {
      $form[$key] = array('#type' => 'value', '#value' => $comment->$key->value);
    }
    $form['uid'] = array('#type' => 'value', '#value' => $user->id());

    return parent::form($form, $form_state, $comment);
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::actions().
   */
  protected function actions(array $form, array &$form_state) {
    $element = parent::actions($form, $form_state);

    $element['submit']['#value'] = '发表';

    return $element;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, array &$form_state) {
    if (!\Drupal::currentUser()->isAuthenticated()) {
      $form_state['redirect'] = array('login', array('query' => drupal_get_destination()));
      return;
    }

    $share = share_load($form_state['values']['sid']);
    $comment = $this->entity;
    
    $comment->save();
    $form_state['values']['cid'] = $comment->id();

    drupal_set_message('评论已发表');
    $form_state['redirect'] = array('share/' . $share->id(), array('fragment' => 'comment-' . $comment->id()));
    // Clear the block and page caches so that anonymous users see the comment
    // they have posted.
    cache_invalidate_tags(array('content' => TRUE));
  }

}

?>
