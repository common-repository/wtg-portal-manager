<?php  
/** 
 * Core file, provides required functionality for even basic plugins.
 * 
 * There is a transition to move a lot of the functions in this file to their own class
 * and to class-wpcore.php, making the wtgportalmanager easier to use for small to large projects.
 * Right now it packs a little too much in a single file for a small plugin.
 * 
 * @package WTG Portal Manager
 * @author Ryan Bayne   
 * @since 0.0.1
 */
            
// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/** 
* Main class - add methods that will likely be used in all WTG plugins, use wpmain.php for those specific to the build
* 
* @since 0.0.1
* 
* @author Ryan Bayne 
*/                                                 
class WTGPORTALMANAGER extends WTGPORTALMANAGER_Configuration {
    
    /**
     * Page hooks (i.e. names) WordPress uses for the WTGPORTALMANAGER admin screens,
     * populated in add_admin_menu_entry()
     *
     * @since 0.0.1
     *
     * @var array
     */
    protected $page_hooks = array();
    
    protected

        $plugin_shorcodes = array (
            array( 'portalupdate',    'portal_updates_shortcode' ),
        );

        
	/**
	 * Holds the singleton instance of this class as used in Jetpack.
	 * 
	 * @since 1.0.0
	 * @var WTGPORTALMANAGER
	 */
	static $instance = false;
	
    public function __construct() {
        global $wtgportalmanager_settings;

        // load class used at all times
        $this->DB = self::load_class( 'WTGPORTALMANAGER_DB', 'class-wpdb.php', 'classes' );
        $this->PHP = self::load_class( 'WTGPORTALMANAGER_PHP', 'class-phplibrary.php', 'classes' );
        $this->Install = self::load_class( 'WTGPORTALMANAGER_Install', 'class-install.php', 'classes' );
        $this->Files = self::load_class( 'WTGPORTALMANAGER_Files', 'class-files.php', 'classes' );
        $this->GLOBALUI = self::load_class( 'WTGPORTALMANAGER_GLOBALUI', 'class-globalui.php', 'classes' );
                
        // global settings are initialized in the main file
        // however if the installations settings are missing this will install them
        $wtgportalmanager_settings = self::adminsettings();
  
        // add actions and filters to WP very early
        $this->add_actions(self::actions());
        $this->add_filters(self::filters());

        add_action('wp_footer', array( $this->GLOBALUI, 'displaytrace' ) );
       
        if( is_admin() ){
        
            // admin globals 
            global $wtgportalmanager_notice_array;
            
            $wtgportalmanager_notice_array = array();// set notice array for storing new notices in (not persistent notices)
            
            // load class used from admin only                   
            $this->UI = self::load_class( 'WTGPORTALMANAGER_ADMINUI', 'class-adminui.php', 'classes' );
            $this->Helparray = self::load_class( 'WTGPORTALMANAGER_Help', 'class-help.php', 'classes' );    
        }            
    }

    /**
    * Set variables that are required on most pages.
    * 
    * This is an action called by admin_menu which appears to have before admin_init.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.1
    */
    public function set_admin_globals() {
        global $wtgportalmanager_menu_array, $wtgportalmanager_page_name;
        
        // set menu array
        $WTGPORTALMANAGER_TabMenu = self::load_class( 'WTGPORTALMANAGER_TabMenu', 'class-pluginmenu.php', 'classes' );
        $wtgportalmanager_menu_array = $WTGPORTALMANAGER_TabMenu->menu_array();   
        
        // set page name (it's my own approach, each tab/view has a name which is shorter than the WP view ID)
        $wtgportalmanager_page_name = self::get_admin_page_name(); 
    }
        
    /**
    * $_POST and $_GET request processing procedure.
    *
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.5
    */
    public function process_admin_POST_GET() {
        // no processing for autosaves in this plugin
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
       
        // no processing during these actions, processing for these may be handled elsewhere
        // i.e. processing for post types is handed by save_post hook
        $views_to_avoid = array( 'editpost' );
        if( isset( $_POST['action'] ) && in_array( $_POST['action'], $views_to_avoid ) ) {
            return;    
        }
              
        $method = 'unknown';
        $function = 'nofunctionestablished123';
                                 
        // $_POST request check then nonce validation 
        if( isset( $_POST['wtgportalmanager_admin_action'] ) ) {   
            $early_deny_request = false;
                
            // run nonce security for a form submission
            if( !isset( $_POST['wtgportalmanager_form_name'] ) ) {    
                return false;
            }

            check_admin_referer( $_POST['wtgportalmanager_form_name'] );# exists here if failed
            $function = $_POST['wtgportalmanager_form_name'];
                    
            // set method - used to apply the correct security procedures
            $method = 'post';
        }          
               
        // $_GET reuest check by plugin OR a WP core $_GET request that is handled by the plugin
        if( isset( $_GET['wtgportalmanageraction'] )  ) {        
               
            check_admin_referer( $_GET['wtgportalmanageraction'] );# exists here if failed
            $function = $_GET['wtgportalmanageraction'];  

            // set method - used to apply the correct security procedures
            $method = 'get';  
        }
              
        // include the class that processes form submissions and nonce links
        if( $method !== 'unknown' ) {    
            $WTGPORTALMANAGER_REQ = self::load_class( 'WTGPORTALMANAGER_Requests', 'class-requests.php', 'classes' );   
            $WTGPORTALMANAGER_REQ->init();
            $WTGPORTALMANAGER_REQ->process_admin_request( $method, $function );
        }
    }
        
    /**
    * Registers shortcodes. 
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @version 1.0
    */
    public function plugin_shortcodes() { 
        foreach( $this->plugin_shorcodes as $shortcode )
        {
            add_shortcode( $shortcode[0], array( $this, $shortcode[1] ) );    
        }   
    }
	
