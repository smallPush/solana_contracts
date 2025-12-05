<?php

namespace Drupal\solana_contracts;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Solana Account entities.
 *
 * @ingroup solana_contracts
 */
class SolanaAccountListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Solana Account ID');
    $header['address'] = $this->t('Address');
    $header['user'] = $this->t('User');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\solana_contracts\Entity\SolanaAccount */
    $row['id'] = $entity->id();
    $row['address'] = Link::createFromRoute(
      $entity->label(),
      'entity.solana_account.edit_form',
      ['solana_account' => $entity->id()]
    );
    $row['user'] = $entity->get('user_id')->entity->getDisplayName();
    return $row + parent::buildRow($entity);
  }

}
