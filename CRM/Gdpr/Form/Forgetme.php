<?php

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Gdpr_Form_Forgetme extends CRM_Core_Form {

  /**
   * Contact ID.
   *
   * @var int
   */
  protected $_contactID = NULL;

  /**
   * Form preProcess function.
   *
   * @throws \Exception
   */
  public function preProcess() {

    // <!-- To DO - check permission -->

    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE);
  }

  public function buildQuickForm() {

    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Forget me'),
        'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    ));

    parent::buildQuickForm();
  }

  public function postProcess() {

    if (!$this->_contactId) {
      CRM_Core_Error::fatal(ts("Something went wrong. Please contact Admin."));
    }

    // Remove all the linked relationship records of this contact
    $params = array(
      'sequential' => 1,
      'contact_id_a' => $this->_contactId,
      'contact_id_b' => $this->_contactId,
      'options' => array('or' => array(array("contact_id_a", "contact_id_b"))),
    );
    self::removeEntityRecords('Relationship', $params);

    // Remove all the address records of this contact
    $params = array(
      'sequential' => 1,
      'contact_id' => $this->_contactId,
    );
    self::removeEntityRecords('Address', $params);

    // Remove all the email records of this contact
    $params = array(
      'sequential' => 1,
      'contact_id' => $this->_contactId,
    );
    self::removeEntityRecords('Email', $params);

    // Remove all the phone records of this contact
    $params = array(
      'sequential' => 1,
      'contact_id' => $this->_contactId,
    );
    self::removeEntityRecords('Phone', $params);

    // Remove all the website records of this contact
    $params = array(
      'sequential' => 1,
      'contact_id' => $this->_contactId,
    );
    self::removeEntityRecords('Website', $params);

    // Remove all the IM records of this contact
    $params = array(
      'sequential' => 1,
      'contact_id' => $this->_contactId,
    );
    self::removeEntityRecords('Im', $params);

    // Finally make contact as anonymous
    self::makeContactAnonymous();

    return;
  }

  // Function to get stored entity records of a given type and remove them
  private function removeEntityRecords($entity = NULL, $params = array()) {

    // return, if entity or params are not passed
    if (!$entity || empty($params)) {
      CRM_Core_Session::setStatus(ts("{$entity} records has not been deleted."), ts('Record not Deleted cleanly'), 'error');
      return;
    }

    $recordIds = array();

     // Get all records of the given entity
    $records = CRM_Gdpr_Utils::CiviCRMAPIWrapper($entity, 'get', $params);

    if ($records && !empty($records['values'])) {
      foreach ($records['values'] as $key => $record) {
        array_push($recordIds, $record['id']);
      }
    }

    // delete all the records
    foreach ($recordIds as $key => $recordId) {
      $result = CRM_Gdpr_Utils::CiviCRMAPIWrapper($entity, 'delete', array(
        'sequential' => 1,
        'id' => $recordId,
      ));
    }

  }

  private function makeContactAnonymous() {
    if (!$this->_contactId) {
      CRM_Core_Error::fatal(ts("Something went wrong. Please contact Admin."));
    }

    // get all fields of contact API
    $fieldsResult = CRM_Gdpr_Utils::CiviCRMAPIWrapper('Contact', 'getfields', array(
      'sequential' => 1,
    ));

    $fields = array();
    if ($fieldsResult && !empty($fieldsResult['values'])) {
      $fields = $fieldsResult['values'];
    }

    // setting up params to update contact record
    $params = array(
      'sequential' => 1,
    );

    // Loop through fields and set them empty
    foreach ($fields as $key => $field) {
      //Fix me : skipping if not a core field. We may need to clear the custom fields later
      if ( !array_key_exists('is_core_field', $field) || $field['is_core_field'] != 1 ) {
        continue;
      }

      $fieldName = $field['name'];
      $params[$fieldName] = '';
    }

    // Add contact ID into params to update the contact record
    $params['id'] = $this->_contactId;
    // Set diplay name as Anonymous by default
    $params['display_name'] = 'Anonymous';

    // Get Display Name from GDPR settings
    $settings = CRM_Gdpr_Utils::getGDPRSettings();
    if (!empty($settings['forgetme_name'])) {
      $params['display_name'] = $settings['forgetme_name'];
    }

    // Update contact Record
    $updateResult = CRM_Gdpr_Utils::CiviCRMAPIWrapper('Contact', 'create', $params);

    if ($updateResult && !empty($updateResult['values'])) {
      CRM_Core_Session::setStatus(ts("Contact has been made anonymous."), ts('Forget successful'), 'success');
    } else {
      CRM_Core_Session::setStatus(ts("Records has not been cleared."), ts('Record not Deleted cleanly. Please contact admin!'), 'error');
    }

  }

}
