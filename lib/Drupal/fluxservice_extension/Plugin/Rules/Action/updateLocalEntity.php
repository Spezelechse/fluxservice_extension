<?php

/**
 * @file
 * Contains updateLocalEntity.
 */

namespace Drupal\fluxservice_extension\Plugin\Rules\Action;

use Drupal\fluxservice_extension\Rules\FluxRulesPluginHandlerBaseExtended;

/**
 * Send a customer action.
 */
class updateLocalEntity extends FluxRulesPluginHandlerBaseExtended implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {

    return static::getInfoDefaults() + array(
      'name' => 'fluxservice_update_local_entity',
      'label' => t('Update local entity'),
      'parameter' => array(
        'remote_entity' => array(
          'type' => 'entity',
          'label' => t('Remote: Entity'),
          'wrapped' => FALSE,
          'required' => TRUE,
        ),
        'local_entity' => array(
          'type' => 'entity',
          'label' => t('Local: Entity'),
          'wrapped' => TRUE,
          'required' => TRUE,
        ),
      ),
    );
  }

  /**
   * Executes the action.
   */
  public function execute($account, $remote_entity, $local_entity) {
    print_r("update local service<br>");
    dpm("update local service");

    $local_type="";
    $local_id=0;
    
    $local_type=$local_entity->type();
    $local_id=$local_entity->getIdentifier();

    if(empty($local_id)){
      $local_id=$local_entity->nid->value();
    }

    $controller = entity_get_controller($remote_entity->entityType());
    
    $controller->updateLocal($remote_entity, $local_id, $local_type);
  }
}
