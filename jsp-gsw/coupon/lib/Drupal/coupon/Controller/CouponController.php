<?php

/**
 * @file
 * Contains \Drupal\coupon\Controller\CouponController.
 */
namespace Drupal\coupon\Controller;

use Drupal\coupon\CouponManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
//use Drupal\Core\Controller\ControllerInterface;
use Drupal\user\Plugin\Core\Entity\User;

use Drupal\coupon\CouponInterface;

/**
 * Controller routines for coupon routes.
 */
class CouponController implements ContainerInjectionInterface {
  /**
   * Coupon Manager Service.
   *
   * @var \Drupal\coupon\CouponManager
   */
  protected $couponManager;

  /**
   * Injects CouponManager Service.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('coupon.manager'));
  }

  /**
   * Constructs a CouponController object.
   */
  public function __construct(CouponManager $couponManager) {
    $this->couponManager = $couponManager;
  }

  /**
   * page callback for: api/coupon.
   * return 
   */
  public function couponCreatePage() {
    $return = $this->couponManager->createCoupon();
    if (isset($return['message'])) {
      return  new JsonResponse($return['message'], $return['status']);
    } else {
      return new JsonResponse($this->get_coupon_response($return['coupon']));
    }
  }

  /**
   * get store response
   */
  protected function get_coupon_response($coupon) {
    $picture = $coupon->getPicture();
    $store_name = '';
    if ($coupon->sid->value && $store = $coupon->getStore()) {
      $store_name = $store->label();
      $store_picture = $store->getPicture();
    }
    return array(
      'id' => $coupon->id(),
      'title' => $coupon->label(),
      'body' => check_plain($coupon->body->value),
      'image_url' => $picture ? file_create_url($picture->getFileUri()) : variable_get('store_default_picture', 'http://api.gsw100.com/sites/default/files/store_default_picture.png'),
      'store_id' => $coupon->sid->value,
      'store_name' => $store_name,
      'store_image_url' => (isset($store_picture) && $store_picture) ? file_create_url($store_picture->getFileUri()) : variable_get('store_default_picture', 'http://api.gsw100.com/sites/default/files/store_default_picture.png'),
      'note' => $coupon->note->value,
      'start' => isset($coupon->start) ? date('Y-m-d H:i:s', $coupon->start->value) : date('Y-m-d H:i:s'),
      'end' => isset($coupon->expire) ? date('Y-m-d H:i:s', $coupon->expire->value) : date('Y-m-d H:i:s'),
      'created' => date('Y-m-d H:i:s', $coupon->created->value), 
      'status' => $coupon->status->value,
    );
  }

  /**
   * page callback for: api/store/{store_id}/late
   */
  public function loadNewCoupon($store_id) {
    $coupon = $this->couponManager->loadNewCouponByStoreid($store_id);
    return new JsonResponse($this->get_coupon_response($coupon));
  }

  /**
   * page callback for: api/store/{store_id}/coupon/{coupon_id}
   */
  public function couponView($store_id, $coupon_id) {
    $coupon = $this->couponManager->loadCouponByStoreid($store_id, $coupon_id);
    return new JsonResponse($this->get_coupon_response($coupon));
  }

  /**
   * page callback for: api/store/{store_id}/coupon/{coupon_id}/edit
   * TODO 检查该接口 是否再使用
   */
  public function couponEdit($store_id, $coupon_id) {
    $return = $this->couponManager->couponEdit($store_id, $coupon_id);
    if (isset($return['message'])) {
      return new JsonResponse($return['message'], $return['status']);
    } else {
      return new JsonResponse($this->get_coupon_response($return['coupon']));
    }
  }

  /**
   * page callback for: api/user/{user_id}/coupons
   */
  public function loadUserCoupons($user_id) {
    $coupons = array();
    foreach ($this->couponManager->getCouponsByUserid($user_id) as $coupon) {
      $coupons[] = $this->get_coupon_response($coupon);
    }
    return new JsonResponse($coupons);
  }

  /**
   * page callback for: api/coupon/{coupon_id}
   */
  public function couponUtils($coupon_id) {
    $return = $this->couponManager->couponUtils($coupon_id);
    if (isset($return['data'])) {
      return new JsonResponse($this->get_coupon_response($return['data']));
    }
    return new JsonResponse($return['message'], $return['status']);
  }

  /**
   * page callback for: api/coupons/{coupon_ids}
   */
  public function deleteCoupons($coupon_ids) {
    $return = $this->couponManager->deleteCoupons($coupon_ids);
    return new JsonResponse($return['message'], $return['status']);
  }

  /**
   * page callback for: api/coupon/{coupon_id}/revoke
   */
  public function revokeCoupon($coupon_id) {
    $return = $this->couponManager->revokeCoupon($coupon_id);
    return new JsonResponse($return['message'], $return['status']);
  }

