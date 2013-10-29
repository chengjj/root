<?php

namespace Drupal\jsp\Controller;

use Drupal\Core\Controller\ControllerBase;

class EntityAdmin extends ControllerBase {
  public function entityCreate($entity_type) {
    $entity = entity_create($entity_type, array());
    return \Drupal::entityManager()->getForm($entity);
  }
}
