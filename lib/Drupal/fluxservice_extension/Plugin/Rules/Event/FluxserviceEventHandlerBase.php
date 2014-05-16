<?php

/**
 * @file
 * Contains FluxserviceEventHandlerBase.
 */

namespace Drupal\fluxservice_extension\Plugin\Rules\Event;

use Drupal\fluxservice\Rules\DataUI\AccountEntity;
use Drupal\fluxservice\Rules\DataUI\ServiceEntity;
use Drupal\fluxservice\Rules\EventHandler\CronEventHandlerBase;

/**
 * Cron-based base class for Fluxservice event handlers.
 */
abstract class FluxserviceEventHandlerBase extends CronEventHandlerBase {

  /**
   * Returns info for the provided service account variable.
   */
  public static function getServiceVariableInfo() {
    return array(
      'bundle' => array(
        'type' => 'text',
        'label' => 'Service account type',
        'restriction' => 'input',
      ),
      'account' => array(
        'type' => 'fluxservice_account',
        'label' => t('Service account'),
        'description' => t('The fluxservice account which this shall be executed.'),
      ),
    );
  }
 
  /**
   * {@inheritdoc}
   */
  public function getDefaults() {
    return array(
      'account' => '',
      'bundle' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form_state) {
    $settings = $this->getSettings();
    $account_types = array_keys(fluxservice_extension_service_account_get_options());

    $form['bundle'] = array(
      '#type' => 'select',
      '#title' => t('Account type'),
      '#description' => t('The type used to choose which accounts are used.'),
      '#options' => fluxservice_extension_service_account_get_options(),
      '#default_value' => $account_types[0],
      '#ajax' => rules_ui_form_default_ajax(),
      '#required' => TRUE,
    );

    if($form_state['triggering_element']['#name']=='bundle'){
      $bundle=$form_state['triggering_element']['#value'];
      $form_state['storage']['bundle']=$bundle;
    }
    else if(isset($form_state['storage']['bundle'])){
      $bundle=$form_state['storage']['bundle'];
    }
    else{
      $bundle=$account_types[0];
    }

    $form['account'] = array(
      '#type' => 'select',
      '#title' => t('Account'),
      '#description' => t('The service account used for authenticating with the REST API.'),
      '#options' => AccountEntity::getOptions($bundle, $form_state['rules_config']),
      '#default_value' => $settings['account'],
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getEventNameSuffix() {
    return drupal_hash_base64(serialize($this->getSettings()));
  }
}
