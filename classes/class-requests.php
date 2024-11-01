<?php
/** 
* Class for handling $_POST and $_GET requests
* 
* The class is called in the process_admin_POST_GET() method found in the WTGPORTALMANAGER class. 
* The process_admin_POST_GET() method is hooked at admin_init. It means requests are handled in the admin
* head, globals can be updated and pages will show the most recent data. Nonce security is performed
* within process_admin_POST_GET() then the require method for processing the request is used.
* 
* Methods in this class MUST be named within the form or link itself, basically a unique identifier for the form.
* i.e. the Section Switches settings have a form name of "sectionswitches" and so the method in this class used to
* save submission of the "sectionswitches" form is named "sectionswitches".
* 
* process_admin_POST_GET() uses eval() to call class + method 
* 
* @package WTG Portal Manager
* @author Ryan Bayne   
* @since 0.0.1
*/

// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
* Class processes form submissions, the class is only loaded once nonce and other security checked
* 
* @author Ryan R. Bayne
* @package WTG Portal Manager
* @since 0.0.1
* @version 1.0.2
*/
class WTGPORTALMANAGER_Requests {  
    public function __construct() {
        global $wtgportalmanager_settings;
    	
    	/*   Commented March 2016 as moving away from __construct use.
        // create class objects
        $this->WTGPORTALMANAGER = WTGPORTALMANAGER::load_class( 'WTGPORTALMANAGER', 'class-wtgportalmanager.php', 'classes' ); # plugin specific functions
        $this->CONFIG = $this->WTGPORTALMANAGER->load_class( "WTGPORTALMANAGER_Configuration", "class-configuration.php", 'classes','pluginmenu' );                          
        $this->UI = $this->CONFIG->load_class( 'WTGPORTALMANAGER_ADMINUI', 'class-adminui.php', 'classes' ); # interface, mainly notices
        $this->DB = $this->CONFIG->load_class( 'WTGPORTALMANAGER_DB', 'class-wpdb.php', 'classes' ); # database interaction
        $this->PHP = $this->CONFIG->load_class( 'WTGPORTALMANAGER_PHP', 'class-phplibrary.php', 'classes' ); # php library by Ryan R. Bayne
        $this->Files = $this->CONFIG->load_class( 'WTGPORTALMANAGER_Files', 'class-files.php', 'classes' );
        $this->Forms = $this->CONFIG->load_class( 'WTGPORTALMANAGER_Formbuilder', 'class-forms.php', 'classes' );
        $this->WPCore = $this->CONFIG->load_class( 'WTGPORTALMANAGER_WPCore', 'class-wpcore.php', 'classes' );
        $this->PHPBB = $this->CONFIG->load_class( "WTGPORTALMANAGER_PHPBB", "class-phpbb.php", 'classes','pluginmenu' );   */               
    }
    
    public function init() {

        $this->CONFIG = new WTGPORTALMANAGER_Configuration();
        $this->WTGPORTALMANAGER = $this->CONFIG->load_class( 'WTGPORTALMANAGER', 'class-wtgportalmanager.php', 'classes' ); # plugin specific functions        
        $this->UI = $this->CONFIG->load_class( 'WTGPORTALMANAGER_ADMINUI', 'class-adminui.php', 'classes' ); # interface, mainly notices
        $this->DB = $this->CONFIG->load_class( 'WTGPORTALMANAGER_DB', 'class-wpdb.php', 'classes' ); # database interaction
        $this->PHP = $this->CONFIG->load_class( 'WTGPORTALMANAGER_PHP', 'class-phplibrary.php', 'classes' ); # php library by Ryan R. Bayne
        $this->Files = $this->CONFIG->load_class( 'WTGPORTALMANAGER_Files', 'class-files.php', 'classes' );
        $this->Forms = $this->CONFIG->load_class( 'WTGPORTALMANAGER_Formbuilder', 'class-forms.php', 'classes' );
        $this->WPCore = $this->CONFIG->load_class( 'WTGPORTALMANAGER_WPCore', 'class-wpcore.php', 'classes' );
        $this->PHPBB = $this->CONFIG->load_class( "WTGPORTALMANAGER_PHPBB", "class-phpbb.php", 'classes','pluginmenu' );
              
    }
    /**
    * Processes security for $_POST and $_GET requests,
    * then calls another function to complete the specific request made.
    * 
    * This function is called by process_admin_POST_GET() which is hooked by admin_init.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @version 1.2
    */
    public function process_admin_request( $method, $function ) { 
          
        $this->PHP->var_dump( $_POST, '<h1>$_POST</h1>' );           
        $this->PHP->var_dump( $_GET, '<h1>$_GET</h1>' );    
                              
        // $_POST security
        if( $method == 'post' || $method == 'POST' || $method == '$_POST' ) { 
                             
            // check_admin_referer() wp_die()'s if security fails so if we arrive here WordPress security has been passed
            // now we validate individual values against their pre-registered validation method
            // some generic notices are displayed - this system makes development faster
            $post_result = true;
            $post_result = $this->Forms->apply_form_security();// ensures $_POST['wtgportalmanager_form_formid'] is set, so we can use it after this line
            
            // apply my own level of security per individual input
            if( $post_result ){ $post_result = $this->Forms->apply_input_security(); }// detect hacking of individual inputs i.e. disabled inputs being enabled 
            
            // validate users values
            if( $post_result ){ $post_result = $this->Forms->apply_input_validation( $_POST['wtgportalmanager_form_formid'] ); }// values (string,numeric,mixed) validation

            // cleanup to reduce registered data
            $this->Forms->deregister_form( $_POST['wtgportalmanager_form_formid'] );
                    
            // if $overall_result includes a single failure then there is no need to call the final function
            if( $post_result === false ) {        
                return false;
            }
        }
   
        // handle a situation where the submitted form requests a function that does not exist
        if( !method_exists( $this, $function ) ){
            
            wp_die( 
                sprintf( 
                    __( "The method for processing your request was not 
                    found. This can usually be resolved quickly. Please report method %s 
                    does not exist. <a href='https://www.youtube.com/watch?v=vAImGQJdO_k' 
                    target='_blank'>Watch a video</a> explaining this problem.", 
                    'wtgportalmanager' ), 
                    $function
                ) 
            ); 
            return false;// should not be required with wp_die() but it helps to add clarity when browsing code and is a precaution.   
        }
        
        // all security passed - call the processing function
        if( isset( $function) && is_string( $function ) ) {
            eval( 'self::' . $function .'();' );
        }          
    }  

