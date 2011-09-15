<?php
/**
 * IU WpCAS
 *
 * A free plugin to integrate WordPress with Indiana University's CAS Authentication.
 * 
 * This is a plugin to integrate with Indiana University's Central Authentication System (CAS).
 * The purpose is to offload the handling of users/passwords to a trusted system for authentication
 * (are they who they say they are?) purposes, while still maintaining control for authorization 
 * (what is this person allowed to see/do?) from within the backend of the WordPress admin panel. 
 * Users are added by IU network ID, with no need to worry about choosing passwords. Additionally, 
 * the maintainer of the blog now doesn't have to handle forgotten passwords or password resets. 
 * The plugin is provided for free (libre & gratis) to the IU Community (and anyone else who uses a similar CAS system) by the Indiana University UITS Enterprise Web Tech Services team.
 *
 * PHP version 5
 * 
 * License: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *
 * @package     IUWpCAS
 * @author      David R. Poindexter III <davpoind@iupui.edu>
 * @copyright   2011 Indiana University
 * @link        https://github.com/mtheoryx/iuwpcas
 * @license     GPLv2 or later
 * @version     0.1.0
 * @since       File available since release 0.1.0
 */

/*
    Plugin Name: IU WpCAS
    Plugin URI: https://github.com/mtheoryx/iuwpcas
    Description: This is a plugin to integrate with Indiana University's Central Authentication System (CAS).
    Author: David R Poindexter III
    Version: 0.1.0
    Author URI: http://davidrpoindexter.com/
    License: GPLv2 or later
*/

/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Used internally by WordPress
 * 
 * @var $cas_configured bool
 */
$cas_configured = true;

$cas_lockdown = false;
/**
 * Hooks into WordPress authentication system
 */
add_action('init', array('IUCASAuthentication', 'lock_down_check'));
add_action('wp_authenticate', array('IUCASAuthentication', 'authenticate'), 10, 2);
add_action('wp_logout', array('IUCASAuthentication', 'logout'));
add_action('lost_password', array('IUCASAuthentication', 'disable_function'));
add_action('retrieve_password', array('IUCASAuthentication', 'disable_function'));
add_action('password_reset', array('IUCASAuthentication', 'disable_function'));
add_filter('show_password_fields', array('IUCASAuthentication', 'show_password_fields'));
add_action('check_passwords', array('IUCASAuthentication', 'check_passwords'), 10, 3);
add_filter('login_url', array('IUCASAuthentication', 'bypass_reauth'));

/*
* Adds activation/deactivation options option panel to the admin backend, sets some defaults.
*/
add_action('admin_init', 'register_options');
function register_options() {
	register_setting('iucas-options', 'logout_type');
	register_setting('iucas-options', 'cassvc');
	register_setting('iucas-options', 'lockdown');
}

register_activation_hook(__FILE__, 'initial_defaults');
function initial_defaults() {
	update_option('logout_type', 'cas');
	update_option('cassvc', 'IU');
	update_option('lockdown', 'false');
}

register_deactivation_hook(__FILE__, 'unregister_options');
function unregister_options() {
	unregister_setting('iucas-options', 'logout_type');
	unregister_setting('iucas-options', 'cassvc');
	update_option('logout_type', '');
	update_option('cassvc', '');
	update_option('lockdown', '');
}

register_uninstall_hook(__FILE__, 'uninstall_options');
function uninstall_options() {
	delete_option('logout_type');
	delete_option('cassvc');
	delete_option('lockdown');
}

/**
* Admin Menu functions
*/
if (is_admin()) {
	include_once('lib/iuwpcas-admin.php');
	include_once('lib/iuwpcas-logout-options.php');
	include_once('lib/iuwpcas-url-options.php');
	include_once('lib/iuwpcas-lockdown-options.php');
	add_action('admin_menu', 'iu_cas_admin_menu_link');
}

function iu_cas_admin_menu_link() {
	$icon = plugin_dir_url( __FILE__ ).'assets/img/blockiu_white.gif';
	add_menu_page('IU CAS Settings', 'IU CAS', 'administrator', 'iu-cas-settings', 'iuwpcas_admin', $icon, 100);
	add_submenu_page('iu-cas-settings', 'IU CAS Logout Settings', 'IU CAS Logout', 'administrator', 'iu-cas-logout-settings', 'iuwpcas_logout_options');
	add_submenu_page('iu-cas-settings', 'IU CAS URL Settings', 'IU CAS URL', 'administrator', 'iu-cas-url-settings', 'iuwpcas_url_options');
	add_submenu_page('iu-cas-settings', 'IU CAS Lockdown Settings', 'IU CAS Lockdwon', 'administrator', 'iu-cas-lockdown-settings', 'iuwpcas_lockdown_options');
}



/**
 * Checks if the plugin class has already been defined. If not, it defines it here.
 * 
 * This is to avoid class name conflicts within WordPress and plugins.
 */
