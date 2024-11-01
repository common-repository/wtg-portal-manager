<?php
/**
 * Build pages offers forms for adding or removal pages to the current active portal.   
 *
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 0.0.1
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Build pages offers forms for adding or removal pages to the current active portal.   
 *
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne
 * @since 0.0.1
 */
class WTGPORTALMANAGER_Buildpages_View extends WTGPORTALMANAGER_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 0.0.1
     *
     * @var int
     */
    protected $screen_columns = 2;
    
    protected $view_name = 'buildpages';
    
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
            array( $this->view_name . '-createnewportalpage', __( 'Create New Portal Page', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal', 'default', array( 'formid' => 'createnewportalpage' ), true, 'activate_plugins' ),
            array( $this->view_name . '-addpagerelationship', __( 'Add New Page Relationship', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal', 'default', array( 'formid' => 'addpagerelationship' ), true, 'activate_plugins' ),
            
            // side
            array( $this->view_name . '-listrelatedpages', __( 'Portals Related Pages', 'wtgportalmanager' ), array( $this, 'parent' ), 'side', 'default', array( 'formid' => 'listrelatedpages' ), true, 'activate_plugins' ),
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
        $this->CONFIG = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_Configuration', 'class-configuration.php', 'classes' );

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
    * @version 1.0
    */
    function parent( $data, $box ) {
        eval( 'self::postbox_' . $this->view_name . '_' . $box['args']['formid'] . '( $data, $box );' );
    }    
    
    /**
    * Create a new page and link it to the current active portal.
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @version 1.1
    */
    public function postbox_buildpages_createnewportalpage( $data, $box ) {                                
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], 
        __( 'Create a new page with a specific purpose for the current portal. It
        will be instantly associated with your portal and added to the portals menu.', 'wtgportalmanager' ), false );        
        $this->Forms->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        ?>  

            <table class="form-table">                  
            <?php 
            $items_array = array();
            
            foreach( $this->CONFIG->postformats() as $format => $meta ) {
                $items_array[ $format ] = $meta['title'];
            }
            
            // select pages purpose, based on WP post formats and WTG custom formats
            $this->Forms->menu_basic( 
                $box['args']['formid'], 
                'pagepurpose', 
                'pagepurpose', 
                __( 'Page Purpose', 'wtgportalmanager' ), 
                $items_array, 
                false, 
                '', 
                array( 'alpha' ) 
            );
            ?>
            </table>
        
        <?php 
        $this->UI->postbox_content_footer();                  
    }      
         
    /**
    * Form for adding new page to the portal which basically means
    * making a relationship with the portals menu and the page.
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @version 1.0
    */
    public function postbox_buildpages_addpagerelationship( $data, $box ) {                                
  
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Add an existing page to your active portal. A relationship between page and portal will be created - allowing other options for that page.', 'wtgportalmanager' ), false );        
        $this->Forms->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        ?>  

            <table class="form-table">                  
            <?php 
            $this->Forms->text_basic( $box['args']['formid'], 'addpageid', 'addpageid', 'Page ID', '', true, array( 'numeric' ) );
  
            $items_array = array();
            
            foreach( $this->CONFIG->postformats() as $format => $meta ) {
                $items_array[ $format ] = $meta['title'];
            }
            
            // select pages purpose, based on WP post formats and WTG custom formats
            $this->Forms->menu_basic( 
                $box['args']['formid'], 
                'pagepurpose', 
                'pagepurpose', 
                __( 'Page Purpose', 'wtgportalmanager' ), 
                $items_array, 
                false, 
                '', 
                array( 'alpha' ) 
            );
            ?>
            </table>
        
        <?php 
        $this->UI->postbox_content_footer();                  
    }   
     
    /**
    * List of pages being used in the portal.
    *  
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @version 1.0
    * 
    * @todo add link to open pages
    * @todo add link to view pages
    */
    public function postbox_buildpages_listrelatedpages( $data, $box ) {                                
  
        $intro = __( 'A list of pages with a relationship with the current portal. This list is
        for reference only. See the Manage section for a more detailed table.', 'wtgportalmanager' );
        
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], $intro, false ); 
               
        $this->Forms->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        echo "<p>" . __( "Just page ID's for now pending improvement. This box will probably become a table of data including page names.", 'wtgportalmanager'). "</p>";
        ?>  

            <table class="form-table">                  
            <?php 
            $page_array = $this->DB->get_project_page_ids( WTGPORTALMANAGER_PROJECT_ID );
          
            if( !$page_array ) {
                _e( 'You have not linked any pages to your current portal.', 'wtgportalmanager');    
            } else {
                
                echo '<ol>';
       
                $linktext = __( 'Remove', 'wtgportalmanager' );
                
                foreach( $page_array as $key => $page_id ) {

                    $adminurl = admin_url() . 'admin.php?page=' . $_GET['page'] . '&wtgportalmanageraction=removeprojectpage&removedid=' . $page_id;
                    
                    $linktitle = sprintf( __( "Remove page %s from the current portal. This does not delete the actual page.", 'wtgportalmanager' ), $page_id );
                    
                    $removelink = '<a href="' . wp_nonce_url( $adminurl, 'removeprojectpage' ) . '" title="' . $linktitle . '">' . $linktext . '</a>';
                                                        
                    $p = $this->DB->get_project_page( WTGPORTALMANAGER_PROJECT_ID, $page_id );
                    echo '<li>' . $p['post_title'] . ' - ' . $p['purpose'] . ' - ' . $removelink . '</li>';

                }
                
                echo '</ol>';
            }
            ?>
            </table>
        
        <?php 
        //$this->UI->postbox_content_footer();                  
    } 
 
}?>