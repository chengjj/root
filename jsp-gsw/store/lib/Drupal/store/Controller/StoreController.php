<?php

/**
 * @file
 * Contains \Drupal\store\Controller\StoreController.
 */
namespace Drupal\store\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\store\StoreManager;

use Drupal\store\StoreInterface;

/**
 * Controller routines for store routes.
 */
class StoreController implements ContainerInjectionInterface {
  /**
   * Store Manager Service.
   *
   * @var \Drupal\store\StoreManager
   */
  protected $storeManager;

  /**
   * Injects StoreManager Service.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('store.manager'));
  }

  /**
   * Constructs a StoreController object.
   */
  public function __construct(StoreManager $storeManager) {
    $this->storeManager = $storeManager;
  }

  /**
   * page callback for:api/taxonomy/{catalog_cid}/stores
   */
  public function searchCatalogStores($catalog_cid, Request $request) {
    $return = $this->storeManager->searchCatalogStores($catalog_cid, $request);
    $stores = array();
    foreach ($return['stores'] as $store) {
      $stores[] = $this->get_store_response($store);
    }
    return new JsonResponse($stores, 200, $return['header']);
  }

  /**
   * get store response
   */
  protected function get_store_response($store) {
    $picture = $store->getPicture();
    $latest_coupon = coupon_latest_coupon($store->id());
    $coupon_title = isset($latest_coupon) ? $latest_coupon->label() : '';
    return array(
      'id' => $store->id(),
      'owner_id' => $store->uid->value,
      'name' => $store->name->value,/* for store_revision->name*/
      'image_url' => $picture ? file_create_url($picture->getFileUri()) : variable_get('store_default_picture', 'http://api.gsw100.com/sites/default/files/store_default_picture.png'),
      'latitude' => $store->latitude->value,
      'longitude' => $store->longitude->value,
      'address' => $store->address->value,
      'phone' => $store->phone->value,
      'hours' => $store->hours->value ? $store->hours->value : '8:00-21:00',/*fix android*/
      'discount' => $store->discount->value,
      'updated_at' => date('Y-m-d H:i:s', $store->update_at->value),
      'coupon_count' => $store->coupon_count->value,
      'taxo_id' => $store->cid->value,
      'deal_count' => $store->deal_count->value,
      'district_id' => $store->district_id->value,
      'city_id' => $store->city_id->value,
      'user_count' => $store->user_num->value,
      'follow_count' => $store->follow_count->value,
      'coupon_title' => $coupon_title,
      'comment_count' => $store->comment_count->value,
    );
  }

  /**
   * page callback for: api/user/{user_id}/stores
   */
  public function userStoresByUid($user_id) {
    $stores = array();
    foreach ($this->storeManager->getUserStoresByid($user_id) as $store) {
      $stores[] = $this->get_store_response($store);
    }
    return new JsonResponse($stores);
  }

  /**
   * page callback for: api/user/stores
   */
  public function userStores() {
    $stores = array();
    foreach ($this->storeManager->getUserStores() as $store) {
      $stores[] = $this->get_store_response($store);
    }
    return new JsonResponse($stores);
  }

  /**
   * page callback for: api/store/{store_sid}
   */
  public function storeUtils($store_sid) {
    $return = $this->storeManager->storeUtils($store_sid);
    if (isset($return['message'])) {
      return new JsonResponse($return['message'], $return['status']);
    } else {
      return new JsonResponse($this->get_store_response($return['store']));
    }
  }

  /**
   * page callback for: api/store/{store_id}/consume/{user_uid}
   */
  public function storeConsumer($store_id, $user_uid) {
    $store = $this->storeManager->userConsumer($store_id, $user_uid);
    return new JsonResponse($this->get_store_response($store));
  }

  /**
   * page callback for:api/store/{store_id}/{operate}
   */
  public function storeEdit($store_id, $operate) {
    $return = $this->storeManager->storeEdit($store_id, $operate);
    if ($return['store']) {
      return new JsonResponse($this->get_store_response($return['store']));
    } else {
      return new JsonResponse(NULL, $return['status']);
    }
  }

  /**
   * page callback for:api/stores
   */
  public function searchStores() {
    return new JsonResponse(array('message' => '请输入要搜索的关键词'), 404);
  }

