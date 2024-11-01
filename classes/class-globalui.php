<?php
/** 
 * Class for interface methods that are used in both admin
 * and on the front end.
 * 
 * @package WTG Portal Manager
 * @author Ryan Bayne   
 * @since 0.0.1
 */

// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class WTGPORTALMANAGER_GLOBALUI {     
    
    public function __construct() {
    }  

    /**
    * Part of processing requests from Developer menu in the Admin Bar. 
    * 
    * Returns boolean to indicate if the view control value exists. This is used
    * with the Admin bar which has actions for controlling views.  
    * 
    * @author Ryan R. Bayne
    * @package WTG Portal Manager
    * @version 1.0
    * 
    * @param mixed $control remember this method may be used multiple 
    * times but different developer controls passed.
    */
    public function is_view_control_requested( $control ) {    
    	if( !isset( $_GET['_wpnonce'] ) ) { 
			return false;
    	}   
    	if( !wp_verify_nonce( $_GET['_wpnonce'], $control ) ) {    
			return false;
    	}                        
		if( isset( $_GET['viewcontrol'] ) && $_GET['viewcontrol'] == $control ) {   
			return true;   
		}    	   
		return false;
	}
	
    /**
    * Admin toolbar for developers.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.2
    * 
    * @todo this is still to be complete by adding arguments for each situation 
    * then changing the main action to "all" from "pluginpages", do not change 
    * "pluginpages" until arguments secure for each scenario.
    */
    function developer_toolbar() {
		
		self::developer_toolbar_webtechglobaladmin();
		self::developer_toolbar_front();
		self::developer_toolbar_coreviews();
		self::developer_toolbar_other();
		
    }
    
    /**
    * The developer toolbar items for admin side only.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.0
    * 
    * @todo Determine if there is a WP function for establish URL on any view admin or post or page
    * then replace the use of home_url with it. Possibly create a function for this.
    */
    function developer_toolbar_webtechglobaladmin() {
        global $wp_admin_bar, $wp;
                            
        // Establish Main URL
        if( is_admin() ) {
			$base_url = admin_url() . 'admin.php?page=' . $_GET['page'];			
        }
        else {
			// Use the current URL for what will be a post or a category etc.
			$base_url = home_url(add_query_arg(array(),$wp->request));
			$base_url = '?urlsource=wtgportalmanager';
        }
        
        // Level 1
        $args = array(
            'id'     => 'webtechglobal-toolbarmenu-developers',
            'title'  => __( 'Developers', 'text_domain' ),          
        );
        $wp_admin_bar->add_menu( $args );
        
	    // Group - Debug Tools
	    $args = array(
	        'id'     => 'webtechglobal-toolbarmenu-debugtools',
	        'parent' => 'webtechglobal-toolbarmenu-developers',
	        'title'  => __( 'Debug Tools', 'text_domain' ), 
	        'meta'   => array( 'class' => 'first-toolbar-group' )         
	    );        
	    $wp_admin_bar->add_menu( $args );

		// error display switch        
		$href = wp_nonce_url( $base_url . '&wtgportalmanageraction=' . 'debugmodeswitch'  . '', 'debugmodeswitch' );
		$debug_status = get_option( 'webtechglobal_displayerrors' );
		if($debug_status){
		    $error_display_title = __( 'Hide Errors', 'wtgportalmanager' );
		} else {
		    $error_display_title = __( 'Display Errors', 'wtgportalmanager' );
		}
		$args = array(
		    'id'     => 'webtechglobal-toolbarmenu-errordisplay',
		    'parent' => 'webtechglobal-toolbarmenu-debugtools',
		    'title'  => $error_display_title,
		    'href'   => $href,            
		);
        $wp_admin_bar->add_menu( $args );
     
	    // Group - Configuration Options
	    $args = array(
	        'id'     => 'webtechglobal-toolbarmenu-configurationoptions',
	        'parent' => 'webtechglobal-toolbarmenu-developers',
	        'title'  => __( 'Configuration Options', 'text_domain' ), 
	        'meta'   => array( 'class' => 'second-toolbar-group' )         
	    );        
	    $wp_admin_bar->add_menu( $args );        
		  
		// reinstall plugin settings array     
		$href = wp_nonce_url( $base_url . '&wtgportalmanageraction=' . 'wtgportalmanageractionreinstallsettings'  . '', 'wtgportalmanageractionreinstallsettings' );
		$args = array(
		    'id'     => 'webtechglobal-toolbarmenu-reinstallsettings',
		    'parent' => 'webtechglobal-toolbarmenu-configurationoptions',
		    'title'  => __( 'Re-Install Settings', 'trainingtools' ),
		    'href'   => $href,            
		);
		$wp_admin_bar->add_menu( $args );
		
		
		// reinstall all database tables
		$thisaction = 'wtgportalmanagerreinstalltables';
		$href = wp_nonce_url( $base_url . '&wtgportalmanageraction=' . $thisaction, $thisaction );
		$args = array(
		    'id'     => 'webtechglobal-toolbarmenu-reinstallalldatabasetables',
		    'parent' => 'webtechglobal-toolbarmenu-configurationoptions',
		    'title'  => __( 'Re-Install Tables', 'multitool' ),
		    'href'   => $href,            
		);
		$wp_admin_bar->add_menu( $args );
 
	    // Group - View Controls
	    $args = array(
	        'id'     => 'webtechglobal-toolbarmenu-viewcontrols',
	        'parent' => 'webtechglobal-toolbarmenu-developers',
	        'title'  => __( 'View Controls', 'text_domain' ), 
	        'meta'   => array( 'class' => 'third-toolbar-group' )         
	    );        
	    $wp_admin_bar->add_menu( $args ); 

		// Delete cache being used for the current display.
		$href = wp_nonce_url( $base_url . '&viewcontrol=deletecaches', 'deletecaches' );
		$args = array(
		    'id'     => 'webtechglobal-toolbarmenu-deletecaches',
		    'parent' => 'webtechglobal-toolbarmenu-viewcontrols',
		    'title'  => __( 'Delete Caches', 'multitool' ),
		    'href'   => $href,            
		);
		$wp_admin_bar->add_menu( $args );
		   
		// Display trace data for the current view.
		$href = wp_nonce_url( $base_url . '&viewcontrol=displaytrace', 'displaytrace' );
		$args = array(
		    'id'     => 'webtechglobal-toolbarmenu-displaytracking',
		    'parent' => 'webtechglobal-toolbarmenu-viewcontrols',
		    'title'  => __( 'Display Trace', 'multitool' ),
		    'href'   => $href,            
		);
		$wp_admin_bar->add_menu( $args );
		       
	}    
    
    /**
    * The developer toolbar items for front/public side only.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.0
    */
    function developer_toolbar_front() {
    	
	}    
        
    /**
    * The developer toolbar items for all other views excluding WP core
    * views, WTG plugins and the front-end. 
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.0
    */
    function developer_toolbar_coreviews() {
    	
	}    
            
    /**
    * The developer toolbar items for all other views excluding WP core
    * views, WTG plugins and the front-end. 
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.0
    */
    function developer_toolbar_other() {
    	
	} 
	            
    /**
    * Use with Developer menu in Admin Bar. Allows us to display information for the
    * current view that helps us determine what causes the resulting view. 
    * 
    * Call at the end of a procedure to display argument results and final
    * variable values. Use when something isn't working as it should, there is
    * a chance of future problems (the procedure is complex) and debugging could
    * take a while each time. It is also great for calling on live sites, clients sites
    * and it results in saving time.
    * 
    * @author Ryan R. Bayne
    * @package WebTechGlobal WordPress Plugins
    * @version 1.0
    */
    function displaytrace() {      
    	if( self::is_view_control_requested( 'displaytrace' ) ) {
    		global $wtgportalmanager_displaytrace;
    		
    		// If no entries output the fact.
    		if( !is_array( $wtgportalmanager_displaytrace ) ) {
				echo __( 'No trace was set while loading the current view.', 'wtgportalmanager' );
				return;	
    		}
    		
    		// If no entries output the fact.
    		if( empty( $wtgportalmanager_displaytrace ) ) {
				echo __( 'No trace was setup for the current view.', 'wtgportalmanager' );
				return;	
    		}
    		
    		// $wtgportalmanager_displaytrace is set with an array of tracking data. 
			$final_output = '<table>';
			$final_output .= '			  
			<tr>
				<th>Function</th>
				<th>Line</th> 
				<th>Variable Value</th>
				<th>Developer Comment</th>
			</tr>';
                         
			foreach( $wtgportalmanager_displaytrace as $k => $entry ) {
				$new_item = '<tr>';
				                   
					$new_item .= '<td>';
					if( isset( $entry['function'] ) ) {
						$new_item .= $entry['function'];		
					}
					$new_item .= '</td>';
					
					$new_item .= '<td>';
					if( isset( $entry['line'] ) ) {
						$new_item .= $entry['line'];	
					}
					$new_item .= '</td>';
					
					$new_item .= '<td>';
					if( isset( $entry['variable'] ) ) {
						$new_item .= $entry['variable'];	
					}
					$new_item .= '</td>';
					
					$new_item .= '<td>';
					if( isset( $entry['comment'] ) ) {
						$new_item .= $entry['comment'];	
					}
					$new_item .= '</td>';

				$new_item .= '</tr>';
				                  
				$final_output .= $new_item;	
			}
			
			$final_output .= '</table>';
			echo $final_output;
    	}	
	} 

}
?>
