<?php
/**
 * @file
 * Contains \Drupal\coupon\CouponManager.
 */

namespace Drupal\coupon;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\user\Plugin\Core\Entity\User;

/**
 * Coupon Manager Service.
 */
class CouponManager {
  /**
   * Database Service Object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Entity manager Service Object.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;
  
  /**
   * Constructs a CouponManager object.
   */
  public function __construct(Connection $database, EntityManagerInterface $entityManager) {
    $this->database = $database;
    $this->entityManager = $entityManager;
  }

  /**
   * API user_authenticate httprequest head
   */
  protected function user_authenticate_by_http() {
    $coupon = FALSE;
    $name = isset($_SERVER['PHP_AUTH_USER']) ? trim($_SERVER['PHP_AUTH_USER']) : '';
    $pass = isset($_SERVER['PHP_AUTH_PW']) ? trim($_SERVER['PHP_AUTH_PW']) : '';

    $tokens = isset($_SERVER['HTTP_AUTHORIZATION']) ? trim($_SERVER['HTTP_AUTHORIZATION']) : '';
    if ($tokens) {
      $tokens = explode(' ', $tokens);
      if ($tokens[0] == 'token') {
        $autho_token = coupon_autho_token_load(array('token' => $tokens[1]));
        if ($autho_token->uid) {
          return user_load($autho_token->uid);
        }
      }
    }
    
    if ($name && $pass) {
      if (!user_is_blocked($name)) {
        $uid = user_authenticate($name, $pass);
        if ($uid) {
          $coupon = user_load($uid);
        }
      }
    }
    return $coupon;
  }

  /**
   * create coupon
   */
  public function createCoupon() {
    $account = account_user_authenticate_by_http();
    if (!$account->id()) {
      return array('message' => array('message' => '用户登录异常'), 'status' => '404');
    }
    $json_data = file_get_contents('php://input');
    $array = json_decode($json_data, TRUE);
    $store_id = isset($array['storeId']) ? $array['storeId'] : '';
    if ($store_id) {
      $store = store_load($store_id);
    }
    if (isset($json_data) && $json_data ) {
      $title = $array['title'];
      $body  = $array['body'];
      $coupon_picture = isset($array['coupon_picture']) ? $array['coupon_picture'] : ''; //TODO move picure to folder
      $consumer_limit = $array['note'];
      $start_date = $array['start'];
      $end_date = $array['end'];

      $array = array(
        'title' => isset($title) ? $title : '',
        'body' => isset($body) ? $body : '',
        'sid' => isset($store) ? $store->id() : 0,
        'note' => isset($consumer_limit) ? $consumer_limit : '',
        'start' => isset($start_date) ? strtotime($start_date) : 0,
        'expire' => isset($end_date) ? strtotime($end_date) : 0,
        'uid' => $account->id(),
        'status' => '0',
        'changed' => REQUEST_TIME,
        'created' => REQUEST_TIME,
      );
      $coupon = entity_create('coupon', $array);
      $coupon->enforceIsNew();
      $coupon->save();

      //TODO 
      /*$coupon = coupon_save(NULL, $array); */
      if ($coupon_picture) {
        $coupon_picture = base64_decode($coupon_picture);
        $file = file_save_data($coupon_picture);

        if ($file) {
          $file->setTemporary();
          $coupon->setPicture($file);
          $coupon->save();
        }
        //$coupon = coupon_save($coupon, array('picture_upload' => $file));
      }
    }

    return array('coupon' => $coupon);
  }

  /**
   * load new coupon by store sid
   */
  public function loadNewCouponByStoreid($store_id) {
    $cid = $this->database->query('SELECT cid FROM {coupons} WHERE sid = :sid ORDER BY created DESC', array(':sid' => $store_id))->fetchField();
    return coupon_load($cid);
  }

