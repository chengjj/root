<?php

namespace Drupal\city;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

interface CityStorageControllerInterface extends EntityStorageControllerInterface {

  /**
   * Loads all cities.
   *
   * @return array
   *   An array keyed on cid listing all available cities.
   */
  public function loadAllKeyed();

}