  /**
   * page callback for:api/stores/{keyword}
   */
  public function searchStoresByKeyword($keyword, Request $request) {
    $return = $this->storeManager->searchStores($keyword, $request);
    $stores = array();
    foreach ($return['stores'] as $store) {
      $stores[] = $this->get_store_response($store);
    }
    return new JsonResponse($stores, 200, $return['header']);
  }

  /**
   * page callback for:api/stores/{keyword}/{city_id}
   */
  public function searchStoresByKeywordCity($keyword, $city_id, Request $request) {
    $return = $this->storeManager->searchStoresByKeywordCity($keyword, $city_id, $request);
    $stores = array();
    foreach ($return['stores'] as $store) {
      $stores[] = $this->get_store_response($store);
    }
    return new JsonResponse($stores, 200, $return['header']);
  }

  /**
   * page callback for:api/hotkeywords/{city_id}
   */
  public function hotKeyword($city_id) {
    $keywords = $this->storeManager->getHotKeyword($city_id);
    return new JsonResponse($keywords);
  }

  /**
   * page callback for:api/store/{store_id}/photos
   */
  public function storePhotos($store_id) {
    $store = $this->storeManager->storePhotos($store_id);
    if (is_array($store) && isset($store['message'])) {
      return new JsonResponse($store['message'], $store['status']);
    } else {
      $response = array();
      $i = 1;
      foreach (array('photo1', 'photo2', 'photo3', 'photo4') as $field_name) {
        if ($store->{$field_name}) {
          $store->{$field_name} = file_load($store->{$field_name});
        } else {
          $store->{$field_name} = 0;
        }
        $response[] = array(
          'name' => 'p' . $i,
          'id' => $store->{$field_name} ? $store->{$field_name}->id() : 0, 
          'default_image' => $store->{$field_name} ? file_create_url($store->{$field_name}->getFileUri()) : '',
          'thumbnail_image' => $store->{$field_name} ? get_uri_by_image_style(array('style_name' => '128x128', 'uri' => $store->{$field_name}->getFileUri())) : '',
        );
        $i ++;
      }
      return new JsonResponse($response);
    }
  }

  /**
   * page callback: api/adv_block/stores/{city_id}
   */
  public function advBlockStores(Request $request, $city_id) {
    $response = array();
    $adv_block_name = '手机客户端精选推荐位商家(640x390)';
    if ($bid = db_query('SELECT bid FROM {adv_blocks} WHERE type=:type AND title=:title', array(':type' => 'store', ':title' => $adv_block_name))->fetchField()) {
      //TODO use adv_block_items.city_id AND stores.city_id
      $result = db_query('SELECT a.iid, a.entity_id, a.title, a.picture FROM {adv_block_items} a INNER JOIN {stores} s ON a.entity_id = s.sid WHERE s.city_id = :city_id AND a.bid = :bid ORDER BY a.weight', array(':city_id' => $city_id, ':bid' => $bid));
      foreach ($result as $row) {
        if ($row->entity_id && $store = store_load($row->entity_id)) {
          $picture = FALSE;
          if ($row->picture) {
            $picture = file_load($row->picture);
          }
          if (!$picture) {
            $picture = $store->getPicture();
          }
          $response[] = array(
            'id' => $row->iid,
            'title' => $row->title ? $row->title : $store->label(),
            'image_url' => $picture ? get_uri_by_image_style(array('style_name' => '640x390', 'uri' => $picture->getFileUri())) : '', 
            'city_id' => $city_id,
            'store_id' => $store->id(),
          );
        }
      }
    }
    return new JsonResponse($response);
  }

  public function followed(Request $request, StoreInterface $store) {
    $user = \Drupal::currentUser();
    $followed = false;
    if ($user->isAuthenticated()) {
      $query = db_select('store_follow', 'f');
      $query->addField('f', 'uid');
      $query->condition('f.uid', $user->id());
      $query->condition('f.sid', $store->id());
      if ($query->execute()->fetchObject()) {
        $followed = true;
      }
    }
    return new JsonResponse(array('followed' => $followed));
  }

  public function follow(Request $request, StoreInterface $store) {
      
    $user = \Drupal::currentUser();
    
    db_insert('store_follow')
      ->fields(array(
        'uid' => $user->id(),
        'sid' => $store->id(),
      ))
      ->execute();
    $store->follow_count->value ++;
    $store->save();

    $account = entity_load('account', $user->id());
    
    $account->store_follow_count->value ++;
    
    $account->save();
    return new JsonResponse(array('followed' => true));
  }

