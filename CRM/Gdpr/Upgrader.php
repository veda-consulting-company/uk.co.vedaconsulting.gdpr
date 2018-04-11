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
   */
  public function install() {
    //$this->executeSqlFile('sql/myinstall.sql');

    // Create 'GDPR Cancelled' membership status
    $this->createGDPRCancelledMembershipStatus();
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   *
   **/
  public function postInstall() {
    $this->executeCustomDataFile('xml/CustomGroupData.xml');
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   */
  public function uninstall() {
    //$this->executeSqlFile('sql/myuninstall.sql');

    // Delete 'GDPR Cancelled' membership status
    $result = CRM_Gdpr_Utils::CiviCRMAPIWrapper('MembershipStatus', 'get', array(
      'sequential' => 1,
      'return' => array("id"),
      'name' => "GDPR_Cancelled",
      'api.MembershipStatus.delete' => array(
        'id' => "\$value.id",
      ),
    ));

    // Delete 'Contacts without any activity for a period' custom search
    $result = CRM_Gdpr_Utils::CiviCRMAPIWrapper('CustomSearch', 'get', array(
      'sequential' => 1,
      'return' => array("id"),
      'name' => "CRM_Gdpr_Form_Search_ActivityContact",
      'api.CustomSearch.delete' => array(
        'id' => "\$value.id",
      ),
    ));

    // Delete 'Search Group Subscription by Date Range' custom search
    $result = CRM_Gdpr_Utils::CiviCRMAPIWrapper('CustomSearch', 'get', array(
      'sequential' => 1,
      'return' => array("id"),
      'name' => "CRM_Gdpr_Form_Search_GroupcontactDetails",
      'api.CustomSearch.delete' => array(
        'id' => "\$value.id",
      ),
    ));
  }

  /**
   * Example: Run a simple query when a module is enabled.
   */
  public function enable() {
    // Enable 'GDPR Cancelled' membership status
    $result = CRM_Gdpr_Utils::CiviCRMAPIWrapper('MembershipStatus', 'get', array(
      'sequential' => 1,
      'return' => array("id"),
      'name' => "GDPR_Cancelled",
      'api.MembershipStatus.create' => array(
        'id' => "\$value.id",
        'is_active' => 1,
      ),
    ));
  }

  /**
   * Example: Run a simple query when a module is disabled.
   */
  public function disable() {
    // Disable 'GDPR Cancelled' membership status
    $result = CRM_Gdpr_Utils::CiviCRMAPIWrapper('MembershipStatus', 'get', array(
      'sequential' => 1,
      'return' => array("id"),
      'name' => "GDPR_Cancelled",
      'api.MembershipStatus.create' => array(
        'id' => "\$value.id",
        'is_active' => 0,
      ),
    ));
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
    CRM_Gdpr_Utils::CiviCRMAPIWrapper('CustomSearch', 'create', array(
      'sequential' => 1,
      'option_group_id' => "custom_search",
      'name' => "CRM_Gdpr_Form_Search_ActivityContact",
      'is_active' => 1,
      'label' => "CRM_Gdpr_Form_Search_ActivityContact",
      'description' => "Contacts without any activity for a period",
    ));
    return TRUE;
  }

  /**
   * Perform upgrade to version 1.2
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1200() {
    $this->log('Applying update 1200');
    // Create 'GDPR Cancelled' membership status
    $this->createGDPRCancelledMembershipStatus();
    return TRUE;
  }

  /**
   * Perform upgrade to version 1.2.0.1
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1201() {
    $this->ctx->log->info('Applying update 1.2.0.1');
    $this->executeCustomDataFile('xml/CustomGroupData.xml');
    return TRUE;
  }

  /**
   * Perform upgrade to version 1.2.0.2
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1202() {
    $this->ctx->log->info('Applying update 1.2.0.2');
    // Change labels for custom data.
    $sql_file = 'sql/alterCustomDataLabels.sql';
    $this->executeSqlFile($sql_file);
    return TRUE;
  }

  /**
   * Perform upgrade to version 1.2.0.3
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1203() {
    $this->ctx->log->info('Applying update 1.2.0.3');
    $this->executeCustomDataFile('xml/CustomGroupData.xml');
    return TRUE;
  }

  /**
   * Perform upgrade to version 1.2.0.4
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1204() {
    $this->ctx->log->info('Applying update 1.2.0.4, to create activity type forget me');
    $this->executeCustomDataFile('xml/CustomGroupData.xml');
    return TRUE;
  }


  /**
   * Example: Run an external SQL script when the module is uninstalled.
   */
  private function createGDPRCancelledMembershipStatus() {
    // Get max weight for membership status
    $result = CRM_Gdpr_Utils::CiviCRMAPIWrapper('MembershipStatus', 'get', array(
      'sequential' => 1,
      'return' => array("weight"),
      'options' => array('sort' => "weight DESC", 'limit' => 1),
    ));
    $weight = $result['values'][0]['weight'] + 1;

    // Create 'GDPR Cancelled' membership status
    CRM_Gdpr_Utils::CiviCRMAPIWrapper('MembershipStatus', 'create', array(
      'name' => "GDPR_Cancelled",
      'label' => "GDPR Cancelled",
      'is_admin' => 1, // Is Admin Only
      'is_active' => 1,
      'is_reserved' => 1, // Is reserved, so that users cannot delete it
      'is_current_member' => 0,
      'weight' => $weight,
    ));
  }

  private function log($message) {
    if (is_object($this->ctx) && method_exists($this->ctx, 'info')) {
      $this->ctx->log->info($message);
    }
  }
}
