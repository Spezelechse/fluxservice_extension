<?php

/**
 * @file
 * Contains RemoteEntityExtended.
 */

namespace Drupal\fluxservice_extension\Plugin\Entity;

use Drupal\fluxservice\Entity\RemoteEntity;

/**
 * Abstract class for remote entities.
 */
abtract class RemoteEntityExtended extends RemoteEntity implements RemoteEntityExtendedInterface {

  public function __construct(array $values = array(), $entity_type = NULL) {
    parent::__construct($values, $entity_type);
  }

  /**
   * Gets the entity property definitions.
   */
  public static function getEntityPropertyInfo($entity_type, $entity_info) {
    $info['id'] = array(
      'label' => t('Id'),
      'description' => t("Remote id."),
      'type' => 'text',
      'setter callback' => 'entity_property_verbatim_set',
    );
    return $info;
  }
}