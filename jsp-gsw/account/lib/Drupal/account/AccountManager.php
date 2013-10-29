<?php
/**
 * @file
 * Contains \Drupal\account\AccountManager.
 */

namespace Drupal\account;

use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\user\Plugin\Core\Entity\User;

/**
 * Account Manager Service.
 */
class AccountManager {
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
   * Constructs a AccountManager object.
   */
  public function __construct(Connection $database, EntityManager $entityManager) {
    $this->database = $database;
    $this->entityManager = $entityManager;
  }

  /**
   * register user roles $roles['Store user']
   */
  function registerStoreRole($name, $pass) {
    if ($name && $pass) {
      $account = user_load_by_name($name);
      if (is_object($account) && $account->id()) {
        $result = -1;
      } else {
        $uuid = new Uuid();
        $array = array(
          'name' => $name,
          'pass' => $pass,
          'status' => 1,
          'uuid' => $uuid->generate(),
          'created' => time(),
        );
        $account = entity_create('user', $array);
        $account->enforceIsNew();
        //$account = new User($array, 'user');
        $account->save(); 
        //TODO add role
        $account->addRole('Store user');
        //$account->roles['Store user'] = 'Store user';
        $account->save(); 

        $user_account = account_load($account->id());
        $user_account->phone = $name; 
        $user_account->save();

        // Log user in.
        /*$form_state['uid'] = $account->id();
        user_login_finalize($form_state);*/
      }
    }
    return $account;
  }
  
  /**
   * register user send  msg
   */
  public function register($phone) {
    $status = 200;
    $account = user_load_by_name($phone);
    if (!$account) {
      $account = user_load_by_phone($phone);
    }
    
    if (!$account) {
      //user not found,next send msg
      if (strlen($phone) == 11) {
        //$pass = substr(md5($phone . date('Ymd')), -6);
        $pass = account_generate_code($phone);
        $content = '感谢您注册贵客,您的初始密码为:' . $pass . ',该密码仅当天有效,请凭此密码登录以创建帐号,登录后请及时修改密码,谢谢![贵客]';

        $result = account_send_msg($content, $phone);

        if ($result['status'] == 'Processing') {
          $status = 200;
        } else if ($result['status'] == 'Failed') {
          //send msg failed
          $status = 422; //Unprocessable Entity
          $errors = array();
          $errors[] = array(
            'resource' => 'Account',
            'field' => 'name',
            'code' => 'invalid',
          );
          $data = array(
            'message' => $result['statusmessage'],
            /*'errors' => $errors,*/
          );
        }
      } else {
        //phone number invalid
        $status = 422; //Unprocessable Entity
        $errors = array();
        $errors[] = array(
          'resource' => 'Account',
          'field' => 'name',
          'code' => 'invalid',
        );
        $data = array(
          'message' => '手机号码错误!',
          /*'errors' => $errors,*/
        );
      }
    } else {
      //user already exists
      $status = 422; //Unprocessable Entity
      $errors = array();
      $errors[] = array(
        'resource' => 'Account',
        'field' => 'name',
        'code' => 'already_exists',
      );
      $data = array(
        'message' => '该手机号已注册!',
        /*'errors' => $errors,*/
      );
    }

    return array('data' => $data, 'status' => $status);
  }

  /**
   * phone verify 
   */
  public function phoneVerify($phone) {
    $account = user_load_by_name($phone);
    if (!$account) {
      $account = user_load_by_phone($phone);
    }
    if (is_object($account) && $account->id()) {
      $pass = account_generate_code($phone);
      $content = '您在贵客申请的验证码为:' . $pass . ',该验证码今日内有效,凭此验证码可重新设置密码,谢谢![贵客]';

      $result = account_send_msg($content, $phone);
    }

    $data = null;
    if ($result['status'] == 'Processing') {
      $status = 200;
    } else {
      $status = 422; //Unprocessable Entity
      $errors = array();
      $errors[] = array(
        'resource' => 'Account',
        'field' => 'name',
        'code' => 'missing',
      );
      $data = array(
        'message' => '该手机号还未注册!',
        /*'errors' => $errors,*/
      );
    }
    return array('data' => $data, 'status' => $status);
  }

