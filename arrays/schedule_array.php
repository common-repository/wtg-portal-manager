<?php
/** 
 * Default schedule array for WTG Portal Manager plugin 
 * 
 * @package WTG Portal Manager
 * @author Ryan Bayne   
 * @since 0.0.1
 */

// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

$wtgportalmanager_schedule_array = array();
// history
$wtgportalmanager_schedule_array['history']['lastreturnreason'] = __( 'None', 'wtgportalmanager' );
$wtgportalmanager_schedule_array['history']['lasteventtime'] = time();
$wtgportalmanager_schedule_array['history']['lasteventtype'] = __( 'None', 'wtgportalmanager' );
$wtgportalmanager_schedule_array['history']['day_lastreset'] = time();
$wtgportalmanager_schedule_array['history']['hour_lastreset'] = time();
$wtgportalmanager_schedule_array['history']['hourcounter'] = 1;
$wtgportalmanager_schedule_array['history']['daycounter'] = 1;
$wtgportalmanager_schedule_array['history']['lasteventaction'] = __( 'None', 'wtgportalmanager' );
// times/days
$wtgportalmanager_schedule_array['days']['monday'] = true;
$wtgportalmanager_schedule_array['days']['tuesday'] = true;
$wtgportalmanager_schedule_array['days']['wednesday'] = true;
$wtgportalmanager_schedule_array['days']['thursday'] = true;
$wtgportalmanager_schedule_array['days']['friday'] = true;
$wtgportalmanager_schedule_array['days']['saturday'] = true;
$wtgportalmanager_schedule_array['days']['sunday'] = true;
// times/hours
$wtgportalmanager_schedule_array['hours'][0] = true;
$wtgportalmanager_schedule_array['hours'][1] = true;
$wtgportalmanager_schedule_array['hours'][2] = true;
$wtgportalmanager_schedule_array['hours'][3] = true;
$wtgportalmanager_schedule_array['hours'][4] = true;
$wtgportalmanager_schedule_array['hours'][5] = true;
$wtgportalmanager_schedule_array['hours'][6] = true;
$wtgportalmanager_schedule_array['hours'][7] = true;
$wtgportalmanager_schedule_array['hours'][8] = true;
$wtgportalmanager_schedule_array['hours'][9] = true;
$wtgportalmanager_schedule_array['hours'][10] = true;
$wtgportalmanager_schedule_array['hours'][11] = true;
$wtgportalmanager_schedule_array['hours'][12] = true;
$wtgportalmanager_schedule_array['hours'][13] = true;
$wtgportalmanager_schedule_array['hours'][14] = true;
$wtgportalmanager_schedule_array['hours'][15] = true;
$wtgportalmanager_schedule_array['hours'][16] = true;
$wtgportalmanager_schedule_array['hours'][17] = true;
$wtgportalmanager_schedule_array['hours'][18] = true;
$wtgportalmanager_schedule_array['hours'][19] = true;
$wtgportalmanager_schedule_array['hours'][20] = true;
$wtgportalmanager_schedule_array['hours'][21] = true;
$wtgportalmanager_schedule_array['hours'][22] = true;
$wtgportalmanager_schedule_array['hours'][23] = true;
// limits
$wtgportalmanager_schedule_array['limits']['hour'] = '1000';
$wtgportalmanager_schedule_array['limits']['day'] = '5000';
$wtgportalmanager_schedule_array['limits']['session'] = '300';
// event types (update event_action() if adding more eventtypes)
// deleteuserswaiting - this is the auto deletion of new users who have not yet activated their account 
$wtgportalmanager_schedule_array['eventtypes']['deleteuserswaiting']['name'] = __( 'Delete Users Waiting', 'wtgportalmanager' ); 
$wtgportalmanager_schedule_array['eventtypes']['deleteuserswaiting']['switch'] = 'disabled';  
?>