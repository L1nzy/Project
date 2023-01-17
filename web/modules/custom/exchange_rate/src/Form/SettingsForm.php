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
  protected $showExchangeRateForm;

  /**
   * Exchange rate form id.
   *
   * @var string
   */
  protected string $id = 'exchange_rate.admin_settings';

  /**
   * Constructs a new SettingsForm object.
   *
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, ExchangeRateFunctionality $showExchangeRateForm) {
    parent::__construct($config_factory);
    $this->showExchangeRateForm = $showExchangeRateForm;
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
      $this->id,
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
    $config = $this->config($this->id);
    $form['settings']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url'),
      '#default_value' => $config->get('url'),
    ];

    $form['settings']['request'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('request for form validity'),
      '#default_value' => $config->get('request'),
    ];

    $options = [];
    $currency = $this->showExchangeRateForm->getCurrencies();

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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config($this->id)
      ->set('url', $form_state->getValue('url'))
      ->set('request', $form_state->getValue('request'))
      ->set('currency', $form_state->getValue('currency'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
