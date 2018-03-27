<?php

//require_once 'CRM/Gdpr/Utils.php';
class CRM_Gdpr_Page_Tab extends CRM_Core_Page {

  public function run() {
  	// Retrieve contact id from url
  	$contactId = CRM_Utils_Request::retrieve('cid', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);

  	// Get all group subscription
  	$groupSubscriptions = CRM_Gdpr_Utils::getallGroupSubscription($contactId);
  	$this->assign('groupSubscriptions', $groupSubscriptions);
  	$this->assign('contactId', $contactId);

    $summary['communications_preferences'] = $this->getCommunicationsPreferencesDetails($contactId);
    $summary['data_policy'] = $this->getDataPolicyDetails($contactId);
    $this->assign('summary', $summary);


    parent::run();
  }

  public function getDataPolicyDetails($contactId) {
    $details = array(
      'title' => ts('Data Policy acceptance.'),
      'details' => ts('Not yet accepted by the contact.'),
    );
    $activity = CRM_Gdpr_SLA_Utils::getContactLastAcceptance($contactId);
    $isDue = CRM_Gdpr_SLA_Utils::isContactDueAcceptance($contactId);
    if ($activity) {
      $details['details'] = $activity['subject'];
      $details['date'] = $activity['created_date'];
      $field = CRM_Gdpr_SLA_Utils::getTermsConditionsField();
      $key = 'custom_' . $field['id'];
      $url = !empty($activity[$key]) ? $activity[$key] : '';
      $label = CRM_Gdpr_SLA_Utils::getLinkLabel();
      $separator = '<br />';
      if ($isDue) {
        $dueMsg = '<span class="notice">The contact is due to renew their acceptance.</span>';
      }
      if ($url) {
        $details['details']  .= $separator . '<a target="blank" href="' . $url . '">' . $label  .'</a>' . $separator . $dueMsg;
      }
    }
    return $details;
  }

  public function getCommunicationsPreferencesDetails($contactId) {
    $details = array(
      'title' => ts('Communications Preferences'),
      'details' => ts('Not yet updated by the contact.'),
    );
    $activity = CRM_Gdpr_CommunicationsPreferences_Utils::getLastUpdatedForContact($contactId);
    if ($activity) {
      $details['details'] = $activity['subject'];
      $details['date'] = $activity['created_date'];
    }
    return $details;
  }
}
