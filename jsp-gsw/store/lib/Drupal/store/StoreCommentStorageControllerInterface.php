<?php

/**
 * @file
 * Contains \Drupal\store\StoreCommentStorageControllerInterface.
 */

namespace Drupal\store;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines a common interface for comment entity controller classes.
 */
interface StoreCommentStorageControllerInterface extends EntityStorageControllerInterface {

  /**
   * Updates the comment statistics for a given store.
   *
   * @param $sid
   *   The store ID.
   */
  public function updateStoreCommentCount($sid);

}
