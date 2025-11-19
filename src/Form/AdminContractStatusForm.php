<?php

namespace Drupal\solana_contracts\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\solana_contracts\Entity\Contract;

/**
 * Provides a form for updating the contract status.
 */
class AdminContractStatusForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'solana_contracts_admin_contract_status_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Contract $contract = NULL) {
    $form['contract_id'] = [
      '#type' => 'hidden',
      '#value' => $contract->id(),
    ];

    $form['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Status'),
      '#options' => [
        'pending' => 'Pending',
        'signed_a' => 'Signed by A',
        'signed_b' => 'Signed by B',
        'signed_both' => 'Signed by both parties',
        'expired' => 'Expired',
      ],
      '#default_value' => $contract->get('status')->value,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $contract_id = $form_state->getValue('contract_id');
    $status = $form_state->getValue('status');

    $contract = Contract::load($contract_id);
    $contract->set('status', $status);
    $contract->save();
  }

}
