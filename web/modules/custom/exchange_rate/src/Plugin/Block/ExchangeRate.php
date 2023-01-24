<?php

namespace Drupal\exchange_rate\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exchange_rate\ExchangeRateFunctionality;

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
   * @var \Drupal\exchange_rate\ExchangeRateFunctionality
   */
  protected $showExchangeRateBlock;

  /**
   * Filling in the basic constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExchangeRateFunctionality $showExchangeRateBlock) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->showExchangeRateBlock = $showExchangeRateBlock;
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
    // List of all currencies.
    $list = $this->showExchangeRateBlock->getUrl();

    $currency = $this->showExchangeRateBlock->getSelectedCurrencies();
    $listCurrencies = [];

    foreach ($currency as &$variable) {
      $listCurrencies[] = $this->showExchangeRateBlock->getOneCurrency($variable);
    }

    return [
      '#theme' => 'block--exchange-rate',
      '#data' => $list,
      '#exchange_var' => $listCurrencies,
      '#attached' => [
        'library' => [
          'exchange_rate/module-chart-js',
        ],
        'drupalSettings' => [
          'exchange_rate' => [
            'title' => $this->t('title'),
            'currency' => $listCurrencies,
          ],
        ],
      ],
    ];
  }

}
