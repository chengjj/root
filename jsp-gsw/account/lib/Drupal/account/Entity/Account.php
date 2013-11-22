<?php

namespace Drupal\account\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\account\AccountInterface;

/**
 * Defines the account entity class.
 *
 * @EntityType(
 *   id = "account",
 *   label = "用户信息",
 *   controllers = {
 *     "storage" = "Drupal\account\AccountStorageController",
 *     "form" = {
 *       "default" = "Drupal\account\AccountFormController"
 *     }
 *   },
 *   base_table = "accounts",
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "uid",
 *     "label" = "nickname"
 *   },
 *   links = {
 *     "edit-form" = "account.edit"
 *   }
 * )
 */
class Account extends ContentEntityBase implements AccountInterface {

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('uid')->value;
  }

  public static function baseFieldDefinitions($entity_type) {
    $properties['uid'] = array(
      'label' => t('ID'),
      'description' => t('The user ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['nickname'] = array(
      'label' => '昵称',
      'type' => 'string_field',
    );
    $properties['picture'] = array(
      'label' => 'Avatar',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'file',
        'default_value' => 0,
      ),
    );
    $properties['sex'] = array(
      'label' => '性别',
      'type' => 'boolean_field',
    );
    $properties['follow_count'] = array(
      'label' => '关注数量',
      'type' => 'integer_field',
    );
    $properties['fans_count'] = array(
      'label' => '粉丝数量',
      'type' => 'integer_field',
    );
    $properties['store_follow_count'] = array(
      'label' => '关注的商家数量',
      'type' => 'integer_field',
    );
    $properties['phone'] = array(
      'label' => '绑定手机号',
      'type' => 'string_field',
    );
    $properties['district'] = array(
      'label' => '常居地',
      'type' => 'integer_field',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'district',
        'default_value' => 0,
      ),
    );

    return $properties;
  }

}

