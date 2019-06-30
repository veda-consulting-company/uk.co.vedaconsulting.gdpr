<?php

use CRM_Gdpr_ExtensionUtil as E;
use CRM_Gdpr_CommunicationsPreferences_Utils as U;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Gdpr_Form_UpdatePreference extends CRM_Core_Form {
  protected $settings;
  protected $commPrefSettings;
  protected $commPrefGroupsetting;
  public $channelEleNames;
  public $groupEleNames;
  protected $_fields = array();

  public $containerPrefix = 'enable_';

  //for Profile form validation
  public $_id;
  public $_gid;
  public $_context;
  public $_ruleGroupID;

  public function preProcess() {
    //Retrieve contact id from URL
    $this->_cid = $this->getContactID();

    //Do not allow anon users to update unless have a valid checksum
    if(empty($this->_cid)){
      //Do Nothing for now
    }

    //Add Gdpr CSS file
    CRM_Core_Resources::singleton()->addStyleFile('uk.co.vedaconsulting.gdpr', 'css/gdpr.css');
    parent::preProcess();
  }

  public function getSettings() {
    $this->settings    = U::getSettings();
    $this->commPrefSettings = $this->settings[U::SETTING_NAME];
    $this->commPrefGroupsetting   = $this->settings[U::GROUP_SETTING_NAME];
  }

  public function buildQuickForm() {

    //Get all Communication preference settings
    $this->getSettings();
    $this->_session = CRM_Core_Session::singleton();
    $userID  = $this->_session->get('userID');

    // $this->assign('commPrefGroupsetting', $this->commPrefGroupsetting);

    if (!empty($this->commPrefSettings['profile'])) {
      $this->buildCustom($this->commPrefSettings['profile']);
    }

    //Display Page Title from settings
    if ($pageTitle = $this->commPrefSettings['page_title']) {
      CRM_Utils_System::setTitle(E::ts($pageTitle));
    }

    //Display Page intro from settings.
    if ($introText = $this->commPrefSettings['page_intro']) {
      $this->assign('page_intro', $introText);
    }

    //Include reCAPTCHA?
    if ($addCaptcha = $this->commPrefSettings['add_captcha']) {
      $captcha = CRM_Utils_ReCAPTCHA::singleton();
      $captcha->add($this);
      $this->assign('isCaptcha', TRUE);
    }

    //Inject channels and groups into comms preferenec form.
    //we have moved this section into helper functions, because we are reusing same functions in other place like event / contribution thank you page to have comms preference embed form
    U::injectCommPreferenceFieldsIntoForm($this);

    //GDPR Terms and conditions
    //if already accepted then we dont this link at all
    $isContactDueAcceptance = empty($this->_cid) ?  TRUE : CRM_Gdpr_SLA_Utils::isContactDueAcceptance($this->_cid);
    if ($gdprTermsConditionsUrl = CRM_Gdpr_SLA_Utils::getTermsConditionsUrl()) {
      $this->assign('gdprTcURL', $gdprTermsConditionsUrl);
    }
    if ($gdprTermsConditionslabel = CRM_Gdpr_SLA_Utils::getLinkLabel()) {
      $this->assign('gdprTcLabel', $gdprTermsConditionslabel);
    }
    if ($isContactDueAcceptance) {
      $termsConditionsField = $this->getTermsAndConditionFieldId();

      $tcFieldName  = 'custom_'.$termsConditionsField;
      $tcLink = E::ts("<a href='%1' target='_blank'>%2</a>", array(1 => $gdprTermsConditionsUrl, 2 => $gdprTermsConditionslabel));
      $this->assign('tcLink', $tcLink);
      $this->assign('tcIntro', CRM_Gdpr_SLA_Utils::getIntro());
      $tcFieldlabel = CRM_Gdpr_SLA_Utils::getCheckboxText();
      $this->assign('tcFieldlabel', $tcFieldlabel);
      $this->assign('tcFieldName', $tcFieldName);
      $this->assign('isContactDueAcceptance', $isContactDueAcceptance);

      $this->add('checkbox', $tcFieldName, $gdprTermsConditionslabel, NULL, TRUE);
    }
    else {
      $accept_activity = CRM_Gdpr_SLA_utils::getContactLastAcceptance($this->_cid);
      $accept_date = '';
      if (!empty($accept_activity['activity_date_time'])) {
        $accept_date = date('d/m/Y', strtotime($accept_activity['activity_date_time']));
      }

      $tcFieldlabel = E::ts("Here is our <a href='%1' target='_blank'>%2</a>, which you agreed to on %3.",
        array(
          1 => $gdprTermsConditionsUrl,
          2 => $gdprTermsConditionslabel,
          3 => $accept_date,
        )
      );
      $this->assign('tcFieldlabel', $tcFieldlabel);
    }

    //have source field for offline comms preference, make sure we dont show this field when contact update their own preferences
    if ($userID && $userID != $this->_cid) {
      $this->add('text', 'activity_source', E::ts('Source of Communication Preferences'));
    }

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('groupEleNames', $this->groupEleNames);

    //add form rule
    $this->addFormRule(array('CRM_Gdpr_Form_UpdatePreference', 'formRule'), $this);
    if (!empty($this->commPrefSettings['profile'])) {
      $this->addFormRule(array('CRM_Profile_Form', 'formRule'), $this);
    }

    parent::buildQuickForm();
  }

  /**
   * Add the custom fields.
   *
   * @param int $id
   * @param string $name
   * @param bool $viewOnly
   */
  public function buildCustom($id, $name = 'custom_pre', $viewOnly = FALSE) {
    if ($id) {
      //For Profile form validation
      $this->_gid = $id;
      $dao = new CRM_Core_DAO_UFGroup();
      $dao->id = $id;
      if ($dao->find(TRUE)) {
        $this->_isUpdateDupe = $dao->is_update_dupe; // Profile duplicate match option
        // $this->_isUpdateDupe = $dao->is_update_dupe;
        $this->_isAddCaptcha = $dao->add_captcha;
        $this->_ufGroup = (array) $dao;
      }

      $button = substr($this->controller->getButtonName(), -4);
      $contactID = $this->_cid;

      if ($contactID) {
        CRM_Core_BAO_UFGroup::filterUFGroups($id, $contactID);
      }

      $fields = CRM_Core_BAO_UFGroup::getFields($id, FALSE, CRM_Core_Action::ADD,
        NULL, NULL, FALSE, NULL,
        FALSE, NULL, CRM_Core_Permission::CREATE,
        'field_name', TRUE
      );

      $addCaptcha = FALSE;

      $this->assign($name, $fields);
      if (is_array($fields)) {
        foreach ($fields as $key => $field) {
          if ($viewOnly &&
            isset($field['data_type']) &&
            $field['data_type'] == 'File' || ($viewOnly && $field['name'] == 'image_URL')
          ) {
            // ignore file upload fields
            continue;
          }
          //make the field optional if primary participant
          //have been skip the additional participant.
          if ($button == 'skip') {
            $field['is_required'] = FALSE;
          }
          // CRM-11316 Is ReCAPTCHA enabled for this profile AND is this an anonymous visitor
          elseif ($field['add_captcha'] && !$contactID) {
            // only add captcha for first page
            $addCaptcha = TRUE;
          }
          $this->_mode = $contactID ? CRM_Profile_Form::MODE_EDIT : CRM_Profile_Form::MODE_CREATE;
          CRM_Core_BAO_UFGroup::buildProfile($this, $field, $this->_mode, $contactID, TRUE);

          $this->_fields[$key] = $field;
        }
      }

      if ($addCaptcha && !$viewOnly) {
        $captcha = CRM_Utils_ReCAPTCHA::singleton();
        $captcha->add($this);
        $this->assign('isCaptcha', TRUE);
      }
    }
  }

  public static function formRule($fields, $files, $self){
    $errors = array();

    if (!empty($self->groupEleNames)) {
      foreach ($self->groupEleNames as $groupName => $groupEleName) {
        //get the channel array and group channel array
        $groupChannelArray = array();
        foreach ($self->channelEleNames as $channel) {
          $groupChannel = str_replace($self->containerPrefix, '', $channel);
          $channelSettingValue = $self->commPrefGroupsetting[$groupEleName][$groupChannel];

          if (!is_null($channelSettingValue) && $channelSettingValue != '') {
            $channelArray[$groupChannel] = ($fields[$channel] == 'YES') ? 1 : 0;
            $groupChannelArray[$groupChannel] = empty($self->commPrefGroupsetting[$groupEleName][$groupChannel]) ? 0 : 1;
          }
        }

        //check any difference then return as error
        if(!empty($fields[$groupEleName]) && ($diff = array_diff_assoc($groupChannelArray, $channelArray))){
          //do something here.
          $diff = implode(', ', array_keys($diff));
          $errors[$groupEleName] = E::ts("Communication Preferences {$diff} has to be selected for this group");
        }
      }
    }

    return empty($errors) ? TRUE : $errors;
  }

  public function setDefaultValues() {
    $defaults = array();
    if (!empty($this->_cid)) {
      $contactDetails = civicrm_api3('Contact', 'getsingle', array( 'id' => $this->_cid));

      $lastAcceptance = CRM_Gdpr_SLA_Utils::getContactLastAcceptance($this->_cid);

      //Set Channel default values
      $contactPrefPrefix = 'do_not_';
      foreach ($this->commPrefSettings['channels'] as $key => $value) {
        $name  = str_replace($this->containerPrefix, '', $key);
        if (!$lastAcceptance) {
          // No acceptance, and preferences are 0 then, set unknown, otherwise display yes/no
          $defaults[$key] = '';
        }
        elseif ($value) {
          $defaults[$key] = !empty($contactDetails[$contactPrefPrefix.$name]) ? 'NO' : 'YES';
        }
      }

      //Set Group default values
      $groups = U::getGroups();
      foreach ($groups as $group) {
        $container_name = 'group_' . $group['id'];
        if (!empty($this->commPrefGroupsetting[$container_name]['group_enable'])) {
          $contactGroupDetails = civicrm_api3('GroupContact'
            , 'get'
            , array( 'contact_id' => $this->_cid
              , 'group_id' => $group['id']
              , 'status' => 'Added',
            )
          );

          if (!empty($contactGroupDetails['id'])) {
            $defaults[$container_name] = 1;
          }
        }
      }

      //Set Profile defaults
      $fields = array();
      $removeCustomFieldTypes = array('Contribution', 'Membership');
      $contribFields = CRM_Contribute_BAO_Contribution::getContributionFields();

      foreach ($this->_fields as $name => $dontCare) {
        //don't set custom data Used for Contribution (CRM-1344)
        if (substr($name, 0, 7) == 'custom_') {
          $id = substr($name, 7);
          if (!CRM_Core_BAO_CustomGroup::checkCustomField($id, $removeCustomFieldTypes)) {
            continue;
          }
          // ignore component fields
        }
        elseif (array_key_exists($name, $contribFields) || (substr($name, 0, 11) == 'membership_') || (substr($name, 0, 13) == 'contribution_')) {
          continue;
        }
        $fields[$name] = 1;
      }

      if (!empty($fields)) {
        CRM_Core_BAO_UFGroup::setProfileDefaults($this->_cid, $fields, $defaults);
      }
    }
    //#7955 params from URL
    $emailPrimary = CRM_Utils_Request::retrieve('field_email', 'String', CRM_Core_DAO::$_nullObject);
    if ($emailPrimary) {
      $defaults['email-Primary'] = $emailPrimary;
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
    //profile form validation will do dedupe and update id in $form
    $existingContact = $this->_cid;
    if (!empty($this->_id)) {
      $existingContact = $this->_id;
    }

    $contactType = 'Individual';
    if ($existingContact) {
      $contactType = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $existingContact, 'contact_type');
    }
    $contactID = CRM_Contact_BAO_Contact::createProfileContact(
      $submittedValues,
      $this->_fields,
      $existingContact,
      NULL,
      NULL,
      $contactType,
      TRUE
    );
     //Terms and condition Record SLA acceptance
    $termsConditionsField = $this->getTermsAndConditionFieldId();
    $tcFieldName  = 'custom_'.$termsConditionsField;
    if (!empty($submittedValues[$tcFieldName])) {
      $acceptance = CRM_Gdpr_SLA_Utils::recordSLAAcceptance($contactID);
    }

    //we have now moved this section into common helper function which reused in other place like event/contribution thank you to let update comms preference using embed form.
    U::updateCommsPrefByFormValues($contactID, $submittedValues);   
    U::createCommsPrefActivity($contactID, $submittedValues);

    if (!empty($this->commPrefSettings['completion_message'])) {
      $thankYouMsg = html_entity_decode($this->commPrefSettings['completion_message']);

      //FIXME Redirect to Thank you page or destination url from setting
      CRM_Core_Session::setStatus($thankYouMsg, E::ts('Communication Preferences'), 'Success');
    }

    //Get the destination url from settings and redirect if we found one.
    if (!empty($this->commPrefSettings['completion_redirect'])) {
      $destinationURL = !empty($this->commPrefSettings['completion_url']) ? $this->commPrefSettings['completion_url'] : NULL;
      //MV: commenting this line, We have already restriceted the setting to get only absoulte URl.
      //check URL is not absolute and no leading slash then add leading slash before redirect.
      $parseURL = parse_url($destinationURL);
      if (empty($parseURL['host']) && (strpos($destinationURL, '/') !== 0)) {
        $destinationURL = '/'.$destinationURL;
      }
      CRM_Utils_System::redirect($destinationURL);
    }
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
