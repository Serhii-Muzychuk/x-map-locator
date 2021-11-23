<?php

namespace Drupal\x_map_locator_sales_rep\Controller;

use Drupal\Core\Controller\ControllerBase;
use Laminas\Diactoros\Response\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @TechDoc("Controller for get Sales by zip code.")
 *
 * Class GetSalesByZipController
 *
 * @package Drupal\x_map_locator_sales_rep\Controller
 */
class GetSalesByZipController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): GetSalesByZipController {
    $instance = parent::create($container);
    $instance->request = $container->get('request_stack')->getCurrentRequest();
    $instance->xSalesRep = $container->get('x_sales_rep.client');
    $instance->config = $container->get('config.factory')->get('x_map_locator.settings');

    return $instance;
  }

  /**
   * @TechDoc("Provides search by Sales rep service.")
   *
   * {@inheritdoc}
   */
  public function build() {
    $zip = $this->request->get('zip');
    $sales_response = $this->xSalesRep->search($zip);

    if (empty($sales_response)) {
      return new JsonResponse('', 404);
    }

    $limit = !empty($this->config->get('number_limit')) ? $this->config->get('number_limit') : 0;
    $response = '';

    foreach ($sales_response as $item) {
      if (round($item->TERRITORY_CODE) < round($limit)) {
        $response = trim(strtolower($item->REP_EMAIL));
      }
    }

    return new JsonResponse($response, 200);
  }

}
