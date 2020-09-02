<?php

use CRM_Gdpr_ExtensionUtil as E;

class CRM_Gdpr_Form_Task_Contact extends CRM_Contact_Form_Task {

  public function preProcess() {
    parent::preProcess();

    $this->assign('title', E::ts('GDPR forget me'));
    $this->assign('help_text', E::ts('These contact records will be anonymized'));

    $this->assign('status',
      E::ts("Selected contacts: %1", [
        1 => count($this->_contactIds),
      ])
    );
  }

  public function buildQuickForm() {
    $this->addDefaultButtons(E::ts('Next'));
  }

  public function postProcess() {
    foreach($this->_contactIds as $contactId) {
      civicrm_api3('Contact', 'anonymize', ['id' => $contactId]);
    }
  }

}
