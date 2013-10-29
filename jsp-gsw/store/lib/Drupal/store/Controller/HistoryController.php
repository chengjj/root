<?php

namespace Drupal\store\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\store\StoreInterface;

/**
 * Returns responses for History routes.
 */
class HistoryController extends ControllerBase {
  /**
   * Marks a store as read by the current user right now.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request of the page.
   * @param \Drupal\store\StoreInterface $store
   *   The store whose "last read" timestamp should be updated.
   */
  public function readStore(Request $request, StoreInterface $store) {
    if ($this->currentUser()->isAnonymous()) {
      throw new AccessDeniedHttpException();
    }

    // Update the history table, stating that this user viewed this store.
    store_history_write($store->id());

    return new JsonResponse((int)store_history_read($store->id()));
  }
}

?>
