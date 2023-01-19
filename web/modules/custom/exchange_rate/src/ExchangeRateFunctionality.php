<?php

namespace Drupal\exchange_rate;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

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
   *
   * @var string
   */
  protected string $id = 'exchange_rate.admin_settings';

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * List with previous currencies.
   *
   * @var array
   */
  private $previouseCurrencies = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $client, ConfigFactoryInterface $factory, LoggerChannelFactoryInterface $loggerFactory) {
    $this->client = $client;
    $this->factory = $factory;
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * Returns the parsed file JSON.
   */
  protected function getJson() {
    try {
      $url = $this->factory->get($this->id)->get('url') ?: 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchangenew?json';
      $request = $this->client->get($url);
      $result = $request->getBody()->getContents();
      return json_decode($result);
    }
    catch (RequestException $e) {
      $this->logMessage('Problem with connecting to the server or empty link on JSON file');
      return [];
    }

  }

  /**
   * Get url with config and parse.
   */
  public function getUrl() {
    $data = $this->getJson();
    $currencyData = [];
    $defaultCurrencyData = ['USD', 'EUR', 'PLN', 'GBP'];
    $currency = $this->factory->get($this->id)->get('currency');
    $request = $this->factory->get($this->id)->get('request');

    try {
      if ($request) {
        foreach ($currency as $key => &$variable) {
          if ($variable != 0) {
            $currencyData[] = $key;
          }
        }

        if (count($currencyData) >= 1) {
          $endResult = array_filter($data, function ($key) use ($currencyData) {
            if (in_array($key->cc, $currencyData, TRUE)) {
              return TRUE;
            }
            return FALSE;
          });
        }
        else {
          $endResult = array_filter($data, function ($key) use ($defaultCurrencyData) {
            if (in_array($key->cc, $defaultCurrencyData, TRUE)) {
              return TRUE;
            }
            return FALSE;
          });

          $this->logMessage('None of the currencies was selected, the default list of currencies will be displayed');
        }

        return $endResult;
      }
      else {
        $this->logMessage('The request link to the server has been disabled');
        return [];
      }

    }
    catch (RequestException $e) {
      $this->logMessage('Impossible get the list of currencies');
      return [];
    }
  }

  /**
   * Get currencies list with json file.
   */
  public function getCurrencies() {
    $array = $this->getJson();
    $data = [];

    try {
      foreach ($array as &$value) {
        foreach ($value as $key => &$item) {
          if ($key == 'cc') {
            $data[] = $item;
          }
        }
      }
    }
    catch (RequestException $e) {
      $this->logMessage('Impossible get the list of currencies');
    }

    return $data;
  }

  /**
   * Generates an error message.
   */
  public function logMessage($message) {
    $this->loggerFactory->get('exchange_rate')->notice($message);
  }

  /**
   * Get a list with previous currencies.
   */
  public function getPreviouseCurrencies() {
    $currency = $this->factory->get($this->id)->get('currency');
    $this->previouseCurrencies = $currency;
  }

  /**
   * Returned which currencies were deleted.
   */
  public function deletedCurrencies() {
    $currency = $this->factory->get($this->id)->get('currency');
    $oldCurrencies = $this->previouseCurrencies;
    $arrOldCurrency = [];
    $arrNewCurrency = [];

    foreach ($oldCurrencies as $key => &$item) {
      $arrOldCurrency[] = $key;
    }

    foreach ($currency as $key => &$item) {
      $arrNewCurrency[] = $key;
    }

    return array_diff($arrOldCurrency, $arrNewCurrency);
  }

}