    /**
    * Outputs latest updates from multiple sources. All sources are brought into
    * the same styling and layout.
    * 
    * The plan is to compile a tidy list of updates from multiple sources...
    * 
    * Twitter
    * Facebook
    * LinkedIn
    * WordPress.org forum
    * phpBB and other forums
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.3
    * 
    * @todo create an option for the cache period
    * @todo create and use cache settings for each individual API (different sources will have different expiry)
    * @todo create a cache for all results - essentially store the entire HTML in .txt file
    * @todo each time a new source is added, consider new but optional shortcode attributes that may avoid queries (remove this TODO once most sources added)
    */
    public function portal_updates_shortcode( $atts ) {
               
        // if we do not have a portal ID - we will assume we are on a view that does not
        // yet support it i.e. category with a list of posts. 
        if( !defined( 'WTGPORTALMANAGER_PUBLIC_PROJECTID' ) ) {
            return __( 'The current portal is still being configured. Please
            return later. 
            
            <br><br>
            
            Note for webmasters. This page
            needs to be linked to the portal fully so that WTGPORTALMANAGER_PUBLIC_PROJECTID
            can be set.', 'wtgportalmanager' );   
        }
          
        global $wtgportalmanager_settings;

        // Handle Cache 
        $cached_query = false;
		if( $this->GLOBALUI->is_view_control_requested( 'deletecaches' ) ) {   
			// Request using Developer toolbar to delete cache.
			delete_transient( 'wtgportalmanager_updatepage' . WTGPORTALMANAGER_PUBLIC_PROJECTID );
		}  
		else {
	        $cached_query = get_transient( 'wtgportalmanager_updatepage' . WTGPORTALMANAGER_PUBLIC_PROJECTID );
	        if( $cached_query !== false ) {    
	        	
	        	self::addtrace( 
            		__FUNCTION__, 
            		__LINE__, 
            		'', 
            		'Cache of updates exists and was returned.' 
            	);
            	
	            return $cached_query; 
	        } 
	        else {
				self::addtrace( 
            		__FUNCTION__, 
            		__LINE__, 
            		$cached_query, 
            		'No cache was found for the list of updates. Procedure will built a list..' 
            	);
	        }
		}
                
        // get the current portals meta value for "updatepagesources" - it should return an array of applied data sources             
        $sources_array = self::get_project_meta( WTGPORTALMANAGER_PUBLIC_PROJECTID, 'updatepagesources' );
      
        // we cant continue if no sources are setup for the update page, it's partly a security issue and to avoid a lot of errors
        if( !is_array( $sources_array ) ) {
 
            return __( 'This page will soon show a list of updates. The webmaster is currently
            configuring what sources should be used to compile the list. If you see this
            message for longer than 1 hour though - please make them aware.', 'wtgportalmanager' );
            
            // TODO 5: Add a form for administrator use only, allow sources to be configured.
  
        }       
                
        // variable per source - set them all at once for a simplier argument later
        $twitter_status = false;
        $facebook_status = false;
        $forum_status = false;
        $blogposts_status = false;
        $newversions_status = false;
        $pricedrop_status = false;
        $newtasks_status = false;  
        
        // load twitter status
        if( isset( $wtgportalmanager_settings['api']['twitter']['active'] ) && $wtgportalmanager_settings['api']['twitter']['active'] == 'enabled' ) {
            if( in_array( 'twitter', $sources_array ) ) {       
                $twitter_status = true;
            }  
        }      
              
        // load facebook status
        
        // load forum (phpBB, possibly other forum software) status
        if( isset( $wtgportalmanager_settings['forumconfig']['status'] ) && $wtgportalmanager_settings['forumconfig']['status'] == 'enabled' ) {
            if( in_array( 'forum', $sources_array ) ) {
                $forum_status = true;
            }    
        }
             
        // load blog posts status
        if( in_array( 'blogposts', $sources_array ) ) {
            $maincategory_id = self::get_project_meta( WTGPORTALMANAGER_PUBLIC_PROJECTID, 'maincategory', true );
            if( is_numeric( $maincategory_id ) ) { 
                $blogposts_status = true;
            }
        }
        
        // load new versions (software project management solutions) status
        
        // load price drop (integrated cart plugins) status
        
        // load new WTG Tasks Manager (or other solutions) entries status  
        
        // build an array of html wrapped items - we need to order items by ['updatetime']
        $html_wrapped_items_array = array();
                                 
        // add Twitter items
        if( $twitter_status === true ) {  
                              
            // we have the ability to pass arguments to this, it is optional
            $this->TWITTER = self::load_class( "WTGPORTALMANAGER_Twitter", "class-twitter.php", 'classes' );   
             
            // Establish a set of Twitter API credentials.
            $portals_api_credentials = self::get_portals_twitter_api( WTGPORTALMANAGER_PUBLIC_PROJECTID );

            // Establish if any values are going to cause a problem.
            $problem_found = false; 
            if( is_array( $portals_api_credentials ) ) {

                // Replace portals configuration with the credentials passed to this method.
                if( isset( $atts['usertimeline'] ) && is_string( $atts['usertimeline'] ) ) {
					$portals_api_credentials['screenname'] = $atts['usertimeline'];
                }
                            	
                // usertimeline
                if( !$portals_api_credentials['screenname'] || $portals_api_credentials['screenname'] == '' ) { 
                    $problem_found = 'screenname'; 
                }
                
                // consumer key
                if( !isset( $portals_api_credentials['consumer_key'] ) || !is_string( $portals_api_credentials['consumer_key'] ) ) { 
                    $problem_found = 'consumer_key'; 
                }

                // consumer secret
                if( !isset( $portals_api_credentials['consumer_secret'] ) || !is_string( $portals_api_credentials['consumer_secret'] ) ) { 
                    $problem_found = 'consumer_secret'; 
                }

                // access token
                if( !isset( $portals_api_credentials['access_token'] ) || !is_string( $portals_api_credentials['access_token'] ) ) { 
                    $problem_found = 'access_token'; 
                }

                // token secret
                if( !isset( $portals_api_credentials['token_secret'] ) || !is_string( $portals_api_credentials['token_secret'] ) ) { 
                    $problem_found = 'token_secret'; 
                }
            } 
            else {
				$problem_found = 'The current portal does not have any Twitter credentials.';
			    self::addtrace( 
            		__FUNCTION__, 
            		__LINE__, 
            		$portals_api_credentials, 
            		$problem_found 
            	);		
            }

            // If the $api_credentials giving are not valid then use default to help reduce problems.
            if( $problem_found !== false ) {

            	self::addtrace( 
            		__FUNCTION__, 
            		__LINE__, 
            		$problem_found, 
            		'Problem was found so stepping into load default Twitter API credentials.' 
            	);
            	
                $default_api_credentials = $this->TWITTER->get_default_credentials();
                
                // A single missing value means we cannot continue.    
                $invalid_default_credentials = false;
                           
                // Write over defaults with the credentials passed to this method.
                if( isset( $atts['usertimeline'] ) && is_string( $atts['usertimeline'] ) ) {
					$default_api_credentials['screenname'] = $atts['usertimeline'];
                }
                
                // usertimeline
                if( !$default_api_credentials['screenname'] ) { $invalid_default_credentials = 'screenname'; }
                
                // consumer key
                if( !$default_api_credentials['consumer_key'] ) { $invalid_default_credentials = 'consumer_key'; }
                
                // consumer secret
                if( !$default_api_credentials['consumer_secret'] ) { $invalid_default_credentials = 'consumer_secret'; }
                
                // access token                                                       
                if( !$default_api_credentials['access_token'] ) { $invalid_default_credentials = 'access_token'; }
                
                // token secret                                                             
                if( !$default_api_credentials['token_secret'] ) { $invalid_default_credentials = 'token_secret'; }
                
                // Discontinue if default values are not complete/invalid.
                if( $invalid_default_credentials !== false ) { return; }
                
                // Default credentials can be used. 
                $final_twitter_credentials = $default_api_credentials;
            } else {
                 // Default credentials can be used. 
                $final_twitter_credentials = $portals_api_credentials;               
            }
        
            // Call Twitter using established credentials.                              
            $result = $this->TWITTER->startTwitter( 
                $final_twitter_credentials, 
                20, 
                false, 
                WTGPORTALMANAGER_PUBLIC_PROJECTID 
            );
        
        	if( isset( $result['error'] ) ) {
            	self::addtrace( 
            		__FUNCTION__, 
            		__LINE__, 
            		$result['error'], 
            		'The startTwitter() call returned an error.' 
            	);				
        	}
        	elseif( !isset( $result['error'] ) && is_array( $result ) ) {     
                       
                foreach( $result as $key => $item ) { 
                    
                    $date = new DateTime( $item['created_at'] );
                    $made_DateTime = $date->format('Y-m-d H:i:s');

                    $new_item = '';    
                    $new_item .= '<li><img src="' . WTGPORTALMANAGER_IMAGES_URL . '/logos/twitter_logo_100x100.png">';
                    $new_item .= '<p>' . $made_DateTime . '</p>';
                    $new_item .= '<br>';
                    $new_item .= '<p>' . $item['text'] . '</p>';
                    $new_item .= '</li>';
                    
                    $html_wrapped_items_array[ strtotime( $item['created_at'] ) ] = $new_item;
                }
            }
        }   
              
        // add Facebook items 
        if( $facebook_status ) {
                 
        }
        
        // add Forum items
        if( $forum_status ) {    
            
            $this->PHPBB = self::load_class( "WTGPORTALMANAGER_PHPBB", "class-phpbb.php", 'classes','pluginmenu' );
            
            // check cache
            $cached_query = get_transient( 'wtgportalmanager_updatepage_phpbb' . WTGPORTALMANAGER_PUBLIC_PROJECTID );

            if( $cached_query ) 
            {
                $result = $cached_query;
            }
            else
            {
                $result = $this->PHPBB->get_posts_inrange_simple( 0, 9999999999, null, null, 20 ); 
                
                // cache result 
                set_transient( 'wtgportalmanager_updatepage_phpbb' . WTGPORTALMANAGER_PUBLIC_PROJECTID, $result, 600 );
            }
            
            if( is_array( $result ) ) {     
                  
                foreach( $result as $key => $item ) {    
       
                    $new_item = '';     
                    $new_item .= '<li><img src="' . WTGPORTALMANAGER_IMAGES_URL . '/logos/phpbb_logo_100x100.png">';
                    $new_item .= '<p>' . date( 'Y-m-d H:i:s', $item['post_time'] ) . '</p>';
                    $new_item .= '<br>';
                    $new_item .= '<p>' . $item['post_subject'] . '</p>';
                    $new_item .= '</li>';
                    
                    $html_wrapped_items_array[ (int) $item['post_time']  ] = $new_item;
                }
            }              
        }
        
        // add WP posts as items
        if( $blogposts_status ) {
             
            // check cache
            $cached_query = get_transient( 'wtgportalmanager_updatepage_wpposts' . WTGPORTALMANAGER_PUBLIC_PROJECTID );

            // get data (cached or new query)
            if( $cached_query ) 
            {
                $result = $cached_query;
            }
            else
            {
                $args = array(
                    'posts_per_page'   => 20,
                    'category'         => $maincategory_id,
                    'post_type'        => 'post',
                    'post_status'      => 'publish',
                    'suppress_filters' => true 
                );  
                            
                $result = get_posts( $args ); 
                 
                if( $result ) {
                    // cache result 
                    set_transient( 'wtgportalmanager_updatepage_wpposts' . WTGPORTALMANAGER_PUBLIC_PROJECTID, $result, 600 );
                }
            }
            
            // add new blog posts as items in our list
            if( $result ) {     
                  
                foreach( $result as $key => $item ) {    
       
                    $new_item = '';     
                    $new_item .= '<li><img src="' . WTGPORTALMANAGER_IMAGES_URL . '/logos/wordpress_logo_100x100.png">';
                    $new_item .= '<p>' . $item->post_date . '</p>';
                    $new_item .= '<br>';
                    $new_item .= '<p>' . $this->PHP->truncatestring( $item->post_content, 150 ) . '</p>';
                    $new_item .= '</li>';
                    
                    $html_wrapped_items_array[ strtotime( $item->post_date )  ] = $new_item;
                }
            }
        }
        
        // add new version notifications
        if( $newversions_status ) {
            
        }
        
        // add price drops
        if( $pricedrop_status ) {
            
        }
        
        // add new TODO tasks
        if( $newtasks_status ) {
            
        }
              
        // put array into order by key (the time)
        krsort ( $html_wrapped_items_array, SORT_NUMERIC  );
          
        // output (replaces shortcode with the value we return)
        $output = '
        <div class="portalmanager_update_item">
            <ul>
        ';
                  
        foreach( $html_wrapped_items_array as $key => $next_item ) {
            $output .= $next_item;    
        }
        
        $output .= '
            </ul>
        </div>';
        
        // cache our entire result to prevent refreshing of the updates page causing flooding
        // with a cache per source it is less likely, however if enough sources are configured
        // there is potential for the Updates page to be very demanding         
        set_transient( 'wtgportalmanager_updatepage' . WTGPORTALMANAGER_PUBLIC_PROJECTID, $output, 60 );
  
        return $output;
    }
        
    /**
    * Begin setting values for portal.
    * 
    * Values required in both admin and public should go in this method. Use
    * the other methods for handling either environment. 
    *
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.1
    */
    public function init_project() {

        // admin
        if( is_admin() ) {
            self::init_project_admin();
            return;    
        }   
         
        // public procedure
        self::init_project_public();
        
    }
    
    /**
    * Set portal values on admin side, easier to do that public side. 
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @since 0.1.1
    * @version 1.0
    */
    public function init_project_admin() {
        
        // set defaults for this method only
        $portal_available = false;
        $name = __( 'No Active Portal', 'wtgportalmanager' );
        $description = __( 'No Active Portal', 'wtgportalmanager' );
        $found_a_project = false;
        
        // get active project ID
        $get_id = $this->DB->get_active_project_id();
        
        if( is_numeric( $get_id ) ) {
            
            $p = $this->DB->get_project( $get_id );
            
            $found_a_project = true;
            
        } elseif( is_numeric( $get_id ) ) {

            // get all projects
            $get_projects = $this->DB->get_projects();
         
                    /* 
                        'project_id' => string '1' (length=1)
                        'timestamp' => string '2015-09-27 20:56:49' (length=19)
                        'projectname' => string 'WTG Portal Manager' (length=10)
                        'description' => null
                        'mainmanager' => null
                        'phase' => null
                        'archived' => string '0' (length=1)
                   */
                           
            if( !$get_projects ) {
                // arriving here means no project exists
                // if no projects, create new notice and forward to main page
                $this->UI->create_notice( 
                    "You need to create a new portal project before visiting
                    the plugins other pages. You have been returned to the 
                    plugins main page where you will find a form to create one.", 
                    'info', 
                    'Small', 
                    "Please Create A Portal"
                );     
            }          

            // arriving here means we have one or more projects to use
            if( $get_projects ) {
                
                // get a none archived project            
                foreach( $get_projects as $key => $p ) {

                    // activate another project
                    if( !$p['archived'] ) {
                        
                        $found_a_project = true;
                        
                        self::activate_project( 
                            $p['project_id'], 
                            get_current_user_id() 
                        );   
                        
                         $this->UI->create_notice( 
                            "A portal project has just been activated because 
                            the previously activated one could not be found. 
                            Please ensure the activated portal is the one you
                            wish to work on.", 
                            'warning', 
                            'Small', 
                            "Portal Project Activated"
                        );
   
                        // prevent further iteration and use $p later
                        break;    
                    }                        
                }
            }
            
        } 
        
        // do not attempt to set constants if no project found
        if( !$found_a_project ) { 
            
            // arriving here could be an unexpected state but it could mean
            // there are projects but they are archived. That will probably be
            // very rare and the archived ability isn't even fully available
            $this->UI->create_notice( 
                "You do not appear to have any portal projects or all your portals have been
                archived. You were redirected to this page where you can create a portal.", 
                'info', 
                'Small', 
                "Please Create A Portal"
            ); 
            
            return false;
        }
               
        // set constants for current project
        define( "WTGPORTALMANAGER_PROJECT_ID", $p['project_id'] );
        define( "WTGPORTALMANAGER_PROJECT_NAME", $p['projectname'] );
        define( "WTGPORTALMANAGER_PROJECT_DESC", $p['description'] );
        define( "WTGPORTALMANAGER_PROJECT_MANAGER", $p['mainmanager'] );
        define( "WTGPORTALMANAGER_PROJECT_ARCHIVED", $p['archived'] );       

    }
    
    /**
    * Sets project values for public visitors, uses different methods from
    * admin because it's not as simple determining if the visitor is viewing
    * a portal or not. 
    * 
    * I have not yet come up with a way to allow a page used in a portal to 
    * also be treated outside of a portal and not get portal treatment. I 
    * predict this will be an issue eventually. I myself already want to include
    * pages in multiple portals i.e. terms and conditions for WTG Portal Manager would be
    * in the WTG Portal Manager portal and the Terms & Conditions portal. Determining
    * which portal the page is being viewed in may be tricky.                                     
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @since 0.1.1
    * @version 1.2
    */
    public function init_project_public() {
        global $wpdb;

        $post_id = url_to_postid( $this->PHP->currenturl() );
        
        $post_type = get_post_type( $post_id );
        
        // category is handled differently 
        if( isset( $_GET['cat'] ) && is_numeric( $_GET['cat'] ) ) 
        {
            // we will get a portal id IF is a main category                
            $project_id = $this->DB->get_value( 
                'project_id', 
                $wpdb->prefix . 'webtechglobal_projectsmeta', 
                "meta_key = 'maincategory' AND meta_value = '"  . $_GET['cat'] . "'" 
            ); 
            
            if( $project_id && is_numeric( $project_id ) ) 
            {   
                define( "WTGPORTALMANAGER_PUBLIC_PROJECTID", $project_id );     
            } 
            else
            {   
                // try with a sub-category
                $project_id = $this->DB->get_value( 
                    'project_id', 
                    $wpdb->prefix . 'webtechglobal_projectsmeta', 
                    "meta_key = 'subcategory' AND meta_value = '"  . $_GET['cat'] . "'" 
                );    
                
                if( $project_id && is_numeric( $project_id ) ) 
                {
                    define( "WTGPORTALMANAGER_PUBLIC_PROJECTID" , $project_id );   
                }                    
            }
        }
        elseif( $post_id !== 0 && is_numeric( $post_id ) && $post_type !== false ) 
        {       
            // is current page in project meta as a linked page?
            $project_meta_row = $this->DB->selectrow( 
                $wpdb->prefix . 'webtechglobal_projectsmeta', 
                "meta_value = '$post_id' AND meta_key = 'page'", 
                'webtechglobal_projects_id' 
            );
                
            $pid = false;
            if( isset( $project_meta_row->webtechglobal_projects_id ) ) {
                $pid = $project_meta_row->webtechglobal_projects_id; 
            }
            
            if( is_numeric( $pid ) ) { 
                define( "WTGPORTALMANAGER_PUBLIC_PROJECTID", $pid );
            }
        }    
    }

    /**
    * Admin toolbar for developers.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @version 1.0
    */
    function admin_toolbars() {   
        if( user_can( get_current_user_id(), 'activate_plugins' ) ) {
            $this->GLOBALUI->developer_toolbar();
        }        
    }
               
    /**
    * register admin only .css must be done before printing styles
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function plugin_admin_register_styles() {
        wp_register_style( 'wtgportalmanager_css_notification',plugins_url( 'wtg-portal-manager/css/notifications.css' ), array(), '1.0.0', 'screen' );
        wp_register_style( 'wtgportalmanager_css_admin',plugins_url( 'wtg-portal-manager/css/admin.css' ), __FILE__);          
    }
    
    /**
    * print admin only .css - the css must be registered first
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function plugin_admin_print_styles() {
        wp_enqueue_style( 'wtgportalmanager_css_notification' );  
        wp_enqueue_style( 'wtgportalmanager_css_admin' );               
    }    
    
    /**
    * queues .js that is registered already
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function plugin_admin_enqueue_scripts() {    
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_style( 'wp-pointer' );          
    }    
   
    /**
     * Enqueue a CSS file with ability to switch from .min for debug
     *
     * @since 0.0.1
     *
     * @param string $name Name of the CSS file, without extension(s)
     * @param array $dependencies List of names of CSS stylesheets that this stylesheet depends on, and which need to be included before this one
     */
    public function enqueue_style( $name, array $dependencies = array() ) {
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        $css_file = "css/{$name}{$suffix}.css";
        $css_url = plugins_url( $css_file, WTGPORTALMANAGER__FILE__ );
        wp_enqueue_style( "wtgportalmanager-{$name}", $css_url, $dependencies, WTGPORTALMANAGER::version );
    }
    
    /**
     * Enqueue a JavaScript file, can switch from .min for debug,
     * possibility with dependencies and extra information
     *
     * @since 0.0.1
     *
     * @param string $name Name of the JS file, without extension(s)
     * @param array $dependencies List of names of JS scripts that this script depends on, and which need to be included before this one
     * @param bool|array $localize_script (optional) An array with strings that gets transformed into a JS object and is added to the page before the script is included
     * @param bool $force_minified Always load the minified version, regardless of SCRIPT_DEBUG constant value
     */
    public function enqueue_script( $name, array $dependencies = array(), $localize_script = false, $force_minified = false ) {
        $suffix = ( ! $force_minified && defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        $js_file = "js/{$name}{$suffix}.js";
        $js_url = plugins_url( $js_file, WTGPORTALMANAGER__FILE__ );
        wp_enqueue_script( "wtgportalmanager-{$name}", $js_url, $dependencies, WTGPORTALMANAGER_VERSION, true );
    }  
    
    /**
    * Register and enqueue CSS for public pages. This method is all that is needed
    * from within this plugin to applying styling to none admin pages.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function plugin_enqueue_public_styles() {    
        wp_register_style( 'wtgportalmanager_css_public',plugins_url( 'wtg-portal-manager/css/public.css' ), __FILE__);
        wp_enqueue_style( 'wtgportalmanager_css_public' );
    }
    
    /**
    * returns the WTGPORTALMANAGER_WPMain class object already created in this WTGPORTALMANAGER class
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function class_wpmain() {
        return $this->wpmain;
    }

    /**  
     * Set up actions for each page
     *
     * @since 0.0.1
     */
    public function add_adminpage_actions() {
        // register callbacks to trigger load behavior for admin pages
        foreach ( $this->page_hooks as $page_hook ) {
            add_action( "load-{$page_hook}", array( $this, 'load_admin_page' ) );
        }
    }
        
    /**
     * Render the view that has been initialized in load_admin_page() (called by WordPress when the actual page content is needed)
     *
     * @since 0.0.1
     */
    public function show_admin_page() {   
        $this->view->render();
    }    
    
    /**
     * Create a new instance of the $view, which is stored in the "views" subfolder, and set it up with $data
     * 
     * Requires a main view file to be stored in the "views" folder, unlike the original view approach.
     * 
     * Do not move this to another file not even interface classes 
     *
     * @since 0.0.1
     * @uses load_class()
     *
     * @param string $view Name of the view to load
     * @param array $data (optional) Parameters/PHP variables that shall be available to the view
     * @return object Instance of the initialized view, already set up, just needs to be render()ed
     */
    public static function load_draggableboxes_view( $page_slug, array $data = array() ) {
        // include the view class
        require_once( WTGPORTALMANAGER_ABSPATH . 'classes/class-view.php' );
        
        // make first letter uppercase for a better looking naming pattern
        $ucview = ucfirst( $page_slug );// this is page name 
        
        // get the file name using $page and $tab_number
        $dir = 'views';
        
        // include the view file and run the class in that file                                
        $the_view = self::load_class( "WTGPORTALMANAGER_{$ucview}_View", "{$page_slug}.php", $dir );
                       
        $the_view->setup( $page_slug , $data );
        
        return $the_view;
    }

    /**
     * Generate the complete nonce string, from the nonce base, the action and an item
     *
     * @since 0.0.1
     *
     * @param string $action Action for which the nonce is needed
     * @param string|bool $item (optional) Item for which the action will be performed, like "table"
     * @return string The resulting nonce string
     */
    public static function nonce( $action, $item = false ) {
        $nonce = "wtgportalmanager_{$action}";
        if ( $item ) {
            $nonce .= "_{$item}";
        }
        return $nonce;
    }
    
    /**
     * Begin render of admin screen
     * 1. determining the current action
     * 2. load necessary data for the view
     * 3. initialize the view
     * 
     * @uses load_draggableboxes_view() which includes class-view.php
     * 
     * @author Ryan Bayne
     * @package WTG Portal Manager
     * @since 0.0.1
     * @version 1.1
     */
     public function load_admin_page() { 

        // remove "wtgportalmanager_" from page value in URL which leaves the page name as used in the menu array
        $page = 'main';
        if( isset( $_GET['page'] ) && $_GET['page'] !== 'wtgportalmanager' ){    
            $page = substr( $_GET['page'], strlen( 'wtgportalmanager_' ) );
        }

        // pre-define data for passing to views
        $data = array( 'datatest' => 'A value for testing' );

        // depending on page load extra data
        switch ( $page ) {          
            case 'updateplugin':
   
                break;            
            case 'betatesting':
                $data['mydatatest'] = 'Testing where this goes and how it can be used during call for ' . $page;
                break;
        }
          
        // prepare and initialize draggable panel view for prepared pages
        // if this method is not called the plugin uses the old view method
        $this->view = $this->load_draggableboxes_view( $page, $data );
    }   
                   
    protected function add_actions( $plugin_actions ) {          
        foreach( $plugin_actions as $actionArray ) {        
            list( $action, $details, $whenToLoad ) = $actionArray;
                                   
            if(!$this->should_filter_or_action_load( $whenToLoad ) ) {      
                continue;
            }
                 
            switch(count( $details) ) {         
                case 3:
                    add_action( $action, array( $this, $details[0] ), $details[1], $details[2] );     
                break;
                case 2:
                    add_action( $action, array( $this, $details[0] ), $details[1] );   
                break;
                case 1:
                default:
                    add_action( $action, array( $this, $details) );
            }
        }    
    }
    
    protected function add_filters( $filters ) {
        foreach( $filters as $filterArray ) {
            list( $filter, $details, $whenToLoad ) = $filterArray;
                           
            if(!$this->should_filter_or_action_load( $whenToLoad ) ) {
                continue;
            }
            
            switch(count( $details) ) {
                case 3:
                    add_filter( $filter, array( $this, $details[0] ), $details[1], $details[2] );
                break;
                case 2:
                    add_filter( $filter, array( $this, $details[0] ), $details[1] );
                break;
                case 1:
                default:
                    add_filter( $filter, array( $this, $details) );
            }
        }    
    }    
    
    /**
    * Should the giving action or filter be loaded?
    * 1. we can add security and check settings per case, the goal is to load on specific pages/areas
    * 2. each case is a section and we use this approach to load action or filter for specific section
    * 3. In early development all sections are loaded, this function is prep for a modular plugin
    * 4. addons will require core functions like this to be updated rather than me writing dynamic functions for any possible addons
    *  
    * @param mixed $whenToLoad
    */
    private function should_filter_or_action_load( $whenToLoad) {
        $wtgportalmanager_settings = $this->adminsettings();
          
        switch( $whenToLoad) {
            case 'all':    
                return true;
            break;
            case 'adminpages':// load when logged into admin and on any admin page
            
                if( is_admin() ){return true;}
                return false;    
                
            break;
            case 'publicpages':// when not on admin pages
            
                if( !is_admin() ){return true;}
                return false;    
                
            break;
            case 'pluginscreens':// load when on a WTG Portal Manager admin screen
            
                if( isset( $_GET['page'] ) && strstr( $_GET['page'], 'wtgportalmanager' ) ){return true;}
                return false;    
                
            break;            
            case 'pluginscreensorfront':// load when on a WTG Portal Manager admin screen
            
            	// Return true and allow the action to be registered if on a plugins own admin page.
                if( isset( $_GET['page'] ) && strstr( $_GET['page'], 'wtgportalmanager' ) ){return true;}
                
                // Return true and allow the action to be registered if viewing the front end.
                if( !is_admin() ) { return true; }
                
                return false;    
                
            break;            
            case 'pluginanddashboard':

                if( self::is_dashboard() ) {
                    return true;    
                }

                if( isset( $_GET['page'] ) && strstr( $_GET['page'], 'wtgportalmanager' ) ){
                    return true;
                }
                
                return false;    
            break;
            case 'projects':
            
                return true;   
                 
            break;            
            case 'systematicpostupdating':  
            
                if(!isset( $wtgportalmanager_settings['standardsettings']['systematicpostupdating'] ) || $wtgportalmanager_settings['standardsettings']['systematicpostupdating'] != 'enabled' ){
                    return false;    
                }      
                return true;
                
            break;
            case 'admin_notices':                         

                if( self::is_dashboard() ) {
                    return true;    
                }
                                                           
                if( isset( $_GET['page'] ) && strstr( $_GET['page'], 'wtgportalmanager' ) ){
                    return true;
                }
                                                                                                   
                return false;
            break;
            case 'administrator':

                require_once( ABSPATH . '/wp-load.php');
                if(!function_exists('get_currentuserinfo')){
                    require_once( ABSPATH . WPINC . '/formatting.php' );
                    require_once( ABSPATH . WPINC . '/capabilities.php' );
                    require_once( ABSPATH . WPINC . '/user.php' );
                    require_once( ABSPATH . WPINC . '/meta.php' );
                    require_once( ABSPATH . WPINC . '/pluggable.php' );
                    wp_cookie_constants( );
                }
            
                if( is_user_logged_in() ) { return true; }
                if( current_user_can( 'activate_plugins' ) ) { return true; }
                
                return false; 
            break;           
        }

        return true;
    }   
    
    /**
    * Determine if on the dashboard page. 
    * 
    * $current_screen is not set early enough for calling in some actions. So use this
    * function instead.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function is_dashboard() {
        global $pagenow;
        // method one: check $pagenow value which could be "index.php" and that means the dashboard
        if( isset( $pagenow ) && $pagenow == 'index.php' ) { return true; }
        // method two: should $pagenow not be set, check the server value
        return strstr( $this->PHP->currenturl(), 'wp-admin/index.php' );
    }
      
    /**
    * "The wp_insert_post action is called with the same parameters as the save_post action 
    * (the post ID for the post being created), but is only called for new posts and only 
    * after save_post has run." 
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function hook_insert_post( $post_id ){
        /*
        // establish correct procedure for the post type that was inserted
        $post_type = get_post_type( $post_id );
      
        switch ( $post_type) {
            case 'exampleone':
                
                break;
            case 'c 2pnotinuseyet':
                
                break;
        } 
        */
    }
                      
    /**
     * Add a widget to the dashboard.
     *
     * This function is hooked into the 'wp_dashboard_setup' action below.
     */
     
    /**
    * Hooked by wp_dashboard_setup
    * 
    * @uses WTGPORTALMANAGER_ADMINUI::add_dashboard_widgets() which has the widgets
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function add_dashboard_widgets() {
        $this->UI->add_dashboard_widgets();            
    }  
            
    /**
    * Determines if the plugin is fully installed or not
    * 
    * NOT IN USE - I've removed a global and a loop pending a new class that will need to be added to this function
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */       
    public function is_installed() {
        return true;        
    }                       

    public function screen_options() {
        global $pippin_sample_page;
        $screen = get_current_screen();

        // toplevel_page_wtgportalmanager (main page)
        if( $screen->id == 'toplevel_page_wtgportalmanager' ){
            $args = array(
                'label' => __( 'Members per page' ),
                'default' => 1,
                'option' => 'wtgportalmanager_testoption'
            );
            add_screen_option( 'per_page', $args );
        }     
    }

    public function save_screen_option( $status, $option, $value ) {
        if ( 'wtgportalmanager_testoption' == $option ) return $value;
    }
      
    /**
    * WordPress Help tab content builder
    * 
    * Using class-help.php we can make use of help information and add extensive support text.
    * The plan is to use a SOAP API that gets the help text from the WebTechGlobal server.
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0.2
    */
    public function help_tab () {
        global $wtgportalmanager_menu_array;
                               
        // get the current screen array
        $screen = get_current_screen();
        
        // load help class which contains help content array
        $WTGPORTALMANAGER_Help = self::load_class( 'WTGPORTALMANAGER_Help', 'class-help.php', 'classes' );

        // call the array
        $help_array = $WTGPORTALMANAGER_Help->get_help_array();
             
        // get page name i.e. wtgportalmanager_page_wtgportalmanager_affiliates would return affiliates
        $page_name = $this->PHP->get_string_after_last_character( $screen->id, '_' );
        
        // if on main page "wtgportalmanager" then set tab name as main
        if( $page_name == 'wtgportalmanager' ){$page_name = 'main';}
     
        // does the page have any help content? 
        if( !isset( $menu_array[ $page_name ] ) ){
            return false;
        }
        
        // set view name
        $view_name = $page_name;

        // does the view have any help content
        if( !isset( $help_array[ $page_name ][ $view_name ] ) ){
            return false;
        }
              
        // build the help content for the view
        $help_content = '<p>' . $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewabout' ] . '</p>';

        // add a link encouraging user to visit site and read more OR visit YouTube video
        if( isset( $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewreadmoreurl' ] ) ){
            $help_content .= '<p>';
            $help_content .= __( 'You are welcome to visit the', 'wtgportalmanager' ) . ' ';
            $help_content .= '<a href="' . $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewreadmoreurl' ] . '"';
            $help_content .= 'title="' . __( 'Visit the WTG Portal Manager website and read more about', 'wtgportalmanager' ) . ' ' . $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewtitle' ] . '"';
            $help_content .= 'target="_blank"';
            $help_content .= '>';
            $help_content .= __( 'WTG Portal Manager Website', 'wtgportalmanager' ) . '</a> ' . __( 'to read more about', 'wtgportalmanager' ) . ' ' . $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewtitle' ];           
            $help_content .= '.</p>';
        }  
        
        // add a link to a Youtube
        if( isset( $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewvideourl' ] ) ){
            $help_content .= '<p>';
            $help_content .= __( 'There is a', 'wtgportalmanager' ) . ' ';
            $help_content .= '<a href="' . $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewvideourl' ] . '"';
            $help_content .= 'title="' . __( 'Go to YouTube and watch a video about', 'wtgportalmanager' ) . ' ' . $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewtitle' ] . '"';
            $help_content .= 'target="_blank"';
            $help_content .= '>';            
            $help_content .= __( 'YouTube Video', 'wtgportalmanager' ) . '</a> ' . __( 'about', 'wtgportalmanager' ) . ' ' . $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewtitle' ];           
            $help_content .= '.</p>';
        }

        // add a link to a forum discussion
        if( isset( $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewdiscussurl' ] ) ){
            $help_content .= '<p>';
            $help_content .= __( 'We invite you to take discuss', 'wtgportalmanager' ) . ' ';
            $help_content .= '<a href="' . $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewdiscussurl' ] . '"';
            $help_content .= 'title="' . __( 'Visit the WebTechGlobal forum to discuss', 'wtgportalmanager' ) . ' ' . $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewtitle' ] . '"';
            $help_content .= 'target="_blank"';
            $help_content .= '>';            
            $help_content .= $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewtitle' ] . '</a> ' . __( 'on the WebTechGlobal Forum', 'wtgportalmanager' );           
            $help_content .= '.</p>';
        }         

        // finish by adding the first tab which is for the view itself (soon to become registered pages) 
        $screen->add_help_tab( array(
            'id'    => $page_name,
            'title'    => __( 'About', 'wtgportalmanager' ) . ' ' . $help_array[ $page_name ][ $view_name ][ 'viewinfo' ][ 'viewtitle' ] ,
            'content'    => $help_content,
        ) );
  
        // add a tab per form
        $help_content = '';
        foreach( $help_array[ $page_name ][ $view_name ][ 'forms' ] as $form_id => $value ){
                                
            // the first content is like a short introduction to what the box/form is to be used for
            $help_content .= '<p>' . $value[ 'formabout' ] . '</p>';
                         
            // add a link encouraging user to visit site and read more OR visit YouTube video
            if( isset( $value[ 'formreadmoreurl' ] ) ){
                $help_content .= '<p>';
                $help_content .= __( 'You are welcome to visit the', 'wtgportalmanager' ) . ' ';
                $help_content .= '<a href="' . $value[ 'formreadmoreurl' ] . '"';
                $help_content .= 'title="' . __( 'Visit the WTG Portal Manager website and read more about', 'wtgportalmanager' ) . ' ' . $value[ 'formtitle' ] . '"';
                $help_content .= 'target="_blank"';
                $help_content .= '>';
                $help_content .= __( 'WTG Portal Manager Website', 'wtgportalmanager' ) . '</a> ' . __( 'to read more about', 'wtgportalmanager' ) . ' ' . $value[ 'formtitle' ];           
                $help_content .= '.</p>';
            }  
            
            // add a link to a Youtube
            if( isset( $value[ 'formvideourl' ] ) ){
                $help_content .= '<p>';
                $help_content .= __( 'There is a', 'wtgportalmanager' ) . ' ';
                $help_content .= '<a href="' . $value[ 'formvideourl' ] . '"';
                $help_content .= 'title="' . __( 'Go to YouTube and watch a video about', 'wtgportalmanager' ) . ' ' . $value[ 'formtitle' ] . '"';
                $help_content .= 'target="_blank"';
                $help_content .= '>';            
                $help_content .= __( 'YouTube Video', 'wtgportalmanager' ) . '</a> ' . __( 'about', 'wtgportalmanager' ) . ' ' . $value[ 'formtitle' ];           
                $help_content .= '.</p>';
            }

            // add a link to a forum discussion
            if( isset( $value[ 'formdiscussurl' ] ) ){
                $help_content .= '<p>';
                $help_content .= __( 'We invite you to discuss', 'wtgportalmanager' ) . ' ';
                $help_content .= '<a href="' . $value[ 'formdiscussurl' ] . '"';
                $help_content .= 'title="' . __( 'Visit the WebTechGlobal forum to discuss', 'wtgportalmanager' ) . ' ' . $value[ 'formtitle' ] . '"';
                $help_content .= 'target="_blank"';
                $help_content .= '>';            
                $help_content .= $value[ 'formtitle' ] . '</a> ' . __( 'on the WebTechGlobal Forum', 'wtgportalmanager' );           
                $help_content .= '.</p>';
            } 
                               
            // loop through options
            foreach( $value[ 'options' ] as $key_two => $option_array ){  
                $help_content .= '<h3>' . $option_array[ 'optiontitle' ] . '</h3>';
                $help_content .= '<p>' . $option_array[ 'optiontext' ] . '</p>';
                            
                if( isset( $option_array['optionurl'] ) ){
                    $help_content .= ' <a href="' . $option_array['optionurl'] . '"';
                    $help_content .= ' title="' . __( 'Read More about', 'wtgportalmanager' )  . ' ' . $option_array['optiontitle'] . '"';
                    $help_content .= ' target="_blank">';
                    $help_content .= __( 'Read More', 'wtgportalmanager' ) . '</a>';      
                }
      
                if( isset( $option_array['optionvideourl'] ) ){
                    $help_content .= ' - <a href="' . $option_array['optionvideourl'] . '"';
                    $help_content .= ' title="' . __( 'Watch a video about', 'wtgportalmanager' )  . ' ' . $option_array['optiontitle'] . '"';
                    $help_content .= ' target="_blank">';
                    $help_content .= __( 'Video', 'wtgportalmanager' ) . '</a>';      
                }
            }
            
            // add the tab for this form and its help content
            $screen->add_help_tab( array(
                'id'    => $page_name . $view_name,
                'title'    => $help_array[ $page_name ][ $view_name ][ 'forms' ][ $form_id ][ 'formtitle' ],
                'content'    => $help_content,
            ) );                
                
        }
  
    }  

    /**
    * Gets the required capability for the plugins page from the page array
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    *  
    * @param mixed $wtgportalmanager_page_name
    * @param mixed $default
    */
    public function get_page_capability( $page_name ){
        $capability = 'administrator';// script default for all outcomes

        // get stored capability settings 
        $saved_capability_array = get_option( 'wtgportalmanager_capabilities' );
                
        if( isset( $saved_capability_array['pagecaps'][ $page_name ] ) && is_string( $saved_capability_array['pagecaps'][ $page_name ] ) ) {
            $capability = $saved_capability_array['pagecaps'][ $page_name ];
        }
                   
        return $capability;   
    }   
    
    /**
    * WordPress plugin menu
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.2.8
    */
    public function admin_menu() {    
        global $wtgportalmanager_settings, $wtgportalmanager_menu_array;

        // set the callback, we can change this during the loop and call methods more dynamically
        // this approach allows us to call the same function for all pages
        $subpage_callback = array( $this, 'show_admin_page' );
                
        // menu array is modified a little so we create a new variable for it
        $WTGPORTALMANAGER_Menu = $wtgportalmanager_menu_array;
    
        // add menu
        $this->page_hooks[] = add_menu_page( $WTGPORTALMANAGER_Menu['main']['title'], 
        __( 'WTG Portal Manager', 'wtgportalmanager' ), 
        'administrator', 
        'wtgportalmanager',  
        $subpage_callback ); 
        
        // help tab                                                 
        add_action( 'load-toplevel_page_wtgportalmanager', array( $this, 'help_tab' ) );

        // track which group has already been displayed using the parent name
        $groups = array();
        
        // remove arrayinfo from the menu array
        unset( $WTGPORTALMANAGER_Menu['arrayinfo'] );
        
        // get all group menu titles
        $group_titles_array = array();
        foreach( $WTGPORTALMANAGER_Menu as $key_pagename => $page_array ){ 
            if( $page_array['parent'] === 'parent' ){                
                $group_titles_array[ $page_array['groupname'] ]['grouptitle'] = $page_array['menu'];
            }
        }          
        
        // loop through sub-pages - remove pages that are not to be registered
        foreach( $WTGPORTALMANAGER_Menu as $key_pagename => $page_array ){                 

            // if not visiting this plugins pages, simply register all the parents
            if( !isset( $_GET['page'] ) || !strstr( $_GET['page'], 'wtgportalmanager' ) ){
                
                // remove none parents
                if( $page_array['parent'] !== 'parent' ){    
                    unset( $WTGPORTALMANAGER_Menu[ $key_pagename ] ); 
                }        
            
            }elseif( isset( $_GET['page'] ) && strstr( $_GET['page'], 'wtgportalmanager' ) ){
                
                // remove pages that are not the main, the current visited or a parent
                if( $key_pagename !== 'main' && $page_array['slug'] !== $_GET['page'] && $page_array['parent'] !== 'parent' ){
                    unset( $WTGPORTALMANAGER_Menu[ $key_pagename ] );
                }     
                
            } 
            
            // remove the parent of a group for the visited page
            if( isset( $_GET['page'] ) && $page_array['slug'] === $_GET['page'] ){
                unset( $WTGPORTALMANAGER_Menu[ $WTGPORTALMANAGER_Menu[ $key_pagename ]['parent'] ] );
            }
            
            // remove update page as it is only meant to show when new version of files applied
            if( $page_array['slug'] == 'wtgportalmanager_pluginupdate' ) {
                unset( $WTGPORTALMANAGER_Menu[ $key_pagename ] );
            }
        }
                 
        foreach( $WTGPORTALMANAGER_Menu as $key_pagename => $page_array ){ 
                                
            $new_hook = add_submenu_page( 'wtgportalmanager', 
                   $group_titles_array[ $page_array['groupname'] ]['grouptitle'], 
                   $group_titles_array[ $page_array['groupname'] ]['grouptitle'], 
                   self::get_page_capability( $key_pagename ), 
                   $WTGPORTALMANAGER_Menu[ $key_pagename ]['slug'], 
                   $subpage_callback );     
         
            $this->page_hooks[] = $new_hook;
                   
            // help tab                                                 
            add_action( 'load-wtgportalmanager_page_wtgportalmanager_' . $key_pagename, array( $this, 'help_tab' ) );              
        }
    }
    
    /**
     * Tabs menu loader - calls function for css only menu or jquery tabs menu
     * 
     * @param string $thepagekey this is the screen being visited
     */
    public function build_tab_menu( $current_page_name ){    
        global $wtgportalmanager_menu_array;       
                
        echo '<h2 class="nav-tab-wrapper">';
        
        // get the current pages viewgroup for building the correct tab menu
        $view_group = $wtgportalmanager_menu_array[ $current_page_name ][ 'groupname'];
            
        foreach( $wtgportalmanager_menu_array as $page_name => $values ){
                                                         
            if( $values['groupname'] === $view_group ){
                
                $activeclass = 'class="nav-tab"';
                if( $page_name === $current_page_name ){                      
                    $activeclass = 'class="nav-tab nav-tab-active"';
                }
                
                echo '<a href="' . self::create_adminurl( $values['slug'] ) . '" '.$activeclass.'>' . $values['pluginmenu'] . '</a>';       
            }
        }      
        
        echo '</h2>';
    }     

    /**
    * Used to display this plugins notices on none plugin pages i.e. dashboard.
    * 
    * should_filter_or_action_load() decides if the admin_notices hook is called, which hooks this function.
    * I think that check should only check which page is being viewed. Anything more advanced might need to
    * be performed in display_users_notices().
    * 
    * @uses display_users_notices()
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function admin_notices() {
        $this->UI->display_users_notices();
    }
                                 
    /**
    * Popup and content for media button displayed just above the WYSIWYG editor 
    */
    public function pluginmediabutton_popup() {
        global $wtgportalmanager_settings; ?>
        
        <div id="wtgportalmanager_popup_container" style="display:none;">

        </div>
        
        <?php
    }    
    
    /**
    * 
    * Part of the WTG Schedule System for WordPress.
    * 
    * 1. Does not use WP CRON or normal server CRON
    * 2. Another system is being created to use WP CRON
    * 3. WTG system does not allow specific timing, only restriction of specific hours
    * 4. Limits can be applied using this system
    * 5. Overall the same effect can be achieved and without the use of WP CRON
    * 
    * Determines if an event is due and processes what we refer to as an action (event action) it if true.
    * 1. Early in the function we do every possible check to find a reason not to process
    * 2. This function checks all required values exist, else it sets them then returns as this is considered an event action
    * 3. This function itself is considered part of the event, we cycle through event types
    * 
    * Debugging Trace
    * $wtgportalmanager_schedule_array['history']['trace'] is used to indicate how far the this script went before a return.
    * This is a simple way to quickly determine where we are arriving.
    * 
    * @return boolean false if no due events else returns true to indicate event was due and full function ran
    */
    public function event_check() {
        $wtgportalmanager_schedule_array = self::get_option_schedule_array();
        
        // do not continue if WordPress is DOING_AJAX
        if( self::request_made() ){return;}
                      
        self::log_schedule( __( 'The schedule is being checked. There should be further log entries explaining the outcome.', 'wtgportalmanager' ), __( 'schedule being checked', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);
        
        // now do checks that we will store the return reason for to allow us to quickly determine why it goes no further                     
        //  get and ensure we have the schedule array
        //  we do not initialize the schedule array as the user may be in the processing of deleting it
        //  do not use wtgportalmanager_event_refused as we do not want to set the array
        if(!isset( $wtgportalmanager_schedule_array ) || !is_array( $wtgportalmanager_schedule_array ) ){       
            self::log_schedule( __( 'Scheduled events cannot be peformed due to the schedule array of stored settings not existing.', 'wtgportalmanager' ), __( 'schedule settings missing', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);
            return false;
        }
                      
        // check when last event was run - avoid running two events within 1 minute of each other
        // I've set it here because this function could grow over time and we dont want to go through all the checks PER VISIT or even within a few seconds of each other.
        if( isset( $wtgportalmanager_schedule_array['history']['lasteventtime'] ) )
        {    
            // increase lasteventtime by 60 seconds
            $soonest = $wtgportalmanager_schedule_array['history']['lasteventtime'] + 60;//hack info page http://www.webtechglobal.co.uk/hacking/increase-automatic-events-delay-time
            
            if( $soonest > time() ){
                self::log_schedule( __( 'No changed made as it has not been 60 seconds since the last event.', 'wtgportalmanager' ), __( 'enforcing schedule event delay', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);
                self::event_return( __( 'has not been 60 seconds since list event', 'wtgportalmanager' ) ); 
                return;
            }             
        }
        else
        {               
            // set lasteventtime value for the first time
            $wtgportalmanager_schedule_array['history']['lasteventtime'] = time();
            $wtgportalmanager_schedule_array['history']['lastreturnreason'] = __( 'The last even time event was set for the first time, no further processing was done.', 'wtgportalmanager' );
            self::update_option_schedule_array( $wtgportalmanager_schedule_array );
            self::log_schedule( __( 'The plugin initialized the timer for enforcing a delay between events. This action is treated as an event itself and no further
            changes are made during this schedule check.', 'wtgportalmanager' ), __( 'initialized schedule delay timer', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);

            self::event_return( __( 'initialised the last event time value', 'wtgportalmanager' ) );
            return;        
        }                             
                                           
        // is last event type value set? if not set default as dataupdate, this means postcreation is the next event
        if(!isset( $wtgportalmanager_schedule_array['history']['lasteventtype'] ) )
        {    
            $wtgportalmanager_schedule_array['history']['lasteventtype'] = 'dataupdate';
            $wtgportalmanager_schedule_array['history']['lastreturnreason'] = __( 'The last event type value was set for the first time', 'wtgportalmanager' );
            self::update_option_schedule_array( $wtgportalmanager_schedule_array );

            self::log_schedule( __( 'The plugin initialized last event type value, this tells the plugin what event was last performed and it is used to
            determine what event comes next.', 'wtgportalmanager' ), __( 'initialized schedule last event value', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);
            
            self::event_return( __( 'initialised last event type value', 'wtgportalmanager' ) );
            return;
        }
                 
        // does the "day_lastreset"" time value exist, if not we set it now then return
        if(!isset( $wtgportalmanager_schedule_array['history']['day_lastreset'] ) )
        {    
            $wtgportalmanager_schedule_array['history']['day_lastreset'] = time();
            $wtgportalmanager_schedule_array['history']['lastreturnreason'] = __( 'The last daily reset time was set for the first time', 'wtgportalmanager' );
            self::update_option_schedule_array( $wtgportalmanager_schedule_array );
            
            self::log_schedule( __( 'Day timer was set in schedule system. This is the 24 hour timer used to track daily events. It was set, no further action was taking 
            and should only happen once.', 'wtgportalmanager' ), __( '24 hour timer set', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);
            
            self::event_return( __( 'initialised last daily reset time', 'wtgportalmanager' ) );        
            return;
        } 
                                                         
        // does the "hour_lastreset"" time value exist, if not we set it now then return
        if(!isset( $wtgportalmanager_schedule_array['history']['hour_lastreset'] ) )
        { 
            $wtgportalmanager_schedule_array['history']['hour_lastreset'] = time();
            $wtgportalmanager_schedule_array['history']['lastreturnreason'] = __( 'The hourly reset time was set for the first time', 'wtgportalmanager' );
            self::update_option_schedule_array( $wtgportalmanager_schedule_array );
            
            self::log_schedule( __( 'Hourly timer was set in schedule system. The time has been set for hourly countdown. No further action was 
            taking. This should only happen once.', 'wtgportalmanager' ), __( 'one hour timer set', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);        
            
            self::event_return( __( 'initialised hourly reset time', 'wtgportalmanager' ) );
            return;
        }    
               
        // does the hourcounter value exist, if not we set it now then return (this is to initialize the variable)
        if(!isset( $wtgportalmanager_schedule_array['history']['hourcounter'] ) )
        {     
            $wtgportalmanager_schedule_array['history']['hourcounter'] = 0;
            $wtgportalmanager_schedule_array['history']['lastreturnreason'] = __( 'The hourly events counter was set for the first time', 'wtgportalmanager' );
            self::update_option_schedule_array( $wtgportalmanager_schedule_array );
            self::log_schedule( __( 'Number of events per hour has been set for the first time, this change is treated as an event.', 'wtgportalmanager' ), __( 'hourly events counter set', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);     
            self::event_return( __( 'initialised hourly events counter', 'wtgportalmanager' ) );   
            return;
        }     
                                     
        // does the daycounter value exist, if not we set it now then return (this is to initialize the variable)
        if(!isset( $wtgportalmanager_schedule_array['history']['daycounter'] ) )
        {
            $wtgportalmanager_schedule_array['history']['daycounter'] = 0;
            $wtgportalmanager_schedule_array['history']['lastreturnreason'] = __( 'The daily events counter was set for the first time', 'wtgportalmanager' );
            self::update_option_schedule_array( $wtgportalmanager_schedule_array );
            self::log_schedule( __( 'The daily events counter was not set. No further action was taking. This measure should only happen once.', 'wtgportalmanager' ), __( 'daily events counter set', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);     
            self::event_return( __( 'initialised daily events counter', 'wtgportalmanager' ) );           
            return;
        } 

        // has hourly target counter been reset for this hour - if not, reset now then return (this is an event)
        // does not actually start at the beginning of an hour, it is a 60 min allowance not hour to hour
        $hour_reset_time = $wtgportalmanager_schedule_array['history']['hour_lastreset'] + 3600;
        if(time() > $hour_reset_time )
        {     
            // reset hour_lastreset value and the hourlycounter
            $wtgportalmanager_schedule_array['history']['hour_lastreset'] = time();
            $wtgportalmanager_schedule_array['history']['hourcounter'] = 0;
            $wtgportalmanager_schedule_array['history']['lastreturnreason'] = __( 'Hourly counter was reset for another 60 minute period', 'wtgportalmanager' );
            self::update_option_schedule_array( $wtgportalmanager_schedule_array );
            self::log_schedule( __( 'Hourly counter has been reset, no further action is taking during this event. This should only happen once every hour.', 'wtgportalmanager' ), __( 'hourly counter reset', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);
            self::event_return( __( 'hourly counter was reset', 'wtgportalmanager' ) );        
            return;
        }  

        // have all target counters been reset for today - if not we will reset now and end event check (in otherwords this was the event)
        $day_reset_time = $wtgportalmanager_schedule_array['history']['day_lastreset'] + 86400;
        if(time() > $day_reset_time )
        {
            $wtgportalmanager_schedule_array['history']['hour_lastreset'] = time();
            $wtgportalmanager_schedule_array['history']['day_lastreset'] = time();
            $wtgportalmanager_schedule_array['history']['hourcounter'] = 0;
            $wtgportalmanager_schedule_array['history']['daycounter'] = 0;
            $wtgportalmanager_schedule_array['history']['lastreturnreason'] = __( 'Daily and hourly events counter reset for a new 24 hours period', 'wtgportalmanager' );
            self::update_option_schedule_array( $wtgportalmanager_schedule_array ); 
            self::log_schedule( __( '24 hours had passed and the daily counter had to be reset. No further action is taking during these events and this should only happen once a day.', 'wtgportalmanager' ), __( 'daily counter reset', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);   
            self::event_return( '24 hour counter was reset' );            
            return;
        }

        // ensure event processing allowed today
        $day = strtolower(date( 'l' ) );
        if(!isset( $wtgportalmanager_schedule_array['days'][$day] ) )
        {
            self::event_return( __( 'Event processing is has not been permitted for today', 'wtgportalmanager' ) );
            self::log_schedule( __( 'Event processing is not permitted for today. Please check schedule settings to change this.', 'wtgportalmanager' ), __( 'schedule not permitted today', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);
            self::event_return( 'schedule not permitting day' );        
            return;    
        } 

        // ensure event processing allow this hour   
        $hour = strtolower( date( 'G' ) );
        if(!isset( $wtgportalmanager_schedule_array['hours'][$hour] ) )
        {
            self::event_return( __( 'Event processing is has not been permitted for the hour', 'wtgportalmanager' ) );
            self::log_schedule( __( 'Processsing is not permitted for the current hour. Please check schedule settings to change this.', 'wtgportalmanager' ), __( 'hour not permitted', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);
            self::event_return( __( 'schedule not permitting hour', 'wtgportalmanager' ) );        
            return;    
        }

        // ensure hourly limit value has been set
        if(!isset( $wtgportalmanager_schedule_array['limits']['hour'] ) )
        {  
            $wtgportalmanager_schedule_array['limits']['hour'] = 1;
            $wtgportalmanager_schedule_array['history']['lastreturnreason'] = __( 'Hourly limit was set for the first time', 'wtgportalmanager' );
            self::update_option_schedule_array( $wtgportalmanager_schedule_array );
            self::log_schedule( __( 'The hourly limit value had not been set yet. You can change the limit but the default has been set to one. No further action is taking during this event and this should only happen once.', 'wtgportalmanager' ), __( 'no hourly limit set', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);
            self::event_return( __( 'initialised hourly limit', 'wtgportalmanager' ) );        
            return;
        }     
                    
        // ensure daily limit value has been set
        if(!isset( $wtgportalmanager_schedule_array['limits']['day'] ) )
        {
            $wtgportalmanager_schedule_array['limits']['day'] = 1;
            $wtgportalmanager_schedule_array['history']['lastreturnreason'] = __( 'Daily limit was set for the first time', 'wtgportalmanager' );
            self::update_option_schedule_array( $wtgportalmanager_schedule_array );
            self::log_schedule( __( 'The daily limit value had not been set yet. It has now been set as one which allows only one post to be created or updated etc. This action should only happen once.', 'wtgportalmanager' ), __( 'no daily limit set', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__); 
            self::event_return( __( 'initialised daily limit', 'wtgportalmanager' ) );           
            return;
        }

        // if this hours target has been met return
        if( $wtgportalmanager_schedule_array['history']['hourcounter'] >= $wtgportalmanager_schedule_array['limits']['hour'] )
        {
            self::event_return( 'The hours event limit/target has been met' );
            self::log_schedule( __( 'The events target for the current hour has been met so no further processing is permitted.', 'wtgportalmanager' ), __( 'hourly target met', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);
            self::event_return( __( 'hours limit reached', 'wtgportalmanager' ) );            
            return;        
        }
         
        // if this days target has been met return
        if( $wtgportalmanager_schedule_array['history']['daycounter'] >= $wtgportalmanager_schedule_array['limits']['day'] )
        {
            self::event_return( __( 'The days event limit/target has been met', 'wtgportalmanager' ) );
            self::log_schedule( __( 'The daily events target has been met for the current 24 hour period (see daily timer counter). No events will be processed until the daily timer reaches 24 hours and is reset.', 'wtgportalmanager' ), __( 'daily target met', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);
            self::event_return( __( 'days limit reached', 'wtgportalmanager' ) );        
            return;       
        }
               
        // decide which event should be run (based on previous event, all events history and settings)
        $run_event_type = $this->event_decide();
                  
        self::log_schedule(sprintf( __( 'The schedule system decided that the next event type is %s.', 'wtgportalmanager' ), $run_event_type), __( 'next event type determined', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);
            
        // update $wtgportalmanager_schedule_array with decided event type to advance the cycle and increase hourly plus daily counter
        $wtgportalmanager_schedule_array['history']['lasteventtype'] = $run_event_type;
        $wtgportalmanager_schedule_array['history']['lasteventtime'] = time(); 
        $wtgportalmanager_schedule_array['history']['hourcounter'] = $wtgportalmanager_schedule_array['history']['hourcounter'] + 1; 
        $wtgportalmanager_schedule_array['history']['daycounter'] = $wtgportalmanager_schedule_array['history']['daycounter'] + 1;
        self::update_option_schedule_array( $wtgportalmanager_schedule_array );
        
        // run procedure for decided event
        $event_action_outcome = $this->event_action( $run_event_type); 
        
        return $event_action_outcome;   
    }

    /**
    * add_action hook init for calling using WP CRON events as a simple
    * wtgportalmanager solution. Passing value to this method, can be used to call more specific method
    * for an event.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function eventcheckwpcron() {
        // echo 'CRON EXECUTED';
        return false;
    }
    
    /**
    * add_action hook init to act as a parent function to cron jobs run
    * using the server and not WP CRON or the WebTechGlobal automation system.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function event_check_servercron() {
        return false;
    }
        
    /**
    * Establishes which event should be run then return it.
    * Must call ths function within a function that checks that uses wtgportalmanager_DOING_AJAX() first 
    * 
    * 1. If you add a new event type, you must also update wtgportalmanager_tab1_pagecreation.php (Schedule), specifically Events Status panel
    * 2. Update event_action when adding a new event type
    * 3. Update the Event Types panel and add option for new event types
    * 4. Update wtgportalmanager_form_save_eventtypes
    * 
    * @link http://www.webtechglobal.co.uk/hacking/event-types
    */
    public function event_decide() {
        global $wtgportalmanager_schedule_array, $wtgportalmanager_settings;
        
        // return focused event if active
        $override_event = $this->event_decide_focus();// returns false if no override settings in place    
        if( $override_event && is_string( $override_event) )
        {
            self::log_schedule(sprintf( __( 'The plugins ability to override the next due event type has been applied and then next event forced is %s.', 'wtgportalmanager' ), $override_event), __( 'next event type override', 'wtgportalmanager' ),1, 'scheduledeventcheck', __LINE__, __FILE__, __FUNCTION__);         
            return $override_event;
        }    

        // set default
        $run_event_type = 'createposts';
        
        // if we have no last event to establish the next event return the default
        if(!isset( $wtgportalmanager_schedule_array['history']['lasteventtype'] ) ){
            return $run_event_type;
        }
        $bypass = false;// change to true when the next event after the last is not active, then the first available in the list will be the event 
        
        // dataimport -> dataupdate  
        if( $wtgportalmanager_schedule_array['history']['lasteventtype'] == 'dataimport' ){
            if( isset( $wtgportalmanager_schedule_array['eventtypes']['dataupdate']['switch'] ) && $wtgportalmanager_schedule_array['eventtypes']['dataupdate']['switch'] == true){
                 return 'dataupdate';    
            }else{
                $bypass = true; 
            }            
        }
        
        // dataupdate -> postcreation
        if( $wtgportalmanager_schedule_array['history']['lasteventtype'] == 'dataupdate' || $bypass == true){
            if( isset( $wtgportalmanager_schedule_array['eventtypes']['postcreation']['switch'] ) && $wtgportalmanager_schedule_array['eventtypes']['postcreation']['switch'] == true){
                 return 'postcreation';    
            }else{
                $bypass = true; 
            }            
        }    
        
        // postcreation -> postupdate
        if( $wtgportalmanager_schedule_array['history']['lasteventtype'] == 'postcreation' || $bypass == true){
            if( isset( $wtgportalmanager_schedule_array['eventtypes']['postupdate']['switch'] ) && $wtgportalmanager_schedule_array['eventtypes']['postupdate']['switch'] == true){
                return 'postupdate';    
            }else{
                $bypass = true; 
            }            
        }    

        // postupdate -> dataimport
        if( $wtgportalmanager_schedule_array['history']['lasteventtype'] == 'postupdate' || $bypass == true){
            if( isset( $wtgportalmanager_schedule_array['eventtypes']['dataimport']['switch'] ) && $wtgportalmanager_schedule_array['eventtypes']['dataimport']['switch'] == true){
                 return 'dataimport';    
            }else{
                $bypass = true; 
            }            
        }      
                           
        return $run_event_type;        
    }
    
    /**
    * Determines if user wants the schedule to focus on one specific event type
    */
    public function event_decide_focus() {
        $wtgportalmanager_schedule_array = self::get_option_schedule_array();
        if( isset( $wtgportalmanager_schedule_array['focus'] ) && $wtgportalmanager_schedule_array['focus'] != false ){
            return $wtgportalmanager_schedule_array['focus'];    
        }
    }
    
    /**
    * Runs the required event
    * 1. The event type determines what function is to be called. 
    * 2. We can add arguments here to call different (custom) functions and more than one action.
    * 3. Global settings effect the event type selected, it is always cycled to ensure good admin
    * 
    * @param mixed $run_event_type, see event_decide() for list of event types 
    */
    public function event_action( $run_event_type){    
        global $wtgportalmanager_settings, $WTGPORTALMANAGER;
        $wtgportalmanager_schedule_array = WTGPORTALMANAGER::get_option_schedule_array();       
        $wtgportalmanager_schedule_array['history']['lasteventaction'] = $run_event_type . ' Requested'; 
            
        // we can override the $run_event_type                          
        // run specific script for the giving action      
        switch ( $run_event_type) {
            case "dataimport":  
            
                // find a project with data still to import and return the project id (this includes new csv files with new rows)
                
                // enter project id into log
                
                // import data
                
                // enter result into log
                
                break;  
            case "dataupdate":
                 
                // find a project with a new csv file and return the id
                
                // import and update table where previously imported rows have now changed (do not import the new rows)

                break;
            
        }// end switch
        self::update_option_schedule_array( $wtgportalmanager_schedule_array );
    } 
    
    /**
    * HTML for a media button that displays above the WYSIWYG editor
    * 
    * @param mixed $context
    */
    public function pluginmediabutton_button( $context) {
        //append the icon
        $context = "<a class='button thickbox' title='WTG Portal Manager Column Replacement Tokens (CTRL + C then CTRL + V)'
        href='#TB_inline?width=400&inlineId=wtgportalmanager_popup_container'>WTG Portal Manager</a>";
        return $context;
    }  
      
    /**
    * Used in admin page headers to constantly check the plugins status while administrator logged in 
    */
    public function diagnostics_constant() {
        if( is_admin() && current_user_can( 'manage_options' ) ){
            
            // avoid diagnostic if a $_POST, $_GET or Ajax request made (it is installation state diagnostic but active debugging)                                          
            if( self::request_made() ){
                return;
            }
                              
        }
    }
    
    /**
    * DO NOT CALL DURING FULL PLUGIN INSTALL
    * This function uses update. Do not call it during full install because user may be re-installing but
    * wishing to keep some existing option records.
    * 
    * Use this function when installing admin settings during use of the plugin. 
    */
    public function install_admin_settings() {
        require_once( WTGPORTALMANAGER_ABSPATH . 'arrays/settings_array.php' );
        return $this->option( 'wtgportalmanager_settings', 'update', $wtgportalmanager_settings );# update creates record if it does not exist   
    } 
     
    /**
    * includes a file per custom post type, we can customize this to include or exclude based on settings
    */
    public function custom_post_types() { 
        global $wtgportalmanager_settings;      
        
        // has the WebTechGlobal Flag system been activated for this package?                    
        if( isset( $wtgportalmanager_settings['posttypes']['wtgflags']['status'] ) && $wtgportalmanager_settings['posttypes']['wtgflags']['status'] === 'enabled' ) {    
            //require( WTGPORTALMANAGER_ABSPATH . 'posttypes/flags.php' );   
        }
        
        // has post "Edit Post" enhancements been setup for this package and activated in default settings?                                                       
        if( isset( $wtgportalmanager_settings['posttypes']['posts']['status'] ) && $wtgportalmanager_settings['posttypes']['posts']['status'] === 'enabled' ) {    
            //require( WTGPORTALMANAGER_ABSPATH . 'posttypes/posts.php' );   
        }
    }
 
    /**
    * Admin Triggered Automation
    */
    public function admin_triggered_automation() {
        // clear out log table (48 hour log)
        self::log_cleanup();
    }
    
    /**
    * gets one or more rows from imported data for specific post created by specific project
    * 
    * @uses get_posts_rows() which does a join query 
    * 
    * @param mixed $project_id
    * @param mixed $post_id
    * @param mixed $idcolumn
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0 
    */
    public function get_posts_record( $project_id, $post_id, $idcolumn = false ){
        return self::get_posts_rows( $project_id, $post_id, $idcolumn );
    } 
    
    /**
    * Gets the MySQL version of column
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    * 
    * @returns false if no column set
    */
    public function get_category_column( $project_id, $level ) {
        if( isset( $this->current_project_settings['categories']['data'][$level]['column'] ) ){
            return $this->current_project_settings['categories']['data'][$level]['column'];    
        }           
        
        return false;
    } 

    /**
    * Determines if process request of any sort has been requested
    * 1. used to avoid triggering automatic processing during proccess requests
    * 
    * @returns true if processing already requested else false
    */
    public function request_made() {
        // ajax
        if(defined( 'DOING_AJAX' ) && DOING_AJAX){
            return true;    
        } 
        
        // form submissions - if $_POST is set that is fine, providing it is an empty array
        if( isset( $_POST) && !empty( $_POST) ){
            return true;
        }
        
        // WTG Portal Manager own special processing triggers
        if( isset( $_GET['wtgportalmanageraction'] ) || isset( $_GET['nonceaction'] ) ){
            return true;
        }
        
        return false;
    } 
   
    /**
    * Used to build history, flag items and schedule actions to be performed.
    * 1. it all falls under log as we would probably need to log flags and scheduled actions anyway
    *
    * @global $wpdb
    * @uses extract, shortcode_atts
    * 
    * @link http://www.wtgportalmanager.com/hacking/log-table
    */
    public function newlog( $atts ){     
        global $wtgportalmanager_settings, $wpdb;

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
            'version' => WTGPORTALMANAGER_VERSION, 
            'panelid' => false,# id of submitted panel
            'panelname' => false,# name of submitted panel 
            'tabscreenid' => false,# id of the menu tab  
            'tabscreenname' => false,# name of the menu tab 
            'dump' => false,# dump anything here 
            'ipaddress' => false,# users ip 
            'userid' => false,# user id if any    
            'noticemessage' => false,# when using log to create a notice OR if logging a notice already displayed      
            'comment' => false,# dev comment to help with troubleshooting
            'type' => false,# general|error|trace 
            'category' => false,# createposts|importdata|uploadfile|deleteuser|edituser 
            'action' => false,# 3 posts created|22 posts updated (the actuall action performed)
            'priority' => false,# low|normal|high (use high for errors or things that should be investigated, use low for logs created mid procedure for tracing progress)                        
            'triga' => false# autoschedule|cronschedule|wpload|manualrequest
        ), $atts ) );
        
        // start query
        $query = "INSERT INTO $table_name";
        
        // add columns and values
        $query_columns = '(outcome';
        $query_values = '(1';
        
        if( $line){$query_columns .= ',line';$query_values .= ', "'.$line.'"';}
        if( $file){$query_columns .= ',file';$query_values .= ', "'.$file.'"';}                                                                           
        if( $function){$query_columns .= ',function';$query_values .= ', "'.$function.'"';}  
        if( $sqlresult){$query_columns .= ',sqlresult';$query_values .= ', "'.$sqlresult.'"';}     
        if( $sqlquery ){$query_columns .= ',sqlquery';$query_values .= ', "'.$sqlquery.'"';}     
        if( $sqlerror){$query_columns .= ',sqlerror';$query_values .= ', "'.$sqlerror.'"';}    
        if( $wordpresserror){$query_columns .= ',wordpresserror';$query_values .= ', "'.$wordpresserror.'"';}     
        if( $screenshoturl){$query_columns .= ',screenshoturl';$query_values .= ', "'.$screenshoturl.'"' ;}     
        if( $userscomment){$query_columns .= ',userscomment';$query_values .= ', "'.$userscomment.'"';}     
        if( $page){$query_columns .= ',page';$query_values .= ', "'.$page.'"';}     
        if( $version){$query_columns .= ',version';$query_values .= ', "'.$version.'"';}     
        if( $panelid){$query_columns .= ',panelid';$query_values .= ', "'.$panelid.'"';}     
        if( $panelname){$query_columns .= ',panelname';$query_values .= ', "'.$panelname.'"';}     
        if( $tabscreenid){$query_columns .= ',tabscreenid';$query_values .= ', "'.$tabscreenid.'"';}     
        if( $tabscreenname){$query_columns .= ',tabscreenname';$query_values .= ', "'.$tabscreenname.'"';}     
        if( $dump){$query_columns .= ',dump';$query_values .= ', "'.$dump.'"';}     
        if( $ipaddress){$query_columns .= ',ipaddress';$query_values .= ', "'.$ipaddress.'"';}     
        if( $userid){$query_columns .= ',userid';$query_values .= ', "'.$userid.'"';}     
        if( $noticemessage){$query_columns .= ',noticemessage';$query_values .= ', "'.$noticemessage.'"';}     
        if( $comment){$query_columns .= ',comment';$query_values .= ', "'.$comment.'"';}     
        if( $type){$query_columns .= ',type';$query_values .= ', "'.$type.'"';}     
        if( $category ){$query_columns .= ',category';$query_values .= ', "'.$category.'"';}     
        if( $action){$query_columns .= ',action';$query_values .= ', "'.$action.'"';}     
        if( $priority ){$query_columns .= ',priority';$query_values .= ', "'.$priority.'"';}     
        if( $triga){$query_columns .= ',triga';$query_values .= ', "'.$triga.'"';}
        
        $query_columns .= ' )';
        $query_values .= ' )';
        $query .= $query_columns .' VALUES '. $query_values;  
        $wpdb->query( $query );     
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
        $atts['logged'] = self::datewp();
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
     * Checks existing plugins and displays notices with advice or informaton
     * This is not only for code conflicts but operational conflicts also especially automated processes
     *
     * $return $critical_conflict_result true or false (true indicatesd a critical conflict found, prevents installation, this should be very rare)
     */
    function conflict_prevention( $outputnoneactive = false ){
        // track critical conflicts, return the result and use to prevent installation
        // only change $conflict_found to true if the conflict is critical, if it only effects partial use
        // then allow installation but warn user
        $conflict_found = false;
            
        // we create an array of profiles for plugins we want to check
        $plugin_profiles = array();

        // Tweet My Post (javascript conflict and a critical one that breaks entire interface)
        $plugin_profiles[0]['switch'] = 1;//used to use or not use this profile, 0 is no and 1 is use
        $plugin_profiles[0]['title'] = __( 'Tweet My Post', 'wtgportalmanager' );
        $plugin_profiles[0]['slug'] = 'tweet-my-post/tweet-my-post.php';
        $plugin_profiles[0]['author'] = 'ksg91';
        $plugin_profiles[0]['title_active'] = __( 'Tweet My Post Conflict', 'wtgportalmanager' );
        $plugin_profiles[0]['message_active'] = __( 'Please deactivate Twitter plugins before performing mass post creation. This will avoid spamming Twitter and causing more processing while creating posts.', 'wtgportalmanager' );
        $plugin_profiles[0]['message_inactive'] = __( 'If you activate this or any Twitter plugin please ensure the plugins options are not setup to perform mass tweets during post creation.', 'wtgportalmanager' );
        $plugin_profiles[0]['type'] = 'info';//passed to the message function to apply styling and set type of notice displayed
        $plugin_profiles[0]['criticalconflict'] = true;// true indicates that the conflict will happen if plugin active i.e. not specific settings only, simply being active has an effect
                             
        // loop through the profiles now
        if( isset( $plugin_profiles) && $plugin_profiles != false ){
            foreach( $plugin_profiles as $key=>$plugin){   
                if( is_plugin_active( $plugin['slug'] ) ){ 
                   
                    // recommend that the user does not use the plugin
                    $this->notice_depreciated( $plugin['message_active'], 'warning', 'Small', $plugin['title_active'], '', 'echo' );

                    // if the conflict is critical, we will prevent installation
                    if( $plugin['criticalconflict'] == true){
                        $conflict_found = true;// indicates critical conflict found
                    }
                    
                }elseif(is_plugin_inactive( $plugin['slug'] ) ){
                    
                    if( $outputnoneactive)
                    {   
                        $this->n_incontent_depreciated( $plugin['message_inactive'], 'warning', 'Small', $plugin['title'] . ' Plugin Found' );
                    }
        
                }
            }
        }

        return $conflict_found;
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
    
    public function send_email( $recipients, $subject, $content, $content_type = 'html' ){     
                           
        if( $content_type == 'html' )
        {
            add_filter( 'wp_mail_content_type', 'wtgportalmanager_set_html_content_type' );
        }
        
        $result = wp_mail( $recipients, $subject, $content );

        if( $content_type == 'html' )
        {    
            remove_filter( 'wp_mail_content_type', 'wtgportalmanager_set_html_content_type' );  
        }   
        
        return $result;
    }    
    
    /**
    * Creates url to an admin page.
    * 
    * Do not use for requests as there is no nonce setup with this method.
    *  
    * @param mixed $page, registered page slug i.e. wtgportalmanager_install which results in wp-admin/admin.php?page=wtgportalmanager_install   
    * @param mixed $values, pass a string beginning with & followed by url values
    */
    public function url_toadmin( $page, $values = '' ){                                  
        return get_admin_url() . 'admin.php?page=' . $page . $values;
    }
    
    /**
    * Adds <button> with jquerybutton class and </form>, for using after a function that outputs a form
    * Add all parameteres or add none for defaults
    * @param string $buttontitle
    * @param string $buttonid
    */
    public function formend_standard( $buttontitle = 'Submit', $buttonid = 'notrequired' ){
            if( $buttonid == 'notrequired' ){
                $buttonid = 'wtgportalmanager_notrequired'.rand(1000,1000000);# added during debug
            }else{
                $buttonid = $buttonid.'_formbutton';
            }?>

            <p class="submit">
                <input type="submit" name="wtgportalmanager_wpsubmit" id="<?php echo $buttonid;?>" class="button button-primary" value="<?php echo $buttontitle;?>">
            </p>

        </form><?php
    }
    
    /**
     * Echos the html beginning of a form and beginning of widefat post fixed table
     * 
     * @param string $name (a unique value to identify the form)
     * @param string $method (optional, default is post, post or get)
     * @param string $action (optional, default is null for self submission - can give url)
     * @param string $enctype (pass enctype="multipart/form-data" to create a file upload form)
     */
    public function formstart_standard( $name, $id = 'none', $method = 'post', $class, $action = '', $enctype = '' ){
        if( $class){
            $class = 'class="'.$class.'"';
        }else{
            $class = '';         
        }
        echo '<form '.$class.' '.$enctype.' id="'.$id.'" method="'.$method.'" name="wtgportalmanager_request_'.$name.'" action="'.$action.'">
        <input type="hidden" id="wtgportalmanager_admin_action" name="wtgportalmanager_admin_action" value="true">';
    } 
        
    /**
    * Adds Script Start and Stylesheets to the beginning of pages
    */
    public function pageheader( $pagetitle, $layout ){
        global $current_user, $wtgportalmanager_settings;

        // get admin settings again, all submissions and processing should update settings
        // if the interface does not show expected changes, it means there is a problem updating settings before this line
        $wtgportalmanager_settings = self::adminsettings(); 

        get_currentuserinfo();?>
                    
        <div id="wtgportalmanager-page" class="wrap">
            <?php self::diagnostics_constant();?>
        
            <div id="icon-options-general" class="icon32"><br /></div>
            
            <?php 
            $name = '';
            if( $pagetitle !== 'WTG Portal Manager' ) {
                $name = 'WTG Portal Manager: ';    
            }
            ?>
            
            <?php 
            // build page H2 title
            $h2_title = '';
            
            // if not "WTG Portal Manager" set this title
            if( $pagetitle !== 'WTG Portal Manager' ) {
                $h2_title = 'WTG Portal Manager: ' . $pagetitle;    
            }

            // if update screen set this title
            if( $_GET['page'] == 'wtgportalmanager_pluginupdate' ){
                $h2_title = __( 'New WTG Portal Manager Update Ready', 'wtgportalmanager' );
            }           
            ?>
            
            <h2><?php echo $h2_title;?></h2>

            <?php 
            // run specific admin triggered automation tasks, this way an output can be created for admin to see
            self::admin_triggered_automation();  

            // check existing plugins and give advice or warnings
            self::conflict_prevention();
                     
            // display form submission result notices
            $this->UI->output_depreciated();// now using display_all();
            $this->UI->display_all();              
          
            // check systems requirements, also checks installation status
            self::check_requirements( true );
    }                          
    
    /**
    * Checks if the cores minimum requirements are met and displays notices if not
    * Checks: Internet Connection (required for jQuery ), PHP version, Soap Extension
    */
    public function check_requirements( $display ){
        // variable indicates message being displayed, we will only show 1 message at a time
        $requirement_missing = false;

        // php version
        if(defined(WTGPORTALMANAGER_PHPVERSIONMINIMUM) ){
            if(WTGPORTALMANAGER_PHPVERSIONMINIMUM > phpversion() ){
                $requirement_missing = true;
                if( $display == true){
                    self::notice_depreciated(sprintf( __( 'The plugin detected an older PHP version than the minimum requirement which 
                    is %s. You can requests an upgrade for free from your hosting, use .htaccess to switch
                    between PHP versions per WP installation or sometimes hosting allows customers to switch using their control panel.', 'wtgportalmanager' ),WTGPORTALMANAGER_PHPVERSIONMINIMUM)
                    , 'warning', 'Large', __( 'WTG Portal Manager Requires PHP ', 'wtgportalmanager' ) . WTGPORTALMANAGER_PHPVERSIONMINIMUM);                
                }
            }
        }
        
        return $requirement_missing;
    }               
    
    /**       
     * Generates a username using a single value by incrementing an appended number until a none used value is found
     * @param string $username_base
     * @return string username, should only fail if the value passed to the function causes so
     * 
     * @todo log entry functions need to be added, store the string, resulting username
     */
    public function create_username( $username_base){
        $attempt = 0;
        $limit = 500;// maximum trys - would we ever get so many of the same username with appended number incremented?
        $exists = true;// we need to change this to false before we can return a value

        // clean the string
        $username_base = preg_replace( '/([^@]*).*/', '$1', $username_base );

        // ensure giving string does not already exist as a username else we can just use it
        $exists = username_exists( $username_base );
        if( $exists == false )
        {
            return $username_base;
        }
        else
        {
            // if $suitable is true then the username already exists, increment it until we find a suitable one
            while( $exists != false )
            {
                ++$attempt;
                $username = $username_base.$attempt;

                // username_exists returns id of existing user so we want a false return before continuing
                $exists = username_exists( $username );

                // break look when hit limit or found suitable username
                if( $attempt > $limit || $exists == false ){
                    break;
                }
            }

            // we should have our login/username by now
            if ( $exists == false ) 
            {
                return $username;
            }
        }
    }
    
    /**
    * Wrapper, uses wtgportalmanager_url_toadmin to create local admin url
    * 
    * @param mixed $page
    * @param mixed $values 
    */
    public function create_adminurl( $page, $values = '' ){
        return self::url_toadmin( $page, $values);    
    }
    
    /**
    * Returns the plugins standard date (MySQL Date Time Formatted) with common format used in WordPress.
    * Optional $time parameter, if false will return the current time().
    * 
    * @param integer $timeaddition, number of seconds to add to the current time to create a future date and time
    * @param integer $time optional parameter, by default causes current time() to be used
    */
    public function datewp( $timeaddition = 0, $time = false, $format = false ){
        // initialize time string
        if( $time != false && is_numeric( $time) ){$thetime = $time;}else{$thetime = time();}
        // has a format been past
        if( $format == 'gm' ){
            return gmdate( 'Y-m-d H:i:s', $thetime + $timeaddition);
        }elseif( $format == 'mysql' ){
            // return actual mysql database current time
            return current_time( 'mysql',0);// example 2005-08-05 10:41:13
        }
        
        // default to standard PHP with a common format used by WordPress and MySQL but not the actual database time
        return date( 'Y-m-d H:i:s', $thetime + $timeaddition);    
    }   
    
    public function get_installed_version() {
        return get_option( 'wtgportalmanager_installedversion' );    
    }  
    
    /**
    * Use to start a new result array which is returned at the end of a function. It gives us a common set of values to work with.

    * @uses self::arrayinfo_set()
    * @param mixed $description use to explain what array is used for
    * @param mixed $line __LINE__
    * @param mixed $function __FUNCTION__
    * @param mixed $file __FILE__
    * @param mixed $reason use to explain why the array was updated (rather than what the array is used for)
    * @return string
    */                                   
    public function result_array( $description, $line, $function, $file ){
        $array = self::arrayinfo_set(array(), $line, $function, $file );
        $array['description'] = $description;
        $array['outcome'] = true;// boolean
        $array['failreason'] = false;// string - our own typed reason for the failure
        $array['error'] = false;// string - add php mysql wordpress error 
        $array['parameters'] = array();// an array of the parameters passed to the function using result_array, really only required if there is a fault
        $array['result'] = array();// the result values, if result is too large not needed do not use
        return $array;
    }         
    
    /**
    * Get arrays next key (only works with numeric key )
    * 
    * @version 0.2 - return 0 if not array, used to return 1 but no longer a reason to do that
    * @author Ryan Bayne
    */
    public function get_array_nextkey( $array ){
        if(!is_array( $array ) || empty( $array ) ){
            return 0;   
        }
        
        ksort( $array );
        end( $array );
        return key( $array ) + 1;
    }
    
    /**
    * Gets the schedule array from wordpress option table.
    * Array [times] holds permitted days and hours.
    * Array [limits] holds the maximum post creation numbers 
    */
    public static function get_option_schedule_array() {
        $wtgportalmanager_schedule_array = get_option( 'wtgportalmanager_schedule' );
        return maybe_unserialize( $wtgportalmanager_schedule_array );    
    }
    
    /**
    * Updates the schedule array from wordpress option table.
    * Array [times] holds permitted days and hours.
    * Array [limits] holds the maximum post creation numbers 
    */
    public function update_option_schedule_array( $schedule_array ){
        $schedule_array_serialized = maybe_serialize( $schedule_array );
        return update_option( 'wtgportalmanager_schedule', $schedule_array_serialized);    
    }
    
    public function update_settings( $wtgportalmanager_settings ){
        $admin_settings_array_serialized = maybe_serialize( $wtgportalmanager_settings );
        return update_option( 'wtgportalmanager_settings', $admin_settings_array_serialized);    
    }
    
    /**
    * Returns WordPress version in short
    * 1. Default returned example by get_bloginfo( 'version' ) is 3.6-beta1-24041
    * 2. We remove everything after the first hyphen
    */
    public function get_wp_version() {
        $longversion = get_bloginfo( 'version' );
        return strstr( $longversion , '-', true );
    }
    
    /**
    * Determines if the giving value is a WTG Portal Manager page or not
    */
    public function is_plugin_page( $page){
        return strstr( $page, 'wtgportalmanager' );  
    } 
        
    /**
    * Get POST ID using post_name (slug)
    * 
    * @param string $name
    * @return string|null
    */
    public function get_post_ID_by_postname( $name){
        global $wpdb;
        // get page id using custom query
        return $wpdb->get_var( "SELECT ID 
        FROM $wpdb->posts 
        WHERE post_name = '".$name."' 
        AND post_type='page' ");
    }       
    
    /**
    * Returns all the columns in giving database table that hold data of the giving data type.
    * The type will be determined with PHP not based on MySQL column data types. 
    * 1. Table must have one or more records
    * 2. 1 record will be queried 
    * 3. Each columns values will be tested by PHP to determine data type
    * 4. Array returned with column names that match the giving type
    * 5. If $dt is false, all columns will be returned with their type however that is not the main purpose of this function
    * 6. Types can be custom, using regex etc. The idea is to establish if a value is of the pattern suitable for intended use.
    * 
    * @param string $tableName table name
    * @param string $dataType data type URL|IMG|NUMERIC|STRING|ARRAY
    * 
    * @returns false if no record could be found
    */
    public function cols_by_datatype( $tableName, $dataType = false ){
        global $wpdb;
        
        $ra = array();// returned array - our array of columns matching data type
        $matchCount = 0;// matches
        $ra['arrayinfo']['matchcount'] = $matchCount;

        $rec = $wpdb->get_results( 'SELECT * FROM '. $tableName .'  LIMIT 1',ARRAY_A);
        if(!$rec){return false;}
        
        $knownTypes = array();
        foreach( $rec as $id => $value_array ){
            foreach( $value_array as $column => $value ){     
                             
                $isURL = self::is_url( $value );
                if( $isURL){++$matchCount;$ra['matches'][] = $column;}
           
            }       
        }
        
        $ra['arrayinfo']['matchcount'] = $matchCount;
        return $ra;
    }  
    
    public function querylog_bytype( $type = 'all', $limit = 100){
        global $wpdb;

        // where
        $where = '';
        if( $type != 'all' ){
          $where = 'WHERE type = "'.$type.'"';
        }

        // limit
        $limit = 'LIMIT ' . $limit;
        
        // get_results
        $rows = $wpdb->get_results( 
        "
        SELECT * 
        FROM wtgportalmanager_log
        ".$where."
        ".$limit."

        ",ARRAY_A);

        if(!$rows){
            return false;
        }else{
            return $rows;
        }
    }  
    
    /**
    * Determines if all tables in a giving array exist or not
    * @returns boolean true if all table exist else false if even one does not
    */
    public function tables_exist( $tables_array ){
        if( $tables_array && is_array( $tables_array ) ){         
            // foreach table in array, if one does not exist return false
            foreach( $tables_array as $key => $table_name){
                $table_exists = $this->DB->does_table_exist( $table_name);  
                if(!$table_exists){          
                    return false;
                }
            }        
        }
        return true;    
    } 
    
    /**
    * Stores the last known reason why auto event was refused during checks in event_check()
    */
    public function event_return( $return_reason){
        $wtgportalmanager_schedule_array = self::get_option_schedule_array();
        $wtgportalmanager_schedule_array['history']['lastreturnreason'] = $return_reason;
        self::update_option_schedule_array( $wtgportalmanager_schedule_array );   
    }  
    
    /**
    * Uses wp-admin/includes/image.php to store an image in WordPress files and database
    * from HTTP
    * 
    * @uses wp_insert_attachment()
    * @param mixed $imageurl
    * @param mixed $postid
    * @return boolean false on fail else $thumbid which is stored in post meta _thumbnail_id
    */
    public function create_localmedia_fromhttp( $url, $postid ){ 
        $photo = new WP_Http();
        $photo = $photo->request( $url );
     
        if(is_wp_error( $photo) ){  
            return false;
        }
           
        $attachment = wp_upload_bits( basename( $url ), null, $photo['body'], date( "Y-m", strtotime( $photo['headers']['last-modified'] ) ) );
               
        $file = $attachment['file'];
                
        // get filetype
        $type = wp_check_filetype( $file, null );
                
        // build attachment object
        $att = array(
            'post_mime_type' => $type['type'],
            'post_content' => '',
            'guid' => $url,
            'post_parent' => null,
            'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $attachment['file'] ) ),
        );
       
        // action insert attachment now
        $attach_id = wp_insert_attachment( $att, $file, $postid);
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id,  $attach_data );
        
        return $attach_id;
    }
    
    public function create_localmedia_fromlocalimages( $file_url, $post_id ){           
        require_once(ABSPATH . 'wp-load.php' );
        require_once(ABSPATH . 'wp-admin/includes/image.php' );
        global $wpdb, $wtgportalmanager_settings;
               
        if(!$post_id ) {
            return false;
        }

        //directory to import to 
        if( isset( $wtgportalmanager_settings['create_localmedia_fromlocalimages']['destinationdirectory'] ) ){   
            $artDir = $wtgportalmanager_settings['create_localmedia_fromlocalimages']['destinationdirectory'];
        }else{
            $artDir = 'wp-content/uploads/importedmedia/';
        }

        //if the directory doesn't exist, create it    
        if(!file_exists(ABSPATH . $artDir) ) {
            mkdir(ABSPATH . $artDir);
        }
        
        // get extension
        $ext = pathinfo( $file_url, PATHINFO_EXTENSION);
        
        // do we need to change the new filename to avoid existing files being overwritten?
        $new_filename = basename( $file_url); 

        if (@fclose(@fopen( $file_url, "r") )) { //make sure the file actually exists
            copy( $file_url, ABSPATH . $artDir . $new_filename);

            $siteurl = get_option( 'siteurl' );
            $file_info = getimagesize(ABSPATH . $artDir . $new_filename);

            //create an array of attachment data to insert into wp_posts table
            $artdata = array(
                'post_author' => 1, 
                'post_date' => current_time( 'mysql' ),
                'post_date_gmt' => current_time( 'mysql' ),
                'post_title' => $new_filename, 
                'post_status' => 'inherit',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_name' => sanitize_title_with_dashes(str_replace( "_", "-", $new_filename) ),                                            
                'post_modified' => current_time( 'mysql' ),
                'post_modified_gmt' => current_time( 'mysql' ),
                'post_parent' => $post_id,
                'post_type' => 'attachment',
                'guid' => $siteurl.'/'.$artDir.$new_filename,
                'post_mime_type' => $file_info['mime'],
                'post_excerpt' => '',
                'post_content' => ''
            );

            $uploads = wp_upload_dir();
            $save_path = $uploads['basedir'] . '/importedmedia/' . $new_filename;

            //insert the database record
            $attach_id = wp_insert_attachment( $artdata, $save_path, $post_id );

            //generate metadata and thumbnails
            if ( $attach_data = wp_generate_attachment_metadata( $attach_id, $save_path) ) {
                wp_update_attachment_metadata( $attach_id, $attach_data);
            }

            //optional make it the featured image of the post it's attached to
            $rows_affected = $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $post_id, 'meta_key' => '_thumbnail_id', 'meta_value' => $attach_id) );
        }else {
            return false;
        }

        return true;        
    }    
    
    /**
    * First function to adding a post thumbnail
    * 
    * @todo create_localmedia_fromlocalimages() needs to be used when image is already local
    * @param mixed $overwrite_existing, if post already has a thumbnail do we want to overwrite it or leave it
    */
    public function create_post_thumbnail( $post_id, $image_url, $overwrite_existing = false ){
        global $wpdb;

        if(!file_is_valid_image( $image_url) ){  
            return false;
        }
             
        // if post has existing thumbnail
        if( $overwrite_existing == false ){
            if ( get_post_meta( $post_id, '_thumbnail_id', true) || get_post_meta( $post_id, 'skip_post_thumb', true ) ) {
                return false;
            }
        }
        
        // call action function to create the thumbnail in wordpress gallery 
        $thumbid = self::create_localmedia_fromhttp( $image_url, $post_id );
        // or from create_localmedia_fromlocalimages()  
        
        // update post meta with new thumbnail
        if ( is_numeric( $thumbid) ) {
            update_post_meta( $post_id, '_thumbnail_id', $thumbid );
        }else{
            return false;
        }
    }
    
    /**
    * builds a url for form action, allows us to force the submission to specific tabs
    */
    public function form_action( $values_array = false ){
        $get_values = '';

        // apply passed values
        if(is_array( $values_array ) ){
            foreach( $values_array as $varname => $value ){
                $get_values .= '&' . $varname . '=' . $value;
            }
        }
        
        echo self::url_toadmin( $_GET['page'], $get_values);    
    }
    
    /**
    * count the number of posts in the giving month for the giving post type
    * 
    * @param mixed $month
    * @param mixed $year
    * @param mixed $post_type
    */
    public function count_months_posts( $month, $year, $post_type){                    
        $countposts = get_posts( "year=$year&monthnum=$month&post_type=$post_type");
        return count( $countposts);    
    }     
    
    /**
    * Update one or more posts
    * 1. can pass a post ID and force update even if imported row has not changed
    * 2. Do not pass a post ID and query is done to get changed imported rows only to avoid over processing
    * 
    * CURRENTLY NOT READY FOR USE - was taking from WTG Portal Manager but not suitable to call in general use yet
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0.2
    * 
    * @param integer $project_id
    * @param integer $total
    * @param mixed $post_id boolean false or integer post ID
    * @param array $atts
    */
    public function update_posts( $project_id, $total = 1, $post_id = false, $atts = array() ){
        global $wtgportalmanager_settings;
        
        extract( shortcode_atts( array( 
            'rows' => false
        ), $atts ) );
                
        $autoblog = new WTGPORTALMANAGER_UpdatePost();
        $autoblog->settings = $wtgportalmanager_settings;
        $autoblog->maintable = $database_table;

        // we will control how and when we end the operation
        $autoblog->finished = false;// when true, output will be complete and foreach below will discontinue, this can be happen if maximum execution time is reached
        
        $idcolumn = false;
        if( isset( $autoblog->projectsettings['idcolumn'] ) ){
            $idcolumn = $autoblog->projectsettings['idcolumn'];    
        }
                               
        // get rows updated and not yet applied, this is a default query
        // pass a query result to $updated_rows to use other rows
        if( $post_id === false ){
            $updated_rows = self::get_updated_rows( $project_id, $total, $idcolumn);
        }else{
            $updated_rows = self::get_posts_record( $project_id, $post_id, $idcolumn);
        }
        
        if( !$updated_rows ){
            $this->UI->create_notice( __( 'None of your imported rows have been updated since their original import.' ), 'info', 'Small', 'No Rows Updated' );
            return;
        }
            
        $foreach_done = 0;
        foreach( $updated_rows as $key => $row){
            ++$foreach_done;
                        
            // to get the output at the end, tell the class we are on the final post, only required in "manual" requestmethod
            if( $foreach_done == $total){
                $autoblog->finished = true;
            }            
            // pass row to $autob
            $autoblog->row = $row;    
            // create a post - start method is the beginning of many nested functions
            $autoblog->start();
        }          
    }
    
    /**
    * determines if the giving term already exists within the giving level
    * 
    * this is done first by checking if the term exists in the blog anywhere at all, if not then it is an instant returned false.
    * if a match term name is found, then we investigate its use i.e. does it have a parent and does that parent have a parent. 
    * we count the number of levels and determine the existing terms level
    * 
    * if term exists in level then that terms ID is returned so that we can make use of it
    * 
    * @param mixed $term_name
    * @param mixed $level
    * 
    * @deprecated WTGPORTALMANAGER_Categories class created
    */
    public function term_exists_in_level( $term_name = 'No Term Giving', $level = 0){                 
        global $wpdb;
        $all_terms_array = $this->DB->selectwherearray( $wpdb->terms, "name = '$term_name'", 'term_id', 'term_id' );
        if(!$all_terms_array ){return false;}

        $match_found = false;
                
        foreach( $all_terms_array as $key => $term_array ){
                     
            $term = get_term( $term_array['term_id'], 'category',ARRAY_A);

            // if level giving is zero and the current term does not have a parent then it is a match
            // we return the id to indicate that the term exists in the level
            if( $level == 0 && $term['parent'] === 0){      
                return $term['term_id'];
            }
             
            // get the current terms parent and the parent of that parent
            // keep going until we reach level one
            $toplevel = false;
            $looped = 0;    
            $levels_counted = 0;
            $parent_termid = $term['parent'];
            while(!$toplevel){    
                                
                // we get the parent of the current term
                $category = get_category( $parent_termid );  

                if( is_wp_error( $category )|| !isset( $category->category_parent ) || $category->category_parent === 0){
                    
                    $toplevel = true;
                    
                }else{ 
                    
                    // term exists and must be applied as a parent for the new category
                    $parent_termid = $category->category_parent;
                    
                }
                      
                ++$looped;
                if( $looped == 20){break;}
                
                ++$levels_counted;
            }  
            
            // so after the while we have a count of the number of levels above the "current term"
            // if that count + 1 matches the level required for the giving term term then we have a match, return current term_id
            $levels_counted = $levels_counted;
            if( $levels_counted == $level){
                return $term['term_id'];
            }       
        }
                  
        // arriving here means no match found, either create the term or troubleshoot if there really is meant to be a match
        return false;
    }
    
    /**
    * call to process all methods of spinning on a string
    * 
    * @param mixed $content
    */
    public function spin( $content){
        $content = $this->spinner_brackets( $content);
        return $content;
    }
    
    public function spinner_brackets( $content){
        $mytext = $content;
        while( $this->PHP->stringinstring_using_strpos( "}", $mytext) ){
            $rbracket = strpos( $mytext, "}",0);
            $tString = substr( $mytext,0, $rbracket);
            $tStringToken = explode( "{", $tString);
            $tStringCount = count( $tStringToken) - 1;
            $tString = $tStringToken[$tStringCount];
            $tStringToken = explode( "|", $tString);
            $tStringCount = count( $tStringToken) - 1;
            $i = rand(0, $tStringCount);
            $replace = $tStringToken[$i];
            $tString = "{".$tString."}";
            $mytext = $this->PHP->str_replaceFirst( $tString, $replace, $mytext);
        }
        
        $content = $mytext;

        // set our start and stop characters
        $start_string ='{';
        $stop_string = '}';
        
        // preg match all possible spinners, putting them into $strings array
        preg_match_all( '/' . $start_string. '(.*)' . $stop_string . '/Usi' , $content, $strings);
        
        // count through loop, used to replace the entire string including brackets
        $count = 0; 
        foreach( $strings[1] as &$value ){
            $explodePhrase = explode( "|", $value );// $value is the string without brackets so we explode that
            $key = array_rand( $explodePhrase);// we get one random value
            // $strings includes both with and without brackets. We use $coutn to str_replace the brackets version in our content
            $content = str_replace( $strings[0][$count], $explodePhrase[$key], $content);                 
            ++$count;
        } 
        
        return $content;    
    }  
    
    /**
    * Activate portal for the giving user.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function activate_project( $project_id, $user_id ) {
        return update_user_meta( $user_id, 'wtgportalmanager_activeproject', $project_id );
    }

    /**
    * Makes entry to portal meta table - creating relationship with page and portal. 
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function create_page_relationship( $project_id, $page_id ) {
        return $this->DB->add_project_meta( $project_id, 'page', $page_id, false );
    }
        
    /**
    * Updates giving portals main menu
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function update_main_menu( $project_id, $menu_id ) {
        return update_metadata( 'webtechglobal_projects', $project_id, 'mainmenu', $menu_id );
    }    
    
    /**
    * Updates giving portals main blog category.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function update_main_category( $project_id, $menu_id ) {
        return update_metadata( 'webtechglobal_projects', $project_id, 'maincategory', $menu_id );
    }
    
    /**
    * Add a sub category for the purpose of extending a portals blog or
    * providing more content for other uses within the portal.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function add_portal_subcategory( $project_id, $category_id ) {
        add_metadata( 'webtechglobal_projects', $project_id, 'subcategory', $category_id, false );
    }

    /**
    * Get the giving portals main category ID.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function get_portal_maincategory_id( $project_id ) {
        return get_metadata( 'webtechglobal_projects', $project_id, 'maincategory', true );
    }
    
    /**
    * Gets the sub-categories for the giving portal, does not return the main category ID.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function get_portal_subcategories( $project_id ) {
        return get_metadata( 'webtechglobal_projects', $project_id, 'subcategory', false );    
    }
    
    /**
    * Updates entry in portal meta table.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0             
    * 
    * @param mixed $project_id
    * @param mixed $sidebar_position_id is the hardcoded ID in sidebar.php OR if using posts meta method, pass the post meta key
    * @param mixed $registered_sidebar_id is the ID of the sidebar from $wp_registered_sidebars
    */
    public function set_sidebar_relationship( $project_id, $sidebar_position_id, $registered_sidebar_id ) {
        return update_metadata( 'webtechglobal_projects', $project_id, $sidebar_position_id, $registered_sidebar_id, false );
    }
    
    /**
    * Gets the WTG Portal Manager custom sidebar ID for a giving sidebar position
    * i.e. a theme is often "sidebar-1" and our user may have assigned "mysidebar1" to over-ride
    * that sidebar when viewing the portal.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function get_dynamicsidebar_id( $project_id, $sidebarposition_id ) {
        return get_metadata( 'webtechglobal_projects', $project_id, $sidebarposition_id, true );    
    }
    
    /**
    * A basic check using a default WTG Plugin Framework approach to determine
    * if the current active theme has been integrated with WTG Portal Manager.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function is_theme_compatible() {
        require( WTGPORTALMANAGER_ABSPATH . 'arrays/themes_array.php' );
        if( in_array( get_current_theme(), $wtgportalmanager_themes['integratedthemes'] ) ) {
            return true;
        } else {
            return false;    
        }                
    }
    
    /**
    * Returns the themes integration data created by WebTechGlobal. The
    * same theme array is shared
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function get_themes_integration_info( $level = false ) {
        require( WTGPORTALMANAGER_ABSPATH . 'arrays/themes_array.php' );
        $current_theme_name = get_current_theme();   
        if( isset( $wtgportalmanager_themes[ $current_theme_name ] ) ) 
        {
            if( $level === false ) 
            {
                return $wtgportalmanager_themes[ $current_theme_name ];
            } 
            else 
            {
                if( isset( $wtgportalmanager_themes[ $current_theme_name ][ $level ] ) ) {
                    return $wtgportalmanager_themes[ $current_theme_name ][ $level ];
                }            
            }
        } 
        return false; 
    }  
    
    /**
    * Saves Twitter API app account for the giving portal.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function update_portals_twitter_api( $project_id, $consumer_key, $consumer_secret, $access_token, $token_secret, $screenname ) {
        $meta_value_array = array(
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret,
            'access_token' => $access_token,
            'token_secret' => $token_secret, 
            'screenname' => $screenname // aka users_timeline
        );
        return update_metadata( 'webtechglobal_projects', $project_id, 'twitterapi', $meta_value_array );    
    }
        
    /**
    * Saves Twitter API app account for the giving portal.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function get_portals_twitter_api( $project_id ) {
        return get_metadata( 'webtechglobal_projects', $project_id, 'twitterapi', true );   
    }
    
    /**
    * Update giving portals forum settings i.e. specific forum ID
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function update_portals_forumsettings( $project_id, $portal_switch, $main_forum_id ) {
        $meta_value_array = array(
            'portal_switch' => $portal_switch,
            'main_forum_id' => $main_forum_id
        );
        return update_metadata( 'webtechglobal_projects', $project_id, 'forumsettings', $meta_value_array );    
    }
    
    /**
    * Gets a meta value from the portal meta table.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function get_project_meta( $project_id, $meta_key, $single = true ) {
        return get_metadata( 'webtechglobal_projects', $project_id, $meta_key, $single );
    }
    
    /**
    * Gets a specific setting.
    * 
    * Pass keys for the array in an array.
    * i.e. ['apisettings']['twitter'] would require array( 'api', 'twitter') 
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function get_setting( $keys_array ) {
        global $wtgportalmanager_settings;

        if( !is_array( $wtgportalmanager_settings ) ) {
            $wtgportalmanager_settings = adminsettings();
        }
        
        // build the array access
        $array_access = '$wtgportalmanager_settings';
        foreach( $keys_array as $key ) {
            $array_access .= "['$key']";    
        }
        
        // we will return a value if exists or false
        eval( "
        if( isset( $array_access ) ) { 
            return $array_access; 
        } else { 
            return false;
        }" );
    }
     
    /**
    * Gets option value for wtgportalmanager _adminset or defaults to the file version of the array if option returns invalid.
    * 1. Called in the main wtgportalmanager.php file.
    * 2. Installs the admin settings option record if it is currently missing due to the settings being required by all screens, this is to begin applying and configuring settings straighta away for a per user experience 
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function adminsettings() {
        global $wtgportalmanager_settings;
        
        if( is_array( $wtgportalmanager_settings ) ) {
            return $wtgportalmanager_settings;    
        }
        
        // set the global
        $result = $this->option( 'wtgportalmanager_settings', 'get' );
        $result = maybe_unserialize( $result); 
        if( is_array( $result ) ){
            $wtgportalmanager_settings = $result;
            return $wtgportalmanager_settings; 
        }
        
        // install admin settings
        return $this->install_admin_settings();
    } 

    /**
    * Access all WordPress options functions.
    * 
    * @param mixed $option
    * @param mixed $action add, get, wtgget (own query function) update, delete
    * @param mixed $value
    * @param mixed $autoload used by add_option only
    */
    public function option( $option, $action, $value = 'No Value', $autoload = 'yes' ){
        if( $action == 'add' )
        {  
            return add_option( $option, $value, '', $autoload );            
        }
        elseif( $action == 'get' )
        {
            return get_option( $option);    
        }
        elseif( $action == 'update' )
        {        
            return update_option( $option, $value );
        }
        elseif( $action == 'delete' )
        {
            return delete_option( $option);        
        }
    }
    
    /**
    * removes plugins name from $_GET['page'] and returns the rest, else returns main to indicate parent
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.1
    */
    public function get_admin_page_name() {
        if( !isset( $_GET['page'] ) ){
            return 'main';
        }
        $exloded = explode( '_', $_GET['page'] );
        return end( $exloded );        
    }
    
    /**
    * Create pages for portal and add default content, which could get advanced if we
    * want to use integrated plugins or themes by default. 
    * 
    * I decided to put all pages into one method, with switch for now. When each
    * case gets longer they should be moved into their own methods.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @version 1.2
    * 
    * @param mixed $project_id
    * @param mixed $portal_name
    * @param mixed $format WP post formats AND WTG custom ones, see postformats()
    * @param mixed $parent_id
    * 
    * @todo still needs a case for each programmed page purpose, with 
    * custom default content per page.
    * 
    * @todo create option for post_status
    */
    public function create_portal_page( $project_id, $portal_name, $format, $default_content = true, $parent_id = null ) {
        $default_content = __( 'This page is still being authored, thank you for your patience.', 'wtgportalmanager' );

        if( $default_content ) {
            // build custom content per format (some WP formats, some custom by WTG)
            switch ( $format ) {
               case 'main':// home page for the entire portal
                    /* create custom content here based on the purpose of page and settings, this is where things could get advanced */
                 break;
               case 'updates':
                 $default_content = "[portalupdate usertimeline=WebTechGlobal]";
                 break;
               case 2:
            
                 break;
            }
        }
        
        ###############################################################
        #                                                             #
        #                         INSERT POST                         #
        #                                                             #
        ###############################################################
        //$pf_array = $this->CONFIG->postformats();
        $pf_array = self::postformats();
        
        $post_array = array(
          'post_content'   => $default_content,
          'post_title'     => $portal_name . ' ' . $pf_array[ $format ]['title'],  
          'post_status'    => 'publish',
          'post_type'      => 'page',
        );  
                                                           
        $new_portal_page_id = wp_insert_post( $post_array ); 
        
        // add page to portal meta table as a "page"
        $this->DB->add_project_meta( $project_id, 'page', $new_portal_page_id, false );   

        // add both portal meta and meta to the page (post meta) itself for 
        // establishing the pages purpose within the portal
        $this->DB->update_page_purpose( $project_id, $new_portal_page_id, $format );
        
        return $new_portal_page_id;
    }
    
    /**
    * Saves sidebar to WTG Portal Manager settings. They are registered on init.
    * 
    * Only add the sidebar name and get the array key for the new sidebar array.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @since 0.0.1
    * @version 1.0
    * 
    * @todo 1 This function is expected to return a sidebar ID - does it really need to? 
    */
    public function insert_sidebar( $sidebar_name ) {    
        $sidebars_array = get_option( 'wtgportalmanager_sidebars' );
        $sidebars_array['createdsidebars'][] = $sidebar_name;
        update_option( 'wtgportalmanager_sidebars', $sidebars_array, false );    
    }
    
    /**
    * Register sidebars created by WTG Plugin Manager.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.2
    */
    public function register_sidebars() {

        // sidebars that are always required - themes only
        /*
        for ($i = 1; $i <= 4; $i++)
        {
            register_sidebar(array(
                'name' => __('Footer area','mfn-opts') .' #'.$i,
                'id' => 'footer-area-'.$i,
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h4>',
                'after_title' => '</h4>',
            ));
        }
        */
        
        // Get and register user created sidebars.
        $sidebars_array = get_option( 'wtgportalmanager_sidebars' );
        
        // users custom sidebars
        if( is_array( $sidebars_array ) && isset( $sidebars_array['createdsidebars'] ) && is_array( $sidebars_array['createdsidebars'] ) )
        {
            foreach ( $sidebars_array['createdsidebars'] as $sidebar )
            {    
                register_sidebar( array (
                    'name' => $sidebar,
                    'id' => 'sidebar-'. str_replace("+", "-", urlencode(strtolower($sidebar))) ,
                    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                    'after_widget' => '</aside>',
                    'before_title' => '<h3>',
                    'after_title' => '</h3>',
                ));
            }    
        }
    }
    
    /**
    * Gets array of custom sidebars registered using this plugin with ID as key.
    * The key application makes the array easier to work with in many situations.
    * 
    * Does not include all WP registered sidebars only this plugins.
    * It may include other plugins sidebars in future if the option becomes shared.
    * You will know this by the option name.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.2
    */
    public function get_sidebars( $scope = 'all' ) {
        $array_of_sidebars = get_option( 'wtgportalmanager_sidebars' );
        
        if( is_array( $array_of_sidebars['createdsidebars'] ) ) 
        { 
            foreach( $array_of_sidebars['createdsidebars'] as $sidebar ) {
                $sidebar_id = 'sidebar-'. str_replace("+", "-", urlencode(strtolower($sidebar)));
                $array_of_sidebars[ $sidebar_id ] = $sidebar;    
            }
        }        
        
        return $array_of_sidebars;
    }
    
    /**
    * Get sidebars that have been linked with the giving portal.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function get_portal_sidebars( $project_id ) {
    
    }
                 
    /**
    * Get all capabilities and returns in array.
    * 
    * Does it by merging the capabilities from every role. This was the only
    * way I could find that did not require a database query and instead uses
    * an existing global.
    * 
    * @author Ryan R. Bayne
    * @package Multitool
    * @since 0.0.1
    * @version 1.2
    * 
    * @todo cache the capabilities array if a count on roles is high and use
    * the rolesarray to create the cache key so that if new roles are added or
    * removed the cache is updated.
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
          
}// end WTGPORTALMANAGER class 

if(!class_exists( 'WP_List_Table' ) ){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
        
/**
* Lists tickets post type using standard WordPress list table
*/
class WTGPORTALMANAGER_Log_Table extends WP_List_Table {
    
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct() {
        global $status, $page;
             
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'movie',     //singular name of the listed records
            'plural'    => 'movies',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default( $item, $column_name){
             
        $attributes = "class=\"$column_name column-$column_name\"";
                
        switch( $column_name){
            case 'row_id':
                return $item['row_id'];    
                break;
            case 'timestamp':
                return $item['timestamp'];    
                break;                
            case 'outcome':
                return $item['outcome'];
                break;
            case 'category':
                echo $item['category'];  
                break;
            case 'action':
                echo $item['action'];  
                break;  
            case 'line':
                echo $item['line'];  
                break;                 
            case 'file':
                echo $item['file'];  
                break;                  
            case 'function':
                echo $item['function'];  
                break;                  
            case 'sqlresult':
                echo $item['sqlresult'];  
                break;       
            case 'sqlquery':
                echo $item['sqlquery'];  
                break; 
            case 'sqlerror':
                echo $item['sqlerror'];  
                break;       
            case 'wordpresserror':
                echo $item['wordpresserror'];  
                break;       
            case 'screenshoturl':
                echo $item['screenshoturl'];  
                break;       
            case 'userscomment':
                echo $item['userscomment'];  
                break;  
            case 'page':
                echo $item['page'];  
                break;
            case 'version':
                echo $item['version'];  
                break;
            case 'panelname':
                echo $item['panelname'];  
                break; 
            case 'tabscreenname':
                echo $item['tabscreenname'];  
                break;
            case 'dump':
                echo $item['dump'];  
                break; 
            case 'ipaddress':
                echo $item['ipaddress'];  
                break; 
            case 'userid':
                echo $item['userid'];  
                break; 
            case 'comment':
                echo $item['comment'];  
                break;
            case 'type':
                echo $item['type'];  
                break; 
            case 'priority':
                echo $item['priority'];  
                break;  
            case 'thetrigger':
                echo $item['thetrigger'];  
                break; 
                                        
            default:
                return 'No column function or default setup in switch statement';
        }
    }
                    
    /** ************************************************************************
    * Recommended. This is a custom column method and is responsible for what
    * is rendered in any column with a name/slug of 'title'. Every time the class
    * needs to render a column, it first looks for a method named 
    * column_{$column_title} - if it exists, that method is run. If it doesn't
    * exist, column_default() is called instead.
    * 
    * This example also illustrates how to implement rollover actions. Actions
    * should be an associative array formatted as 'slug'=>'link html' - and you
    * will need to generate the URLs yourself. You could even ensure the links
    * 
    * 
    * @see WP_List_Table::::single_row_columns()
    * @param array $item A singular item (one full row's worth of data)
    * @return string Text to be placed inside the column <td> (movie title only )
    **************************************************************************/
    /*
    function column_title( $item){

    } */
    
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns() {
        $columns = array(
            'row_id' => 'Row ID',
            'timestamp' => 'Timestamp',
            'category'     => 'Category'
        );
        
        if( isset( $this->action ) ){
            $columns['action'] = 'Action';
        }                                       
           
        if( isset( $this->line ) ){
            $columns['line'] = 'Line';
        } 
                     
        if( isset( $this->file ) ){
            $columns['file'] = 'File';
        }
                
        if( isset( $this->function ) ){
            $columns['function'] = 'Function';
        }        
  
        if( isset( $this->sqlresult ) ){
            $columns['sqlresult'] = 'SQL Result';
        }

        if( isset( $this->sqlquery ) ){
            $columns['sqlquery'] = 'SQL Query';
        }
 
        if( isset( $this->sqlerror ) ){
            $columns['sqlerror'] = 'SQL Error';
        }
          
        if( isset( $this->wordpresserror ) ){
            $columns['wordpresserror'] = 'WP Error';
        }

        if( isset( $this->screenshoturl ) ){
            $columns['screenshoturl'] = 'Screenshot';
        }
        
        if( isset( $this->userscomment ) ){
            $columns['userscomment'] = 'Users Comment';
        }
 
        if( isset( $this->columns_array->page ) ){
            $columns['page'] = 'Page';
        }

        if( isset( $this->version ) ){
            $columns['version'] = 'Version';
        }
 
        if( isset( $this->panelname ) ){
            $columns['panelname'] = 'Panel Name';
        }
  
        if( isset( $this->tabscreenid ) ){
            $columns['tabscreenid'] = 'Screen ID';
        }

        if( isset( $this->tabscreenname ) ){
            $columns['tabscreenname'] = 'Screen Name';
        }

        if( isset( $this->dump ) ){
            $columns['dump'] = 'Dump';
        }

        if( isset( $this->ipaddress) ){
            $columns['ipaddress'] = 'IP Address';
        }

        if( isset( $this->userid ) ){
            $columns['userid'] = 'User ID';
        }

        if( isset( $this->comment ) ){
            $columns['comment'] = 'Comment';
        }

        if( isset( $this->type ) ){
            $columns['type'] = 'Type';
        }
                                    
        if( isset( $this->priority ) ){
            $columns['priority'] = 'Priority';
        }
       
        if( isset( $this->thetrigger ) ){
            $columns['thetrigger'] = 'Trigger';
        }

        return $columns;
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items_further() and sort
     * your data accordingly (usually by modifying your query ).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array( 'data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            //'post_title'     => array( 'post_title', false ),     //true means it's already sorted
        );
        return $sortable_columns;
    }
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(

        );
        return $actions;
    }
    
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items_further()
     **************************************************************************/
    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die( 'Items deleted (or they would be if we had items to delete)!' );
        }
        
    }
    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items_further( $data, $per_page = 5) {
        global $wpdb; //This is used only if making any database queries        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array( $columns, $hidden, $sortable);
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
      
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count( $data);

        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice( $data,(( $current_page-1)*$per_page), $per_page);
 
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
  
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil( $total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}
?>