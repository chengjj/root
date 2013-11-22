<?php

/**
 * @file
 * Definition of Drupal\share\Entity\ShareComment.
 */

namespace Drupal\share\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\share\ShareCommentInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines the share comment entity.
 * @EntityType(
 *   id = "share_comment",
 *   label = "商品评论",
 *   controllers = {
 *     "storage" = "Drupal\share\ShareCommentStorageController",
 *     "view_builder" = "Drupal\share\ShareCommentRenderController",
 *     "list" = "Drupal\jsp\JspEntityListController",
 *     "access" = "Drupal\jsp\JspEntityAccessController",
 *     "form" = {
 *       "default" = "Drupal\share\ShareCommentFormController"
 *     }
 *   },
 *   base_table = "share_comments",
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "cid",
 *     "label" = "subject",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class ShareComment extends ContentEntityBase implements ShareCommentInterface {

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('cid')->value;
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
      'description' => t('The share_comments UUID.'),
      'type' => 'uuid_field',
      'read-only' => TRUE,
    );
    $properties['sid'] = array(
      'label' => '商品',
      'description' => t('The ID of the share of which this comment is a reply.'),
      'type' => 'entity_reference_field',
      'settings' => array('target_type' => 'share'),
      'required' => TRUE,
    );
    $properties['subject'] = array(
      'label' => '内容',
      'description' => t('The comment title or subject.'),
      'type' => 'string_field',
    );
    $properties['uid'] = array(
      'label' => '会员',
      'description' => t('The user ID of the comment author.'),
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $properties['created'] = array(
      'label' => '发表时间',
      'description' => t('The time that the comment was created.'),
      'type' => 'integer_field',
    );
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageControllerInterface $storage_controller) {
    parent::preSave($storage_controller);

    if ($this->isNew()) {
      if (empty($this->created->value)) {
        $this->created->value = REQUEST_TIME;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageControllerInterface $storage_controller, $update = TRUE) {
    parent::postSave($storage_controller, $update);

    $storage_controller->updateShareStatistics($this);
  }

  public function getAuthor() {
    return $this->get('uid')->entity;
  }

}
?>
