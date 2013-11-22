<?php

namespace Drupal\coupon;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines a common interface for coupon entity controller classes.
 */
interface CouponStorageControllerInterface extends EntityStorageControllerInterface {

  /**
   * Delete the bookmarks for coupon.
   *
   * @param array $entities
   *   An array of coupon objects, keyed by the coupon id being
   *   deleted. The storage backend should delete the bookmark data of the
   *   coupons.
   */
  public function deleteBookmarks(array $entities);


  public function unbookmark(CouponInterface $coupon);

}
?>
