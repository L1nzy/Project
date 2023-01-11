<?php

namespace Drupal\exchange_rate\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Hello' Block.
 *
 * @Block(
 *   id = "exchange_rate",
 *   admin_label = @Translation("Exchange rate block"),
 *   category = @Translation("Custom"),
 * )
 */
class ExchangeRate extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $url = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchangenew?json';
    $client = \Drupal::httpClient();
    $request = $client->get($url);
    $response = $request->getBody();

    $decoded_json = json_decode($response->getContents(), TRUE);

    return [
      '#theme' => 'block--exchange-rate',
      '#data' => $decoded_json,
    ];
  }

}
