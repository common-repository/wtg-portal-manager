<?php
/**
 * Configure sources of updates and how they are presented to subscribers.
 * 
 * I want to bring excerpts from various sources together on one page.
 * 
 * Twitter
 * Facebook
 * LinkedIn
 * Blog Categories
 * Hot Forum Topics   
 *
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 0.0.1
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class WTGPORTALMANAGER_Contentsources_View extends WTGPORTALMANAGER_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 0.0.1
     *
     * @var int
     */
    protected $screen_columns = 2;
    
    protected $view_name = 'contentsources';
    
    public $purpose = 'normal';// normal, dashboard, metaarray (return the meta array only)
    
    /**
    * Array of meta boxes, looped through to register them on views and as dashboard widgets
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function meta_box_array() {  
        // array of meta boxes + used to register dashboard widgets (id, title, callback, context, priority, callback arguments (array), dashboard widget (boolean) )   
        return $this->meta_boxes_array = array(
            // array( id, title, callback (usually parent, approach created by Ryan Bayne), context (position), priority, call back arguments array, add to dashboard (boolean), required capability
            array( $this->view_name . '-setupportaltwitter', __( 'Twitter API', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal', 'default', array( 'formid' => 'setupportaltwitter' ), true, 'activate_plugins' ),
            array( $this->view_name . '-setupportalforum', __( 'Portals Related Forum', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal', 'default', array( 'formid' => 'setupportalforum' ), true, 'activate_plugins' ),
       );    
    }
    
    /**
     * Set up the view with data and do things that are specific for this view
     *
     * @since 0.0.1
     *
     * @param string $action Action for this view
     * @param array $data Data for this view
     */
    public function setup( $action, array $data ) {
        global $wtgportalmanager_settings;
        
        // create constant for view name
        if(!defined( "WTGPORTALMANAGER_VIEWNAME") ){define( "WTGPORTALMANAGER_VIEWNAME", $this->view_name );}
        
        // create class objects
        $this->WTGPORTALMANAGER = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER', 'class-wtgportalmanager.php', 'classes' );
        $this->UI = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_ADMINUI', 'class-adminui.php', 'classes' );  
        $this->DB = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_DB', 'class-wpdb.php', 'classes' );
        $this->PHP = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_PHP', 'class-phplibrary.php', 'classes' );
        $this->FORMS = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_Formbuilder', 'class-forms.php', 'classes' );
        
        // we have the ability to pass arguments to this, it is optional
        $this->TWITTER = $this->WTGPORTALMANAGER->load_class( "WTGPORTALMANAGER_Twitter", "class-twitter.php", 'classes' );
        
        parent::setup( $action, $data );
        
        // using array register many meta boxes
        foreach( self::meta_box_array() as $key => $metabox ) {
            // the $metabox array includes required capability to view the meta box
            if( isset( $metabox[7] ) && current_user_can( $metabox[7] ) ) {
                $this->add_meta_box( $metabox[0], $metabox[1], $metabox[2], $metabox[3], $metabox[4], $metabox[5] );   
            }               
        }             
    }
    
    /**
    * Outputs the meta boxes
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function metaboxes() {
        parent::register_metaboxes( self::meta_box_array() );     
    }

    /**
    * This function is called when on WP core dashboard and it adds widgets to the dashboard using
    * the meta box functions in this class. 
    * 
    * @uses dashboard_widgets() in parent class WTGPORTALMANAGER_View which loops through meta boxes and registeres widgets
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function dashboard() { 
        parent::dashboard_widgets( self::meta_box_array() );  
    }
                                                                                                                             
    /**
    * All add_meta_box() callback to this function to keep the add_meta_box() call simple.
    * 
    * This function also offers a place to apply more security or arguments.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    function parent( $data, $box ) {
        eval( 'self::postbox_' . $this->view_name . '_' . $box['args']['formid'] . '( $data, $box );' );
    }    
    
    /**
    * Enter Twitter application keys for the current portal.
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    * 
    * @todo 1 Hide all form field details, apart from some characters at beginning
    */
    public function postbox_contentsources_setupportaltwitter( $data, $box ) {                                
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Configure a new Twitter app so that you can include tweets on the Updates and Activity pages.', 'wtgportalmanager' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );

        global $wtgportalmanager_settings;
                                                                                                       
        if (defined('WTG_USING_EXISTING_LIBRARY_TWITTEROAUTH') && WTG_USING_EXISTING_LIBRARY_TWITTEROAUTH) {
            $reflector = new ReflectionClass('TwitterOAuth');
            $file = $reflector->getFileName();
      
            echo '<div id="message" class="error"><p><strong>oAuth Twitter Feed for Developers</strong> is using an existing version of the TwitterOAuth class library to provide compatibility with existing plugins.<br />This could lead to conflicts if the plugin is using an different version of the class.</p><p>The class is being loaded at <strong>'.$file.'</strong></p></div>';
        }
      
        if (defined('WTG_USING_EXISTING_LIBRARY_OAUTH') && WTG_USING_EXISTING_LIBRARY_OAUTH) {
            $reflector = new ReflectionClass('OAuthConsumer');
            $file = $reflector->getFileName();
            
            echo '<div id="message" class="error"><p><strong>oAuth Twitter Feed for Developers</strong> is using an existing version of the PHP OAuth library to provide compatibility with existing plugins or your PHP installation.<br />This could lead to conflicts if the plugin, or your PHP installed class is using an different version of the class.</p><p>The class is being loaded at <strong>'.$file.'</strong></p></div>';
        }
      
        echo '<p>Configure an app here <a href="https://apps.twitter.com/">http://apps.twitter.com</a>. You don\'t need to set a callback location, you only need read access and you will need to generate an oAuth token once you\'ve created the application.</p>';

        echo '<hr />';
        
        $portals_twitter_array = $this->WTGPORTALMANAGER->get_project_meta( WTGPORTALMANAGER_PROJECT_ID, 'twitterapi', true );
        ?>  

            <table class="form-table">                  
            <?php                 
            $twitter_consumer_key = '';       
            if( isset( $portals_twitter_array['consumer_key'] ) ) { $twitter_consumer_key = $portals_twitter_array['consumer_key']; }
            $this->FORMS->text_basic( $box['args']['formid'], 'consumer_key', 'consumer_key', 'Consumer Key (API Key)', $twitter_consumer_key, true, array( 'alphanumeric' ) );
            
            $twitter_consumer_secret = '';
            if( isset( $portals_twitter_array['consumer_secret'] ) ) { $twitter_consumer_secret = $portals_twitter_array['consumer_secret']; }            
            $this->FORMS->text_basic( $box['args']['formid'], 'consumer_secret', 'consumer_secret', 'Consumer Secret (API Secret)', $twitter_consumer_secret, true, array( 'alphanumeric' ) );
            
            $twitter_access_token = '';
            if( isset( $portals_twitter_array['access_token'] ) ) { $twitter_access_token = $portals_twitter_array['access_token']; }            
            $this->FORMS->text_basic( $box['args']['formid'], 'access_token', 'access_token', 'Your Access Token', $twitter_access_token, true, array( 'alphanumeric' ) );
            
            $twitter_token_secret = '';
            if( isset( $portals_twitter_array['token_secret'] ) ) { $twitter_token_secret = $portals_twitter_array['token_secret']; }            
            $this->FORMS->text_basic( $box['args']['formid'], 'access_token_secret', 'access_token_secret', 'Access Token Secret', $twitter_token_secret, true, array( 'alphanumeric' ) );
            
            $twitter_screenname = '';
            if( isset( $portals_twitter_array['screenname'] ) ) { $twitter_screenname = $portals_twitter_array['screenname']; }            
            $this->FORMS->text_basic( $box['args']['formid'], 'screenname', 'screenname', 'Twitter Feed Screen Name', $twitter_screenname, false, array( 'alphanumeric' ) );
            ?>
            </table>

        <?php 
        $this->UI->postbox_content_footer();
                    
        echo '<hr />';
        
        echo '<h3>Debug Information</h3>';
        $last_error = __( 'no error has been stored.', 'wtgportalmanager' );
        if( isset( $wtgportalmanager_settings['api']['twitter']['apps'][WTGPORTALMANAGER_PROJECT_ID]['error'] ) ) 
        {
            $last_error = $wtgportalmanager_settings['api']['twitter']['apps'][WTGPORTALMANAGER_PROJECT_ID]['error'];   
        }
        else
        {
            $last_error = __( 'The current portals API credentials have never led to an error.', 'wtgportalmanager' );
        } 
        
        echo '<p>Last Twitter API Error: ' . $last_error . '</p>';                         
    }   
    
    /**
    * Current portal forum options. 
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_contentsources_setupportalforum( $data, $box ) {                                
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Encourage traffic between your portal and forum by displaying data in your forum database on the portal. This form is to configure the bridge between WP and your forum. To apply the changes to your current portal go to the Updates Page and Recent Activty Page tabs.', 'wtgportalmanager' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $wtgportalmanager_settings;
        
        if( !isset( $wtgportalmanager_settings['forumconfig']['status'] ) || $wtgportalmanager_settings['forumconfig']['status'] !== 'enabled' ) 
        {
            echo $this->UI->notice_return( 'info', 'Tiny', __( 'Forum Not Ready', 'wtgportalmanager' ), __( 'Global forum settings have not been configured on the plugins dashboard OR the settings are set to Disabled. Go to the plugins dashboard an enable forum activity. Then continue configuring your portals individually.', 'wtgportalmanager' ), false, true );         
        }
        else
        {
            $portal_meta_array = $this->WTGPORTALMANAGER->get_project_meta( WTGPORTALMANAGER_PROJECT_ID, 'forumsettings' );
            ?>  
                
            <table class="form-table">                  
            <?php 
            $current_forum_switch = null;
            if( isset( $portal_meta_array['portal_switch'] ) ) { $current_forum_switch = $portal_meta_array['portal_switch']; }
            $this->FORMS->switch_basic( $box['args']['formid'], 'portalforumswitch', 'portalforumswitch', __( 'Portal Forum Switch', 'wtgportalmanager' ), 'disabled', $current_forum_switch, false ); 
            
            $current_mainforumid = '';
            if( isset( $portal_meta_array['main_forum_id'] ) ) { $current_mainforumid = $portal_meta_array['main_forum_id']; }
            $this->FORMS->text_basic( $box['args']['formid'], 'mainforumid', 'mainforumid', __( 'Main Forum ID', 'wtgportalmanager' ), $current_mainforumid, true, array( 'numeric' ) );
            ?>
            </table>

        <?php
        }
     
        $this->UI->postbox_content_footer();                       
    }   
 
}?>