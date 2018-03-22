<?php

use CRM_Gdpr_ExtensionUtil as E;
use CRM_Gdpr_CommunicationsPreferences_Utils as U;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Gdpr_Form_UpdatePreference extends CRM_Core_Form {
  
  public function preProcess() {
    //Retrieve contact id from URL
    $this->_cid = CRM_Utils_Request::retrieve('cid', 'Positive', $this, FALSE);

    //For Now consider if cid is not passed on URL then use logged in user contact ID.
    if (empty($this->_cid)) {
      $this->_cid = CRM_Core_Session::getLoggedInContactID();
    }
    
    //Do not allow anon users to update unless have a valid checksum
    CRM_Contact_BAO_Contact_Permission::validateChecksumContact($this->_cid, $this, TRUE);

    parent::preProcess();
  }
  
  public function buildQuickForm() {
    //Get all Communication preference settings
    $settings    = U::getSettings();
    $commPrefSettings = $settings[U::SETTING_NAME];
    $commPrefGroupsetting   = $settings[U::GROUP_SETTING_NAME];

    //Display Page Title from settings
    if ($pageTitle = $commPrefSettings['page_title']) {
      CRM_Utils_System::setTitle(ts($pageTitle));
    }

    //Display Page intro from settings.
    if ($introText = $commPrefSettings['page_intro']) {
      $this->assign('page_intro', $introText);
    }

    //Check the channels are enabled ?
    $channelEleNames   = array();
    $isChannelsEnabled = $commPrefSettings['enable_channels'];
    if ($isChannelsEnabled) {
      //Display Page intro from settings.
      if ($channelIntro = $commPrefSettings['channels_intro']) {
        $this->assign('channels_intro', $channelIntro);
      }

      $commPrefOpGroup = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', U::COMM_PREF_OPTIONS, 'id', 'name');
      $commPrefOptions = CRM_Core_BAO_OptionValue::getOptionValuesAssocArray($commPrefOpGroup);
      $containerPrefix = 'enable_';
      foreach ($commPrefSettings['channels'] as $key => $value) {
        if ($value) {
          $name  = str_replace($containerPrefix, '', $key);
          $label = ucwords(str_replace('_', ' ', $name));
          $this->add('select', $name, $label, $commPrefOptions, TRUE);
          $channelEleNames[] = $name; 
        }
      }
    }
    
    // export form elements
    $this->assign('channelEleNames', $channelEleNames);

    //Communication preference Group settings enabled ?
    $isGroupSettingEnabled = $commPrefSettings['enable_groups'];
    if ($isGroupSettingEnabled) {
      
      if ($groupsHeading = $commPrefSettings['groups_heading']) {
        $this->assign('groups_heading', $groupsHeading);
      }
      
      if ($groupsIntro = $commPrefSettings['groups_intro']) {
        $this->assign('groups_intro', $groupsIntro);
      }      

      //all for all groups and disable checkbox is group_enabled from settings
      $groups = U::getGroups();
      foreach ($groups as $group) {
        $container_name = 'group_' . $group['id'];
        if (!empty($commPrefGroupsetting[$container_name]['group_enable'])) {
          $title = $commPrefGroupsetting[$container_name]['group_title'];
          $groupsFromSettings[$title] = $group['id'];
        }
      }
      $this->addCheckBox('enabled_groups', 
        ts($commPrefSettings['groups_heading']),
        $groupsFromSettings
        , NULL, NULL, NULL, NULL, '<br><br>'
      );      
    }
    
    //GDPR Terms and conditions
    //if already accepted then we dont this link at all
    $isContactDueAcceptance = CRM_Gdpr_SLA_Utils::isContactDueAcceptance();
    if ($isContactDueAcceptance) {
      if ($gdprTermsConditionsUrl = CRM_Gdpr_SLA_Utils::getTermsConditionsUrl()) {
        $this->assign('gdprTcURL', $gdprTermsConditionsUrl);
      }

      $termsConditionsField = $this->getTermsAndConditionFieldId();
      
      $tcFieldName  = 'custom_'.$termsConditionsField;
      $tcFieldlabel = sprintf("I have read and agree to the <a href='%s' target='_blank'>Terms and Conditions</a>", $gdprTermsConditionsUrl);
      
      $this->assign('tcFieldlabel', $tcFieldlabel);
      $this->assign('tcFieldName', $tcFieldName);
      $this->assign('isContactDueAcceptance', $isContactDueAcceptance);
      
      $this->add('checkbox', $tcFieldName, ts(''), NULL, TRUE);
    }

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('groupEleNames', array('enabled_groups'));

    parent::buildQuickForm();
  }

  public function setDefaultValues() {
    $defaults = array();
    if (!empty($this->_cid)) {
      $contactDetails = civicrm_api3('Contact', 'getsingle', array( 'id' => $this->_cid));

      $settings    = U::getSettings();
      $commPrefSettings = $settings[U::SETTING_NAME];
      $commPrefGroupsetting   = $settings[U::GROUP_SETTING_NAME];

      //Set Channel default values
      $containerPrefix = 'enable_';
      $contactPrefPrefix = 'do_not_';
      foreach ($commPrefSettings['channels'] as $key => $value) {
        if ($value) {
          $name  = str_replace($containerPrefix, '', $key);
          $defaults[$name] = !empty($contactDetails[$contactPrefPrefix.$name]) ? 'NO' : 'YES'; 
        }
      }

      //Set Group default values
      $groups = U::getGroups();
      foreach ($groups as $group) {
        $container_name = 'group_' . $group['id'];
        if (!empty($commPrefGroupsetting[$container_name]['group_enable'])) {
          $contactGroupDetails = civicrm_api3('GroupContact'
            , 'get'
            , array( 'contact_id' => $this->_cid
              , 'group_id' => $group['id']
              , 'status' => 'Added',
            )
          );

          if (!empty($contactGroupDetails['id'])) {
            $defaults['enabled_groups'][$group['id']] = 1;
          }
        }
      }      
    }
    return $defaults;
  }

  public function getTermsAndConditionFieldId() {
    $termsConditionsField =  CRM_Gdpr_SLA_Utils::getTermsConditionsField();
    return $termsConditionsField['id']; 
  }
  
  public function postProcess() {
    $submittedValues = $this->exportValues();
    $commPrefMapper  = U::getCommunicationPreferenceMapper();

    $settings         = U::getSettings();
    $commPrefSettings = $settings[U::SETTING_NAME];
    $commPrefGroupsetting   = $settings[U::GROUP_SETTING_NAME];


    //Terms and condition Record SLA acceptance
    $termsConditionsField = $this->getTermsAndConditionFieldId();
    $tcFieldName  = 'custom_'.$termsConditionsField;
    if (!empty($submittedValues[$tcFieldName]) && !empty($this->_cid)) {
      $acceptance = CRM_Gdpr_SLA_Utils::recordSLAAcceptance($this->_cid);
    }

    //Prepare Comm pref params
    $commPref = array('id' => $this->_cid);

    //Comm pref channel Values
    $containerPrefix = 'enable_';
    foreach ($commPrefSettings['channels'] as $key => $value) {
      $name  = str_replace($containerPrefix, '', $key);
      if (!empty($submittedValues[$name])) {
        $channelValue = $submittedValues[$name];
        $commPref = array_merge($commPref, $commPrefMapper[$name][$channelValue]);
      }
    }

    //Using API to update contact
    $contact = civicrm_api3('Contact', 'create', $commPref);

    $groups = U::getGroups();
    foreach ($groups as $groupId => $group) {
      $container_name = 'group_' . $group['id'];
      if (!empty($commPrefGroupsetting[$container_name]['group_enable'])) {
        $groupDetails = array(
          'contact_id' => $this->_cid,
          'group_id'   => $group['id'],
        );
        
        $existsInGroup = civicrm_api3('GroupContact', 'get', $groupDetails);
        
        //Set status added or removed based on user selection
        $status = !empty($submittedValues['enabled_groups'][$groupId]) ? 'Added' : 'Removed';
        $groupDetails['status'] = $status;
        
        //check before Add / Remove from group.
        if ((!empty($existsInGroup['id']) && $status == 'Removed')
          OR (empty($existsInGroup['id']) && $status == 'Added')
        ) {
          $groupResult = civicrm_api3('GroupContact', 'create', $groupDetails);
        }
      }
    }

    CRM_Core_Session::setStatus(ts('Communication Preferences has been updated successfully'), 'Communication Preference', 'Success');
    parent::postProcess();
  }


  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
