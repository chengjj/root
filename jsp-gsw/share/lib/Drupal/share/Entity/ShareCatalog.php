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
 *   label = "商品分类",
 *   controllers = {
 *     "storage" = "Drupal\share\ShareCatalogStorageController",
 *     "list" = "Drupal\jsp\JspEntityListController",
 *     "access" = "Drupal\jsp\JspEntityAccessController"
 *   },
 *   base_table = "share_catalog",
 *   entity_keys = {
 *     "id" = "cid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "weight" = "weight"
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
    $properties['uuid'] = array(
      'label' => t('UUID'),
      'description' => t('The share_catalog UUID.'),
      'type' => 'uuid_field',
      'read-only' => TRUE,
    );
    $properties['parent_cid'] = array(
      'label' => '上级分类',
      'type' => 'entity_reference_field',
      // Save new catalogs with no parents by default.
      'settings' => array(
        'target_type' => 'share_catalog',
        'default_value' => 0
      ),
    );
    $properties['name'] = array(
      'label' => '名称',
      'type' => 'string_field',
      'settings' => array('default_value' => ''),
    );
    $properties['weight'] = array(
      'label' => '排列顺序',
      'type' => 'integer_field',
    );
    return $properties;
  }

}
