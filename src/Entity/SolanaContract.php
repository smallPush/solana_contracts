<?php

namespace Drupal\solana_contracts\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Solana Contract entity.
 *
 * @ContentEntityType(
 * id = "solana_contract",
 * label = @Translation("Solana Contract"),
 * handlers = {
 * "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 * "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 * "views_data" = "Drupaliews\EntityViewsData",
 * "form" = {
 * "default" = "Drupal\Core\Entity\ContentEntityForm",
 * "add" = "Drupal\Core\Entity\ContentEntityForm",
 * "edit" = "Drupal\Core\Entity\ContentEntityForm",
 * "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 * },
 * "route_provider" = {
 * "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 * },
 * },
 * base_table = "solana_contract",
 * admin_permission = "administer solana contracts",
 * entity_keys = {
 * "id" = "id",
 * "label" = "title",
 * "uuid" = "uuid",
 * "status" = "status",
 * },
 * links = {
 * "canonical" = "/solana_contract/{solana_contract}",
 * "add-form" = "/admin/structure/solana_contract/add",
 * "edit-form" = "/solana_contract/{solana_contract}/edit",
 * "delete-form" = "/solana_contract/{solana_contract}/delete",
 * "collection" = "/admin/structure/solana_contract",
 * },
 * )
 */
class SolanaContract extends ContentEntityBase {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Title of the contract.
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Contract Title'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
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

    // Detailed description.
    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'text_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Party A: The creator or first signer (User reference).
    $fields['party_a'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Party A'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback(static::class . '::getCurrentUserId')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Party B: The counterparty (User reference).
    $fields['party_b'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Party B'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Contract Status.
    $fields['status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Contract Status'))
      ->setSettings([
        'allowed_values' => [
          'pending' => t('Pending'),
          'signed_a' => t('Signed by Party A'),
          'signed_b' => t('Signed by Party B'),
          'signed_both' => t('Signed by Both Parties'),
          'expired' => t('Expired'),
        ],
      ])
      ->setDefaultValue('pending')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'list_default',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Solana Transaction Hash (stores the final proof).
    $fields['solana_hash'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Solana Tx Hash'))
      ->setDescription(t('The transaction ID or signature hash on the Solana network.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Expiration Date.
    $fields['expiration_date'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Expiration Date'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'datetime_default',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Standard created field.
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    // Standard changed field.
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * Default value callback for Party A.
   * @return array
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

}