<?php

/**
 * @file
 * Contains \Drupal\account\Controller\AccountController.
 */
namespace Drupal\account\Controller;

use Drupal\account\AccountManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
//use Drupal\Core\Controller\ControllerInterface;
use Drupal\user\Plugin\Core\Entity\User;

use Drupal\user\UserInterface;

use Drupal\account\AccountInterface;
use Drupal\account\Form\UserLoginForm;

/**
 * Controller routines for account routes.
 */
class AccountController extends ControllerBase implements ContainerInjectionInterface {
  /**
   * Account Manager Service.
   *
   * @var \Drupal\account\AccountManager
   */
  protected $accountManager;

  /**
   * Injects AccountManager Service.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('account.manager'));
  }

  /**
   * Constructs a AccountController object.
   */
  public function __construct(AccountManager $accountManager) {
    $this->accountManager = $accountManager;
  }

  /**
   * page callback for: api/user.
   * return 
   */
  public function userApiPage() {
    $result = 0;//-1:用户被block 0:用户名或密码错误 uid:登录成功
    $status = 200;

    $name = isset($_SERVER['PHP_AUTH_USER']) ? trim($_SERVER['PHP_AUTH_USER']) : '';
    $pass = isset($_SERVER['PHP_AUTH_PW']) ? trim($_SERVER['PHP_AUTH_PW']) : '';

    if ($name && $pass) {
      $json_data = file_get_contents("php://input");
      if (isset($json_data) && $json_data) {
        $array = json_decode($json_data, TRUE);
        $type = $array['type'];
      }
      $account = user_load_by_name($name);
      
      if ($account && $account->id()) {
        $result = user_authenticate($name, $pass);
        if ($result) {
          $response = $this->get_login_response($account);
        } else {
          $status = 422; //Unprocessable Entity
          $errors = array();
          $errors[] = array(
            'resource' => 'Account',
            'field' => 'pass',
            'code' => 'missing',
          );
          $response = array(
            'message' => '密码错误,请输入正确的密码!',
          );
        }
      } else {
        //phone register
        $sub_str = account_generate_code($name);
        if (strlen($name) == 11 && $pass == $sub_str) {  
          $array = array(
            'name' => $name,
            'pass' => $pass,
            'status' => 1,
            'created' => time(),
          );
          $account = entity_create('user', $array);
          $account->enforceIsNew();
          //$account = new User($array, 'user');
          $account->save(); 
          //TODO save accounts 
          account_save($account, 1);
          
          $user_account = account_load($account->id());
          $user_account->phone = $name; 
          $user_account->save();

          $response = $this->get_login_response($account);
        } else {
          $status = 422; //Unprocessable Entity
          $errors = array();
          $errors[] = array(
            'resource' => 'Account',
            'field' => 'pass',
            'code' => 'missing',
          );
          $response = array(
            'message' => '密码错误,请输入正确的密码!',
            /*'errors' => $errors,*/
          );
        }
      }
    } else {
      $tokens = isset($_SERVER['HTTP_AUTHORIZATION']) ? trim($_SERVER['HTTP_AUTHORIZATION']) : '';
      $tokens = explode(' ', $tokens);
      if ($tokens[0] == 'token') {
        $autho_token = account_autho_token_load(array('token' => $tokens[1]));
        if ($autho_token->uid) {
          $account = user_load($autho_token->uid);

          $json_data = file_get_contents("php://input");
          if (isset($json_data) && $json_data) {
            $array = json_decode($json_data, TRUE);
            $type = $array['type'];
          }
          
          $response = $this->get_login_response($account);
        }
      }
    }
    return new JsonResponse($response, $status);
  }

