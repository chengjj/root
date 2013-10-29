<?php

namespace Drupal\coupon\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\coupon\CouponInterface;
use Drupal\file\Entity\File;

/**
 * Defines the coupon entity.
 *
 * @EntityType(
 *   id = "coupon",
 *   label = "优惠券",
 *   module = "coupon",
 *   controllers = {
 *     "storage" = "Drupal\coupon\CouponStorageController",
 *     "view_builder" = "Drupal\coupon\CouponRenderController"
 *   },
 *   base_table = "coupons",
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "cid",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/coupon/{coupon}",
 *     "edit-form" = "/coupon/{coupon}/edit"
 *   }
 * )
 */
class Coupon extends ContentEntityBase implements CouponInterface {

  public function id() {
    return $this->get('cid')->value;
  }

  public function getStore() {
    return $this->get('sid')->entity;
  }

  public function getAuthor() {
    return $this->get('uid')->entity;
  }

  public function getPicture() {
    return $this->get('fid')->entity;
  }

  public function setPicture(File $picture) {
    if (!$picture->isPermanent()) {
      $image_factory = \Drupal::service('image.factory');
      $image = $image_factory->get($picture->getFileUri());
      $image_extension = $image->getExtension();
      $picture_directory = file_default_scheme() . '://' . variable_get('coupons_picture_path', 'coupons');

      // Prepare the pictures directory.
      file_prepare_directory($picture_directory, FILE_CREATE_DIRECTORY);
      $destination = file_stream_wrapper_uri_normalize($picture_directory . '/coupon-' . $this->id() . '.' .$image_extension);
      // Move the temporary file info the final location.
      if ($picture = file_move($picture, $destination, FILE_EXISTS_RENAME)) {
        $picture->setPermanent();
        $picture->save();
        file_usage()->add($picture, 'coupon','coupon', $this->id());
      }
    }
    $this->set('fid', $picture->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageControllerInterface $storage_controller, array $entities) {
    parent::preDelete($storage_controller, $entities);

    $storage_controller->deleteBookmarks($entities);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $properties['cid'] = array(
      'label' => 'cid',
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => 'UUID',
      'type' => 'uuid_field',
      'read-only' => TRUE,
    );
    $properties['sid'] = array(
      'label' => '商家ID',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'store',
        'default_value' => 0,
      ),
    );
    $properties['title'] = array(
      'label' => t('Title'),
      'type' => 'string_field',
    );
    $properties['fid'] = array(
      'label' => 'file ID',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'file',
        'default_value' => 0,
      ),
    );
    $properties['uid'] = array(
      'label' => '用户ID',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $properties['body'] = array(
      'label' => ('描述'),
      'type' => 'string_field',
    );
    $properties['note'] = array(
      'label' => ('使用限制'),
      'type' => 'string_field',
    );
    $properties['start'] = array(
      'label' => 'start',
      'type' => 'integer_field',
    );
    $properties['expire'] = array(
      'label' => 'expire',
      'type' => 'integer_field',
    );
    $properties['status'] = array(
      'label' => 'status',
      'type' => 'integer_field',
    );
    $properties['created'] = array(
      'label' => '发布时间',
      'type' => 'integer_field',
    );
    $properties['changed'] = array(
      'label' => '更新时间',
      'type' => 'integer_field',
    );

    return $properties;
  }
}

?>
