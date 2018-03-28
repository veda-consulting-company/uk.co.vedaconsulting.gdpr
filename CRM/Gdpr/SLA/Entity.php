<?php
/**
 * @file
 *  Base class for Terms and Conditions relating to a particular entity.
 */
class CRM_Gdpr_SLA_Entity {

  protected $id = NULL;

  protected $entity = array();

  protected $type = '';

  protected $customGroup = '';

  protected $customFields = array();

  protected $activityType = '';

  protected $activityCustomGroup = '';


  /**
   * Determines if this entity has terms and conditions enabled.
   */
  public function isEnabled() {
     return $this->getValue('Enable_terms_and_Conditions_Acceptance');
  }

  public function getCheckboxPosition() {
    return $this->getValue('Checkbox_Position');
  }

  public function getCheckboxText() {
    return $this->getValue('Checkbox_text');
  }

  public function getIntroduction() {
    return $this->getValue('Introduction');
  }

  public function getUrl() {
    return $this->getValue('Terms_and_Conditions_File');
  }

  public function getLinks() {
    $links = array();
    $url = $this->getUrl();
    $label = $this->getValue('Link_Label');
    if ($url) {
      $links['event'] = array(
        'url' => $url,
        'label' => $label,
      );
    }
    $global_link_url = CRM_Gdpr_SLA_Utils::getTermsConditionsUrl();
    $global_link_label = CRM_Gdpr_SLA_Utils::getLinkLabel();
    $global_checkbox_text = CRM_Gdpr_SLA_Utils::getCheckboxText(); 
    if ($global_link_url) {
    $links['global'] = array(
        'url' => $global_link_url,
        'label' => $global_link_label,
      );
    }
    return $links;
  }

  public function getValue($field_name) {
    $fields = $this->getCustomFields($this->customGroup);
    $field = !empty($fields[$field_name]) ? $fields[$field_name] : array();
    if (!$field) {
      return;
    }
    $fid = $field['id'];
    $key = 'custom_' . $fid;
    $entity = $this->getEntity();
    if ($fid && $entity && isset($entity[$key])) {
      return $entity[$key];
    }
  }

  protected function getCustomFields($group) {
    if (empty($this->customFields[$group])) {
      $result = civicrm_api3('CustomField', 'get', array(
        'sequential' => 1,
        'custom_group_id' => $group,
      ));
      if (!empty($result['values'])) {
        $fields = array();
        //Index them by name for easier lookups.
        foreach ($result['values'] as $value) {
          $fields[$value['name']] = $value;
        }
        $this->customFields[$group] = $fields;
      }
    }
    return $this->customFields[$group];
  }

  function __construct($id, $type) {
    $this->id = $id;
    $this->type = $type;
  }

  public function getEntity() {
    if (!$this->entity) {
      $params = array(
        'sequential' => FALSE,
        'id' => $this->id,
      );
      $result = civicrm_api3($this->type, 'get', $params);
      if (!empty($result['values'][$this->id])) {
        $this->entity = $result['values'][$this->id];
      }
    }
    return $this->entity;
  }

  public function recordAcceptance($contactId = NULL) {
    $contactId = $contactId ? $contactId : CRM_Core_Session::singleton()->getLoggedInContactID();
    $fields = $this->getCustomFields($this->activityCustomGroup);
    $url = $this->getUrl();
    $entity = $this->getEntity();
    $source = $this->type . ': ' .  $entity['title'] . ' (' . $entity['id'] . ')';
    $params = array(
      'source_contact_id' => $contactId,
      'target_id' => $contactId,
      'subject' => $this->type . ' Terms and Conditions accepted',
      'status_id' => 'Completed',
      'activity_type_id' => $this->activityType,
      'custom_' . $fields['Terms_Conditions']['id'] => $url,
      'custom_' . $fields['Source']['id'] => $source,
    );
    $result = civicrm_api3('Activity', 'create', $params);
  }
}
