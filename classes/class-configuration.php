<?php  
/** 
 * Configuration for WTG Portal Manager. Created September 2015 after
 * scheduling and automation system improved.
 * 
 * @package WTG Portal Manager
 * @author Ryan Bayne   
 * @version 1.0.
 */

// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
                                               
class WTGPORTALMANAGER_Configuration extends WTGPORTALMANAGER_Log {
    
    // WebTechGlobal project system constants
    const PROJECT_TYPE = 'Portals';
    const PROJECTSYSTEM_SINGULAR = 'Portal';   
    public $wtgportalmanager_displaytrace = array();
    
    public function __construct() {
        global $wtgportalmanager_displaytrace;
        
        // load class used at all times
        $this->DB = self::load_class( 'WTGPORTALMANAGER_DB', 'class-wpdb.php', 'classes' );
        $this->PHP = self::load_class( 'WTGPORTALMANAGER_PHP', 'class-phplibrary.php', 'classes' );
        $this->Install = self::load_class( 'WTGPORTALMANAGER_Install', 'class-install.php', 'classes' );
        $this->Files = self::load_class( 'WTGPORTALMANAGER_Files', 'class-files.php', 'classes' );
        $this->GLOBALUI = self::load_class( 'WTGPORTALMANAGER_GLOBALUI', 'class-globalui.php', 'classes' );
        
	}
	    
    /**
    * Plugins main actions.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.0
    */
    public function actions() {
        // add_action() controller
        // Format: array( event | function in this class(in an array if optional arguments are needed) | loading circumstances)
        // Other class requiring WordPress hooks start here also, with a method in this main class that calls one or more methods in one or many classes
        // create a method in this class for each hook required plugin wide
        return array( 
            array( 'init',                           array('init_project',1),                                  'all' ),                    
            array( 'admin_menu',                     'set_admin_globals',                                      'all' ),        
            array( 'admin_menu',                     'admin_menu',                                             'all' ),
            array( 'admin_init',                     'process_admin_POST_GET',                                 'admin' ),
            array( 'admin_init',                     'add_adminpage_actions',                                  'all' ), 
            array( 'wp_dashboard_setup',             'add_dashboard_widgets',                                  'all' ),
            array( 'wp_insert_post',                 'hook_insert_post',                                       'all' ),
            array( 'admin_footer',                   'pluginmediabutton_popup',                                'pluginscreens' ),
            array( 'media_buttons_context',          'pluginmediabutton_button',                               'pluginscreens' ),
            array( 'admin_enqueue_scripts',          'plugin_admin_enqueue_scripts',                           'pluginscreens' ),
            array( 'init',                           'plugin_admin_register_styles',                           'pluginscreens' ),
            array( 'admin_print_styles',             'plugin_admin_print_styles',                              'pluginscreens' ),
            array( 'wp_enqueue_scripts',             'plugin_enqueue_public_styles',                           'publicpages' ),            
            array( 'admin_notices',                  'admin_notices',                                          'admin_notices' ),
            array( 'init',                           'plugin_shortcodes',                                      'publicpages' ),            
            array( 'widgets_init',                   'register_sidebars',                                      'all' ),
            array( 'wp_before_admin_bar_render',     array('admin_toolbars',999),                              'pluginscreensorfront' ),
            array( 'init',                           'debugmode',                                              'administrator' ),                        
        );    
    }
               
    /**
    * Array of filters to be used during __construct of main class.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.0
    * 
    * @todo requires the ability to pass a class name then use the class object during. 
    */
    public function filters() {
        return array(
        	//array( 'wp_footer',                     array( 'examplefunction1', 10, 2),         'all' ),
        );    
    }  

    /**
    * Error display and debugging 
    * 
    * When request will display maximum php errors including WordPress errors 
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 0.1
    * 
    * @todo is this function in use? It may be in another file.
    */
    public function debugmode() {

        $debug_status = get_option( 'webtechglobal_displayerrors' );
        if( !$debug_status ){ return false; }
        
        // times when this error display is normally not  required
        if ( ( 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) )
                || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
                || ( defined( 'DOING_CRON' ) && DOING_CRON )
                || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                    return false;
        }    
            
        global $wpdb;
        ini_set( 'display_errors',1);
        error_reporting(E_ALL);      
        if(!defined( "WP_DEBUG_DISPLAY") ){define( "WP_DEBUG_DISPLAY", true);}
        if(!defined( "WP_DEBUG_LOG") ){define( "WP_DEBUG_LOG", true);}
        //add_action( 'all', create_function( '', 'var_dump( current_filter() );' ) );
        //define( 'SAVEQUERIES', true );
        //define( 'SCRIPT_DEBUG', true );
        $wpdb->show_errors();
        $wpdb->print_error();
        