    /**
    * form processing function
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */    
    public function request_success( $form_title, $more_info = '' ){  
        $this->UI->create_notice( "Your submission for $form_title was successful. " . $more_info, 'success', 'Small', "$form_title Updated");          
    } 

    /**
    * form processing function
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */    
    public function request_failed( $form_title, $reason = '' ){
        $this->UI->n_depreciated( $form_title . ' Unchanged', "Your settings for $form_title were not changed. " . $reason, 'error', 'Small' );    
    }

    /**
    * form processing function
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */    
    public function logsettings() {
        global $wtgportalmanager_settings;
        $wtgportalmanager_settings['globalsettings']['uselog'] = $_POST['wtgportalmanager_radiogroup_logstatus'];
        $wtgportalmanager_settings['globalsettings']['loglimit'] = $_POST['wtgportalmanager_loglimit'];
                                                   
        ##################################################
        #           LOG SEARCH CRITERIA                  #
        ##################################################
        
        // first unset all criteria
        if( isset( $wtgportalmanager_settings['logsettings']['logscreen'] ) ){
            unset( $wtgportalmanager_settings['logsettings']['logscreen'] );
        }
                                                           
        // if a column is set in the array, it indicates that it is to be displayed, we unset those not to be set, we dont set them to false
        if( isset( $_POST['wtgportalmanager_logfields'] ) ){
            foreach( $_POST['wtgportalmanager_logfields'] as $column){
                $wtgportalmanager_settings['logsettings']['logscreen']['displayedcolumns'][$column] = true;                   
            }
        }
                                                                                 
        // outcome criteria
        if( isset( $_POST['wtgportalmanager_log_outcome'] ) ){    
            foreach( $_POST['wtgportalmanager_log_outcome'] as $outcomecriteria){
                $wtgportalmanager_settings['logsettings']['logscreen']['outcomecriteria'][$outcomecriteria] = true;                   
            }            
        } 
        
        // type criteria
        if( isset( $_POST['wtgportalmanager_log_type'] ) ){
            foreach( $_POST['wtgportalmanager_log_type'] as $typecriteria){
                $wtgportalmanager_settings['logsettings']['logscreen']['typecriteria'][$typecriteria] = true;                   
            }            
        }         

        // category criteria
        if( isset( $_POST['wtgportalmanager_log_category'] ) ){
            foreach( $_POST['wtgportalmanager_log_category'] as $categorycriteria){
                $wtgportalmanager_settings['logsettings']['logscreen']['categorycriteria'][$categorycriteria] = true;                   
            }            
        }         

        // priority criteria
        if( isset( $_POST['wtgportalmanager_log_priority'] ) ){
            foreach( $_POST['wtgportalmanager_log_priority'] as $prioritycriteria){
                $wtgportalmanager_settings['logsettings']['logscreen']['prioritycriteria'][$prioritycriteria] = true;                   
            }            
        }         

        ############################################################
        #         SAVE CUSTOM SEARCH CRITERIA SINGLE VALUES        #
        ############################################################
        // page
        if( isset( $_POST['wtgportalmanager_pluginpages_logsearch'] ) && $_POST['wtgportalmanager_pluginpages_logsearch'] != 'notselected' ){
            $wtgportalmanager_settings['logsettings']['logscreen']['page'] = $_POST['wtgportalmanager_pluginpages_logsearch'];
        }   
        // action
        if( isset( $_POST['csv2pos_logactions_logsearch'] ) && $_POST['csv2pos_logactions_logsearch'] != 'notselected' ){
            $wtgportalmanager_settings['logsettings']['logscreen']['action'] = $_POST['csv2pos_logactions_logsearch'];
        }   
        // screen
        if( isset( $_POST['wtgportalmanager_pluginscreens_logsearch'] ) && $_POST['wtgportalmanager_pluginscreens_logsearch'] != 'notselected' ){
            $wtgportalmanager_settings['logsettings']['logscreen']['screen'] = $_POST['wtgportalmanager_pluginscreens_logsearch'];
        }  
        // line
        if( isset( $_POST['wtgportalmanager_logcriteria_phpline'] ) ){
            $wtgportalmanager_settings['logsettings']['logscreen']['line'] = $_POST['wtgportalmanager_logcriteria_phpline'];
        }  
        // file
        if( isset( $_POST['wtgportalmanager_logcriteria_phpfile'] ) ){
            $wtgportalmanager_settings['logsettings']['logscreen']['file'] = $_POST['wtgportalmanager_logcriteria_phpfile'];
        }          
        // function
        if( isset( $_POST['wtgportalmanager_logcriteria_phpfunction'] ) ){
            $wtgportalmanager_settings['logsettings']['logscreen']['function'] = $_POST['wtgportalmanager_logcriteria_phpfunction'];
        }
        // panel name
        if( isset( $_POST['wtgportalmanager_logcriteria_panelname'] ) ){
            $wtgportalmanager_settings['logsettings']['logscreen']['panelname'] = $_POST['wtgportalmanager_logcriteria_panelname'];
        }
        // IP address
        if( isset( $_POST['wtgportalmanager_logcriteria_ipaddress'] ) ){
            $wtgportalmanager_settings['logsettings']['logscreen']['ipaddress'] = $_POST['wtgportalmanager_logcriteria_ipaddress'];
        }
        // user id
        if( isset( $_POST['wtgportalmanager_logcriteria_userid'] ) ){
            $wtgportalmanager_settings['logsettings']['logscreen']['userid'] = $_POST['wtgportalmanager_logcriteria_userid'];
        }
        
        $this->WTGPORTALMANAGER->update_settings( $wtgportalmanager_settings );
        $this->UI->n_postresult_depreciated( 'success', __( 'Log Settings Saved', 'wtgportalmanager' ), __( 'It may take sometime for new log entries to be created depending on your websites activity.', 'wtgportalmanager' ) );  
    }  
    
