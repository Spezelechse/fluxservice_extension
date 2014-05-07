<?php

/**
 * @file
 * Contains fetchRemoteEntityByLocalEntity.
 */

namespace Drupal\fluxtrello\Plugin\Rules\Action;

use Drupal\fluxtrello\Plugin\Service\TrelloAccountInterface;
use Drupal\fluxtrello\Plugin\Entity\TrelloCustomer;
use Drupal\fluxtrello\Rules\RulesPluginHandlerBase;

/**
 * update remote entities.
 */
class fetchRemoteEntityByLocalEntity extends RulesPluginHandlerBase implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {

    return static::getInfoDefaults() + array(
      'name' => 'fluxtrello_fetch_remote_entity_by_local_entity',
      'label' => t('Fetch remote entity by local entity'),
      'parameter' => array(
        'local_entity' => array(
          'type' => 'entity',
          'label' => t('Local entity'),
          'required' => TRUE,
          'wrapped' => TRUE,
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
  public function execute($local_entity) {
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

    $res=db_select('fluxtrello','fm')
            ->fields('fm',array('remote_id','remote_type'))
            ->condition('fm.id',$local_id,'=')
            ->condition('fm.type',$local_type,'=')
            ->condition('fm.isNode',$isNode)
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
