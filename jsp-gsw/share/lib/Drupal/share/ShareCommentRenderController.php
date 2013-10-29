<?php

/**
 * @file
 * Definition of Drupal\share\ShareCommentRenderController.
 */

namespace Drupal\share;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\entity\Entity\EntityDisplay;

/**
 * Render controller for comments.
 */
class ShareCommentRenderController extends EntityViewBuilder {

  /**
   * Overrides Drupal\Core\Entity\EntityViewBuilder::alterBuild().
   */
  protected function alterBuild(array &$build, EntityInterface $comment, EntityDisplay $display, $view_mode, $langcode = NULL) {
    parent::alterBuild($build, $comment, $display, $view_mode, $langcode);

    // Add anchor for each comment.
    $prefix = "<a id=\"comment-{$comment->id()}\"></a>\n";
    $build['#prefix'] = $prefix;
  }

}

?>
