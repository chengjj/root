<?php

namespace Drupal\city\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\city\DistrictInterface;

/**
 * Defines the district entity class.
 *
 * @EntityType(
 *   id = "district",
 *   label = "区域",
 *   module = "city",
 *   controllers = {
 *     "storage" = "Drupal\city\DistrictStorageController",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder"
 *   },
 *   base_table = "districts",
 *   entity_keys = {
 *     "id" = "did",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class District extends ContentEntityBase implements DistrictInterface {

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('did')->value;
  }

  public static function baseFieldDefinitions($entity_type) {
    $properties['did'] = array(
      'label' => t('ID'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => t('UUID'),
      'type' => 'uuid_field',
      'read-only' => TRUE,
    );
    $properties['name'] = array(
      'label' => '名称',
      'type' => 'string_field',
    );
    $properties['cid'] = array(
      'label' => '城市',
      'type' => 'integer_field',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'city',
        'default_value' => 0,
      ),
    );
    $properties['weight'] = array(
      'label' => '排列顺序',
      'type' => 'integer_field',
      'settings' => array('default_value' => 0),
    );

    return $properties;
  }
}
?>
