<?php

/**
* @file
* Contains \Drupal\store\StoreStorageController.
*/
namespace Drupal\store;

use Drupal\Core\Entity\FieldableDatabaseStorageController;

/**
* Defines the storage controller class for Block entities.
*/
class StoreStorageController extends FieldableDatabaseStorageController implements StoreStorageControllerInterface { 

  /**
   * {@inheritdoc}
   */
  public function deleteCoupons(array $entities) {
    $this->database->delete('coupons')
      ->condition('sid', array_keys($entities))
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteFollows(array $entities) {
    $this->database->delete('store_follow')
      ->condition('sid', array_keys($entities))
      ->execute();
  }

  public function loadUserStores($uid) {
    $query = \Drupal::entityQuery($this->entityType);
    $query
      ->condition('uid', $uid)
      ->sort('created');
    if ($sids = $query->execute()) {
      return $this->loadMultiple($sids);
    }
    else {
      return FALSE;
    }
  }
}
