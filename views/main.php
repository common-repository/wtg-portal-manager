<?php
/**
 * Main [section] - Projects [page]
 * 
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 0.0.1
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * View class for Main [section] - Projects [page]
 * 
 * @package WTG Portal Manager
 * @subpackage Views
 * @author Ryan Bayne
 * @since 0.0.1
 */
class WTGPORTALMANAGER_Main_View extends WTGPORTALMANAGER_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 0.0.1
     *
     * @var int
     */
    protected $screen_columns = 2;
    
    protected $view_name = 'main';
    
    public $purpose = 'normal';// normal, dashboard

    /**
    * Array of meta boxes, looped through to register them on views and as dashboard widgets
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function meta_box_array() {
        global $wtgportalmanager_settings;

        // array of meta boxes + used to register dashboard widgets (id, title, callback, context, priority, callback arguments (array), dashboard widget (boolean) )   
        $this->meta_boxes_array = array(
            array( $this->view_name . '-mailchimp', __( 'Subscribe for Updates', 'multitool' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'mailchimp' ), true, 'activate_plugins' ),
                        
            // package specific settings and tools
            array( 'main-createportal', __( 'Create Portal', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'createportal' ), true, 'activate_plugins' ),
            array( 'main-portalslist', __( 'Projects List', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'portalslist' ), true, 'activate_plugins' ),
            array( 'main-currentportal', __( 'Current Portal Selection', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'currentportal' ), true, 'activate_plugins' ),
            array( 'main-createsidebar', __( 'Create Sidebar', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'createsidebar' ), true, 'activate_plugins' ),
            array( 'main-sidebarlist', __( 'Sidebar List', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'sidebarlist' ), true, 'activate_plugins' ),
            array( 'main-configureforum', __( 'Configure Forum', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'configureforum' ), true, 'activate_plugins' ),
            array( 'main-setupdefaulttwitter', __( 'Default Twitter API Credentials', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'setupdefaulttwitter' ), true, 'activate_plugins' ),          

            // WTG core settings       
            array( 'main-globalswitches', __( 'Global Switches', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'globalswitches' ), true, 'activate_plugins' ),
            array( 'main-logsettings', __( 'Log Settings', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'logsettings' ), true, 'activate_plugins' ),
            array( 'main-pagecapabilitysettings', __( 'Page Capability Settings', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'pagecapabilitysettings' ), true, 'activate_plugins' ),
           
            // information and social
            array( 'main-twitterupdates', __( 'WebTechGlobal Twitter', 'wtgportalmanager' ), array( $this, 'parent' ), 'side','default',array( 'formid' => 'twitterupdates' ), true, 'activate_plugins' ),
            array( 'main-facebook', __( 'WebTechGlobal Facebook Page', 'wtgportalmanager' ), array( $this, 'parent' ), 'side','default',array( 'formid' => 'facebook' ), true, 'activate_plugins' ),
            array( $this->view_name . '-developertoolssetup', __( 'Developer Tools Setup', 'multitool' ), array( $this, 'parent' ), 'side','default',array( 'formid' => 'developertoolssetup' ), true, 'activate_plugins' ),            

        );
        
        // add meta boxes that have conditions i.e. a global switch
        if( isset( $wtgportalmanager_settings['widgetsettings']['dashboardwidgetsswitch'] ) && $wtgportalmanager_settings['widgetsettings']['dashboardwidgetsswitch'] == 'enabled' ) {
            $this->meta_boxes_array[] = array( 'main-dashboardwidgetsettings', __( 'Dashboard Widget Settings', 'wtgportalmanager' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'dashboardwidgetsettings' ), true, 'activate_plugins' );   
        }
        
        return $this->meta_boxes_array;                
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
        global $wtgportalmanager_settings,$wtgportalmanager_menu_array;
        
        // create constant for view name
        if(!defined( "WTGPORTALMANAGER_VIEWNAME") ){define( "WTGPORTALMANAGER_VIEWNAME", $this->view_name );}
        
        // create class objects
        $this->WTGPORTALMANAGER = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER', 'class-wtgportalmanager.php', 'classes' );
        $this->UI = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_ADMINUI', 'class-adminui.php', 'classes' );  
        $this->DB = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_DB', 'class-wpdb.php', 'classes' );
        $this->PHP = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_PHP', 'class-phplibrary.php', 'classes' );    
        $this->FORMS = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_Formbuilder', 'class-forms.php', 'classes' );
        $this->CONFIG = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER_Configuration', 'class-configuration.php', 'classes' );
         
        parent::setup( $action, $data );
        
        // only output meta boxes
        if( $this->purpose == 'normal' ) {
            self::metaboxes();// register meta boxes for the current view
        } elseif( $this->purpose == 'dashboard' ) {
            // do nothing - add_dashboard_widgets() in class-adminui.php calls dashboard_widgets() from this class
        } elseif( $this->purpose == 'customdashboard' ) {
            return self::meta_box_array();// return meta box array
        } else {
            // do nothing 
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
    * @package WTGPORTALMANAGER
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
    public function postbox_main_schedulerestrictions( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'This is a less of a specific day and time schedule. More of a system that allows systematic triggering of events within permitted hours. A new schedule system is in development though and will offer even more control with specific timing of events capable.', 'wtgportalmanager' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        ?>  

            <table class="form-table">
 
                <!-- Option Start -->
                <tr valign="top">
                    <th scope="row"><?php _e( 'Days', 'wtgportalmanager' ); ?></th>
                    <td>
                        <?php 
                        $days_array = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
                        $days_counter = 1;
                        
                        foreach( $days_array as $key => $day ){
                            
                            // set checked status
                            if( isset( $this->schedule['days'][$day] ) ){
                                $day_checked = 'checked';
                            }else{
                                $day_checked = '';            
                            }
                                 
                            echo '<input type="checkbox" name="wtgportalmanager_scheduleday_list[]" id="daycheck'.$days_counter.'" value="'.$day.'" '.$day_checked.' />
                            <label for="daycheck'.$days_counter.'">'.ucfirst( $day ).'</label><br />';    
                            ++$days_counter;
                        }?>
                    </td>
                </tr>
                <!-- Option End -->                          

                <!-- Option Start -->
                <tr valign="top">
                    <th scope="row"><?php _e( 'Hours', 'wtgportalmanager' ); ?></th>
                    <td>
                    <?php
                    // loop 24 times and create a checkbox for each hour
                    for( $i=0;$i<24;$i++){
                        
                        // check if the current hour exists in array, if it exists then it is permitted, if it does not exist it is not permitted
                        if( isset( $this->schedule['hours'][$i] ) ){
                            $hour_checked = ' checked'; 
                        }else{
                            $hour_checked = '';
                        }
                        
                        echo '<input type="checkbox" name="wtgportalmanager_schedulehour_list[]" id="hourcheck'.$i.'"  value="'.$i.'" '.$hour_checked.' />
                        <label for="hourcheck'.$i.'">'.$i.'</label><br>';    
                    }
                    ?>
                    </td>
                </tr>
                <!-- Option End -->          
         
                <!-- Option Start -->
                <tr valign="top">
                    <th scope="row"><?php _e( 'Daily Limit', 'wtgportalmanager' );?></th>
                    <td>
                        <fieldset><legend class="screen-reader-text"><span>Daily Limit</span></legend>
                            <input type="radio" id="wtgportalmanager_radio1_dripfeedrate_maximumperday" name="day" value="1" <?php if( isset( $this->schedule['limits']['day'] ) && $this->schedule['limits']['day'] == 1){echo 'checked';} ?> /><label for="wtgportalmanager_radio1_dripfeedrate_maximumperday"> <?php _e( '1', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio2_dripfeedrate_maximumperday" name="day" value="5" <?php if( isset( $this->schedule['limits']['day'] ) && $this->schedule['limits']['day'] == 5){echo 'checked';} ?> /><label for="wtgportalmanager_radio2_dripfeedrate_maximumperday"> <?php _e( '5', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio3_dripfeedrate_maximumperday" name="day" value="10" <?php if( isset( $this->schedule['limits']['day'] ) && $this->schedule['limits']['day'] == 10){echo 'checked';} ?> /><label for="wtgportalmanager_radio3_dripfeedrate_maximumperday"> <?php _e( '10', 'wtgportalmanager' );?> </label><br> 
                            <input type="radio" id="wtgportalmanager_radio9_dripfeedrate_maximumperday" name="day" value="24" <?php if( isset( $this->schedule['limits']['day'] ) && $this->schedule['limits']['day'] == 24){echo 'checked';} ?> /><label for="wtgportalmanager_radio9_dripfeedrate_maximumperday"> <?php _e( '24', 'wtgportalmanager' );?> </label><br>                    
                            <input type="radio" id="wtgportalmanager_radio4_dripfeedrate_maximumperday" name="day" value="50" <?php if( isset( $this->schedule['limits']['day'] ) && $this->schedule['limits']['day'] == 50){echo 'checked';} ?> /><label for="wtgportalmanager_radio4_dripfeedrate_maximumperday"> <?php _e( '50', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio5_dripfeedrate_maximumperday" name="day" value="250" <?php if( isset( $this->schedule['limits']['day'] ) && $this->schedule['limits']['day'] == 250){echo 'checked';} ?> /><label for="wtgportalmanager_radio5_dripfeedrate_maximumperday"> <?php _e( '250', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio6_dripfeedrate_maximumperday" name="day" value="1000" <?php if( isset( $this->schedule['limits']['day'] ) && $this->schedule['limits']['day'] == 1000){echo 'checked';} ?> /><label for="wtgportalmanager_radio6_dripfeedrate_maximumperday"> <?php _e( '1000', 'wtgportalmanager' );?> </label><br>                                                                                                                       
                            <input type="radio" id="wtgportalmanager_radio7_dripfeedrate_maximumperday" name="day" value="2000" <?php if( isset( $this->schedule['limits']['day'] ) && $this->schedule['limits']['day'] == 2000){echo 'checked';} ?> /><label for="wtgportalmanager_radio7_dripfeedrate_maximumperday"> <?php _e( '2000', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio8_dripfeedrate_maximumperday" name="day" value="5000" <?php if( isset( $this->schedule['limits']['day'] ) && $this->schedule['limits']['day'] == 5000){echo 'checked';} ?> /><label for="wtgportalmanager_radio8_dripfeedrate_maximumperday"> <?php _e( '5000', 'wtgportalmanager' );?> </label>                   
                        </fieldset>
                    </td>
                </tr>
                <!-- Option End -->   
                         
                <!-- Option Start -->
                <tr valign="top">
                    <th scope="row"><?php _e( 'Hourly Limit', 'wtgportalmanager' );?></th>
                    <td>
                        <fieldset><legend class="screen-reader-text"><span>Hourly Limit</span></legend>
                            <input type="radio" id="wtgportalmanager_radio1_dripfeedrate_maximumperhour" name="hour" value="1" <?php if( isset( $this->schedule['limits']['hour'] ) && $this->schedule['limits']['hour'] == 1){echo 'checked';} ?> /><label for="wtgportalmanager_radio1_dripfeedrate_maximumperhour"> <?php _e( '1', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio2_dripfeedrate_maximumperhour" name="hour" value="5" <?php if( isset( $this->schedule['limits']['hour'] ) && $this->schedule['limits']['hour'] == 5){echo 'checked';} ?> /><label for="wtgportalmanager_radio2_dripfeedrate_maximumperhour"> <?php _e( '5', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio3_dripfeedrate_maximumperhour" name="hour" value="10" <?php if( isset( $this->schedule['limits']['hour'] ) && $this->schedule['limits']['hour'] == 10){echo 'checked';} ?> /><label for="wtgportalmanager_radio3_dripfeedrate_maximumperhour"> <?php _e( '10', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio9_dripfeedrate_maximumperhour" name="hour" value="24" <?php if( isset( $this->schedule['limits']['hour'] ) && $this->schedule['limits']['hour'] == 24){echo 'checked';} ?> /><label for="wtgportalmanager_radio9_dripfeedrate_maximumperhour"> <?php _e( '24', 'wtgportalmanager' );?> </label><br>                    
                            <input type="radio" id="wtgportalmanager_radio4_dripfeedrate_maximumperhour" name="hour" value="50" <?php if( isset( $this->schedule['limits']['hour'] ) && $this->schedule['limits']['hour'] == 50){echo 'checked';} ?> /><label for="wtgportalmanager_radio4_dripfeedrate_maximumperhour"> <?php _e( '50', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio5_dripfeedrate_maximumperhour" name="hour" value="100" <?php if( isset( $this->schedule['limits']['hour'] ) && $this->schedule['limits']['hour'] == 100){echo 'checked';} ?> /><label for="wtgportalmanager_radio5_dripfeedrate_maximumperhour"> <?php _e( '100', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio6_dripfeedrate_maximumperhour" name="hour" value="250" <?php if( isset( $this->schedule['limits']['hour'] ) && $this->schedule['limits']['hour'] == 250){echo 'checked';} ?> /><label for="wtgportalmanager_radio6_dripfeedrate_maximumperhour"> <?php _e( '250', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio7_dripfeedrate_maximumperhour" name="hour" value="500" <?php if( isset( $this->schedule['limits']['hour'] ) && $this->schedule['limits']['hour'] == 500){echo 'checked';} ?> /><label for="wtgportalmanager_radio7_dripfeedrate_maximumperhour"> <?php _e( '500', 'wtgportalmanager' );?> </label><br>       
                            <input type="radio" id="wtgportalmanager_radio8_dripfeedrate_maximumperhour" name="hour" value="1000" <?php if( isset( $this->schedule['limits']['hour'] ) && $this->schedule['limits']['hour'] == 1000){echo 'checked';} ?> /><label for="wtgportalmanager_radio8_dripfeedrate_maximumperhour"> <?php _e( '1000', 'wtgportalmanager' );?> </label><br>                                                                                                                           
                       </fieldset>
                    </td>
                </tr>
                <!-- Option End -->   

                <!-- Option Start -->
                <tr valign="top">
                    <th scope="row"><?php _e( 'Event Limit', 'wtgportalmanager' );?></th>
                    <td>
                        <fieldset><legend class="screen-reader-text"><span>Event Limit</span></legend>
                            <input type="radio" id="wtgportalmanager_radio1_dripfeedrate_maximumpersession" name="session" value="1" <?php if( isset( $this->schedule['limits']['session'] ) && $this->schedule['limits']['session'] == 1){echo 'checked';} ?> /><label for="wtgportalmanager_radio1_dripfeedrate_maximumpersession"> <?php _e( '1', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio2_dripfeedrate_maximumpersession" name="session" value="5" <?php if( isset( $this->schedule['limits']['session'] ) && $this->schedule['limits']['session'] == 5){echo 'checked';} ?> /><label for="wtgportalmanager_radio2_dripfeedrate_maximumpersession"> <?php _e( '5', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio3_dripfeedrate_maximumpersession" name="session" value="10" <?php if( isset( $this->schedule['limits']['session'] ) && $this->schedule['limits']['session'] == 10){echo 'checked';} ?> /><label for="wtgportalmanager_radio3_dripfeedrate_maximumpersession"> <?php _e( '10', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio9_dripfeedrate_maximumpersession" name="session" value="25" <?php if( isset( $this->schedule['limits']['session'] ) && $this->schedule['limits']['session'] == 25){echo 'checked';} ?> /><label for="wtgportalmanager_radio9_dripfeedrate_maximumpersession"> <?php _e( '25', 'wtgportalmanager' );?> </label><br>                    
                            <input type="radio" id="wtgportalmanager_radio4_dripfeedrate_maximumpersession" name="session" value="50" <?php if( isset( $this->schedule['limits']['session'] ) && $this->schedule['limits']['session'] == 50){echo 'checked';} ?> /><label for="wtgportalmanager_radio4_dripfeedrate_maximumpersession"> <?php _e( '50', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio5_dripfeedrate_maximumpersession" name="session" value="100" <?php if( isset( $this->schedule['limits']['session'] ) && $this->schedule['limits']['session'] == 100){echo 'checked';} ?> /><label for="wtgportalmanager_radio5_dripfeedrate_maximumpersession"> <?php _e( '100', 'wtgportalmanager' );?> </label><br>
                            <input type="radio" id="wtgportalmanager_radio6_dripfeedrate_maximumpersession" name="session" value="200" <?php if( isset( $this->schedule['limits']['session'] ) && $this->schedule['limits']['session'] == 200){echo 'checked';} ?> /><label for="wtgportalmanager_radio6_dripfeedrate_maximumpersession"> <?php _e( '200', 'wtgportalmanager' );?> </label><br>                                                                                                                        
                            <input type="radio" id="wtgportalmanager_radio7_dripfeedrate_maximumpersession" name="session" value="300" <?php if( isset( $this->schedule['limits']['session'] ) && $this->schedule['limits']['session'] == 300){echo 'checked';} ?> /><label for="wtgportalmanager_radio7_dripfeedrate_maximumpersession"> <?php _e( '300', 'wtgportalmanager' );?> </label><br>          
                        </fieldset>
                    </td>
                </tr>
                <!-- Option End -->     
                
            </table>
             
        <?php 
        $this->UI->postbox_content_footer();
    }
    
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_main_scheduleinformation( $data, $box ) {  ?>
            <h4><?php _e( 'Last Schedule Finish Reason', 'wtgportalmanager' );?></h4>
            <p>
            <?php 
            if( isset( $this->schedule['history']['lastreturnreason'] ) ){
                echo $this->schedule['history']['lastreturnreason']; 
            }else{
                _e( 'No event refusal reason has been set yet', 'wtgportalmanager' );    
            }?>
            </p>
            
            <h4><?php _e( 'Events Counter - 60 Minute Period', 'wtgportalmanager' );?></h4>
            <p>
            <?php 
            if( isset( $this->schedule['history']['hourcounter'] ) ){
                echo $this->schedule['history']['hourcounter']; 
            }else{
                _e( 'No events have been done during the current 60 minute period', 'wtgportalmanager' );    
            }?>
            </p> 

            <h4><?php _e( 'Events Counter - 24 Hour Period', 'wtgportalmanager' );?></h4>
            <p>
            <?php 
            if( isset( $this->schedule['history']['daycounter'] ) ){
                echo $this->schedule['history']['daycounter']; 
            }else{
                _e( 'No events have been done during the current 24 hour period', 'wtgportalmanager' );    
            }?>
            </p>

            <h4><?php _e( 'Last Event Type', 'wtgportalmanager' ); ?></h4>
            <p>
            <?php 
            if( isset( $this->schedule['history']['lasteventtype'] ) ){
                
                if( $this->schedule['history']['lasteventtype'] == 'dataimport' ){
                    echo 'Data Import';            
                }elseif( $this->schedule['history']['lasteventtype'] == 'dataupdate' ){
                    echo 'Data Update';
                }elseif( $this->schedule['history']['lasteventtype'] == 'postcreation' ){
                    echo 'Post Creation';
                }elseif( $this->schedule['history']['lasteventtype'] == 'postupdate' ){
                    echo 'Post Update';
                }elseif( $this->schedule['history']['lasteventtype'] == 'twittersend' ){
                    echo 'Twitter: New Tweet';
                }elseif( $this->schedule['history']['lasteventtype'] == 'twitterupdate' ){
                    echo 'Twitter: Send Update';
                }elseif( $this->schedule['history']['lasteventtype'] == 'twitterget' ){
                    echo 'Twitter: Get Reply';
                }
                 
            }else{
                _e( 'No events have been carried out yet', 'wtgportalmanager' );    
            }?>
            </p>

            <h4><?php _e( 'Last Event Action', 'wtgportalmanager' ); ?></h4>
            <p>
            <?php 
            if( isset( $this->schedule['history']['lasteventaction'] ) ){
                echo $this->schedule['history']['lasteventaction']; 
            }else{
                _e( 'No event actions have been carried out yet', 'wtgportalmanager' );    
            }?>
            </p>
                
            <h4><?php _e( 'Last Event Time', 'wtgportalmanager' ); ?></h4>
            <p>
            <?php 
            if( isset( $this->schedule['history']['lasteventtime'] ) ){
                echo date( "F j, Y, g:i a", $this->schedule['history']['lasteventtime'] ); 
            }else{
                _e( 'No schedule events have ran on this server yet', 'wtgportalmanager' );    
            }?>
            </p>
            
            <h4><?php _e( 'Last Hourly Reset', 'wtgportalmanager' ); ?></h4>
            <p>
            <?php 
            if( isset( $this->schedule['history']['hour_lastreset'] ) ){
                echo date( "F j, Y, g:i a", $this->schedule['history']['hour_lastreset'] ); 
            }else{
                _e( 'No hourly reset has been done yet', 'wtgportalmanager' );    
            }?>
            </p>   
                
            <h4><?php _e( 'Last 24 Hour Period Reset', 'wtgportalmanager' ); ?></h4>
            <p>
            <?php 
            if( isset( $this->schedule['history']['day_lastreset'] ) ){
                echo date( "F j, Y, g:i a", $this->schedule['history']['day_lastreset'] ); 
            }else{
                _e( 'No 24 hour reset has been done yet', 'wtgportalmanager' );    
            }?>
            </p> 
               
            <h4><?php _e( 'Your Servers Current Data and Time', 'wtgportalmanager' ); ?></h4>
            <p><?php echo date( "F j, Y, g:i a",time() );?></p>     
            
        <?php                       
    }
    
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.1
    */
    public function postbox_main_globalswitches( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'These switches disable or enable systems. Disabling systems you do not require will improve the plugins performance.', 'wtgportalmanager' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $wtgportalmanager_settings;
        ?>  

            <table class="form-table">
            <?php        
            $this->UI->option_switch( __( 'WordPress Notice Styles', 'wtgportalmanager' ), 'uinoticestyle', 'uinoticestyle', $wtgportalmanager_settings['noticesettings']['wpcorestyle'] );
            $this->UI->option_switch( __( 'WTG Flag System', 'wtgportalmanager' ), 'flagsystemstatus', 'flagsystemstatus', $wtgportalmanager_settings['posttypes']['wtgflags']['status'] );
            $this->UI->option_switch( __( 'Dashboard Widgets Switch', 'wtgportalmanager' ), 'dashboardwidgetsswitch', 'dashboardwidgetsswitch', $wtgportalmanager_settings['widgetsettings']['dashboardwidgetsswitch'], 'Enabled', 'Disabled', 'disabled' );      
            $this->UI->option_switch( __( 'Twitter API Switch', 'wtgportalmanager' ), 'twitterapiswitch', 'twitterapiswitch', $wtgportalmanager_settings['api']['twitter']['active'], 'Enabled', 'Disabled', 'disabled' );      
            ?>
            </table> 
            
        <?php 
        $this->UI->postbox_content_footer();
    }
    
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_main_logsettings( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'The plugin has its own log system with multi-purpose use. Not everything is logged for the sake of performance so please request increased log use if required.', 'wtgportalmanager' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $wtgportalmanager_settings;
        ?>  

            <table class="form-table">
                <!-- Option Start -->
                <tr valign="top">
                    <th scope="row">Log</th>
                    <td>
                        <?php 
                        // if is not set ['admintriggers']['newcsvfiles']['status'] then it is enabled by default
                        if(!isset( $wtgportalmanager_settings['globalsettings']['uselog'] ) ){
                            $radio1_uselog_enabled = 'checked'; 
                            $radio2_uselog_disabled = '';                    
                        }else{
                            if( $wtgportalmanager_settings['globalsettings']['uselog'] == 1){
                                $radio1_uselog_enabled = 'checked'; 
                                $radio2_uselog_disabled = '';    
                            }elseif( $wtgportalmanager_settings['globalsettings']['uselog'] == 0){
                                $radio1_uselog_enabled = ''; 
                                $radio2_uselog_disabled = 'checked';    
                            }
                        }?>
                        <fieldset><legend class="screen-reader-text"><span>Log</span></legend>
                            <input type="radio" id="logstatus_enabled" name="wtgportalmanager_radiogroup_logstatus" value="1" <?php echo $radio1_uselog_enabled;?> />
                            <label for="logstatus_enabled"> <?php _e( 'Enable', 'wtgportalmanager' ); ?></label>
                            <br />
                            <input type="radio" id="logstatus_disabled" name="wtgportalmanager_radiogroup_logstatus" value="0" <?php echo $radio2_uselog_disabled;?> />
                            <label for="logstatus_disabled"> <?php _e( 'Disable', 'wtgportalmanager' ); ?></label>
                        </fieldset>
                    </td>
                </tr>
                <!-- Option End -->
      
                <?php       
                // log rows limit
                if(!isset( $wtgportalmanager_settings['globalsettings']['loglimit'] ) || !is_numeric( $wtgportalmanager_settings['globalsettings']['loglimit'] ) ){$wtgportalmanager_settings['globalsettings']['loglimit'] = 1000;}
                $this->UI->option_text( 'Log Entries Limit', 'wtgportalmanager_loglimit', 'loglimit', $wtgportalmanager_settings['globalsettings']['loglimit'] );
                ?>
            </table> 
            
                    
            <h4>Outcomes</h4>
            <label for="wtgportalmanager_log_outcomes_success"><input type="checkbox" name="wtgportalmanager_log_outcome[]" id="wtgportalmanager_log_outcomes_success" value="1" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['outcomecriteria']['1'] ) ){echo 'checked';} ?>> Success</label>
            <br> 
            <label for="wtgportalmanager_log_outcomes_fail"><input type="checkbox" name="wtgportalmanager_log_outcome[]" id="wtgportalmanager_log_outcomes_fail" value="0" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['outcomecriteria']['0'] ) ){echo 'checked';} ?>> Fail/Rejected</label>

            <h4>Type</h4>
            <label for="wtgportalmanager_log_type_general"><input type="checkbox" name="wtgportalmanager_log_type[]" id="wtgportalmanager_log_type_general" value="general" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['typecriteria']['general'] ) ){echo 'checked';} ?>> General</label>
            <br>
            <label for="wtgportalmanager_log_type_error"><input type="checkbox" name="wtgportalmanager_log_type[]" id="wtgportalmanager_log_type_error" value="error" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['typecriteria']['error'] ) ){echo 'checked';} ?>> Errors</label>
            <br>
            <label for="wtgportalmanager_log_type_trace"><input type="checkbox" name="wtgportalmanager_log_type[]" id="wtgportalmanager_log_type_trace" value="flag" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['typecriteria']['flag'] ) ){echo 'checked';} ?>> Trace</label>

            <h4>Priority</h4>
            <label for="wtgportalmanager_log_priority_low"><input type="checkbox" name="wtgportalmanager_log_priority[]" id="wtgportalmanager_log_priority_low" value="low" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['prioritycriteria']['low'] ) ){echo 'checked';} ?>> Low</label>
            <br>
            <label for="wtgportalmanager_log_priority_normal"><input type="checkbox" name="wtgportalmanager_log_priority[]" id="wtgportalmanager_log_priority_normal" value="normal" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['prioritycriteria']['normal'] ) ){echo 'checked';} ?>> Normal</label>
            <br>
            <label for="wtgportalmanager_log_priority_high"><input type="checkbox" name="wtgportalmanager_log_priority[]" id="wtgportalmanager_log_priority_high" value="high" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['prioritycriteria']['high'] ) ){echo 'checked';} ?>> High</label>
            
            <h1>Custom Search</h1>
            <p>This search criteria is not currently stored, it will be used on the submission of this form only.</p>
         
            <h4>Page</h4>
            <select name="wtgportalmanager_pluginpages_logsearch" id="wtgportalmanager_pluginpages_logsearch" >
                <option value="notselected">Do Not Apply</option>
                <?php
                $current = '';
                if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['page'] ) && $wtgportalmanager_settings['logsettings']['logscreen']['page'] != 'notselected' ){
                    $current = $wtgportalmanager_settings['logsettings']['logscreen']['page'];
                } 
                $this->UI->page_menuoptions( $current);?> 
            </select>
            
            <h4>Action</h4> 
            <select name="csv2pos_logactions_logsearch" id="csv2pos_logactions_logsearch" >
                <option value="notselected">Do Not Apply</option>
                <?php 
                $current = '';
                if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['action'] ) && $wtgportalmanager_settings['logsettings']['logscreen']['action'] != 'notselected' ){
                    $current = $wtgportalmanager_settings['logsettings']['logscreen']['action'];
                }
                $action_results = $this->DB->log_queryactions( $current);
                if( $action_results){
                    foreach( $action_results as $key => $action){
                        $selected = '';
                        if( $action['action'] == $current){
                            $selected = 'selected="selected"';
                        }
                        echo '<option value="'.$action['action'].'" '.$selected.'>'.$action['action'].'</option>'; 
                    }   
                }?> 
            </select>
            
            <h4>Screen Name</h4>
            <select name="wtgportalmanager_pluginscreens_logsearch" id="wtgportalmanager_pluginscreens_logsearch" >
                <option value="notselected">Do Not Apply</option>
                <?php 
                $current = '';
                if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['screen'] ) && $wtgportalmanager_settings['logsettings']['logscreen']['screen'] != 'notselected' ){
                    $current = $wtgportalmanager_settings['logsettings']['logscreen']['screen'];
                }
                $this->UI->screens_menuoptions( $current );?> 
            </select>
                  
            <h4>PHP Line</h4>
            <input type="text" name="wtgportalmanager_logcriteria_phpline" value="<?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['line'] ) ){echo $wtgportalmanager_settings['logsettings']['logscreen']['line'];} ?>">
            
            <h4>PHP File</h4>
            <input type="text" name="wtgportalmanager_logcriteria_phpfile" value="<?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['file'] ) ){echo $wtgportalmanager_settings['logsettings']['logscreen']['file'];} ?>">
            
            <h4>PHP Function</h4>
            <input type="text" name="wtgportalmanager_logcriteria_phpfunction" value="<?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['function'] ) ){echo $wtgportalmanager_settings['logsettings']['logscreen']['function'];} ?>">
            
            <h4>Panel Name</h4>
            <input type="text" name="wtgportalmanager_logcriteria_panelname" value="<?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['panelname'] ) ){echo $wtgportalmanager_settings['logsettings']['logscreen']['panelname'];} ?>">

            <h4>IP Address</h4>
            <input type="text" name="wtgportalmanager_logcriteria_ipaddress" value="<?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['ipaddress'] ) ){echo $wtgportalmanager_settings['logsettings']['logscreen']['ipaddress'];} ?>">
           
            <h4>User ID</h4>
            <input type="text" name="wtgportalmanager_logcriteria_userid" value="<?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['userid'] ) ){echo $wtgportalmanager_settings['logsettings']['logscreen']['userid'];} ?>">    
          
            <h4>Display Fields</h4>                                                                                                                                        
            <label for="wtgportalmanager_logfields_outcome"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_outcome" value="outcome" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['outcome'] ) ){echo 'checked';} ?>> <?php _e( 'Outcome', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_line"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_line" value="line" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['line'] ) ){echo 'checked';} ?>> <?php _e( 'Line', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_file"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_file" value="file" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['file'] ) ){echo 'checked';} ?>> <?php _e( 'File', 'wtgportalmanager' );?></label> 
            <br>
            <label for="wtgportalmanager_logfields_function"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_function" value="function" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['function'] ) ){echo 'checked';} ?>> <?php _e( 'Function', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_sqlresult"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_sqlresult" value="sqlresult" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['sqlresult'] ) ){echo 'checked';} ?>> <?php _e( 'SQL Result', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_sqlquery"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_sqlquery" value="sqlquery" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['sqlquery'] ) ){echo 'checked';} ?>> <?php _e( 'SQL Query', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_sqlerror"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_sqlerror" value="sqlerror" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['sqlerror'] ) ){echo 'checked';} ?>> <?php _e( 'SQL Error', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_wordpresserror"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_wordpresserror" value="wordpresserror" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['wordpresserror'] ) ){echo 'checked';} ?>> <?php _e( 'WordPress Erro', 'wtgportalmanager' );?>r</label>
            <br>
            <label for="wtgportalmanager_logfields_screenshoturl"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_screenshoturl" value="screenshoturl" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['screenshoturl'] ) ){echo 'checked';} ?>> <?php _e( 'Screenshot URL', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_userscomment"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_userscomment" value="userscomment" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['userscomment'] ) ){echo 'checked';} ?>> <?php _e( 'Users Comment', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_page"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_page" value="page" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['page'] ) ){echo 'checked';} ?>> <?php _e( 'Page', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_version"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_version" value="version" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['version'] ) ){echo 'checked';} ?>> <?php _e( 'Plugin Version', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_panelname"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_panelname" value="panelname" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['panelname'] ) ){echo 'checked';} ?>> <?php _e( 'Panel Name', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_tabscreenname"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_tabscreenname" value="tabscreenname" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['outcome'] ) ){echo 'checked';} ?>> <?php _e( 'Screen Name *', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_dump"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_dump" value="dump" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['dump'] ) ){echo 'checked';} ?>> <?php _e( 'Dump', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_ipaddress"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_ipaddress" value="ipaddress" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['ipaddress'] ) ){echo 'checked';} ?>> <?php _e( 'IP Address', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_userid"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_userid" value="userid" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['userid'] ) ){echo 'checked';} ?>> <?php _e( 'User ID', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_comment"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_comment" value="comment" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['comment'] ) ){echo 'checked';} ?>> <?php _e( 'Developers Comment', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_type"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_type" value="type" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['type'] ) ){echo 'checked';} ?>> <?php _e( 'Entry Type', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_category"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_category" value="category" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['category'] ) ){echo 'checked';} ?>> <?php _e( 'Category', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_action"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_action" value="action" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['action'] ) ){echo 'checked';} ?>> <?php _e( 'Action', 'wtgportalmanager' );?></label>
            <br>
            <label for="wtgportalmanager_logfields_priority"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_priority" value="priority" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['priority'] ) ){echo 'checked';} ?>> <?php _e( 'Priority', 'wtgportalmanager' );?></label> 
            <br>
            <label for="wtgportalmanager_logfields_thetrigger"><input type="checkbox" name="wtgportalmanager_logfields[]" id="wtgportalmanager_logfields_thetrigger" value="thetrigger" <?php if( isset( $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns']['thetrigger'] ) ){echo 'checked';} ?>> <?php _e( 'Trigger', 'wtgportalmanager' );?></label> 

    
        <?php 
        $this->UI->postbox_content_footer();
    }    
        
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_main_iconsexplained( $data, $box ) {    
        ?>  
        <p class="about-description"><?php _e( 'The plugin has icons on the UI offering different types of help...' ); ?></p>
        
        <h3>Help Icon<?php echo $this->UI->helpicon( 'http://www.webtechglobal.co.uk/wtgportalmanager' )?></h3>
        <p><?php _e( 'The help icon offers a tutorial or indepth description on the WebTechGlobal website. Clicking these may open
        take a key page in the plugins portal or post in the plugins blog. On a rare occasion you will be taking to another users 
        website who has published a great tutorial or technical documentation.' )?></p>        
        
        <h3>Discussion Icon<?php echo $this->UI->discussicon( 'http://www.webtechglobal.co.uk/wtgportalmanager' )?></h3>
        <p><?php _e( 'The discussion icon open an active forum discussion or chat on the WebTechGlobal domain in a new tab. If you see this icon
        it means you are looking at a feature or area of the plugin that is a hot topic. It could also indicate the
        plugin author would like to hear from you regarding a specific feature. Occasionally these icons may take you to a discussion
        on other websites such as a Google circles, an official page on Facebook or a good forum thread on a users domain.' )?></p>
                          
        <h3>Info Icon<img src="<?php echo WTGPORTALMANAGER_IMAGES_URL;?>info-icon.png" alt="<?php _e( 'Icon with an i click it to read more information in a popup.' );?>"></h3>
        <p><?php _e( 'The information icon will not open another page. It will display a pop-up with extra information. This is mostly used within
        panels to explain forms and the status of the panel.' )?></p>        
        
        <h3>Video Icon<?php echo $this->UI->videoicon( 'http://www.webtechglobal.co.uk/wtgportalmanager' )?></h3>
        <p><?php _e( 'clicking on the video icon will open a new tab to a YouTube video. Occasionally it may open a video on another
        website. Occasionally a video may even belong to a user who has created a good tutorial.' )?></p> 
               
        <h3>Trash Icon<?php echo $this->UI->trashicon( 'http://www.webtechglobal.co.uk/wtgportalmanager' )?></h3>
        <p><?php _e( 'The trash icon will be shown beside items that can be deleted or objects that can be hidden.
        Sometimes you can hide a panel as part of the plugins configuration. Eventually I hope to be able to hide
        notices, especially the larger ones..' )?></p>      
      <?php     
    }
    
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_main_twitterupdates( $data, $box ) {    
        ?>
        <p class="about-description"><?php _e( 'Thank this plugins developers with a Tweet...', 'wtgportalmanager' ); ?></p>    
        <a class="twitter-timeline" href="https://twitter.com/WebTechGlobal" data-widget-id="511630591142268928">Tweets by @WebTechGlobal</a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id) ){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");</script>                                                   
        <?php     
    }    
    
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.2
    */
    public function postbox_main_facebook( $data, $box ) {  
        $introduction = __( 'Get free and automatic entry into giveaways by
        Liking or commenting on the WTG Facebook page. You could be giving free
        hosting, domains, premium plugins and themes.', 'wtgeci' );       
        echo "<p class=\"multitool_boxes_introtext\">". $introduction ."</p>"        
        ?>       
        <iframe src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2FWebTechGlobal1&amp;width=350&amp;height=290&amp;colorscheme=light&amp;show_faces=true&amp;header=true&amp;stream=false&amp;show_border=true" scrolling="no" frameborder="0" style="padding: 10px 0 0 0;border:none; overflow:hidden; width:100%; height:290px;" allowTransparency="true"></iframe>                                                                             
        <?php     
    }

        /**
    * Mailchimp subscribers list form.
    * 
    * WebTechGlobal mailing list. 
    * 
    * @author Ryan Bayne
    * @package WebTechGlobal WordPress Plugins 
    * @version 1.1
    */
    public function postbox_main_mailchimp( $data, $box ) {  
    ?>

        <!-- Begin MailChimp Signup Form -->
        <link href="//cdn-images.mailchimp.com/embedcode/classic-081711.css" rel="stylesheet" type="text/css">
        <style type="text/css">
            #mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; }
            /* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
               We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
        </style>
        <div id="mc_embed_signup">
        <form action="//webtechglobal.us9.list-manage.com/subscribe/post?u=99272fe1772de14ff2be02fe6&amp;id=018f5572ec" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
            <div id="mc_embed_signup_scroll">
            <h2><?php _e( 'Please Subscribe to WTG Mailing List', 'starcitizenfansitekit' ); ?></h2>
        <h3><?php _e( 'This is an annual newsletter and high-priority updates service
        only. Providing a valid PayPal transaction ID will gain you an extra free entry
        into all of our giveaways in future.', 'starcitizenfansitekit' ); ?></h3>
        <div class="indicates-required"><span class="asterisk">*</span> indicates required</div>
        <div class="mc-field-group">
            <label for="mce-EMAIL">Email Address  <span class="asterisk">*</span>
        </label>
            <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
        </div>
        <div class="mc-field-group">
            <label for="mce-FNAME">First Name </label>
            <input type="text" value="" name="FNAME" class="" id="mce-FNAME">
        </div>
        <div class="mc-field-group">
            <label for="mce-LNAME">Last Name </label>
            <input type="text" value="" name="LNAME" class="" id="mce-LNAME">
        </div>
        <div class="mc-field-group input-group">
            <strong>Email Format </strong>
            <ul><li><input type="radio" value="html" name="EMAILTYPE" id="mce-EMAILTYPE-0"><label for="mce-EMAILTYPE-0">html</label></li>
        <li><input type="radio" value="text" name="EMAILTYPE" id="mce-EMAILTYPE-1"><label for="mce-EMAILTYPE-1">text</label></li>
        </ul>
        </div>
        <p>Powered by <a href="http://eepurl.com/2W_2n" title="MailChimp - email marketing made easy and fun">MailChimp</a></p>
            <div id="mce-responses" class="clear">
                <div class="response" id="mce-error-response" style="display:none"></div>
                <div class="response" id="mce-success-response" style="display:none"></div>
            </div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
            <div style="position: absolute; left: -5000px;"><input type="text" name="b_99272fe1772de14ff2be02fe6_018f5572ec" tabindex="-1" value=""></div>
            <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
            </div>
        </form>
        </div>

        <!--End mc_embed_signup-->

    <?php   

    }  
 
    /**
    * Form for setting which captability is required to view the page
    * 
    * By default there is no settings data for this because most people will never use it.
    * However when it is used, a new option record is created so that the settings are
    * independent and can be accessed easier.  
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_main_pagecapabilitysettings( $data, $box ) {
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Set the capability a user requires to view any of the plugins pages. This works independently of role plugins such as Role Scoper.', 'wtgportalmanager' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $wtgportalmanager_menu_array;
        ?>
        
        <table class="form-table">
        
        <?php 
        // get stored capability settings 
        $saved_capability_array = get_option( 'wtgportalmanager_capabilities' );
        
        // add a menu for each page for the user selecting the required capability 
        foreach( $wtgportalmanager_menu_array as $key => $page_array ) {
            
            // do not add the main page to the list as a strict security measure
            if( $page_array['name'] !== 'main' ) {
                $current = null;
                if( isset( $saved_capability_array['pagecaps'][ $page_array['name'] ] ) && is_string( $saved_capability_array['pagecaps'][ $page_array['name'] ] ) ) {
                    $current = $saved_capability_array['pagecaps'][ $page_array['name'] ];
                }
                
                $this->UI->option_menu_capabilities( $page_array['menu'], 'pagecap' . $page_array['name'], 'pagecap' . $page_array['name'], $current );
            }
        }?>
        
        </table>
        
        <?php 
        $this->UI->postbox_content_footer();        
    }
    
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_main_dashboardwidgetsettings( $data, $box ) { 
        global $wtgportalmanager_settings,$wtgportalmanager_menu_array;
           
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'This panel is new and is advanced.   
        Please seek my advice before using it.
        You must be sure and confident that it operates in the way you expect.
        It will add widgets to your dashboard. 
        The capability menu allows you to set a global role/capability requirements for the group of wigets from any giving page. 
        The capability options in the "Page Capability Settings" panel are regarding access to the admin page specifically.', 'wtgportalmanager' ), false );   
             
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );

        echo '<table class="form-table">';

        // now loop through views, building settings per box (display or not, permitted role/capability  

        foreach( $wtgportalmanager_menu_array as $key => $section_array ) {

            /*
                'groupname' => string 'main' (length=4)
                'slug' => string 'wtgportalmanager_generalsettings' (length=24)
                'menu' => string 'General Settings' (length=16)
                'pluginmenu' => string 'General Settings' (length=16)
                'name' => string 'generalsettings' (length=15)
                'title' => string 'General Settings' (length=16)
                'parent' => string 'main' (length=4)
            */
            
            // get dashboard activation status for the current page
            $current_for_page = '123nocurrentvalue';
            if( isset( $wtgportalmanager_settings['widgetsettings'][ $section_array['name'] . 'dashboardwidgetsswitch'] ) ) {
                $current_for_page = $wtgportalmanager_settings['widgetsettings'][ $section_array['name'] . 'dashboardwidgetsswitch'];   
            }
            
            // display switch for current page
            $this->UI->option_switch( $section_array['menu'], $section_array['name'] . 'dashboardwidgetsswitch', $section_array['name'] . 'dashboardwidgetsswitch', $current_for_page, 'Enabled', 'Disabled', 'disabled' );
            
            // get current pages minimum dashboard widget capability
            $current_capability = '123nocapability';
            if( isset( $wtgportalmanager_settings['widgetsettings'][ $section_array['name'] . 'widgetscapability'] ) ) {
                $current_capability = $wtgportalmanager_settings['widgetsettings'][ $section_array['name'] . 'widgetscapability'];   
            }
                            
            // capabilities menu for each page (rather than individual boxes, the boxes will have capabilities applied in code)
            $this->UI->option_menu_capabilities( __( 'Capability Required', 'wtgportalmanager' ), $section_array['name'] . 'widgetscapability', $section_array['name'] . 'widgetscapability', $current_capability );
        }

        echo '</table>';
                    
        $this->UI->postbox_content_footer();
    }    
    
    /**
    * Form for creating a new portal.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0                                                                              
    */
    public function postbox_main_createportal( $data, $box ) {
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Create a new portal using existing pages and menus or generate them. Only a small number of fields below are required, skip what you do not need. I recommend that you maintain a test blog that is much like your live site and setup your portal there first.', 'wtgportalmanager' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $wtgportalmanager_settings;
        ?>  

            <table class="form-table">
            <?php        
            $this->FORMS->text_advanced( $box['args']['formid'], 'newprojectname', 'newprojectname', __( 'Portal Name', 'wtgportalmanager' ), '', false, true, true, false, false, array( 'alphanumeric' ) );      
            $this->FORMS->textarea_basic( $box['args']['formid'], 'newportaldescription', 'newportaldescription', __( 'Portal Description', 'wtgportalmanager' ), '', true, 5, 20, array( 'alphanumeric' ) );
            $this->FORMS->text_basic( $box['args']['formid'], 'newportalblogcategory', 'newportalblogcategory', __( 'Blog Category ID', 'wtgportalmanager' ), '', false, 5, 20, array( 'numeric' ) );       
            $this->FORMS->text_basic( $box['args']['formid'], 'newportalforumid', 'newportalforumid', __( 'Forum ID', 'wtgportalmanager' ), '', false, 5, 20, array( 'numeric' ) );

            // get users menus
            $menu_terms_array = get_terms( 'nav_menu', array( 'hide_empty' => false ) ); 
            
            // build array of items for menu (key must be the value that is submitted for storage by form)
            $menus = array( 'createmenu' => __( 'Create New Menu', 'wtgportalmanager' ) );
            foreach( $menu_terms_array as $key => $term ) {
                $menus[ $term->term_id ] = $term->name;
            }
                        
            $this->FORMS->input( $box['args']['formid'], 'menu', 'selectedmenu', 'selectedmenu', __( 'Main Menu', 'wtgportalmanager' ), 'Select Registered Menu', true, '', array( 'itemsarray' => $menus, 'defaultvalue' => 'notselected123', 'defaultitem_name' => __( 'Menu Not Selected', 'wtgportalmanager' ) ) );        
            $this->FORMS->text_basic( $box['args']['formid'], 'sidebarmetakey', 'sidebarmetakey', __( 'Sidebar Metakey', 'wtgportalmanager' ), '', false, 5, 20, array( 'alphanumeric' ) );       
            
            // select existing sidebar or select Create Sidebar
            global $wp_registered_sidebars;
            $sidebars_array = array( 'createsidebar' => __( 'Create Sidebar', 'wtgportalmanager' ) );
            
            foreach( $wp_registered_sidebars as $sidebar ) {
                $sidebars_array[$sidebar['id']] = $sidebar['name'];
            }  

            $this->FORMS->input( 
                $box['args']['formid'], 
                'menu', 
                'selectedsidebar', 
                'selectedsidebar', 
                __( 'Main Sidebar', 'wtgportalmanager' ), 
                'Select Registered Sidebars', 
                true, 
                '', 
                array( 'itemsarray' => $sidebars_array, 
                       'defaultvalue' => 'notselected123', 
                       'defaultitem_name' => __( 'Sidebar Not Selected', 'wtgportalmanager' ) 
                ) 
            );        
                     
            ?>
            </table>
            
            
            <hr>
            
   
            <table class="form-table">
            <?php   
            // main pages (it's not all about the portal menu, integration with other plugins on a per page basis is the focus)
            $this->FORMS->input_subline( __( 'Enter # in the fields below to 
            create pages for your portal or enter existing page ID.', 'wtgportalmanager' ) );
            
            $pf_array = $this->CONFIG->postformats();
            
            foreach ( $pf_array as $format => $meta ) {
                $this->FORMS->text_basic( 
                    $box['args']['formid'], 
                    'newportal' . $format, 
                    'newportal' . $format, 
                    $meta['title'], 
                    '', 
                    false, 
                    5, 
                    20, 
                    array( 'numeric' ) 
                );    
            }   
                        
            // option for applying default content and settings to each page - what that means will depends on the plugins and themes installed
            $current_value = array();
            $this->FORMS->checkboxes_basic( $box['args']['formid'], 'applydefaultcontent', 'applydefaultcontent', __( 'Apply Default Content', 'wtgportalmanager' ), array( true => 'Yes' ), $current_value, false, array( 'boolean' ), false );
            ?>
            </table> 
            
        <?php 
        $this->UI->postbox_content_footer();
    }

    /**
    * List of portals.
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    * 
    * @todo Indicate if a project has a matching portal.
    * @todo Add ability to quickly create portals, probably URL method.
    */
    public function postbox_main_portalslist( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], 
        __( 'List of projects. WTG plugins share project data to make integration easier.
        If you create a project in anotherp plugin it will appear in this list. However
        projects here may not have portals yet.', 'wtgportalmanager' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $wtgportalmanager_settings;
        
        $portals = $this->DB->get_projects();
        
        if( !$portals ) {
            _e( 'No projects have been created.', 'wtgportalmanager' );    
        } else {
        ?>  

            <table class="form-table">
            <?php 
            foreach( $portals as $key => $port ) {
                $this->UI->option_subline( '', $port['projectname'] );
            }       
            ?>
            </table> 
            
        <?php 
        }
        
        //$this->UI->postbox_content_footer();
    }
    
    /**
    * Form for creating a new custom sidebar.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    * 
    * http://devotepress.com/wordpress-coding/how-to-register-sidebars-in-wordpress/#.VK08DCusVp0
    */
    public function postbox_main_createsidebar( $data, $box ) {
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Create a dynamic sidebar then assign it to one or more portals. When viewing pages within the portal the sidebar will show. Your theme must support dynamic_sidebar() functionality offered by the WordPress core.', 'wtgportalmanager' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        ?>  

            <table class="form-table">
            <?php        
            $this->FORMS->text_advanced( $box['args']['formid'], 'newsidebarname', 'newsidebarname', __( 'Sidebar Name', 'wtgportalmanager' ), '', false, true, true, false, false, array( 'alphanumeric' ) );      
            ?>
            </table> 
            
        <?php 
        $this->UI->postbox_content_footer();
    }

    /**
    * List of sidebars.
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_main_sidebarlist( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Basic list of sidebars, this will be improved.', 'wtgportalmanager' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $wp_registered_sidebars;
        ?>  

            <table class="form-table">
            <?php  
            foreach( $wp_registered_sidebars as $sidebar ) {
                echo '<br>' . $sidebar['name'] . '<br>';
            }  
            ?>
            </table> 
            
        <?php 
        $this->UI->postbox_content_footer();
    }

    /**
    * Displays the current active portal (one that can be edited on the plugins other pages) and
    * has menu for activating another portal.
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_main_currentportal( $data, $box ) { 
        // get users currently active portal
        $active_project_id = get_user_meta( get_current_user_id(), 'wtgportalmanager_activeproject', true );
        
        // construct postbox header message
        if( !$active_project_id ) {
            $message =  __( 'Portal with ID ' . $active_project_id . ' is currently active. You can activate another portal using this form if you do not wish to edit the current portal.', '' );
        } else {
            $message =  __( 'You have not activated a portal for edited or have not created any portals yet. Use this form to active a portal for edited once your first portal has been created.', '' );
        }
           
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], $message, false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $wtgportalmanager_settings;
        ?>  
        
            <table class="form-table">
            <?php 
            $portals = $this->DB->get_projects();
            
            // build portal array - portal ID as key
            $portals_array = array();
            foreach( $portals as $key => $port ) {
                $portals_array[ $port['project_id'] ] = $port['projectname'];    
            }
            
            $this->FORMS->menu_basic( $box['args']['formid'], 'portalactivation', 'portalactivation', __( 'Select Portal', 'wtgportalmanager' ), $portals_array, true, '' );
    
            ?>
            </table> 
            
        <?php 
        $this->UI->postbox_content_footer( 'Activate Selected Portal' );
    }    

    /**
    * Enter Twitter oAuth credentials for acting as a default account. This
    * would be for treating the domain as an application. However this system
    * will be added to multiple plugins that offer difference services. It 
    * means a default for each plugin and so each plugin can be setup as an app. 
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    * 
    * @todo The Twitter API radio on this form did not show a value despite the setting being in global switches.
    */
    public function postbox_main_setupdefaulttwitter( $data, $box ) { 
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Activate API is required but the fields for a default set of app keys and tokens are not. You may enter an app account on a per portal basis.', 'wtgportalmanager' ), false );        
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
        ?>  

            <table class="form-table">
            
            <?php 
            $current_twitter = null;         
            if( isset( $wtgportalmanager_settings['api']['twitter']['active'] ) ){ $current_twitter = $wtgportalmanager_settings['api']['twitter']['active']; }
            $this->FORMS->switch_basic( $box['args']['formid'], 'twitterapiswitch', 'twitterapiswitch', __( 'Twitter API Switch', 'wtgportalmanager' ), 'disabled', $current_twitter, false ); 
            ?>
                                          
            <?php 
            $twitter_consumer_key = '';
            if( isset( $wtgportalmanager_settings['api']['twitter']['apps']['default']['consumer_key'] ) ) { $twitter_consumer_key = $wtgportalmanager_settings['api']['twitter']['apps']['default']['consumer_key']; }
            $this->FORMS->text_basic( $box['args']['formid'], 'consumer_key', 'consumer_key', 'Consumer Key (API Key)', $twitter_consumer_key, true, array( 'alphanumeric' ) );
            
            $twitter_consumer_secret = '';
            if( isset( $wtgportalmanager_settings['api']['twitter']['apps']['default']['consumer_secret'] ) ) { $twitter_consumer_secret = $wtgportalmanager_settings['api']['twitter']['apps']['default']['consumer_secret']; }            
            $this->FORMS->text_basic( $box['args']['formid'], 'consumer_secret', 'consumer_secret', 'Consumer Secret (API Secret)', $twitter_consumer_secret, true, array( 'alphanumeric' ) );
            
            $twitter_access_token = '';
            if( isset( $wtgportalmanager_settings['api']['twitter']['apps']['default']['access_token'] ) ) { $twitter_access_token = $wtgportalmanager_settings['api']['twitter']['apps']['default']['access_token']; }            
            $this->FORMS->text_basic( $box['args']['formid'], 'access_token', 'access_token', 'Your Access Token', $twitter_access_token, true, array( 'alphanumeric' ) );
            
            $twitter_token_secret = '';
            if( isset( $wtgportalmanager_settings['api']['twitter']['apps']['default']['token_secret'] ) ) { $twitter_token_secret = $wtgportalmanager_settings['api']['twitter']['apps']['default']['token_secret']; }            
            $this->FORMS->text_basic( $box['args']['formid'], 'access_token_secret', 'access_token_secret', 'Access Token Secret', $twitter_token_secret, true, array( 'alphanumeric' ) );
            
            $twitter_screenname = '';
            if( isset( $wtgportalmanager_settings['api']['twitter']['apps']['default']['screenname'] ) ) { $twitter_screenname = $wtgportalmanager_settings['api']['twitter']['apps']['default']['screenname']; }            
            $this->FORMS->text_basic( $box['args']['formid'], 'screenname', 'screenname', 'Twitter Feed Screen Name', $twitter_screenname, false, array( 'alphanumeric' ) );
            ?>

            </table>
            
        <?php 
        $this->UI->postbox_content_footer();
                    
        echo '<hr />';
        
        echo '<h3>Debug Information</h3>';
        $last_error = __( 'no error has been stored.', 'wtgportalmanager' );
        if( isset( $wtgportalmanager_settings['api']['twitter']['apps']['deafault']['error'] ) ) 
        {
            $last_error = $wtgportalmanager_settings['api']['twitter']['apps']['deafault']['error'];   
        }
        else
        {
            $last_error = __( 'The current portals API credentials have never led to an error.', 'wtgportalmanager' );
        } 
        
        echo '<p>Last Twitter API Error: ' . $last_error . '</p>';                         
    }    
    
    /**
    * Bridge to a phpBB forum (WTG provides service to customize for other forum types) 
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function postbox_main_configureforum( $data, $box ) { 
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Use this form if your domain is home to a forum and you want your portals to display forum activity. This plugin is phpBB 3.1 ready - support for other forums is planned. Using this form will automatically store some of the configuration data of your forum in WordPress to make a proper connection between the two platforms.', 'wtgportalmanager' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $wtgportalmanager_settings;
        ?>  

            <table class="form-table">
            
            <?php 
            $current_forum_switch = null;
            if( isset( $wtgportalmanager_settings['forumconfig']['status'] ) ) { $current_forum_switch = $wtgportalmanager_settings['forumconfig']['status']; }
            $this->FORMS->switch_basic( $box['args']['formid'], 'globalforumswitch', 'globalforumswitch', __( 'Global Forum Switch', 'wtgportalmanager' ), 'disabled', $current_forum_switch, false ); 
            ?>
                                          
            <?php  
            $current_forum_path = ABSPATH;
            if( isset( $wtgportalmanager_settings['forumconfig']['path'] ) ) { $current_forum_path = $wtgportalmanager_settings['forumconfig']['path']; }
            $this->FORMS->text_basic( $box['args']['formid'], 'forumpath', 'forumpath', __( 'Forums Path', 'wtgportalmanager' ), $current_forum_path, true, array() );
            ?>

            </table>
            
        <?php 
        $this->UI->postbox_content_footer();                      
    } 
    
    /**
    * Activate developers and other primary developer settings.
    * 
    * @author Ryan Bayne
    * @package WebTechGlobal WordPress Plugins
    * @since 0.0.3
    * @version 1.0
    */
    public function postbox_main_developertoolssetup( $data, $box ) { 
        global $wp_roles;
        
        $intro = __( 'WebTechGlobal plugins have built in tools to
        aid development. The tools are better understood by WTG staff
        but any good developer can learn to use them.
        To get started you must first add the Developer role and assign
        a user as a developer. The Developer role includes all the 
        capabilities of an Administrator and some custom capabilities
        built into this plugin. Submit this form to initiate access
        to WTG Developer Tools for WordPress.', 'multitool' );
        
        $this->UI->postbox_content_header( 
            $box['title'], 
            $box['args']['formid'],
            $intro, 
            false 
        );        
        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        $caps = $this->WTGPORTALMANAGER->capabilities();
        
        // build array of WP roles
        $custom_roles_array = array();
        foreach( $caps as $c => $bool ) {
            $custom_roles_array[ $c ] = $c;    
        }        
        ?>
                
        <table class="form-table">

        <?php 
        // option to install developer role
        $developer_role_status = __( 'Not Installed', 'multitool' );
        foreach( $wp_roles->roles as $role_name => $role_array ) {
            if( $role_name == 'developer' ) {
                $developer_role_status = __( 'Installed', 'multitool' );    
            }            
        }

        $this->FORMS->input_subline(
            $developer_role_status,
            __( 'Developer Role Status', 'multitool')     
        ); 
        
        if( $developer_role_status == 'Not Installed' ) {
            $this->FORMS->checkboxesgrouped_basic( 
                $box['args']['formid'], 
                'addrolecapabilities', 
                'addrolecapabilities', 
                __( 'Select Capabilities', 'multitool' ), 
                $custom_roles_array, 
                array(), 
                true, 
                array()
            );
        }               
        ?>

        </table> 
        
        <?php   
        $this->UI->postbox_content_footer();
    }      
}?>