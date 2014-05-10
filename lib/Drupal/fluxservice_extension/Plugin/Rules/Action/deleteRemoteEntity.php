<?php

/**
 * @file
 * Contains deleteRemoteEntity.
 */

namespace Drupal\fluxservice_extension\Plugin\Rules\Action;

use Drupal\fluxservice_extension\Rules\RulesPluginHandlerBase;

/**
 * delete remote entities.
 */
class deleteRemoteEntity extends RulesPluginHandlerBase implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {

    return static::getInfoDefaults() + array(
      'name' => 'fluxservice_delete_remote_entity',
      'label' => t('Delete remote entity'),
      'parameter' => array(
        'account' => static::getServiceParameterInfo(),
        'local_entity' => array(
          'type' => 'entity',
          'label' => t('Local: Entity'),
          'required' => TRUE,
          'wrapped' => TRUE,
        ),
      ),
    );
  }

  /**
   * Executes the action.
   */
  public function execute($account, $local_entity) {
    dpm('delete remote service');
    print_r('delete remote service<br>');

    $local_type="";
    $local_id=0;
    
    $local_type=$local_entity->type();
    $local_id=$local_entity->getIdentifier();

    if(empty($local_id)){
      $local_id=$local_entity->nid->value();
    }

    $res=db_select('fluxservice','fm')
            ->fields('fm',array('service_id','remote_type'))
            ->condition('fm.id',$local_id,'=')
            ->condition('fm.type',$local_type,'=')
            ->execute()
            ->fetchAssoc();

    if($res){
      $controller = entity_get_controller($res['remote_type']);
    
      $controller->deleteRemote($local_id, $local_type, $account, $res['remote_type'], $res['service_id']);
    }
  }
}
