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
    error_log($module);
    if ($module == 'user') {
      $route = $collection->get('user.view');
      if ($route) {
        $route->setDefaults(array(
          '_content' => '\Drupal\account\Controller\AccountController::userView',
        ));
      }
    }
  }

}
