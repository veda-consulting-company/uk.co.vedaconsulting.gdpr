<?php

use CRM_Gdpr_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Gdpr_Form_ContributionPage_TermsAndConditions extends CRM_Contribute_Form_ContributionPage {
  /**
   * We use a custom field group to store values for this event.
   */
  private $groupId = NULL;

  private $uploadElement = 'Terms_and_Conditions_File_Upload';

  public function preProcess() {
    parent::preProcess();
    $this->_type = $this->_cdType = 'ContributionPage';
    $this->_groupCount = 1;
    $this->_subName = '';
    $this->_id = $this->id = $this->_entityId = CRM_Utils_Request::retrieve('id', 'Positive');
    $group_id = $this->getGroupId();
    CRM_Custom_Form_CustomData::setGroupTree($this, '', $group_id, FALSE);
  }
  
  /**
   * Get the Id of the custom group for terms and conditions.
   */
  private function getGroupId() {
    if (!$this->groupId)
      $result = civicrm_api3('CustomGroup', 'get', array(
        'sequential' => 1,
        'name' => 'Contribution_Page_terms_and_conditions',
      ));
    if (!empty($result['values'][0])) {
      $this->groupId = $result['values'][0]['id'];
    }
    return $this->groupId;
  }
  
  /**
   * Gets Custom field from the group tree by name.
   */
  private function getFieldByName($field_name) {
    static $fields = array();
    if (empty($fields)) {
      $tree = $this->_groupTree;
      $group = reset($tree);
      if (!empty($group['fields'])) {
        foreach ($group['fields'] as $fid => $field) {
          $fields[$field['name']] = $field;
        }
      }
    }
    return $fields[$field_name] ? $fields[$field_name] : array();
  }

  /**
   * Gets the element for a custom field by the name of the field.
   *
   * @param
   *  Name of the custom field. Note the group is already known, so uniqueness
   *  is preserved.
   */
  private function getElementByFieldName($field_name) {
    $field = $this->getFieldByName($field_name);
    $element = array();
    if ($field) {
      $element = $this->getElement($field['element_name']);
    }
    return $element;
  }

  /**
   * Set defaults.
   *
   * @return array
   */
  public function setDefaultValues() {
    $defaults = CRM_Custom_Form_CustomData::setDefaultValues($this);
    // Fill in missing values with defaults from the field settings.
    $tree = $this->_groupTree;
    $group = reset($tree);
    if (!empty($group['fields'])) {
      foreach ($group['fields'] as $fid => $field) {
        if (empty($defaults[$field['element_name']]) && !empty($field['default_value'])) {
          $fields[$field['name']] = $field;
          $defaults[$field['element_name']] = $field['default_value'];
        }
      }
    }
    return $defaults;
  }

  /**
   * Build quick form.
   */
  public function buildQuickForm() {
    CRM_Core_BAO_CustomGroup::buildQuickForm($this, $this->_groupTree);
    // Add a file upload element for the terms and conditions file.
    $tc_field = $this->getFieldByName('Terms_and_Conditions_File');
    if ($tc_field) {
      $upload_name = $this->uploadElement;
      $upload = $this->add(
        'file',
        $upload_name,
        'Terms &amp; Conditions File'
      );
      $tc_current = array(
        'url' => $tc_field['element_value'],
        'label' => basename($tc_field['element_value']),
      );
      $tc_value = $tc_field['element_value'];
      // Provide some variables so the template can display the upload field in
      // place of the link field.
      $this->assign('terms_conditions_link_element_name', $tc_field['element_name']);
      $this->assign('terms_conditions_file_element_name', $upload_name);
      $this->assign('terms_conditions_current', $tc_current);
    }
    // Set the size of text elements.
    foreach ($this->_elements as $element) {
      if ($element->getType() == 'text') {
        $element->setSize(60);
      }
    }
    $this->assignDescriptions();
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $this->saveValues();
    
    // Calling parent::endPostProcess() will not direct to the right url since
    // this form is not included in the ContributionPage State Machine.
    CRM_Core_Session::setStatus(ts("Terms & Conditions information has been saved."), ts('Saved'), 'success');
    $this->postProcessHook();
    CRM_Utils_System::redirect(CRM_Utils_System::url("civicrm/admin/contribute/terms_conditions",
    "action=update&reset=1&id={$this->_id}"
    ));
  }

  protected function saveValues() {
    $params = $this->controller->exportValues($this->_name);
    $file_url = $this->saveTCFile();
    if ($file_url) {
    $tc_field = $this->getFieldByName('Terms_and_Conditions_File');
      $params[$tc_field['element_name']] = $file_url;
    }
    $params['custom'] = CRM_Core_BAO_CustomField::postProcess($params,
      $this->_id,
      'ContributionPage'
    );
    CRM_Core_BAO_CustomValueTable::store($params['custom'], 'civicrm_contribution_page', $this->id);

  }

  /**
   * Assigns template variable descriptions with the preHelp text of the field.
   */
  private function assignDescriptions() {
    $tree = $this->_groupTree;
    $group = reset($tree);
    $descriptions = array();
    if (!empty($group['fields'])) {
      foreach ($group['fields'] as $fid => $field) {
        $descriptions[$field['element_name']] = !empty($field['help_pre']) ?  $field['help_pre'] : '';
      }
    }
    $this->assign('descriptions', $descriptions);
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

  /**
   * Save an uploaded Terms and Conditions file.
   *  @return string
   *    Path of the saved file.
   */
  private function saveTCFile() {
    $fileElement = $this->getElement($this->uploadElement);
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
