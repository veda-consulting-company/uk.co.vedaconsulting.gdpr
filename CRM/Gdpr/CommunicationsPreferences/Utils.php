<?php
use CRM_Gdpr_ExtensionUtil as E;

class CRM_Gdpr_CommunicationsPreferences_Utils {

  const SETTING_GROUP = 'GDPR_CommunicationsPreferences_Settings';
  const SETTING_NAME = 'gdpr_communications_preferences_settings';
  /* Setting name for group preferences */
  const GROUP_SETTING_NAME = 'gdpr_communications_preferences_group_settings';
  const COMM_PREF_OPTIONS = 'comm_pref_options';
  const COMM_PREF_ACTIVITY_TYPE = 'Update_Communication_Preferences';

  private static $groups = array();

  public static function getSettingsDefaults() {
    $settings[self::SETTING_NAME] = array(
      'page_title' => E::ts('Communication Preferences'),
      'page_intro' => E::ts('We want to ensure we are only sending you information that is of interest to you, in a way you are happy to receive.'),
      'enable_channels' => 1,
      'channels_intro' => E::ts('Please tell us how you would like us to keep in touch.'),
      'channels' => array(
        'enable_email' => 1,
        'enable_phone' => 1,
        'enable_post' => 1,
        'enable_sms' => 0,
      ),
      'enable_groups' => 0,
      'groups_heading' => E::ts('Interest groups'),
      'groups_intro' => E::ts('We want to continue to keep you informed about our work. Opt-in to the groups that interest you.'),
      'completion_message' => E::ts('Your communications preferences have been updated. Thank you.')
    );

    foreach (self::getGroups() as $group) {
      $group_values['group_enable'] = 0;
      foreach (array('title', 'description') as $key) {
        if (isset($group[$key])) {
          $group_values['group_' . $key] = $group[$key];
        }
      }
      $settings[self::GROUP_SETTING_NAME]['group_' . $group['id']] = $group_values;
    }
    return $settings;
  }

  /**
   * Get Communication Preferences settings.
   *
   * @param bool $use_defaults
   *  Whether to use default values if the settings do not exist.
   */
  public static function getSettings($use_defaults = TRUE) {
    $settings = array();
    $defaults = $use_defaults ? self::getSettingsDefaults() : array();
    foreach (array(self::SETTING_NAME, self::GROUP_SETTING_NAME) as $setting_name) {
      $serialized = CRM_Core_BAO_Setting::getItem(self::SETTING_GROUP, $setting_name);
      if (!$serialized && $use_defaults)  {
        $settings[$setting_name] = !empty($defaults[$setting_name]) ? $defaults[$setting_name] : array();
      }
      else {
        $settings[$setting_name] = $serialized ? unserialize($serialized) : array();
      }
    }
    if (!empty($settings[self::GROUP_SETTING_NAME])) {
      $settings[self::GROUP_SETTING_NAME] = self::pruneGroupSettings($settings[self::GROUP_SETTING_NAME]);
    }
    return $settings;
  }

  /**
   * Gets available contact profiles as an option array.
   *
   * @return array keyed by profile id, with value the profile label.
   */
  public static function getProfileOptions() {
    $types = array('Individual', 'Contact');
    
    //To get Profile with array of group type
    //using core method to get the profiles, because group_type has been imploded with (,) in database civicrm_uf_group. 
    //for eg group type can be Individual,Contact or Contact,Individual . so api didn't return all the result where profiles with group type Individual or Contact. (have to mention 'Individual,Contact')
    //we can use core method to get all profiles which are Individual or Contact or Both
    
    $profiles = CRM_Core_BAO_UFGroup::getProfiles($types);

    $options = array(0 => '-- Please select --') + $profiles;
    return $options;
  }


  /**
   * Remove group settings for groups that no longer exist or are no longer
   * public.
   */
  public static function pruneGroupSettings($group_settings) {
    $groups = self::getGroups();
    $prefix = 'group_';
    $pruned_settings = array();
    foreach ($group_settings as $key => $value) {
      $id = strpos($key, $prefix) === 0 ? substr($key, strlen($prefix)) : NULL;
      if ($id || is_numeric($id) || !empty($groups[$id])) {
        $pruned_settings[$key] = $value;
      }
    }
    return $pruned_settings;
  }

  /**
   * Save Communication Prefences settings.
   *
   * @param array $settings_array
   */
  public static function saveSettings($settings_array) {
    foreach (array(self::SETTING_NAME, self::GROUP_SETTING_NAME) as $setting_name) {
      if (isset($settings_array[$setting_name])) {
        $setting_serialized = serialize($settings_array[$setting_name]);
        CRM_Core_BAO_Setting::setItem($setting_serialized, self::SETTING_GROUP, $setting_name);
      }
    }
  }

  /**
   * Gets the public groups.
   *
   * @return array
   */
  public static function getGroups() {
    if (!self::$groups) {
      $params = array(
          'is_active' => 1,
          'visibility' => "Public Pages",
          // Key by id for convenience.
          'serialized' => FALSE,
      );
      $result = civicrm_api3('Group', 'get', $params);
      if (!empty($result['values'])) {
        self::$groups = $result['values'];
      }
    }
    return self::$groups;
  }

