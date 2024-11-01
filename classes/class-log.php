<?php
/**                  
* WebTechGlobal log system for WordPress.
* 
* @package WebTechGlobal WordPress Plugins
* @author Ryan Bayne   
* @version 1.0
*/

// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class WTGPORTALMANAGER_Log {
    public function __construct() {
        $this->DB = WTGPORTALMANAGER_Configuration::load_class( 'WTGPORTALMANAGER_DB', 'class-wpdb.php', 'classes' );
    }
        
    /**
    * Used to build history, flag items and schedule actions to be performed.
    * 1. it all falls under log as we would probably need to log flags and scheduled actions anyway
    *
    * @global $wpdb
    * @uses extract, shortcode_atts
    */
    public function newlog( $atts ){     
        global $wtgportalmanager_settings, $wpdb, $wtgportalmanager_filesversion;

        $table_name = $wpdb->prefix . 'webtechglobal_log';
        
        // if ALL logging is off - if ['uselog'] not set then logging for all files is on by default
        if( isset( $wtgportalmanager_settings['globalsettings']['uselog'] ) && $wtgportalmanager_settings['globalsettings']['uselog'] == 0){
            return false;
        }
        
        // if log table does not exist return false
        if( !$this->DB->does_table_exist( $table_name ) ){
            return false;
        }
             
        // if a value is false, it will not be added to the insert query, we want the database default to kick in, NULL mainly
        extract( shortcode_atts( array(  
            'outcome' => 1,# 0|1 (overall outcome in boolean) 
            'line' => false,# __LINE__ 
            'function' => false,# __FUNCTION__
            'file' => false,# __FILE__ 
            'sqlresult' => false,# dump of sql query result 
            'sqlquery' => false,# dump of sql query 
            'sqlerror' => false,# dump of sql error if any 
            'wordpresserror' => false,# dump of a wp error 
            'screenshoturl' => false,# screenshot URL to aid debugging 
            'userscomment' => false,# beta testers comment to aid debugging (may double as other types of comments if log for other purposes) 
            'page' => false,# related page 
            'version' => $wtgportalmanager_filesversion, 
            'panelid' => false,# id of submitted panel
            'panelname' => false,# name of submitted panel 
            'tabscreenid' => false,# id of the menu tab  
            'tabscreenname' => false,# name of the menu tab 
            'dump' => false,# dump anything here 
            'ipaddress' => false,# users ip 
            'userid' => false,# user id if any     
            'comment' => false,# dev comment to help with troubleshooting
            'type' => 'general',# like a parent category, category being more of a child i.e. general|error|trace|usertrace|communication 
            'category' => false,# postevent|dataevent|uploadfile|deleteuser|edituser|usertrace|projectchange|settingschange 
            'action' => false,# 3 posts created|22 posts updated (the actuall action performed)
            'priority' => 'normal',# low|normal|high (use high for errors or things that should be investigated, use low for logs created mid procedure for tracing progress)                        
            'triga' => false# autoschedule|cronschedule|wpload|manualrequest|systematic|unknown
        ), $atts ) );
        
        // start query
        $query = "INSERT INTO $table_name";
        
        // add columns and values
        $query_columns = '(outcome';
        $query_values = '(1';
        
        if( $line ){$query_columns .= ',line';$query_values .= ', "'.$line.'"';}
        if( $file ){$query_columns .= ',file';$query_values .= ', "'.$file.'"';}                                                                           
        if( $function ){$query_columns .= ',function';$query_values .= ', "'.$function.'"';}  
        if( $sqlresult ){$query_columns .= ',sqlresult';$query_values .= ', "'.$sqlresult.'"';}     
        if( $sqlquery ){$query_columns .= ',sqlquery';$query_values .= ', "'.$sqlquery.'"';}     
        if( $sqlerror ){$query_columns .= ',sqlerror';$query_values .= ', "'.$sqlerror.'"';}    
        if( $wordpresserror ){$query_columns .= ',wordpresserror';$query_values .= ', "'.$wordpresserror.'"';}     
        if( $screenshoturl ){$query_columns .= ',screenshoturl';$query_values .= ', "'.$screenshoturl.'"' ;}     
        if( $userscomment ){$query_columns .= ',userscomment';$query_values .= ', "'.$userscomment.'"';}     
        if( $page ){$query_columns .= ',page';$query_values .= ', "'.$page.'"';}     
        if( $version ){$query_columns .= ',version';$query_values .= ', "'.$version.'"';}     
        if( $panelid ){$query_columns .= ',panelid';$query_values .= ', "'.$panelid.'"';}     
        if( $panelname ){$query_columns .= ',panelname';$query_values .= ', "'.$panelname.'"';}     
        if( $tabscreenid ){$query_columns .= ',tabscreenid';$query_values .= ', "'.$tabscreenid.'"';}     
        if( $tabscreenname ){$query_columns .= ',tabscreenname';$query_values .= ', "'.$tabscreenname.'"';}     
        if( $dump ){$query_columns .= ',dump';$query_values .= ', "'.$dump.'"';}     
        if( $ipaddress ){$query_columns .= ',ipaddress';$query_values .= ', "'.$ipaddress.'"';}     
        if( $userid ){$query_columns .= ',userid';$query_values .= ', "'.$userid.'"';}     
        if( $comment ){$query_columns .= ',comment';$query_values .= ', "'.$comment.'"';}     
        if( $type ){$query_columns .= ',type';$query_values .= ', "'.$type.'"';}     
        if( $category ){$query_columns .= ',category';$query_values .= ', "'.$category.'"';}     
        if( $action ){$query_columns .= ',action';$query_values .= ', "'.$action.'"';}     
        if( $priority ){$query_columns .= ',priority';$query_values .= ', "'.$priority.'"';}     
        if( $triga ){$query_columns .= ',triga';$query_values .= ', "'.$triga.'"';}
        
        $query_columns .= ' )';
        $query_values .= ' )';
        $query .= $query_columns .' VALUES '. $query_values;  
        $wpdb->query( $query );     
    } 
    
    /**
    * Log general/common/normal processing activity - no errors, no trace, no testing.
    *
    * @author Ryan R. Bayne
    * @package CSV 2 POSt
    * @since 0.0.1
    * @version 1.0
    * 
    * @param mixed $line __LINE__
    * @param mixed $function __FUNCTION__
    * @param mixed $file __FILE__
    * @param string $triga autoschedule|cronschedule|wpload|manualrequest or add custom
    * @param string $category postevent|dataevent|uploadfile|deleteuser|edituser or add custom
    * @param string $action user readable information
    * @param boolean $outcome 
    * @param integer $userid
    */
    public function newlog_general( $line, $function, $file, $triga, $category, $action, $outcome = 1, $userid = false ) {

        $atts = array(
            'line' => $line,
            'function' => $function,
            'file' => $file,
            'triga' => $triga,
            'category' => $category,
            'action' => $action,
            'outcome' => $outcome,
            'priority' => 'normal',# low|normal|high
            'type' => 'general',# general|error|trace
        );
        
        if( $userid && is_numeric( $userid ) ) {
            $atts['userid'] = $userid;
        }
    
        self::newlog( $atts );
    }
    
    /**
    * Use this to log automated events and track progress in automated scripts.
    * Mainly used in schedule function but can be used in any functions called by add_action() or
    * other processing that is triggered by user events but not specifically related to what the user is doing.
    * 
    * @param mixed $outcome
    * @param mixed $trigger schedule, hook (action hooks such as text spinning could be considered automation), cron, url, user (i.e. user does something that triggers background processing)
    * @param mixed $line
    * @param mixed $file
    * @param mixed $function
    */
    public function log_schedule( $comment, $action, $outcome, $category = 'scheduledeventaction', $trigger = 'autoschedule', $line = 'NA', $file = 'NA', $function = 'NA' ){
        $atts = array();   
        $atts['logged'] = $this->PHP->datewp();
        $atts['comment'] = $comment;
        $atts['action'] = $action;
        $atts['outcome'] = $outcome;
        $atts['category'] = $category;
        $atts['line'] = $line;
        $atts['file'] = $file;
        $atts['function'] = $function;
        $atts['trigger'] = $function;
        // set log type so the log entry is made to the required log file
        $atts['type'] = 'automation';
        self::newlog( $atts);    
    } 
   
    /**
    * Cleanup log table - currently keeps 2 days of logs
    */
    public function log_cleanup() {
        global $wpdb;     
        if( $this->DB->database_table_exist( $wpdb->webtechglobal_log) ){
            global $wpdb;
            $twodays_time = strtotime( '2 days ago midnight' );
            $twodays = date( "Y-m-d H:i:s", $twodays_time);
            $wpdb->query( 
                "
                    DELETE FROM $wpdb->webtechglobal_log
                    WHERE timestamp < '".$twodays."'
                "
            );
        }
    }

    /**
    * Query the log table.
    *
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function getlog( $columns = '*', $where = '' ) {
        global $wtgportalmanager_settings, $wpdb;
        
        // if ALL logging is off return
        if( isset( $wtgportalmanager_settings['globalsettings']['uselog'] ) && $wtgportalmanager_settings['globalsettings']['uselog'] == 0 ){
            return false;
        }
           
        // if log table does not exist return false
        if( !$this->DB->does_table_exist( $wpdb->prefix . 'webtechglobal_log' ) ){
            return false;
        }
        
        // if $columns is not a string
        if( !is_string( $columns ) ) {
            $columns = '*';
        }
        
        // add space to query
        if( is_string( $where ) ) {
            $where = ' WHERE ' . $where;    
        }

        $complete_query = "SELECT $columns FROM " . $wpdb->prefix . 'webtechglobal_log' . $where;

        return $wpdb->get_results( "SELECT $columns FROM " . $wpdb->prefix . 'webtechglobal_log' . $where, ARRAY_A );     
    }
        
}
?>
