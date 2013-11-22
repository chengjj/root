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
 *   controllers = {
 *     "storage" = "Drupal\adv\AdvStorageController",
 *     "view_builder" = "Drupal\adv\AdvRenderController",
 *     "list" = "Drupal\jsp\JspEntityListController",
 *     "access" = "Drupal\jsp\JspEntityAccessController"
 *   },
 *   base_table = "advs",
 *   entity_keys = {
 *     "id" = "aid",
 *     "label" = "title",
 *     "uuid" = "uuid"
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

  public function setPicture(File $picture) {
    if (!$picture->isPermanent()) {
      $image_factory = \Drupal::service('image.factory');
      $image = $image_factory->get($picture->getFileUri());
      $image_extension = $image->getExtension();
      $picture_directory = file_default_scheme() . '://' . variable_get('adv_picture_path', 'advs');

      // Prepare the pictures directory.
      file_prepare_directory($picture_directory, FILE_CREATE_DIRECTORY);
      $destination = file_stream_wrapper_uri_normalize($picture_directory . '/adv-' . $this->id() . '.' .$image_extension);
      // Move the temporary file info the final location.
      if ($picture = file_move($picture, $destination, FILE_EXISTS_RENAME)) {
        $picture->setPermanent();
        $picture->save();
        file_usage()->add($picture, 'adv','adv', $this->id());
      }
    }
    $this->set('fid', $picture->id());
    return $this;
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
    $properties['uuid'] = array(
      'label' => 'UUID',
      'type' => 'uuid_field',
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
    $properties['start'] = array(
      'label' => 'start',
      'type' => 'integer_field',
    );
    $properties['expire'] = array(
      'label' => 'expire',
      'type' => 'integer_field',
    );
    $properties['cid'] = array(
      'label' => '广告地区city ID',
      'type' => 'integer_field',
    );
    $properties['type'] = array(
      'label' => '广告类型',
      'type' => 'string_field',
    );
    $properties['status'] = array(
      'label' => '状态',
      'type' => 'integer_field',
    );
    $properties['sid'] = array(
      'label' => '商家ID',
      'type' => 'integer_field',
    );
    $properties['created'] = array(
      'label' => '创建时间',
      'type' => 'integer_field',
    );
    $properties['changed'] = array(
      'label' => '修改时间',
      'type' => 'integer_field',
    );
    $properties['uid'] = array(
      'label' => '发布者UID',
      'type' => 'integer_field',
    );
    return $properties;
  }

}

