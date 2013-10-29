<?php

/**
 * @file
 * Definition of Drupal\share\Entity\Share.
 */

namespace Drupal\share\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\share\ShareInterface;

use Drupal\Core\Language\Language;
use Drupal\file\Entity\File;

/**
 * Defines the share entity.
 *
 * @EntityType(
 *   id = "share",
 *   label = "商品",
 *   module = "share",
 *   controllers = {
 *     "storage" = "Drupal\share\ShareStorageController",
 *     "view_builder" = "Drupal\share\ShareRenderController"
 *   },
 *   base_table = "shares",
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "sid",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/share/{share}"
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
  
  public static function baseFieldDefinitions($entity_type) {
    $properties['sid'] = array(
      'label' => 'sid',
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['cid'] = array(
      'label' => 'cid',
      'type' => 'integer_field',
    );
    $properties['title'] = array(
      'label' => 'title',
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
      'label' => 'created',
      'type' => 'integer_field',
    );
    $properties['status'] = array(
      'label' => 'status',
      'type' => 'integer_field',
    );
    $properties['uuid'] = array(
      'label' => 'UUID',
      'type' => 'uuid_field',
      'read-only' => TRUE,
    );
    $properties['uid'] = array(
      'label' => '用户ID',
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $properties['view_count'] = array(
      'label' => 'view_count',
      'type' => 'integer_field',
    );
    return $properties;
  }
}

?>
