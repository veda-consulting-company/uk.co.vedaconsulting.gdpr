<?php

use CRM_Gdpr_ExtensionUtil as E;
use CRM_Gdpr_CommunicationsPreferences_Utils as U;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Gdpr_Form_CommunicationsPreferences extends CRM_Core_Form {
  /**
   * API values of public groups.
   */
  protected $groups = array();

  protected $groupContainerNames = array();

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Communications Preferences'));
    $channels = U::getChannelOptions();
    $text_area_attributes = array('cols' => 60, 'rows' => 5);
    $this->add(
      'text',
      'page_title',
      ts('Page title'),
      array('size' => 40)
    );
    $this->add(
      'textarea',
      'page_intro',
      ts('Introduction'),
      $text_area_attributes
    );
    $this->add(
      'select',
      'profile',
      ts('Include Profile'),
      U::getProfileOptions()
    );
    $descriptions['profile'] = ts('Include a profile so the user can identify and check their details are up-to-date. It should  include a primary email address field.');

    // Let the template know about elements in this section.
    $page_elements = array(
      'page_title',
      'page_intro',
      'profile'
    );
    $this->assign('page_elements', $page_elements);
    // Comms prefs channels
    $this->add(
      'advcheckbox',
      'enable_channels',
      ts('Enable Channels'),
      '',
      false,
      array(
        'data-toggle' => '.channels-wrapper',
        'class' => 'toggle-control'
      )
    );
    $this->add(
      'textarea',
      'channels_intro',
      ts('Introduction'),
      $text_area_attributes
    );
    $channel_group = $this->add(
      'group',
      'channels',
      ts('Users can opt-in to these channels')
    );
    foreach ($channels as $channel => $label) {
      $elem = HTML_QuickForm::createElement(
        'checkbox',
        'enable_' . $channel,
        $label,
        $label,
        array('class' => 'enable-channel')
      );
      $channel_checkboxes[] = $elem;
    }
    $channel_group->setElements($channel_checkboxes);
    $channels_elements = array(
      'channels_intro',
      'channels',
    );
    $this->assign('channels_elements', $channels_elements);
    $this->add(
      'checkbox',
      'enable_groups',
      ts('Allow users to opt-in to mailing groups.'),
      '',
      false,
      array(
        'data-toggle' => '.groups-wrapper',
        'class' => 'toggle-control'
      )
    );
    $this->add(
      'text',
      'groups_heading',
      ts('Heading for the groups section'),
      array('size' => 40)
    );
    $this->add(
      'textarea',
      'groups_intro',
      ts('Introduction or description for this section.'),
      $text_area_attributes
    );
    $groups = $this->getGroups();
    foreach ($groups as $group) {
      $container_name = 'group_' . $group['id'];
      $this->groupContainerNames[] = $container_name;

      $group_container = $this->add(
        'group',
        $container_name,
        $group['title']
      );
      $group_elems = array();
      $group_elems[] = HTML_QuickForm::createElement(
        'advcheckbox',
        'group_enable',
        'Enable',
        '',
        array(
         'data-group-id' => $group['id'],
        )
      );
      $group_elems[] = HTML_QuickForm::createElement(
        'text',
        'group_title',
        $group['title'],
        array('size' => 30)
      );
      $group_elems[] = HTML_QuickForm::createElement(
        'textarea',
        'group_description',
        'Description',
         array('cols' => 40, 'rows' => 6)
      );
      foreach ($channels as $key => $label) {
        $group_elems[] = HTML_Quickform::createElement(
          'advcheckbox',
          $key,
          $label,
          $label
        );
      }
      $group_container->setElements($group_elems);
      $group_containers[] = $container_name;
    }
    $this->addRadio(
      'completion_redirect',
      'On completion',
      array(1 => 'Redirect to another page', 0 => 'Display a message on the form page.')
    );
    $this->add(
      'text',
      'completion_url',
      ts('Completion page'),
      array('size' => 50)
    );
    $descriptions['completion_url'] = ts('Add the a URL for a page to redirect the user after they complete the form. The page should already exist. The URL may be absolute (http://example.com/thank-you) or relative (thank-you), with no leading forward slash. Leave blank to redirect to the front page.');
    $this->add(
      'textarea',
      'completion_message',
      ts('Completion message'),
      $text_area_attributes
    );
    $descriptions['completion_message'] = ts('A message to display to the user after the form is submitted. ');
    // Let the template know about which fields belong in the groups section.
    $groups_elements = array(
      'groups_heading',
      'groups_intro',
    );
    $this->assign('descriptions', $descriptions);
    $this->assign('groups_elements', $groups_elements);
    $this->assign('group_containers', $group_containers);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
    ));
    $this->setDefaults($this->getDefaults());
    parent::buildQuickForm();
  }

  /**
   * Gets public groups.
   */
  function getGroups() {

    // Just a wrapper for the Utility function.
    if (!$this->groups) {
      $this->groups = U::getGroups();
    }
    return $this->groups;
  }

  public function postProcess() {
    $values = $this->exportValues();
    parent::postProcess();
    $groupContainers = $this->groupContainerNames;
    // Save values to settings except for groups.
    $settingsElements = array_diff($this->getRenderableElementNames(), $groupContainers);
    foreach ($settingsElements as $settingName) {
      if (isset($values[$settingName])) {
        $settings[$settingName] = $values[$settingName];
      }
    }
    $groupSettings = array();
    foreach ($groupContainers as $key) {
      if (isset($values[$key])) {
        $groupSettings[$key] = $values[$key];
      }
    }
    $save = array(
      U::SETTING_NAME => $settings,
      U::GROUP_SETTING_NAME => $groupSettings,
    );
    U::saveSettings($save);
    $url = CRM_Utils_System::url('civicrm/gdpr/dashboard', 'reset=1');
    CRM_Core_Session::setStatus('Settings Saved.', 'GDPR', 'success');
    CRM_Utils_System::redirect($url);
    CRM_Utils_System::civiExit();
  }

  public function getDefaults() {
    $settings = U::getSettings();
    $key = U::SETTING_NAME;
    $group_key = U::GROUP_SETTING_NAME;
    $form_defaults = array();
    // Flatten to fit the form structure.
    if (isset($settings[$key]) && isset($settings[$group_key])) {
      $form_defaults = array_merge($settings[$key], $settings[$group_key]);
    }
    return $form_defaults;
  }

  protected function getProfileOptions() {

  }
  public function addRules() {
    $this->addFormRule(array('CRM_Gdpr_Form_CommunicationsPreferences', 'validateRedirectUrl'));
  }

  /**
   * Validation callback for completion redirect url.
   */
  public function validateRedirectUrl($values) {
    $errors = array();
    if (!empty($values['completion_redirect'])) {
      if (empty($values['completion_url'])) {
        // This is okay, we will redirect to the home page.
      }
      else {
        $url = $values['completion_url'];
        $parsed_url = parse_url($url);
        $base_url = CIVICRM_UF_BASEURL;
        if (!empty($parsed_url['host']) && !empty($parsed_url['scheme'])) {
          $full_url = $url;
        }
        else {
          // Remove leading slash from base and trailing slash from path.
          if (0 === strpos($url, '/')) {
            $url = substr($url, 1);
          }
          $last_pos = strlen($base_url) -1;
          if (strrpos($base_url, '/') === $last_pos) {
            $base_url = substr($base_url, 0, $last_pos);
          }
          $full_url = $base_url . '/' . $url;
        }

        // We have been unable to construct a URL.
        if (!$full_url) {
          $errors['completion_url'] = ts('Invalid URL.');
        }
        elseif (function_exists('curl_init')) {
          // Test if the url exists.
          $ch  = curl_init();
          curl_setopt($ch, CURLOPT_URL, $full_url);
          curl_setopt($ch, CURLOPT_HEADER, TRUE);
          curl_setopt($ch, CURLOPT_NOBODY, TRUE);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,true);
          curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
          $result = curl_exec($ch);
          $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $code = trim($code);
          curl_close($ch);
          if ($code[0] != '2') {
            $errors['completion_url'] = ts('The completion URL does not belong to a valid page. Please check that an anonymous in user can access it.');
          }
        }
      }
    }
    return $errors;
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
