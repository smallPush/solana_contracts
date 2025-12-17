<?php

namespace Drupal\solana_contracts\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Solana Account entity.
 *
 * @ingroup solana_contracts
 *
 * @ContentEntityType(
 *   id = "solana_account",
 *   label = @Translation("Solana Account"),
 *   base_table = "solana_account",
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\solana_contracts\SolanaAccountListBuilder",
 *     "form" = {
 *       "default" = "Drupal\solana_contracts\Form\SolanaAccountForm",
 *       "add" = "Drupal\solana_contracts\Form\SolanaAccountForm",
 *       "edit" = "Drupal\solana_contracts\Form\SolanaAccountForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\solana_contracts\ContractAccessControlHandler",
 *   },
 *   list_cache_tags = { "solana_account_list" },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "address",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/solana_account/{solana_account}",
 *     "add-form" = "/solana_account/add",
 *     "edit-form" = "/solana_account/{solana_account}/edit",
 *     "delete-form" = "/solana_account/{solana_account}/delete",
 *     "collection" = "/solana_account/list",
 *   },
 * )
 */
class SolanaAccount extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['address'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Solana Address'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['private_key'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Private Key'))
      ->setRequired(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'basic_string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'));

    return $fields;
  }

}
