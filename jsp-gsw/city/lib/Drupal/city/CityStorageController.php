<?php
/**
 * @file
 * Contains \Drupal\city\CityStorageController.
 */
namespace Drupal\city;

use Drupal\Core\Entity\FieldableDatabaseStorageController;

/**
 * Storage controller for Cities.
 */
class CityStorageController extends FieldableDatabaseStorageController implements CityStorageControllerInterface { 

  /**
   * {@inheritdoc}
   */
  public function loadAllKeyed() {
    return $this->database->query('SELECT c.cid, c.name FROM {cities} c ORDER BY weight')->fetchAllKeyed();
  }
}

