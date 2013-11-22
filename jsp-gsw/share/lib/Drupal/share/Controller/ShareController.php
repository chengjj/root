<?php

/**
 * @file
 * Contains \Drupal\share\Controller\ShareController.
 */
namespace Drupal\share\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
//use Drupal\Core\Controller\ControllerInterface;
use Drupal\share\ShareManager;

use Drupal\share\ShareInterface;
use Drupal\share\ShareCatalogInterface;
use Drupal\share\ShareStorageController;

/**
 * Controller routines for share routes.
 */
class ShareController implements ContainerInjectionInterface {
  protected $shareStorageController;

  /**
   * Share Manager Service.
   *
   * @var \Drupal\share\ShareManager
   */
  protected $shareManager;

  /**
   * Injects ShareManager Service.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorageController('share'),
      $container->get('share.manager')
    );
  }

  /**
   * Constructs a ShareController object.
   */
  public function __construct(ShareStorageController $shareStorageController, ShareManager $shareManager) {
    $this->shareStorageController = $shareStorageController;
    $this->shareManager = $shareManager;
  }

  /**
   * get share reponse
   */
  protected function get_share_response($share) {
    //large
    //1:1 450x450 
    //1:1.35 400x540 
    //1:1.5 300x450

    //small
    //1:1 200x200 
    //1:1.35 200x270 
    //1:1.5 200x300
    $picture = $share->getPicture();
    if (is_object($picture)) {
      $image_factory = \Drupal::service('image.factory');
      $image = $image_factory->get($picture->getFileUri());
      $width = $image->getWidth();
      $height = $image->getHeight();

      $scale = $height / $width;
      if ($scale <= 1) {
        $large_variables = array('style_name' => '450x450', 'uri' => $picture->getFileUri());
        $small_variables = array('style_name' => '200x200', 'uri' => $picture->getFileUri());
      } else if ($scale > 1 && $scale <= 1.35) {
        $large_variables = array('style_name' => '400x540', 'uri' => $picture->getFileUri());
        $small_variables = array('style_name' => '200x270', 'uri' => $picture->getFileUri());
      } else if ($scale > 1.35) {
        $large_variables = array('style_name' => '300x450', 'uri' => $picture->getFileUri());
        $small_variables = array('style_name' => '200x300', 'uri' => $picture->getFileUri());
      }
    }
    //TODO remove urldecode().
    $url = urldecode($share->url->value);
    $item_id = $share->item_id->value;
    if (!is_numeric($item_id)) {
      $pattern = '/id=(\d+)*/is';
      if (preg_match_all($pattern, $url, $matches)) {
        $item_id = $matches[1][0];
      } else {
        $item_id = 0;
      }
    }

    return array(
      'id' => $share->id(),
      'owner_id' => $share->uid->value,
      'title' => $share->label(),
      'price' => sprintf('%01.2f', $share->price->value),
      'smal_image_url' => $small_variables ? get_uri_by_image_style($small_variables) : '',
      'large_image_url' => $large_variables ? get_uri_by_image_style($large_variables) : '',
      'url' => $url,
      'item_id' => $item_id,
      'user_favorites' => $share->bookmark_count->value,
      'sold_count' => 0,
      'description' => $share->description->value,
    );
  }

  /**
   * Get share list
   * page callback for: api/share/list
   */
  public function shareList(Request $request) {
    $return = $this->shareManager->getShares($request);
    if (isset($return['message'])) {
      return new JsonResponse($return['message'], $return['status']);
    } else {
      $shares = array();
      foreach ($return['shares'] as $share) {
        $shares[] = $this->get_share_response($share);
      }
      return new JsonResponse($shares, 200, $return['header']);
    }
  }
  /**
   * Get share list
   * page callback for: share/editorshare
   */
  public function editorshare() {
    return array('#theme' => 'share_editor_list');
  }
  /**
   * bookmark share 
   * page callback for: api/user/bookmark/share/{share_id}
   */
  public function shareBookmark($share_id) {
    $return = $this->shareManager->shareBookmark($share_id);
    return new JsonResponse($return['data'], $return['status']);
  }

  /**
   * user bookmark shares
   * page callback for: api/bookmark/shares
   */
  public function userBookmarkShares(Request $request) {
    $return = $this->shareManager->getUserBookmarkShares($request);
    if (isset($return['message'])) {
      return new JsonResponse($return['message'], $return['status']);
    } else {
      $shares = array();
      foreach ($return['shares'] as $share) {
        $shares[] = $this->get_share_response($share);
      }
      return new JsonResponse($shares, 200, $return['header']);
    }
  }

  public function bookmarked(Request $request, ShareInterface $share) {
    $bookmarked = false;
    if ($GLOBALS['user']->isAuthenticated()) {
      $query = db_select('share_bookmarks', 'b');
      $query->addField('b', 'uid');
      $query->condition('b.sid', $share->id());
      $query->condition('b.uid', $GLOBALS['user']->id());
      if ($bookmark = $query->execute()->fetchObject()) {
        $bookmarked = true;
      }
    }
    return new JsonResponse(array('bookmarked' => $bookmarked));
  }

