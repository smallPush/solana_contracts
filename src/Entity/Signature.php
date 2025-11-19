<?php

namespace Drupal\solana_contracts\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Signature entity.
 *
 * @ingroup solana_contracts
 *
 * @ContentEntityType(
 *   id = "signature",
 *   label = @Translation("Signature"),
 *   base_table = "signature",
 *   handlers = {
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class Signature extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['contract_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Contract'))
      ->setSetting('target_type', 'contract')
      ->setRequired(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE);

    $fields['signed'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Signed'));

    $fields['signature'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Digital Signature'));

    $fields['status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Status'))
      ->setSettings([
        'allowed_values' => [
          'pending' => 'Pending',
          'signed' => 'Signed',
          'rejected' => 'Rejected',
        ],
      ])
      ->setDefaultValue('pending');

    return $fields;
  }
}
