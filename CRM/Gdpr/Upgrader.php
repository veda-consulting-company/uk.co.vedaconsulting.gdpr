<?php
use CRM_Gdpr_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Gdpr_Upgrader extends CRM_Gdpr_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   *
  public function install() {
    $this->executeSqlFile('sql/myinstall.sql');
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   *
  public function postInstall() {
    $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
      'return' => array("id"),
      'name' => "customFieldCreatedViaManagedHook",
    ));
    civicrm_api3('Setting', 'create', array(
      'myWeirdFieldSetting' => array('id' => $customFieldId, 'weirdness' => 1),
    ));
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled.
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled.
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Perform upgrade to version 1.1
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1100() {
    $this->log('Applying update 1100');
    // Create 'Contacts without any activity for a period' custom search by API
    civicrm_api3('CustomSearch', 'create', array(
      'sequential' => 1,
      'option_group_id' => "custom_search",
      'name' => "CRM_Gdpr_Form_Search_ActivityContact",
      'is_active' => 1,
      'label' => "CRM_Gdpr_Form_Search_ActivityContact",
      'description' => "Contacts without any activity for a period",
    ));
    return TRUE;
  }

  private function log($message) {
    if (is_object($this->ctx) && method_exists($this->ctx, 'info')) {
      $this->ctx->log->info($message);
    }
  }
}
