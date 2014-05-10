<?php

/**
 * @file
 * Contains fetchRemoteEntityByLocalEntity.
 */

namespace Drupal\fluxservice_extension\Plugin\Rules\Action;

use Drupal\fluxservice_extension\Rules\RulesPluginHandlerBase;

/**
 * update remote entities.
 */
class fetchRemoteEntityByLocalEntity extends RulesPluginHandlerBase implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {

    return static::getInfoDefaults() + array(
      'name' => 'fluxservice_fetch_remote_entity_by_local_entity',
      'label' => t('Fetch remote entity by local entity'),
      'parameter' => array(
        'local_entity' => array(
          'type' => 'entity',
          'label' => t('Local entity'),
          'required' => TRUE,
          'wrapped' => TRUE,
        ),
        'remote_type' => array(
          'type' => 'text',
          'label' => t('Remote entity type'),
          'options list' => 'rules_entity_action_type_options',
          'description' => t('Specifies the type of the fetched entity.'),
          'restriction' => 'input',
        ),
      ),
      'provides' => array(
        'entity_fetched' => array(
          'type'=>'entity',
          'label' => t('Fetched entity')),
      )
    );
  }

  /**
   * Executes the action.
   */
  public function execute($local_entity, $remote_type) {
    $local_type="";
    $local_id=0;
    
    $local_type=$local_entity->type();
    $local_id=$local_entity->getIdentifier();

    if(empty($local_id)){
      $local_id=$local_entity->nid->value();
    }

    $module_name=explode('_', $remote_type);

    $res=db_select($module_name[0],'fm')
            ->fields('fm',array('remote_id','remote_type'))
            ->condition('fm.id',$local_id,'=')
            ->condition('fm.type',$local_type,'=')
            ->execute()
            ->fetchAssoc();

    if($res){
      if(!$remote_entity=entity_load_single($res['remote_type'], $res['remote_id'])){
        $remote_entity=$local_entity;
        $res['remote_type']=$local_type;
      }

      return array('entity_fetched' => entity_metadata_wrapper($res['remote_type'],$remote_entity));
    }
  }
}
