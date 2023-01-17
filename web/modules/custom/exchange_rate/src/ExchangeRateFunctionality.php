<?php

namespace Drupal\exchange_rate;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * The block which shows the exchange rate.
 */
class ExchangeRateFunctionality {

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
   * Exchange rate block id.
   */
  protected string $id = 'exchange_rate.admin_settings';

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $client, ConfigFactoryInterface $factory) {
    $this->client = $client;
    $this->factory = $factory;
  }

  /**
   * Returns the parsed file JSON.
   */
  protected function getJson() {
    $url = $this->factory->get($this->id)->get('url') ?: 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchangenew?json';
    $request = $this->client->get($url);
    $result = $request->getBody()->getContents();

    return json_decode($result);
  }

  /**
   * Get url with config and parse.
   */
  public function getUrl() {
    $data = $this->getJson();
    $currencyData = [];
    $currency = $this->factory->get($this->id)->get('currency');

    try {
      foreach ($currency as $key => &$variable) {
        if ($variable != 0) {
          $currencyData[] = $key;
        }
      }

      $endResult = array_filter($data, function ($key) use ($currencyData) {
        if (in_array($key->cc, $currencyData, TRUE)) {
          return TRUE;
        }
        return FALSE;
      });
    }
    catch (RequestException $e) {
      watchdog_exception('exchange_rate', $e, $e->getMessage());
    }
    return $endResult;
  }

  /**
   * Get currencies list with json file.
   */
  public function getСurrencies() {
    $array = $this->getJson();
    $data = [];

    foreach ($array as &$value) {
      foreach ($value as $key => &$item) {
        if ($key == 'cc') {
          $data[] = $item;
        }
      }
    }

    return $data;
  }

}
