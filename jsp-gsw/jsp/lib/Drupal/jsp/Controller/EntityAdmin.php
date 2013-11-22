<?php

namespace Drupal\jsp\Controller;

use Drupal\Core\Controller\ControllerBase;

class EntityAdmin extends ControllerBase {

  public function overview() {
    $build = array();

    $links = array();
    foreach ($this->entityManager()->getEntityTypeLabels() as $entity_type => $label) {
      $entity_info = $this->entityManager()->getDefinition($entity_type);
      if (isset($entity_info['controllers']['list'])) {
        $links[$entity_type] = array(
          'title' => $label . '管理',
          'href' => 'admin/entity/' . $entity_type,
        );
      }
    }
    $build['links'] = array('#theme' => 'links', '#links' => $links);

    return $build;
  }

}
