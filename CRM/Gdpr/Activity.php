<?php

class CRM_Gdpr_Activity {

  const CONTACT_EXPORTED = 'Contact exported';
  const ACTIVITY_EXPORTED = 'Activity exported';
  const CONTRIBUTION_EXPORTED = 'Contribution exported';

  /**
   * Get activity type id for Contact exported
   *
   * @return int|mixed
   * @throws \CiviCRM_API3_Exception
   */
  public static function contactExportedTypeId() {
    $key = __CLASS__ . __FUNCTION__;
    $cache = Civi::cache()->get($key);
    if (!isset($cache)) {
      $id = self::activityType(self::CONTACT_EXPORTED);
      Civi::cache()->set($key, $id);
      return $id;
    }
    return $cache;
  }

  /**
   * Get activity type id for Activity exported
   *
   * @return int|mixed
   * @throws \CiviCRM_API3_Exception
   */
  public static function activityExportedTypeId() {
    $key = __CLASS__ . __FUNCTION__;
    $cache = Civi::cache()->get($key);
    if (!isset($cache)) {
      $id = self::activityType(self::ACTIVITY_EXPORTED);
      Civi::cache()->set($key, $id);
      return $id;
    }
    return $cache;
  }

  /**
   * Get activity type id for Activity exported
   *
   * @return int|mixed
   * @throws \CiviCRM_API3_Exception
   */
  public static function contributionExportedTypeId() {
    $key = __CLASS__ . __FUNCTION__;
    $cache = Civi::cache()->get($key);
    if (!isset($cache)) {
      $id = self::activityType(self::CONTRIBUTION_EXPORTED);
      Civi::cache()->set($key, $id);
      return $id;
    }
    return $cache;
  }

  /**
   * Get or create activity type
   *
   * @param $name
   *
   * @return int
   * @throws \CiviCRM_API3_Exception
   */
  private static function activityType($name) {
    return self::set('activity_type', $name);
  }

  /**
   * Get or create new option value.
   *
   * @param string $optionGroupName
   * @param string $name
   * @param array $options
   *
   * @return int
   * @throws \CiviCRM_API3_Exception
   */
  private static function set($optionGroupName, $name, $options = []) {
    $params = array(
      'sequential' => 1,
      'option_group_id' => $optionGroupName,
      'name' => $name,
    );
    $result = civicrm_api3('OptionValue', 'get', $params);
    if ($result['count'] == 0) {
      $params['is_active'] = 1;
      $params['title'] = $name;
      $params = array_merge($params, $options);
      $result = civicrm_api3('OptionValue', 'create', $params);
    }
    return $result['values'][0]['value'];
  }

}