  public function unfollow(Request $request, StoreInterface $store) {
    $user = \Drupal::currentUser();
    
    db_delete('store_follow')
      ->condition('uid', $user->id())
      ->condition('sid', $store->id())
      ->execute();

    
    $store->follow_count->value --;
    $store->save();
    $account = entity_load('account', $user->id());
    
    $account->store_follow_count->value --;
    $account->save();

    return new JsonResponse(array('followed' => false));
  }

  /**
   * page callback:download/consumer
   */
  public function downloadConsumer() {
    module_load_include('pages.inc', 'store');
    return download_consumer_page();
  }

  /**
   * page callback:download/consumer
   */
  public function downloadStore() {
    module_load_include('pages.inc', 'store');
    return download_store_android_page();
  }

  /**
   * page callback: download
   */
  public function download() {
    module_load_include('pages.inc', 'store');
    return download_page();
  } 

  /**
   * page callback: download/store/android
   */
  public function downloadAndroid() {
    module_load_include('pages.inc', 'store');
    return download_store_page();
  }

  /**
   * page callback: store/audit/list
   */
  public function storeAdminAudit() {
    module_load_include('pages.inc', 'store');
    return drupal_get_form('store_audit_list_form');
  }

  /**
   * page callback: admin/store
   */
  public function storeAdmin() {
    module_load_include('pages.inc', 'store');
    return drupal_get_form('admin_store_list');
  }
  /**
   * page callback: store/add
   */
  public function storeAdd() {
    $store = entity_create('store', array());
    return \Drupal::entityManager()->getForm($store);
  }

  /**
   * page callback: store/{store}/register
   */
  public function storeRegisterUser(StoreInterface $store) {
    module_load_include('pages.inc', 'store');
    return drupal_get_form('admin_store_register_user_form', $store);
  }

  /**
   * page callback: store/{store}/delete
   */
  public function storeDeleteForm(StoreInterface $store) {
    module_load_include('pages.inc', 'store');
    return drupal_get_form('admin_store_delete', $store);
  }

  /**
   * page callback: /store/search
   */
  public function search() {
    return array('#theme' => 'stores');
  }

  /**
   * page callback: store/js/{action}
   */
  public function ajax(Request $request, $action) {
    module_load_include('pages.inc', 'store');
    return store_js($action);
  }

  public function storeTitle(StoreInterface $store) {
    return $store->label();
  }

  /**
   * page callback: /api/store/{store_id}/comment
   */
  public function storeComment(Request $request, $store_id) {
    $method = $request->getMethod();
    if ($method == 'GET') {
      global $base_url;
      $size = $request->query->get('per_page', 10);
      $start = $request->query->has('page') ? ($request->query->get('page') - 1) * $size : 0;

      $num_rows = db_query('SELECT COUNT(cid) FROM {store_comment} WHERE sid=:sid', array(':sid' => $store_id))->fetchField();
      if ($num_rows % $size) {
        $pages = (int)($num_rows / $size) + 1;
      } else {
        $pages = $num_rows / $size;
      }

      $cids = db_query("SELECT cid FROM {store_comment} WHERE sid = $store_id LIMIT $start, $size")->fetchCol();
      $response = array();
      foreach (store_comment_load_multiple($cids) as $store_comment) {
        $nickname = '匿名';
        if ($account = $store_comment->getAuthor()) {
          $nickname = (isset($account->nickname) && $account->nickname) ? $account->nickname : $account->getUsername();
        }
        $response[] = array(
          'id' => $store_comment->id(),
          'sid' => $store_comment->sid->value,
          'owner_id' => $store_comment->uid->value,
          'owner_name' => $nickname,
          'rank' => $store_comment->rank->value,
          'subject' => $store_comment->label(),
          'created' => date('Y-m-d H:i:s', $store_comment->created->value),
        );
      }

      $http_next = "<$base_url/api/store/$store_id/comment?";
      $http_last = $http_next;

      $page = $request->query->get('page', 1);
      
      $http_next .= "per_page=$size&";
      $http_last .= "per_page=$size&";

      if ($page >= $pages) {
        $http_next = "";
      } else {
        $http_next .= 'page=' . ($page + 1) . '>;rel="next",';
      }
      $http_last .= 'page=' . $pages . '>;rel="last"';

      $header = array('Link' => $http_next . $http_last);

      return new JsonResponse($response, 200, $header);
    } else if ($method == 'POST') {
      $account = $this->storeManager->user_authenticate_by_http();
      if ($account && $account->id()) {
        $json_data = file_get_contents('php://input');
        if (isset($json_data) && $json_data) {
          $array = json_decode($json_data, TRUE);
          if (!is_array($array)) {
            $array = json_decode($array, TRUE);
          }
          if (isset($array['subject']) && $array['subject']) {
            $store_comment = array(
              'subject' => $array['subject'],
              'rank' => $array['rank'] ? 1 : 0,
              'uid' => $account->id(),
              'sid' => $store_id,
              'status' => COMMENT_PUBLISHED,
              'created' => REQUEST_TIME,
            );
            $store_comment = entity_create('store_comment', $store_comment);
            $store_comment->save();

            $nickname = (isset($account->nickname) && $account->nickname) ? $account->nickname : $account->getUsername();
            $response = array(
              'id' => $store_comment->id(),
              'sid' => $store_comment->sid->value,
              'owner_id' => $store_comment->uid->value,
              'owner_name' => $nickname,
              'rank' => $store_comment->rank->value,
              'subject' => $store_comment->label(),
              'created' => date('Y-m-d H:i:s', $store_comment->created->value),
            );

            return new JsonResponse($response); 
          }
        }

      } else {
        return new JsonResponse(array('message' => '对不起你没有权限操作!'),  422);
      }
    }
  }

