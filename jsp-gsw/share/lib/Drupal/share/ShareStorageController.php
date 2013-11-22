<?php

/**
* @file
* Contains \Drupal\share\ShareStorageController.
*/

namespace Drupal\share;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableDatabaseStorageController;

/**
* Defines the storage controller class for share.
*/
class ShareStorageController extends FieldableDatabaseStorageController implements ShareStorageControllerInterface { 

  public function bookmark(ShareInterface $share) {
    $user = \Drupal::currentUser();

    if ($user->isAuthenticated()) {
      db_insert('share_bookmarks')
        ->fields(array(
          'uid' => $user->id(),
          'sid' => $share->id(),
          'created' => REQUEST_TIME,
        ))
        ->execute();

      $this->updateBookmarkCount($share);
    }
  }

  public function unbookmark(ShareInterface $share) {
    $user = \Drupal::currentUser();

    if ($user->isAuthenticated()) {
      db_delete('share_bookmarks')
        ->condition('uid', $user->id())
        ->condition('sid', $share->id())
        ->execute();

      $this->updateBookmarkCount($share);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateBookmarkCount(ShareInterface $share) {
    $query = $this->database->select('share_bookmarks', 'b');
    $query->addExpression('COUNT(uid)');
    $count = $query->condition('b.sid', $share->id())
      ->execute()
      ->fetchField();

    if ($count != $share->bookmark_count->value) {
      $share->bookmark_count->value = $count;
      $share->save();
    }
  }

}

