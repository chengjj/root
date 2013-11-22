<?php

/**
* @file
* Contains \Drupal\coupon\CouponStorageController.
*/

namespace Drupal\coupon;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableDatabaseStorageController;

/**
* Defines the storage controller class for coupon entities.
*/
class CouponStorageController extends FieldableDatabaseStorageController implements CouponStorageControllerInterface { 

  /**
   * {@inheritdoc}
   */
  public function deleteBookmarks(array $entities) {
    $this->database->delete('coupon_bookmarks')
      ->condition('cid', array_keys($entities))
      ->execute();
  }

  public function unbookmark(CouponInterface $coupon) {
    $user = \Drupal::currentUser();

    if($user->isAuthenticated()) {
      db_delete('coupon_bookmarks')
        ->condition('uid', $user->id())
	->condition('cid', $coupon->id())
	->execute();

    }
  }

}
