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
	$cid = CRM_Core_Session::singleton()->getLoggedInContactID();
  if ($cid) {
    $session = CRM_Core_Session::singleton();
    $promptSet = CRM_Gdpr_SLA_Utils::isPromptForAcceptance();
    $key = CRM_Gdpr_SLA_Utils::getPromptFlagSessionKey();

    if ($promptSet && CRM_Gdpr_SLA_Utils::showFormIsFlagged()) {
      CRM_Gdpr_SLA_Utils::showForm();
    }
    else {
      $promptForSLA = $promptSet && CRM_Gdpr_SLA_Utils::isContactDueAcceptance($cid);
      if ($promptForSLA && !CRM_Gdpr_SLA_Utils::showFormIsUnflagged()) {
        CRM_Gdpr_SLA_Utils::flagShowForm();
      }
    }
  }
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
  require_once 'CRM/Gdpr/Utils.php';
  // Check whether the SLA Acceptance type exists already.
  $activity_params = array(
    'name' => 'SLA Acceptance',
    'label' => 'SLA Acceptance',
    'is_active' => 1,
    'option_group_id' => 'activity_type',
  );
  $activity_result = CRM_Gdpr_Utils::CiviCRMAPIWrapper('OptionValue', 'get', array(
  	'sequential' => 1,
  	'option_group_id' => 'activity_type',
  	'name' => $activity_params['name'],
	));
  // Create activity type
  if (empty($activity_result['count'])) {
    CRM_Gdpr_Utils::CiviCRMAPIWrapper('OptionValue', 'create', $activity_params);
  }
  // Import Custom Data.
  $ext_path = dirname(__FILE__);
  $xml_path = $ext_path . DIRECTORY_SEPARATOR . 'xml/CustomGroupData.xml';
  require_once 'CRM/Utils/Migrate/Import.php';
  $import = new CRM_Utils_Migrate_Import();
  $import->run($xml_path);
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
  if ($formName == 'CRM_Custom_Form_CustomDataByType' && $form->_type == 'Event') {
    if (!empty($form->_groupTree)) {
      // Remove custom fields for terms and conditions.
      // They will be included in the tab.
      foreach ($form->_groupTree as $gid => $group) {
        if ($group['name'] == 'Event_terms_and_conditions') {
          foreach($group['fields'] as $field) {
            $form->removeElement($field['element_name']);
          }
          unset($form->_groupTree[$gid]);
        }
      }
    }
  }
  if ($formName == 'CRM_Event_Form_Registration_Register') {
    // Add Terms and Conditions checkbox.
    _gdpr_add_event_form_terms_conditions($form);
  }
}

/**
 * Implements hook_civicrm_post().
 */
function gdpr_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  // Create activity for event Terms and Conditions.
  if ($op == 'create' && $objectName == 'Participant') {
    if (!empty($objectRef->event_id) && !empty($objectRef->contact_id)) {
      if (empty($objectRef->registered_by_id)) {
        $tc = new CRM_Gdpr_SLA_Event($objectRef->event_id);
        $isRegisterForm = 'civicrm/event/register' == CRM_Utils_System::getUrlPath();
        if ($tc->isEnabled() && $isRegisterForm) {
          CRM_Gdpr_SLA_Utils:: recordSLAAcceptance($objectRef->contact_id);
          $tc->recordAcceptance($objectRef->contact_id);
        }
      }
    }
  }
}

/**
 * Adds terms and conditions field to event registration form.
 */
function _gdpr_add_event_form_terms_conditions($form) {
  $tc = new CRM_Gdpr_SLA_Event($form->_eventId);
  if (!$tc->isEnabled()) {
    return;
  }
  $intro = $tc->getIntroduction();
  $links = $tc->getLinks();
  $position = $tc->getCheckboxPosition();
  $text = $tc->getCheckboxText();
  $form->add(
    'checkbox',
    'accept_tc',
    'Terms & Conditions',
    'I accept the Terms &amp; Conditions',
    TRUE,
    array('required' => TRUE)
  );
  $tc_vars = array(
    'element' => 'accept_tc',
    'links' => $links,
    'intro' => $intro,
    'position' => $position,
  );
  $form->assign('terms_conditions', $tc_vars);
  $template_path = realpath(dirname(__FILE__) . '/templates/CRM/Gdpr');
  CRM_Core_Region::instance('page-body')->add(array(
    'template' => "CRM/Gdpr/TermsConditionsField.tpl"
   ));
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
  elseif ($tabsetName == 'civicrm/event/manage') {
    _gdpr_addEventTab($tabs, $context);
  }
}

/**
 * Add a Terms & Conditions tab for Events.
 */
function _gdpr_addEventTab(&$tabs, $context) {
  if (empty($context['event_id'])) {
    return;
  }
  $eventID = $context['event_id'];
  $url = CRM_Utils_System::url('civicrm/event/manage/terms-conditions', "reset=1&id={$eventID}");
  $tabs['terms_conditions'] = array(
    'title' => ts('Terms &amp; Conditions'),
    'url' => $url,
    'active' => 1,
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

  _gdpr_addGDPRTab($tabs, $contactID);
}

function _gdpr_addGDPRTab(&$tabs, $contactID) {
  $url = CRM_Utils_System::url('civicrm/gdpr/view/tab', "reset=1&cid={$contactID}");
  $tabs[] = array( 'id'    => 'gdprTab',
    'url'   => $url,
    'title' => ts('GDPR'),
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
        'permission' => 'access CiviCRM',
        'operator'   => NULL,
        'separator'  => FALSE,
        'parentID'   => $contactsMenuId,
        'navID'      => $maxKey+1,
        'active'     => 1
      )
    );
  }
}
