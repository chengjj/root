<?php

namespace Drupal\share;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

interface ShareStorageControllerInterface extends EntityStorageControllerInterface {

  function updateBookmarkCount(ShareInterface $share);

 /**
 * Defines the storage controller class for share.
 */
  public function bookmark(ShareInterface $share);

 /**
 * delete a share bookmark.
 */
  public function unbookmark(ShareInterface $share);
}