  public function bookmark(Request $request, ShareInterface $share) {
    $this->shareStorageController->bookmark($share);

    return new JsonResponse(array('bookmarked' => true));
  }

  public function unbookmark(Request $request, ShareInterface $share) {
    $this->shareStorageController->unbookmark($share);

    return new JsonResponse(array('bookmarked' => false));
  }

  public function catalogChidren(Request $request, ShareCatalogInterface $parent_catalog) {
    /*$catalogs = share_catalog_load_children($parent_catalog->id());
    $links = array();
    foreach ($catalogs as $catalog) {
      $link = array('title' => $catalog->name->value, 'href'=> 'share', 'query' => array('cid' => $catalog->id()));
      $links[] = $link;
    }

    return new Response($links);*/
    return new JsonResponse(array('bookmarked' => false));
  }

  public function front(Request $request) {
    return array('#theme' => 'shares');
  }

  /**
   * page callback: share/add
   */
  public function shareAdd() {
    return array('#theme' => 'share_add_link');
  }

  /**
   * page callback: share/js/{option}
   */
  public function shareJs($option) {
    module_load_include('pages.inc', 'share');
    return share_js($option);
  }

  /**
   * page callback: admin/share
   */
  public function shareManage() {
    module_load_include('admin.inc', 'share');
    return drupal_get_form('share_admin_share_list');
  }

  /**
   * page callback: admin/share/delete/{share}
   */
  public function shareAdminDelete(Request $request, ShareInterface $share) {
    module_load_include('admin.inc', 'share');
    return drupal_get_form('share_admin_delete_form', $share);
  }

  /**
   * page callback: /share/search
   */
  public function shareSearch() {
    return array('#theme' => 'share_search');
  }

  /**
   * page callback: /admin/taobao/shares
   */
  public function shareFromTaobao() {
    module_load_include('admin.inc', 'share');
    return drupal_get_form('share_admin_get_form_taobao_form');
  }


  public function shareTitle(ShareInterface $share) {
    return $share->label();
  }

  /**
   * page callback: api/share/editor/list
   */
  public function advBlockShares(Request $request) {
    global $base_url;
    $size = $request->query->get('per_page', 100);
    $start = $request->query->has('page') ? ($request->query->get('page') - 1) * $size : 0;
    $sort = $request->query->get('orderby', '最热商品');

    $bid = db_query('SELECT bid FROM {adv_blocks} WHERE title = :title AND type = :type', array(':title' => '首页热门团购', ':type' => 'share'))->fetchField();
    if (!$bid) {
      $bid = \Drupal::config('app.adv_block.shares')->get('default_bid');
    }

    $query = db_select('adv_block_items', 'a');
    $query->addExpression('COUNT(a.iid)');
    $num_rows = $query->execute()->fetchField();

    if ($num_rows % $size) {
      $pages = (int)($num_rows / $size) + 1;
    } else {
      $pages = $num_rows / $size;
    }

    $select_sql = "SELECT s.sid FROM shares s INNER JOIN adv_block_items a ON a.entity_id = s.sid WHERE a.bid = $bid ";
    $order_by_sql = "";
    $limit = " LIMIT $start, $size";

    switch ($sort) {
      case '最热商品':
        $order_by_sql = ' ORDER BY s.bookmark_count DESC,s.comment_count DESC, s.view_count DESC';
        break;
      case '最新发布':
        $order_by_sql = ' ORDER BY s.created DESC';
        break;
    }

    $sids = db_query($select_sql . $order_by_sql . $limit)->fetchCol();
    $response = array();
    foreach (share_load_multiple($sids) as $share) {
      $response[] = $this->get_share_response($share);
    }

    $http_next = "<$base_url/api/share/editor/list?";
    $http_last = $http_next;

    $page = $request->query->get('page', 1);
    
    $http_next .= "per_page=$size&";
    $http_last .= "per_page=$size&";

    if ($request->query->get('orderby')) {
      $http_next .= "orderby=" .$request->query->get('orderby') . "&";
      $http_last .= "orderby=" .$request->query->get('orderby') . "&";
    }
    if ($page >= $pages) {
      $http_next = "";
    } else {
      $http_next .= 'page=' . ($page + 1) . '>;rel="next",';
    }
    $http_last .= 'page=' . $pages . '>;rel="last"';

    $header = array('Link' => $http_next . $http_last);

    return new JsonResponse($response, 200, $header);
  }

  /**
   * page callback: api/share_catalog/list
   */
  public function appShareCatalogList(Request $request) {
    foreach (share_catalog_load_children(0) as $share_catalog) {
      $response[] = array(
        'id' => $share_catalog->id(),
        'parent_cid' => $share_catalog->parent_cid->value,
        'name' => $share_catalog->label(),
        'weight' => $share_catalog->weight->value,
      );
    }
    return new JsonResponse($response);
  }

}
