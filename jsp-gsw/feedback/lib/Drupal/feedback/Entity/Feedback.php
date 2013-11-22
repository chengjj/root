<?php

/**
 * @file
 * Definition of Drupal\feedback\Entity\Feedback.
 */

namespace Drupal\feedback\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\feedback\FeedbackInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines the feedback entity class.
 *
 * @EntityType(
 *   id = "feedback",
 *   label = "意见反馈",
 *   controllers = {
 *     "storage" = "Drupal\feedback\FeedbackStorageController",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list" = "Drupal\jsp\JspEntityListController",
 *     "access" = "Drupal\jsp\JspEntityAccessController",
 *     "form" = {
 *       "default" = "Drupal\feedback\FeedbackFormController",
 *       "delete" = "Drupal\feedback\Form\DeleteForm"
 *     },
 *   },
 *   base_table = "feedback",
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "fid",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/feedback/{feedback}",
 *     "edit-form" = "/feedback/{feedback}/edit"
 *   }
 * )
 */
class Feedback extends ContentEntityBase implements FeedbackInterface {

  public function id() {
    return $this->get('fid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $properties['fid'] = array(
      'label' => t('ID'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => t('UUID'),
      'description' => t('The feedback UUID.'),
      'type' => 'uuid_field',
    );
    $properties['uid'] = array(
      'label' => '会员',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $properties['name'] = array(
      'label' => t('Name'),
      'type' => 'string_field',
      'settings' => array('default_value' => ''),
    );
    $properties['phone'] = array(
      'label' => t('Name'),
      'type' => 'string_field',
      'settings' => array('default_value' => ''),
    );
    $properties['email'] = array(
      'label' => t('e-mail'),
      'description' => t("The feedback author's e-mail address."),
      'type' => 'string_field',
    );
    $properties['title'] = array(
      'label' => '内容',
      'type' => 'string_field',
    );
    $properties['created'] = array(
      'label' => t('Created'),
      'description' => t('The time that the feedback was created.'),
      'type' => 'integer_field',
    );
    return $properties;
  }

}
