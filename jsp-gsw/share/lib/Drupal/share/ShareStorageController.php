<?php

/**
* @file
* Contains \Drupal\share\ShareStorageController.
*/

namespace Drupal\share;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableDatabaseStorageController;

/**
* Defines the storage controller class for share.
*/
class ShareStorageController extends FieldableDatabaseStorageController implements ShareStorageControllerInterface { 
}

