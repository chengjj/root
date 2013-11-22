<?php

namespace Drupal\adv_block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\adv_block\AdvBlockInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AdvBlockController extends ControllerBase {

  public function admin() {
    module_load_include('admin.inc', 'adv_block');
    return drupal_get_form('admin_adv_block_list_form');
  }

  public function admin_adv_block(AdvBlockInterface $adv_block) {
    return drupal_get_form('admin_adv_block_item_list_form', $adv_block);
  } 

  /**
   * page callback: admin/adv_block/%adv_block/advs/sort
   */
  public function admin_adv_block_sort(AdvBlockInterface $adv_block) {
    //return drupal_get_form('admin_adv_block_' . $adv_block->type->value . '_list_sort_form', $adv_block);
    return drupal_get_form('admin_adv_block_item_list_sort_form', $adv_block);
  } 

  public function addItem(Request $request, AdvBlockInterface $adv_block, $entity_id) {
    $item = $this->entityManager()->getStorageController('adv_block_item')->create(array(
      'bid' => $adv_block->id(),
      'entity_id' => $entity_id,
    ));
    $item->save();

    drupal_set_message('成功推荐到' . $adv_block->label());
    return new RedirectResponse($request->get('destination'));
  }
}
