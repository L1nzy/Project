<?php

namespace Drupal\exchange_rate;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class for working with entity type manager,save,delete, and edit currencies.
 */
class ExchangeRateEntity {

  /**
   * Variable for using entity type manger.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $typeManger;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $typeManger) {
    $this->typeManger = $typeManger;
  }

  /**
   * Function for getting the type of content.
   */
  public function getEntityStorage() {
    return $this->typeManger->getStorage('node');
  }

  /**
   * Creates and adds currencies to the content.
   */
  public function createEntityRate($listCurrencies) {
    $savedCurrencies = $this->getEntityRate();

    // The deleted currency which is not unique.
    foreach ($listCurrencies as &$value) {
      foreach ($savedCurrencies as &$item) {
        if ($value->cc == $item->cc && $value->rate == $item->rate && $value->exchangedate == $item->exchangedate) {
          $entity = $this->typeManger->getStorage('node')->load((integer) $item->id);
          $entity->delete();
        }
      }
    }

    // Added currencies to content.
    foreach ($listCurrencies as &$value) {
      $entity = $this->getEntityStorage()->create(
            [
              'type' => 'currencies_rate',
              'title' => 'Test entity',
              'field_exchange_date' => $value->exchangedate,
              'field_cc' => $value->cc,
              'field_rate' => $value->rate,
            ]);
      $entity->save();
    }

  }

  /**
   * Receives all available currencies recorded in the content.
   */
  public function getEntityRate() {
    $list = [];
    $storage = $this->typeManger->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'currencies_rate')
      ->execute();

    foreach ($query as &$value) {
      $node = $this->typeManger->getStorage('node')->load((integer) $value);

      $list[] = (object) [
        "id" => $value,
        "exchangedate" => $node->get('field_exchange_date')->getString(),
        "cc" => $node->get('field_cc')->getString(),
        "rate" => $node->get('field_rate')->getString(),
      ];
    }

    return $list;
  }

  /**
   * Deletes all currencies in the content.
   */
  protected function deleteEntityRate() {
    $storage = $this->typeManger->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'currencies_rate')
      ->execute();

    foreach ($query as &$value) {
      $entity = $this->typeManger->getStorage('node')->load((integer) $value);
      $entity->delete();
    }
  }

}
