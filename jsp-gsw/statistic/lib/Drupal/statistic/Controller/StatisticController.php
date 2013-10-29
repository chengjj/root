<?php

/**
 * @file
 * Contains \Drupal\statistic\Controller\StatisticController.
 */
namespace Drupal\statistic\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
//use Drupal\Core\Controller\ControllerInterface;
use Drupal\statistic\StatisticManager;
use Drupal;

/**
 * Controller routines for statistic routes.
 */
class StatisticController implements ContainerInjectionInterface {
  /**
   * Statistic Manager Service.
   *
   * @var \Drupal\statistic\StatisticManager
   */
  protected $statisticManager;

  /**
   * Injects StatisticManager Service.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('statistic.manager'));
  }

  /**
   * Constructs a StatisticController object.
   */
  public function __construct(StatisticManager $statisticManager) {
    $this->statisticManager = $statisticManager;
  }

  /**
   * page callback: api/statistic
   */
  public function statisticAdd(Request $request) {
    //TODO remove statistic_create_page();
    module_load_include('pages.inc', 'statistic');
    return statistic_create_page();
  }

  /**
   * page callback: admin/statistic
   */
  public function statisticManage(Request $request) {
    //TODO remove admin_statistic_form
    module_load_include('pages.inc', 'statistic');
    return drupal_get_form('admin_statistic_form');
  }
}
