<?php

/**
 * @file
 * Definition of Drupal\catalog\Entity\StoreCatalog.
 */

namespace Drupal\catalog\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\catalog\StoreCatalogInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\file\Entity\File;

/**
 * Defines the catalog entity class.
 *
 * @EntityType(
 *   id = "store_catalog",
 *   label = "商家分类",
 *   controllers = {
 *     "storage" = "Drupal\catalog\StoreCatalogStorageController",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list" = "Drupal\jsp\JspEntityListController",
 *     "access" = "Drupal\jsp\JspEntityAccessController"
 *   },
 *   base_table = "store_catalog",
 *   entity_keys = {
 *     "id" = "cid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "weight" = "weight"
 *   }
 * )
 */
class StoreCatalog extends ContentEntityBase implements StoreCatalogInterface {

  public function id() {
    return $this->get('cid')->value;
  }

  public function setPicture(File $picture) {
    if (!$picture->isPermanent()) {
      $image_factory = \Drupal::service('image.factory');
      $image = $image_factory->get($picture->getFileUri());
      $image_extension = $image->getExtension();
      $picture_directory = file_default_scheme() . '://' . variable_get('store_catalog_picture_path', 'store_catalogs');

      // Prepare the pictures directory.
      file_prepare_directory($picture_directory, FILE_CREATE_DIRECTORY);
      $destination = file_stream_wrapper_uri_normalize($picture_directory . '/store_catalog-' . $this->id() . '.' .$image_extension);
      // Move the temporary file info the final location.
      if ($picture = file_move($picture, $destination, FILE_EXISTS_RENAME)) {
        $picture->setPermanent();
        $picture->save();
        file_usage()->add($picture, 'catalog','store_catalog', $this->id());
      }
    }
    $this->set('picture', $picture->id());
    return $this;
  }

  public function getPicture() {
    return $this->get('picture')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $properties['cid'] = array(
      'label' => t('ID'),
      'description' => t('The catalog ID.'),
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
    $properties['picture'] = array(
      'label' => t('Image'),
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'file',
        'default_value' => 0,
      ),
    );
    $properties['parent_cid'] = array(
      'label' => '上级分类',
      'type' => 'entity_reference_field',
      // Save new catalogs with no parents by default.
      'settings' => array(
        'target_type' => 'store_catalog',
        'default_value' => 0,
      ),
    );
    $properties['city_id'] = array(
      'label' => '城市ID',
      'type' => 'integer_field',
    );
    $properties['weight'] = array(
      'label' => '权重',
      'type' => 'integer_field',
    );
    return $properties;
  }
}

?>
