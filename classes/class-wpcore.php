<?php
/** 
 * WordPress close to core class and functions i.e. WP functions slightly adapted or
 * functions which take global WP values and change them before using them
 * 
 * @package Opus
 * @author Ryan Bayne   
 * @since 0.0.1
 */

// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class WTGPORTALMANAGER_WPCore {  
    /**
    * Returns an array of WordPress core capabilities.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.2
    */
    public function capabilities() {
        global $wp_roles; 
        $capabilities_array = array();
        foreach( $wp_roles->roles as $role => $role_array ) { 
            
            if( !is_array( $role_array['capabilities'] ) ) { continue; }
            
            $capabilities_array = array_merge( $capabilities_array, $role_array['capabilities'] );    
        }
        return $capabilities_array;
    }    
}
?>