<?php
/** 
* Install, uninstall, repair
* 
* The section array can be used to prevent installation of per section elements before activation of the plugin.
* Once activation has been done, section switches can be used to change future activation. This is early stuff
* so not sure if it will be of use.
* 
* @package WTG Portal Manager
* @author Ryan Bayne   
* @since 0.0.1
*/

// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
* Handles install, uninstall, repair, modification of installation state, database table creation included
* 
* @author Ryan R. Bayne
* @package WTG Portal Manager
* @since 0.0.1
* @version 1.0.3
*/
class WTGPORTALMANAGER_Install {
    
    /**
    * Install __construct persistently registers database tables and is the
    * first point to monitoring installation state 
    */
    public function __construct() {

        // load class used at all times
        // $this->DB = new WTGPORTALMANAGER_DB(); commeted 14092014
        $this->DB = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_DB', 'class-wpdb.php', 'classes' );
        $this->PHP = new WTGPORTALMANAGER_PHP();
                
        // on activation run install_plugin() method which then runs more methods i.e. create_tables();
        register_activation_hook( WTGPORTALMANAGER_ABSPATH . 'wtg-portal-manager.php', array( $this, 'install_plugin' ) ); 

        // on deactivation run disabled_plugin() - not a full uninstall
        register_deactivation_hook( WTGPORTALMANAGER_ABSPATH . 'wtg-portal-manager.php',  array( $this, 'deactivate_plugin' ) );
        
        // register webtechglobal_log table
        add_action( 'init', array( $this, 'register_webtechglobal_log_table' ) );
        add_action( 'switch_blog', array( $this, 'register_webtechglobal_log_table' ) );
        $this->register_webtechglobal_log_table(); // register tables manually as the hook may have been missed      

        // register webtechglobal_projects table
        add_action( 'init', array( $this, 'register_webtechglobal_projects_table' ) );
        add_action( 'switch_blog', array( $this, 'register_webtechglobal_projects_table' ) );
        $this->register_webtechglobal_projects_table(); // register tables manually as the hook may have been missed             
          
        // register webtechglobal_projectsmeta table
        add_action( 'init', array( $this, 'register_webtechglobal_projectsmeta_table' ) );
        add_action( 'switch_blog', array( $this, 'register_webtechglobal_projectsmeta_table' ) );
        $this->register_webtechglobal_projectsmeta_table(); // register tables manually as the hook may have been missed             
                   
    }

    // log table - set $wpdb
    function register_webtechglobal_log_table() {
        global $wpdb;
        $wpdb->webtechglobal_log = "{$wpdb->prefix}webtechglobal_log";
    }    

    // projects table - set $wpdb
    // this table is being used in multiple WTG plugins for easy integration
    // changes must be reflected in all plugins
    function register_webtechglobal_projects_table() {
        global $wpdb;
        $wpdb->webtechglobal_projects = "{$wpdb->prefix}webtechglobal_projects";
    }
        
    // projects meta table - set $wpdb
    function register_webtechglobal_projectsmeta_table() {
        global $wpdb;
        $wpdb->webtechglobal_projectsmeta = "{$wpdb->prefix}webtechglobal_projectsmeta";
    }    

    /**
    * Creates the plugins database tables
    *
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @since 0.0.1
    * @version 1.3
    * 
    * @todo put meta table into function of its own as done with the other tables
    */
    function create_tables() {      
        global $charset_collate,$wpdb;
                
        self::webtechglobal_log();
        self::webtechglobal_projects();
        self::webtechglobal_projectsmeta();     
    }
    
