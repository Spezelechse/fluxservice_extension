<?php

/**
 * @file
 * Contains createRemoteEntity.
 */

namespace Drupal\fluxservice_extension\Plugin\Rules\Action;

use Drupal\fluxservice_extension\Rules\RulesPluginHandlerBase;

/**
 * create remote entities.
 */
class createRemoteEntity extends RulesPluginHandlerBase implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {

    return static::getInfoDefaults() + array(
      'name' => 'fluxservice_create_remote_entity',
      'label' => t('Create remote entity'),
      'parameter' => array(
        'account' => static::getServiceParameterInfo(),
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
  public function execute($account, $remote_entity, $local_entity) {
    dpm('create remote service');
    print_r('create remote service<br>');
    
    $local_type="";
    $local_id=0;

    $local_type=$local_entity->type();
    $local_id=$local_entity->getIdentifier();

    if(empty($local_id)){
      $local_id=$local_entity->nid->value();
    }

    $controller = entity_get_controller($remote_entity->entityType());
    
    $created = $controller->createRemote($local_id, $local_type, $account, $remote_entity);

    return array('created_entity'=>entity_metadata_wrapper($remote_entity->entityType(),$created));
  }
}
