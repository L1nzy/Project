<?php

namespace Drupal\exchange_rate\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exchange_rate\BlockServices;

/**
 * Provides a 'Exchange rate' Block.
 *
 * @Block(
 *   id = "exchange_rate",
 *   admin_label = @Translation("Exchange rate block"),
 *   category = @Translation("Custom"),
 * )
 */
class ExchangeRate extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Variable to service.
   *
   * @var \Drupal\exchange_rate\BlockServices
   */
  protected $service;

  /**
   * Filling in the basic constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BlockServices $service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('exchange_rate.api_connector')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $list = $this->service->getUrl();

    return [
      '#theme' => 'block--exchange-rate',
      '#data' => $list,
    ];
  }

}
