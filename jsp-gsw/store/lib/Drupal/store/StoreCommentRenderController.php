<?php

/**
 * @file
 * Definition of Drupal\store\StoreCommentRenderController.
 */

namespace Drupal\store;

use Drupal\Core\Entity\EntityInterface;
use Drupal\entity\Entity\EntityDisplay;
use Drupal\jsp\JspRenderController;

/**
 * Render controller for stores.
 */
class StoreCommentRenderController extends JspRenderController {

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
