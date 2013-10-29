<?php
/**
 * @file
 * Contains \Drupal\share\ShareManager.
 */

namespace Drupal\share;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Share Manager Service.
 */
class ShareManager {
  /**
   * Database Service Object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Entity manager Service Object.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * Constructs a ShareManager object.
   */
  public function __construct(Connection $database, EntityManager $entityManager) {
    $this->database = $database;
    $this->entityManager = $entityManager;
  }
  
  /**
   * load shares
   */
  public function getShares() {
    global $base_url;

    $size = isset($_GET['per_page']) ? $_GET['per_page'] : 10;
    $start = isset($_GET['page'])? ($_GET['page'] - 1) * $size : 0;

    if (!is_numeric($size) || !is_numeric($start)) {
      return array('message' => array('message' => '参数错误!'), 'status' => 422);
    }
    $query = $this->database->select('shares', 's');
    $query->addExpression('COUNT(s.sid)');
    $query->condition('status', 1);
    $num_rows = $query->execute()->fetchField();

    if ($num_rows % $size) {
      $pages = (int)($num_rows / $size) + 1;
    } else {
      $pages = $num_rows / $size;
    }

    $query = $this->database->select('shares', 's')
      ->fields('s', array('sid'))
      ->condition('status', 1);
    $query->orderBy('created', 'DESC');

    if ($size) {
      $query->range($start, $size);
    }
    $sids = $query->execute()
      ->fetchCol();
    if (count($sids)) {
      $shares = share_load_multiple($sids);
    }

    $http_next = $http_last = "<$base_url/api/share/list?";

    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    
    $http_next .= "per_page=$size&";
    $http_last .= "per_page=$size&";

    if ($page >= $pages) {
      $http_next = "";
    } else {
      $http_next .= 'page=' . ($page + 1) . '>;rel="next",';
    }
    $http_last .= 'page=' . $pages . '>;rel="last"';

    $header = array('Link' => $http_next . $http_last);

    return array('shares' => $shares, 'header' => $header);
  }

  /**
   * share bookmark
   */
  public function shareBookmark($share_id) {
    $account = account_user_authenticate_by_http();
    $status = 200;
    if ($account->id()) {
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!share_account_is_bookmark_share($account->id(), $share_id)) {
          share_bookmark($account->id(), $share_id);
        } else {
          $status = 422;
          $share = share_load($share_id);
          $data = array(
            'message' => '您已经收藏过' . $share->label() . '了!',
          );
        }
      }
      else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        if (!is_numeric($share_id)) {
          $share_id = explode(',', $share_id);
        }
        share_unbookmark($account->id(), $share_id);
        $status = 204;
      } else {
        if (!share_account_is_bookmark_share($account->id(), $share_id)) {
          $status = 404;
        }
      }
    }
    return array('data' => $data, 'status' => $status);
  }

  /**
   * get user bookmark shares
   */
  public function getUserBookmarkShares() {
    global $base_url;
    $size = isset($_GET['per_page']) ? $_GET['per_page'] : 10;
    $start = isset($_GET['page'])? ($_GET['page'] - 1) * $size : 0;
    if (!is_numeric($size) || !is_numeric($start)) {
      return array('message' => array('message' => '参数错误!'), 'status' => 422);
    }

    $account = account_user_authenticate_by_http();
    if (!$account->id()) return FALSE;

    $query = $this->database->select('share_bookmarks', 's');
    $query->addExpression('COUNT(s.sid)');
    $num_rows = $query->condition('uid', $account->id())
      ->execute()
      ->fetchField();

    $query = $this->database->select('share_bookmarks', 's')
      ->fields('s', array('sid'))
      ->condition('uid', $account->id());
    if ($size) {
      $query->range($start, $size);
    }
    $sids = $query->execute()->fetchCol();

    if (count($sids)) {
      $shares = share_load_multiple($sids);
    }
    $http_next = $http_last = "<$base_url/api/bookmark/shares?";

    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    
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

    return array('shares' => $shares, 'header' => $header);
  }

}
