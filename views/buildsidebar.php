<?php
/**
 * Select and build the portals sidebar.  
 *
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 0.0.1
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Select and build the portals sidebar.  
 *
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne
 * @since 0.0.1
 */
class WTGPORTALMANAGER_Buildsidebar_View extends WTGPORTALMANAGER_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 0.0.1
     *
     * @var int
     */
    protected $screen_columns = 2;
    
    protected $view_name = 'buildsidebar';
    
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
            array( $this->view_name . '-setsidebars', __( 'Set Sidebars', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal', 'default', array( 'formid' => 'setsidebars' ), true, 'activate_plugins' ),
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
        $this->Forms = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_Formbuilder', 'class-forms.php', 'classes' );

        parent::setup( $action, $data );
        
        $this->add_text_box( 'buildsidebar-sidebarcompatibilitycheck', array( $this, 'postbox_buildsidebar_sidebarcompatibilitycheck' ), 'normal', true );
                
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
    * This function places content below the tab menu and above post-boxes.
    * 
    * @author Ryan Bayne
    * @package WTG Plugin Framework
    * @since 0.0.1
    * @version 1.0
    */    
    public function postbox_buildsidebar_sidebarcompatibilitycheck( $data, $box ) {
      
        $theme_compatibility = $this->WTGPORTALMANAGER->is_theme_compatible();
        
        if( !$theme_compatibility ) {
            echo ' 
            <div class="wtgportalmanager_status_box_container">
                <div class="welcome-panel">

                    <div class="welcome-panel-content">

                        <h3>New Theme Encountered</h3>
     
                        '. $this->UI->info_area( '', 'I have not worked with your theme before and so integration
                        has not been coded for you. You may need to enter an extra bit of information. The meta key
                        that your sidebar.php file uses is required. If your very unsure about this please seek
                        free assistance by sending your theme name to Ryan Bayne.' ) .'
                        
                        <p>'.__( 'I recommend that you do not edit your themes sidebar.php - let me help instead.' ).'</p>                                                     
                    </div>

                </div> 
            </div>';    
        }
    }

    /**
    * Setup the main sidebar. This will be the one that is applied in the theme where 
    * dynamic_sidebar( 'sidebar' - $id ) is used and not dynamic_sidebar( 'sidebar-1' - $id )
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_buildsidebar_setsidebars( $data, $box ) {                                
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Setup the main/default sidebar for the current portal. All pages opened within the portal will display this sidebar - a key element in giving the visitor the illusion of a dedicated area within a bigger WordPress.', 'wtgportalmanager' ), false );        
        $this->Forms->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        // create a more simplified array of registered sidebars - just need ID and names
        global $wp_registered_sidebars;
        $my_registered_sidebars_array = array();
        foreach( $wp_registered_sidebars as $sidebar ) {
            $my_registered_sidebars_array[ $sidebar['id'] ] = $sidebar['name'];    
        }
                
        $theme_compatibility = $this->WTGPORTALMANAGER->is_theme_compatible();
        if( $theme_compatibility ) {        
            
            /**
            * Theme Is Supported
            * 
            * The themes array will contain ['sidebar'] which holds information for each sidebar position
            * available in the theme.
            * 
            * The array includes values for working with post meta where custom sidebar ID's may be stored.
            * A theme will handle that itself but only if the user has manually assigned sidebar to page. 
            * 
            * WTG Portal Manager will add the meta key to all pages (maybe posts eventually) linked to the portal.
            */
            
            $themes_integration_array = $this->WTGPORTALMANAGER->get_themes_integration_info(); 
             
            if( isset( $themes_integration_array['sidebars'] ) ) 
            {   
                echo '<table>';
   
                foreach( $themes_integration_array['sidebars'] as $themes_dynamic_sidebars ) 
                {                           
                    // this is the coded sidebar ID - I call it sidebar position ID
                    // this ID will be used to replace
                    $sidebar_position_id = $themes_dynamic_sidebars['sidebarid'];
                    
                    // pass sidebar position ID (in sidebar.php) and portal ID to get the ID of the custom sidebar assigned to the sidebar position    
                    $saved_sidebar_id = $this->WTGPORTALMANAGER->get_dynamicsidebar_id( 
                        $this->DB->get_active_project_id(), 
                        $sidebar_position_id 
                    );
                
                    // a menu per sidebar - the post meta_key used to store sidebar ID is this menus ID
                    // the post meta_key becomes portal meta_key also, this is used to add the post meta if a portal post does not have it yet
                    $this->Forms->menu_basic( $box['args']['formid'], $themes_dynamic_sidebars['metakey'], $themes_dynamic_sidebars['metakey'], $themes_dynamic_sidebars['name'], $my_registered_sidebars_array, true, $saved_sidebar_id );                                           
                }
                
                echo '</table>';
            }
            
        }
        
        $this->UI->postbox_content_footer();                  
    }    
 
}?>