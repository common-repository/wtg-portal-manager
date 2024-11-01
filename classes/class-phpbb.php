<?php
/** 
* Integration from WordPress to phpBB for use in WebTechGlobal plugins.
* 
* phpBB version 3.1 supported.
* 
* This is not a bridge - I do not want to create a forum related class that cannot be
* used as a guide for all forum software. So phpBB table and column names may be used but 
* many methods will quickly adapt for other forums.                                          
* 
* Bridging also requires core edits and the phpBB team appear to have made no attempt to
* avoid function name conflicts in 3.1 which is annoying. I know WP could make the change
* however WP is usually the bigger part of a website - the main site. WP is also updated
* more frequently than phpBB so naturally phpBB would be the software elected to change.
* 
* When things change more things will change!
*
* @package WTG Portal Manager
* @author Ryan R. Bayne   
* @since 0.0.1
*/

/**
* Bridge from WordPress to phpBB for use in WebTechGlobal plugins.
* 
* phpBB version 3.1 supported.
* 
* @package WTG Portal Manager
* @author Ryan R. Bayne   
* @since 0.0.1
*/
class WTGPORTALMANAGER_PHPBB {
    
    private $defaults = array(
        'cache_expire' => 3600      
    );
    
    private $phpbb_prefix = 'phpbb_';
    
    function __construct() {
        global $wtgportalmanager_settings; 
       
        if( isset( $wtgportalmanager_settings['forumconfig']['tableprefix'] ) ) { 
            $this->phpbb_prefix = $wtgportalmanager_settings['forumconfig']['tableprefix']; 
        }
    }
    
    /**
    * Get phpBB topics + posts (threads).                           
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function get_posts_inrange_simple( $start = 0, $end = 9999999999, $topic = null, $forum = null, $limit = null ) {
        global $wpdb;

        $querywhere = "WHERE post_time > $start AND post_time < $end";
        
        if( is_numeric( $topic ) ) {
            $querywhere .= " AND topic_id = $topic";        
        }
        
        if( is_numeric( $forum ) ) { 
            $querywhere .= " AND forum_id = $forum";
        } 
        
        if( is_numeric( $limit ) ) { 
            $querylimit = " LIMIT $limit";
        } else { $querylimit = ''; } 
                                  
        return $wpdb->get_results( "
            SELECT * 
            FROM " . $this->phpbb_prefix . "posts
            $querywhere
            $querylimit
        ", ARRAY_A );        
    }
        
    /**
    * Determines if giving value is a valid forum ID.
    * 
    * Assumes the phpBB config has been collected already.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function get_forum( $forum_id, $columns = '*' ) {
        global $wtgportalmanager_settings, $wpdb;             
        return $wpdb->get_results( "SELECT ". $columns . " 
        FROM " . $wtgportalmanager_settings['forumconfig']['tableprefix'] . "forums
        WHERE forum_id = '$forum_id'", OBJECT );      
    }
    
    /**
    * Checks if phpBB is installed at the giving directory.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function phpbb_exists( $phpbb_root_path ) {
        // ensure string ends with a slash
        $phpbb_root_path = trailingslashit( $phpbb_root_path ); 
        // now check for files more common to phpBB (possibly matching other forums but that should not be an issue)
        if( !file_exists( $phpbb_root_path . 'phpbb/' ) )
        {
            return false;
        }

        if( !file_exists( $phpbb_root_path . 'mcp.php' ) )
        {
            return false;
        }

        if( !file_exists( $phpbb_root_path . 'ucp.php' ) )
        {
            return false;
        }

        if( !file_exists( $phpbb_root_path . 'config.php' ) )
        {
            return false;
        }
                
        return true;  
    }
    
    /**
    * Gets phpBB version.
    * 
    * Assumes certain config data saved in plugins main settings array.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    * 
    * @return boolean false if no version obtained else returns string version
    */
    public function version() {
        $version = self::config_value( 'version' );
        if( !is_string( $version ) ) { return false; }                        
        return $version;                                           
    }
    
    /**
    * Get configuration value from phpBB config tabe.
    * 
    * Assumes certain config data saved in plugins main settings array.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    * 
    * @returns result of $wpdb->get_value()
    */
    public function config_value( $config_name ) {
        global $wtgportalmanager_settings, $wpdb;             
        return $wpdb->get_var( "SELECT config_value 
        FROM " . $wtgportalmanager_settings['forumconfig']['tableprefix'] . "config
        WHERE config_name = '$config_name'" );    
    } 
} 
?>