  /**
   * load coupon by store_id and coupon_id
   */
  public function loadCouponByStoreid($store_id, $coupon_id) {
    $coupon = coupon_load($coupon_id);
    $store = $coupon->getStore();
    if ($store_id == $store->id()) {
      return $coupon;
    } else {
      return FALSE;
    }
  }

  /**
   * coupon edit
   * TODO 检查该接口 是否再使用
   */
  public function couponEdit($store_id, $coupon_id) {
    $account = account_user_authenticate_by_http();
    if (!$account->id()) {
      return array('message' => array('message' => '用户登录异常'), 'status' => '404');
    }

    $coupon = coupon_load($coupon_id);
    $store = $coupon->getStore();

    $json_data = file_get_contents('php://input');
    if (isset($json_data) && $json_data) {
      $array = json_decode($json_data, TRUE);
      $title = $array['title'];
      $body  = $array['body'];
      $store_id = $array['storeId'];
      $consumer_limit = $array['note'];
      $start_date = $array['start'];
      $end_date = $array['end'];

      $array = array(
        'title' => isset($title) ? $title : '',
        'body' => isset($body) ? $body : '',
        'sid' => isset($store_id) ? $store_id : 0,
        'note' => isset($consumer_limit) ? $consumer_limit : '',
        'start' => isset($start_date) ? strtotime($start_date) : 0,
        'expire' => isset($end_date) ? strtotime($end_date) : 0,
        'uid' => isset($store) ? $store->uid->value : 0,
        'status' => '1',
        'changed' => time(),
      );
      foreach ($array as $key => $value) {
        $coupon->{$key} = $value;
      }
      $coupon->save();
      /*$coupon = coupon_save($coupon, $array);*/

      $coupon_picture = isset($array['coupon_picture']) ? $array['coupon_picture'] : ''; //TODO move picure to folder
      if (!empty($coupon_picture)) {
        $coupon_picture = base64_decode($coupon_picture);
        $file = file_save_data($coupon_picture);

        if ($file) {
          $file->setTemporary();
          $coupon->setPicture($file);
          $coupon->save();
        }
        
        /*$edit = array(
          'picture_upload' => $file,
        );
        //TODO
        coupon_save($coupon, $edit);*/
      } elseif ($coupon_picture == 0) {
        $this->database->query('UPDATE {coupons} SET fid=:fid WHERE cid=:cid', array(':fid' => 0, ':cid' => $coupon->id()));
      }
    } 
    //TODO
    return array('coupon' => coupon_load($coupon->id(), TRUE));
  }

  /**
   * load coupons by user id
   */
  public function getCouponsByUserid($user_id) {
    $request = \Drupal::request();
    $account = user_load($user_id);
    if (!$account->id()) return NULL;

    $page = $request->query->get('page', 1);
    $per_page = $request->query->get('per_page', 10);

    $query = $this->database->select('coupons', 'c')
      ->fields('c', array('cid'))
      ->condition('status', COUPON_STATUS_PUBLISH)
      ->condition('uid', $account->id());
    if ($per_page && is_numeric($per_page) && $page && is_numeric($page)) {
      $startIndex = 0;
      if ($page > 1) {
        $startIndex = ($page - 1) * $per_page;
      }
      $query->range($startIndex, $per_page);
    }
    $query->orderBy('created', 'DESC');

    $cids = $query->execute()->fetchCol();

    return coupon_load_multiple($cids);
  }