  /**
   * Sorts an array of groups according to their user-assigned weight.
   *
   * @param array $groups
   *  Group data from the api, keyed by id.
   *
   * @param array $sortBySettings
   *  Array keyed by a Communcations Preferences group setting, value can be
   *  either 'asc' or 'desc'.
   */
  public static function sortGroups($groups, $sortBySettings = array('group_weight' => 'asc')) {
    $settings = self::getSettings(FALSE);
    $group_settings = $settings[self::GROUP_SETTING_NAME];
    $defaults = array(
      'group_weight' => 0,
      'group_enable' => 0,
      'group_title' => '',
    );
    // Filter out arguments that we do not support.
    $sortKeys = array_intersect_key($sortBySettings, $defaults);
    foreach ($groups as $id => $grp) {
      if (!empty($group_settings['group_' . $id])) {
        $item = $group_settings['group_' . $id];
      }
      else {
        $item = $defaults;
      }
      $groups[$id]['group_weight'] = $item['group_weight'];
      $groups[$id]['group_enable'] = $item['group_enable'];
      $groups[$id]['group_title'] = $item['group_title'];
    }
    uasort($groups, function($a, $b) use ($sortKeys) {
      foreach ($sortKeys as $key => $order) {
        if (is_numeric($a[$key]) && is_numeric($b[$key])) {
          $diff = $order == 'asc' ? $a[$key] - $b[$key] : $b[$key] - $a[$key];
        }
        elseif (is_string($a[$key]) && is_string($b[$key])) {
          $diff = $order == 'asc' ? strcmp($a[$key], $b[$key]) : strcmp($b[$key], $a[$key]);
        }
        if ($diff != 0) {
          return $diff;
        }
      }
      return $diff;
    });
    return $groups;
  }


  /**
   * Gets details of the last time a contact updated their communications
   * preferences.
   *
   * @param int $cid
   *  Contact Id.
   *
   * @return array
   *  Array of activity details or empty array.
   */
  public static function getLastUpdatedForContact($cid) {
    $return = array();
    if (!$cid) {
      return $return;
    }
    $result = civicrm_api3('Activity', 'get', array(
      'sequential' => 1,
      'activity_type_id' => "Update_Communication_Preferences",
      // 'source_contact_id' => $cid,
      //MV: Civi Older version doesn't return api value using source_contact_id. if we add target_contact_id then BAO query include activity contact table and filter out using params
      'target_contact_id' => $cid,      
      'options' => array('sort' => "id desc"),
    ));
    return !empty($result['values']) ? $result['values'][0] : $return;
  }

  /**
   * Get options for channels.
   * @return array
   */
  public static function getChannelOptions() {
    return $channels = array(
      'email' => E::ts('Email'),
      'phone' => E::ts('Phone'),
      'post' => E::ts('Post'),
      'sms' => E::ts('SMS'),
    );
  }

  public static function getCommunicationPreferenceMapper() {
    return array(
      'email' => array(
        'UNKNOWN' => array(
          'do_not_email' => 'NULL',
        ),
        'YES' => array(
          'do_not_email' => 0,
          'is_opt_out' => 0,
        ),
        'NO' => array(
          'is_opt_out' => 1,
        ),
      ),
      'phone' => array(
        'UNKNOWN' => array(
          'do_not_phone' => 'NULL',
        ),
        'YES' => array(
          'do_not_phone' => 0,
        ),
        'NO' => array(
          'do_not_phone' => 1,
        ),
      ),
      'post' => array(
        'UNKNOWN' => array(
          'do_not_mail' => 'NULL',
        ),
        'YES' => array(
          'do_not_mail' => 0,
        ),
        'NO' => array(
          'do_not_mail' => 1,
        ),
      ),
      'sms' => array(
        'UNKNOWN' => array(
          'do_not_sms' => 'NULL',
        ),
        'YES' => array(
          'do_not_sms' => 0,
        ),
        'NO' => array(
          'do_not_sms' => 1,
        ),
      ),
    );
  }

  public static function getCommPreferenceURLForContact($cid, $skipContactIdInURL = FALSE){
    if (empty($cid)) {
      return NULL;
    }

    $urlParams = array(
      'reset' => 1,
      'cid'   => $cid,
      'cs'    => CRM_Contact_BAO_Contact_Utils::generateChecksum($cid),
    );

    //for sumamry hook, cid would add by default, we dont want duplicate URL params.
    if ($skipContactIdInURL) {
      unset($urlParams['cid']);
    }
    return CRM_Utils_System::url('civicrm/gdpr/comms-prefs/update', $urlParams, TRUE, NULL, TRUE, TRUE);
  }

  public static function addCommsPreferenceLinkInThankYouPage($cid, &$form, $entity = 'Event'){
    if (empty($cid)) {
      return;
    }

    $settings = CRM_Gdpr_CommunicationsPreferences_Utils::getSettings();
    $settings = $settings[CRM_Gdpr_CommunicationsPreferences_Utils::SETTING_NAME];

    //To display Communication Preference URL in Thank you page for event
    if (!empty($cid) && !empty($settings['enable_comm_pref_in_thankyou'])) {
      $commPrefURL = CRM_Gdpr_CommunicationsPreferences_Utils::getCommPreferenceURLForContact($cid);
      $form->assign('comm_pref_url', $commPrefURL);
      $form->assign('link_label', $settings['comm_pref_link_label']);
      $form->assign('entity', $entity);
    }
  }
}
