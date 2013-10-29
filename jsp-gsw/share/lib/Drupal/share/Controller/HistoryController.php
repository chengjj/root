<?php

namespace Drupal\share\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\share\ShareInterface;

/**
 * Returns responses for History routes.
 */
class HistoryController extends ControllerBase {
  /**
   * Marks a share as read by the current user right now.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request of the page.
   * @param \Drupal\share\ShareInterface $share
   *   The share whose "last read" timestamp should be updated.
   */
  public function readShare(Request $request, ShareInterface $share) {
    if ($this->currentUser()->isAnonymous()) {
      throw new AccessDeniedHttpException();
    }

    // Update the history table, stating that this user viewed this share.
    share_history_write($share->id());

    $share->view_count->value ++;
    $share->save();

    return new JsonResponse((int)share_history_read($share->id()));
  }
}

?>
