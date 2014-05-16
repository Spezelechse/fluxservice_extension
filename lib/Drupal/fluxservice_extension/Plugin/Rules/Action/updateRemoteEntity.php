<?php

/**
 * @file
 * Contains updateRemoteEntity.
 */

namespace Drupal\fluxservice_extension\Plugin\Rules\Action;

use Drupal\fluxservice_extension\Rules\FluxRulesPluginHandlerBaseExtended;

/**
 * update remote entities.
 */
class updateRemoteEntity extends FluxRulesPluginHandlerBaseExtended implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {

    return static::getInfoDefaults() + array(
      'name' => 'fluxservice_update_remote_entity',
      'label' => t('Update remote entity'),
      'parameter' => static::getServiceParameterInfo()+array(
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
      'provides' => array(
        'updated_entity' => array(
          'type'=>'entity',
          'label' => t('Updated entity')),
      )
    );
  }

  /**
   * Executes the action.
   */
  public function execute($account, $remote_entity, $local_entity) {
    dpm('update remote service');
    print_r('update remote service<br>');

    $local_type="";
    $local_id=0;
    
    $local_type=$local_entity->type();
    $local_id=$local_entity->getIdentifier();

    if(empty($local_id)){
      $local_id=$local_entity->nid->value();
    }

    $controller = entity_get_controller($remote_entity->entityType());
    
    $updated = $controller->updateRemote($local_id, $local_type, $account, $remote_entity);

    return array('updated_entity'=>entity_metadata_wrapper($remote_entity->entityType(),$updated));
  }
}
