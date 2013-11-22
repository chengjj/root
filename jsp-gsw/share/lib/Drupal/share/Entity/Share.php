<?php

/**
 * @file
 * Definition of Drupal\share\Entity\Share.
 */

namespace Drupal\share\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\share\ShareInterface;

use Drupal\Core\Language\Language;
use Drupal\file\Entity\File;

/**
 * Defines the share entity.
 *
 * @EntityType(
 *   id = "share",
 *   label = "商品",
 *   controllers = {
 *     "storage" = "Drupal\share\ShareStorageController",
 *     "view_builder" = "Drupal\share\ShareViewBuilder",
 *     "list" = "Drupal\adv_block\AdvBlockEntityListController",
 *     "access" = "Drupal\jsp\JspEntityAccessController",
 *     "form" = {
 *       "default" = "Drupal\share\ShareFormController"
 *     },
 *   },
 *   base_table = "shares",
 *   entity_keys = {
 *     "id" = "sid",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "share.view",
 *     "edit-form" = "share.edit"
 *   }
 * )
 */
class Share extends ContentEntityBase implements ShareInterface {

  public function id() {
    return $this->get('sid')->value;
  }

  public function getPicture() {
    return $this->get('picture')->entity;
  }

  public function setPicture(File $picture) {
    if (!$picture->isPermanent()) {
      $image_factory = \Drupal::service('image.factory');
      $image = $image_factory->get($picture->getFileUri());
      $image_extension = $image->getExtension();
      $picture_directory = file_default_scheme() . '://' . variable_get('share_picture_path', 'shares');

      // Prepare the pictures directory.
      file_prepare_directory($picture_directory, FILE_CREATE_DIRECTORY);
      $destination = file_stream_wrapper_uri_normalize($picture_directory . '/share-' . $this->id() . '.' .$image_extension);
      // Move the temporary file info the final location.
      if ($picture = file_move($picture, $destination, FILE_EXISTS_RENAME)) {
        $picture->setPermanent();
        $picture->save();
        file_usage()->add($picture, 'share','share', $this->id());
      }
    }
    $this->set('picture', $picture->id());
    return $this;
  }

  public function getAuthor() {
    return $this->get('uid')->entity;
  }
  
  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageControllerInterface $storage_controller, $update = TRUE) {
    parent::postSave($storage_controller, $update);

    if (!$this->isNew()) {
      if ($this->original->picture->entity && (!$this->picture->entity || ($this->picture->entity && $this->original->picture->entity->id() != $this->picture->entity->id()))) {
        file_usage()->delete($this->original->picture->entity, 'share', 'share', $this->id());
      }
      if ($this->picture->entity && (!$this->original->picture->entity || $this->original->picture->entity->id() != $this->picture->entity->id())) {
        file_usage()->add($this->picture->entity, 'share','share', $this->id());
      }
    }
    if ($this->picture->entity) {
      file_usage()->add($this->picture->entity, 'share','share', $this->id());
    }

  }

  public static function baseFieldDefinitions($entity_type) {
    $properties['sid'] = array(
      'label' => 'ID',
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => 'UUID',
      'type' => 'uuid_field',
      'read-only' => TRUE,
    );
    $properties['cid'] = array(
      'label' => '分类',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'share_catalog',
        'default_value' => 0,
      ),
    );
    $properties['title'] = array(
      'label' => '标题',
      'type' => 'string_field',
    );
    $properties['source'] = array(
      'label' => 'source',
      'type' => 'string_field',
    );
    $properties['item_id'] = array(
      'label' => 'item id',
      'type' => 'string_field',
    );
    $properties['price'] = array(
      'label' => 'title',
      'type' => 'string_field',
    );
    $properties['description'] = array(
      'label' => '商品描述',
      'type' => 'string_field',
    );
    $properties['picture'] = array(
      'label' => '图片',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'file',
        'default_value' => 0,
      ),
    );
    $properties['url'] = array(
      'label' => '商品地址',
      'type' => 'string_field',
    );
    $properties['created'] = array(
      'label' => '创建时间',
      'type' => 'integer_field',
    );
    $properties['status'] = array(
      'label' => 'status',
      'type' => 'integer_field',
    );
    $properties['uid'] = array(
      'label' => '用户ID',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $properties['comment_count'] = array(
      'label' => 'comment_count',
      'type' => 'integer_field',
    );
    $properties['bookmark_count'] = array(
      'label' => '收藏数量',
      'type' => 'integer_field',
    );
    $properties['view_count'] = array(
      'label' => 'view_count',
      'type' => 'integer_field',
    );
    return $properties;
  }
}

?>
