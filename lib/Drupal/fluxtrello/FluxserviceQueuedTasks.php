<?php
/**
 * 
 */
namespace Drupal\fluxservice;

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
	}

	public function put($task, $account){
	}

	public function delete($task, $account){
	}
}
?>