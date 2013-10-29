<?php

use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\Plugin\Core\Entity\User;

//TODO use lib/weibo
include_once(drupal_get_path('module', 'sina') . '/lib/weibo/TopSdk.php');

/**
 * Begin the auth process.
 * page callback:  sina/redirect
 */
function weibo_signin_redirect() {
  global $base_url;
  $_SESSION['ref'] = $_SERVER['HTTP_REFERER'];
  $key = variable_get('weibo_consumer_key', '3728623491');
  $secret = variable_get('weibo_consumer_secret', '33cc010a8ec827d54de3353402abef68');
  $o = new WeiboOAuth($key, $secret);
  $keys = $o->getRequestToken($base_url . '/sina/callback');
  $_SESSION['weibooauth'] = $keys;
  $aurl = $o->getAuthorizeURL($keys['oauth_token'], TRUE, $base_url . '/sina/callback');
  return new RedirectResponse($aurl);
}

/**
 * The callback function for auth.
 * page callback: sina/callback
 */
function weibo_auth_callback() {
  $user = Drupal::currentUser();
  $key = variable_get('weibo_consumer_key', '3728623491');
  $secret = variable_get('weibo_consumer_secret', '33cc010a8ec827d54de3353402abef68');
  if ($_REQUEST['oauth_verifier']) {
    $o = new WeiboOAuth($key, $secret, $_SESSION['weibooauth']['oauth_token'], $_SESSION['weibooauth']['oauth_token_secret']);
    $last_key = $o->getAccessToken($_REQUEST['oauth_verifier']);
    $_SESSION['weibooauth_last_key'] = $last_key;
    $weibo_name = 'WEIBO' . $last_key['user_id'];

    if ($user->id() == 0) {
      $uid = db_select('account_tripartite_login', 'a')
        ->fields('a', array('uid'))
        ->condition('name', $weibo_name)
			  ->execute()
			  ->fetchField();
      if ($uid) {
        $user = user_load($uid);
      }
      else {
        $user = sinaweibo_user_signup(TRUE);
      }
      user_login_finalize($user);
    }
    //user_save($user, array('login' => time()));
    return new RedirectResponse(url('<front>', array('absolute' => TRUE)));
  }
}

/**
 * Connect the account with Sina Weibo.
 */
function sinaweibo_user_signup($signup = FALSE) {
  if (isset($_SESSION['weibooauth_last_key'])) {
    $key = variable_get('weibo_consumer_key', '3728623491');
    $secret = variable_get('weibo_consumer_secret', '33cc010a8ec827d54de3353402abef68');

    $c = new WeiboClient($key, $secret, $_SESSION['weibooauth_last_key']['oauth_token'], $_SESSION['weibooauth_last_key']['oauth_token_secret']  );
    $response = $c->verify_credentials();
    //应用未审核 无法获取用户信息
    if ($signup) {
      $weibo_name = 'WEIBO' . $_SESSION['weibooauth_last_key']['user_id'];
      $weibo_pwd = 'uid' . $weibo_name;
      $edit = array(
        'name' => $weibo_name,
        'pass' => $weibo_pwd,
        'status' => 1,
        'access' => REQUEST_TIME,
      );
      $account = entity_create('user', $edit);
      $account->enforceIsNew();
      $account->save(); 

      $account->addRole('authenticated user');
      $account->save(); 
      
      db_query('INSERT INTO {account_tripartite_login} (`uid`, `name`, `type`) VALUES(:uid, :name, :type)', array(':uid' => $account->id(), ':name' => $weibo_name, ':type' => SINA_LOGIN_TYPE));

      return $account;
    }
  }
}
