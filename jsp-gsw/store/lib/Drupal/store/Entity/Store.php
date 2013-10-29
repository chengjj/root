<?php

/**
 * @file
 * Definition of Drupal\store\Entity\Store.
 */

namespace Drupal\store\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\store\StoreInterface;
use Drupal\file\Entity\File;

/**
 * Defines the store entity class.
 *
 * @EntityType(
 *   id = "store",
 *   label = "商家",
 *   module = "store",
 *   controllers = {
 *     "storage" = "Drupal\store\StoreStorageController",
 *     "view_builder" = "Drupal\store\StoreRenderController",
 *     "form" = {
 *       "default" = "Drupal\store\StoreFormController"
 *     },
 *   },
 *   base_table = "stores",
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "sid",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/store/{store}",
 *     "edit-form" = "/store/{store}/edit"
 *   }
 * )
 */
class Store extends ContentEntityBase implements StoreInterface {

  public function id() {
    return $this->get('sid')->value;
  }

  public function getAuthor() {
    return $this->get('uid')->entity;
  }

  public function getPicture() {
    return $this->get('image_url')->entity;
  }
  
  public function setPicture(File $picture) {
    if (!$picture->isPermanent()) {
      $image_factory = \Drupal::service('image.factory');
      $image = $image_factory->get($picture->getFileUri());
      $image_extension = $image->getExtension();
      $picture_directory = file_default_scheme() . '://' . variable_get('store_picture_path', 'stores');

      // Prepare the pictures directory.
      file_prepare_directory($picture_directory, FILE_CREATE_DIRECTORY);
      $destination = file_stream_wrapper_uri_normalize($picture_directory . '/store-' . $this->id() . '.' .$image_extension);
      // Move the temporary file info the final location.
      if ($picture = file_move($picture, $destination, FILE_EXISTS_RENAME)) {
        $picture->setPermanent();
        $picture->save();
        file_usage()->add($picture, 'store','store', $this->id());
      }
    }
    $this->set('image_url', $picture->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageControllerInterface $storage_controller, array $entities) {
    parent::preDelete($storage_controller, $entities);

    $storage_controller->deleteCoupons($entities);
    $storage_controller->deleteFollows($entities);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $properties['sid'] = array(
      'label' => t('ID'),
      'description' => t('The store ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => 'UUID',
      'type' => 'uuid_field',
      'read-only' => TRUE,
    );
    $properties['name'] = array(
      'label' => '名称',
      'type' => 'string_field',
    );
    $properties['image_url'] = array(
      'label' => t('Image'),
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'file',
        'default_value' => 0,
      ),
    );
    $properties['address'] = array(
      'label' => '地址',
      'type' => 'string_field',
    );
    $properties['phone'] = array(
      'label' => '联系电话',
      'type' => 'string_field',
    );
    $properties['hours'] = array(
      'label' => '营业时间',
      'type' => 'string_field',
    );
    $properties['latitude'] = array(
      'label' => '纬度',
      'type' => 'string_field',
    );
    $properties['longitude'] = array(
      'label' => '经度',
      'type' => 'string_field',
    );
    $properties['discount'] = array(
      'label' => '基础折扣',
      'type' => 'string_field',
    );
    $properties['uid'] = array(
      'label' => '用户ID',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $properties['created'] = array(
      'label' => '创建时间',
      'type' => 'integer_field',
    );
    $properties['update_at'] = array(
      'label' => '修改时间',
      'type' => 'integer_field',
    );
    $properties['coupon_count'] = array(
      'label' => '促销数量',
      'type' => 'integer_field',
    );
    $properties['cid'] = array(
      'label' => '分类ID',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'store_catalog',
        'default_value' => 0,
      ),
    );
    $properties['deal_count'] = array(
      'label' => '消费总数',
      'type' => 'integer_field',
    );
    //TODO district
    $properties['district_id'] = array(
      'label' => '区域ID',
      'type' => 'integer_field',
    );
    $properties['follow_count'] = array(
      'label' => '关注总数',
      'type' => 'integer_field',
    );
    $properties['user_num'] = array(
      'label' => '会员总数',
      'type' => 'integer_field',
    );
    $properties['city_id'] = array(
      'label' => '城市ID',
      'type' => 'integer_field',
    );
    $properties['photo1'] = array(
      'label' => '商家多图1',
      'type' => 'integer_field',
    );
    $properties['photo2'] = array(
      'label' => '商家多图2',
      'type' => 'integer_field',
    );
    $properties['photo3'] = array(
      'label' => '商家多图3',
      'type' => 'integer_field',
    );
    $properties['photo4'] = array(
      'label' => '商家多图4',
      'type' => 'integer_field',
    );
    $properties['comment_count'] = array(
      'label' => '评论数量',
      'type' => 'integer_field',
    );

    return $properties;
  }
}

?>
