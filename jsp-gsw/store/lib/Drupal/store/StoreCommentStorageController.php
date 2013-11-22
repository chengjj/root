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
  public function updateStoreStatistics($sid) {
    $comment_count = db_query('SELECT COUNT(sid) FROM {store_comment} WHERE sid = :sid', array(
      ':sid' => $sid,
    ))->fetchField();
    $rank_count = db_query('SELECT COUNT(sid) FROM {store_comment} WHERE rank = 1 AND sid = :sid', array(
      ':sid' => $sid,
    ))->fetchField();
    $coupon_count = db_query('SELECT COUNT(cid) FROM { coupons } WHERE status = 1 AND sid = :sid',array(':sid' =>$sid,))->fetchField();
  
    $store = entity_load('store', $sid);
    if ($store->comment_count->value != $comment_count || $store->rank_count->value != $rank_count || $store->coupon_count->value != $coupon_count) {
      $store->comment_count->value = $comment_count;
      $store->rank_count->value = $rank_count;
      $store->coupon_count->value = $coupon_count;
      $store->save();
    }
  }

}
