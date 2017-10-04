<?php

require_once 'CRM/Core/Page.php';

class CRM_Gdpr_Utils {
  /**
   * CiviCRM API wrapper
   *
   * @param string $entity
   * @param string $action
   * @param string $params
   *
   * @return array of API results
   */
  public static function CiviCRMAPIWrapper($entity, $action, $params) {

    if (empty($entity) || empty($action) || empty($params)) {
      return;
    }

    try {
      $result = civicrm_api3($entity, $action, $params);
    }
    catch (Exception $e) {
      CRM_Core_Error::debug_log_message('CiviCRM API Call Failed');
      CRM_Core_Error::debug_var('CiviCRM API Call Error', $e);
      return;
    }

    return $result;
  }

  /**
   * Get all activity types
   *
   * @return array of activity types ids, title
   */
  public static function getAllActivityTypes() {

    $actTypes = array();

    // Get all membership types from CiviCRM
    $result = self::CiviCRMAPIWrapper('OptionValue', 'get', array(
      'sequential' => 1,
      'is_active' => 1,
      'option_group_id' => "activity_type",
      'options' => array('limit' => 0),
    ));

    if (!empty($result['values'])) {
      foreach($result['values'] as $key => $value) {
        $actTypes[$value['value']] = $value['label'];
      }
    }

    return $actTypes;
  }

  /**
   * Get all contact types
   *
   * @return array of contact types ids, title
   */
  public static function getAllContactTypes($parentOnly = FALSE) {

    $contactTypes = array();

    $contactTypeParams = array(
      'sequential' => 1,
      'is_active' => 1,
    );

    // Check if we need to get only the parent contact types
    if ($parentOnly) {
      $contactTypeParams['parent_id'] = array('IS NULL' => 1);
    }

    // Get all membership types from CiviCRM
    $result = self::CiviCRMAPIWrapper('ContactType', 'get', $contactTypeParams);

    if (!empty($result['values'])) {
      foreach($result['values'] as $key => $value) {
        $contactTypes[$value['name']] = $value['label'];
      }
    }

    return $contactTypes;
  }

  /**
   * Function to get all group subscription
   *
   * @return array()
   */
  public static function getallGroupSubscription($contactId) {
    if (empty($contactId)) {
      return;
    }

    $groupSubscriptions = array();
    $sql = "SELECT c.sort_name, g.title, s.date, s.id, s.contact_id, s.group_id, s.status FROM 
civicrm_subscription_history s
INNER JOIN civicrm_contact c ON s.contact_id = c.id
INNER JOIN civicrm_group g ON g.id = s.group_id
WHERE s.contact_id = %1 ORDER BY s.date DESC";
    $resource = CRM_Core_DAO::executeQuery($sql, array( 1 => array($contactId, 'Integer')));
    while ($resource->fetch()) {
      $groupSubscriptions[$resource->id] = array(
        'id' => $resource->id,
        'contact_id' => $resource->contact_id,
        'group_id' => $resource->group_id,
        'sort_name' => $resource->sort_name,
        'date' => $resource->date,
        'title' => $resource->title,
        'status' => $resource->status,
      );
    }

    return $groupSubscriptions;
  }

  /**
   * Function get custom search ID using name
   *
   * @return array $csid
   */
  public static function getCustomSearchDetails($name) {

    if (empty($name)) {
      return;
    }
    
    // Get all membership types from CiviCRM
    $result = self::CiviCRMAPIWrapper('OptionValue', 'get', array(
      'sequential' => 1,
      'option_group_id' => "custom_search",
      'name' => $name,
    ));

    return array('id' => $result['values'][0]['value'], 'label' => $result['values'][0]['description']);
  }

  /**
   * Function get GDPR settings
   *
   * @return array $settings (GDPR settings)
   */
  public static function getGDPRSettings() {
    // Get GDPR settings from civicrm_settings table
    $settingsStr = CRM_Core_BAO_Setting::getItem(
      CRM_Gdpr_Constants::GDPR_SETTING_GROUP,
      CRM_Gdpr_Constants::GDPR_SETTING_NAME
    );
    
    return unserialize($settingsStr);
  }

  /**
   * Function get GDPR activity types id and label
   *
   * @return array $activityTypes
   */
  public static function getGDPRActivityTypes() {
    
    $gdprActTypes = array();

    // Get GDPR settings
    $settings = CRM_Gdpr_Utils::getGDPRSettings();

    // Get all activity types
    $actTypes = CRM_Gdpr_Utils::getAllActivityTypes();

    foreach($settings['activity_type'] as $actTypeId) {
      $gdprActTypes[] = $actTypes[$actTypeId];
    }
    
    return $gdprActTypes;
  }

  /**
   * Function get contacts count summary who have not had activity is a set period
   * but has done a click through
   *
   * @return array $contactscount
   */
  public static function getContactsWithClickThrough() {
    $count = 0;

    $clickThroughSql = self::getContactClickThroughSQL($getCountOnly = TRUE);
    $resource = CRM_Core_DAO::executeQuery($clickThroughSql);
    if ($resource->fetch()) {
      $count = $resource->count;
    }

    return $count;
  }

  /**
   * Function get contacts count summary who have not had activity is a set period
   *
   * @return array $contactscount
   */
  public static function getNoActivityContactsSummary() {
    $count = 0;

    // Get contact count who have not had any GDPR activities
    $actContactSummarySql = self::getActivityContactSQL($actTypeParams, TRUE, TRUE);
    $resource = CRM_Core_DAO::executeQuery($actContactSummarySql);
    if ($resource->fetch()) {
      $count = $resource->count;
    }

    return $count;
  }

