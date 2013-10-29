<?php

/**
 * @file
 * Definition of Drupal\share\Entity\ShareCatalog.
 */

namespace Drupal\share\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\share\ShareCatalogInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines the share entity class.
 *
 * @EntityType(
 *   id = "share_catalog",
 *   label = "ShareCatalog",
 *   module = "share",
 *   controllers = {
 *     "storage" = "Drupal\share\ShareCatalogStorageController"
 *   },
 *   base_table = "share_catalog",
 *   entity_keys = {
 *     "id" = "cid",
 *     "label" = "name"
 *   }
 * )
 */
class ShareCatalog extends ContentEntityBase implements ShareCatalogInterface {

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('cid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $properties['cid'] = array(
      'label' => t('ID'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['parent_cid'] = array(
      'label' => t('Parent ID'),
      'type' => 'entity_reference_field',
      'settings' => array('target_type' => 'share_catalog'),
    );
    $properties['name'] = array(
      'label' => t('Name'),
      'type' => 'string_field',
      'settings' => array('default_value' => ''),
    );
    $properties['weight'] = array(
      'label' => t('Weight'),
      'type' => 'integer_field',
    );
    return $properties;
  }

}
