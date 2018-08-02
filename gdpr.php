<?php

require_once 'gdpr.civix.php';
use CRM_Gdpr_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function gdpr_civicrm_config(&$config) {
  _gdpr_civix_civicrm_config($config);
}


/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function gdpr_civicrm_xmlMenu(&$files) {
  _gdpr_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function gdpr_civicrm_install() {
  _gdpr_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function gdpr_civicrm_postInstall() {
  _gdpr_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function gdpr_civicrm_uninstall() {
  _gdpr_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function gdpr_civicrm_enable() {
  _gdpr_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function gdpr_civicrm_disable() {
  _gdpr_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function gdpr_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _gdpr_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function gdpr_civicrm_managed(&$entities) {
  _gdpr_civix_civicrm_managed($entities);
  $entities[] = [
    'module' => 'uk.co.vedaconsulting.gdpr',
    'name' => 'contributionpagecustomdata',
    'update' => 'never',
    'entity' => 'OptionValue',
    'params' => [
      'label' => ts('Contribution Page'),
      'name' => 'civicrm_contribution_page',
      'value' => 'ContributionPage',
      'option_group_id' => 'cg_extend_objects',
      'is_active' => 1,
      'version' => 3,
    ],
  ];
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function gdpr_civicrm_caseTypes(&$caseTypes) {
  _gdpr_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function gdpr_civicrm_angularModules(&$angularModules) {
  _gdpr_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function gdpr_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _gdpr_civix_civicrm_alterSettingsFolders($metaDataFolders);
}


/**
 * Implementation of hook_civicrm_alterContent
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterContent
 */
function gdpr_civicrm_alterContent(&$content, $context, $tplName, &$object) {
  if ($context  == 'page' && $tplName == 'CRM/Contact/Page/View/Summary.tpl') {
    // Do not add content in AJAX generated page/form
    // as they be inline page/form
    if (isset($_GET['snippet'])) {
      return;
    }

    // Get Contact Id
    $contactId = $object->getVar('_contactId');

    if (empty($contactId)) {
      return;
    }

    require_once "CRM/Logging/Schema.php";
    $config = CRM_Core_Config::singleton();
    if ($config->logging) {
      $addressHistoryAjaxUrl = CRM_Utils_System::url('civicrm/ajax/rest', 'className=CRM_Gdpr_Page_AJAX&fnName=getAddressHistory&json=1');

      $addressHistoryContent = <<<EOD
<script type="text/javascript">
    cj(document).ready(function(){
      var contactId = "{$contactId}";
      var getAddressHistoryUrl = "{$addressHistoryAjaxUrl}";
      if (contactId)
      {
        cj.ajax({
          type: "POST",
          url: getAddressHistoryUrl,
          data: { contactId : contactId },
          success: function (data) {
            var split = data.split('|');
            if( split[0] != 0 ){
            var linkHtml = '<div class="crm-content" align="right"><a href="javascript:void(0);" id="address_history_dialog_link"> Address History ('+split[0]+') </a></div>';

            cj(linkHtml).insertAfter('#website-block');
            cj(linkHtml).wrap('<div class="contact_panel"></div>');
            cj('#address_history_dialog_link').click(function(){
              var oTable = cj(split[1]).dataTable({
                 "bSort": true,
                 "bJQueryUI": true,
                 "bAutoWidth": false,
                 "bSortClasses": false
               });
              cj(oTable).wrap('<div id="address_history"></div>');
              cj(oTable).parent('div').dialog({title: "Address History",
                modal: true,
                resizable: true,
                bgiframe: true,
                width: 675,
                height: 400,
                overlay: {
                  opacity: 0.5,
                  background: "black"
                },
                buttons: {
                  "Done": function() {
                    cj(this).dialog("destroy");
                  }
                }
               });//end dialog
             });//end click
            }//end if
          }//end success
        });//end ajax
      }
    });

  </script>
EOD;
      $addressHistoryContent = str_replace('&amp;', '&', $addressHistoryContent);
      $content = $content.$addressHistoryContent;
    }
  }
}

/**
 * Implements hook_civicrm_buildForm().
 */
function gdpr_civicrm_buildForm($formName, $form) {
  if ($formName == 'CRM_Event_Form_ManageEvent_EventInfo') {
    CRM_Core_Resources::singleton()->addStyleFile('uk.co.vedaconsulting.gdpr', 'css/gdpr.css');
  }
  if ($formName == 'CRM_Event_Form_Registration_Register') {
    CRM_Core_Resources::singleton()->addStyleFile('uk.co.vedaconsulting.gdpr', 'css/gdpr.css');
    $tc = new CRM_Gdpr_SLA_Event($form->_eventId);
    if ($tc->isEnabled(TRUE)) {
      $tc->addField($form);
    }
  }
  if ($formName == 'CRM_Event_Form_Registration_ThankYou') {
    $cid = $form->_values['participant']['contact_id'];
    if (!empty($cid)) {
      $templatePath = realpath(dirname(__FILE__).'/templates');
      CRM_Core_Region::instance('page-body')->add(
        array(
          'template' => "{$templatePath}/CRM/Gdpr/Event/ThankYou.tpl"
        )
      );

      //amend communication preference link/embed form in thank you page
      CRM_Gdpr_CommunicationsPreferences_Utils::commsPreferenceInThankyouPage($cid, $form, 'Event');  
    }
  }
  if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
    CRM_Core_Resources::singleton()->addStyleFile('uk.co.vedaconsulting.gdpr', 'css/gdpr.css');
    $tc = new CRM_Gdpr_SLA_ContributionPage($form->_id);
    if ($tc->isEnabled(TRUE)) {
      $tc->addField($form);
    }
  }
  if ($formName == 'CRM_Contribute_Form_Contribution_ThankYou') {
    // Contact id for logged in user.
    $cid = $form->_contactID;
    if (!$cid) {
      $trxnId = $form->getVar('_trxnId');
      if ($trxnId) {
        $cid = CRM_Core_DAO::getFieldValue('CRM_Contribute_DAO_Contribution', $trxnId, 'contact_id', 'trxn_id');
      }
    }
    if ($cid) {
      $templatePath = realpath(dirname(__FILE__) . '/templates');
      CRM_Core_Region::instance('page-body')->add(
        array(
          'template' => "{$templatePath}/CRM/Gdpr/Event/ThankYou.tpl"
        )
      );
      //amend communication preference link/embed form in thank you page
      CRM_Gdpr_CommunicationsPreferences_Utils::commsPreferenceInThankyouPage($cid, $form, 'ContributionPage');
    }
  }
  if ($formName == 'CRM_Mailing_Form_Subscribe') {
    $settings = CRM_Gdpr_CommunicationsPreferences_Utils::getSettings();
    $settings = $settings[CRM_Gdpr_CommunicationsPreferences_Utils::SETTING_NAME];
    if (!empty($settings['use_as_mailing_subscribe'])) {
      $nullRef = NULL;
      $cs = CRM_Utils_Request::retrieve('cs', 'String', $nullRef, FALSE, NULL, 'GET');
      $cid = CRM_Utils_Request::retrieve('cid', 'Int', $nullRef, FALSE, NULL, 'GET');
      $urlParams = array('reset' => 1);
      if ($cid && $cs) {
        $urlParams['cid'] = $cid;
        $urlParams['cs'] = $cs;
      }
      $path = 'civicrm/gdpr/comms-prefs/update';
      $commsPrefsUrl = CRM_Utils_System::url($path, $urlParams, TRUE, NULL, TRUE, TRUE);
      CRM_Utils_System::redirect($commsPrefsUrl);
    }
  }
}

/**
 * Implements hook_civicrm_postProcess().
 */
function gdpr_civicrm_postProcess($formName, $form) {
  if ($formName == 'CRM_Contribute_Form_Contribution_Confirm') {
    $contact_id = $form->_contactID;
    $contribution_page_id = $form->_id;
    if ($contact_id && $contribution_page_id) {
      $tc = new CRM_Gdpr_SLA_ContributionPage($contribution_page_id);
      if ($tc->isEnabled(TRUE)) {
        $tc->recordAcceptance($contact_id);
        CRM_Gdpr_SLA_Utils::recordSLAAcceptance($contact_id);
      }
    }
  }
}


/**
 * Implements hook_civicrm_post().
 */
function gdpr_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  // Create activity for event Terms and Conditions.
  if ($op == 'create' && $objectName == 'Participant') {
    if (!empty($objectRef->event_id) && !empty($objectRef->contact_id)) {
      // Acceptance not made on behalf of another.
      if (empty($objectRef->registered_by_id)) {
        $tc = new CRM_Gdpr_SLA_Event($objectRef->event_id);
        $isRegisterForm = 'civicrm/event/register' == CRM_Utils_System::currentPath();
        if ($isRegisterForm && $tc->isEnabled(TRUE)) {
          CRM_Gdpr_SLA_Utils::recordSLAAcceptance($objectRef->contact_id);
          $tc->recordAcceptance($objectRef->contact_id);
        }
      }
    }
  }
}

/*
 * Implements hook_civicrm_tabset().
 */
function gdpr_civicrm_tabset($tabsetName, &$tabs, $context) {
  //check if the tabset is Contact Summary Page
  if ($tabsetName == 'civicrm/contact/view') {
    $contactId = $context['contact_id'];
    _gdpr_addGDPRTab($tabs, $contactId);
  }
  elseif ($tabsetName == 'civicrm/event/manage' && !empty($context['event_id'])) {
    _gdpr_addTermsConditionsTab($tabs, 'event', $context['event_id']);
  }
  elseif ($tabsetName == 'civicrm/admin/contribute' && !empty($context['contribution_page_id'])) {
    _gdpr_addTermsConditionsTab($tabs, 'contribution_page', $context['contribution_page_id']);
  }
}


/**
 * Implements hook_civicrm_exportIds().
 */
function gdpr_civicrm_exportIds($ids, $componentTable, $exportMode) {
  switch ($exportMode) {
    case CRM_Export_Form_Select::CONTACT_EXPORT:
      $session = CRM_Core_Session::singleton();
      $loggedUserID = $session->get('userID');
      $activityTypeId = CRM_Gdpr_Activity::contactExportedTypeId();
      $query = "SELECT contact_id FROM {$componentTable}";
      $dao = CRM_Core_DAO::executeQuery($query);
      while ($dao->fetch()) {
        $contactId = $dao->contact_id;
        $params = [
          'sequential' => 1,
          'source_record_id' => $contactId,
          'source_contact_id' => $loggedUserID,
          'activity_type_id' => $activityTypeId,
          'activity_date_time' => date('YmdHis'),
          'status_id' => 'Completed',
          'api.ActivityContact.create' => [
            'activity_id' => '$value.id',
            'contact_id' => $contactId,
            'record_type_id' => 3,
          ]
        ];
        CRM_Gdpr_Utils::CiviCRMAPIWrapper('Activity', 'create', $params);
      }
      break;

    case CRM_Export_Form_Select::ACTIVITY_EXPORT:
      CRM_Core_Error::debug_var('$ids', $ids);
      $activityTypeId = CRM_Gdpr_Activity::activityExportedTypeId();
      break;

    case CRM_Export_Form_Select::CONTRIBUTE_EXPORT:
      $activityTypeId = CRM_Gdpr_Activity::contributionExportedTypeId();
      break;
  }
}

/**
 * Add a Terms & Conditions tab for Event or Contribution Page.
 */
function _gdpr_addTermsConditionsTab(&$tabs, $entityType, $id) {
  switch ($entityType) {
    case 'event' :
      $urlParams = array(
        'path' => 'civicrm/event/manage/terms_conditions',
        'qs' => 'reset=1&id=' . $id,
      );
    break;

    case 'contribution_page' :
      $urlParams = array(
        'path' => 'civicrm/admin/contribute/terms_conditions',
        'qs' => 'reset=1&action=update&id=' . $id,
      );
    break;

    default:
      return;
  }

  $url = CRM_Utils_System::url($urlParams['path'], $urlParams['qs']);
  $tabs['terms_conditions'] = array(
    'title' => E::ts('Terms &amp; Conditions'),
    'url' => $url,
    'active' => TRUE,
    'class' => 'ajaxForm',
  );
}

/*
 * Add a tab to show group subscription
 */
function gdpr_civicrm_tabs(&$tabs, $contactID) {
  if (_gdpr_isCiviCRMVersion47()) {
    return;
  }
  if (CRM_Core_Permission::check('access GDPR')) {
    _gdpr_addGDPRTab($tabs, $contactID);
  }
}

function _gdpr_addGDPRTab(&$tabs, $contactID) {
  $url = CRM_Utils_System::url('civicrm/gdpr/view/tab', "reset=1&cid={$contactID}");
  $tabs[] = array( 'id'    => 'gdprTab',
    'url'   => $url,
    'title' => E::ts('GDPR'),
    'weight' => 300,
    'class'  => 'livePage',
  );
}

/**
 * Checks if civicrm version is 4.7
 *
 * @return mixed
 */
function _gdpr_isCiviCRMVersion47(){
  return version_compare(CRM_Utils_System::version(), '4.7', '>');
}
/**
 * Add navigation for GDPR Dashboard
 *
 * @param $params associated array of navigation menus
 */
function gdpr_civicrm_navigationMenu( &$params ) {
  // get the id of Contacts Menu
  $contactsMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Contacts', 'id', 'name');
  // skip adding menu if there is no contacts menu
  if ($contactsMenuId) {
    // get the maximum key under contacts menu
    $maxKey = max( array_keys($params[$contactsMenuId]['child']));
    $params[$contactsMenuId]['child'][$maxKey+1] =  array (
      'attributes' => array (
        'label'      => 'GDPR Dashboard',
        'name'       => 'GDPR Dashboard',
        'url'        => 'civicrm/gdpr/dashboard?reset=1',
        'permission' => 'access GDPR',
        'operator'   => NULL,
        'separator'  => FALSE,
        'parentID'   => $contactsMenuId,
        'navID'      => $maxKey+1,
        'active'     => 1
      )
    );
  }
}

/**
 * implementation of hook_civicrm_token
 */
function gdpr_civicrm_tokens( &$tokens ){
  //Keeping this token only to sustain the old tokens otherwise, 
  //the token 'CommunicationPreferences' can be used in all places  
  $tokens['contact']['contact.comm_pref_supporter_url'] = E::ts("Communication Preferences URL");
  $tokens['contact']['contact.comm_pref_supporter_link'] = E::ts("Communication Preferences Link");
  $tokens['CommunicationPreferences'] = array(
    'CommunicationPreferences.comm_pref_supporter_url' => E::ts("Communication Preferences URL (Bulk Mailing)"),
    'CommunicationPreferences.comm_pref_supporter_link' => E::ts("Communication Preferences Link (Bulk Mailing)"),
  );
}

/**
 * implementation of hook_civicrm_tokenValues
 */
function gdpr_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
  if (!empty($tokens['contact']) OR !empty($tokens['CommunicationPreferences'])) {
    /*
    THIS CHANGE IS ONLY FOR ACTIONSCHEDULE (SEND EMAIL USING SCHEDULE REMINDER)

    we have mentioned the contact custom tokens in token hook. 
    so whenever replaceHookToken called its trying replace Category 'Contact' tokens 
    (which cause null values in template result).

    This is not happening when send email via contact summary or mailing, 
    because all other work flow to send emails are builds the contact details array 
    before replaceHookToken fired using this function CRM_Utils_Token::getTokenDetails().
    
    When Action schedule send email, Contact Details get from BAO API Query 
    which doesn't return some default contact values ex: email_greetings, 
    postal greetings etc. So build the contact details array with all default values.
    
    This changes is needed only when we have $tokens['contact']. Keeping this oly to sustain 
    the old token. 
    IN FUTURE or V3.0, WE CAN REMOVE THIS CHANGE ALONG WITH CONTACT CUSTOM TOKEN ABOVE.
    */
    if ($context == 'CRM_Core_BAO_ActionSchedule') {
      list($values) = CRM_Utils_Token::getTokenDetails($cids,
        array(),
        FALSE,
        FALSE
      );
    }
    foreach ($cids as $cid) {
      $commPrefURL = CRM_Gdpr_CommunicationsPreferences_Utils::getCommPreferenceURLForContact($cid);
      $link = sprintf("<a href='%s' target='_blank'>%s</a>",$commPrefURL, E::ts('Communication Preferences'));
      $values[$cid]['contact.comm_pref_supporter_url'] = $commPrefURL;
      $values[$cid]['contact.comm_pref_supporter_link'] = html_entity_decode($link);

      //For Bulk Mailing
      $values[$cid]['CommunicationPreferences.comm_pref_supporter_url'] = $commPrefURL;
      $values[$cid]['CommunicationPreferences.comm_pref_supporter_link'] = html_entity_decode($link);
    }
  }
}

/**
 * implementation of hook_civicrm_summaryActions
 */
function gdpr_civicrm_summaryActions( &$actions, $contactID ) {
  $actions['comm_pref'] = array(
    'title' => 'Communication Preferences Link',
    //need a weight parameter here, Contact BAO looking for weight key and returning notice message. 
    'weight' => 60, 
    'ref' => 'comm_pref',
    'key' => 'comm_pref',
    'href' => CRM_Gdpr_CommunicationsPreferences_Utils::getCommPreferenceURLForContact($contactID, TRUE),
  );  
}

function gdpr_civicrm_permission(&$permissions) {
  $prefix = E::ts('GDPR') . ': ';
  $version = CRM_Utils_System::version();
  if (version_compare($version, '4.6.1') >= 0) {
    $permissions += array(
      'access GDPR' => array(
        $prefix . E::ts('access GDPR'),
        E::ts('View GDPR related information'),
      ),
      'forget contact' => array(
        $prefix . E::ts('forget contact'),
        E::ts('Anonymize contacts'),
      ),
      'administer GDPR' => array(
        $prefix . E::ts('administer GDPR'),
        E::ts('Manage GDPR settings'),
      ),
    );
  }
  else {
    $permissions += array(
      'access GDPR' => $prefix . E::ts('access GDPR'),
      'forget contact' => $prefix . E::ts('forget contact'),
      'administer GDPR' => $prefix . E::ts('administer GDPR'),
    );
  }
}

/**
 * Implements hook_civicrm_pageRun()
 */
function gdpr_civicrm_pageRun( &$page ) {
  $pageName = $page->getVar('_name');
  if($pageName == 'CRM_Contact_Page_View_Summary') {
    $cid = $page->getVar('_contactId');
    $templatePath = realpath(dirname(__FILE__).'/templates');
    CRM_Core_Region::instance('page-body')->add(
      array(
        'template' => "{$templatePath}/CRM/Gdpr/Page/ContactSummary.tpl"
      )
    );
    $accept_activity = CRM_Gdpr_SLA_Utils::getContactLastAcceptance($cid);
    $accept_date = NULL;
    if (!empty($accept_activity['activity_date_time'])) {
      $accept_date = date('d/m/Y', strtotime($accept_activity['activity_date_time']));
      $page->assign('lastAcceptanceDate', $accept_date);
    }
  }
  if ($pageName == 'CRM_Event_Page_EventInfo') {
    CRM_Core_Resources::singleton()->addStyleFile('uk.co.vedaconsulting.gdpr', 'css/gdpr.css');
  }
}
