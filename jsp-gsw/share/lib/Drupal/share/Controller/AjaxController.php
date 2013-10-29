<?php

/**
 * @file
 * Contains \Drupal\share\Controller\AjaxController.
 */
namespace Drupal\share\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\share\ShareCatalogInterface;

/**
 * Controller routines for share ajax routes.
 */
class AjaxController extends ControllerBase {

  public function catalogChildren(Request $request, ShareCatalogInterface $share_catalog) {
    $catalogs = share_catalog_load_children($share_catalog->id());
    $links = array();
    foreach ($catalogs as $catalog) {
      $link = array('title' => $catalog->name->value, 'href'=> 'share', 'query' => array('cid' => $catalog->id()));
      $links[] = $link;
    }
    $build = array('#theme' => 'links', '#links' => $links);

    return new Response(drupal_render($build));
  }
}

