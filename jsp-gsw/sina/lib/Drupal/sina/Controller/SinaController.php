<?php

/**
 * @file
 * Contains \Drupal\sina\Controller\SinaController.
 */
namespace Drupal\sina\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for sina routes.
 */
class SinaController extends ControllerBase {

  /**
   * page callback:  sina/redirect
   */
  public function sinaLogin(Request $request) {
    module_load_include('pages.inc', 'sina');
    return weibo_signin_redirect();
  }

  /**
   * page callback:  sina/redirect
   */
  public function sinaAuth(Request $request) {
    module_load_include('pages.inc', 'sina');
    return weibo_auth_callback();
  }
}
