<?php
/** 
 * Remote calls to WordPress.org.
 * 
 * @package WTG Portal Manager
 * @author Ryan Bayne   
 * @version 1.0
 */  

class WTGPORTALMANAGER_WPORG {

    /**
    * Plugin stats API
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.0
    */
    static function wpse84254_get_download_stats( $plugin_name ){
        $response = wp_remote_request(
            add_query_arg(
                 'slug'
                ,'YOUR-REPO-PLUGIN-SLUG'
                ,'http://wordpress.org/extend/stats/plugin-xml.php'
            )
            ,array( 'sslverify' => false )
        );
        // Check if response is valid
        if ( is_wp_error( $response ) )
            return $response->get_error_message();
        if (
            empty( $response )
            OR 200 !== wp_remote_retrieve_response_code( $response )
            OR 'OK' !== wp_remote_retrieve_response_message( $response )
        )
            return _e( 'No Stats available', 'pluginstats_textdomain' );

        $response  = wp_remote_retrieve_body( $response );
        $response  = (array) simplexml_load_string( $response )->children()->chart_data;
        $response  = (array) $response['row'];
        $dates     = (array) array_shift( $response );
        $dates     = $dates['string'];
        // Get rid of unnecessary prepended empty object
        array_shift( $dates );
        $downloads = (array) array_pop( $response )->number;
        if ( count( $dates ) !== count( $downloads ) )
            return;

        $result = array_combine(
             $dates
            ,$downloads
        );
        return array_map(
             'absint'
            ,$result
        );
    }    
}
?>
