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
use Drupal;

use Drupal\share\ShareInterface;
use Drupal\share\ShareCatalogInterface;

/**
 * Controller routines for share routes.
 */
class ShareController implements ContainerInjectionInterface {
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
    return new static($container->get('share.manager'));
  }

  /**
   * Constructs a ShareController object.
   */
  public function __construct(ShareManager $shareManager) {
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
      $image_factory = Drupal::service('image.factory');
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
      'user_favorites' => get_share_user_favorites($share->id()),
      'sold_count' => 0,
      'description' => $share->description->value,
    );
  }

  /**
   * Get share list
   * page callback for: api/share/list
   */
  public function shareList() {
    $return = $this->shareManager->getShares();
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
  public function userBookmarkShares() {
    $return = $this->shareManager->getUserBookmarkShares();
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
    if ($GLOBALS['user']->isAuthenticated()) {
      db_insert('share_bookmarks')
        ->fields(array(
          'uid' => $GLOBALS['user']->id(),
          'sid' => $share->id(),
          'created' => REQUEST_TIME,
        ))
        ->execute();
    }
    return new JsonResponse(array('bookmarked' => true));
  }

  public function unbookmark(Request $request, ShareInterface $share) {
    db_delete('share_bookmarks')
      ->condition('uid', $GLOBALS['user']->id())
      ->condition('sid', $share->id())
      ->execute();
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
   * page callback: admin/share/edit/{share}
   */
  public function shareAdminEdit(Request $request, ShareInterface $share) {
    module_load_include('admin.inc', 'share');
    return drupal_get_form('share_admin_edit_form', $share);
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
}
