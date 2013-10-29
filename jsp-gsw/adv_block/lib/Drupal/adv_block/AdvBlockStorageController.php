<?php

/**
 * @file
 * Definition of Drupal\adv_block\AdvBlockStorageController.
 */

namespace Drupal\adv_block;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableDatabaseStorageController;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Defines the controller class for adv_blocks.
 *
 * This extends the Drupal\Core\Entity\DatabaseStorageController class, adding
 * required special handling for adv_block entities.
 */
class AdvBlockStorageController extends FieldableDatabaseStorageController implements AdvBlockStorageControllerInterface {

}

