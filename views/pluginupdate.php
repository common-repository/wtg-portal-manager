<?php
/**
 * Plugin Update [page] 
 * 
 * This page displays when the plugin requires updating. The idea is to layout all changes
 * and warn the user importing changes that might need to be tested.  
 *
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 8.1.3
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Update the plugin [page] 
 * 
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne
 * @since 8.1.3
 */
class WTGPORTALMANAGER_Pluginupdate_View extends WTGPORTALMANAGER_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 8.1.3
     *
     * @var int
     */
    protected $screen_columns = 1;
    
    protected $view_name = 'pluginupdate';
    
    public $purpose = 'normal';// normal, dashboard

    /**
    * Array of meta boxes, looped through to register them on views and as dashboard widgets
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 8.1.33
    * @version 1.0.0
    */
    public function meta_box_array() {
        // array of meta boxes + used to register dashboard widgets (id, title, callback, context, priority, callback arguments (array), dashboard widget (boolean) )   
        return $this->meta_boxes_array = array(
            // array( id, title, callback (usually parent, approach created by Ryan Bayne), context (position), priority, call back arguments array, add to dashboard (boolean), required capability
            array( 'pluginupdate-changes', __( 'Changes', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'changes' ), true, 'activate_plugins' ),
            array( 'pluginupdate-instructions', __( 'Update Instructions', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'instructions' ), true, 'activate_plugins' ),
            array( 'pluginupdate-beginpluginupdate', __( 'Begin Plugin Update', 'wtgportalmanager' ), array( $this, 'parent' ), 'side','default',array( 'formid' => 'beginpluginupdate' ), true, 'activate_plugins' ),
        );    
    }
            
    /**
     * Set up the view with data and do things that are specific for this view
     *
     * @since 8.1.3
     *
     * @param string $action Action for this view
     * @param array $data Data for this view
     */
    public function setup( $action, array $data ) {
        global $wtgportalmanager_settings;
        
        // create constant for view name
        if(!defined( "WTG_WTGPORTALMANAGER_VIEWNAME") ){define( "WTG_WTGPORTALMANAGER_VIEWNAME", $this->view_name );}
        
        // create class objects
        $this->WTGPORTALMANAGER = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER', 'class-wtgportalmanager.php', 'classes' );
        $this->UI = WTGPORTALMANAGER::load_class( 'C2P_UI', 'class-adminui.php', 'classes' ); 
        $this->DB = WTGPORTALMANAGER::load_class( 'C2P_DB', 'class-wpdb.php', 'classes' );
        $this->PHP = WTGPORTALMANAGER::load_class( 'C2P_PHP', 'class-phplibrary.php', 'classes' );
        $this->Forms = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_Formbuilder', 'class-forms.php', 'classes' );
                        
        // load the current project row and settings from that row
        if( isset( $wtgportalmanager_settings['currentproject'] ) && $wtgportalmanager_settings['currentproject'] !== false ) {
            $this->project_object = $this->DB->get_project( $wtgportalmanager_settings['currentproject'] ); 
            if( !$this->project_object ) {
                $this->current_project_settings = false;
            } else {
                $this->current_project_settings = maybe_unserialize( $this->project_object->projectsettings ); 
            }
        }
        
        // get schedule array 
        $this->schedule = WTGPORTALMANAGER::get_option_schedule_array();
                
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
    * @since 0.0.3
    * @version 1.0.0
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
    * @since 0.0.2
    * @version 1.0.0
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
    * @since 8.1.32
    * @version 1.0.1
    */
    function parent( $data, $box ) {
        eval( 'self::postbox_' . $this->view_name . '_' . $box['args']['formid'] . '( $data, $box );' );
    }
     
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 8.1.3
    * @version 1.0.0
    */
    public function postbox_pluginupdate_changes( $data, $box ) {    
        echo '<p>' . __( 'Sorry there is no information at this time. This update page is new and awaiting completion of
        a system designed to manage all documentation. Just remember to backup your files and data.', 'wtgportalmanager' ) . '</p>';
    }
    
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 8.1.3
    * @version 1.0.0
    */
    public function postbox_pluginupdate_instructions( $data, $box ) {    
        echo '<p>' . __( 'There are no special update instructions for this update','wtgportalmanager' ) . '</p>';    
    } 
    
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 8.1.3
    * @version 1.0.0
    */
    public function postbox_pluginupdate_beginpluginupdate( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'The information on this screen will help you to decide how sensitive this update is. Sometimes care is not needed but if you are ever unsure WebTechGlobal recommends testing the plugin on a temporary WordPress installation. Then make a backup of your live site files and database.', 'wtgportalmanager' ), false );        
        $this->UI->hidden_form_values( $box['args']['formid'], $box['title']);
        $this->UI->postbox_content_footer();
    }       
}
?>