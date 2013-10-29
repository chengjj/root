<?php

/**
 * @file
 * Contains \Drupal\city\Controller\CityController.
 */
namespace Drupal\city\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
//use Drupal\Core\Controller\ControllerInterface;
use Drupal\city\CityManager;
use Drupal;
/**
 * Controller routines for city routes.
 */
class CityController implements ContainerInjectionInterface {
  /**
   * City Manager Service.
   *
   * @var \Drupal\city\CityManager
   */
  protected $cityManager;

  /**
   * Injects CityManager Service.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('city.manager'));
  }

  /**
   * Constructs a CityController object.
   */
  public function __construct(CityManager $cityManager) {
    $this->cityManager = $cityManager;
  }

  /**
   * returns all cities 
   * page callback for api/cities
   * @return json
   */
  public function cityList() {
    $cities = array();
    foreach ($this->cityManager->getAllCities() as $city) {
      $cities[] = array('id' => $city->id(), 'name' => $city->label());
    }
    return new JsonResponse($cities);
  }

  /**
   * return $city_name city
   * page callback for api/city/%city_name
   */
  public function cityDetail($city_name) {
    $city = $this->cityManager->getCityByName($city_name);
    return new JsonResponse(array('id' => $city->id(), 'name' => $city->label()));
  }

  /**
   * returns all cities 
   * page callback for api/city/%city_id/districts
   * @return json
   */
  public function districtList($city_id) {
    $districts = array();
    foreach ($this->cityManager->getDistrictsByCityId($city_id) as $district) {
      $districts[] = array('id' => $district->id(), 'city_id' => $city_id, 'name' => $district->label()); 
    }
    return new JsonResponse($districts);
  }

  /**
   * returns all cities 
   * page callback for api/city/%city_id/district/%district_name
   * @return json
   */
  public function districtDetail($city_id, $district_name) {
    $district = $this->cityManager->getDistrictByName($city_id, $district_name);
    return new JsonResponse(array('id' => $district->id(), 'city_id' => $district->cid->value, 'name' => $district->label()));
  }

}
