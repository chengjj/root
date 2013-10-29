<?php

/**
 * @file
 * Definition of Drupal\feedback\FeedbackFormController.
 */

namespace Drupal\feedback;

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
 * Base for controller for feedback forms.
 */
class FeedbackFormController extends ContentEntityFormController {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   */
  public function form(array $form, array &$form_state) {
    $feedback = $this->entity;

    $form['#title'] = '意见反馈';

    // Use #feedback-form as unique jump target, regardless of entity type.
    $form['#id'] = drupal_html_id('feedback_form');
    $form['#theme'] = array('feedback_form');

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => '姓名',
      '#maxlength' => 64,
      '#default_value' => $feedback->name->value,
    );
    $form['phone'] = array(
      '#type' => 'textfield',
      '#title' => '联系电话',
      '#maxlength' => 64,
      '#default_value' => $feedback->phone->value,
    );
    $form['email'] = array(
      '#type' => 'textfield',
      '#title' => 'Email',
      '#maxlength' => 64,
      '#default_value' => $feedback->email->value,
    );
    $form['body'] = array(
      '#type' => 'textarea',
      '#title' => '反馈问题',
      '#default_value' => $feedback->body->value,
    );

    return parent::form($form, $form_state, $feedback);
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
   * Overrides Drupal\Core\Entity\EntityFormController::validate().
   */
  public function validate(array $form, array &$form_state) {
    parent::validate($form, $form_state);

    if (empty($form_state['values']['body'])) {
      form_set_error('body', '请填写反馈问题！');
    }
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, array &$form_state) {
    $feedback = $this->entity;
    $feedback->save();
    drupal_set_message('反馈意见已经提交成功!');
  }
}
