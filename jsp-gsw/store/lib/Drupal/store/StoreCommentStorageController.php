<?php

/**
 * @file
 * Definition of Drupal\store\StoreCommentStorageController.
 */

namespace Drupal\store;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableDatabaseStorageController;
use Drupal\Component\Uuid\Uuid;

/**
 * Defines the controller class for comments.
 *
 * This extends the Drupal\Core\Entity\DatabaseStorageController class, adding
 * required special handling for comment entities.
 */
class StoreCommentStorageController extends FieldableDatabaseStorageController implements StoreCommentStorageControllerInterface {

  /**
   * {@inheritdoc}
   */
  public function updateStoreCommentCount($sid) {
    $count = db_query('SELECT COUNT(sid) FROM {store_comment} WHERE sid = :sid', array(
      ':sid' => $sid,
    ))->fetchField();
    $store = store_load($sid);
    $store->comment_count->value = $count;
    $store->save();
  }

}
