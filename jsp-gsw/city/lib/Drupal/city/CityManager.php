<?php
/**
 * @file
 * Contains \Drupal\city\CityManager.
 */

namespace Drupal\city;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * City Manager Service.
 */
class CityManager {
  /**
   * Database Service Object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Entity manager Service Object.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Cities Array.
   *
   * @var array
   */
  protected $cities;

  /**
   * Constructs a CityManager object.
   */
  public function __construct(Connection $database, EntityManagerInterface $entityManager) {
    $this->database = $database;
    $this->entityManager = $entityManager;
  }
  
  /**
   * Returns an array of all cities.
   *
   * This list may be used for generating a list of all the cities, or for building
   * the options for a form select.
   *
   * @return
   *   An array of all cities.
   */
  public function getAllCities() {
    if (!isset($this->cities)) {
      $this->loadCities();
    }
    return $this->cities;
  }

  /**
   * Loads Cities Array.
   */
  protected function loadCities() {
    $this->cities = array();
    $cids = $this->database->query("SELECT cid FROM {cities}")->fetchCol();
    //$this->cities = city_load_multiple($cids);
    $this->cities = $this->entityManager->getStorageController('city')->loadMultiple($cids);
    //$this->cities = $this->cityLoadMultiple($cids);
  }

  /**
   * load city entity by city_name if not create it.
   */
  public function getCityByName($city_name) {
    //$city = $this->database->query('SELECT * FROM {cities} WHERE `name` = :name', array(':name' => $city_name))->fetchObject();
    $city = city_load_by_name($city_name);
    if (!$city) {
      $this->database->query('UPDATE {cities_seq} SET cid = cid + 1');
      $cid = $this->database->query('SELECT cid FROM {cities_seq}')->fetchField();
      $array = array(
        'cid' => $cid,
        'name' => $city_name,
      );
      $city = entity_create('city', $array);
      $city->enforceIsNew();
      $city->save();
      //$this->database->query('INSERT INTO {cities} (`cid`, `name`)VALUES(:cid, :name)', array(':cid' => $cid, ':name' => $city_name));
      //$city = $this->entityManager->getStorageController('city')->load($cid);
      //$city = $this->cityLoad($cid);
      //TODO $entity->save()
    }
    return $city;
  }

  /**
   * load district entities by cid
   */
  public function getDistrictsByCityId($cid) {
    /*$dids = $this->database->query('SELECT did FROM {districts} WHERE cid = :cid', array(':cid' => $cid))->fetchCol();
    if ($dids)
      return $this->entityManager->getStorageController('district')->loadMultiple($dids);
      return $this->districtLoadMultiple($dids);
    return FALSE;*/
    if ($districts = city_load_districts($cid)) {
      return $districts;
    } else {
      return FALSE;
    }
  }

  /**
   * load district entity by city_id if not create it.
   */
  public function getDistrictByName($city_id, $district_name) {
    $city = $this->entityManager->getStorageController('city')->load($city_id);
    //$city = $this->cityLoad($city_id);
    if (!$city) return FALSE;
    if ($did = $this->database->query('SELECT * FROM {districts} WHERE cid = :cid AND `name` = :name', array(':cid' => $city_id, ':name' => $district_name))->fetchField()) {
      $district = $this->entityManager->getStorageController('district')->load($did);
    } else {
      $this->database->query('UPDATE {districts_seq} SET did = did + 1');
      $did = $this->database->query('SELECT did FROM {districts_seq}')->fetchField();
      $array = array(
        'did' => $did,
        'cid' => $city_id,
        'name' => $district_name,
      );
      $district = entity_create('district', $array);
      $district->enforceIsNew();
      $district->save();
      //$this->database->query('INSERT INTO {districts} (`did`, `cid`, `name`)VALUES(:did, :cid, :name)', array(':did' => $did,':cid' => $city_id, ':name' => $district_name));
      //$district = $this->entityManager->getStorageController('district')->load($did);
      //$district = $this->districtLoad($did);
      //TODO $entity->save()
    }
    return $district;
  }

}
