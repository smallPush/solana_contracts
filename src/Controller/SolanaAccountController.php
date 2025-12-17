<?php

namespace Drupal\solana_contracts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\solana_contracts\Entity\SolanaAccount;

/**
 * Controller for Solana Account actions.
 */
class SolanaAccountController extends ControllerBase {

  /**
   * Savings the generated keys.
   */
  public function saveKeys(Request $request) {
    $content = json_decode($request->getContent(), TRUE);
    $publicKey = $content['publicKey'] ?? '';
    $secretKey = $content['secretKey'] ?? '';

    if (empty($publicKey) || empty($secretKey)) {
      return new JsonResponse(['message' => 'Missing keys'], 400);
    }

    $currentUser = $this->currentUser();
    if ($currentUser->isAnonymous()) {
      return new JsonResponse(['message' => 'User must be logged in'], 403);
    }

    // Check if user already has an account.
    $storage = $this->entityTypeManager()->getStorage('solana_account');
    $query = $storage->getQuery()
      ->condition('user_id', $currentUser->id())
      ->accessCheck(FALSE);
    $ids = $query->execute();

    if (!empty($ids)) {
       // Ideally we might update, but for now lets return existing.
       // Or update if they want to overwrite? The plan implies creating if missing.
       // A user should have only one account for now in this context.
       $account = $storage->load(reset($ids));
       if ($account->get('private_key')->value) {
           return new JsonResponse(['message' => 'Account already exists with keys'], 200);
       }
       // If exists but no private key (maybe created manually without one?), update it.
       $account->set('address', $publicKey);
       $account->set('private_key', $secretKey);
       $account->save();
       return new JsonResponse(['message' => 'Keys updated successfully'], 200);
    }

    // Create new account.
    $account = SolanaAccount::create([
      'user_id' => $currentUser->id(),
      'address' => $publicKey,
      'private_key' => $secretKey,
    ]);
    $account->save();

    return new JsonResponse(['message' => 'Keys saved successfully'], 201);
  }

}
