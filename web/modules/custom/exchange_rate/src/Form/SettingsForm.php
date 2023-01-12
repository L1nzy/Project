<?php

/**
 * @file
 * Contains Drupal\exchange_rate\Form\SettingsForm.
 */

namespace Drupal\exchange_rate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\exchange_rate\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'exchange_rate.admin_settings',
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
    $config = $this->config('exchange_rate.admin_settings');
    $form['settings']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url'),
      '#default_value' => $config->get('url'),
    ];

    $form['settings']['request'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('request'),
      '#default_value' => $config->get('request'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('exchange_rate.admin_settings')
      ->set('url', $form_state->getValue('url'))
      ->set('request', $form_state->getValue('request'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
