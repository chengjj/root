<?php

/**
 * @file
 * Definition of Drupal\store\Entity\StoreComment.
 */

namespace Drupal\store\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\store\StoreCommentInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines the store comment entity class.
 *
 * @EntityType(
 *   id = "store_comment",
 *   label = "店铺评论",
 *   controllers = {
 *     "storage" = "Drupal\store\StoreCommentStorageController",
 *     "view_builder" = "Drupal\store\StoreCommentRenderController",
 *     "list" = "Drupal\jsp\JspEntityListController",
 *     "access" = "Drupal\jsp\JspEntityAccessController",
 *     "form" = {
 *       "default" = "Drupal\store\StoreCommentFormController",
 *     }
 *   },
 *   base_table = "store_comment",
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "cid",
 *     "label" = "subject",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class StoreComment extends ContentEntityBase implements StoreCommentInterface {

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('cid')->value;
  }

  public function getAuthor() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function postCreate(EntityStorageControllerInterface $storage_controller) {
    parent::postCreate($storage_controller);

    $storage_controller->updateStoreStatistics($this->sid->value);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageControllerInterface $storage_controller) {
    parent::preSave($storage_controller);

    if (!isset($this->status->value)) {
      $this->status->value = COMMENT_PUBLISHED;
    }
    if ($this->isNew()) {
      if (empty($this->created->value)) {
        $this->created->value = REQUEST_TIME;
      }
    }
  }

  public static function postDelete(EntityStorageControllerInterface $storage_controller, array $entities) {
    parent::postDelete($storage_controller, $entities);

    foreach ($entities as $entity) {
      $storage_controller->updateStoreStatistics($this->sid->value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function permalink() {

    $url['path'] = 'store/' . $this->sid->value;
    $url['options'] = array('fragment' => 'comment-' . $this->id());

    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $properties['cid'] = array(
      'label' => t('ID'),
      'description' => t('The comment ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => t('UUID'),
      'description' => t('The comment UUID.'),
      'type' => 'uuid_field',
    );
    $properties['sid'] = array(
      'label' => '商家',
      'description' => t('The ID of the store of which this comment is a reply.'),
      'type' => 'entity_reference_field',
      'settings' => array('target_type' => 'store'),
      'required' => TRUE,
    );
    $properties['rank'] = array(
      'label' => '评价',
      'type' => 'boolean_field',
    );
    $properties['subject'] = array(
      'label' => '内容',
      'type' => 'string_field',
    );
    $properties['uid'] = array(
      'label' => '发布者',
      'description' => t('The user ID of the comment author.'),
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $properties['created'] = array(
      'label' => '创建时间',
      'description' => t('The time that the comment was created.'),
      'type' => 'integer_field',
    );
    $properties['status'] = array(
      'label' => t('Publishing status'),
      'description' => t('A boolean indicating whether the comment is published.'),
      'type' => 'boolean_field',
    );
    return $properties;
  }

}
