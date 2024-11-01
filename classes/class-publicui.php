<?php
/** 
 * Methods for public features that do not require the visitor to be logged
 * in. Everything else goes into class-adminui.php not because the features
 * are displayed in admin but because it requires backend access initially.
 * 
 * @package WebTechGlobal WordPress Plugins
 * @author Ryan Bayne   
 * @since 0.0.1
 */

// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class WTGPORTALMANAGER_PUBLICUI {     
    
    public function __construct() {
        
        // load class used at all times
        //$this->DB = self::load_class( 'WTGPORTALMANAGER_DB', 'class-wpdb.php', 'classes' );
        //$this->PHP = self::load_class( 'WTGPORTALMANAGER_PHP', 'class-phplibrary.php', 'classes' );
        //$this->WPCore = self::load_class( 'WTGPORTALMANAGER_WPCore', 'class-wpcore.php', 'classes' );  
    }  
    
    
}
?>
