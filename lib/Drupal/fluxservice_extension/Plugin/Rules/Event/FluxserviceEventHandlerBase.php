<?php

/**
 * @file
 * Contains FluxserviceEventHandlerBase.
 */

namespace Drupal\fluxservice_extension\Plugin\Rules\Event;

use Drupal\fluxservice\Rules\DataUI\AccountEntity;
use Drupal\fluxservice\Rules\DataUI\ServiceEntity;
use Drupal\fluxservice\Rules\EventHandler\CronEventHandlerBase;
use Drupal\fluxservice\Rules\RulesPluginHandlerBase;

/**
 * Cron-based base class for Fluxservice event handlers.
 */
abstract class FluxserviceEventHandlerBase extends CronEventHandlerBase {

  /**
   * Returns info-defaults for service plugin handlers.
   */
  public static function getInfoDefaults() {
    return RulesPluginHandlerBase::getInfoDefaults();
  }

  /**
   * Rules service integration access callback.
   */
  public static function integrationAccess($type, $name) {
    return fluxservice_access_by_plugin('fluxservice');
  }

  /**
   * Returns info for the provided service service account variable.
   */
  public static function getServiceVariableInfo() {
    return array(
      'type' => 'fluxservice_account',
      'bundle' => 'fluxservice',
      'label' => t('Service account'),
      'description' => t('The account used for authenticating with the Fluxservice API.'),
    );
  }
 
  /**
   * {@inheritdoc}
   */
  public function getDefaults() {
    return array(
      'account' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form_state) {
    $settings = $this->getSettings();

    $form['account'] = array(
      '#type' => 'select',
      '#title' => t('Account'),
      '#description' => t('The service account used for authenticating with the Fluxservice API.'),
      '#options' => AccountEntity::getOptions('fluxservice', $form_state['rules_config']),
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
