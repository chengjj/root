<?php

/**
 * @file
 * Definition of Drupal\adv_block\Entity\AdvBlock.
 */

namespace Drupal\adv_block\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\adv_block\AdvBlockInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines the adv_block entity class.
 *
 * @EntityType(
 *   id = "adv_block",
 *   label = "广告位",
 *   module = "adv_block",
 *   controllers = {
 *     "storage" = "Drupal\adv_block\AdvBlockStorageController"
 *   },
 *   base_table = "adv_blocks",
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "bid",
 *     "label" = "title"
 *   }
 * )
 */
class AdvBlock extends ContentEntityBase implements AdvBlockInterface {

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('bid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $properties['bid'] = array(
      'label' => t('ID'),
      'description' => t('The adv_block ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['type'] = array(
      'label' => 'Type',
      'type' => 'string_field',
    );
    $properties['title'] = array(
      'label' => 'Title',
      'type' => 'string_field',
    );
    return $properties;
  }
}
