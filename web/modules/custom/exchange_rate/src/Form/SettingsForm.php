<?php

namespace Drupal\exchange_rate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exchange_rate\ExchangeRateFunctionality;

/**
 * A form that receives a request to receive a JSON file.
 *
 * @package Drupal\exchange_rate\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Variable to show exchange rate form.
   *
   * @var \Drupal\exchange_rate\ExchangeRateFunctionality
   */
  protected $exchangeRateFunctionality;

  /**
   * Exchange rate form id.
   *
   * @var string
   */
  protected string $settingName = 'exchange_rate.admin_settings';

  /**
   * Constructs a new SettingsForm object.
   *
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, ExchangeRateFunctionality $exchangeRateFunctionality) {
    parent::__construct($config_factory);
    $this->exchangeRateFunctionality = $exchangeRateFunctionality;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('exchange_rate.api_connector')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      $this->settingName,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exchange_rate_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config($this->settingName);
    $form['settings']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url'),
      '#default_value' => $config->get('url'),
    ];

    $form['settings']['range'] = [
      '#type' => 'textfield',
      '#attributes' => [
        ' type' => 'number',
        ' min' => '1',
      ],
      '#title' => 'Period of time during which currencies are showing',
      '#maxlength' => 3,
      '#default_value' => $config->get('range'),
    ];

    $form['settings']['request'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('request for form validity'),
      '#default_value' => $config->get('request'),
    ];

    $options = [];
    $currency = $this->exchangeRateFunctionality->getCurrencies();

    foreach ($currency as &$value) {
      $options[$value] = $value;
    }

    $form['settings']['currency'] = [
      '#type' => 'checkboxes',
      '#title' => 'Currencies that will be displayed',
      '#options' => $options,
      '#default_value' => $config->get('currency') ?? [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (empty(trim($form_state->getValue('url')))) {
      $form_state->setErrorByName(
        'url',
        $this->t('The field URL can not be empty.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->exchangeRateFunctionality->getPreviouseCurrencies();

    $this->config($this->settingName)
      ->set('url', $form_state->getValue('url'))
      ->set('range', $form_state->getValue('range'))
      ->set('request', $form_state->getValue('request'))
      ->set('currency', $form_state->getValue('currency'))
      ->save();

    $result = $this->exchangeRateFunctionality->deletedCurrencies();

    foreach ($result as &$value) {
      \Drupal::messenger()->addMessage($this->t('Was deleted currency :' . $value), 'status', TRUE);
    }

    parent::submitForm($form, $form_state);
  }

}
