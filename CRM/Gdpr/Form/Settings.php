<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Gdpr_Form_Settings extends CRM_Core_Form {

  function buildQuickForm() {

    CRM_Utils_System::setTitle(ts('GDPR - Settings'));

    $this->addEntityRef('data_officer', ts('Data Protection Officer (DPO)'), array(
        'create' => TRUE,
        'api' => array('extra' => array('email')),
      ), TRUE);

    // Get all activity types
    $actTypes = CRM_Gdpr_Utils::getAllActivityTypes();

    // Activity types
    $this->add(
      'select',
      'activity_type',
      ts('Activity Types'),
      array('' => ts('- select -')) + $actTypes, // list of options
      TRUE,
      array('class' => 'crm-select2 huge', 'multiple' => 'multiple',)
    );

    // Get all contact types
    $contactTypes = CRM_Gdpr_Utils::getAllContactTypes($parentOnly = TRUE);
    $this->add(
      'select', 
      'contact_type', 
      ts('Contact Types'), 
      array('' => ts('- select -')) + $contactTypes, // list of options
      TRUE, 
      array('class' => 'crm-select2 huge', 'multiple' => 'multiple',)
    );

    $this->add(
      'text',
      'activity_period',
      ts('Period'),
      array('size' => 4),
      TRUE
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    $defaults = array();

    // Get GDPR settings, for setting defaults
    $defaults = CRM_Gdpr_Utils::getGDPRSettings();

    // Set defaults
    if (!empty($defaults)) {
      $this->setDefaults($defaults);
    }

    parent::buildQuickForm();
  }

  function postProcess() {
    $values = $this->exportValues();
    
    $settings = array();
    $settings['data_officer'] = $values['data_officer'];
    $settings['activity_type'] = $values['activity_type'];
    $settings['activity_period'] = $values['activity_period'];
    $settings['contact_type'] = $values['contact_type'];

    $settingsStr = serialize($settings);

    // Save the settings
    CRM_Core_BAO_Setting::setItem($settingsStr, CRM_Gdpr_Constants::GDPR_SETTING_GROUP, CRM_Gdpr_Constants::GDPR_SETTING_NAME);
    
    $message = "GDPR settings saved.";
    $url = CRM_Utils_System::url('civicrm/gdpr/dashboard', 'reset=1');

    CRM_Core_Session::setStatus($message, 'GDPR', 'success');
    CRM_Utils_System::redirect($url);
    CRM_Utils_System::civiExit();
  }
}
