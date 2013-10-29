<?php
/**
 * @file
 * Contains \Drupal\store\StoreManager.
 */

namespace Drupal\store;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Store Manager Service.
 */
class StoreManager {
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
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;


  /**
   * Constructs a StoreManager object.
   */
  public function __construct(Connection $database, EntityManager $entityManager, Request $request) {
    $this->database = $database;
    $this->entityManager = $entityManager;
    $this->request = $request;
  }

  /**
   * API user_authenticate httprequest head
   */
  protected function user_authenticate_by_http() {
    $uid = 0;
    $name = isset($_SERVER['PHP_AUTH_USER']) ? trim($_SERVER['PHP_AUTH_USER']) : '';
    $pass = isset($_SERVER['PHP_AUTH_PW']) ? trim($_SERVER['PHP_AUTH_PW']) : '';

    $tokens = isset($_SERVER['HTTP_AUTHORIZATION']) ? trim($_SERVER['HTTP_AUTHORIZATION']) : '';
    if ($tokens) {
      $tokens = explode(' ', $tokens);
      if ($tokens[0] == 'token') {
        $autho_token = account_autho_token_load(array('token' => $tokens[1]));
        if ($autho_token->uid) {
          return user_load($autho_token->uid);
        }
      }
    }
    
    if ($name && $pass) {
      if (!user_is_blocked($name)) {
        $uid = user_authenticate($name, $pass);
      }
    }
    return user_load($uid);
  }
  
