<?php
/**
 * @file
 * Contains \Drupal\statistic\StatisticManager.
 */

namespace Drupal\statistic;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Statistic Manager Service.
 */
class StatisticManager {
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
   * Constructs a StatisticManager object.
   */
  public function __construct(Connection $database, EntityManagerInterface $entityManager) {
    $this->database = $database;
    $this->entityManager = $entityManager;
  }
  
}