    /**
    * form processing function
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */       
    public function beginpluginupdate() {
        $this->Updates = $this->WTGPORTALMANAGER->load_class( 'WTGPORTALMANAGER_Formbuilder', 'class-forms.php', 'classes' );
        
        // check if an update method exists, else the plugin needs to do very little
        eval( '$method_exists = method_exists ( $this->Updates , "patch_' . $_POST['wtgportalmanager_plugin_update_now'] .'" );' );

        if( $method_exists){
            // perform update by calling the request version update procedure
            eval( '$update_result_array = $this->Updates->patch_' . $_POST['wtgportalmanager_plugin_update_now'] .'( "update");' );       
        }else{
            // default result to true
            $update_result_array['failed'] = false;
        } 
      
        if( $update_result_array['failed'] == true){ 
                  
            $this->UI->create_notice( __( 'The update procedure failed, the reason should be displayed below. Please try again unless the notice below indicates not to. If a second attempt fails, please seek support.', 'wtgportalmanager' ), 'error', 'Small', __( 'Update Failed', 'wtgportalmanager' ) );    
            $this->UI->create_notice( $update_result_array['failedreason'], 'info', 'Small', 'Update Failed Reason' );
        
        }else{  
            
            // storing the current file version will prevent user coming back to the update screen  
            update_option( 'wtgportalmanager_installedversion', WTGPORTALMANAGER_VERSION);

            $this->UI->create_notice( __( 'Good news, the update procedure was complete. If you do not see any errors or any notices indicating a problem was detected it means the procedure worked. Please ensure any new changes suit your needs.', 'wtgportalmanager' ), 'success', 'Small', __( 'Update Complete', 'wtgportalmanager' ) );
            
            // do a redirect so that the plugins menu is reloaded
            wp_redirect( get_bloginfo( 'url' ) . '/wp-admin/admin.php?page=wtgportalmanager' );
            exit;                
        }
    }
    
    /**
    * Save drip feed limits  
    */
    public function schedulerestrictions() {
        $wtgportalmanager_schedule_array = $this->WTGPORTALMANAGER->get_option_schedule_array();
        
        // if any required values are not in $_POST set them to zero
        if(!isset( $_POST['day'] ) ){
            $wtgportalmanager_schedule_array['limits']['day'] = 0;        
        }else{
            $wtgportalmanager_schedule_array['limits']['day'] = $_POST['day'];            
        }
        
        if(!isset( $_POST['hour'] ) ){
            $wtgportalmanager_schedule_array['limits']['hour'] = 0;
        }else{
            $wtgportalmanager_schedule_array['limits']['hour'] = $_POST['hour'];            
        }
        
        if(!isset( $_POST['session'] ) ){
            $wtgportalmanager_schedule_array['limits']['session'] = 0;
        }else{
            $wtgportalmanager_schedule_array['limits']['session'] = $_POST['session'];            
        }
                                 
        // ensure $wtgportalmanager_schedule_array is an array, it may be boolean false if schedule has never been set
        if( isset( $wtgportalmanager_schedule_array ) && is_array( $wtgportalmanager_schedule_array ) ){
            
            // if times array exists, unset the [times] array
            if( isset( $wtgportalmanager_schedule_array['days'] ) ){
                unset( $wtgportalmanager_schedule_array['days'] );    
            }
            
            // if hours array exists, unset the [hours] array
            if( isset( $wtgportalmanager_schedule_array['hours'] ) ){
                unset( $wtgportalmanager_schedule_array['hours'] );    
            }
            
        }else{
            // $schedule_array value is not array, this is first time it is being set
            $wtgportalmanager_schedule_array = array();
        }
        
        // loop through all days and set each one to true or false
        if( isset( $_POST['wtgportalmanager_scheduleday_list'] ) ){
            foreach( $_POST['wtgportalmanager_scheduleday_list'] as $key => $submitted_day ){
                $wtgportalmanager_schedule_array['days'][$submitted_day] = true;        
            }  
        } 
        
        // loop through all hours and add each one to the array, any not in array will not be permitted                              
        if( isset( $_POST['wtgportalmanager_schedulehour_list'] ) ){
            foreach( $_POST['wtgportalmanager_schedulehour_list'] as $key => $submitted_hour){
                $wtgportalmanager_schedule_array['hours'][$submitted_hour] = true;        
            }           
        }    

        if( isset( $_POST['deleteuserswaiting'] ) )
        {
            $wtgportalmanager_schedule_array['eventtypes']['deleteuserswaiting']['switch'] = 'enabled';                
        }
        
        if( isset( $_POST['eventsendemails'] ) )
        {
            $wtgportalmanager_schedule_array['eventtypes']['sendemails']['switch'] = 'enabled';    
        }        
  
        $this->WTGPORTALMANAGER->update_option_schedule_array( $wtgportalmanager_schedule_array );
        $this->UI->notice_depreciated( __( 'Schedule settings have been saved.', 'wtgportalmanager' ), 'success', 'Large', __( 'Schedule Times Saved', 'wtgportalmanager' ) );   
    } 
    