  /**
   * search stores by catalog district city_id 
   */
  public function searchCatalogStores($catalog_cid) {
    //TODO replace $_GET $request->getQuery();
    global $base_url;

    $district = isset($_GET['district']) ? trim($_GET['district']) : 0;
    $size = isset($_GET['per_page']) ? $_GET['per_page'] : 100;
    $start = isset($_GET['page'])? ($_GET['page'] - 1) * $size : 0;
    $lat = isset($_GET['latitude']) ? $_GET['latitude'] : 0;
    $lng = isset($_GET['longitude']) ? $_GET['longitude'] : 0;
    $distance = isset($_GET['distance']) ? $_GET['distance'] : 0;
    $sort = isset($_GET['orderby']) ? $_GET['orderby'] : '离我最近';
    $city_id = isset($_GET['cityId']) ? $_GET['cityId'] : 0;

    $query = $this->database->select('stores', 's');
    $query->addExpression('COUNT(s.sid)');
    $query->condition('latitude', '', '<>');
    $query->condition('longitude', '', '<>');
    if ($catalog_cid) {
      $query->condition('cid', $catalog_cid);
    }
    if ($district) {
      $query->condition('district_id', $district);
    }
    if ($city_id) {
      $query->condition('city_id', $city_id);
    }
    if ($distance) {
      $distance_lat = $distance / 111111;
      $distance_lng = $distance / 100000;
    }
    if ($lat) {
      if ($distance) {
        $query->condition('latitude', (double)($lat + $distance_lat), '<')
          ->condition('latitude', (double)($lat - $distance_lat), '>')
          ->condition('latitude', 0, '<>');
      }
    }

    if ($lng) {
      if ($distance) {
        $query->condition('longitude', (double)($lng + $distance_lng), '<')
          ->condition('longitude', (double)($lng - $distance_lng), '>')
          ->condition('longitude', 0, '<>');
      }
    }
    $num_rows = $query->execute()->fetchField();

    if ($num_rows % $size) {
      $pages = (int)($num_rows / $size) + 1;
    } else {
      $pages = $num_rows / $size;
    }

    $select_sql = "SELECT sid FROM stores ";
    $where_sql = " WHERE name <> '' AND latitude <> '' AND longitude <> '' ";
    $order_by_sql = "";
    $limit = " LIMIT $start, $size";
    if ($catalog_cid) {
      $where_sql .= " AND cid=$catalog_cid";
    }
    if ($district) {
      $where_sql .= " AND district_id=$district";
    }
    if ($city_id) {
      $where_sql .= " AND city_id=$city_id";
    }
    if (in_array($sort, array('离我最近'))) {
      if ($lat && $lng) {
        $select_sql = "SELECT sid, ((ACOS(SIN($lat * PI() / 180) * SIN(latitude * PI() / 180) + COS($lat * PI() / 180) * COS(latitude * PI() / 180) * COS(($lng - longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance FROM stores ";
        if ($distance) {
          $begin_lat = (double)($lat - $distance_lat);
          $end_lat = (double)($lat + $distance_lat);
          $where_sql .= " AND CAST(latitude as DECIMAL(20, 16)) BETWEEN $begin_lat AND $end_lat AND latitude <> '0'";

          $begin_lng = (double)($lng - $distance_lng);
          $end_lng = (double)($lng + $distance_lng);
          $where_sql .= " AND CAST(longitude as DECIMAL(20, 16)) BETWEEN $begin_lng AND $end_lng AND longitude <> '0'";
        }

        $order_by_sql = " ORDER BY distance ASC, discount ASC";
      }
    } else if (in_array($sort, array('最新发布'))) {
      $order_by_sql = " ORDER BY update_at DESC, discount ASC";
    } else if (in_array($sort, array('使用最多'))) {
      $order_by_sql = " ORDER BY deal_count DESC, discount ASC";
    } 

    $result = $this->database->query($select_sql . $where_sql . $order_by_sql . $limit);
    $sids = array();
    foreach ($result as $row) {
      $sids[] = $row->sid;
    }
    if (count($sids)) {
      //TODO $this->entityManager->getStorageController('store')->loadMultiple($sids);
      $stores = store_load_multiple($sids);
    }

    $http_next = "<$base_url/api/taxonomy/$catalog_cid/stores?";
    $http_last = $http_next;

    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    
    $http_next .= "per_page=$size&";
    $http_last .= "per_page=$size&";

    if ($_GET['district']) {
      $http_next .= "district=" .$_GET['district'] . "&";
      $http_last .= "district=" .$_GET['district'] . "&";
    }
    if ($_GET['latitude']) {
      $http_next .= "latitude=" .$_GET['latitude'] . "&";
      $http_last .= "latitude=" .$_GET['latitude'] . "&";
    }
    if ($_GET['longitude']) {
      $http_next .= "longitude=" .$_GET['longitude'] . "&";
      $http_last .= "longitude=" .$_GET['longitude'] . "&";
    }
    if ($_GET['distance']) {
      $http_next .= "distance=" .$_GET['distance'] . "&";
      $http_last .= "distance=" .$_GET['distance'] . "&";
    }
    if ($_GET['orderby']) {
      $http_next .= "orderby=" .$_GET['orderby'] . "&";
      $http_last .= "orderby=" .$_GET['orderby'] . "&";
    }
    if ($page >= $pages) {
      $http_next = "";
    } else {
      $http_next .= 'page=' . ($page + 1) . '>;rel="next",';
    }
    $http_last .= 'page=' . $pages . '>;rel="last"';

    $header = array('Link' => $http_next . $http_last);

    return array('stores' => $stores, 'header' => $header);
  }

  /**
   * get user stores if has no store create id
   */
  public function getUserStores() {
    $stores = array();
    $current_account = $this->user_authenticate_by_http();
    if ($current_account->id()) {
      $sids = $this->database->query('SELECT sid FROM {stores} WHERE uid = :uid', array(':uid' => $current_account->id()))->fetchCol();
      if (count($sids)) {
        $stores = store_load_multiple($sids);
        foreach ($stores as $sid => $store) {
          $store_revision = store_revision_load($store->id());
          if ($store_revision && $store_revision->status == 0) {
            $store->name->value = $store_revision->name;
            $store->discount->value = $store_revision->discount;
            $store->address->value = $store_revision->address;
          }

          $stores[$sid] = $store;
        }
      } else {
        $stores[] = store_create_store($current_account);
      }
    }
    return $stores;
  }
  /**
   * get user stores by uid
   */
  public function getUserStoresByid($uid) {
    //todo 
    $current_account = $this->user_authenticate_by_http();

    $sids = $this->database->query('SELECT sid FROM {stores} WHERE uid = :uid', array(':uid' => $uid))->fetchCol();
    //TODO
    //$stores = $this->entityManager->getStorageController('store')->loadMultiple($sids);
    $stores = store_load_multiple($sids);
    //TODO checkt token
    //if ($uid == $current_account->id()) {
      foreach ($stores as $sid => $store) {
        $store_revision = store_revision_load($store->id());
        if ($store_revision && $store_revision->status == 0) {
          $store->name->value = $store_revision->name;
          $store->discount->value = $store_revision->discount;
          $store->address->value = $store_revision->address;
        }

        $stores[$sid] = $store;
      }
    //}
    return $stores;
  }

