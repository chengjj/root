<?php

use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * page callback for: catalog/js/%
 */
function catalog_js($option) {
  switch ($option) {
    case 'get_share_catalog_children':
      $parent_cid = $_GET['cid'];
      return catalog_get_share_catalog_children($parent_cid);
      break;
    default:
      break;
  }
}

function catalog_get_share_catalog_children($parent_cid) {
  $response = array();
  if ($parent_cid && is_numeric($parent_cid)) {
    $result = db_query('SELECT * FROM {share_catalog} WHERE parent_cid=:parent_cid ORDER BY weight', array(':parent_cid' => $parent_cid));
    foreach ($result as $row) {
      $response[] = array('cid' => $row->cid, 'name' => $row->name);
    }
  } 
  return new JsonResponse($response);
}
