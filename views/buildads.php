<?php
/**
 * Assign and configure specific ads with the goal of creating 
 * campaigns per portal. 
 *
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 0.0.1
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Assign and configure specific ads with the goal of creating 
 * campaigns per portal.
 * 
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne
 * @since 0.0.1
 */
class WTGPORTALMANAGER_Buildads_View extends WTGPORTALMANAGER_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 0.0.1
     *
     * @var int
     */
    protected $screen_columns = 2;
    
    protected $view_name = 'buildads';           
            
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
            array( $this->view_name . '-currenttesting', __( 'Test Introduction', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal', 'default', array( 'formid' => 'currenttesting' ), true, 'activate_plugins' ),
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
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_buildads_currenttesting( $data, $box ) { ?>
    
        <p>Main Developer: Ryan Bayne</p>
        
        <p>Supporting Developer: Emmitt Roth</p>
        
        <p>It is time to make use of WordPress CRON and not rely on the WTG Portal Manager schedule system. WP CRON has the benefit of working with
        hosting that do not provide CRON. It is still not 100% accurate but has the benefit of allow users to register specific actions with
        targetted due times. The WTG Portal Manager schedule system simply runs one of many possibly actions within permitted hours.</p>
        
        <p>WTG Portal Manager existing systematic automation is a type of schedule system but in some ways is not. It is closer to a systematic triggering of
        actions within a set rate rather than targetting a specific time. It allows any number of actions to run during
        permitted times giving it the schedule feel but it is more about how long processing is allowed to be run for and what gets done during that
        processing i.e. update 10 posts or 20 posts, create 5 posts or 7 posts. The existing system is all about creating a constant automated progress
        with a speed suitable for the server. It is not meant for accuracy but instead is meant to ensure a gradual and controlled progress that does not
        slow down the blog. It has always been
        recommended that very small tasks are executed within the existing system. I may need to define that system on its own.</p>
        
        <p>This test area</p>
 
        <?php              
    }
}

class WTGPORTALMANAGER_Scheduledevents_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;
             
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'movie',     //singular name of the listed records
            'plural'    => 'movies',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }

    function column_default( $item, $column_name){
             
        $attributes = "class=\"$column_name column-$column_name\"";
                
        switch( $column_name){
            case 'date':
                return $item['date'];    
                break;                                                                  
            case 'schedule':
                return $item['schedule'];    
                break;                                                                  
            case 'interval':
                return $item['interval'];    
                break;            
            case 'hook':
                return $item['hook'];    
                break;                                                                  
            default:
                return 'No column function or default setup in switch statement';
        }
    }

    /*
    function column_title( $item){

    } */

    function get_columns() {
        $columns = array(
            'hook' => __( 'Event/Hook', 'wtgportalmanager' ),        
            'date' => __( 'Date & Time', 'wtgportalmanager' ),
            'schedule' => __( 'Schedule', 'wtgportalmanager' ),
            'interval' => __( 'Interval', 'wtgportalmanager' ),
        );

        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            //'post_title'     => array( 'post_title', false ),     //true means it's already sorted
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(

        );
        return $actions;
    }

    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die( 'Items deleted (or they would be if we had items to delete)!' );
        }
        
    }

    function prepare_items_further( $data, $per_page = 5) {
        global $wpdb; //This is used only if making any database queries        

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable);

        $this->process_bulk_action();

        $current_page = $this->get_pagenum();

        $total_items = count( $data);

        $data = array_slice( $data,(( $current_page-1)*$per_page), $per_page);

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil( $total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}
?>