  /**
   * get account login response 
   * @param $account see account_user_load($users).
   */
  protected function get_login_response($account) {
    $avatar_url = $account->picture ? file_create_url($account->picture->getFileUri()) : variable_get('user_default_picture', 'http://api.gsw100.com/sites/default/files/user_default_picture.png');
    $coupon_bookmark_count = db_query('SELECT COUNT(cid) FROM {coupon_bookmarks} WHERE uid=:uid', array(':uid' => $account->id()))->fetchField();
    $share_bookmark_count = db_query('SELECT COUNT(sid) FROM {share_bookmarks} WHERE uid=:uid', array(':uid' => $account->id()))->fetchField();
    return array(
      'id' => $account->id(), 
      'avatar_url' => $avatar_url, 
      'login' => $account->getUsername(),
      'name' => (isset($account->nickname) && $account->nickname) ? $account->nickname : $account->getUsername(),
      'type' => isset($account->tripartite_login_type) ? $account->tripartite_login_type : '',
      'follow_count' => isset($account->follow_count) ? $account->follow_count : 0,
      'fans_count' => isset($account->fans_count) ? $account->fans_count : 0,
      'store_follow_count' => isset($account->store_follow_count) ? $account->store_follow_count : 0,
      'coupon_bookmark_count' => $coupon_bookmark_count,
      'share_bookmark_count' => $share_bookmark_count,
    ); 
  }
  /**
   * get store response
   */
  protected function get_store_response($store) {
    $picture = $store->getPicture();
    $latest_coupon = coupon_latest_coupon($store->id());
    $coupon_title = isset($latest_coupon) ? $latest_coupon->label() : '';
    $image_url = $picture ? file_create_url($picture->getFileUri()) : variable_get('store_default_picture', 'http://api.gsw100.com/sites/default/files/store_default_picture.png');
    return array(
      'id' => $store->id(),
      'owner_id' => $store->uid->value,
      'name' => $store->label(),
      'image_url' => $image_url,
      'latitude' => $store->latitude->value,
      'longitude' => $store->longitude->value,
      'address' => $store->address->value,
      'phone' => $store->phone->value,
      'hours' => $store->hours->value,
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
   * page callback for: api/users/%user_name
   */
  public function userLoadByNamePage($user_name) {
    $account = user_load_by_name($user_name);
    return new JsonResponse($this->get_login_response($account));
  }

  /**
   * page callback for: api/register/store/{user_name}/{user_pass}
   */
  public function regsiterStoreRole($user_name, $user_pass) {
    $account = $this->accountManager->registerStoreRole($user_name, $user_pass);
    return new JsonResponse($this->get_login_response($account));
  }

  /**
   * page callback for: api/register/{phone}
   * checkout phone send msg
   */
  public function regsiter($phone) {
    $return = $this->accountManager->register($phone);
    return new JsonResponse($return['data'], $return['status']);
  }

  /**
   * page callback for: api/phone/{phone}/verify
   */
  public function phoneVerify($phone) {
    $return = $this->accountManager->phoneVerify($phone);
    return new JsonResponse($return['data'], $return['status']);
  }

  /**
   * page callback for: api/user/followed/{store_sid}
   */
  public function userFollowStore($store_sid) {
    $return = $this->accountManager->getFollowStore($store_sid);
    return new JsonResponse($return['data'], $return['status']);
  }

  /**
   * page callback for: api/user/tripartite/login
   */
  public function userTripartiteLogin() {
    $account = $this->accountManager->tripartiteLogin();
    return new JsonResponse($this->get_login_response($account));
  }
  /**
   * page callback for: api/user/tripartite/register/{key}/{type}
   */
  public function userTripartiteRegister($key, $type) {
    $account = $this->accountManager->tripartiteRegister($key, $type);
    return new JsonResponse($this->get_login_response($account));
  }

  /**
   * page callback for:api/user/{uid}/avatar.
   */
  public function userEditAvatar($uid) {
    $account = $this->accountManager->editAvatar($uid);
    return new JsonResponse($this->get_login_response($account));
  }

  /**
   * page callback for:api/user/{uid}
   */
  public function userEditNikename($uid) {
    $return = $this->accountManager->editNikename($uid);
    if (isset($return['message'])) {
      return  new JsonResponse($return['message'], $return['status']);
    } else {
      return new JsonResponse($this->get_login_response($return['data']));
    }
  }

  /**
   * page callback for: api/user/password
   */
  public function userChangePasswd() {
    $return = $this->accountManager->changePasswd();
    if ($return['message']) {
      return  new JsonResponse($return['message'], $return['status']);
    } else {
      return new JsonResponse($this->get_login_response($return['data']), $return['status']);
    }
  }

  /**
   * page callback for: api/follows
   */
  public function userFollowStoresList(Request $request) {
    $responses = array();
    $return = $this->accountManager->followStoresList($request);
    foreach ($return['stores'] as $store) {
      $responses[] = $this->get_store_response($store);
    }
    return new JsonResponse($responses, 200, $return['header']);
  }
  
  /**
   * page callback for: api/partner_login/{type}/{uid}
   */
  public function userPartnerLogin($type, $uid) {
    $account = $this->accountManager->partnerLogin($type, $uid);
    return new JsonResponse($this->get_login_response($account));
  }

  /**
   * page callback for: api/user/followed
   */
  public function userDeleteFollowStores() {
    $status = $this->accountManager->deleteFollowStores();
    return new JsonResponse(NULL, $status);
  }

  /**
   * page callback for: api/authorizations
   */
  public function getAuthoToken() {
    return new JsonResponse($this->accountManager->getAuthoToken());
  }

  /**
   * page callback for: api/package/{mobile}/{version}
   */
  public function getMobilePackage($mobile, $version) {
    return new JsonResponse($this->accountManager->getMobilePackage($mobile, $version));
  }

  /**
   * page callback for:api/store/login
   */
  public function storeUserLogin() {
    $return = $this->accountManager->storeUserLogin();
    if (isset($return['message'])) {
      return  new JsonResponse($return['message'], $return['status']);
    } else {
      return new JsonResponse($return['data'], $return['status']);
    }
  }

  public function followed(Request $request, UserInterface $user) {
    $currentUser = \Drupal::currentUser();
    $followed = false;
    if ($currentUser->isAuthenticated()) {
      $query = db_select('account_follows', 'f');
      $query->addField('f', 'uid');
      $query->condition('f.uid', $currentUser->id());
      $query->condition('f.follow_uid', $user->id());
      if ($query->execute()->fetchObject()) {
        $followed = true;
      }
    }
    return new JsonResponse(array('followed' => $followed));
  }

  public function follow(Request $request, UserInterface $user) {
    $this->accountManager->follow($user);
    return new JsonResponse(array('followed' => true));
  }

  public function storefollows(){
    $currentUser = \Drupal::currentUser();
    $query = db_select('stores', 's');
    $query->addField('s', 'follow_count');
    $query->condition('s.uid', $currentUser->id());

    $follow_count = $query->execute()->fetchField();
    //$build['#contents'] = $follow_count;
    $build = array('#theme'=>'account_admin',
      '#title' => '关注数',
      'form' => array('#markup' => '<h3 class="gzs">关注数</h3>
        <p class="data_con">您在贵客受到的关注数量为：<em>' . $follow_count . '</em></p>'),
    );
    return $build;
  }

  public function unfollow(Request $request, UserInterface $user) {
    $currentUser = \Drupal::currentUser();
    db_delete('account_follows')
      ->condition('uid', $currentUser->id())
      ->condition('follow_uid', $user->id())
      ->execute();

    if ($account = account_load($currentUser->id())) {
      $account->follow_count->value --;
      $account->save();
    }
    if($account = account_load($user->id())) {
      $account->fans_count->value --;
      $account->save();
    }

    return new JsonResponse(array('followed' => false));
  }

  /**
   * callback for: account/js/{operation}
   */
  public function accountAjaxAction($operation) {
    switch ($operation) {
      case 'get_verification_code':
        $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
        if (strlen($phone) == 11 && preg_match('/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/', $phone)) {
          if ($account = user_load_by_name($phone) || $account = user_load_by_phone($phone)) {
            return new JsonResponse(array('status' => 0, 'error' => '该手机号码已注册'));
          } else {
            $pass = account_generate_code($phone);
            $content = '您在贵商网申请的验证码为:' . $pass . ',该验证码今日内有效,凭此验证码可用于注册,谢谢![贵商网]';
            account_send_msg($content, $phone);
            return new JsonResponse(array('status' => 1));
          }
        } else {
          return new JsonResponse(array('status' => 0, 'error' => '手机号码不正确!'));
        }
        break;
    }
  }

  /**
   * callback for: login
   */
  public function accountLogin() {
    $currentUser = \Drupal::currentUser();
    if ($currentUser->id()) {
      return new RedirectResponse(url('user/' . $currentUser->id(), array('absolute' => TRUE)));
    }
    return array('#theme' => 'account_login', '#user_login_form' => drupal_get_form(UserLoginForm::create(\Drupal::getContainer())));
  }

  /**
   * callback for: register 
   */
  public function accountRegister() {
    $currentUser = \Drupal::currentUser();
    if ($currentUser->id()) {
      return new RedirectResponse(url('user/' . $currentUser->id(), array('absolute' => TRUE)));
    }
    return array('#theme' => 'account_register', '#mode' => 'phone', '#user_register_form' => drupal_get_form('account_register_form'));
  }

  /**
   * callback for: register/email
   */
  public function accountEmailRegister() {
    $currentUser = \Drupal::currentUser();
    if ($currentUser->id()) {
      return new RedirectResponse(url('user/' . $currentUser->id(), array('absolute' => TRUE)));
    }
    return array('#theme' => 'account_register', '#mode' => 'email', '#user_register_form' => drupal_get_form('account_register_email_form'));
  }

  /**
   * callback for: resetpwd
   */
  public function accountResetpwd(Request $request) {
    $currentUser = \Drupal::currentUser();
    if ($currentUser->id()) {
      return new RedirectResponse(url('user/' . $currentUser->id(), array('absolute' => TRUE)));
    }
    return array('#theme' => 'account_reset_passwd', '#account_reset_passwd_phone_form' => drupal_get_form('account_reset_passwd_phone_form'), '#account_reset_passwd_email_form' => drupal_get_form('account_reset_passwd_email_form'));
  }

  /**
   * callback for: resetpwd/{user}/msg
   */
  public function accountResetpwdMsg(Request $request, UserInterface $user) {
    $currentUser = \Drupal::currentUser();
    if ($currentUser->id()) {
      return new RedirectResponse(url('user/' . $currentUser->id(), array('absolute' => TRUE)));
    }
    return array('#theme' => 'account_reset_passwd_msg');
  }

  /**
   * callback for: resetpwd/{user}/login
   */
  public function accountResetpwdLogin(Request $request, UserInterface $user) {
    $currentUser = \Drupal::currentUser();
    if ($currentUser->id()) {
      return new RedirectResponse(url('user/' . $currentUser->id(), array('absolute' => TRUE)));
    }
    $reset_passwd_uid = isset($_SESSION['reset_passwd_uid']) ? $_SESSION['reset_passwd_uid'] : '';
    if ($user->id() != $reset_passwd_uid) {
      return new RedirectResponse(url('user/' . $currentUser->id(), array('absolute' => TRUE)));
    }
    return array('#theme' => 'account_reset_passwd_login', '#login_form' => drupal_get_form('account_reset_passwd_login_form', $user));
  }

  /**
   * callback for: user/{user}/follows
   */
  public function userFollows(UserInterface $user,Request $request) {
    $user->searchform=1;
    $build = array('#theme' => 'account', '#user' => $user);
    $links = array(
      array('title' => '我的关注', 'href' => 'user/' . $user->id() . '/follows'),
      array('title' => '我关注的商家', 'href' => 'user/' . $user->id() . '/stores'),
      array('title' => '我的粉丝', 'href' => 'user/' . $user->id() . '/fans'),
    );
    $build['#links'] = array('#theme' => 'links', '#links' => $links, '#attributes' => array('class' => array('action-links')));
    
    if ($ids = account_follow_select_users($user->id(), $request->query->get('keyword', ''), TRUE, 12)) {
      $users = user_load_multiple($ids);
      $build['#contents'] = entity_view_multiple($users, 'teaser');
      $build['#search_form'] = drupal_get_form('Drupal\account\Form\UserSearchForm');
      $build['#pager'] = array('#theme' => 'pager', '#tags' => array('最前', '<上一页', '', '下一页>', '最后'));
    }
    else {
      $build['#contents'] = drupal_get_form('\Drupal\account\Form\FollowUsers');
    }

    return $build;
  }
  
  function userStores(UserInterface $user) {
    $build = array('#theme' => 'account', '#user' => $user);
    $links = array(
      array('title' => '我的关注', 'href' => 'user/' . $user->id() . '/follows'),
      array('title' => '我关注的商家', 'href' => 'user/' . $user->id() . '/stores'),
      array('title' => '我的粉丝', 'href' => 'user/' . $user->id() . '/fans'),
    );
    $build['#links'] = array('#theme' => 'links', '#links' => $links, '#attributes' => array('class' => array('action-links')));
    if ($sids = account_select_follow_stores($user->id(),TRUE,12)) {
      $stores = store_load_multiple($sids);
      $build['#contents'] = store_view_multiple($stores,'follow');
      $build['#pager'] = array('#theme' => 'pager', '#tags' => array('最前', '<上一页', '', '下一页>', '最后'));
    }
    else {
      $build['#contents'] = '<div class="follow_note">您还没有关注商家，现在就去关注吧！</div>';
    }

    return $build;
  }
  /**
   * callback for: user/{user}/follows
   */
  public function userFans(UserInterface $user) {
    $build = array('#theme' => 'account', '#user' => $user);

    $links = array(
      array('title' => '我的关注', 'href' => 'user/' . $user->id() . '/follows'),
      array('title' => '我关注的商家', 'href' => 'user/' . $user->id() . '/stores'),
      array('title' => '我的粉丝', 'href' => 'user/' . $user->id() . '/fans'),
    );
    $build['#links'] = array('#theme' => 'links', '#links' => $links, '#attributes' => array('class' => array('action-links')));

    if ($ids = account_follow_select_fans($user->id(), TRUE, 12)) {
      $users = user_load_multiple($ids);
      $build['#contents'] = entity_view_multiple($users, 'teaser');

      $build['#pager'] = array('#theme' => 'pager', '#tags' => array('最前', '<上一页', '', '下一页>', '最后'));
    }
    else {
      $build['#contents'] = '暂无粉丝。';
    }

    return $build;
  }

  function userBookmarkShareTitle(UserInterface $user) {
    $currentUser = \Drupal::currentUser();
    if ($currentUser->id() == $user->id()) {
      return '我收藏的商品';
    } else {
      $nickname = $user->nickname ? $user->nickname : $user->getUsername();
      return $nickname . '收藏的商品';
    }
  }
  function userBookmarkShare(UserInterface $user) {
    $currentUser = \Drupal::currentUser();

    $build = array('#theme' => 'account', '#user' => $user);

    $links = array(
      array('title' => '收藏的商品', 'href' => 'user/' . $user->id() . '/bookmark/share'),
      array('title' => '收藏的优惠', 'href' => 'user/' . $user->id() . '/bookmark/coupon'),
    );
    $build['#links'] = array('#theme' => 'links', '#links' => $links, '#attributes' => array('class' => array('action-links')));

    if ($sids = account_select_bookmark_shares($user->id())) {
      if ($user->id() == $currentUser->id()) {
        $build['#contents'] = drupal_get_form('\Drupal\account\Form\ShareHistoryForm');
      }
      else {
        $shares = share_load_multiple($sids);
        $build['#contents'] = waterfall(share_view_multiple($shares), 4);
        $build['#pager'] = array('#theme' => 'pager', '#tags' => array('最前', '<上一页', '', '下一页>', '最后'));
      }
    }
    else {
      $build['#contents'] = '<div class="note_box">
       <h1>还没有收藏单品~</h1>
       <p><a target="_blank" href="' . url('share') . '">现在就去逛逛</a>
     </p></div>';
    }

    return $build;
  }
  
  
  function userBookmarkCoupon(UserInterface $user) {
    $currentUser = \Drupal::currentUser();
    $build = array('#theme' => 'account', '#user' => $user);

    $links = array(
      array('title' => '收藏的商品', 'href' => 'user/' . $user->id() . '/bookmark/share'),
      array('title' => '收藏的优惠', 'href' => 'user/' . $user->id() . '/bookmark/coupon'),
    );
    $build['#links'] = array('#theme' => 'links', '#links' => $links, '#attributes' => array('class' => array('action-links')));
    if ($cids = account_select_bookmark_coupons($user->id())) {
      if ($user->id() == $currentUser->id()) {
        $build['#contents'] = drupal_get_form('\Drupal\account\Form\CouponBookmarkForm');
      }
      else{
        $coupons = entity_load_multiple('coupon', $cids);
        $build['#contents'] = entity_view_multiple($coupons, 'bookmark');
        $build['#contents']['#prefix'] = '<div class="salelist">';
        $build['#contents']['#suffix'] = '</div>';
        $build['#pager'] = array('#theme' => 'pager', '#tags' => array('最前', '<上一页', '', '下一页>', '最后'));
      }
    }
    else {
      $build['#contents'] = '还没有收藏任何优惠。';
    }

    return $build;
  }
  function userunBookmarkCoupon(UserInterface $user) {
    $currentUser = \Drupal::currentUser();
    db_delete('account_follows')
      ->condition('uid', $currentUser->id())
      ->condition('follow_uid', $user->id())
      ->execute();

    if($account = account_load($user->id())) {
      $account->fans_count->value --;
      $account->save();
    }

    return new JsonResponse(array('followed' => false));
  }
  function userShare(UserInterface $user){
	  $build = array('#theme' => 'account', '#user' => $user);

  	$links = array(
        array('title' => '我晒的', 'href' => 'user/' . $user->id() . '/share'),
 	  );

  	if ($sids = account_select_shares($user->id(), TRUE, \Drupal::config('share.settings')->get('items_per_page'))) {
    	$shares = share_load_multiple($sids);
   	$build['#contents'] = waterfall(share_view_multiple($shares), 4);
  	$build['#pager'] = array('#theme' => 'pager', '#tags' => array('最前', '<上一页', '', '下一页>', '最后'));
  }
  	else {
    	$build['#contents'] = '<div class="note_box">
     	<h1>您还没有晒宝贝~</h1>
     	<p><a data-url="/share/js/add" href="javascript:void(0)" data-title="晒宝贝" dialog-width="785" class="show ajax-dialog" target="_blank">现在就晒宝贝</a>
   	</p></div>';
    }

    return $build;	
  }

  function userCommentShare(UserInterface $user){
	  $build = array('#theme' => 'account', '#user' => $user);

	  $links = array(
            array('title' => '我评价的商品', 'href' => 'user/' . $user->id() . '/comment/share'),
            array('title' => '我评价的优惠', 'href' => 'user/' . $user->id() . '/comment/coupon'),
 	  );

	  $build['#links'] = array('#theme' => 'links', '#links' => $links, '#attributes' => array('class' => array('action-links')));

 	  if ($cids = account_select_share_comments($user->id(),TRUE,3)) {
        $comments = share_comment_load_multiple($cids);
        $build['#contents'] = share_comment_view_multiple($comments, 'user');
        $build['#pager'] = array('#theme' => 'pager', '#tags' => array('最前', '<上一页', '', '下一页>', '最后'));
        }
        else {
        $build['#contents'] = '还未评价任何商品。';
        }
    return $build;
  }
  function userCommentCoupon(UserInterface $user){
	  $build = array('#theme' => 'account', '#user' => $user);

	  $links = array(
            array('title' => '我评价的商品', 'href' => 'user/' . $user->id() . '/comment/share'),
            array('title' => '我评价的优惠', 'href' => 'user/' . $user->id() . '/comment/coupon'),
 	  );

	  $build['#links'] = array('#theme' => 'links', '#links' => $links, '#attributes' => array('class' => array('action-links')));

 	  if ($cids = account_select_store_comments($user->id())) {
        $comments = store_comment_load_multiple($cids);
        $build['#contents'] = store_comment_view_multiple($comments, 'user');
        $build['#pager'] = array('#theme' => 'pager', '#tags' => array('最前', '<上一页', '', '下一页>', '最后'));
        }
        else {
        $build['#contents'] = '还未评价任何优惠信息。';
        }
    return $build;
  }
  
  /**
   * page callback: account/{user}/edit/password
   */
  public function accountEditPasswdByToken(Request $request, UserInterface $user) {
    $currentUser = \Drupal::currentUser();
    if ($currentUser->id() == $user->id()) {
      $pass_reset_session = isset($_SESSION['pass_reset_' . $user->id()]) ? $_SESSION['pass_reset_' . $user->id()] : '';
      $pass_reset_token = $request->query->get('pass-reset-token');
      if ($pass_reset_token == $pass_reset_session) {
        module_load_include('pages.inc', 'account');
        return array('#theme' => 'account_reset_passwd_login_for_email', '#login_form' => drupal_get_form('account_edit_passwd_by_token_form'));
      } else {
        return new RedirectResponse(url('account/edit/password', array('absolute' => TRUE)));
      }
    }
  }

  public function userView(UserInterface $user) {
    $currentUser = \Drupal::currentUser();
    if ($user->id() != $currentUser->id()) {
      return $this->redirect('user.bookmark_share', array('user' => $user->id()));
    }
    else {
      return entity_view($user, 'full');
    }
  }
}
