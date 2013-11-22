<?php

/**
 * @file
 * Contains \Drupal\share\ShareCatalogStorageControllerInterface.
 */

namespace Drupal\share;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines a common interface for share entity controller classes.
 */
interface ShareCatalogStorageControllerInterface extends EntityStorageControllerInterface {

  /**
   * Loads child catalogs.
   *
   * @return array
   *   An array keyed on cid listing all available categories.
   */
  public function loadChildKeyed($parent_id = 0);

}
