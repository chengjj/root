<?php

/**
 * @file
 * Contains \Drupal\image\EventSubscriber\RouteSubscriber.
 */

namespace Drupal\account\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Defines a route subscriber to register a url for serving image styles.
 */
class AccountRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection, $module) {
    if ($module == 'user') {
      $route = $collection->get('user.view');
      if ($route) {
        $route->setDefaults(array(
          '_content' => '\Drupal\account\Controller\AccountController::userView',
        ));
      }
      if ($route = $collection->get('user.login')) {
        $route->setDefaults(array(
          '_content' => '\Drupal\account\Controller\AccountController::accountLogin',
        ));
      }
      if ($route = $collection->get('user.register')) {
        $route->setDefaults(array(
          '_content' => '\Drupal\account\Controller\AccountController::accountRegister',
        ));
      }
      if ($route = $collection->get('user.pass')) {
        $route->setDefaults(array(
          '_content' => '\Drupal\account\Controller\AccountController::accountResetpwd',
        ));
      }
      if ($route = $collection->get('user.page')) {
        $route->setDefaults(array(
          '_content' => '\Drupal\account\Controller\AccountController::accountLogin',
        ));
      }
    }
  }

}
