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

    // Forget me action
    $this->add('text', 'forgetme_name', ts('Forgetme contact name'));

    $this->add(
      'text',
      'activity_period',
      ts('Period'),
      array('size' => 4),
      TRUE
    );

    $months = range(6, 60, 6);
    $slaPeriodOptions = array_combine($months, $months);
    // SLA Acceptance settings.
    $this->add(
      'text',
      'sla_page_title',
      ts('Page title')
    );
    $this->add(
      'select',
      'sla_period',
      ts('Acceptance period (months)'),
      array('' => ts('- select -')) + $slaPeriodOptions, // list of options
      TRUE,
      array('class' => 'crm-select2')
    );
    $this->add(
      'file',
      'sla_tc_upload',
      ts('Data Policy file')
    );
    $this->add(
      'checkbox',
      'sla_tc_new_version',
      ts('This is a new version of the document.')
    );
    $this->add(
      'hidden',
      'sla_tc_version'
    );
    $this->add(
      'text',
      'sla_link_label',
      ts('Link Label')
    );
    $this->add(
      'hidden',
      'sla_tc'
    );
    $this->add(
      'text',
      'sla_checkbox_text',
      ts('Checkbox text')
    );
    $this->add(
      'textarea',
      'sla_agreement_text',
      ts('Introductory text'),
      array('cols' => 50)
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    $bare_defaults = array(
      'sla_link_label' => ts('Data Policy'),
      'sla_checkbox_text' => ts('I accept the Data Policy.'),
      'sla_tc_new_version' => FALSE,
    );

    // Get GDPR settings, for setting defaults
    $defaults = CRM_Gdpr_Utils::getGDPRSettings();
    array_merge($bare_defaults, $defaults);

    // Set defaults
    if (!empty($defaults)) {
      $this->setDefaults($defaults);
    }
    // Pass on variables to link to terms and conditions.
    if (!empty($defaults['sla_tc'])) {
      $sla_tc['url'] = $defaults['sla_tc'];
      $sla_tc['name'] = basename($defaults['sla_tc']);
      $this->assign('sla_tc_current', $sla_tc);
      $version = !empty($defaults['sla_tc_version']) ? $defaults['sla_tc_version'] : 1;
      $this->assign('sla_tc_version', $version);
      $updated = !empty($defaults['sla_tc_updated']) ? $defaults['sla_tc_updated'] : '';
      if ($updated) {
        $this->assign('sla_tc_updated', $updated);
      }
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
    $settings['forgetme_name'] = $values['forgetme_name'];
    $settings['sla_period'] = $values['sla_period'];
    $settings['sla_prompt'] = !empty($values['sla_prompt']) ? 1 : 0;
    $settings['sla_agreement_text'] = $values['sla_agreement_text'];
    $settings['sla_link_label'] = $values['sla_link_label'];
    $settings['sla_page_title'] = $values['sla_page_title'];
    $settings['sla_checkbox_text'] = $values['sla_checkbox_text'];
    $uploadFile = $this->saveTCFile();
    if ($uploadFile) {
      $settings['sla_tc'] = $uploadFile;
    } else {
      $settings['sla_tc'] = $values['sla_tc'];
    }
    if (!empty($values['sla_tc_new_version'])) {
      $version = (isset($values['sla_tc_version']) && is_numeric($values['sla_tc_version']) ? $values['sla_tc_version'] + 1 : 1);
      $settings['sla_tc_version'] = $version;
      $settings['sla_tc_updated'] = date('Y-m-d');
    }
    $settingsStr = serialize($settings);

    // Save the settings
    CRM_Core_BAO_Setting::setItem($settingsStr, CRM_Gdpr_Constants::GDPR_SETTING_GROUP, CRM_Gdpr_Constants::GDPR_SETTING_NAME);

    $message = "GDPR settings saved.";
    $url = CRM_Utils_System::url('civicrm/gdpr/dashboard', 'reset=1');
    CRM_Core_Session::setStatus($message, 'GDPR', 'success');
    CRM_Utils_System::redirect($url);
    CRM_Utils_System::civiExit();
  }

  public static function formRule($params, $files) {

  }

  /**
   * Save an uploaded Terms and Conditions file.
   *  @return string 
   *    Path of the saved file.
   */
  private function saveTCFile() {
    $fileElement = $this->_elements[$this->_elementIndex['sla_tc_upload']];
    if ($fileElement && !empty($fileElement->_value['name'])) {
      $config = CRM_Core_Config::singleton();
      $publicUploadDir = $config->imageUploadDir;
      $fileInfo = $fileElement->_value;
      $pathInfo = pathinfo($fileElement->_value['name']);
      if (empty($pathInfo['filename'])) {
        return;
      }
      // If necessary add a delta to the file name to avoid writing over an existing file.
      $delta = 0;
      $fileName = '';
      while (!$fileName) {
        $suffix = $delta ? '-' . $delta : '';
        $testName = $pathInfo['filename'] . $suffix . '.' . $pathInfo['extension'];
        if (!file_exists($publicUploadDir . '/' . $testName)) {
          $fileName = $testName;
        }
        $delta++;
      }
      // Move to public uploads directory and create file record.
      // This will be referenced in Activity custom field.
      $saved = $fileElement->moveUploadedFile($publicUploadDir,$fileName);
      if ($saved) {
        return $this->getFileUrl($publicUploadDir . $fileName);
      }
    }
  }

  /**
   * Gets the url of an uploaded file from its filesystem path.
   *
   * @param string $path
   *
   * return string
   */
  private function getFileUrl($path) {
    $config = CRM_Core_Config::singleton();
    $cmsRoot = $config->userSystem->cmsRootPath();
    if (0 === strpos($path, $cmsRoot)) {
      $url = substr($path, strlen($cmsRoot));
      return $url;
    }
  }
}
