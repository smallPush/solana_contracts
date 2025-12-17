<?php

namespace Drupal\solana_contracts\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Solana Contracts settings for this site.
 */
class SolanaSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'solana_contracts_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['solana_contracts.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['solana_network'] = [
      '#type' => 'select',
      '#title' => $this->t('Solana Network'),
      '#description' => $this->t('Select the Solana network environment.'),
      '#options' => [
        'https://api.mainnet-beta.solana.com' => $this->t('Mainnet Beta'),
        'https://api.devnet.solana.com' => $this->t('Devnet'),
      ],
      '#default_value' => $this->config('solana_contracts.settings')->get('solana_network') ?? 'https://api.devnet.solana.com',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('solana_contracts.settings')
      ->set('solana_network', $form_state->getValue('solana_network'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
