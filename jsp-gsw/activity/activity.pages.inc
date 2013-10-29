<?php

/**
 * title callback: user/%user/activity.
 */
function activity_get_user_activity_title($account) {
  return $account->nickname . '的动态';
}

/**
 * page callback: user/%user/activity.
 */
function activity_get_user_activity_page($account) {
  return theme('account_activity', array('account' => $account, $pager => TRUE));
}
