<?php

namespace Drupal\solana_contracts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\solana_contracts\Entity\SolanaContract;
use Drupal\solana_contracts\Entity\SolanaSignature;

/**
 * Controller for handling Solana API requests.
 */
class SolanaApiController extends ControllerBase {

  /**
   * Endpoint to register a signature.
   * Route: /api/solana/sign (Must be defined in routing.yml).
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * The current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   * The JSON response.
   */
  public function registerSignature(Request $request) {
    // Parse JSON body.
    $content = json_decode($request->getContent(), TRUE);

    // Basic validation.
    if (empty($content['contract_id']) || empty($content['signature'])) {
      return new JsonResponse(['error' => 'Missing parameters'], 400);
    }

    $contract_id = $content['contract_id'];
    $signature_hash = $content['signature'];
    $wallet_address = $content['wallet_address'] ?? 'unknown';

    $user = \Drupal::currentUser();

    // Security check: User must be logged in.
    if ($user->isAnonymous()) {
      return new JsonResponse(['error' => 'User must be logged in'], 403);
    }

    // Load the contract entity.
    $contract = SolanaContract::load($contract_id);
    if (!$contract) {
      return new JsonResponse(['error' => 'Contract not found'], 404);
    }

    // Determine if the current user is Party A or Party B.
    $user_id = $user->id();
    $is_party_a = ($contract->get('party_a')->target_id == $user_id);
    $is_party_b = ($contract->get('party_b')->target_id == $user_id);

    if (!$is_party_a && !$is_party_b) {
      return new JsonResponse(['error' => 'You are not a party to this contract'], 403);
    }

    // Check for expiration.
    $expiration = $contract->get('expiration_date')->value;
    if ($expiration && time() > $expiration) {
        $contract->set('status', 'expired');
        $contract->save();
        return new JsonResponse(['error' => 'Contract has expired'], 403);
    }

    // Create the Signature Entity log.
    try {
      $signature_entity = SolanaSignature::create([
        'contract_id' => $contract_id,
        'user_id' => $user_id,
        'digital_signature' => $signature_hash,
        'status' => 'signed',
      ]);
      $signature_entity->save();
    } catch (\Exception $e) {
      return new JsonResponse(['error' => 'Failed to save signature: ' . $e->getMessage()], 500);
    }

    // Update Contract Status logic.
    $current_status = $contract->get('status')->value;
    $new_status = $current_status;

    if ($is_party_a) {
      // If Party B already signed, now both have signed.
      if ($current_status === 'signed_b') {
        $new_status = 'signed_both';
      } elseif ($current_status === 'pending') {
        $new_status = 'signed_a';
      }
    } elseif ($is_party_b) {
      // If Party A already signed, now both have signed.
      if ($current_status === 'signed_a') {
        $new_status = 'signed_both';
      } elseif ($current_status === 'pending') {
        $new_status = 'signed_b';
      }
    }

    // Update the contract entity.
    $contract->set('status', $new_status);
    // Store the latest signature hash on the contract for quick reference.
    $contract->set('solana_hash', $signature_hash);
    $contract->save();

    return new JsonResponse([
      'message' => 'Signature registered successfully',
      'new_status' => $new_status,
      'tx' => $signature_hash
    ], 200);
  }

}