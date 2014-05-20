<?php

/**
 * @file
 * Contains deleteRemoteEntity.
 */

namespace Drupal\fluxservice_extension\Plugin\Rules\Action;

use Drupal\fluxservice_extension\Rules\FluxRulesPluginHandlerBaseExtended;

/**
 * delete remote entities.
 */
class deleteRemoteEntity extends FluxRulesPluginHandlerBaseExtended implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {

    return static::getInfoDefaults() + array(
      'name' => 'fluxservice_delete_remote_entity',
      'label' => t('Delete remote entity'),
      'parameter' => static::getServiceParameterInfo()+array(
        'local_entity' => array(
          'type' => 'entity',
          'label' => t('Local: Entity'),
          'required' => TRUE,
          'wrapped' => TRUE,
        ),
      ),
      'callbacks' => static::getServiceCallbacks()+array(
      ),
    );
  }

  /**
   * Executes the action.
   */
  public function execute($bundle, $account, $local_entity) {
//    dpm('delete remote');

    $local_type="";
    $local_id=0;
    
    $local_type=$local_entity->type();
    $local_id=$local_entity->getIdentifier();

    if(empty($local_id)){
      $local_id=$local_entity->nid->value();
    }

    $res=db_select($bundle,'fm')
            ->fields('fm',array('remote_id','remote_type'))
            ->condition('fm.id',$local_id,'=')
            ->condition('fm.type',$local_type,'=')
            ->execute()
            ->fetchAssoc();

    if($res){
      $controller = entity_get_controller($res['remote_type']);
    
      $controller->deleteRemote($local_id, $local_type, $account, $res['remote_type'], $res['remote_id']);
    }
  }
}