        // constant required for package - everything before now is global to all
        // of WordPress and the error display switch is global to all WTG plugins
        if(!defined( "WEBTECHGLOBAL_ERRORDISPLAY") ){define( "WEBTECHGLOBAL_ERRORDISPLAY", true );}
    }  
    
    /**
    * Create a new instance of the $class, which is stored in $file in the $folder subfolder
    * of the plugin's directory.
    * 
    * One bad thing about using this is suggestive code does not work on the object that is returned
    * making development a little more difficult. This behaviour is experienced in phpEd 
    *
    * @author Ryan R. Bayne
    * @version 1.2
    *
    * @param string $class Name of the class
    * @param string $file Name of the PHP file with the class
    * @param string $folder Name of the folder with $class's $file
    * @param mixed $params (optional) Parameters that are passed to the constructor of $class
    * @return object Initialized instance of the class
    */
    public static function load_class( $class, $file, $folder, $params = null ) {
        $class = apply_filters( 'wtgportalmanager_load_class_name', $class );
        if ( ! class_exists( $class ) ) {   
            self::load_file( $file, $folder );
        }
        
        // we can avoid creating a new object, we can use "new" after the load_class() line
        // that way functions in the lass are available in code suggestion
        if( is_array( $params ) && in_array( 'noreturn', $params ) ){
            return true;   
        }
        
        $the_class = new $class( $params );
        return $the_class;
    }   

    /**
     * Load a file with require_once(), after running it through a filter
     * 
     * @param string $file Name of the PHP file with the class
     * @param string $folder Name of the folder with $class's $file
     */
    public static function load_file( $file, $folder ) {   
        $full_path = WTGPORTALMANAGER_ABSPATH . $folder . '/' . $file;
        $full_path = apply_filters( 'wtgportalmanager_load_file_full_path', $full_path, $file, $folder );
        if ( $full_path ) {   
            require_once $full_path;
        }
    }  
 
    /**
    * This function returns official post formats found
    * in the WordPress core and custom ones.
    * 
    * The things is WP does not currently allow custom ones
    * properly. 
    * 
    * The data is used as page purposes in plugins like
    * WTG Portal Manager. The purpose of a page allows us
    * to apply functionality and not just layout. Default
    * content is one use.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.1
    */
    public function postformats() {
        $pf = array();
        
        ######################################
        #                                    #
        #     WordPress Supported Formats    #
        #                                    #
        ######################################
        
        
        $pf['gallery'] = array(
            'title' => __( 'Gallery', 'wtgportalmanager' ),
            'wpsupported' => true,
            'description' => __( 'A gallery of images. Post will likely contain a gallery shortcode and will have image attachments.', 'wtgportalmanager' )    
        );
        
        $pf['aside'] = array(
            'title' => __( 'Aside', 'wtgportalmanager' ),
            'wpsupported' => true,
            'description' => __( 'Typically styled without a title. Similar to a Facebook note update..', 'wtgportalmanager' )    
        );
        
        $pf['link'] = array(
            'title' => __( 'Link', 'wtgportalmanager' ),
            'wpsupported' => true,
            'description' => __( 'A link to another site. Themes may wish to use the first <a href=””> tag in the post content as the external link for that post. An alternative approach could be if the post consists only of a URL, then that will be the URL and the title (post_title) will be the name.', 'wtgportalmanager' )    
        );
        

        $pf['image'] = array(
            'title' => __( 'Image', 'wtgportalmanager' ),
            'wpsupported' => true,
            'description' => __( 'A single image. The first <img /> tag in the post could be considered the image. Alternatively, if the post consists only of a URL, that will be the image URL and the title of the post (post_title) will be the title attribute for the image.', 'wtgportalmanager' )    
        );
           
        $pf['quote'] = array(
            'title' => __( 'Quote', 'wtgportalmanager' ),
            'wpsupported' => true,
            'description' => __( 'A quotation. Probably will contain a blockquote holding the quote content. Alternatively, the quote may be just the content, with the source/author being the title.', 'wtgportalmanager' )   
        );

        $pf['status'] = array(
            'title' => __( 'Status', 'wtgportalmanager' ),
            'wpsupported' => true,
            'description' => __( 'A short status update, similar to a Twitter status update.', 'wtgportalmanager' )    
        );

        $pf['video'] = array(
            'title' => __( 'Video', 'wtgportalmanager' ),
            'wpsupported' => true,
            'description' => __( 'A single video or video playlist. The first <video /> tag or object/embed in the post content could be considered the video. Alternatively, if the post consists only of a URL, that will be the video URL. May also contain the video as an attachment to the post, if video support is enabled on the blog (like via a plugin).', 'wtgportalmanager' )    
        );
        
        $pf['audio'] = array(
            'title' => __( 'Audio', 'wtgportalmanager' ),
            'wpsupported' => true,
            'description' => __( 'An audio file or playlist. Could be used for Podcasting.', 'wtgportalmanager' )    
        );
        
        $pf['chat'] = array(
            'title' => __( 'Chat', 'wtgportalmanager' ),
            'wpsupported' => true,
            'description' => __( 'A chat transcript.', 'wtgportalmanager' )    
        );

        ######################################
        #                                    #
        #        WebTechGlobal Formats       #
        #                                    #
        #######################################
        
        $pf['frontpage'] = array(
            'title' => __( 'Front Page', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'Front page, main page, home or portal index. This format is about offering a starting point to a defined area within a website and the template may pull elements of WP together to make it advanced.', 'wtgportalmanager' )    
        );
                
        $pf['support'] = array(
            'title' => __( 'Support Info', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'In a project/product setting this format can be used to display support tools and information specific to the product i.e. phone numbers.', 'wtgportalmanager' )    
        );                
                 
        $pf['faq'] = array(
            'title' => __( 'FAQ', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'With the right data in meta this format can be used to generate an FAQ.', 'wtgportalmanager' )    
        );
        
        $pf['features'] = array(
            'title' => __( 'Features List', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'This format will turn an array of product features into a list. Different levels of detail available so create a good array.', 'wtgportalmanager' )    
        );
    
        $pf['updates'] = array(
            'title' => __( 'Updates', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'A mix of optional official sources brought together to show a regular stream of relaxed updates on a project/service or even an individuals work. See the WTG Portal Manager portal which mixes Twitter, phpBB forum and WordPress blog posts to give visitors a good impression on recent activity.', 'wtgportalmanager' )    
        );
    
        $pf['activity'] = array(
            'title' => __( 'Activity', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'Similar to Updates but includes community activity i.e. blog post comments, forum replies, re-tweets etc. Themes offer API authorization for required services.', 'wtgportalmanager' )    
        );
        
        $pf['tasks'] = array(
            'title' => __( 'Tasks', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'This is a record list format with interactive features spefically related to task management i.e. taking ownership, requesting attention, assigning stakeholders.', 'wtgportalmanager' )    
        );
                                
        $pf['demo'] = array(
            'title' => __( 'Demo', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'This view would offer temporary one-time login to a demo, generated on load.', 'wtgportalmanager' )    
        );
               
        $pf['testimonials'] = array(
            'title' => __( 'Testimonials', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'An array of testimonials turned into a typical list of customers, possibly with photo and a style much like free-hand writing or journal.', 'wtgportalmanager' )    
        );
            
        $pf['changes'] = array(
            'title' => __( 'Changes', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'List of official changes within a project or even to the terms & conditions of a service.', 'wtgportalmanager' )    
        );
        
        // Added March 2016
        $pf['information'] = array(
            'title' => __( 'Information', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'General information, other, misc or holding page.', 'wtgportalmanager' )    
        );
        
        // Added March 2016        
        $pf['steps'] = array(
            'title' => __( 'Steps', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'Meant for a list of step by step instructions with the intention of offering some sort of functionality per step and tracking the users progress.', 'wtgportalmanager' )    
        );
                
        // Added March 2016        
        $pf['datatable'] = array(
            'title' => __( 'Data Table', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'Use for a view that focuses on offering a table of data. The intention is to offer highly interactive tables supported with Ajax.', 'wtgportalmanager' )    
        );
                
        // Added March 2016        
        $pf['index'] = array(
            'title' => __( 'Index', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'Use for organized lists of related links with format applicable to the main subject if there is one. The intential of this format is to automatically administrate the index when pages are deleted or created with certain parents or even keywords.', 'wtgportalmanager' )    
        );
                
        // Added March 2016        
        $pf['people'] = array(
            'title' => __( 'People', 'wtgportalmanager' ),
            'wpsupported' => false,
            'description' => __( 'Use for to maintain a list of users related to something. It could be a list of donators, contributors or reference to developers who took part in a project. The key functionality is that WordPress user data is used to fill the
            content.', 'wtgportalmanager' )    
        );
        
        return $pf;    
    }   

    /**
    * Add values to global tracking array which is used to output a list of
    * values and argument results. The output can be requested through the 
    * Developer menu in the Admin Bar.
    * 
    * @author Ryan R. Bayne
    * @package Multitool
    * @version 1.0
    */
    public function addtrace( $function, $line, $variable = null, $comment = null ) {
    	global $wtgportalmanager_displaytrace;
    	if( !is_array( $wtgportalmanager_displaytrace ) ) {
			$wtgportalmanager_displaytrace = array();
    	}	
    	
    	$wtgportalmanager_displaytrace[] = array(
    		'function' => $function,
    		'line' => $line,
    		'variable' => $variable,
    		'comment' => $comment
    	);
    	
    	return $wtgportalmanager_displaytrace;
	}    
}
?>
