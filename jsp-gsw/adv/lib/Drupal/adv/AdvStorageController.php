<?php

/**
 * @file
 * Definition of Drupal\adv\AdvStorageController.
 */

namespace Drupal\adv;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableDatabaseStorageController;

/**
 * Defines the controller class for advs.
 *
 * This extends the Drupal\Core\Entity\DatabaseStorageController class, adding
 * required special handling for adv entities.
 */
class AdvStorageController extends FieldableDatabaseStorageController implements AdvStorageControllerInterface {

}

