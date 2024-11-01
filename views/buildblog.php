<?php
/**
 * Define blog categories and blog specific blog posts display for the current portal.  
 *
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 0.0.1
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Define blog categories and blog specific blog posts display for the current portal.  
 *
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne
 * @since 0.0.1
 */
class WTGPORTALMANAGER_Buildblog_View extends WTGPORTALMANAGER_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 0.0.1
     *
     * @var int
     */
    protected $screen_columns = 2;
    
    protected $view_name = 'buildblog';

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
            array( $this->view_name . '-maincategory', __( 'Main Category', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal', 'default', array( 'formid' => 'maincategory' ), true, 'activate_plugins' ),
            array( $this->view_name . '-portalcategories', __( 'Portals Categories', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal', 'default', array( 'formid' => 'portalcategories' ), true, 'activate_plugins' ),
            array( $this->view_name . '-addcategories', __( 'Add Categories', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal', 'default', array( 'formid' => 'addcategories' ), true, 'activate_plugins' )
        );    
    }
            
    /**
    * Set up the view with data and do things that are specific for this view
    *
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.11
    * @version 1.0
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
        
        // load the current project row and settings from that row
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
    * Select a main category for the current active portal. The portal will
    * focus more on the posts in the main category. The main category will normally
    * have official posts, other categories may be run by the community or specific
    * developers. 
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_buildblog_maincategory( $data, $box ) {   
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Select the main blog category for your portal - usually the category offering official posts only, unique content and higher priority information than posts in other possible categories.', 'wtgportalmanager' ), false );        
        $this->Forms->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );                                 
        
        $current_category_id = $this->WTGPORTALMANAGER->get_portal_maincategory_id( $this->DB->get_active_project_id() );
        
        if( !is_numeric( $current_category_id ) ) {
            $current_category_id = 0;// WP default category    
        }
             
        echo '<table class="form-table">';

        $args = array(
            'type'                     => 'post',
            'child_of'                 => 0,
            'parent'                   => '',
            'orderby'                  => 'name',
            'order'                    => 'ASC',
            'hide_empty'               => 0,
            'hierarchical'             => 1,
            'exclude'                  => '',
            'include'                  => '',
            'number'                   => '',
            'taxonomy'                 => 'category',
            'pad_counts'               => false 
        );
 
        $categories = get_categories( $args );
        
        foreach( $categories as $key => $cat_item ) {
            $categories_array[ $cat_item->cat_ID ] = $cat_item->name;
        }
        
        $categories_array[ 'notselected' ] = __( 'Category Not Selected', 'wtgportalmanager' );
               
        $this->Forms->menu_basic( $box['args']['formid'], 'selectedcategory', 'selectedcategory', __( 'Select Main Category', 'wtgportalmanager' ), $categories_array, true, $current_category_id );     
        
        echo '</table>';
        
        $this->UI->postbox_content_footer();            
    } 
    
    /**
    * Assign additional categories to the current active blog.
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_buildblog_addcategories( $data, $box ) {   
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'If your portal should offer post from other categories please select those categories here. Excerpts or media from posts may be used to encourage traffic within the portal.', 'wtgportalmanager' ), false );        
        $this->Forms->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );                                 
        
        echo '<table class="form-table">';

        $args = array(
            'type'                     => 'post',
            'child_of'                 => 0,
            'parent'                   => '',
            'orderby'                  => 'name',
            'order'                    => 'ASC',
            'hide_empty'               => 0,
            'hierarchical'             => 1,
            'exclude'                  => '',
            'include'                  => '',
            'number'                   => '',
            'taxonomy'                 => 'category',
            'pad_counts'               => false 
        );
 
        $categories = get_categories( $args );
   
        foreach( $categories as $key => $cat_item ) {
            $categories_array[ $cat_item->cat_ID ] = $cat_item->name;
        }
        
        $this->Forms->menu_basic( $box['args']['formid'], 'selectedsubcategory', 'selectedsubcategory', __( 'Select Sub Category', 'wtgportalmanager' ), $categories_array, true );     
        
        echo '</table>';
        
        $this->UI->postbox_content_footer();            
    } 

    /**
    * A list of categories assigned to the current portal with the ability to remove them. 
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_buildblog_portalcategories( $data, $box ) {   
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'This is a list of categories already linked to the current portal.', 'wtgportalmanager' ), false );        
        $this->Forms->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );                                 
        
        // get subcategories
        $subcategories_array = $this->WTGPORTALMANAGER->get_portal_subcategories( $this->DB->get_active_project_id() );
        
        echo 'Just a basic list of ID but it will be improved with a table.';
        
        if( !$subcategories_array ) {  
            $this->WTGPORTALMANAGER->info_area( '', __( 'You do not have any sub-categories linked to the current portal.', 'wtgportalmanager' ) );  
        } else {
            foreach( $subcategories_array as $key => $subcat ) {
                echo '<p>' . $subcat . '</p>';    
            }
        }
        
        $this->UI->postbox_content_footer( __( 'Remove Selected Categories', 'wtgportalmanager' ) );            
    } 
    
    
}?>