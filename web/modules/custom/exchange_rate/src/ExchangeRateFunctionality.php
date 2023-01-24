<?php

namespace Drupal\exchange_rate;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Messenger\MessengerInterface;

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
  protected string $settingName = 'exchange_rate.admin_settings';

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
   * Variable entity for saving exchange rate.
   *
   * @var \Drupal\exchange_rate\ExchangeRateEntity
   */
  private $entity;

  /**
   * Include the messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $client, ConfigFactoryInterface $factory, LoggerChannelFactoryInterface $loggerFactory, ExchangeRateEntity $entity, MessengerInterface $messenger) {
    $this->client = $client;
    $this->factory = $factory;
    $this->loggerFactory = $loggerFactory;
    $this->entity = $entity;
    $this->messenger = $messenger;
  }

  /**
   * Returns the parsed file JSON.
   */
  protected function getJson() {
    try {
      $range = $this->factory->get($this->settingName)->get('range');

      $endDrupalDate = new DrupalDateTime('-' . $range . ' day');
      $endDate = $endDrupalDate->format('Ymd');
      $startDrupalDate = new DrupalDateTime();
      $startDate = $startDrupalDate->format('Ymd');

      $endpoint = '?start=' . $endDate . '&end=%20' . $startDate . '&sort=exchangedate&order=desc&json';

      $url = $this->factory->get($this->settingName)->get('url') . $endpoint;
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
    $defaultCurrencyData = ['USD', 'EUR', 'PLN', 'GBP'];
    $request = $this->factory->get($this->settingName)->get('request');
    $list = [];

    if ($request) {
      $result = $this->getSelectedCurrencies();

      if (!empty($result)) {
        $list = $result;
      }
      else {
        $list = $defaultCurrencyData;
        $this->logMessage('None of the currencies was selected, the default list of currencies will be displayed');
      }

      $endResult = array_filter($data, function ($key) use ($list) {
        return in_array($key->cc, $list, TRUE);
      });

      return $endResult;
    }
    else {
      $this->logMessage('The request link to the server has been disabled');
      return [];
    }
  }

  /**
   * Get currencies list with json file.
   */
  public function getCurrencies() {
    $array = $this->getJson();
    $data = [];

    foreach ($array as &$value) {
      $data[] = $value->cc;
    }

    return $data;
  }

  /**
   * Function with getting selected currencies.
   */
  public function getSelectedCurrencies() {
    $currencyData = [];
    $currency = $this->factory->get($this->settingName)->get('currency');

    foreach ($currency as $key => &$variable) {
      if ($variable != 0) {
        $currencyData[] = $key;
      }
    }

    return array_unique($currencyData);
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
    $currency = $this->factory->get($this->settingName)->get('currency');
    $this->previouseCurrencies = $currency;
  }

  /**
   * Returned which currencies were deleted.
   */
  public function deletedCurrencies() {
    $currency = $this->factory->get($this->settingName)->get('currency');
    $arrOldCurrency = array_keys($this->previouseCurrencies);
    $arrNewCurrency = array_keys($currency);

    $result = array_diff($arrOldCurrency, $arrNewCurrency);

    foreach ($result as &$value) {
      $this->messenger->addMessage('Was deleted currency :' . $value);
    }
  }

  /**
   * The function returns an array of one currency period of time.
   */
  public function getOneCurrency($nameCurrency, $nameArray) {
    $arrayCurrency = [];

    if (!empty($nameCurrency)) {
      foreach ($nameArray as &$variable) {
        if ($variable->cc == strtoupper($nameCurrency)) {
          $arrayCurrency[] = $variable;
        }
      }
    }
    else {
      return [];
    }

    return $arrayCurrency;
  }

  /**
   * Added settings for showing currencies and use API or content for showing.
   */
  public function saveCurrencies() {
    $range = $this->factory->get($this->settingName)->get('range');

    $startDrupalDate = new DrupalDateTime('-' . $range . ' day');
    $startDate = $startDrupalDate->format('Ymd');
    $todayDrupalDate = new DrupalDateTime();
    $todayDate = $todayDrupalDate->format('Ymd');

    $currenciesOfContent = $this->entity->getEntityRate();
    $isValid = FALSE;
    $currency = $this->getSelectedCurrencies();
    $validDate = 0;

    foreach ($currenciesOfContent as &$value) {
      foreach ($currency as &$item) {
        if ($value->exchangedate == (string) $startDate && $value->cc == $item) {
          $validDate++;
        }
      }
    }

    foreach ($currenciesOfContent as &$value) {
      if ($value->exchangedate == (string) $todayDate) {
        $isValid = TRUE;
        break;
      }
    }

    if ($isValid && count($currency) == $validDate) {
      $allCurrenciesOfContent = $this->getSelectedCurrencies();

      $endResult = array_filter($currenciesOfContent, function ($key) use ($allCurrenciesOfContent) {
        return in_array($key->cc, $allCurrenciesOfContent, TRUE);
      });

      $dateList = [];

      for ($i = 0; $i < $range; $i++) {
        $drupalDate = new DrupalDateTime('-' . $i . ' day');
        $dateList[] = $drupalDate->format('Y.m.d');
      }

      return array_filter($endResult, function ($key) use ($dateList) {
        return in_array($key->exchangedate, $dateList, TRUE);
      });
    }
    else {
      $this->entity->createEntityRate($this->getUrl());
      return $this->getUrl();
    }
  }

}
