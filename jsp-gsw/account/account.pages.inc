<?php
/**
 * @file
 * User page callback file for the user module.
 */
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Drupal\user\Plugin\Core\Entity\User;
use Drupal\taxonomy\Plugin\Core\Entity\Term;
use Drupal\Component\Utility\Crypt;

/**
 * Access callback for path account/picture.
 */
function account_picture(){
}

/**
 * Access callback for path account/bind/phone.
 */
function account_bind_phone(){
}

//基本资料form表单
function account_edit_form($form, &$form_state, $account = NULL) {
  $form['nickname'] = array(
    '#title' => '昵称',
    '#type' => 'textfield',
    '#default_value' => isset($account->nickname) ? $account->nickname : '',
  );
  $form['sex'] = array(
    '#type' => 'radios',
    '#title' => '性别',
    '#options' => array(
      1 => '男',
      2 => '女',
    ),
    '#default_value' => isset($account->sex) ? $account->sex : 0,
  );
  $form['signature'] = array(
    '#type' => 'textarea',
    '#title' => '自我介绍',
    '#default_value' => isset($account->signature) ? $account->signature : '',
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '保存',
  );

  return $form;
}

//修改密码form表单
function account_reset_pass_form($form, $form_state) {
  $form['pass'] = array(
    '#title' => '密码',
    '#type' => 'textfield',
  );
  $form['new_pass'] = array(
    '#title' => '新密码',
    '#type' => 'textfield',
  );
  $form['repass'] = array(
    '#title' => '确认密码',
    '#type' => 'textfield',
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '保存',
  );
}

//修改头像表单,此处考虑用核心的还是自己写一个头像字段
function account_photo_setting_form($form, &$form_state, $account) {
  $form['picture'] = array(
    '#type' => 'fieldset',
    '#title' => '图片',
  );
  $form['picture']['picture'] = array(
    '#type' => 'value',
    '#value' => isset($account->image_url->fid) ? $account->image_url->fid : NULL,
  );
  $form['picture']['picture_current'] = array(
    '#markup' => theme('account_picture', array('account' => $account)),
  );
  $form['picture']['picture_upload'] = array(
    '#type' => 'file',
    '#title' => '上传logo',
    '#size' => '20',
  );

}

//绑定手机表单
function account_bind_phone_form($form, &$form_state) {
  $form['phone'] = array(
    '#title' => '手机号码',
    '#type' => 'textfield',
  );
  $form['validate'] = array(
    '#title' => '验证码',
    '#type' => 'textfield',
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => '保存',
  );
}

