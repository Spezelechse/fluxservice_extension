<?php

/**
 * @file
 * Contains fetchReferenceByTrelloId.
 */

namespace Drupal\fluxtrello\Plugin\Rules\Action;

use Drupal\fluxtrello\Plugin\Service\TrelloAccountInterface;
use Drupal\fluxtrello\Rules\RulesPluginHandlerBase;

/**
 * fetch reference by trello id.
 */
class fetchReferenceByTrelloId extends RulesPluginHandlerBase implements \RulesActionHandlerInterface {

  /**
   * Defines the action.
   */
  public static function getInfo() {

    return static::getInfoDefaults() + array(
      'name' => 'fluxtrello_fetch_reference_by_trello_id',
      'label' => t('Fetch reference by trello id'),
      'parameter' => array(
        'trello_id' => array(
          'type' => 'text',
          'label' => t('Trello id'),
          'required' => TRUE,
        ),
        'local_type' => array(
          'type' => 'text',
          'label' => t('Local entity type'),
          'options list' => 'rules_entity_action_type_options',
          'description' => t('Specifies the type of referenced entity.'),
          'restriction' => 'input',
        ),
        'remote_entity' => array(
          'type' => 'entity',
          'label' => t('Remote entity'),
          'required' => TRUE,
          'wrapped' => FALSE,
        ),
      ),
      'provides' => array(
        'reference' => array(
          'type'=>'entity',
          'label' => t('Fetched reference')),
      )
    );
  }

  /**
   * Executes the action.
   */
  public function execute($trello_id, $local_type,$remote_entity) {
    print_r("<br>fetch reference: ".$trello_id."<br>");
    $res=db_select('fluxtrello','fm')
          ->fields('fm',array('id','type','remote_type','trello_id'))
          ->condition('fm.trello_id',$trello_id,'=')
          ->condition('fm.type',$local_type,'=')
          ->execute()
          ->fetchAssoc();
    
    if($res){
      $reference=entity_load_single($res['type'], $res['id']);  
    }
    else{
      $res['type']=$remote_entity->entityType();

      $reference=$remote_entity;
    }

    return array('reference' => entity_metadata_wrapper($res['type'],$reference));
  }
}
