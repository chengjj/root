<?php
/**
 * @file
 * Contains \Drupal\catalog\CatalogManager.
 */

namespace Drupal\catalog;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Catalog Manager Service.
 */
class CatalogManager {
  /**
   * Database Service Object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Entity manager Service Object.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * Constructs a CatalogManager object.
   */
  public function __construct(Connection $database, EntityManager $entityManager) {
    $this->database = $database;
    $this->entityManager = $entityManager;
  }
  
  /**
   * load catalog entity by city_id.
   */
  public function getStoreCatalogListByCityId($city_id) {
    //if store_catalog has city_id load it
    $cids = $this->database->query('SELECT * FROM {store_catalog} WHERE `city_id` = :city_id AND parent_cid = 0 ORDER BY weight', array(':city_id' => $city_id))->fetchCol();
    if (!$cids) 
      $cids = $this->database->query('SELECT cid FROM {store_catalog} WHERE parent_cid = 0 ORDER BY weight')->fetchCol();
    if ($cids) 
      return $this->entityManager->getStorageController('store_catalog')->loadMultiple($cids);
    return FALSE; 
  }

  /**
   * load catalog entity by catalog_cid
   */
  public function getStoreCatalog($cid) {
    if (!is_numeric($cid)) return FALSE;
    return $this->entityManager->getStorageController('store_catalog')->load($cid);
  }
}
