<?php
/**
 * @file
 * Contains \Drupal\adv\AdvManager.
 */

namespace Drupal\adv;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
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
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a AdvManager object.
   */
  public function __construct(Connection $database, EntityManagerInterface $entityManager) {
    $this->database = $database;
    $this->entityManager = $entityManager;
  }
  
}
