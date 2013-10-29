<?php

namespace Drupal\jsp;

use Drupal\Core\Entity\EntityViewBuilder;

class JspRenderController extends EntityViewBuilder {

  /**
   * Overrides \Drupal\Core\Entity\EntityViewBuilder::viewMultiple().
   */
  public function viewMultiple(array $entities = array(), $view_mode = 'full', $langcode = NULL) {
    $build = parent::viewMultiple($entities, $view_mode, $langcode);

    if ($view_mode != 'full') {
      $ids = array_keys($build);
      $id = array_pop($ids);
      $build[$id]['#attributes']['class'][] = 'last';
    }

    return $build;
  }

}