  /**
   * Function get contacts list who have not had activity is a set period
   *
   * @return array $contactList
   */
  public static function getNoActivityContactsList($params) {

    $contactList = array();
      
    $contactListSql = self::getActivityContactSQL($params, FALSE, TRUE);
    $resource = CRM_Core_DAO::executeQuery($contactListSql);
    while ($resource->fetch()) {

      // get last activity date time
      /*$lastActSql = "SELECT a.activity_date_time FROM civicrm_activity_contact ac
INNER JOIN civicrm_activity a ON a.id = ac.activity_id   
WHERE ac.record_type_id = 3 AND a.activity_type_id = %1 AND ac.contact_id = %2
ORDER BY a.activity_date_time LIMIT 1
      ";
      $lastActParams = array(
        1 => array($params['activity_type_id'], 'Integer'),
        2 => array($resource->id, 'Integer'),
      );
      $lastActResource = CRM_Core_DAO::executeQuery($lastActSql, $lastActParams);
      $lastActDateTime = '';
      if ($lastActResource->fetch()) {
        $lastActDateTime = $lastActResource->activity_date_time;
      }*/

      $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$resource->id);
      $contactList[$resource->id] = array(
        'id' => $resource->id,
        'sort_name' => "<a href='{$url}'>".$resource->sort_name."</a>",
        //'activity_date_time' => '',
      );
    }

    return $contactList;
  }

  /**
   * Get count of contacts for a particular activity type.
   *
   * @param array $params
   *   Associated array for params.
   *
   * @return null|string
   */
  public static function getActivityContactCount(&$params) {

    $contactListSql = self::getActivityContactSQL($params, TRUE, TRUE);
    $count = CRM_Core_DAO::singleValueQuery($contactListSql);
    
    return $count;
  }

  /**
   * Function to compose SQL for getting contacts who have not had an activity
   *
   * @param array $params
   *   Associated array for params.
   *
   * @return where|string
   */
  public static function getActivityContactSQL(&$params, $getCountOnly = FALSE, $excludeClickThrough = FALSE) {

    // Get GDPR settings
    $settings = CRM_Gdpr_Utils::getGDPRSettings();
    if (empty($settings['activity_period']) || empty($settings['activity_type'])) {
      return;
    }

    // Get current date - set period
    $date = date('Y-m-d H:i:s', strtotime('-'.$settings['activity_period'].' days'));
    $actTypeIdsStr = implode(',', $settings['activity_type']);

    $orderBy = $limit = '';
    if ($params['context'] == 'activitycontactlist') {
      $params['offset'] = ($params['page'] - 1) * $params['rp'];
      $params['rowCount'] = $params['rp'];
      $params['sort'] = CRM_Utils_Array::value('sortBy', $params);

      if (!empty($params['rowCount']) && is_numeric($params['rowCount'])
        && is_numeric($params['offset']) && $params['rowCount'] > 0
      ) {
        $limit = " LIMIT {$params['offset']}, {$params['rowCount']} ";
      }

      $orderBy = ' ORDER BY c.id desc';
      if (!empty($params['sort'])) {
        $orderBy = ' ORDER BY ' . CRM_Utils_Type::escape($params['sort'], 'String');
      }
    }

    $extraWhere = '';
    if (!empty($settings['contact_type'])) {
      $contactTypeStr = "'".implode("','", $settings['contact_type'])."'";
      $extraWhere .= " AND c.contact_type IN ({$contactTypeStr})";
    }

    if (!empty($params['contact_name'])) {
      $extraWhere .= " AND c.sort_name LIKE '%{$params['contact_name']}%'";
    }

    $selectColumns = "c.id, c.sort_name";
    if ($getCountOnly) {
      $selectColumns = "count(*) as count";
      $limit = '';
    }

    $excludeClickSql = '';
    if ($excludeClickThrough) {
      $clickThroughSql = self::getContactClickThroughSQL();
      $excludeClickSql = " AND c.id NOT IN ({$clickThroughSql})";
    }

    $sql = "SELECT {$selectColumns} FROM civicrm_contact c 
WHERE c.id NOT IN (
SELECT contact_id FROM civicrm_activity_contact ac 
INNER JOIN civicrm_activity a ON a.id = ac.activity_id
WHERE ac.record_type_id = 3 AND a.activity_type_id IN ({$actTypeIdsStr}) 
AND a.activity_date_time > '{$date}'
) AND c.is_deleted = 0 {$extraWhere} {$excludeClickSql} {$orderBy} {$limit}";

    return $sql;
  }

  /**
   * Function to compose SQL for getting contacts who clicked a link in email
   *
   * @param array $params
   *   Associated array for params.
   *
   * @return where|string
   */
  public static function getContactClickThroughSQL($getCountOnly = FALSE) {
    // Get GDPR settings
    $settings = CRM_Gdpr_Utils::getGDPRSettings();
    if (empty($settings['activity_period'])) {
      return;
    }

    // Get current date - set period
    $date = date('Y-m-d H:i:s', strtotime('-'.$settings['activity_period'].' days'));

    $selectColumns = "queue.contact_id";
    if ($getCountOnly) {
      $selectColumns = "count(*) as count";
    }

    $sql = "SELECT {$selectColumns} FROM civicrm_mailing_event_trackable_url_open url
INNER JOIN civicrm_mailing_event_queue queue ON queue.id = url.event_queue_id
WHERE url.time_stamp > '{$date}'";

    return $sql;
  }

}//End Class
