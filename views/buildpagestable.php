<?php
/**
 * Table of portals existing pages and common pages with quick creation option.
 *
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 8.1.3
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class WTGPORTALMANAGER_Buildpagestable_View extends WTGPORTALMANAGER_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 8.1.3
     *
     * @var int
     */
    protected $screen_columns = 1;
    
    protected $view_name = 'portalspagestable';
    
    public $purpose = 'normal';// normal, dashboard

    /**
    * Array of meta boxes, looped through to register them on views and as dashboard widgets
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 8.1.33
    * @version 1.1
    */
    public function meta_box_array() {
        // array of meta boxes + used to register dashboard widgets (id, title, callback, context, priority, callback arguments (array), dashboard widget (boolean) )   
        return $this->meta_boxes_array = array(
            // array( id, title, callback (usually parent, approach created by Ryan Bayne), context (position), priority, call back arguments array, add to dashboard (boolean), required capability
            //array( $this->view_name . '-datasourcestable', __( 'Data Sources Table', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'datasourcestable' ), true, 'activate_plugins' ),
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
        if(!defined( "WTGPORTALMANAGER_VIEWNAME") ){define( "WTGPORTALMANAGER_VIEWNAME", $this->view_name );}
        
        // create class objects
        $this->WTGPORTALMANAGER = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER', 'class-wtgportalmanager.php', 'classes' );
        $this->UI = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_ADMINUI', 'class-adminui.php', 'classes' ); 
        $this->DB = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_DB', 'class-wpdb.php', 'classes' );
        $this->PHP = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_PHP', 'class-phplibrary.php', 'classes' );
        $this->FORMS = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_Formbuilder', 'class-forms.php', 'classes' );
        $this->CONFIG = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_Configuration', 'class-configuration.php', 'classes' );
                
        // set current project values
        if( isset( $wtgportalmanager_settings['currentproject'] ) && $wtgportalmanager_settings['currentproject'] !== false ) {
            $this->project_object = $this->DB->get_project( $wtgportalmanager_settings['currentproject'] ); 
            if( !$this->project_object ) {
                $this->current_project_settings = false;
            } else {
                $this->current_project_settings = maybe_unserialize( $this->project_object->projectsettings ); 
            }
        }
        
        parent::setup( $action, $data );

        $this->add_text_box( 'head', array( $this, 'intro' ), 'normal' );

        // create a data table ( use "head" to position before any meta boxes and outside of meta box related divs)
        $this->add_text_box( 'head', array( $this, 'datatables' ), 'normal' );
                 
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
    * @version 1.1
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
    * @version 1.1
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
        
    public function intro( $data, $box ) {
        
    } 
    
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_directorysources_datasourcestable( $data, $box ) { 

    }     

    /**
    * Displays one or more tables of data at the top of the page before post boxes
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function datatables( $data, $box ) { 
        global $wpdb, $wtgportalmanager_settings;
        
        $portals_pages_array = array();

        // Get portals existing pages.
        $page_array = $this->DB->get_project_page_ids( WTGPORTALMANAGER_PROJECT_ID );

        $final_key = null;
        foreach( $page_array as $key => $p ) {    
            
            $page = get_post( $p );

            $portals_pages_array[$key]['pageid'] = $p;
            $portals_pages_array[$key]['format'] = get_metadata( 'webtechglobal_projects', WTGPORTALMANAGER_PROJECT_ID, 'pagepurpose' . $p, true );
            $portals_pages_array[$key]['post_title'] = $page->post_title;
            $portals_pages_array[$key]['post_status'] = $page->post_status;
            
            $final_key = $key;
        }
        
        // Post Formats (custom formats added by WTG)
        // I will add a row for each format even if the page does not exist.
        foreach( $this->CONFIG->postformats() as $format => $meta ) {

            // increase our key from earlier loop.
            ++$final_key;
                      
            // if $format does not already exist in array we will add it to the table.
            foreach( $portals_pages_array as $next => $checkpageformat ) {

                if( $checkpageformat['format'] == $format ) {
                    break;    
                }   
                
                $portals_pages_array[$final_key]['format'] = $format;         
            }

        }    
        
        $WPTableObject = new WTGPORTALMANAGER_Portalspages_Table( $this->UI );
        $WPTableObject->prepare_items_further( $portals_pages_array, 50 );
        ?>

        <form method="get">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
            <?php             
            // display the table
            $WPTableObject->display();
            ?>
        </form>
 
        <?php               
    }   
}

class WTGPORTALMANAGER_Portalspages_Table extends WP_List_Table {

    private $UI;
     
    function __construct( $UI ) {   
        global $status, $page;
 
        $this->UI = $UI;
        
        //Set parent defaults
        parent::__construct( 
        array(
            'singular'  => 'movie',     //singular name of the listed records
            'plural'    => 'movies',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }

    function column_default( $item, $column_name ){

        $attributes = "class=\"$column_name column-$column_name\"";
                
        switch( $column_name ){
            case 'format':
                // TODO 5: Convert format to readable version, there is a function for doing this already.
                return $item['format'];    
                break;            
            case 'post_title':
                if( isset( $item['post_title'] ) ) { return $item['post_title']; }
                return '';    
                break;                      
            case 'post_status':   
                // TODO 5: Convert status to readable version.
                if( isset( $item['post_status'] ) ) { return $item['post_status']; }
                return '';    
                break;            
            case 'createlink':
                
                // If page does not exist offer Create Page link.
                if( !isset( $item['pageid'] ) ) {
                         
                    return $this->UI->linkaction( 
                        $_GET['page'], 
                        'quickcreateportalpage', 
                        __( 'Create this portal page.', 'wtgportalmanager' ),
                        __( 'Create Page', 'wtgportalmanager' ),   
                        '&format=' . $item['format']  /* values */ 
                    );
                        
                } else {
                    return __( 'Ready', 'wtgportalmanager' ); 
                }
                    
                break;          
            case 'viewlink':
                
                // If page does not exist offer Create Page link.
                if( !isset( $item['pageid'] ) ) {
                    
                    return __( 'Not Ready', 'wtgportalmanager' );
                    
                } else {
                    
                    $posturl = get_post_permalink( $item['pageid'], true );
                    
                    $v = __( 'View Page', 'wtgportalmanager' );
                    
                    $t = __( 'View this page.', 'wtgportalmanager' );
                    
                    return '<a href="' . $posturl . '" title="' . $t . '" class="button wtgportalmanagerbutton">' . $v . '</a>';
                    
                }                
                    
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
       
            'format' => __( 'Format', 'wtgportalmanager' ),
            'post_title' => __( 'Title', 'wtgportalmanager' ),
            'post_status' => __( 'Publish Status', 'wtgportalmanager' ),       
            'createlink' => __( 'Create Page', 'wtgportalmanager' ),       
            'viewlink' => __( 'View Page', 'wtgportalmanager' ),            
                                                                
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

        $total_items = count( $data );

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