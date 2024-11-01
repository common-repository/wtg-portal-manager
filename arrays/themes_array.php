<?php
/** 
 * Theme integration array. I want this file to stay multipurpose and hold any
 * information relating to a theme.
 * 
 * @package WTG Portal Manager
 * @author Ryan Bayne   
 * @since 0.0.1
 */

// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

$wtgportalmanager_themes = array(); 
$wtgportalmanager_themes['integratedthemes'] = array( 'Pindol' );// plugins using this array

// Pindol - the theme used on WebTechGlobal  
$wtgportalmanager_themes['Pindol']['author'] = 'Muffin Group';
$wtgportalmanager_themes['Pindol']['authorurl'] = 'http://muffingroup.com';

$wtgportalmanager_themes['Pindol']['sidebars'][0]['name'] = __( 'Dynamic Sidebar', 'wtgportalmanager' );
$wtgportalmanager_themes['Pindol']['sidebars'][0]['type'] = 'metakey';// metakey method passes only the value from the meta to dynamic_sidebar()
$wtgportalmanager_themes['Pindol']['sidebars'][0]['metakey'] = 'mfn-post-sidebar';
$wtgportalmanager_themes['Pindol']['sidebars'][0]['sidebarid'] = '';// not applicable - entire ID string comes from post meta
/* keep this - pending upgrades that allow widgets to be modified on a per portal basis
$wtgportalmanager_themes['Pindol']['sidebars'][1]['name'] = __( 'Footer area #1', 'wtgportalmanager' );
$wtgportalmanager_themes['Pindol']['sidebars'][1]['type'] = 'fixed';// fixed has a hardcoded string - we replace the registered sidebar with our own in $wp_registered_sidebars
$wtgportalmanager_themes['Pindol']['sidebars'][1]['metakey'] = false;// not applicable - no post meta is involved within the themes sidebar.php but it may be involved for WTG Portal Manager to determine correct sidebar
$wtgportalmanager_themes['Pindol']['sidebars'][1]['sidebarid'] = 'footer-area-1';

$wtgportalmanager_themes['Pindol']['sidebars'][2]['name'] = __( 'Footer area #2', 'wtgportalmanager' );
$wtgportalmanager_themes['Pindol']['sidebars'][2]['type'] = 'fixed';// metakey | fixed
$wtgportalmanager_themes['Pindol']['sidebars'][2]['metakey'] = false;//
$wtgportalmanager_themes['Pindol']['sidebars'][2]['sidebarid'] = 'footer-area-2';

$wtgportalmanager_themes['Pindol']['sidebars'][3]['name'] = __( 'Footer area #3', 'wtgportalmanager' );
$wtgportalmanager_themes['Pindol']['sidebars'][3]['type'] = 'fixed';// metakey | fixed
$wtgportalmanager_themes['Pindol']['sidebars'][3]['metakey'] = false;// 
$wtgportalmanager_themes['Pindol']['sidebars'][3]['sidebarid'] = 'footer-area-3';

$wtgportalmanager_themes['Pindol']['sidebars'][4]['name'] = __( 'Footer area #4', 'wtgportalmanager' );
$wtgportalmanager_themes['Pindol']['sidebars'][4]['type'] = 'fixed';// metakey | fixed
$wtgportalmanager_themes['Pindol']['sidebars'][4]['metakey'] = false;//
$wtgportalmanager_themes['Pindol']['sidebars'][4]['sidebarid'] = 'footer-area-4';
*/

// Twenty Fourteen 
$wtgportalmanager_themes['Twenty Fourteen']['author'] = 'Muffin Group';
$wtgportalmanager_themes['Twenty Fourteen']['authorurl'] = 'http://muffingroup.com';
/* keep this - pending upgrades that allow widgets to be modified on a per portal basis
$wtgportalmanager_themes['Twenty Fourteen']['sidebars'][0]['name'] = __( 'Primary Sidebar', 'wtgportalmanager' );
$wtgportalmanager_themes['Twenty Fourteen']['sidebars'][0]['type'] = 'fixed';// metakey | fixed
$wtgportalmanager_themes['Twenty Fourteen']['sidebars'][0]['metakey'] = false;//
$wtgportalmanager_themes['Twenty Fourteen']['sidebars'][0]['sidebarid'] = 'sidebar-1';

$wtgportalmanager_themes['Twenty Fourteen']['sidebars'][1]['name'] = __( 'Content Sidebar', 'wtgportalmanager' );
$wtgportalmanager_themes['Twenty Fourteen']['sidebars'][1]['type'] = 'fixed';// metakey | fixed
$wtgportalmanager_themes['Twenty Fourteen']['sidebars'][1]['metakey'] = false;//
$wtgportalmanager_themes['Twenty Fourteen']['sidebars'][1]['sidebarid'] = 'sidebar-2';

$wtgportalmanager_themes['Twenty Fourteen']['sidebars'][2]['name'] = __( 'Footer Widget Area', 'wtgportalmanager' );
$wtgportalmanager_themes['Twenty Fourteen']['sidebars'][2]['type'] = 'fixed';// metakey | fixed
$wtgportalmanager_themes['Twenty Fourteen']['sidebars'][2]['metakey'] = false;//
$wtgportalmanager_themes['Twenty Fourteen']['sidebars'][2]['sidebarid'] = 'sidebar-3';
*/
?>