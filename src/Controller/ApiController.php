<?php

namespace Drupal\solana_contracts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * API Controller for Solana Contracts.
 */
class ApiController extends ControllerBase {

  /**
   * Returns all contracts and their status.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function getContracts() {
    $storage = $this->entityTypeManager()->getStorage('contract');
    $contracts = $storage->loadMultiple();
    $data = [];

    foreach ($contracts as $contract) {
      /** @var \Drupal\solana_contracts\Entity\Contract $contract */
      $data[] = [
        'id' => $contract->id(),
        'title' => $contract->label(),
        'description' => $contract->get('description')->value,
        'status' => $contract->get('contract_status')->value,
        'party_a' => $contract->get('party_a')->target_id,
        'party_b' => $contract->get('party_b')->target_id,
        'hash' => $contract->get('hash')->value,
        'created' => $contract->get('created')->value,
        'expires' => $contract->get('expires')->value,
      ];
    }

    return new JsonResponse($data);
  }

}