    /**
    * form processing function
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */       
    public function logsearchoptions() {
        $this->UI->n_postresult_depreciated( 'success', __( 'Log Search Settings Saved', 'wtgportalmanager' ), __( 'Your selections have an instant effect. Please browse the Log screen for the results of your new search.', 'wtgportalmanager' ) );                   
    }
 
    /**
    * form processing function
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */        
    public function defaultcontenttemplate () {        
        $this->UI->create_notice( __( 'Your default content template has been saved. This is a basic template, other advanced options may be available by activating the WTG Portal Manager Templates custom post type (pro edition only) for managing multiple template designs.' ), 'success', 'Small', __( 'Default Content Template Updated' ) );         
    }
        
    /**
    * form processing function
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */       
    public function reinstalldatabasetables() {
        $installation = new WTGPORTALMANAGER_Install();
        $installation->reinstalldatabasetables();
        $this->UI->create_notice( 'All tables were re-installed. Please double check the database status list to
        ensure this is correct before using the plugin.', 'success', 'Small', 'Tables Re-Installed' );
    }
     
    /**
    * Enable and disable systems.
    * 
    * @author Ryan Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.1
    */          
    public function globalswitches() {
        global $wtgportalmanager_settings;
        
        $wtgportalmanager_settings['noticesettings']['wpcorestyle'] = $_POST['uinoticestyle'];        
        $wtgportalmanager_settings['posttypes']['wtgflags']['status'] = $_POST['flagsystemstatus'];
        $wtgportalmanager_settings['widgetsettings']['dashboardwidgetsswitch'] = $_POST['dashboardwidgetsswitch'];
        $wtgportalmanager_settings['api']['twitter']['active'] = $_POST['twitterapiswitch'];
                 
        $this->WTGPORTALMANAGER->update_settings( $wtgportalmanager_settings ); 
        
        $this->UI->create_notice( __( 'Global switches have been updated. These switches can initiate the use of 
        advanced systems. Please monitor your blog and ensure the plugin operates as you expected it to. If
        anything does not appear to work in the way you require please let WebTechGlobal know.' ),
        'success', 'Small', __( 'Global Switches Updated' ) );       
    } 
       
    /**
    * save capability settings for plugins pages
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function pagecapabilitysettings() {
        global $wtgportalmanager_menu_array;
        
        // get the capabilities array from WP core
        $capabilities_array = $this->WPCore->capabilities();

        // get stored capability settings 
        $saved_capability_array = get_option( 'wtgportalmanager_capabilities' );

        // to ensure no extra values are stored (more menus added to source) loop through page array
        foreach( $wtgportalmanager_menu_array as $key => $page_array ) {
            
            // ensure $_POST value is also in the capabilities array to ensure user has not hacked form, adding their own capabilities
            if( isset( $_POST['pagecap' . $page_array['name'] ] ) && in_array( $_POST['pagecap' . $page_array['name'] ], $capabilities_array ) ) {
                $saved_capability_array['pagecaps'][ $page_array['name'] ] = $_POST['pagecap' . $page_array['name'] ];
            }
                
        }
          
        update_option( 'wtgportalmanager_capabilities', $saved_capability_array );
         
        $this->UI->create_notice( __( 'Capabilities for this plugins pages have been stored. Due to this being security related I recommend testing before you logout. Ensure that each role only has access to the plugin pages you intend.' ), 'success', 'Small', __( 'Page Capabilities Updated' ) );        
    }
    
    /**
    * Saves the plugins global dashboard widget settings i.e. which to display, what to display, which roles to allow access
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function dashboardwidgetsettings() {
        global $wtgportalmanager_settings,$wtgportalmanager_menu_array;
  
        foreach( $wtgportalmanager_menu_array as $key => $section_array ) {

            if( isset( $_POST[ $section_array['name'] . 'dashboardwidgetsswitch' ] ) ) {
                $wtgportalmanager_settings['widgetsettings'][ $section_array['name'] . 'dashboardwidgetsswitch'] = $_POST[ $section_array['name'] . 'dashboardwidgetsswitch' ];    
            }
            
            if( isset( $_POST[ $section_array['name'] . 'widgetscapability' ] ) ) {
                $wtgportalmanager_settings['widgetsettings'][ $section_array['name'] . 'widgetscapability'] = $_POST[ $section_array['name'] . 'widgetscapability' ];    
            }

        }

        $this->WTGPORTALMANAGER->update_settings( $wtgportalmanager_settings );    
        $this->UI->create_notice( __( 'Your dashboard widget settings have been saved. Please check your dashboard to ensure it is configured as required per role.', 'wtgportalmanager' ), 'success', 'Small', __( 'Settings Saved', 'wtgportalmanager' ) );         
    }

    /**
    * Insert a new portal to the portals database table. 
    * 
    * A portal name should match project names when possible to make integration
    * with WTG project management plugin.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @version 1.2
    */
    public function createportal() {
        $frontpage_page_id = false;
        $menu_id = false;
        $sidebar_id = false;
    
        if( $_POST['selectedmenu'] == 'createmenu' ) {
            $menu_id = wp_create_nav_menu( $_POST['newprojectname'] );
        } else if( is_numeric( $_POST['selectedmenu'] ) ) {
            $menu_id = $_POST['selectedmenu'];    
        }

        // insert the portal
        $new_project_id = $this->DB->insertproject( 
            $_POST['newprojectname'], 
            $_POST['newportaldescription'], 
            $menu_id 
        );  
        
        if( isset( $_POST['newportalblogcategory'] ) ){
            $this->DB->add_project_meta( $new_project_id, 'maincategory', $_POST['newportalblogcategory'], true );    
        }
        
        if( isset( $opt['newportalforumid'] ) ){
            $this->DB->add_project_meta( $new_project_id, 'primary_forum_id', $_POST['newportalforumid'], true );    
        }
                         
        ##########################################################
        #                                                        #
        #                     SETUP PAGES                        #
        #                                                        #
        ##########################################################
        $pf_array = $this->CONFIG->postformats();
        
        $page_id = false;
        foreach( $pf_array as $format => $format_meta ) {
            
            // skip default page if user entered nothing for it
            if( empty( $_POST['newportal' . $format ] ) ) {
                continue;    
            }
            
            // has user entered value for $format which will create or link a page
            if( isset( $_POST['newportal' . $format ] ) ) {
                
                if( $_POST['newportal' . $format ] == '#' ) 
                {             
                    $applydefault = false;
                    if( isset( $_POST['applydefaultcontent0'] ) ) {
                        $applydefault = true;
                    }
                    
                    // set parent page ID
                    if( $format !== 'frontpage' && $frontpage_page_id !== false ) { 
                        $frontpage_page_id = $new_page_id;
                    }
                                        
                    // create a new page (wp_insert_post() is used)
                    $new_page_id = $this->WTGPORTALMANAGER->create_portal_page( 
                        $new_project_id, 
                        $_POST['newprojectname'], 
                        $format,// portal manager uses formats as a purpose also 
                        $applydefault, 
                        $frontpage_page_id 
                    ); 
    
                } 
                elseif( is_numeric( $_POST['newportal' . $format ] ) ) 
                { 
                    // handle the addition of an existing page
                    $id = $_POST['newportal' . $format ];
                    
                    // create relationship between project and page
                    $this->DB->add_project_meta( $new_project_id, 'page', $id, true );  
                    
                    // update project meta table with pages purpose within the portal 
                    $this->WTGPORTALMANAGER->update_page_purpose( $new_project_id, $id, $format );
                                                                  
                    // set front page in situation where it already existed 
                    if( $format == 'frontpage' ) {
                        $frontpage_page_id = $new_page_id; 
                    }
                }
            }
            
            // create new sidebar or get submitted value
            if( $_POST['selectedsidebar'] == 'createsidebar' ) {
                $sidebar_id = $this->WTGPORTALMANAGER->insert_sidebar( $_POST['newprojectname'] );
            } else { 
                $sidebar_id = $_POST['selectedsidebar'];# validation is done by class-forms.php ensuring this is a numeric value
            }
                                                                      
            // if sidebar metakey provided add the giving sidebar ID or create a sidebar (do not confuse with a sidebar location)
            // for example the Pindol theme used by WebTechGlobal uses meta-key "mfn-post-sidebar" and the value is the sidebar ID.
            // that results in a specific sidebar being displayed when viewing the post or page - this is an approach we use to maintain
            // the feeling of being in a portal.                                              
            if( !empty( $_POST['sidebarmetakey'] ) && $sidebar_id ) {
                update_post_meta( $new_page_id, $_POST['sidebarmetakey'], $sidebar_id );        
            }
            
            // add page to menu
            if( $menu_id && $new_page_id ) {

                $menu_item_data = array(      
                    //'menu-item-db-id' => $menu_item_db_id,   I think this is for updating an existing menu
                    'menu-item-object-id' => $new_page_id,
                    'menu-item-object' => 'page',
                    'menu-item-parent-id' => 0,
                    'menu-item-position' => 0,
                    'menu-item-type' => 'post_type',
                    'menu-item-title' => '',
                    'menu-item-url' => '',
                    'menu-item-description' => '',
                    'menu-item-attr-title' => '',
                    'menu-item-target' => '',
                    'menu-item-classes' => '',
                    'menu-item-xfn' => '',
                    'menu-item-status' => 'publish'
                );
                        
                wp_update_nav_menu_item( $menu_id, 0, $menu_item_data );    
            }
            
            unset( $page_id );
        }
                
        // set the new project to active (applies to current user only)
        $this->WTGPORTALMANAGER->activate_project( $new_project_id, get_current_user_id() );
        
        $this->UI->create_notice( __( "The new portal ID is $new_project_id and 
        you can begin working on the portal now.", 'wtgportalmanager' ), 
        'success', 'Small', __( 'Portal Created', 'wtgportalmanager' ) );                                              
    }
    
    /**
    * Activates portal on admin side for editing - only one partal can be active for editing at a time
    * for the current user.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function currentportal() {
        $this->WTGPORTALMANAGER->activate_project( $_POST['portalactivation'], get_current_user_id() ); 
        $this->UI->create_notice( __( "You activated portal with ID " . $_POST['portalactivation'] . " and when using this plugins other views that is the portal you will be viewing/editing.", 'wtgportalmanager' ), 'success', 'Small', __( 'Portal Activated', 'wtgportalmanager' ) );                                              
    }
    
    /**
    * Create a new page for the current portal.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.1
    */
    public function createnewportalpage() {
        $project_id = $this->DB->get_active_project_id();
        
        // Convert users page purpose selection to readable format.
        $readable_purpose = '';
        foreach( $this->CONFIG->postformats() as $format => $meta ) {
            
            if( $format == $_POST['pagepurpose'] ) {
                $readable_purpose = $meta['title'];  
            }
  
        }        
    
        // Get current portals name to use as the title.
        $project_name = $this->DB->get_project_name( $project_id );
        
        // Build Page Title
        $title = $project_name . ' ' .  $readable_purpose;
         
        // Create post object.                                     
        $my_post = array(
          'post_title'    => wp_strip_all_tags( $title ),
          'post_content'  => __( 'Under Construction', 'wtgportalmanager' ),
          'post_status'   => 'publish',
          'post_author'   => get_current_user_id(),
          'post_type'     => 'page'
        );
         
        // Insert the post into the database.
        $post_id = wp_insert_post( $my_post );
        
        if( !$post_id || !is_numeric( $post_id ) ) {
	        // Failure Output
	        $this->UI->create_notice( __( "WordPress could not create a new post using wp_insert_post(). Please
	        try again then report this issue to WebTechGlobal. Please note that wp_insert_post() is also used to
	        create pages in WP.", 'wtgportalmanager' ), 
	            'success', 
	            'Small', 
	            __( 'WP Function Failed', 'wtgportalmanager' ) 
	        );	
	        return false;		
        }            
          
        // Join Page To Portal
        $this->WTGPORTALMANAGER->create_page_relationship( $project_id, $post_id );
        
        // Pages Purpose (Format) Meta
        $this->DB->update_page_purpose( $project_id, $post_id, $_POST['pagepurpose'] );
        
        // Final Output - Create link to the new page in admin.
        $link = get_edit_post_link( $post_id );
        $link = "<a href='$link' title='Edit post' target='_blank'>clicking here</a>";
        $message = sprintf( __( "A new page has been added to your current active portal. You can
        now edit your post by %s", 'wtgportalmanager' ), $link );
        $this->UI->create_notice( $message, 
            'success', 
            'Small', 
            __( 'Page Relationship Created', 'wtgportalmanager' ) 
        );                                                       
    }
    
    /**
    * Create relationship between current portal and page (not post)
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function addpagerelationship() {
        $project_id = $this->DB->get_active_project_id();
        $this->WTGPORTALMANAGER->create_page_relationship( $project_id, $_POST['addpageid'] );
        $this->DB->update_page_purpose( $project_id, $_POST['addpageid'], $_POST['pagepurpose'] );
        $this->UI->create_notice( 
        	__( "Your page has been added to your current active portal.", 'wtgportalmanager' ), 
        	'success', 
        	'Small', 
        	__( 'Page Relationship Created', 'wtgportalmanager' ) 
        );                                                       
    }

    /**
    * Handle requests made using the form that lists all
    * related pages i.e. delete the relationship.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function listrelatedpages() {
    
    }

    /**
    * Sets the current portals main category.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function maincategory() {
        $this->WTGPORTALMANAGER->update_main_category( $this->DB->get_active_project_id(), $_POST['selectedcategory'] );
        $this->UI->create_notice( __( "The main category allows your portal to have a blog and the selected category will be focused on.", 'wtgportalmanager' ), 'success', 'Small', __( 'Main Category Set', 'wtgportalmanager' ) );
    }
                                                                                                                                                                                                   
    /**
    * Handle request to delete the relationship between
    * portal and categories.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function portalcategories() {

    }

    /**
    * Create relationship between category and portal.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function addcategories() {
        $this->WTGPORTALMANAGER->add_portal_subcategory( $this->DB->get_active_project_id(), $_POST['selectedsubcategory'] );
        $this->UI->create_notice( __( "The new category is now available within your portal.", 'wtgportalmanager' ), 'success', 'Small', __( 'Category Added', 'wtgportalmanager' ) );
    }

    /**
    * Create relationship between users WP menu and portal.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function addmenu() {
        //     'selectedmenu' => string '2' (length=1)
        // 'ismainmenu0' => string 'ismain' (length=6)
    }

    /**
    * Handles request to delete relationship between menu and portal.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function menulist() {

    }
    
    /**
    * Adds a sidebar to this plugins options. This plugin
    * needs to register the sidebar for use by dynamic_sidebars()
    * which must be in theme sidebar.php
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function createsidebar() {
        
        // Install sidebars option. This can be removed after March 2016.
        if( !is_array( get_option( 'wtgportalmanager_sidebars' ) ) ) {
            add_option( 'wtgportalmanager_sidebars', array(), false );    
        }   
                         
        $this->WTGPORTALMANAGER->insert_sidebar( $_POST['newsidebarname'] );
           
        $this->UI->create_notice( 
            __( "A new sidebar has been stored in this plugins 
            options (WP currently offers no alternative solution). As a result the new 
            sidebar will only be available while this plugin is active.", 'wtgportalmanager' ), 
            'success', 
            'Small', 
            __( 'Sidebar Registered', 'wtgportalmanager' ) 
        );
    }
    
    /**
    * Save the main sidebar ID.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function setsidebars() {
        // get the integration data I have setup in array - it is a long term array for all my plugins
        $themes_integration_array = $this->WTGPORTALMANAGER->get_themes_integration_info(); 
             
        // loop current themes sidebars
        foreach( $themes_integration_array['sidebars'] as $themes_dynamic_sidebars ) 
        {                           

            // forms menu names and ID equal the post meta_key used to store sidebar ID's
            $selected_sidebar_id = $_POST[ $themes_dynamic_sidebars['metakey'] ];
            
            // set new portal -> sidebar relationship which adds post meta_key used in sidebar.php to the portal meta_key 
            $this->WTGPORTALMANAGER->set_sidebar_relationship( $this->DB->get_active_project_id(), $themes_dynamic_sidebars['metakey'], $selected_sidebar_id );    
            
            // add post meta to all posts that do not have it but do have a relationship with the current portal
            // get post ID's by querying post, page, maincategory, subcategory meta_keys in portal table
                
        }        
   
        $this->UI->create_notice( __( "Your current portals sidebars have been set. This change is applied instantly. Please view your portal as a none registered viewer to test.", 'wtgportalmanager' ), 'success', 'Small', __( 'Main Sidebar Set', 'wtgportalmanager' ) );        
    }
    
    /**
    * Activates or disables API's 
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function setupdefaulttwitter() {
        global $wtgportalmanager_settings;
         
        $wtgportalmanager_settings['api']['twitter']['active'] = $_POST['twitterapiswitch'];
        //$wtgportalmanager_settings['api']['twitter']['apps']['default'] = $_POST['cache_expire'];
        
        $wtgportalmanager_settings['api']['twitter']['apps']['default']['consumer_key'] = $_POST['consumer_key'];
        $wtgportalmanager_settings['api']['twitter']['apps']['default']['consumer_secret'] = $_POST['consumer_secret'];
        $wtgportalmanager_settings['api']['twitter']['apps']['default']['access_token'] = $_POST['access_token'];
        $wtgportalmanager_settings['api']['twitter']['apps']['default']['token_secret'] = $_POST['access_token_secret'];  
        $wtgportalmanager_settings['api']['twitter']['apps']['default']['screenname'] = $_POST['screenname'];
                                        
        $this->WTGPORTALMANAGER->update_settings( $wtgportalmanager_settings );    
        $this->UI->create_notice( __( "Please check features related to the API 
        you disabled or activated and ensure they are working as normal.", 'wtgportalmanager' ), 
        'success', 'Small', __( 'API Updated', 'wtgportalmanager' ) );       
    }
    
    /**
    * Store Twitter API settings for the current portal only.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.1
    * 
    * @todo do these values require encryption? 
    */
    public function setupportaltwitter() {
        global $wtgportalmanager_settings;
        
        // Allow use of the Twitter API globally.
        $wtgportalmanager_settings['api']['twitter']['active'] = true;
        $this->WTGPORTALMANAGER->update_settings( $wtgportalmanager_settings );
        
        // Store Twitter API credentials for the current portal.
        $this->WTGPORTALMANAGER->update_portals_twitter_api( 
            WTGPORTALMANAGER_PROJECT_ID, 
            $_POST['consumer_key'], 
            $_POST['consumer_secret'], 
            $_POST['access_token'], 
            $_POST['access_token_secret'], 
            $_POST['screenname'] 
        );   
         
        $this->UI->create_notice( __( "You have updated the current portals 
        Twitter App credentials.", 'wtgportalmanager' ), 
        'success', 'Small', __( 'Portals Twitter Updated', 'wtgportalmanager' ) );       
    }
    
    /**
    * Saves global forum configuration.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function configureforum() {
        global $wtgportalmanager_settings;
        
        // sanitize path
        $forum_path_modified = sanitize_text_field( $_POST['forumpath'] );
        $forum_path_modified = stripslashes_deep( $forum_path_modified );        
        $forum_path_modified = trailingslashit( $forum_path_modified );
        
        // now determine if phpBB actually exists 
        $does_phpbb_exist = $this->PHPBB->phpbb_exists( $forum_path_modified );
        if( !$does_phpbb_exist ) {
            $this->UI->create_notice( __( "Your forum installation could not be located on the path you gave. Please ensure your forum is supported and remember to visit the forum for advice.", 'wtgportalmanager' ), 'success', 'Small', __( 'Forum Not Found', 'wtgportalmanager' ) );       
            return;    
        }
        
        // include the phpBB config file - we need database prefix for queries
        require( $forum_path_modified . 'config.php' );
        
        // add config to settings
        $wtgportalmanager_settings['forumconfig']['path'] = $forum_path_modified;
        $wtgportalmanager_settings['forumconfig']['status'] = $_POST['globalforumswitch'];
        $wtgportalmanager_settings['forumconfig']['tableprefix'] = $table_prefix;
        $wtgportalmanager_settings['forumconfig']['admrelativepath'] = $phpbb_adm_relative_path;
        $wtgportalmanager_settings['forumconfig']['phpbbversion'] =  $this->PHPBB->version();
         
        // ensure compatible phpBB version installed
        if( $wtgportalmanager_settings['forumconfig']['phpbbversion'] < '3.1' ) { 
            $this->UI->create_notice( __( "This plugin does not support your current phpBB version which is " . $wtgportalmanager_settings['forumconfig']['phpbbversion'], 'wtgportalmanager' ), 'success', 'Small', __( 'Forum Version Not Supported', 'wtgportalmanager' ) );
            return;
        }
        
        $this->WTGPORTALMANAGER->update_settings( $wtgportalmanager_settings );
        
        $this->UI->create_notice( __( "You have saved your forums configuration and can now begin displaying forum data in your portals.", 'wtgportalmanager' ), 'success', 'Small', __( 'Forum Configuration Saved', 'wtgportalmanager' ) );       
    }
    
    /**
    * Handle request to save the main forum settings for the current portal.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.0
    */
    public function setupportalforum() {
        $got_forum_row = $this->PHPBB->get_forum( $_POST['mainforumid'] );
        
        // ensure forum ID is valid (numeric validation already done before arriving here using my own security approach)
        if( !$got_forum_row || empty( $got_forum_row ) ) {
            $this->UI->create_notice( __( "The forum ID you entered does not match any forums in your phpBB database. Nothing was saved, please try again.", 'wtgportalmanager' ), 'error', 'Small', __( 'Forum Not Found', 'wtgportalmanager' ) );                           
            return;    
        }
        
        $this->WTGPORTALMANAGER->update_portals_forumsettings( WTGPORTALMANAGER_PROJECT_ID, $_POST['portalforumswitch'], $_POST['mainforumid'] );
        $this->UI->create_notice( __( "You have saved your portals forum settings. If you set the switch to enabled then the next step is to ensure your portal is displaying information using forum data.", 'wtgportalmanager' ), 'success', 'Small', __( 'Forum Settings Saved', 'wtgportalmanager' ) );                       
    }
    
    /**
    * Handles request from form for selecting portals sources of information
    * for display on the Updates page.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.2
    */
    public function selectupdatesources() {       
        $this->DB->update_project_meta( WTGPORTALMANAGER_PROJECT_ID, 'updatepagesources', $_POST['informationsources'] );
        $this->UI->create_notice( __( "Sources of information for your Updates page were saved. You should check your current portals Updates page and ensure it is displaying what you expect.", 'wtgportalmanager' ), 'success', 'Small', __( 'Update Sources Saved', 'wtgportalmanager' ) );                               
    }
    
    /**
    * Handles request of selecting portals sources of information
    * for display on the Activity page.
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @since 0.0.1
    * @version 1.2
    */
    public function selectactivitysources() {       
        $this->DB->update_project_meta( WTGPORTALMANAGER_PROJECT_ID, 'activitypagesources', $_POST['informationsources'] );
        $this->UI->create_notice( 
            __( "Sources of information for your Activity page were saved. You should 
            check your current portals Updates page and ensure it is displaying what you expect.", 'wtgportalmanager' ), 
            'success', 
            'Small', 
            __( 'Activity Sources Saved', 'wtgportalmanager' ) 
        );                               
    }

    /**
    * Debug mode switch.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.0
    */
    public function debugmodeswitch() {
        $debug_status = get_option( 'webtechglobal_displayerrors' );
        if($debug_status){     
            update_option( 'webtechglobal_displayerrors',false );
            $new = 'disabled';
            
            $this->UI->create_notice( __( "Error display mode has been $new." ), 'success', 'Tiny', 
            __( 'Debug Mode Switch', 'wtgportalmanager' ) );               
                        
            wp_redirect( get_bloginfo( 'url' ) . '/wp-admin/admin.php?page=' . $_GET['page'] );
            exit;
        } else {
            update_option( 'webtechglobal_displayerrors',true );
            $new = 'enabled';
            
            $this->UI->create_notice( __( "Error display mode has been $new." ), 'success', 'Tiny', 
            __( 'Debug Mode Switch', 'wtgportalmanager' ) );               
            
            wp_redirect( get_bloginfo( 'url' ) . '/wp-admin/admin.php?page=' . $_GET['page'] );
            exit;
        }
    }
    
    /**
    * Destroy the relationship between a page and project.  
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @since 0.1.1
    * @version 1.0
    */
    public function removeprojectpage() {
        global $wpdb;
        
        if( !isset( $_GET['removedid']) ) {
            $this->UI->create_notice( __( "No valid page ID in the URL." ), 'error', 'Tiny', 
            __( 'Portal Not Changed', 'wtgportalmanager' ) );          
            return;
        }
        
        if( !is_numeric( $_GET['removedid'] ) ) {
            $this->UI->create_notice( __( "Invalid page ID in the URL, please ry again." ), 'error', 'Tiny', 
            __( 'No Changes To Portal', 'wtgportalmanager' ) );            
            return;
        }
        
        $this->DB->undo_project_page( WTGPORTALMANAGER_PROJECT_ID, $_GET['removedid'] ); 
        
            $this->UI->create_notice( __( "Page removed from portal. It will no longer be displayed in the portals
            meny or in the porals admin option." ), 'success', 'Tiny', 
            __( 'Portal Changed', 'wtgportalmanager' ) );                   
    }

    /**
    * Developer tools options form submission.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @since 0.0.11
    * @version 1.2
    */
    public function developertoolssetup() {
        global $wp_roles;

        // Does developer role exist?
        $developer_role_status = false;
        foreach( $wp_roles->roles as $role_name => $role_array ) {
            if( $role_name == 'developer' ) {
                $developer_role_status = true;    
            }            
        }
               
        // Do we need to install developer role? 
        $developer_role_result = null;
        if( !$developer_role_status ) {
            
            // Collect capabilities from $_POST for developer role.
            $added_caps = array();
            foreach( $_POST['addrolecapabilities'] as $numeric_key => $role_name ) {
                $added_caps[ $role_name ] = true;
            }
            
            // Add the developer role.        
            $developer_role_result = add_role(
                'developer',
                'Developer',
                $added_caps
            );
        }

        if ( null !== $developer_role_result ) {
            
            $description = __( "Multitool installed the Developer Role
            to your blog. The role and its abilities will apply to all
            WebTechGlobal plugins you have installed.", 'wtgportalmanager' );
            $title = __( 'Developer Role Installed', 'wtgportalmanager' );   
            $this->UI->create_notice( 
                $description, 
                'success', 
                'Small', 
                $title 
            );
            
        } else {
            
            $description = __( "The developer role appears to have
            been installed already. No changes to your roles were made.", 'wtgportalmanager' );
            $title = __( 'No Role Changes', 'wtgportalmanager' );   
            $this->UI->create_notice( 
                $description, 
                'info', 
                'Small', 
                $title 
            );
            
        }           
    }         
    
    /**
    * Create a page for the current portal. 
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.0
    * 
    * @todo use array of formats and custom formats to valid the $_GET['format'] value.
    */
    public function quickcreateportalpage() {
        $project_id = $this->DB->get_active_project_id();
        
        // Convert users page purpose selection to readable format.
        $readable_purpose = '';
        foreach( $this->CONFIG->postformats() as $format => $meta ) {
            
            if( $format == $_GET['format'] ) {
                $readable_purpose = $meta['title'];  
            }
  
        }        
    
        // Get current portals name to use as the title.
        $project_name = $this->DB->get_project_name( $project_id );
        
        // Build Page Title
        $title = $project_name . ' ' .  $readable_purpose;
         
        // Create post object.                                     
        $my_post = array(
          'post_title'    => wp_strip_all_tags( $title ),
          'post_content'  => __( 'Under Construction', 'wtgportalmanager' ),
          'post_status'   => 'publish',
          'post_author'   => get_current_user_id(),
          'post_type'     => 'page'
        );
         
        // Insert the post into the database.
        $post_id = wp_insert_post( $my_post );
                      
        // Join Page To Portal
        $this->WTGPORTALMANAGER->create_page_relationship( $project_id, $post_id );
        
        // Pages Purpose (Format) Meta
        $this->DB->update_page_purpose( $project_id, $post_id, $_GET['format'] );
        
        // Final Output
        $this->UI->create_notice( __( "A new page has been added to your current 
            active portal.", 'wtgportalmanager' ), 
            'success', 
            'Small', 
            __( 'Page Relationship Created', 'wtgportalmanager' ) 
        );    
    }
            
}// WTGPORTALMANAGER_Requests       
?>
