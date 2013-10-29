<?php
/**
 * @file
 * User page callback file for the user module.
 */
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Plugin\Core\Entity\User;
use Drupal\taxonomy\Plugin\Core\Entity\Term;

/**
 * Access callback for path api/settings/store_image
 */
function settings_store_image_status_page() {
  $account = account_user_authenticate_by_http(); 
  $account = account_settings_load($account->id());
  $response = array(
    'id' => $account->id(),
    'store_image' => $account->store_image,
  );
  return new JsonResponse($response);
}

/**
 * Access callback for path api/settings/store_image/%status
 */
function settings_store_image_changed_page($status = NULL) {
  $account = account_user_authenticate_by_http(); 
  db_update('account_settings')
    ->fields(array('store_image' => $status))
    ->condition('uid', $account->id())
    ->execute();
  $account = account_settings_load($account->id());
  $response = array(
    'id' => $account->id(),
    'store_image' => $account->store_image,
  );
  return new JsonResponse($response);
}
?>
