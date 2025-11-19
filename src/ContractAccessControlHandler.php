<?php

namespace Drupal\solana_contracts;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Contract entity.
 *
 * @see \Drupal\solana_contracts\Entity\Contract.
 */
class ContractAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\solana_contracts\Entity\Contract $entity */

    // Admin can do anything.
    if ($account->hasPermission('administer contract entities')) {
      return AccessResult::allowed();
    }

    switch ($operation) {
      case 'view':
        if ($account->hasPermission('view own contract entities')) {
          // Check if user is party A or party B.
          if ($entity->get('party_a')->target_id == $account->id() || $entity->get('party_b')->target_id == $account->id()) {
            return AccessResult::allowed();
          }
        }
        break;

      case 'sign':
        if ($account->hasPermission('sign contract entities')) {
          // Check if user is party A or party B.
          if ($entity->get('party_a')->target_id == $account->id() || $entity->get('party_b')->target_id == $account->id()) {
            return AccessResult::allowed();
          }
        }
        break;
    }

    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'create contract entities');
  }

}
