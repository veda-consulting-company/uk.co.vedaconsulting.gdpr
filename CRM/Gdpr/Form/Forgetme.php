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

    $this->_contactID = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE);
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

    if (!$this->_contactID) {
      CRM_Core_Error::fatal(ts("Something went wrong. Please contact Admin."));
    }

    // Remove all the linked relationship records of this contact
    $params = array(
      'sequential' => 1,
      'contact_id_a' => $this->_contactID,
      'contact_id_b' => $this->_contactID,
      'options' => array('or' => array(array("contact_id_a", "contact_id_b"))),
    );
    self::removeEntityRecords('Relationship', $params);

    // Remove all the address records of this contact
    $params = array(
      'sequential' => 1,
      'contact_id' => $this->_contactID,
    );
    self::removeEntityRecords('Address', $params);

    // Remove all the email records of this contact
    $params = array(
      'sequential' => 1,
      'contact_id' => $this->_contactID,
    );
    self::removeEntityRecords('Email', $params);

    // Remove all the phone records of this contact
    $params = array(
      'sequential' => 1,
      'contact_id' => $this->_contactID,
    );
    self::removeEntityRecords('Phone', $params);

    // Remove all the website records of this contact
    $params = array(
      'sequential' => 1,
      'contact_id' => $this->_contactID,
    );
    self::removeEntityRecords('Website', $params);

    // Remove all the IM records of this contact
    $params = array(
      'sequential' => 1,
      'contact_id' => $this->_contactID,
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
    if (!$this->_contactID) {
      CRM_Core_Error::fatal(ts("Something went wrong. Please contact Admin."));
    }
    $params = array('id' => $this->_contactID);
    // Update contact Record
    $updateResult = CRM_Gdpr_Utils::CiviCRMAPIWrapper('Contact', 'anonymize', $params);

    if ($updateResult && !empty($updateResult['values'])) {
      CRM_Core_Session::setStatus(ts("Contact has been made anonymous."), ts('Forget successful'), 'success');
    } else {
      CRM_Core_Session::setStatus(ts("Records has not been cleared."), ts('Record not Deleted cleanly. Please contact admin!'), 'error');
    }

  }

}
