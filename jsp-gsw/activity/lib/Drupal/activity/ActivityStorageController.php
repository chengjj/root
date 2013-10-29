<?php

/**
 * @file
 * Definition of Drupal\activity\ActivityStorageController.
 */

namespace Drupal\activity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableDatabaseStorageController;
use Drupal\Component\Uuid\Uuid;

/**
 * Defines the controller class for activitys.
 *
 * This extends the Drupal\Core\Entity\DatabaseStorageController class, adding
 * required special handling for activity entities.
 */
class ActivityStorageController extends FieldableDatabaseStorageController implements ActivityStorageControllerInterface {

}
