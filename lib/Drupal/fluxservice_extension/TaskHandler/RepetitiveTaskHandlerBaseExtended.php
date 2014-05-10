<?php

/**
 * @file
 * Contains RepetitiveTaskHandlerBaseExtended.
 */

namespace Drupal\fluxservice_extension\TaskHandler;

use Drupal\fluxservice\Rules\TaskHandler\RepetitiveTaskHandlerBase;
use Guzzle\Http\Exception\BadResponseException;
use Drupal\fluxservice_extension\FluxserviceTaskQueue;

/**
 * Base class for remote task handlers that dispatch Rules events.
 */
abstract class RepetitiveTaskHandlerBaseExtended extends RepetitiveTaskHandlerBase {
  protected $needed_types=array();

  public function __construct(array $task) {
    parent::__construct($task);
    
    //extract the entity type from the event type
    $type_split=explode("_",$this->task['identifier']);
    $module=$type_split[0];
    $type=$type_split[1];

    $this->task['module_name']=$module;
    $this->task['entity_type']=$type;
  }
  /**
   * Gets the configured event name to dispatch.
   */
  public function getEvent() {
    return $this->task['identifier'];
  }

  /**
   * 
   */
  public function getEntityType(){
    return $this->task['entity_type'];
  }

  /**
   * 
   */
  public function getModuleName(){
    return $this->task['module_name'];
  }

  /**
   * Gets the configured account.
   *
   * @throws \RulesEvaluationException
   *   If the account cannot be loaded.
   *
   * @return Account
   */
  public function getAccount() {
    $account = entity_load_single('fluxservice_account', $this->task['data']['account']);
    if (!$account) {
      throw new \RulesEvaluationException('The specified service account cannot be loaded.', array(), NULL, \RulesLog::ERROR);
    }
    return $account;
  }

  /**
   * {@inheritdoc}
   */
  public function afterTaskQueued() {
    try {
      $service = $this->getAccount()->getService();

      // Continuously reschedule the task.
      db_update('rules_scheduler')
        ->condition('tid', $this->task['tid'])
        ->fields(array('date' => $this->task['date'] + $service->getPollingInterval()))
        ->execute();
    }
    catch(\RulesEvaluationException $e) {
      rules_log($e->msg, $e->args, $e->severity);
    }
  }

  /**
   *
   */
  public function checkRequirements(){
    $service = $this->getAccount()->getService();

    if(!$service->remoteDependenciesAreUsed()){
      return true;
    }

    if($this->checkDataExists()){
      //check required is handled
      foreach ($this->needed_types as $type) {
        $sched=db_select('rules_scheduler','rs')
                ->fields('rs',array('date'))
                ->condition('rs.identifier',$this->getModuleName().'_'.$type.'%','LIKE')
                ->execute()
                ->fetch();
        if(!$sched){
          watchdog('fluxservice', "Missing taskhandler for ".$type."  (@".$this->getEntityType().")");
          return false;
        }else{
          //check is handled before
        $res=db_select('rules_scheduler','rs')
            ->fields('rs',array('tid'))
            ->condition('rs.date',$sched->date,'>')
            ->condition('rs.identifier',$this->getModuleName().'_'.$this->getEntityType().'%','LIKE')
            ->execute()
            ->fetch();
        if(!$res){
          watchdog('fluxservice', "Notice: Wrong taskhandler order, will be changed now (@".$this->getEntityType().")");
          return false;
        }
        }
      }
      return true;
    }
    return false;
  }

 /**
  * 
  */
  public function checkDataExists(){
    foreach ($this->needed_types as $type) {
      $res=db_select($this->getModuleName(),'fm')
          ->fields('fm')
          ->condition('fm.remote_type',$this->getModuleName().'_'.$type)
          ->execute();

      if($res->rowCount()<=0){
        watchdog('fluxservice', "Missing database entries for ".$type." (@".$this->getEntityType().")");
        return false;
      }
    }
    return true;
  }

  /**
   * 
   */
  public function afterTaskComplete(){
    $service = $this->getAccount()->getService();

    $data=$this->getScheduleData();

    if($data){
      db_update('rules_scheduler')
        ->condition('tid', $this->task['tid'])
        ->fields(array('date' => $data->date + 1 + $service->getPollingInterval()))
        ->execute();
    }
    else{
      db_update('rules_scheduler')
        ->condition('tid', $this->task['tid'])
        ->fields(array('date' => $this->task['date'] + $service->getPollingInterval()))
        ->execute();
    }
  }

