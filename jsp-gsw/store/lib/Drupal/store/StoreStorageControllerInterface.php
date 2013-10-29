<?php

/**
 * @file
 * Contains \Drupal\store\StoreStorageControllerInterface.
 */

namespace Drupal\store;

use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines a common interface for store entity controller classes.
 */
interface StoreStorageControllerInterface extends EntityStorageControllerInterface {

  /**
   * Delete the coupons for store.
   *
   * @param array $entities
   *   An array of store objects, keyed by the store id being
   *   deleted. The storage backend should delete the coupon data of the
   *   stores.
   */
  public function deleteCoupons(array $entities);

  /**
   * Delete the follows for store.
   *
   * @param array $entities
   *   An array of store objects, keyed by the store id being
   *   deleted. The storage backend should delete the follow data of the
   *   stores.
   */
  public function deleteFollows(array $entities);

}

?>
