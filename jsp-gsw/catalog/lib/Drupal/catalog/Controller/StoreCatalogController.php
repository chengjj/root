<?php

/**
 * @file
 * Contains \Drupal\catalog\Controller\StoreCatalogController.
 */
namespace Drupal\catalog\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
//use Drupal\Core\Controller\ControllerInterface;
use Drupal\catalog\CatalogManager;
/**
 * Controller routines for catalog routes.
 */
class StoreCatalogController implements ContainerInjectionInterface {
  /**
   * Catalog Manager Service.
   *
   * @var \Drupal\catalog\CatalogManager
   */
  protected $catalogManager;

  /**
   * Injects CatalogManager Service.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('catalog.manager'));
  }

  /**
   * Constructs a StoreCatalogController object.
   */
  public function __construct(CatalogManager $catalogManager) {
    $this->catalogManager = $catalogManager;
  }

  /**
   * returns all store catalog for current city 
   * page callback for api/taxonomies/{city_id}
   * @return json
   */
  public function storeCatalogList($city_id) {
    $catalogs = array();
    foreach ($this->catalogManager->getStoreCatalogListByCityId($city_id) as $store_catalog) {
      //TODO image_url default image or return null
      $children = array();
      foreach (store_catalog_load_children($store_catalog->id()) as $children_catalog) {
        $children[] = $this->get_store_catalog_response($children_catalog, $city_id);
      }
      $picture = $store_catalog->getPicture();
      $catalogs[] = array(
        'id' => $store_catalog->id(), 
        'name' => $store_catalog->label(),
        'image_url' => $picture ? file_create_url($picture->getFileUri()) : '',
        'weight' => $store_catalog->weight->value,
        'city_id' => $city_id,
        'parent_cid' => $store_catalog->parent_cid->value,
        'children' => $children,
      );
    }
    return new JsonResponse($catalogs);
  }

  /**
   * return store catalog by catalog_cid
   * page callbacl for: api/taxonomy/{catalog_cid}
   */
  public function storeCatalogDetail($catalog_cid) {
    $store_catalog = $this->catalogManager->getStoreCatalog($catalog_cid);
    return new JsonResponse($this->get_store_catalog_response($store_catalog));
  }

  /**
   * get store catalog response
   */
  protected function get_store_catalog_response($store_catalog, $city_id = 0) {
    $picture = $store_catalog->getPicture();
    return array(
      'id' => $store_catalog->id(), 
      'name' => $store_catalog->label(),
      'image_url' => $picture ? file_create_url($picture->getFileUri()) : '',
      'weight' => $store_catalog->weight->value,
      'city_id' => $store_catalog->city_id->value ? $store_catalog->city_id->value : $city_id,
      'parent_cid' => $store_catalog->parent_cid->value,
    );
  }

  /**
   * page callback: catalog/js/{option}
   */
  public function storeCatalogJs(Request $request, $option) {
    module_load_include('pages.inc', 'catalog');
    return catalog_js($option, $request);
  }
}
