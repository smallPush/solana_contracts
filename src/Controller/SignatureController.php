<?php

namespace Drupal\solana_contracts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\solana_contracts\Entity\Contract;
use Drupal\solana_contracts\Entity\Signature;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for handling signature submissions.
 */
class SignatureController extends ControllerBase {

  /**
   * Saves a signature.
   */
  public function save(Request $request, Contract $contract) {
    $data = json_decode($request->getContent(), TRUE);
    $signature = $data['signature'];

    $user = $this->currentUser();

    $signature_entity = Signature::create([
      'contract_id' => $contract->id(),
      'user_id' => $user->id(),
      'signature' => $signature,
      'status' => 'signed',
    ]);
    $signature_entity->save();

    // Update the contract status.
    $current_status = $contract->get('contract_status')->value;
    $party_a_id = $contract->get('party_a')->target_id;
    $party_b_id = $contract->get('party_b')->target_id;
    $user_id = $user->id();

    if (($current_status === 'signed_b' && $user_id == $party_a_id) || ($current_status === 'signed_a' && $user_id == $party_b_id)) {
      $contract->set('contract_status', 'signed_both');
    }
    elseif ($user_id == $party_a_id) {
      $contract->set('contract_status', 'signed_a');
    }
    elseif ($user_id == $party_b_id) {
      $contract->set('contract_status', 'signed_b');
    }
    $contract->save();

    return new JsonResponse(['status' => 'ok']);
  }

  /**
   * Rejects a signature.
   */
  public function reject(Request $request, Contract $contract) {
    $user = $this->currentUser();

    $signature_entity = Signature::create([
      'contract_id' => $contract->id(),
      'user_id' => $user->id(),
      'status' => 'rejected',
    ]);
    $signature_entity->save();

    return new JsonResponse(['status' => 'ok']);
  }
}
