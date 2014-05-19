<?php

/**
 * @file
 * Contains createRemoteEntity.
 */

namespace Drupal\fluxservice_extension\Plugin\Rules\Action;

use Drupal\fluxservice_extension\Rules\FluxRulesPluginHandlerBaseExtended;

/**
 * create remote entities.
 */
class createRemoteEntity extends FluxRulesPluginHandlerBaseExtended implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {

    return static::getInfoDefaults() + array(
      'name' => 'fluxservice_create_remote_entity',
      'label' => t('Create remote entity'),
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
        'created_entity' => array(
          'type'=>'entity',
          'label' => t('Created entity')),
      )
    );
  }

  /**
   * Executes the action.
   */
  public function execute($bundle, $account, $remote_entity, $local_entity) {
    dpm('create remote');
    print_r('create remote<br>');
    print_r($remote_entity);
    
    $local_type="";
    $local_id=0;

    $local_type=$local_entity->type();
    $local_id=$local_entity->getIdentifier();

    if(empty($local_id)){
      if(isset($local_entity->id)){
        $local_id=$local_entity->id->value();
      }
      else if(isset($local_entity->nid)){
        $local_id=$local_entity->nid->value();
      }
    }

    $controller = entity_get_controller($remote_entity->entityType());
    $created = $controller->createRemote($local_id, $local_type, $account, $remote_entity);

    return array('created_entity'=>entity_metadata_wrapper($remote_entity->entityType(),$created));
  }
}