  /**
   * store utils
   */
  public function storeUtils($store_sid) {
    $store = store_load($store_sid);
    if (!$store) return array('message' => array('message' => '商家不存在'), 'status' => 422);

    $account = $this->user_authenticate_by_http(); 
    $json_data = file_get_contents('php://input');
    $store_revision = store_revision_load($store->id());
    if (isset($json_data) && $json_data) {
      $array = json_decode($json_data, TRUE);

      $name = $array['name'];
      $address = $array['address'];
      $cid = $array['taxoId'];
      $phone = $array['phone'];
      $hours = $array['hours'];
      $discount = $array['discount'];
      $latitude = $array['latitude'];
      $longitude = $array['longitude'];
      $districtId = $array['districtId'];
      $cityId = $array['cityId'];//实际意义不大

      if ($store_revision->sid && !$store_revision->status) {
        //副本里存在未审核数据
        if (!store_name_repeat($name, $store->id())) {
          $edit_array = array(
            'sid' => $store->id(), 
            'uid' => $account->id(),
            'discount' => $discount,
            'name' => $name,
            'address' => $address,
            'status' => 0,
          );
          $store_revision = store_revision_save($store_revision, $edit_array);
        } else {
          return  array('message' => array('message' => '商家名称已存在，请换个名字！'), 'status' => 422);
        }
      } else if ($name != $store->label() || $discount != $store->discount->value || $address != $store->address->value) {
        if (store_name_repeat($name, $store->id())) {
          return  array('message' => array('message' => '商家名称已存在，请换个名字！'), 'status' => 422);
        }

        $edit_array = array(
          'sid' => $store->id(), 
          'uid' => $account->id(),
          'discount' => $discount,
          'name' => $name,
          'address' => $address,
          'status' => 0,
        );
        $store_revision = store_revision_save($store_revision, $edit_array);
      }

      $values = array();
      if ($phone) {
        $values['phone'] = $phone;
      }
      if ($latitude) {
        $values['latitude'] = $latitude;
      }
      if ($longitude) {
        $values['longitude'] = $longitude;
      }
      if ($hours) {
        $values['hours'] = $hours;
      }
      if ($cid) {
        $values['cid'] = $cid;
      }
      if ($districtId) {
        $values['district_id'] = $districtId;
      }
      if ($cityId) {
        $values['city_id'] = $cityId;
      }
      
      foreach ($values as $key => $value) {
        $store->{$key} = $value;
      }
      $store->save();
      //$store = store_save($store, $values);
      /*if ($store->image_url && is_numeric($store->image_url)) {
        $store->image_url = file_load($store->image_url); 
      }*/
    }

    if ($store->district_id && !$store->city_id) {
      //TODO update stores city_id
      $city_id = $this->database->query('SELECT cid FROM {districts} WHERE did = :did', array(':did' => $store->district_id->value))->fetchField();
      $city_id && $store->city_id->value = $city_id;
    }

    if ($account->id() == $store->uid->value) {
      if (!$store_revision) {
        $store_revision = store_revision_load($store->id());
      }
      if ($store_revision && $store_revision->status == 0) {
        $store->name->value = $store_revision->name;
        $store->discount->value = $store_revision->discount;
        $store->address->value = $store_revision->address;
      }
    }
    return array('store' => $store);
  }
  
