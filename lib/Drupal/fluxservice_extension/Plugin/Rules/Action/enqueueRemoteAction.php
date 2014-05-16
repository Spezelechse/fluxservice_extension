<?php

/**
 * @file
 * Contains enqueueRemoteAction.
 */

namespace Drupal\fluxservice_extension\Plugin\Rules\Action;

use Drupal\fluxservice_extension\Rules\FluxRulesPluginHandlerBaseExtended;
use Drupal\fluxservice_extension\FluxserviceTaskQueue;

/**
 * enqueue remote action.
 */
class enqueueRemoteAction extends FluxRulesPluginHandlerBaseExtended implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {
    return static::getInfoDefaults() + array(
      'name' => 'fluxservice_enqueue_remote_action',
      'label' => t('Enqueue remote action'),
      'parameter' => array(
        'local_entity' => array(
          'type' => 'entity',
          'label' => t('Local: Entity'),
          'wrapped' => TRUE,
          'required' => TRUE,
        ),
        'remote_type' => array(
          'type' => 'text',
          'label' => t('Remote entity type'),
          'options list' => 'rules_entity_action_type_options',
          'description' => t('Specifies the type of remote entity that was part of the action.'),
          'restriction' => 'input',
        ),
        'task_type' => array(
          'type' => 'text',
          'options list' => 'task_type_get_options',
          'label' => t('Task type'),
          'restriction' => 'input',
          'required' => TRUE,
        ),
        'task_priority' => array(
          'type' => 'text',
          'options list' => 'task_priority_get_options',
          'label' => t('Task priority'),
          'description' => t('standard: create=2, update=1, delete=0; Queue is ordered descending by priority.'),
          'restriction' => 'input',
          'required' => TRUE,
        ),
      )
    );
  }

  /**
   * Executes the action.
   */
  public function execute($local_entity, $remote_type, $task_type, $task_priority) {
    $local_type="";
    $local_id=0;
    
    $local_type=$local_entity->type();
    $local_id=$local_entity->getIdentifier();

    if(empty($local_id)){
      $local_id=$local_entity->nid->value();
    }

    FluxserviceTaskQueue::addTask(array(  'callback'=>$task_type,
                                          'task_priority'=>$task_priority,
                                          'local_id'=>$local_id,
                                          'local_type'=>$local_type,
                                          'remote_type'=>$remote_type));
  }
}
