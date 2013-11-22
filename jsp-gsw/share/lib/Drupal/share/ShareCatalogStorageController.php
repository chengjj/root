<?php

/**
 * @file
 * Definition of Drupal\share\ShareCatalogStorageController.
 */

namespace Drupal\share;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableDatabaseStorageController;
use Drupal\Component\Uuid\Uuid;

/**
 * Defines the controller class for shares.
 *
 * This extends the Drupal\Core\Entity\DatabaseStorageController class, adding
 * required special handling for share entities.
 */
class ShareCatalogStorageController extends FieldableDatabaseStorageController implements ShareCatalogStorageControllerInterface {

  /**
   * {@inheritdoc}
   */
  public function loadChildKeyed($parent_id = 0) {
    return $this->database->query('SELECT c.cid, c.name FROM {share_catalog} c WHERE c.parent_cid=:parent_id ORDER BY weight', array(':parent_id' => $parent_id))->fetchAllKeyed();
  }

}