  /**
   * page callback for: api/store/{store_id}/coupons
   */
  public function storeCoupons($store_id) {
    $coupons = array();
    foreach ($this->couponManager->getCouponsByStoreid($store_id) as $coupon) {
      $coupons[] = $this->get_coupon_response($coupon);
    }
    return new JsonResponse($coupons);
  }

  /**
   * page callback for: api/store/{store_id}/coupons/{status}
   */
  public function loadStoreCouponsByStatus($store_id, $status) {
    $coupons = array();
    foreach ($this->couponManager->getCouponsByStoreidStatus($store_id, $status) as $coupon) {
      $coupons[] = $this->get_coupon_response($coupon);
    }
    return new JsonResponse($coupons);
  }


  /**
   * user bookmark coupons
   * page callback for: api/bookmark/coupons
   */
  public function userBookmarkCoupons(Request $request) {
    $return = $this->couponManager->getUserBookmarkCoupons($request);
    if (isset($return['message'])) {
      return new JsonResponse($return['message'], $return['status']);
    } else {
      $coupons = array();
      foreach ($return['coupons'] as $coupon) {
        $store = $coupon->getStore();
        $coupon_picture = $coupon->getPicture();
        $store_picture = $store->getPicture();
        $coupons[] = array(
          'id' => $coupon->id(),
          'title' => $coupon->label(),
          'body' => isset($coupon->body) ? check_plain($coupon->body->value) : '',
          'store_image_url' => $store_picture ? file_create_url($store_picture->getFileUri()) : variable_get('store_default_picture', 'http://api.gsw100.com/sites/default/files/store_default_picture.png'),
          'image_url' => $coupon_picture ? file_create_url($coupon_picture->getFileUri()) : variable_get('store_default_picture', 'http://api.gsw100.com/sites/default/files/store_default_picture.png'),
          'store_id' => isset($store) ? $store->id() : 0,
          'store_name' => isset($store) ? $store->label() : '',
          'note' => isset($coupon->note) ? $coupon->note->value : '',
          'start' => isset($coupon->start) ? date('Y-m-d H:i:s', $coupon->start->value) : date('Y-m-d H:i:s'),
          'end' => isset($coupon->expire) ? date('Y-m-d H:i:s', $coupon->expire->value) : date('Y-m-d H:i:s'),
          'created' => date('Y-m-d H:i:s', $coupon->created->value), 
          'status' => $coupon->status->value,
        );
      }
      return new JsonResponse($coupons, 200, $return['header']);
    }
  }

  /**
   * bookmark coupon 
   * page callback for: api/user/bookmark/coupon/{coupon_id}
   */
  public function couponBookmark($coupon_id) {
    $return = $this->couponManager->couponBookmark($coupon_id);
    return new JsonResponse($return['data'], $return['status']);
  }

  public function bookmarked(Request $request, CouponInterface $coupon) {
    $user = \Drupal::currentUser();
    $bookmarked = false;
    if ($user->isAuthenticated()) {
      $query = db_select('coupon_bookmarks', 'b');
      $query->addField('b', 'uid');
      $query->condition('b.uid', $user->id());
      $query->condition('b.cid', $coupon->id());
      if ($query->execute()->fetchObject()) {
        $bookmarked = true;
      }
    }
    return new JsonResponse(array('bookmarked' => $bookmarked));
  }

  public function bookmark(Request $request, CouponInterface $coupon) {
    $user = \Drupal::currentUser();
    db_insert('coupon_bookmarks')
      ->fields(array(
        'uid' => $user->id(),
        'cid' => $coupon->id(),
        'created' => REQUEST_TIME,
      ))
      ->execute();
    return new JsonResponse(array('bookmarked' => true));
  }

  public function unbookmark(Request $request, CouponInterface $coupon) {
    $user = \Drupal::currentUser();
    db_delete('coupon_bookmarks')
      ->condition('uid', $user->id())
      ->condition('cid', $coupon->id())
      ->execute();
    return new JsonResponse(array('bookmarked' => false));
  }

  /**
   * page callback: coupons
   */
  public function front(Request $request) {
    return array('#theme' => 'coupon_front_page');
  }


  /**
   * page callback: admin/coupons
   */
  public function couponManage() {
    return drupal_get_form('admin_coupon_list_form');
  }

  /**
   * page callback: coupon/edit
   */
  public function couponAdd() {
    module_load_include('admin.inc', 'coupon');
    return drupal_get_form('admin_coupon_edit_form'); 
  }

  /**
   * page callback: coupon/edit/{coupon}
   */
  public function couponEditForm(CouponInterface $coupon) {
    module_load_include('admin.inc', 'coupon');
    return drupal_get_form('admin_coupon_edit_form', $coupon); 
  }

  /**
   * page callback: admin/coupons/pending
   */
  public function couponAdminPending() {
    module_load_include('pages.inc', 'coupon');
    return drupal_get_form('coupons_pending_form'); 
  }

}
