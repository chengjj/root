<?php

/**
 * @file
 * Contains \Drupal\adv\Controller\AdvController.
 */
namespace Drupal\adv\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
//use Drupal\Core\Controller\ControllerInterface;
use Drupal\adv\Entity\AdvInterface;
use Drupal\adv\AdvManager;
/**
 * Controller routines for adv routes.
 */
class AdvController implements ContainerInjectionInterface {
  /**
   * Adv Manager Service.
   *
   * @var \Drupal\adv\AdvManager
   */
  protected $advManager;

  /**
   * Injects AdvManager Service.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('adv.manager'));
  }

  /**
   * Constructs a AdvController object.
   */
  public function __construct(AdvManager $advManager) {
    $this->advManager = $advManager;
  }

  /**
   * page callback: api/adverts
   */
  public function advList(Request $request) {
    return $this->adv_city_list_page($request);
  }

  /**
   * page callback: api/adverts/{city_id}
   */
  public function advListByCity(Request $request, $city_id) {
    return $this->adv_city_list_page($request, $city_id);
  }

  protected function adv_city_list_page(Request $request, $city_id = NULL) {
    $page = $request->query->get('page');
    if (!$page || !is_numeric($page)) {
      $page = 1;
    }
    $per_page = $request->query->get('per_page');
    if (!$per_page || !is_numeric($per_page)) {
      $per_page = 5;
    }
    $advs = adv_load_multiple_by_city_id($city_id, $page - 1, $per_page);
    foreach ($advs as $adv) {
      $picture = $adv->getPicture();
      $response[] = array(
        'id' => $adv->id(),
        'image_url' => $picture ? file_create_url($picture->getFileuri()) : variable_get('store_default_picture', 'http://api.gsw100.com/sites/default/files/store_default_picture.png'),
        'city_id' => $adv->cid->value ? $adv->cid->value : 0,
        'store_id' => $adv->sid->value ? $adv->sid->value : '',
      );
    }

    return new JsonResponse($response);
  }

  /**
   * page callback: admin/adv
   */
  public function advManage() {
    module_load_include('admin.inc', 'adv');
    return drupal_get_form('admin_adv_list_form');
  }

  /**
   * page callback: adv/js/{action}
   */
  public function advAjaxAction($action,Request $request) {
    switch ($action) {
      case 'store':
        $matches = array();
        $string = $request->query->get('q');
        if (!empty($string)) {
          $result = db_select('stores', 's')
            ->fields('s', array('name'))
            ->condition('name', db_like($string) . '%', 'LIKE')
            ->range(0,10)
            ->execute();
           foreach ($result as $store) {
             $matches[$store->name] = check_plain($store->name);
           } 
        }
        return new JsonResponse($matches);
        break;
      case 'adv':
        $matches = array();
        $string = $request->query->get('q');
        if (!empty($string)) {
          $result = db_select('advs', 's')
            ->fields('s', array('title'))
            ->condition('title', db_like($string) . '%', 'LIKE')
            ->range(0,10)
            ->execute();
          foreach ($result as $adv) {
            $matches[$adv->title] = check_plain($adv->title);
          }
        }
        return new JsonResponse($matches);
    }
  }

  /**
   * page callback:admin/adv/edit
   */
  public function advAdd() {
    module_load_include('admin.inc', 'adv');
    return drupal_get_form('admin_adv_edit_form');
  }

  /**
   * page callback:admin/adv/edit/{adv_id}
   */
  public function advEdit(Request $request, $adv_id) {
    //TODO use $adv entity
    if ($adv = entity_load('adv', $adv_id)) {
      module_load_include('admin.inc', 'adv');
      return drupal_get_form('admin_adv_edit_form', $adv);
    }
  }
  
}
