<?php

namespace Drupal\jsp\Form;

use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Form\FormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OverviewEntities extends FormBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * Constructs a new OverEntities.
   *
   * @param \Drupal\Core\Entity\EntityManager $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManager $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'jsp_overview_entities';
  }

  public function buildForm(array $form, array &$form_state, $entity_type = NULL) {
    $form_state['entity_type'] = $entity_type;

    $entity_info = $this->entityManager->getDefinition($entity_type);
    $fields = $this->entityManager->getFieldDefinitions($entity_type);

    foreach ($fields as $field) {
      if ($field['type'] == 'field_item:entity_reference') {
        print_r($field['settings']['target_type']);
      }
    }

    $form['#title'] = $entity_info['label'] . '管理';

    $form['filters'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('table-filter', 'js-show'),
      ),
    );
    $form['filters']['keywords'] = array(
      '#type' => 'search',
      '#title' => '搜索',
      '#size' => 30,
    );

    $query = db_select($entity_info['base_table'], 'e')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    if (isset($form_state['values']['keywords'])) {
      $keywords = trim($form_state['values']['keywords']);
      if (!empty($keywords)) {
        $query->condition($entity_info['entity_keys']['label'], '%' . $keywords . '%', 'LIKE');
      }
    }
    if (isset($fields['weight'])) {
      $query->orderBy('e.weight');
    }
    $query->orderBy('e.' . $entity_info['entity_keys']['label']);
    $ids = $query
      ->fields('e', array($entity_info['entity_keys']['id']))
      ->limit(50)
      ->execute()
      ->fetchCol();
    $entities = entity_load_multiple($entity_type, $ids);

    $header = array('项目');
    if (isset($fields['weight'])) {
      $header[] = '排列顺序';
    }
    $header[] = '操作';

    $form['entities'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#empty' => '还没有任何数据.',
      '#attributes' => array(
        'id' => $entity_type,
      )
    );

    $destination = drupal_get_destination();
    foreach ($entities as $key => $entity) {
      $form['entities'][$key]['#entity'] = $entity;

      $form['entities'][$key]['item'] = entity_view($entity, 'admin');

      if (isset($fields['weight'])) {
        $form['entities'][$key]['weight'] = array(
          '#type' => 'weight',
          '#title' => '排列顺序',
          '#title_display' => 'invisible',
          '#default_value' => $entity->weight->value,
          '#attributes' => array(
            'class' => array('entity-weight'),
          ),
        );
      }

      $uri = $entity->uri();
      $edit_uri = $entity->uri('edit-form');
      $operations = array(
        'edit' => array(
          'title' => '编辑',
          'href' => $edit_uri['path'],
          'query' => $destination,
        ),
        'delete' => array(
          'title' => '删除',
          'href' => $uri['path'] . '/delete',
        ),
      );
      $form['entities'][$key]['operations'] = array(
        '#type' => 'operations',
        '#links' => $operations,
      );

      $form['entities'][$key]['#attributes']['class'] = array();
      if (isset($fields['weight'])) {
        $form['entities'][$key]['#attributes']['class'][] = 'draggable';
      }
    }

    if (isset($fields['weight'])) {
      $form['entities']['#tabledrag'][] = array('order', 'sibling', 'entity-weight');
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => '保存',
      );
    }

    $form['pager'] = array('#theme' => 'pager');

    return $form;
  }

  public function submitForm(array &$form, array &$form_state) {
    $entity_type = $form_state['entity_type'];

    $fields = $this->entityManager->getFieldDefinitions($entity_type);
    if (isset($fields['weight'])) {
      // Sort entity order based on weight.
      uasort($form_state['values']['entities'], 'drupal_sort_weight');

      $changed_entities = array();
      $weight = 0;
      foreach($form_state['values']['entities'] as $id => $values) {
        if (isset($form['entities'][$id]['#entity'])) {
          $entity = $form['entities'][$id]['#entity'];
          if ($entity->weight->value != $weight) {
            $entity->weight->value = $weight;
            $changed_entities[$entity->id()] = $entity;
          }
          $weight ++;
        }
      }
      // Save all updated entities.
      foreach ($changed_entities as $entity) {
        $entity->save();
      }
      drupal_set_message('排列顺序已保存。');
    }
  }
}

