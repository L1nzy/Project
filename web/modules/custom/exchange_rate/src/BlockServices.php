<?php

namespace Drupal\exchange_rate;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Controller for get url alias.
 */
class BlockServices {
  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $factory;

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $client, ConfigFactoryInterface $factory) {
    $this->client = $client;
    $this->factory = $factory;
  }

  /**
   * Get url with config and parse.
   */
  public function getUrl() {
    $data = [];
    $url = $this->factory
      ->get('exchange_rate.admin_settings')
      ->get('url') ?: 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchangenew?json';

    try {
      $request = $this->client->get($url);
      $result = $request->getBody()->getContents();
      $data = json_decode($result);
    }
    catch (RequestException $e) {
      watchdog_exception('exchange_rate', $e, $e->getMessage());
    }
    return $data;
  }

}
