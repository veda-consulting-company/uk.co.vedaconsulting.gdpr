<?php

use CRM_Gdpr_ExtensionUtil as E;

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
        'name' => E::ts('Forget me'),
        'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ),
    ));

    parent::buildQuickForm();
  }

  public function postProcess() {

    if (!$this->_contactID) {
      CRM_Core_Error::fatal(E::ts("Something went wrong. Please contact Admin."));
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
      CRM_Core_Session::setStatus(E::ts("{$entity} records has not been deleted."), E::ts('Record not Deleted cleanly'), 'error');
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
      CRM_Core_Error::fatal(E::ts("Something went wrong. Please contact Admin."));
    }
    $params = array('id' => $this->_contactID);
    // Update contact Record
    $updateResult = CRM_Gdpr_Utils::CiviCRMAPIWrapper('Contact', 'anonymize', $params);

    if ($updateResult && !empty($updateResult['values'])) {
      CRM_Core_Session::setStatus(E::ts("Contact has been made anonymous."), E::ts('Forget successful'), 'success');

      //MV:#7040, if successfully anonymized then record activity.
      self::createForgetMeActivity($this->_contactID);

    } else {
      CRM_Core_Session::setStatus(E::ts("Records has not been cleared."), E::ts('Record not Deleted cleanly. Please contact admin!'), 'error');
    }

  }

  public static function createForgetMeActivity($contactID) {
    if (empty($contactID)) {
      return FALSE;
    }

    $activityTypeIds = array_flip(CRM_Core_PseudoConstant::activityType(TRUE, FALSE, FALSE, 'name'));
    //check Activity type exits before fire an API.
    if (!empty($activityTypeIds[CRM_Gdpr_Constants::FORGET_ME_ACTIVITY_TYPE_NAME])) {
      
      $activityTypeId = $activityTypeIds[CRM_Gdpr_Constants::FORGET_ME_ACTIVITY_TYPE_NAME];
      //Make logged in user record as source contact record
      $sourceContactID = $contactID;
      if ($loggedinUser = CRM_Core_Session::singleton()->getLoggedInContactID()) {
        $sourceContactID = $loggedinUser;
      }
      $subject = ts('GDPR - Contact has been made anonymous');
      $params = array(
        'activity_type_id'  => $activityTypeId,
        'source_contact_id' => $sourceContactID,
        'target_id'         => $contactID,
        'activity_date_time'=> date('Y-m-d H:i:s'),
        'subject'           => $subject,
        'status_id'         => 2, //COMPLETED
      );

      CRM_Gdpr_Utils::CiviCRMAPIWrapper('Activity', 'create', $params);
    }
  } //End function
}
