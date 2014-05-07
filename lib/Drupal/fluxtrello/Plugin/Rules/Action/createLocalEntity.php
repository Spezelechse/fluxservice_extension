<?php

/**
 * @file
 * Contains createLocalEntity.
 */

namespace Drupal\fluxtrello\Plugin\Rules\Action;

use Drupal\fluxtrello\Plugin\Service\TrelloAccountInterface;
use Drupal\fluxtrello\Rules\RulesPluginHandlerBase;

/**
 * Create local entiy.
 */
class createLocalEntity extends RulesPluginHandlerBase implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {

    return static::getInfoDefaults() + array(
      'name' => 'fluxtrello_create_local_entity',
      'label' => t('Create local entity'),
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
    );
  }

  /**
   * Executes the action.
   */
  public function execute(TrelloAccountInterface $account, $remote_entity, $local_entity) {
    print_r('create local<br>');
    dpm('create local');
    
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

      if(empty($local_id)){
        $local_id=$local_entity->nid->value();
      }
    }

    $controller = entity_get_controller($remote_entity->entityType());
    
    $controller->createLocal($remote_entity, $local_id, $local_type, $isNode);
  }
}
