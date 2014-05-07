<?php

/**
 * @file
 * Contains RulesPluginHandlerBase.
 */

namespace Drupal\fluxservice\Rules;

use Drupal\fluxservice\Rules\FluxRulesPluginHandlerBase;

/**
 * Base class for trello Rules plugin handler.
 */
abstract class RulesPluginHandlerBase extends FluxRulesPluginHandlerBase {

  /**
   * Returns info-defaults for trello plugin handlers.
   */
  public static function getInfoDefaults() {
    return array(
      'category' => 'fluxservice',
      'access callback' => array(get_called_class(), 'integrationAccess'),
    );
  }

  /**
   * Rules trello integration access callback.
   */
  public static function integrationAccess($type, $name) {
    return fluxservice_access_by_plugin('fluxservice');
  }

  /**
   * Returns info suiting for trello service account parameters.
   */
  public static function getServiceParameterInfo() {
    return array(
      'type' => 'fluxservice_account',
      'bundle' => 'fluxservice',
      'label' => t('Fluxservice account'),
      'description' => t('The fluxservice account which this shall be executed.'),
    );
  }

}
