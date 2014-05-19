<?php

/**
 * @file
 * Contains RemoteEntityControllerExtended.
 */

namespace Drupal\fluxservice_extension;

use Drupal\fluxservice\RemoteEntityController;
use Drupal\fluxservice_extension\Entity\RemoteEntityExtendedInterface;
use Guzzle\Http\Exception\BadResponseException;

/**
 * Class RemoteEntityController
 */
abstract class RemoteEntityControllerExtended extends RemoteEntityController {
/**
 * Creates a new database entry at the mapping table
 */
  public function createLocal($remote_entity, $local_entity_id, $local_entity_type){
    $fields=array('id', 'type', 'remote_entity_id', 'remote_type', 'remote_id', 'touched_last', 'checkvalue');
    $values=array($local_entity_id, 
                  $local_entity_type,
                  $remote_entity->id, 
                  $remote_entity->entityType(), 
                  $remote_entity->remote_id,
                  time(),
                  $remote_entity->getCheckValue());

    $this->addAdditionalFields($fields, $values, $remote_entity);

    $module_name=explode('_', $remote_entity->entityType());

    $nid=db_insert($module_name[0])
      ->fields($fields)
      ->values($values)
      ->execute();
  }

  public function createRemote($local_entity_id, $local_entity_type, $account, $remote_entity, $request="", $remote_type=""){
    $client=$account->client();

    if($remote_entity!=null){
      $req=$this->createRequest($client, 'create', $remote_entity);
      $remote_type=$remote_entity->entityType();
    }
    else if($request!=""){
      $req=$request;
    }
    else{
      //TODO: throw error missing argument
    }

    //extract remote object type
    $type=$remote_type;
    $type_split=explode("_",$type);
    $type=$type_split[1];


    //build operation name
    $operation='post'.ucfirst($type);
    
    //try to send the post request
    try{
      $response=$client->$operation($req);
    }
    catch(BadResponseException $e){
      if($e->getResponse()->getStatusCode()==404){
        $this->handle404( '[404] Host "'.$client->getBaseUrl().'" not found ('.$operation.')',
                          array(
                            'callback'=>'post',
                            'task_priority'=>2,
                            'local_id'=>$local_entity_id,
                            'local_type'=>$local_entity_type,
                            'request'=>$req,
                            'remote_type'=>$remote_type),
                            $e->getResponse()->getMessage());
      }
      else{
        watchdog('fluxservice @ '.$operation, $e->getResponse()->getMessage());
      }
    }

    if(isset($response)){
      $response=$this->prepareResponse($response); 

      $remoteEntity = fluxservice_entify($response, $remote_type, $account);

      //create local database entry
      $this->createLocal($remoteEntity, $local_entity_id, $local_entity_type);
      return $remoteEntity;
    }
  }

  public function deleteRemote($local_entity_id, $local_entity_type, $account, $remote_type, $remote_id){
    $client=$account->client();

    $req=$this->createRequest($client, 'delete', null, $remote_id);
    
    $type_split=explode("_",$remote_type);
    $type=$type_split[1];
      
    //build operation name
    $operation='delete'.ucfirst($type);
    $continue=false;

    //try to send delete request
    try{
      $response=$client->$operation($req);
    }
    catch(BadResponseException $e){
      if($e->getResponse()->getStatusCode()==404){
        $continue=$this->handle404( '[404] Host "'.$account->client()->getBaseUrl().'" not found ('.$operation.')',
                          array(
                            'callback'=>'delete',
                            'task_priority'=>0,
                            'local_id'=>$local_entity_id,
                            'local_type'=>$local_entity_type,
                            'remote_type'=>$remote_type,
                            'remote_id'=>$remote_id),
                            $e->getResponse()->getMessage());
      }
      else{
        watchdog('fluxservice @ '.$operation, $e->getResponse()->getMessage());
      }
    }
  }
  /**
   *  Updates the mapping table
   */
  public function updateLocal($remote_entity, $local_entity_id, $local_entity_type){
    $fields=array('checkvalue'=>$remote_entity->getCheckValue());

    $module_name=explode('_', $remote_entity->entityType());

    db_update($module_name[0])
      ->fields($fields)
      ->condition('id', $local_entity_id, '=')
      ->condition('type', $local_entity_type, '=')
      ->execute();
  }


  /**
  *   Sends a put request to update a mite data set and if successful updates the local table
  */
  public function updateRemote($local_entity_id, $local_entity_type, $account, $remote_entity){
    $client=$account->client();

    $req=$this->createRequest($client, 'update', $remote_entity);

    //extract mite type
    $type=$remote_entity->entityType();
    $type_split=explode("_",$type);
    $type=$type_split[1];

    //build operation name
    $operation='put'.ucfirst($type);

    //try to send the update request
    try{
      $response=$client->$operation($req);
    }
    catch(BadResponseException $e){
      if($e->getResponse()->getStatusCode()==404){
        $this->handle404( '[404] Host "'.$account->client()->getBaseUrl().'" not found ('.$operation.')', 
                          array(
                            'callback'=>'put',
                            'task_priority'=>1,
                            'local_id'=>$local_entity_id,
                            'local_type'=>$local_entity_type,
                            'request'=>$req,
                            'remote_type'=>$remote_entity->entityType(),
                            'remote_id'=>$remote_entity->id),
                            $e->getResponse()->getMessage());
      }
      else{
        watchdog('fluxservice @ '.$operation, $e->getResponse()->getMessage());
      }
    }

    //check if successful
    if(isset($response)){
      //get the new updated dataset
      $req=$this->createRequest($client, 'get', null, $remote_entity->remote_id);
      $operation='get'.ucfirst($type);
      $response=$this->prepareResponse($client->$operation($req));

      $remoteEntity = fluxservice_entify($response, $remote_entity->entityType(), $account);

      //update local database entry
      $this->updateLocal($remoteEntity, $local_entity_id, $local_entity_type);
      return $remoteEntity;
    }
  }
  /**
    * 
    */

  abstract public function createRequest($client, $operation_type, $remote_entity=null, $remote_id=0);

  /**
   * 
   */
  abstract public function handle404($log_message, $data=array(), $response_message="");

  /**
   * 
   */
  abstract public function extractRemoteType($entity_type);

  /**
   * 
   */
  abstract public function addAdditionalFields(&$fields, &$values, $remote_entity);

  /**
   * 
   */
  abstract public function prepareResponse($response);
}
