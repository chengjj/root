<?php

namespace Drupal\coupon\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\coupon\CouponInterface;

/**
 * Returns responses for History routes.
 */
class HistoryController extends ControllerBase {
  /**
   * Marks a coupon as read by the current user right now.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request of the page.
   * @param \Drupal\coupon\CouponInterface $coupon
   *   The coupon whose "last read" timestamp should be updated.
   */
  public function readCoupon(Request $request, CouponInterface $coupon) {
    if ($this->currentUser()->isAnonymous()) {
      throw new AccessDeniedHttpException();
    }

    // Update the history table, stating that this user viewed this coupon.
    coupon_history_write($coupon->id());

    return new JsonResponse((int)coupon_history_read($coupon->id()));
  }
}

?>