  /**
   * coupon utils
   */
  public function couponUtils($coupon_id) {
    $coupon = coupon_load($coupon_id);
    $account = account_user_authenticate_by_http();
    $status = 404;
    if ($account->id() != $coupon->uid->value) {
      return array('message' => array('message' => '没有权限删除该促销信息'), 'status' => $status);
    }
    if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
      coupon_delete($coupon->id());
      $status = 204;
      return array('message' => NULL, 'status' => $status);
    } elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
      $json_data = file_get_contents('php://input');
      $array_input = json_decode($json_data, TRUE);
      if (isset($json_data)) {
        $title = $array_input['title'];
        $body  = $array_input['body'];
        $consumer_limit = $array_input['note'];
        $start_date = $array_input['start'];
        $end_date = $array_input['end'];

        $array = array(
          'title' => isset($title) ? $title : '' ,
          'body'  => isset($body) ? $body : '', 
          'note'  => isset($consumer_limit) ? $consumer_limit : '',
          'start' => isset($start_date) ? strtotime($start_date) : 0,
          'expire' => isset($end_date) ? strtotime($end_date) : 0,
          'status' => COUPON_STATUS_PENDING,
          'changed' => time(),
        );
        if ($coupon->status == COUPON_STATUS_EXPIRED) {
          //已过期重新发布 需要重新生成coupon,并删除之前的coupon,新增即调用 entity_create()
          $coupon->status = COUPON_STATUS_DELETE;
          $coupon->save();
          $coupon = entity_create('coupon', $array);
          $coupon->enforceIsNew();
          $coupon->save();
        } else {
          foreach ($array as $key => $value) {
            $coupon->{$key} = $value;
          }
          $coupon->save();
        }

        /*
        //TODO
        $coupon = coupon_save($coupon, $edit);*/

        $coupon_picture = isset($array_input['coupon_picture']) ? $array_input['coupon_picture'] : ''; //TODO move picure to folder
        if ($coupon_picture) {
          $coupon_picture = base64_decode($coupon_picture);
          /*$file = file_save_data($coupon_picture, 'public://coupons/cid_' . $coupon->id() . '.png');*/
          $file = file_save_data($coupon_picture);

          if ($file) {
            $file->setTemporary();
            $coupon->setPicture($file);
            $coupon->save();
          }
          /*$coupon = coupon_save($coupon, array('picture_upload' =>
          $file));*/
        } elseif ($coupon_picture === 0 || $coupon_picture === '0') {
          $this->database->query('UPDATE {coupons} SET fid=:fid WHERE cid=:cid', array(':fid' => 0, ':cid' => $coupon->id()));
        }
        //$status = 204;

      }
      //return array('message' => NULL, 'status' => $status);变更接口 编辑coupon信息后返回编辑后或新增的coupon对象
      return array('data' => coupon_load($coupon->id(), TRUE));

    }
  }

  /**
   * delete coupons
   */
  public function deleteCoupons($coupon_ids) {
    $account = account_user_authenticate_by_http();
    $status = 404; 
    $message = array('message' => '删除失败');

    if ($account->id() && ($_SERVER['REQUEST_METHOD'] == 'DELETE')) {
      if (is_numeric($coupon_ids)) {
        $cids = (array) $coupon_ids;
      } else {
        $cids = explode(',', $coupon_ids);
      }
      $coupons = coupon_load_multiple($cids);

      $delete_cids = array();
      foreach ($coupons as $coupon) {
        if ($account->id() == $coupon->uid->value) {
          $delete_cids[] = $coupon->id();
        }
      }

      if (!empty($delete_cids)) {
        coupon_delete_multiple($delete_cids);
        $status = 204;
        $message = array('message' => '删除成功');
      }
    }
    return array('message' => $message, 'status' => $status);
  }

  /**
   * revoke coupon
   */
  public function revokeCoupon($coupon_id) {
    $coupon = coupon_load($coupon_id);
    $account = account_user_authenticate_by_http();
    $status = 200;
    $message = null;
    if ($account->id() == $coupon->uid->value) {
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $coupon->status = COUPON_STATUS_CANCEL;
        $coupon->save();
        /*$edit = array(
          'status' => -2,
        );
        coupon_save($coupon, $edit);*/
      } else {
        $status = 404;
        $message = array('message' => '撤销促销失败');
      }
    }
    return array('message' => $message, 'status' => $status);
  }

  /**
   * load coupons by store id
   */
  public function getCouponsByStoreid($store_id) {
    return $this->getCouponsByStoreidStatus($store_id, 'normal');
  }

  /**
   * load coupons by store id and status
   */
  public function getCouponsByStoreidStatus($store_id, $status) {
    //TODO request 
    $request = \Drupal::request();
    $store = store_load($store_id);
    if (!isset($store) || empty($store)) {
      return NULL;
    }

    switch ($status) {
      case 'pending':
        $coupon_status = 0;
        break;
      case 'normal':
        $coupon_status = 1;
        break;
      case 'expired':
        $coupon_status = array(-1, -2); // -1: expired, -2: revoked
        break;
      default:
        $coupon_status = 1;
        break;
    }
    $page = $request->query->get('page', 1);
    $per_page = $request->query->get('per_page', 5);

    $query = $this->database->select('coupons', 'c')
      ->fields('c', array('cid'))
      ->condition('sid', $store_id);

    if ($per_page && is_numeric($per_page) && $page && is_numeric($page)) {
      $startIndex = 0;
      if ($page > 1) {
        $startIndex = ($page - 1) * $per_page;
      }
      $query->range($startIndex, $per_page);
    }

    if (is_array($coupon_status)) {
      $query->condition('status', $coupon_status, 'IN');
    } else {
      $query->condition('status', $coupon_status);
    }
    $query->orderBy('created', 'DESC');

    $cids = $query->execute()->fetchCol();

    return coupon_load_multiple($cids);
  }


  /**
   * coupon bookmark
   */
  public function couponBookmark($coupon_id) {
    $account = account_user_authenticate_by_http();
    $status = 200;
    if ($account->id()) {
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!coupon_account_is_bookmark_coupon($account->id(), $coupon_id)) {
          coupon_bookmark($account->id(), $coupon_id);
        } else {
          $status = 422;
          $coupon = coupon_load($coupon_id);
          $data = array(
            'message' => '您已经收藏过' . $coupon->label() . '了!',
          );
        }
      }
      else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        if (!is_numeric($coupon_id)) {
          $coupon_id = explode(',', $coupon_id);
        }
        coupon_unbookmark($account->id(), $coupon_id);
        $status = 204;
      } else {
        if (!coupon_account_is_bookmark_coupon($account->id(), $coupon_id)) {
          $status = 404;
        }
      }
    }
    return array('data' => $data, 'status' => $status);
  }


  /**
   * get user bookmark coupons
   */
  public function getUserBookmarkCoupons(Request $request) {
    global $base_url;
    $size = $request->query->get('per_page', 10);
    $start = $request->query->has('page') ? ($request->query->get('page') - 1) * $size : 0;
    if (!is_numeric($size) || !is_numeric($start)) {
      return array('message' => array('message' => '参数错误!'), 'status' => 422);
    }

    $account = account_user_authenticate_by_http();
    if (!$account->id()) return FALSE;

    $query = $this->database->select('coupon_bookmarks', 'c');
    $query->addExpression('COUNT(c.cid)');
    $num_rows = $query->condition('uid', $account->id())
      ->execute()
      ->fetchField();

    $query = $this->database->select('coupon_bookmarks', 'c')
      ->fields('c', array('cid'))
      ->condition('uid', $account->id());
    if ($size) {
      $query->range($start, $size);
    }
    $cids = $query->execute()->fetchCol();

    if (count($cids)) {
      $coupons = coupon_load_multiple($cids);
    }
    $http_next = $http_last = "<$base_url/api/bookmark/coupons?";

    $page = $request->query->get('page', 1);
    
    $http_next .= "per_page=$size&";
    $http_last .= "per_page=$size&";

    if ($num_rows % $size) {
      $pages = (int)($num_rows / $size) + 1;
    } else {
      $pages = $num_rows / $size;
    }

    if ($page >= $pages) {
      $http_next = '';
    } else {
      $http_next .= 'page=' . ($page + 1) . '>;rel="next",';
    }
    $http_last .= 'page=' . $pages . '>;rel="last"';

    $header = array('Link' => $http_next . $http_last);

    return array('coupons' => $coupons, 'header' => $header);
  }

}
