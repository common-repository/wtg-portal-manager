<?php
/** 
 * Default administration settings for WTG Portal Manager plugin. These settings are installed to the 
 * wp_options table and are used from there by default. 
 * 
 * @package WTG Portal Manager
 * @author Ryan Bayne   
 * @since 0.0.1
 * @version 1.0.7
 */

// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

// install main admin settings option record
$wtgportalmanager_settings = array();
// encoding
$wtgportalmanager_settings['standardsettings']['encoding']['type'] = 'utf8';
// admin user interface settings start
$wtgportalmanager_settings['standardsettings']['ui_advancedinfo'] = false;// hide advanced user interface information by default
// other
$wtgportalmanager_settings['standardsettings']['ecq'] = array();
$wtgportalmanager_settings['standardsettings']['chmod'] = '0750';
$wtgportalmanager_settings['standardsettings']['systematicpostupdating'] = 'enabled';
// testing and development
$wtgportalmanager_settings['standardsettings']['developementinsight'] = 'disabled';
// global switches
$wtgportalmanager_settings['standardsettings']['textspinrespinning'] = 'enabled';// disabled stops all text spin re-spinning and sticks to the last spin

##########################################################################################
#                                                                                        #
#                           SETTINGS WITH NO UI OPTION                                   #
#              array key should be the method/function the setting is used in            #
##########################################################################################
$wtgportalmanager_settings['create_localmedia_fromlocalimages']['destinationdirectory'] = 'wp-content/uploads/importedmedia/';
 
##########################################################################################
#                                                                                        #
#                            DATA IMPORT AND MANAGEMENT SETTINGS                         #
#                                                                                        #
##########################################################################################
$wtgportalmanager_settings['datasettings']['insertlimit'] = 100;

##########################################################################################
#                                                                                        #
#                                    WIDGET SETTINGS                                     #
#                                                                                        #
##########################################################################################
$wtgportalmanager_settings['widgetsettings']['dashboardwidgetsswitch'] = 'disabled';

##########################################################################################
#                                                                                        #
#                               CUSTOM POST TYPE SETTINGS                                #
#                                                                                        #
##########################################################################################
$wtgportalmanager_settings['posttypes']['wtgflags']['status'] = 'disabled'; 
$wtgportalmanager_settings['posttypes']['posts']['status'] = 'disabled';

##########################################################################################
#                                                                                        #
#                                    NOTICE SETTINGS                                     #
#                                                                                        #
##########################################################################################
$wtgportalmanager_settings['noticesettings']['wpcorestyle'] = 'enabled';

##########################################################################################
#                                                                                        #
#                           YOUTUBE RELATED SETTINGS                                     #
#                                                                                        #
##########################################################################################
$wtgportalmanager_settings['youtubesettings']['defaultcolor'] = '&color1=0x2b405b&color2=0x6b8ab6';
$wtgportalmanager_settings['youtubesettings']['defaultborder'] = 'enable';
$wtgportalmanager_settings['youtubesettings']['defaultautoplay'] = 'enable';
$wtgportalmanager_settings['youtubesettings']['defaultfullscreen'] = 'enable';
$wtgportalmanager_settings['youtubesettings']['defaultscriptaccess'] = 'always';

##########################################################################################
#                                                                                        #
#                                  LOG SETTINGS                                          #
#                                                                                        #
##########################################################################################
$wtgportalmanager_settings['logsettings']['uselog'] = 1;
$wtgportalmanager_settings['logsettings']['loglimit'] = 1000;
$wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['outcome'] = true;
$wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['timestamp'] = true;
$wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['line'] = true;
$wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['function'] = true;
$wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['page'] = true; 
$wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['panelname'] = true;   
$wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['userid'] = true;
$wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['type'] = true;
$wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['category'] = true;
$wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['action'] = true;
$wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['priority'] = true;
$wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['comment'] = true;
?>