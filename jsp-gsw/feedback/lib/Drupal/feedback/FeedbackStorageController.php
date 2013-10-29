<?php

/**
 * @file
 * Definition of Drupal\feedback\FeedbackStorageController.
 */

namespace Drupal\feedback;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableDatabaseStorageController;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Defines the controller class for feedbacks.
 *
 * This extends the Drupal\Core\Entity\DatabaseStorageController class, adding
 * required special handling for feedback entities.
 */
class FeedbackStorageController extends FieldableDatabaseStorageController implements FeedbackStorageControllerInterface {

}