function account_share($user) {
  $build = array('#theme' => 'account', '#user' => $user);

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

function account_comment_share($user) {
  $build = array('#theme' => 'account', '#user' => $user);

  $links = array(
    array('title' => '评价的商品', 'href' => 'user/' . $user->id() . '/comment/share'),
    array('title' => '评价的优惠', 'href' => 'user/' . $user->id() . '/comment/store'),
  );
  $build['#links'] = array('#theme' => 'links', '#links' => $links, '#attributes' => array('class' => array('action-links')));

  if ($cids = account_select_share_comments($user->id())) {
    $comments = share_comment_load_multiple($cids);
    $build['#contents'] = share_comment_view_multiple($comments, 'user');
    $build['#pager'] = array('#theme' => 'pager', '#tags' => array('最前', '<上一页', '', '下一页>', '最后'));
  }
  else {
    $build['#contents'] = '还未评价任何商品。';
  }

  return $build;
}

function account_comment_store($user) {
  $build = array('#theme' => 'account', '#user' => $user);

  $links = array(
    array('title' => '评价的商品', 'href' => 'user/' . $user->id() . '/comment/share'),
    array('title' => '评价的优惠', 'href' => 'user/' . $user->id() . '/comment/store'),
  );
  $build['#links'] = array('#theme' => 'links', '#links' => $links, '#attributes' => array('class' => array('action-links')));

  if ($ids = account_select_store_comments($user->id())) {
    $comments = store_comment_load_multiple($ids);
    $build['#contents'] = store_comment_view_multiple($comments, 'user');
    $build['#pager'] = array('#theme' => 'pager', '#tags' => array('最前', '<上一页', '', '下一页>', '最后'));
  }
  else {
    $build['#contents'] = '还未评价任何优惠。';
  }

  return $build;
}

/**
 * Menu callback; process one time login link and redirects to the user page on success.
 *
 * @deprecated Use \Drupal\account\Form\UserForm::resetPass()
 * 邮件找回密码 一次性链接地址
 */
function account_pass_reset($form, &$form_state, $uid, $timestamp, $hashed_pass, $action = NULL) {
  $current_user = \Drupal::currentUser();

  // When processing the one-time login link, we have to make sure that a user
  // isn't already logged in.
  if ($current_user->isAuthenticated()) {
    // The existing user is already logged in.
    if ($current_user->id() == $uid) {
      drupal_set_message(t('You are logged in as %user. <a href="!user_edit">Change your password.</a>', array('%user' => $current_user->getUsername(), '!user_edit' => url("user/" . $current_user->id() . "/edit"))));
    }
    // A different user is already logged in on the computer.
    else {
      $reset_link_account = user_load($uid);
      if (!empty($reset_link_account)) {
        drupal_set_message(t('Another user (%other_user) is already logged into the site on this computer, but you tried to use a one-time link for user %resetting_user. Please <a href="!logout">logout</a> and try using the link again.',
          array('%other_user' => $current_user->getUsername(), '%resetting_user' => $reset_link_account->getUsername(), '!logout' => url('user/logout'))));
      } else {
        // Invalid one-time link specifies an unknown user.
        drupal_set_message(t('The one-time login link you clicked is invalid.'));
      }
    }
    return new RedirectResponse(url('<front>', array('absolute' => TRUE)));
  }
  else {
    // Time out, in seconds, until login URL expires.
    $timeout = \Drupal::config('user.settings')->get('password_reset_timeout');
    $current = REQUEST_TIME;
    $account = user_load($uid);
    // Verify that the user exists and is active.
    if ($timestamp <= $current && $account && $account->isActive()) {
      // No time out for first time login.
      if ($account->getLastLoginTime() && $current - $timestamp > $timeout) {
        drupal_set_message(t('You have tried to use a one-time login link that has expired. Please request a new one using the form below.'));
        return new RedirectResponse(url('resetpwd', array('absolute' => TRUE)));
      }
      elseif ($account->isAuthenticated() && $timestamp >= $account->getLastLoginTime() && $timestamp <= $current && $hashed_pass == user_pass_rehash($account->getPassword(), $timestamp, $account->getLastLoginTime())) {
        // First stage is a confirmation form, then login
          // Set the new user.
          // user_login_finalize() also updates the login timestamp of the
          // user, which invalidates further use of the one-time login link.
          //user_login_finalize($account);
          global $user;
          $user = $account;
          watchdog('user', 'User %name used one-time login link at time %timestamp.', array('%name' => $account->getUsername(), '%timestamp' => $timestamp));
          //drupal_set_message(t('You have just used your one-time login link. It is no longer necessary to use this link to log in. Please change your password.'));
          // Let the user's password be changed without the current password check.
          $token = Crypt::randomStringHashed(55);
          $_SESSION['pass_reset_' . $account->id()] = $token;
          return new RedirectResponse(url('account/' . $account->id() . '/edit/password', array(
            'query' => array('pass-reset-token' => $token),
            'absolute' => TRUE,
          )));
          //TODO 
      }
      else {
        drupal_set_message(t('You have tried to use a one-time login link that has either been used or is no longer valid. Please request a new one using the form below.'));
        return new RedirectResponse(url('resetpwd', array('absolute' => TRUE)));
      }
    }
    else {
      // Deny access, no more clues.
      // Everything will be in the watchdog's URL for the administrator to check.
      throw new AccessDeniedHttpException();
    }
  }
}

function account_edit_passwd_by_token_form($form, &$form_state) {
  $form['pass'] = array(
    '#type' => 'password',
    '#title' => '输入新密码：',
    '#size' => 60,
    '#required' => TRUE,
    '#attributes' => array(
      'class' => array('text', 'r3'),
    ),
    '#prefix' => '<div class="line">',
    '#suffix' => '</div>',
  );
  $form['confirm_pass'] = array(
    '#type' => 'password',
    '#title' => '再输入一次：',
    '#size' => 60,
    '#required' => TRUE,
    '#attributes' => array(
      'class' => array('text', 'r3'),
    ),
    '#prefix' => '<div class="line">',
    '#suffix' => '</div>',
  );
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit', 
    '#value' => '提  交',
    '#attributes' => array(
      'class' => array('botton'),
    ),
    '#prefix' => '<div class="ipt_sub">',
    '#suffix' => '</div>',
  );
  return $form;
}

function account_edit_passwd_by_token_form_validate($form, &$form_state) {
  $pass = $form_state['values']['pass'];
  $confirm_pass = $form_state['values']['confirm_pass'];

  if (strlen($pass) < 6 ) {
    form_set_error('pass', '请输入6位密码!');
    return;
  }
  if ($pass != $confirm_pass) {
    form_set_error('confirm_pass', '两次密码不一致，请重新输入!');
    return;
  }
}

function account_edit_passwd_by_token_form_submit($form, &$form_state) {
  $currentUser = \Drupal::currentUser();
  $account = user_load($currentUser->id());
  $account->setPassword($form_state['values']['pass']);
  $account->save();
  
  $form_state['redirect'] = 'user/' . $currentUser->id();
}
