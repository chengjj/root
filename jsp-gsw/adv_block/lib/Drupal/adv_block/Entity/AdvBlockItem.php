<?php

/**
 * @file
 * Definition of Drupal\adv_block\Entity\AdvBlockItem.
 */

namespace Drupal\adv_block\Entity;

use Drupal\adv_block\AdvBlockItemInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines the adv_block_item entity class.
 *
 * @EntityType(
 *   id = "adv_block_item",
 *   label = "广告位项目",
 *   module = "adv_block",
 *   controllers = {
 *     "storage" = "Drupal\adv_block\AdvBlockItemStorageController",
 *     "view_builder" = "Drupal\adv_block\AdvBlockItemRenderController",
 *     "form" = {
 *       "default" = "Drupal\adv_block\AdvBlockItemFormController",
 *       "delete" = "Drupal\adv_block\Form\DeleteForm"
 *     }
 *   },
 *   base_table = "adv_block_items",
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "iid",
 *     "label" = "title"
 *   },
 *   links = {
 *     "edit-form" = "/admin/adv_block_item/{adv_block_item}/edit"
 *   }
 * )
 */
class AdvBlockItem extends ContentEntityBase implements AdvBlockItemInterface {

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('iid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $properties['iid'] = array(
      'label' => 'ID',
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['bid'] = array(
      'label' => '广告位 ID',
      'type' => 'entity_reference_field',
      'settings' => array('target_type' => 'adv_block'),
    );
    $properties['type'] = array(
      'label' => '内容类型',
      'type' => 'string_field',
    );
    $properties['entity_id'] = array(
      'label' => t('Entity ID'),
      'type' => 'integer_field',
    );
    $properties['title'] = array(
      'label' => '标题',
      'type' => 'string_field',
      'settings' => array('default_value' => ''),
    );
    $properties['picture'] = array(
      'label' => '图片',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'file',
        'default_value' => 0,
      ),
    );
    $properties['reason'] = array(
      'label' => '推荐理由',
      'type' => 'string_field',
    );
    $properties['weight'] = array(
      'label' => '排列顺序',
      'type' => 'integer_field',
      'settings' => array('default_value' => 0),
    );
    return $properties;
  }

}