if ( !class_exists('IUCASAuthentication') ) {
	
	
	
	/**
	 * Plugin class for custom authentication method.
	 * 
	 * {{@internal Missing Long Description}}}
	 * 
	 * @package IUWpCas
	 * @since 0.1.0
	 */
	class IUCASAuthentication {    
		
		/**
		 * Main authentication method we are hijacking.
		 * 
		 * We use a WP hook to 
		 * 
		 * @since 0.1.0
		 * @global bool $using_cookie
		 * @global bool $cas_configured
		 *
		 * @todo Pull IU-specific variables out, and into db or config file
		 */
		/**
		* Cas Lockdown options
		*/
		function lock_down_check() {
			if ( get_option('lockdown') == "true" ) {
				$cas_lockdown = true;
				$requested_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				// $url = preg_replace('/\?casticket.*/', '', $requested_url);
				
				if (self::has_cas_ticket()) {
					return false;
				} else {
					self::get_cas_ticket($requested_url);
				}
				
				if ($cas_response == false) {
					die('not allowed');
				}
				return false;
			}
			return false;
		}
		
		function authenticate( &$username, &$password ) {
			global $using_cookie, $cas_configured;
			$requested_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$cas_response = self::get_cas_ticket($requested_url);
			
			if ($cas_response !== false) {
				$cas_user_id = $cas_response;
			}
		    
			/*
				We know they're in the IU Network.
				Do they have an account in this wordpress blog?
			*/
			$wp_user = get_userdatabylogin( $cas_user_id );
		
			if ( !$wp_user ) { 
				//could direct to error page, or show a notice that they aren't allowed here
				wp_redirect( site_url() );
			} else {
				$wp_username = $wp_user->user_login;
				wp_set_auth_cookie( $wp_user->ID );
				wp_redirect( site_url('/wp-admin/') );
				die();
			}
		
		}
		
		function has_cas_ticket() {
			if ( !isset($_GET['casticket']) || (empty($_GET['casticket'])) ) {
				return false;
			} else {
				return true;
			}
		}
		
		function get_cas_ticket($requested_url_option = false){
			
			if ($requested_url_option == false) {
				$requested_url = get_option('siteurl')."/wp-login.php";
			} else {
				$requested_url = $requested_url_option;
			}
			
			$requested_url = $requested_url_option;
			
			/**
			 * Login URL we are using for CAS authentication for users to get a CAS ticket.
			 */
			if ( get_option('cassvc') ) {
				$cassvc = get_option('cassvc');
			} else {
				$cassvc = "IU";
			}
			$cas_login = "https://cas.iu.edu/cas/login?cassvc=".$cassvc."&casurl=".$requested_url;
			
			/**
			 * Check for CAS ticket set in URL parameters.
			 * 
			 * If they don't have one set, we need to kick them out to CAS to get a ticket
			 * set before proceeding.
			 */
			if ( !isset($_GET['casticket']) || (empty($_GET['casticket'])) ) {
				wp_redirect( $cas_login );
				exit();
			}
			
			/**
			 * CAS ticket returned as a URL GET parameter.
			 */
			$cas_ticket = $_GET['casticket']; //we know they have a cas ticket set
			
			/**
			 * URL we send users to for validation of their CAS ticket
			 */
			$cas_validate_url = "https://cas.iu.edu/cas/validate?cassvc=".$cassvc."&casticket=".$cas_ticket.'&casurl='.$requested_url;
		    
		    /**
		     * Response back from CAS after ticket validation.
		     * 
		     * Possible values:
		     * -Line 1: yes or no
		     * -Line 2: IU Network ID or blank
		     */
			$lines = file( $cas_validate_url );
			$cas_response = rtrim( $lines[0] );
			
			/**
		     * If ticket was valid, sets the IU Network ID sent back in CAS validation response.
		     */
			if ( $cas_response != "no" ) {
                $cas_user_id = rtrim( $lines[1] );
				return $cas_user_id;
            } else {
                wp_redirect( $cas_login );
				// return false;
                exit();
            }
			
			return false;
		}
		
	    /**
	     * Sets random user passwords upon new account creation.
	     * 
	     * 
	     * patched Mar 25 2010 by Jonathan Rogers, jonathan, via findyourfans.com
	     * 
	     * @param $user string
	     * @param $pass1 string
	     * @param $pass2 string
	     */
		function check_passwords( $user, $pass1, $pass2 ) {
			$random_password = substr( md5( uniqid( microtime( ))), 0, 8 );
			$pass1=$pass2=$random_password;
		}
		
		/**
		 * Bypasses WP login to IU CAS service to avoid reauthentication via Wordpress
		 * 
		 * @param $login_url string
		 * @return $login_url string
		 */
	    function bypass_reauth( $login_url ) {
			$login_url = 'https://cas.iu.edu/cas/login?cassvc=IU&casurl='.get_option('siteurl').'/wp-login.php';
	        return $login_url;
	    }
	    
	    /**
	     * Custom logout function to bypass WordPress default logout and provide a custom redirect target.
	     * 
	     * @todo Provide users a notification of logout, and option to log out of CAS.
	     */
		function logout(){
			wp_set_current_user(0);
			wp_clear_auth_cookie();
			
			session_unset();
			
			if ( checked('site', get_option('logout_type'), false) ) {
				wp_redirect( get_option('siteurl') );
			} else if ( checked('cas', get_option('logout_type'), false) ) {
				wp_redirect( 'https://cas.iu.edu/cas/logout' );
			} else {
				//no option set yet
				wp_redirect( get_option('siteurl') );
			}
			
			exit();
		}
		
		/**
		 * Disables display of password fields in the user profile page.
		 *
		 * We don't use WP authentication, therefore don't need to worry about any WP-specific passwords.
		 * 
		 * @param $show_password_fields bool
		 * @return bool
		 */
	    function show_password_fields( $show_password_fields ) {
	      return false;
	    }
	    
	    /**
	     * Utility function to disable WP behaviors.
	     */
	    function disable_function() {
	      die('Disabled');
	    }
	    
	}
	
}
?>