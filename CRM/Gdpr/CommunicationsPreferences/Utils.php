<?php

class CRM_Gdpr_CommunicationsPreferences_Utils {

  const SETTING_GROUP = 'GDPR_CommunicationsPreferences_Settings';
  const SETTING_NAME = 'gdpr_communications_preferences_settings';
  /* Setting name for group preferences */
  const GROUP_SETTING_NAME = 'gdpr_communications_preferences_group_settings';

  private static $groups = array();

  public static function getSettingsDefaults() {
    $settings[self::SETTING_NAME] = array(
      'page_title' => ts('Communication Preferences'),
      'page_intro' => ts('We want to ensure we are only sending you information that is of interest to you, in a way you are happy to receive.'),
      'enable_channels' => 1,
      'channels_intro' => ts('Please tell us how you would like us to keep in touch.'),
      'channels' => array(
        'enable_email' => 1,
        'enable_phone' => 1,
        'enable_post' => 1,
        'enable_sms' => 0,
      ),
      'enable_groups' => 0,
      'groups_heading' => ts('Interest groups'),
      'groups_intro' => ts('We want to continue to keep you informed about our work. Opt-in to the groups that interest you.'),
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
        $settings[$setting_name] = $defaults[$setting_name];
      }
      else {
        $settings[$setting_name] = $serialized ? unserialize($serialized) : array();
      }
    }
    $settings[self::GROUP_SETTING_NAME] = self::pruneGroupSettings($settings[self::GROUP_SETTING_NAME]);
    return $settings;
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
}