  /**
   * page callback: /api/user/store/comments
   */
  public function userComments(Request $request) {
    $account = $this->storeManager->user_authenticate_by_http();
    if ($account && $account->id()) {
      global $base_url;
      $size = $request->query->get('per_page', 10);
      $start = $request->query->has('page') ? ($request->query->get('page') - 1) * $size : 0;
      if ($request->query->has('rank')) {
        $rank = $request->query->get('rank');
      }
      $sid = $request->query->get('sid', 0);
      
      $query = db_select('store_comment', 's');
      $query->addExpression('COUNT(s.cid)');
      isset($rank) && $query->condition('rank', $rank);
      $sid && $query->condition('sid', $sid);
      $num_rows = $query->condition('uid', $account->id())
        ->execute()
        ->fetchField();

      if ($num_rows % $size) {
        $pages = (int)($num_rows / $size) + 1;
      } else {
        $pages = $num_rows / $size;
      }

      $query = db_select('store_comment', 's')
        ->fields('s', array('cid'))
        ->condition('uid', $account->id()); 

      if ($size) {
        $query->range($start, $size);
      }
      isset($rank) && $query->condition('rank', $rank);
      $sid && $query->condition('sid', $sid);

      $cids = $query->execute()->fetchCol();

      $response = array();
      foreach (store_comment_load_multiple($cids, TRUE) as $store_comment) {
        $response[] = array(
          'id' => $store_comment->id(),
          'sid' => $store_comment->sid->value,
          'store_name' => $store_comment->sid->entity->label(),
          'owner_id' => $store_comment->uid->value,
          'rank' => $store_comment->rank->value,
          'subject' => $store_comment->label(),
          'created' => date('Y-m-d H:i:s', $store_comment->created->value),
        );
      }

      $http_next = "<$base_url/api/user/store/comments?";
      $http_last = $http_next;

      $page = $request->query->get('page', 1);
      
      $http_next .= "per_page=$size&";
      $http_last .= "per_page=$size&";
      
      if (isset($rank)) {
        $http_next .= "rank=$rank&";
        $http_last .= "rank=$rank&";
      }

      if ($sid) {
        $http_next .= "sid=$sid&";
        $http_last .= "sid=$sid&";
      }

      if ($page >= $pages) {
        $http_next = "";
      } else {
        $http_next .= 'page=' . ($page + 1) . '>;rel="next",';
      }
      $http_last .= 'page=' . $pages . '>;rel="last"';

      $header = array('Link' => $http_next . $http_last);
      return new JsonResponse($response, 200, $header);
    } else {
      return new JsonResponse(array('message' => '对不起你没有权限操作!'),  422);
    }
  }

}
