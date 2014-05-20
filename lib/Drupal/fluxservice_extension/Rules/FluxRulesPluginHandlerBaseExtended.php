<?php

/**
 * @file
 * Contains RulesPluginHandlerBaseExtended.
 */

namespace Drupal\fluxservice_extension\Rules;

use Drupal\fluxservice\Rules\FluxRulesPluginHandlerBase;

/**
 * Base class for trello Rules plugin handler.
 */
abstract class FluxRulesPluginHandlerBaseExtended extends FluxRulesPluginHandlerBase {

  /**
   * Returns info-defaults for trello plugin handlers.
   */
  public static function getInfoDefaults() {
    return array(
      'category' => 'fluxservice_extension',
      'access callback' => array(get_called_class(), 'integrationAccess'),
    );
  }

  /**
   * Returns info suiting for trello service account parameters.
   */
  public static function getServiceParameterInfo($bundle='') {
    $param = array();

    if($bundle==''){
      $account_types=fluxservice_extension_service_account_get_options();
      $bundle=array_keys($account_types);
      
      if(isset($bundle[0])){
        $bundle=$bundle[0];
      }
      else{
        $bundle='fluxservice';
      }

      $param['bundle'] = array(
              'type' => 'text',
              'label' => 'Service account type',
              'options list' => 'fluxservice_extension_service_account_get_options',
              'restriction' => 'input',
            );
    }

    $param['account'] = array(
            'type' => 'fluxservice_account',
            'bundle' => $bundle,
            'label' => t('Service account'),
            'description' => t('The fluxservice account which this shall be executed.'),
          );

    return $param;
  }

  /**
   * 
   */
  public static function getServiceCallbacks(){
    return array(
            'form_alter' => 'rules_action_set_account_type_form_alter',
          );
  }
}
