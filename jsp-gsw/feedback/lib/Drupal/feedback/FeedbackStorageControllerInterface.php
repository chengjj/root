<?php

/**
 * @file
 * Contains \Drupal\feedback\FeedbackStorageControllerInterface.
 */

namespace Drupal\feedback;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\feedback\FeedbackInterface;

/**
 * Defines a common interface for feedback entity controller classes.
 */
interface FeedbackStorageControllerInterface extends EntityStorageControllerInterface {

}