  /**
   * user follow store
   */
  public function getFollowStore($store_sid) {
    $account = $this->user_authenticate_by_http();
    $status = 200;
    if ($account->id()) {
      if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        if (!store_account_is_followed_store($account->id(), $store_sid)) {
          store_account_follow_save($account->id(), $store_sid);
        } else {
          $status = 422;
          $store = store_load($store_sid);
          $data = array(
            'message' => '您已经关注过' . $store->label() . '了!',
          );
        }
      }
      else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        if (!is_numeric($store_sid)) {
          $store_sid = explode(',', $store_sid);
        }
        store_account_follow_delete($account->id(), $store_sid);
        $status = 204;
      } else {
        if (!store_account_is_followed_store($account->id(), $store_sid)) {
          $status = 404;
        }
      }
    }
    return array('data' => $data, 'status' => $status);
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
   * user tripartite login
   */
  public function tripartiteLogin() {
    $result = 0;//-1:用户被block 0:用户名或密码错误 uid:登录成功
    $account = FALSE;
    $name = isset($_SERVER['PHP_AUTH_USER']) ? trim($_SERVER['PHP_AUTH_USER']) : '';

    if ($name) {
      if (user_is_blocked($name)) {
        $result = -1;
      } else {
        $account = user_load_by_name($name);
        if (!$account) {
          $uuid = new Uuid();
          $array = array(
            'name' => $name,
            'status' => 1,
            'uuid' => $uuid->generate(),
            'created' => time(),
          );
          $account = entity_create('user', $array);
          $account->enforceIsNew();
          //$account = new User($array, 'user');
          $account->save(); 
        }
      }
    }
    return $account;
  }

  public function tripartiteRegister($key, $type) {
    $account = FALSE;
    if ($key && $type) {
      $name = md5($type . '_' . $key);
      $account = user_load_by_name($name);
      if (is_object($account) && $account->id()) {
        $result = -1;
      } else {
        $uuid = new Uuid();
        $array = array(
          'name' => $name,
          'status' => 1,
          'uuid' => $uuid->generate(),
          'created' => time(),
        );
        $account = entity_create('user', $array);
        $account->enforceIsNew();
        //$account = new User($array, 'user');
        $account->save(); 
        
        $this->database->query('INSERT INTO {account_tripartite_login} (`uid`, `name`, `type`) VALUES(:uid, :name, :type)', array(':uid' => $account->id(), ':name' => $name, ':type' => $type));

        $account = user_load($account->id(), TRUE);//hook_user_load load account_tripartite_login info
      }
    }
    return $account;
  }

  /**
   * user edit avatar
   */
  public function editAvatar($uid) {
    $account = $this->user_authenticate_by_http();
    if ($account->id() != $uid) return FALSE;
    
    $json_data = file_get_contents('php://input');
    if (isset($json_data) && $json_data) {
      $array = json_decode($json_data, TRUE);
      
      $avatar = base64_decode($array['avatar']);
      if ($file = file_save_data($avatar, 'public://user_pictures/uid_' . $account->id() . '.png')) {
        $file->setPermanent();
        $file->save();
        //TODO account->save accounts
        $this->database->query('UPDATE {accounts} SET `picture` = :picture WHERE uid = :uid', array(':picture' => $file->id(), ':uid' => $account->id()));
      }
      //$account->user_picture['und'][0] = (array)$file;
      //$account->save();
    }

    return user_load($account->id(), TRUE);
  }

  /**
   * user edit nickname and get user entity
   */
  public function editNikename($uid) {
    $account = user_load($uid);

    $json_data = file_get_contents('php://input');
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $current_account = $this->user_authenticate_by_http();
      if ($account->id() != $current_account->id()) return FALSE;
      if (isset($json_data) && $json_data) {
        $array = json_decode($json_data, TRUE);
        
        $name = $array['name'];
        if ($this->nickname_repeat($name, $account->id())) {
          return array('message' => array('message' => '对不起，该昵称已存在！'), 'status' => 422);
        }
        //TODO account->save accounts
        $this->database->query('UPDATE {accounts} SET nickname = :nickname WHERE uid = :uid', array(':nickname' => $name, ':uid' => $account->id()));
        //$account->account_nickname['und'][0]['value'] = $name;
        //$account->save();
        $account = user_load($account->id(), TRUE);
      }

    }
    return array('data' => $account);
  }

  /**
   * check nickname repeat
   */
  protected function nickname_repeat($nickname, $uid) {
    return $this->database->query('SELECT uid FROM {accounts} WHERE  nickname=:nickname AND uid <> :uid', array(':nickname' => $nickname, ':uid' => $uid))->fetchField();
  }

  /**
   * user change password
   */
  public function changePasswd() {
    $result = 0;//-1:用户被block 0:用户名或密码错误 uid:登录成功
    $status = 404;

    $name = isset($_SERVER['PHP_AUTH_USER']) ? trim($_SERVER['PHP_AUTH_USER']) : '';
    $pass = isset($_SERVER['PHP_AUTH_PW']) ? trim($_SERVER['PHP_AUTH_PW']) : '';
    $account = $this->user_authenticate_by_http();
    if (!$account->id()) {
      $sub_str = account_generate_code($name);
      //if ($pass == substr(md5($name . date('Ymd')), -6)) {
      if ($pass == $sub_str) {
        //Forgot password
        $account = user_load_by_name($name);
      } else {
        $status = 422; //Unprocessable Entity
        $errors = array();
        $errors[] = array(
          'resource' => 'Account',
          'field' => 'pass',
          'code' => 'missing',
        );
        $message = array(
          'message' => '验证码错误',
        );
        return array('message' => $message, 'status' => $status);
      }
    }

    if ($account->id()) {
      $json_data = file_get_contents('php://input');
      if (isset($json_data) && $json_data) {
        $array = json_decode($json_data, TRUE);

        $account->setPassword($array['password']);
        $account->save();

        $status = 200;
      }
    }

    return array('data' => $account, 'status' => $status, );
  }

  /**
   * user follow stores 
   */
  public function followStoresList() {
    global $base_url;
    $size = isset($_GET['per_page']) ? $_GET['per_page'] : 10;
    $start = isset($_GET['page'])? ($_GET['page'] - 1) * $size : 0;
    $account = $this->user_authenticate_by_http();
    if (!$account->id()) return FALSE;
    
    $query = $this->database->select('store_follow', 'f');
    $query->addExpression('COUNT(f.sid)');
    $num_rows = $query->condition('uid', $account->id())
      ->execute()
      ->fetchField();

    $query = $this->database->select('store_follow', 'f')
      ->fields('f', array('sid'))
      ->condition('uid', $account->id());
    if ($size) {
      $query->range($start, $size);
    }
    $sids = $query->execute()->fetchCol();

    $http_next = "<$base_url/api/follows?";
    $http_last = $http_next;

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

    return array('stores' => store_load_multiple($sids), 'header' => $header);
  }

  /**
   * user partner login
   */
  public function partnerLogin($type, $uid) {
    $name = isset($_SERVER['PHP_AUTH_USER']) ? trim($_SERVER['PHP_AUTH_USER']) : '';
    $pass = isset($_SERVER['PHP_AUTH_PW']) ? trim($_SERVER['PHP_AUTH_PW']) : '';

    if ($name && $pass) {
      $account = user_load_by_name($name);
      if (!$account) {
        $uuid = new Uuid();
        $array = array(
          'name' => $name,
          'pass' => $pass,
          'status' => 1,
          'uuid' => $uuid->generate(),
          'created' => time(),
        );
        $account = entity_create('user', $array);
        $account->enforceIsNew();
        //$account = new User($array, 'user');
        $account->save(); 

        $json_data = file_get_contents('php://input');

        if (isset($json_data) && $json_data) {
          $array = json_decode($json_data, TRUE);
          
          $avatar_url = $array['avatar'];

          if ($avatar_url) {
            if ($img_data = @file_get_contents($avatar_url)) {
              $file = file_save_data($img_data, 'public://user_pictures/uid_' . $account->id() . '.png');
              $file->setPermanent();
              $file->save();
              //TODO save accounts 
              $this->database->query('UPDATE {accounts} SET `picture` = :picture WHERE uid = :uid', array(':picture' => $file->id(), ':uid' => $account->id()));
            }
          }
          $name = $array['name'];
          $name = account_weibo_nickname_init($name);
          //TODO save accounts
          $this->database->query('UPDATE {accounts} SET nickname = :nickname WHERE uid = :uid', array(':nickname' => $name, ':uid' => $account->id()));
        }
        //account_tripartite_login_save($account->id(), $uid, $type);
        $this->database->query('INSERT INTO {account_tripartite_login} (`uid`, `name`, `type`) VALUES(:uid, :name, :type)', array(':uid' => $account->id(), ':name' => $uid, ':type' => $type));
      }
      return user_load($account->id());
    }
  }

  /**
   * delete user follow stores
   */
  public function deleteFollowStores() {
    $status = 200;
    $account = $this->user_authenticate_by_http();
    if ($account) {
      if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        store_account_follow_delete($account->id());
        $status = 204;
      } 
    }
    return $status;
  }

  /**
   * get user autho token
   */
  public function getAuthoToken() {
    $account = $this->user_authenticate_by_http();
    if ($account->id()) {
      $autho_token = account_autho_token_load(array('uid' => $account->id()));
      if (!$autho_token) {
        $token = base64_encode($account->getUsername() . time());
        $autho_token = account_autho_token_save($account, $token);
      }
    }
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      $response[] = array(
        'scopes' => array(),
        'note' => '',
        'note_url' => '',
        'token' => $autho_token->token,
      ); 
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $response = array(
        'scopes' => array(),
        'note' => '',
        'note_url' => '',
        'token' => $autho_token->token,
      ); 
    }
    return $response;
  }

  /**
   * get mobile package for update
   */
  public function getMobilePackage($mobile, $version) {
    if ($version == 'version') {
      $package = variable_get('android_package_version', array());
    } else if ($version == 'debug_version'){
      $package = variable_get('android_package_debug_version', array());
    }
    return $package[$mobile];
  }

  /**
   * store user login
   */
  public function storeUserLogin() {
    $account = $this->user_authenticate_by_http();
    $status = 200;
    $name = isset($_SERVER['PHP_AUTH_USER']) ? trim($_SERVER['PHP_AUTH_USER']) : '';
    $pass = isset($_SERVER['PHP_AUTH_PW']) ? trim($_SERVER['PHP_AUTH_PW']) : '';

    if (!$account->id()) {
      $sub_str = account_generate_code($name);
      if (strlen($name) == 11 && $pass == $sub_str) {  
        $uuid = new Uuid();
        $array = array(
          'name' => $name,
          'pass' => $pass,
          'status' => 1,
          'uuid' => $uuid->generate(),
          'created' => time(),
        );
        $account = entity_create('user', $array);
        $account->enforceIsNew();
        //$account = new User($array, 'user');
        $account->save(); 

        account_save($account, 1);
      } else {
        $status = 422; //Unprocessable Entity
        $message = array(
          'message' => '密码错误,请输入正确的密码!',
        );
        return array('message' => $message, 'status' => $status);
      }
    }
    
    $query = $this->database->select('stores', 's')
      ->fields('s', array('sid'))
      ->condition('uid', $account->id())
      ->condition('name', '', '<>')
      ->execute()->fetchObject();
    
    $store = store_get_store($account->id());

    if (!$store) {
      $store = store_create_store($account);
    }

    $query_revision = db_select('store_revision', 's')
      ->fields('s', array('sid'))
      ->condition('sid', $store->id())
      ->execute()->fetchObject();
    
    /*$form_state['uid'] = $account->id();
    user_login_finalize($form_state);*/

    $response = array(
      'exist' => $query || $query_revision ? 1 : 0,
      'store_id' => isset($store) ? $store->id() : 0,
      'id' => $account->id(), 
      'avatar_url' => $account->picture ? file_create_url($account->picture->getFileUri()) : variable_get('user_default_picture', 'http://api.gsw100.com/sites/default/files/user_default_picture.png'),
      'login' => $account->getUsername(),
      'name' => (isset($account->nickname) && $account->nickname) ? $account->nickname : $account->getUsername(),
      'type' => isset($account->tripartite_login_type) ? $account->tripartite_login_type : '',
    );
    return array('data' => $response, 'status' => $status);
  }
  
}
