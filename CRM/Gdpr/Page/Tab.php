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
    parent::run();
  }
}
