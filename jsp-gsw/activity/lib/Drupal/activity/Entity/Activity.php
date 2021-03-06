<?php

/**
 * @file
 * Definition of Drupal\activity\Entity\Activity.
 */

namespace Drupal\activity\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\activity\ActivityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines the activity entity class.
 *
 * @EntityType(
 *   id = "activity",
 *   label = "动态",
 *   controllers = {
 *     "storage" = "Drupal\activity\ActivityStorageController",
 *     "view_builder" = "Drupal\activity\ActivityRenderController",
 *     "list" = "Drupal\jsp\JspEntityListController",
 *     "access" = "Drupal\jsp\JspEntityAccessController"
 *   },
 *   base_table = "activity",
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "aid",
 *     "label" = "param2",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class Activity extends ContentEntityBase implements ActivityInterface {

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('aid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthor() {
    return $this->get('uid')->entity;
  }
  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageControllerInterface $storage_controller) {
    parent::preSave($storage_controller);

    if ($this->isNew()) {
      if (empty($this->created->value)) {
        $this->created->value = REQUEST_TIME;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $properties['aid'] = array(
      'label' => 'ID',
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => t('UUID'),
      'description' => t('The activity UUID.'),
      'type' => 'uuid_field',
    );
    $properties['uid'] = array(
      'label' => '会员',
      'description' => t('The user ID of the activity author.'),
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $properties['sid'] = array(
      'label' => '店铺',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'store',
        'default_value' => 0,
      ),
    );
    $properties['type'] = array(
      'label' => t('Type'),
      'type' => 'integer_field',
    );
    $properties['param'] = array(
      'label' => t('Param'),
      'type' => 'integer_field',
    );
    $properties['created'] = array(
      'label' => t('Created'),
      'description' => t('The time that the activity was created.'),
      'type' => 'integer_field',
    );
    return $properties;
  }

}
