<?php
/**
 * @file
 * Contains \Drupal\adv\AdvManager.
 */

namespace Drupal\adv;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adv Manager Service.
 */
class AdvManager {
  /**
   * Database Service Object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Entity manager Service Object.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * Constructs a AdvManager object.
   */
  public function __construct(Connection $database, EntityManager $entityManager) {
    $this->database = $database;
    $this->entityManager = $entityManager;
  }
  
}
