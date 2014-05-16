<?php

/**
 * @file
 * Contains createLocalEntity.
 */

namespace Drupal\fluxservice_extension\Plugin\Rules\Action;

use Drupal\fluxservice_extension\Rules\FluxRulesPluginHandlerBaseExtended;

/**
 * Create local entiy.
 */
class createLocalEntity extends FluxRulesPluginHandlerBaseExtended implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {

    return static::getInfoDefaults() + array(
      'name' => 'fluxservice_create_local_entity',
      'label' => t('Create local entity'),
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
      'callbacks' => static::getServiceCallbacks()+array(
      ),
    );
  }

  /**
   * Executes the action.
   */
  public function execute($bundle, $account, $remote_entity, $local_entity) {
    print_r('create local<br>');
    dpm('create local');
    
    $local_type="";
    $local_id=0;

    $local_type=$local_entity->type();
    $local_id=$local_entity->getIdentifier();

    if(empty($local_id)){
      $local_id=$local_entity->nid->value();
    }
  
    $controller = entity_get_controller($remote_entity->entityType());
    
    $controller->createLocal($remote_entity, $local_id, $local_type);
  }
}
