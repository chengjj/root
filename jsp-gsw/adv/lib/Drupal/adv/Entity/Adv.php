<?php

/**
 * @file
 * Definition of Drupal\adv\Entity\Adv.
 */

namespace Drupal\adv\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\adv\AdvInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines the adv entity class.
 *
 * @EntityType(
 *   id = "adv",
 *   label = "广告",
 *   module = "adv",
 *   controllers = {
 *     "storage" = "Drupal\adv\AdvStorageController",
 *     "view_builder" = "Drupal\adv\AdvRenderController"
 *   },
 *   base_table = "advs",
 *   entity_keys = {
 *     "id" = "aid",
 *     "label" = "title"
 *   }
 * )
 */
class Adv extends ContentEntityBase implements AdvInterface {

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('aid')->value;
  }

  public function getPicture() {
    return $this->get('fid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $properties['aid'] = array(
      'label' => t('ID'),
      'description' => t('The adv ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['fid'] = array(
      'label' => '图片',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'file',
        'default_value' => 0,
      ),
    );
    $properties['title'] = array(
      'label' => 'Title',
      'type' => 'string_field',
    );
    $properties['redirect'] = array(
      'label' => 'Redirect',
      'type' => 'string_field',
    );
    $properties['cid'] = array(
      'label' => '广告地区city ID',
      'type' => 'integer_field',
    );
    $properties['sid'] = array(
      'label' => '商家ID',
      'type' => 'integer_field',
    );
    return $properties;
  }

}