    /**
    * Create projects meta table.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.0
    * 
    * @uses dbDelta()
    * @version 1.2
    */
    public function webtechglobal_projectsmeta() {
        global $charset_collate,$wpdb;  
        
// webtechglobal_projectsmeta
$sql_create_table = "CREATE TABLE {$wpdb->webtechglobal_projectsmeta} (
meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
webtechglobal_projects_id bigint(20) unsigned NOT NULL DEFAULT 0,
meta_key varchar(255) DEFAULT NULL,
meta_value longtext,
UNIQUE KEY (meta_id) ) $charset_collate; ";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );        
        dbDelta( $sql_create_table );
        // meta_id 
        // webtechglobal_projects_id 
        // meta_key 
        // meta_value         
    }
      
    /**
    * Global WebTechGlobal log table as used in all WTG plugins.
    * This approach helps to keep the database tidy, while still providing
    * an still improving log system and with all log entries in a single table.
    * Behaviours relating to integration of these plugins can be spotted easier.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.0
    */
    public function webtechglobal_log() {
        global $charset_collate,$wpdb;
        
// webtechglobal_log - log everything in this table and use the data for multiple purposes
$sql_create_table = "CREATE TABLE {$wpdb->webtechglobal_log} (
row_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
service varchar(250) DEFAULT 'trainingtools',
outcome tinyint(1) unsigned NOT NULL DEFAULT 1,
timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
line int(11) unsigned DEFAULT NULL,
file varchar(250) DEFAULT NULL,
function varchar(250) DEFAULT NULL,
sqlresult blob,
sqlquery varchar(45) DEFAULT NULL,
sqlerror mediumtext,
wordpresserror mediumtext,
screenshoturl varchar(500) DEFAULT NULL,
userscomment mediumtext,
page varchar(45) DEFAULT NULL,
version varchar(45) DEFAULT NULL,
panelid varchar(45) DEFAULT NULL,
panelname varchar(45) DEFAULT NULL,
tabscreenid varchar(45) DEFAULT NULL,
tabscreenname varchar(45) DEFAULT NULL,
dump longblob,
ipaddress varchar(45) DEFAULT NULL,
userid int(11) unsigned DEFAULT NULL,
comment mediumtext,
type varchar(45) DEFAULT NULL,
category varchar(45) DEFAULT NULL,
action varchar(45) DEFAULT NULL,
priority varchar(45) DEFAULT NULL,
triga varchar(45) DEFAULT NULL,
UNIQUE KEY (row_id) ) $charset_collate; ";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );        
        dbDelta( $sql_create_table );   
        
        // row_id
        // service - the plugin, theme or web service triggering log entry
        // outcome - set a positive (1) or negative (0) outcome
        // timestamp
        // line - __LINE__
        // file - __FILE__
        // function - __FUNCTION__
        // sqlresult - return from the query (dont go mad with this and store large or sensitive data where possible)
        // sqlquery - the query as executed
        // sqlerror - if failed MySQL error in here
        // wordpresserror - if failed store WP error
        // screenshoturl - if screenshot taking and uploaded
        // userscomment - if user is testing they can submit a comment with error i.e. what they done to cause it
        // page - plugin page ID i.e. c2pdownloads
        // version - version of the plugin (plugin may store many logs over many versions)
        // panelid - (will be changed to formid i.e. savebasicsettings)
        // panelname - (will be changed to formname i.e Save Basic Settings)
        // tabscreenid - the tab number i.e. 0 or 1 or 5
        // tabscreenname - the on screen name of the tab in question, if any i.e. Downloads Overview
        // dump - anything the developer thinks will help with debugging or training
        // ipaddress - security side of things, record who is using the site
        // userid - if user logged into WordPress
        // comment - developers comment in-code i.e. recommendation on responding to the log entry
        // type - general|error|trace
        // category - any term that suits the section or system
        // action - what was being attempted, if known 
        // priority - low|medium|high (low should be default, medium if the log might help improve the plugin or user experience or minor PHP errors, high for critical errors especially security related
        // triga - (trigger but that word is taking) not sure we need this        
    }
        
    /**
    * Create WTG global projects table as used with many plugins.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @since 0.0.3
    * @version 1.0
    * 
    * @todo requires unique or consraint on project name
    */
    public function webtechglobal_projects() {
        global $charset_collate,$wpdb;
        
// webtechglobal_projects                                                                                                                                                                                                                                                                                                                                                                                            
$sql_create_table = "CREATE TABLE {$wpdb->webtechglobal_projects} (
project_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
projectname varchar(250) DEFAULT NULL,
description mediumtext,
mainmanager varchar(45) DEFAULT NULL,
phase varchar(45) DEFAULT NULL, 
archived tinyint(1) unsigned NOT NULL DEFAULT '0',
UNIQUE KEY (project_id) 
) $charset_collate; ";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );        
        dbDelta( $sql_create_table );  
         
        // project_id 
        // timestamp
        // projectname
        // description
        // mainmanager
        // phase    
        // archived (boolean) - archived projects wont show on most interfaces        
    }
                                           
    /**
    * reinstall all database tables in one go 
    */
    public function reinstalldatabasetables() {
        global $wpdb;
        
        require_once( WTGPORTALMANAGER_ABSPATH . 'arrays/tableschema_array.php' );
        
        if(is_array( $wtgportalmanager_tables_array ) ){
            foreach( $wtgportalmanager_tables_array['tables'] as $key => $table){
                if( $this->DB->does_table_exist( $table['name'] ) ){         
                    $wpdb->query( 'DROP TABLE '. $table['name'] );
                }                                                             
            }
        } 
        
        return $this->create_tables();
    } 
    
    function install_options() {
        // installation state values
        update_option( 'wtgportalmanager_installedversion', WTGPORTALMANAGER_VERSION );# will only be updated when user prompted to upgrade rather than activation
        update_option( 'wtgportalmanager_installeddate',time() );# update the installed date, this includes the installed date of new versions
        
        // schedule settings
        require( WTGPORTALMANAGER_ABSPATH . 'arrays/schedule_array.php' );        
        add_option( 'wtgportalmanager_schedule', serialize( $wtgportalmanager_schedule_array ) );

        // notifications array (persistent notice feature)
        add_option( 'wtgportalmanager_notifications', serialize( array() ) ); 
        
        // Sidebars Array
        add_option( 'wtgportalmanager_sidebars', array(), false );
    }
    
    function install_plugin() {              
        $this->create_tables();
        $this->install_options();
        // if this gets installed we know we arrived here in the installation procedure
        update_option( 'wtgportalmanager_is_installed', true );
    } 
    
    /**
    * Deactivate plugin - can use it for uninstall but usually not
    * 1. can use to cleanup WP CRON schedule, remove plugins scheduled events
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    function deactivate_plugin() {
        
    }            
}
?>