  /**
   * edit store and get status
   */
  public function storeEdit($store_id, $operate) {
    $status = 404;
    $store = store_load($store_id);
    if (!$store) return array('store' => FALSE, 'status' => $status);

    if ($operate == 'image') {
      $json_data = file_get_contents('php://input');
      if (isset($json_data) && $json_data) {
        $array = json_decode($json_data, TRUE); 

        $base = $array['image'];
        if ($base) {
          $avatar = base64_decode($base);

          $file = file_save_data($avatar);
          $file->setTemporary();

          $store->setPicture($file);
          $store->save();
          /*$values['picture_upload'] = $file;
          $store = store_save($store, $values);*/

          /*if ($store->image_url && is_numeric($store->image_url)) {
            $store->image_url = file_load($store->image_url); 
          }*/

          $store_revision = store_revision_load($store->id());
          if ($store_revision && $store_revision->status == 0) {
            $store->name->value = $store_revision->name;
            $store->discount->value = $store_revision->discount;
            $store->address->value = $store_revision->address;
          }

        }
      }
      return array('store' => $store, 'status' => $status);
    } else if ($operate == 'pending') {
      $sid = $this->database->select('store_revision', 's')
        ->fields('s', array('sid'))
        ->condition('status', 0)
        ->condition('sid', $store->id())
        ->condition('uid', $store->uid->value)
        ->execute()
        ->fetchField();
      $sid && $status = 200;
      return array('status' => $status);
    }
  }
  /**
   * user consumer store
   */
  public function userConsumer($store_id, $uid) {
    $account = $this->user_authenticate_by_http();
    if (!$account->id())  return array();

    $store = store_load($store_id);
    if (!$store)  return array();

    $record = $this->database->select('store_consumer_records', 's')
      ->fields('s', array('uid', 'created'))
      ->condition('uid', $uid)
      ->condition('sid', $store->id())
      ->orderBy('created', 'DESC')
      ->execute()->fetchObject();
    if ($record) {
      $now_time = date('Y-m-d', time());
      $last_record = date('Y-m-d', $record->created);
      $values = array();
      if ($now_time != $last_record) {
        ++$store->deal_count->value; 
        store_consumer_records_save($uid, $store->id());
      }
    }
    else {
      ++$store->deal_count->value; 
      ++$store->user_num->value;
      store_consumer_records_save($uid, $store->id());
    }
    
    $store->save();
    /*$store = store_save($store, $values);
    if ($store->image_url && is_numeric($store->image_url)) {
      $store->image_url = file_load($store->image_url); 
    }*/

    return $store;
  }

  /**
   * search stores 
   */
  public function searchStoresByKeywordCity($keyword, $city_id) {
    global $base_url;
    $param_cityId = isset($_GET['cityId']) ? $_GET['cityId'] : $city_id;
    $word = store_city_keyword_load($keyword);
    if ($word) {
      $this->database->update('city_keyword')
        ->fields(array('count' => $word->count + 1))
        ->condition('kid', $word->kid)
        ->condition('cid', $param_cityId)
        ->execute();
    } else {
      $this->database->insert('city_keyword')
        ->fields(array(
          'word' => $keyword,
          'count' => 1,
          'cid' => $param_cityId,
        ))
        ->execute();
    }

    $size = isset($_GET['per_page']) ? $_GET['per_page'] : 100;
    $start = isset($_GET['page'])? ($_GET['page'] - 1) * $size : 0;
    
    $query = $this->database->select('stores', 's');
    $query->addExpression('COUNT(s.sid)');
    if ($param_cityId)
      $query->condition('city_id', $param_cityId);
    $num_rows = $query->condition('name', '%' . db_like($keyword) . '%', 'LIKE')
      ->execute()
      ->fetchField();
      
    $query = $this->database->select('stores', 's')
      ->fields('s', array('sid'));
    if ($param_cityId)
      $query->condition('city_id', $param_cityId);
    if ($size) {
      $query->range($start, $size);
    }
    $sids = $query->condition('name', '%' . db_like($keyword) . '%', 'LIKE')
      ->execute()->fetchCol();
    
    $stores = store_load_multiple($sids);
    
    $http_next = "<$base_url/api/stores/$keyword/$param_cityId?cityId=$param_cityId";
    $http_last = $http_next;

    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    
    $http_next .= "&per_page=$size&";
    $http_last .= "&per_page=$size&";
    
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

    return array('stores' => $stores, 'header' => $header);
  }
  /**
   * search stores 
   */
  public function searchStores($keyword) {
    //TODO check url params has cid for city_keyword
    return $this->searchStoresByKeywordCity($keyword, 0);
  }
  
