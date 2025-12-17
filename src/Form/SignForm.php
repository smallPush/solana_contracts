<?php

namespace Drupal\solana_contracts\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\solana_contracts\Entity\Contract;

/**
 * Provides a form for signing a contract.
 */
class SignForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'solana_contracts_sign_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Contract $contract = NULL) {
    $form['#attached']['library'][] = 'solana_contracts/solana-web3';
    $form['#attached']['drupalSettings']['solana_contracts']['contract_hash'] = $contract->get('hash')->value;
    $form['#attached']['drupalSettings']['solana_contracts']['contract_id'] = $contract->id();

    // Check if user has a Solana account.
    $user = $this->currentUser();
    if ($user->isAuthenticated()) {
      $storage = \Drupal::entityTypeManager()->getStorage('solana_account');
      $query = $storage->getQuery()
        ->condition('user_id', $user->id())
        ->accessCheck(FALSE);
      $ids = $query->execute();

      if (!empty($ids)) {
        $account = $storage->load(reset($ids));
        $form['#attached']['drupalSettings']['solana_contracts']['solana_account'] = [
            'public_key' => $account->get('address')->value,
            'private_key' => $account->get('private_key')->value,
        ];
      }
    }

    $form['actions']['connect'] = [
      '#type' => 'button',
      '#value' => $this->t('Connect Wallet'),
      '#attributes' => [
        'id' => 'solana-connect',
      ],
    ];

    $form['actions']['sign'] = [
      '#type' => 'button',
      '#value' => $this->t('Sign Contract'),
      '#attributes' => [
        'id' => 'solana-sign',
      ],
    ];

    $form['actions']['reject'] = [
      '#type' => 'button',
      '#value' => $this->t('Reject Contract'),
      '#attributes' => [
        'id' => 'solana-reject',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // This form is handled via JavaScript.
  }

}
