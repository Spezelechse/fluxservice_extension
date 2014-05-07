<?php

/**
 * @file
 * Contains createRemoteEntity.
 */

namespace Drupal\fluxtrello\Plugin\Rules\Action;

use Drupal\fluxtrello\Plugin\Service\TrelloAccountInterface;
use Drupal\fluxtrello\Rules\RulesPluginHandlerBase;

/**
 * create remote entities.
 */
class createRemoteEntity extends RulesPluginHandlerBase implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {

    return static::getInfoDefaults() + array(
      'name' => 'fluxtrello_create_remote_entity',
      'label' => t('Create remote entity'),
      'parameter' => array(
        'account' => static::getServiceParameterInfo(),
        'remote_entity' => array(
          'type' => 'entity',
          'label' => t('Trello: Entity'),
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
  public function execute(TrelloAccountInterface $account, $remote_entity, $local_entity) {
    dpm('create remote trello');
    print_r('create remote trello<br>');
    
    $local_type="";
    $local_id=0;
    $isNode=1;
    if(method_exists($local_entity, 'entityType')){
      $local_type=$local_entity->entityType();
      $local_id=$local_entity->id;
      $isNode=0;
    }
    else{
      $local_type=$local_entity->type();
      $local_id=$local_entity->getIdentifier();
    }

    $controller = entity_get_controller($remote_entity->entityType());
    
    $created = $controller->createRemote($local_id, $local_type, $isNode, $account, $remote_entity);

    return array('created_entity'=>entity_metadata_wrapper($remote_entity->entityType(),$created));
  }
}