 /**
  * 
  */
  public function getScheduleData(){
    $or=db_or();

    foreach ($this->needed_types as $type) {
      $or->condition('rs.identifier',$this->getModuleName().'_'.$type.'%','LIKE');
    }

    $data=db_select('rules_scheduler','rs')
            ->fields('rs',array('date'))
            ->condition($or)
            ->orderBy('rs.date','DESC')
            ->execute()
            ->fetch();

    return $data;
  }

/**
 * invoke events for all given entities
 * 
 * @param string $entity_type
 * A string defining the entity type
 * 
 * @param array $entities
 * An array of arrays defining the entities
 * 
 * @param Account (service account) $account
 * The account used to connect to the restful service
 * 
 * @param string $change_type
 * Event type that happend to the entity (create, delete, update)
 * 
 * @param array $local_entity_ids
 * if needed the local entity ids which refer to the remote entities
 */
  public function invokeEvent($entity_type, $entities, $account, $change_type, $local_entity_ids=array()){
    if(!empty($entities)){
      $entities = fluxservice_entify_multiple($entities, $entity_type, $account);

      $i=0;
      if($entities){
        foreach ($entities as $entity) {
          if(!empty($local_entity_ids)){
            $local_entity_id=$local_entity_ids[$i++];
            rules_invoke_event($this->getEvent(), $account, $entity, $change_type, $local_entity_id);
          }
          else{
            rules_invoke_event($this->getEvent(), $account, $entity, $change_type); 
          }
        }
      }
    }
  }

/**
 * Checks for remote "updates" (create,update,delete) and invoke the appropriate events
 */
  public function checkAndInvoke(){
    $account = $this->getAccount();

    $data_sets=$this->getRemoteDatasets();

    if(!empty($data_sets)){
      //arrays to store the entities which invoke events (something happend)
      $create=array();
      $update=array();
      $update_local_ids=array();
      $delete=array();
      $delete_local_ids=array();

      $last_check=db_select($this->getModuleName(),'fm')
                    ->fields('fm',array('touched_last'))
                    ->condition('fm.remote_type',$this->task['module_name'].'_'.$this->task['entity_type'],'=')
                    ->orderBy('fm.touched_last','DESC')
                    ->execute()
                    ->fetch();
    
      if($last_check){
        $last_check=$last_check->touched_last;
      }
      else{
        $last_check=time();
      }

      foreach ($data_sets as $data_set) {
        if($data_set['name']!="Selbstentwicklungsraum"){
          $this->checkSingleResponseSet($data_set,$create,$update,$update_local_ids);
        }
      }

      //get deleted id's
      $res=db_select($this->task['module_name'],'fm')
              ->fields('fm',array('id','remote_id','touched_last'))
              ->condition('fm.touched_last',$last_check,'<=')
              ->condition('fm.remote_type',$this->task['module_name'].'_'.$this->task['entity_type'],'=')
              ->execute();

      foreach($res as $data){
        //print_r('delete local: '.$data->touched_last.'<br>');
        array_push($delete_local_ids, $data->id);
        array_push($delete, array('id'=>$data->remote_id));
        db_delete($this->task['module_name'])
          ->condition('id',$data->id, '=')
          ->condition('remote_type',$this->task['module_name'].'_'.$this->task['entity_type'],'=')
          ->execute();
      }

      $this->invokeEvent($this->task['module_name'].'_'.$this->task['entity_type'], $create, $account, 'create');
      $this->invokeEvent($this->task['module_name'].'_'.$this->task['entity_type'], $update, $account, 'update', $update_local_ids);
      $this->invokeEvent($this->task['module_name'].'_'.$this->task['entity_type'], $delete, $account, 'delete', $delete_local_ids);
    }     
  }

/**
 * checks which event is needed for the given remote data_set
 */
  private function checkSingleResponseSet($data_set, &$create, &$update, &$update_local_ids){
    $res=db_select($this->task['module_name'],'fm')
          ->fields('fm',array('checksum','id'))
          ->condition('remote_id',$data_set['id'])
          ->execute()
          ->fetchAssoc();

    unset($data_set['dateLastActivity']);
    unset($data_set['dateLastView']);

    $checksum=md5(json_encode($data_set));
    $data_set['checksum']=$checksum;

    if($res){
      //check for updates

      if($res['checksum']!=$checksum){
        array_push($update, $data_set);
        array_push($update_local_ids, $res['id']);
      }

      db_update($this->task['module_name'])
        ->fields(array('touched_last'=>time()))
        ->condition('id',$res['id'],'=')
        ->execute();
    }
    else{
      array_push($create, $data_set);
    }
  }

  protected function init(){
    $board_ids=array();
    $client = $this->getAccount()->client();

    try{
      $board_ids = $client->getMemberBoards(array( 'username'=>$client->getConfig('username'),
                                                    'key'=>$client->getConfig('consumer_key'),
                                                    'token'=>$client->getConfig('token'),
                                                    'fields'=>''));
    }
    catch(BadResponseException $e){
      if($e->getResponse()->getStatusCode()==404){
        watchdog('Fluxservice','[404] Host "'.$client->getBaseUrl().'" not found ('.$operation.')');
      }
      else{ 
          watchdog('Fluxservice @ getMemberBoards', $e->getResponse()->getMessage());
      }
    }


    return $board_ids;
  }

  /**
   * 
   */
  protected function processQueue(){
    $queue=new FluxserviceTaskQueue($this->getEntityType(),$this->getAccount());

    $queue->process();
  }

  /**
   * @brief Get remote datasets
   * @details Gets all remote entries needed to handle this datatype
   * @return array (entry id => array (property name => value))
   */
  abstract protected function getRemoteDatasets();
}