<?php

namespace Drupal\city\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\city\CityInterface;

/**
 * Defines the city entity class.
 *
 * @EntityType(
 *   id = "city",
 *   label = "城市",
 *   controllers = {
 *     "storage" = "Drupal\city\CityStorageController",
 *     "list" = "Drupal\jsp\JspEntityListController",
 *     "access" = "Drupal\jsp\JspEntityAccessController"
 *   },
 *   base_table = "cities",
 *   entity_keys = {
 *     "id" = "cid",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class City extends ContentEntityBase implements CityInterface {

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('cid')->value;
  }

  public static function baseFieldDefinitions($entity_type) {
    $properties['cid'] = array(
      'label' => t('ID'),
      'description' => t('The city ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => t('UUID'),
      'description' => t('The city UUID.'),
      'type' => 'uuid_field',
      'read-only' => TRUE,
    );
    $properties['name'] = array(
      'label' => '名称',
      'type' => 'string_field',
    );

    return $properties;
  }

}

