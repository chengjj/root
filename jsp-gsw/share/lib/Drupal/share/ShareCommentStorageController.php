<?php

/**
* @file
* Contains \Drupal\share\ShareCommentStorageController.
*/

namespace Drupal\share;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableDatabaseStorageController;

/**
* Defines the storage controller class for share comment.
*/
class ShareCommentStorageController extends FieldableDatabaseStorageController implements ShareCommentStorageControllerInterface { 

  function updateShareStatistics(ShareCommentInterface $comment) {
    $query = $this->database->select('share_comments', 'c');
    $query->addExpression('COUNT(cid)');
    $count = $query->condition('c.sid', $comment->sid->value)
      ->execute()
      ->fetchField();

    $share = entity_load('share', $comment->sid->value);
    $share->comment_count->value = $count;
    $share->save();
  }
}

