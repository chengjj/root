<?php

/**
 * @file
 * Definition of Drupal\adv_block\AdvBlockItemFormController.
 */

namespace Drupal\adv_block;

use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityFormController;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\FieldInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base for controller for adv_block_item forms.
 */
class AdvBlockItemFormController extends ContentEntityFormController {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   */
  public function form(array $form, array &$form_state) {
    $adv_block_item = $this->entity;

    if (!$adv_block_item->isNew()) {
      $entity = entity_load($adv_block_item->type->value, $adv_block_item->entity_id->value);
      $form['entity'] = entity_view($entity, 'teaser');
    }

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => '标题文字',
      '#default_value' => $adv_block_item->title->value,
    );
    $form['reason'] = array(
      '#type' => 'textfield',
      '#title' => '推荐理由',
      '#default_value' => $adv_block_item->reason->value,
    );

    return parent::form($form, $form_state, $adv_block_item);
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, array &$form_state) {
    $adv_block_item = $this->entity;

    $adv_block_item->save();
    $form_state['values']['iid'] = $adv_block_item->id();

    drupal_set_message('广告已保存.');
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, array &$form_state) {
    $destination = array();
    if ($this->getRequest()->query->has('destination')) {
      $destination = drupal_get_destination();
    }
    $form_state['redirect'] = array('admin/adv_block_item/' . $this->entity->id() . '/delete', array('query' => $destination));
  }

}