  /**
   * get hot search keyword
   */
  public function getHotKeyword($city_id) {
    //TODO 增加status字段 通过审核的才可以获取
    /*$query = db_select('city_keyword', 'c')
        ->fields('c', array('word'))
        ->orderBy('count', 'DESC')
        ->execute();
    $words = array();
    foreach ($query as $row) {
      $words[] = $row->word;
    }*/
    return array('火锅', 'KTV', '摄影', '烧烤', '咖啡', '养生',);
  }

  /**
   * store photos operation 
   * return $store not entity_load();
   */
  public function storePhotos($store_id) {
    $store = store_load($store_id);
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      return $this->database->query('SELECT photo1,photo2,photo3,photo4 FROM {stores} WHERE sid=:sid', array(':sid' => $store->id()))->fetchObject();
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $account = $this->user_authenticate_by_http(); 
      if ($store->uid->value != $account->id()) {
        return array('message' => array('message' => '对不起你没有权限操作!'), 'status' => 422);
      }
      $json_data = file_get_contents('php://input');
      if (isset($json_data) && $json_data) {
        /*[{'name': 'p1', id: 100, image_data: ''}, {'name', 'p1','id':'0','image_data':'xxxxxxxxxxx'},{'name': 'p3','id': '0'},{'name': 'p4', 'id': '0'}]*/
        $array = json_decode($json_data, TRUE);
        if (!is_array($array)) {
          $array = json_decode($array, TRUE);
        }
        foreach ($array as $photo) {
          switch ($photo['name']) {
            case 'p1':
              $field_name = 'photo1';
              break;
            case 'p2':
              $field_name = 'photo2';
              break;
            case 'p3':
              $field_name = 'photo3';
              break;
            case 'p4':
              $field_name = 'photo4';
              break;
          }
          if (!$field_name) {
            continue;
          }

          if ($photo['id'] && is_numeric($photo['id'])) {
            $field_value = $photo['id'];
          } else if (empty($photo['id']) && (!isset($photo['image_data']) || empty($photo['image_data']))) {
            $field_value = 0;
          } else if (empty($photo['id']) && $photo['image_data']) {
            $image_data = base64_decode($photo['image_data']);
            
            $picture_directory = file_default_scheme() . '://' . variable_get('store_photo_path', 'store_photos');
            file_prepare_directory($picture_directory, FILE_CREATE_DIRECTORY);

            $file = file_save_data($image_data, 'public://' . variable_get('store_photo_path', 'store_photos') . '/' . $field_name . '-' . $store->id() . '-' . time() . '.png');

            if ($file && $file->id()) {
              file_usage()->add($file, 'store','store', $store->id());
              //TODO file_useage()->delete
              $field_value = $file->id();
            }
          } else {
            $field_value = 0;
          }

          $this->database
            ->update('stores')
            ->fields(array($field_name => $field_value))
            ->condition('sid', $store->id())
            ->execute();
          //TODO if stores cache clear it or use store_save
        }
        $store = $this->database->query('SELECT photo1,photo2,photo3,photo4 FROM {stores} WHERE sid=:sid', array(':sid' => $store->id()))->fetchObject();
      }
    }
    return $store;
  }

}
