<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Utils/Type.php';

/**
 *  Class used to get historic addresses
 */
class CRM_Gdpr_Page_AJAX {
  /**
   * Function to get address log for a contact
   */
  public static function get_address_logs($contactId){
    
    $aGetMemberships =array();

    if(empty($contactId)){
      return $aGetMemberships;
    }

    // Get logging DB
    if (defined('CIVICRM_LOGGING_DSN')) {
      $dsn = DB::parseDSN(CIVICRM_LOGGING_DSN);
    }
    else {
      $dsn = DB::parseDSN(CIVICRM_DSN);
    }
    $logging_db = $dsn['database'];

    $sql = "SELECT lca.*,  lt.display_name as lt_name
FROM {$logging_db}.log_civicrm_address as lca
LEFT JOIN civicrm_location_type as lt ON ( lca.location_type_id = lt.id )
WHERE lca.contact_id = %1
ORDER BY lca.log_date DESC";
      $sqlParams = array( 
        1 => array( $contactId, 'Integer')
      );
      $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);
      while($dao->fetch()){
          $country  = empty($dao->country_id) ? Null  : CRM_Core_PseudoConstant::country( $dao->country_id );
          $county   = empty($dao->county_id)  ? Null  : CRM_Core_PseudoConstant::county( $dao->county_id );
          $aGetMemberships[] = array(
            'street'        => $dao->street_address,
            'location_type' => $dao->lt_name,
            'postal_code'   => $dao->postal_code,
            'country'       => $country,
            'line1'         => $dao->supplemental_address_1,
            'line2'         => $dao->supplemental_address_2,
            'line3'         => $dao->supplemental_address_3,
            'city'          => $dao->city,
            'country'       => $country,
            'county'        => $county,
            'log_action'    => $dao->log_action,
            'log_date'      => CRM_Utils_Date::customFormat($dao->log_date , '%d-%m-%Y')
          );
      }
    //}
    return $aGetMemberships;
  }
  
  public static function get_address_history_table( $data ){
    if(empty($data)){
      return null;
    }
    $table  = <<< TABLE
      <table id="custom_address_history">
        <thead>
          <tr>
            <th>
              Date <small>(dd-mm-yyyy)</small>
            </th>
            <th>
              Action
            </th>
            <th>
              Location Type
            </th>
            <th>
              Street
            </th>
            <th>
              Postal Code
            </th>
            <th>
              Country
            </th>
          </tr>
        </thead>
    <tbody>
TABLE;
    
        
        foreach( $data as $row ){
         $table  .= "<tr> ";
         $table  .= "<td valign='top'> ".$row['log_date']."</td> ";
         $table  .= "<td valign='top'> ".$row['log_action']."</td> ";
         $table  .= "<td valign='top'> ".$row['location_type']."</td> ";
         $table  .= "<td valign='top'> ".$row['street'];
         if(!empty( $row['line1'])){
           $table .= "<br>".$row['line1'];
         }
         if(!empty( $row['line2'])){
           $table .= "<br>".$row['line2'];
         }
         if(!empty( $row['line3'])){
           $table .= "<br>".$row['line3'];
         }
         if(!empty( $row['city'])){
           $table .= "<br>".$row['city'];
         }
         $table  .= "</td>";
         $table  .= "<td valign='top'> ".$row['postal_code']."</td> ";
         $table  .= "<td valign='top'> ".$row['country']."</td> ";
         $table  .= "</tr> ";
         
        }
     
    $table .= <<<TABLE
          </tbody>
        
        </table>
TABLE;
    
    
    return $table;
  }

  static function getAddressHistory(){
    $iContactId = $_POST['contactId'];
    if(!empty($iContactId)){
      $aGetAddressHistory = self::get_address_logs( $iContactId );
      $table = self::get_address_history_table( $aGetAddressHistory );
    }
    
    $countHistory = count($aGetAddressHistory);
    //$return['table'] = $table;
    //echo json_encode($return);
    $return = "";
    if(!empty( $table ) && !empty($countHistory)){
      $return = $countHistory.'|'.$table;
    }
    echo $return;
    exit;
  }

  /**
   * Function to get GDPR activity contact list
   */
  public static function getGdprActivityContactList() {
    $sortMapper = array(
      0 => 'contact_a.id',
      1 => 'contact_a.sort_name',
      //2 => 'a.activity_date_time',
    );

    $sEcho = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
    $offset = isset($_REQUEST['iDisplayStart']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer') : 0;
    $rowCount = isset($_REQUEST['iDisplayLength']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer') : 25;
    $sort = isset($_REQUEST['iSortCol_0']) ? CRM_Utils_Array::value(CRM_Utils_Type::escape($_REQUEST['iSortCol_0'], 'Integer'), $sortMapper) : NULL;
    $sortOrder = isset($_REQUEST['sSortDir_0']) ? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String') : 'asc';
    $context = isset($_REQUEST['context']) ? CRM_Utils_Type::escape($_REQUEST['context'], 'String') : NULL;

    $params = $_REQUEST;
    if ($sort && $sortOrder) {
      $params['sortBy'] = $sort . ' ' . $sortOrder;
    }

    $params['page'] = ($offset / $rowCount) + 1;
    $params['rp'] = $rowCount;

    $params['context'] = 'activitycontactlist';

    // get contact list
    CRM_Core_Error::debug_var('context', $context);
    $contactList = CRM_Gdpr_Utils::getNoActivityContactsList($params);

    $params['total'] = CRM_Gdpr_Utils::getActivityContactCount($params);

    $iFilteredTotal = $iTotal = $params['total'];

    $selectorElements = array(
      'id',
      'sort_name',
      //'activity_date_time',
    );

    header("Content-Type: application/json");
    echo CRM_Utils_JSON::encodeDataTableSelector($contactList, $sEcho, $iTotal, $iFilteredTotal, $selectorElements);
    CRM_Utils_System::civiExit();
  }

  /**
   * To handle the comms preference submission from Thank you page Event/Contribution page.
   */
  static function commPreferenceSubmission(){
    $iContactId = $_POST['contactId'];
    $submittedValues = $_POST['preference'];

    if (empty($iContactId)) {
      echo "Failed to Update communication preference";
      CRM_Utils_System::civiExit();
    }

    //Update preferences
    CRM_Gdpr_CommunicationsPreferences_Utils::updateCommsPrefByFormValues($iContactId, $submittedValues);

    //Create comms preference activity 
    CRM_Gdpr_CommunicationsPreferences_Utils::createCommsPrefActivity($iContactId, $submittedValues);

    //Get completion msg from settings
    $settings = CRM_Gdpr_CommunicationsPreferences_Utils::getSettings();
    $fieldsSettings = $settings[CRM_Gdpr_CommunicationsPreferences_Utils::SETTING_NAME];

    $completionMsg = "Thank you for updating your Communication Preferences..";
    if (!empty($fieldsSettings['comm_pref_thankyou_embed_complete_msg'])) {
      $completionMsg = $fieldsSettings['comm_pref_thankyou_embed_complete_msg'];
    }

    echo $completionMsg;
    CRM_Utils_System::civiExit();
  }
}