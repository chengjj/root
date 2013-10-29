<?php

/**
 * @file
 * Definition of Drupal\store\StoreCommentFormController.
 */

namespace Drupal\store;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityFormController;

/**
 * Base for controller for comment forms.
 */
class StoreCommentFormController extends ContentEntityFormController {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   */
  public function form(array $form, array &$form_state) {
    $user = \Drupal::currentUser();
    $comment = $this->entity;
    $store = $comment->sid->entity;

    $form['rank'] = array(
      '#type' => 'radios',
      '#title' => '评价',
      '#title_display' => 'invisible',
      '#options' => array(
        1 => '好评',
        0 => '差评',
      ),
      '#default_value' => 1,
    );
    $form['subject'] = array(
      '#type' => 'textarea',
      '#title' => '内容',
      '#title_display' => 'invisible',
      '#maxlength' => 64,
      '#default_value' => $comment->subject->value,
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
   * Overrides EntityFormController::buildEntity().
   */
  public function buildEntity(array $form, array &$form_state) {
    $comment = parent::buildEntity($form, $form_state);
    if (!empty($form_state['values']['date']) && $form_state['values']['date'] instanceOf DrupalDateTime) {
      $comment->created->value = $form_state['values']['date']->getTimestamp();
    }
    else {
      $comment->created->value = REQUEST_TIME;
    }
    return $comment;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, array &$form_state) {
    $store = store_load($form_state['values']['sid']);
    $comment = $this->entity;

    $comment->save();
    $form_state['values']['cid'] = $comment->id();

    drupal_set_message(t('评论已保存.'));
    // Redirect to the newly posted comment.
    $redirect = array('store/' . $store->id(), array('fragment' => 'comment-' . $comment->id()));
    $form_state['redirect'] = $redirect;
    // Clear the block and page caches so that anonymous users see the comment
    // they have posted.
    cache_invalidate_tags(array('content' => TRUE));
  }
}
