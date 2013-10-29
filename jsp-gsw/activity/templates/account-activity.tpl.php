<?php
/**
 * @params: 
 *   $account:
 *   $pager:
 */
//TODO $uids follow $user->uid
$uids = db_select('account_follows', 'u')
  ->fields('u', array('follow_uid'))
  ->condition('uid', $account->uid)
  ->execute()
  ->fetchCol();
//TODO $sids follow $store->sid
$sids = db_select('store_follows', 's')
  ->fields('s', array('follow_sid'))
  ->condition('uid', $account->uid)
  ->execute()
  ->fetchCol();

$query = db_select('activity', 'a')
  ->fields('a', array('aid'))
  ->condition('uid', $uids, 'IN')
  ->condition(db_or()->condition('sid', $sids, 'IN'));

if ($pager) {
  $query->extend('Drupal\Core\Database\Query\PagerSelectExtender');
} else {
  $query->range(0, variable_get('row_count', 10));
}

$aids = $query->execute()->fetchCol();

$activities = activity_load_multiple($aids);

print theme('activity_list', array('activities' => $activities));

$pager && print theme('pager');
