<?php
/**
 * Very similar to the Updates page, can even include the same updates. However
 * this page is intended to include the communities activity.
 * 
 * Twitter follow/retweet
 * Facebook likes   
 * Blog post comments
 * New forum topics and replies                                                                       
 *
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 0.0.1
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class WTGPORTALMANAGER_Contentactivity_View extends WTGPORTALMANAGER_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 0.0.1
     *
     * @var int
     */
    protected $screen_columns = 2;
    
    protected $view_name = 'contentactivity';
    
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
            array( $this->view_name . '-selectactivitysources', __( 'Select Sources', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal', 'default', array( 'formid' => 'selectactivitysources' ), true, 'activate_plugins' ),
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
    */
    public function postbox_contentactivity_selectactivitysources( $data, $box ) {                                
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Select information sources for your current portals Activity page. You may not have configured each yet so remember to test them. Settings for some may not exist until you have other integrated plugins installed.', 'wtgportalmanager' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        ?>  

            <table class="form-table">                  
            <?php
            $items_array = array( 'twitter' => 'Twitter', 'facebook' => 'Facebook', 'forum' => 'Forum', 'blogposts' => __( 'Blog Posts', 'wtgportalmanager' ), 'postcomments' => __( 'Post Comments', 'wtgportalmanager' ), 'questionsandanswers' => __( 'Questions and Answers', 'wtgportalmanager' ), 'testimonials' => __( 'Testimonials', 'wtgportalmanager' ), 'downloads' => __( 'Downloads', 'wtgportalmanager' ), 'pricedrop' => __( 'Price Drop', 'wtgportalmanager' ), 'sales' => __( 'Sales', 'wtgportalmanager' ), 'newtasks' => __( 'New Tasks', 'wtgportalmanager' ) );
            $this->FORMS->checkboxes_basic( $box['args']['formid'], 'informationsources', 'informationsources', __( 'Available Sources', 'wtgportalmanager' ), $items_array, array(/* current */), true, array(/* validation */), true );        
            ?>
            </table>

        <?php 
        $this->UI->postbox_content_footer();                  
    }   
 
}?>