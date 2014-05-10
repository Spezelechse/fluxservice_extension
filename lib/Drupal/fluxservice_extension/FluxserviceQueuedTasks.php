<?php
/**
 * 
 */
namespace Drupal\fluxservice_extension;

class FluxserviceQueuedTasks{
	public function getOptions(){
		return array(	
				//callback => label
				'post'=>'create', 
				'put'=>'update', 
				'delete'=>'delete'
				);
	}
	
	public function post($task, $account){
		$controller = entity_get_controller($task->remote_type);
        $remote=$controller->createRemote($task->local_id,
                                          $task->local_type,
                                          $account,
                                          null,
                                          $task->request,
                                          $task->remote_type);
        if(isset($remote)){
        	$res=db_select('rules_trigger','rs')
	            ->fields('rs',array('event'))
	            ->condition('rs.event',$task->remote_type.'_event--%','LIKE')
	            ->execute()
	            ->fetch();

          	rules_invoke_event($res->event, $account, $remote, 'update', $task->local_id);
        }
	}

	public function put($task, $account){
		$entity = entity_load_single($task->local_type, $task->local_id);
        $entity = entity_metadata_wrapper($task->local_type, $entity);
        $entity->save();
	}

	public function delete($task, $account){
        $controller = entity_get_controller($task->remote_type);
        $controller->deleteRemote($task->local_id,
                                  $task->local_type,
                                  $account,
                                  $task->remote_type,
                                  $task->remote_id);
	}
}
?>