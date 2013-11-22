<?php

/**
 * @file
 * Contains \Drupal\account\Form\UserLoginForm.
 */

namespace Drupal\account\Form;

use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormBase;
use Drupal\user\UserStorageControllerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a user login form.
 */
class UserLoginForm extends FormBase {

  /**
   * The flood service.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * The user storage controller.
   *
   * @var \Drupal\user\UserStorageControllerInterface
   */
  protected $userStorage;

  /**
   * Constructs a new UserLoginForm.
   *
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood service.
   * @param \Drupal\user\UserStorageControllerInterface $user_storage
   *   The user storage controller.
   */
  public function __construct(FloodInterface $flood, UserStorageControllerInterface $user_storage) {
    $this->flood = $flood;
    $this->userStorage = $user_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('flood'),
      $container->get('entity.manager')->getStorageController('user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'account_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    // Display login form:
    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => '帐号:',
      '#size' => 60,
      '#maxlength' => USERNAME_MAX_LENGTH,
      '#required' => TRUE,
      '#attributes' => array(
        'autocorrect' => 'off',
        'autocapitalize' => 'off',
        'spellcheck' => 'false',
        'autofocus' => 'autofocus',
        'class' => array('text', 'r3'),
        'hint-text' => '手机/注册邮箱',
      ),
    );
    
    $form['pass'] = array(
      '#type' => 'password',
      '#title' => '密码:',
      '#size' => 60,
      '#required' => TRUE,
      '#attributes' => array(
        'class' => array('text', 'r3'),
      ),
    );

    $form['auto_login'] = array(
      '#type' => 'checkbox',
      '#title' => '下次自动登录',
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => '登  录',
      '#attributes' => array(
        'class' => array('botton'),
      ),
      '#prefix' => '<div class="ipt_sub">',
      '#suffix' => '<a class="" href="' . url('resetpwd') . '">忘记密码了？</a></div>',
    );

    $form['#validate'][] = array($this, 'validateName');
    $form['#validate'][] = array($this, 'validateAuthentication');
    $form['#validate'][] = array($this, 'validateFinal');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $account = $this->userStorage->load($form_state['uid']);
    $form_state['redirect'] = 'user/' . $account->id();

    user_login_finalize($account);
  }

  /**
   * Sets an error if supplied username has been blocked.
   */
  public function validateName(array &$form, array &$form_state) {
    if (!empty($form_state['values']['name']) && user_is_blocked($form_state['values']['name'])) {
      // Blocked in user administration.
      form_set_error('name', $this->t('The username %name has not been activated or is blocked.', array('%name' => $form_state['values']['name'])));
    }
  }

  /**
   * Checks supplied username/password against local users table.
   *
   * If successful, $form_state['uid'] is set to the matching user ID.
   */
  public function validateAuthentication(array &$form, array &$form_state) {
    $password = trim($form_state['values']['pass']);
    $flood_config = $this->config('user.flood');
    if (!empty($form_state['values']['name']) && !empty($password)) {
      // Do not allow any login from the current user's IP if the limit has been
      // reached. Default is 50 failed attempts allowed in one hour. This is
      // independent of the per-user limit to catch attempts from one IP to log
      // in to many different user accounts.  We have a reasonably high limit
      // since there may be only one apparent IP for all users at an institution.
      if (!$this->flood->isAllowed('user.failed_login_ip', $flood_config->get('ip_limit'), $flood_config->get('ip_window'))) {
        $form_state['flood_control_triggered'] = 'ip';
        return;
      }
      $accounts = $this->userStorage->loadByProperties(array('name' => $form_state['values']['name'], 'status' => 1));
      $account = reset($accounts);
      if ($account) {
        if ($flood_config->get('uid_only')) {
          // Register flood events based on the uid only, so they apply for any
          // IP address. This is the most secure option.
          $identifier = $account->id();
        }
        else {
          // The default identifier is a combination of uid and IP address. This
          // is less secure but more resistant to denial-of-service attacks that
          // could lock out all users with public user names.
          $identifier = $account->id() . '-' . $this->getRequest()->getClientIP();
        }
        $form_state['flood_control_user_identifier'] = $identifier;

        // Don't allow login if the limit for this user has been reached.
        // Default is to allow 5 failed attempts every 6 hours.
        if (!$this->flood->isAllowed('user.failed_login_user', $flood_config->get('user_limit'), $flood_config->get('user_window'), $identifier)) {
          $form_state['flood_control_triggered'] = 'user';
          return;
        }
      }
      // We are not limited by flood control, so try to authenticate.
      // Set $form_state['uid'] as a flag for self::validateFinal().
      $form_state['uid'] = user_authenticate($form_state['values']['name'], $password);
    }
  }

  /**
   * Checks if user was not authenticated, or if too many logins were attempted.
   *
   * This validation function should always be the last one.
   */
  public function validateFinal(array &$form, array &$form_state) {
    $flood_config = $this->config('user.flood');
    if (empty($form_state['uid'])) {
      // Always register an IP-based failed login event.
      $this->flood->register('user.failed_login_ip', $flood_config->get('ip_window'));
      // Register a per-user failed login event.
      if (isset($form_state['flood_control_user_identifier'])) {
        $this->flood->register('user.failed_login_user', $flood_config->get('user_window'), $form_state['flood_control_user_identifier']);
      }

      if (isset($form_state['flood_control_triggered'])) {
        if ($form_state['flood_control_triggered'] == 'user') {
          form_set_error('name', format_plural($flood_config->get('user_limit'), 'Sorry, there has been more than one failed login attempt for this account. It is temporarily blocked. Try again later or <a href="@url">request a new password</a>.', 'Sorry, there have been more than @count failed login attempts for this account. It is temporarily blocked. Try again later or <a href="@url">request a new password</a>.', array('@url' => url('user/password'))));
        }
        else {
          // We did not find a uid, so the limit is IP-based.
          form_set_error('name', $this->t('Sorry, too many failed login attempts from your IP address. This IP address is temporarily blocked. Try again later or <a href="@url">request a new password</a>.', array('@url' => url('user/password'))));
        }
      }
      else {
        form_set_error('name', '对不起!用户名或密码错误. <a href="' . url('resetpwd', array('query' => array('name' => $form_state['values']['name']))). '">忘记密码?</a>');
        $accounts = $this->userStorage->loadByProperties(array('name' => $form_state['values']['name']));
        if (!empty($accounts)) {
          watchdog('user', 'Login attempt failed for %user.', array('%user' => $form_state['values']['name']));
        }
        else {
          // If the username entered is not a valid user,
          // only store the IP address.
          watchdog('user', 'Login attempt failed from %ip.', array('%ip' => $this->getRequest()->getClientIp()));
        }
      }
    }
    elseif (isset($form_state['flood_control_user_identifier'])) {
      // Clear past failures for this user so as not to block a user who might
      // log in and out more than once in an hour.
      $this->flood->clear('user.failed_login_user', $form_state['flood_control_user_identifier']);
    }
  }

}
