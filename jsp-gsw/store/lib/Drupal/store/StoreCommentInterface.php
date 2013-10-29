<?php

/**
 * @file
 * Contains \Drupal\store\Entity\StoreCommentInterface.
 */

namespace Drupal\store;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a comment entity.
 */
interface StoreCommentInterface extends ContentEntityInterface {

  /**
   * Returns the permalink URL for this comment.
   *
   * @return array
   *   An array containing the 'path' and 'options' keys used to build the URI
   *   of the comment, and matching the signature of
   *   UrlGenerator::generateFromPath().
   */
  public function permalink();
}
