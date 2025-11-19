<?php

namespace Drupal\solana_contracts\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the contract entity edit forms.
 */
class ContractForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $entity->save();
    $form_state->setRedirect('entity.contract.canonical', ['contract' => $entity->id()]);
  }
}
