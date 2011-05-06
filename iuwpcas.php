<?php
/*
Plugin Name: IU WpCAS
Version: 1.0
Description: Authenticate WordPress through Indiana University Central Authentication Service
Author: David R Poindexter III
*/

$cas_configured = true;

/* plugin hooks into authentication system */
add_action('wp_authenticate', array('IUCASAuthentication', 'authenticate'), 10, 2);
add_action('wp_logout', array('IUCASAuthentication', 'logout'));
add_action('lost_password', array('IUCASAuthentication', 'disable_function'));
add_action('retrieve_password', array('IUCASAuthentication', 'disable_function'));
add_action('password_reset', array('IUCASAuthentication', 'disable_function'));
add_filter('show_password_fields', array('IUCASAuthentication', 'show_password_fields'));
add_action('check_passwords', array('IUCASAuthentication', 'check_passwords'), 10, 3);
add_filter('login_url', array('IUCASAuthentication', 'bypass_reauth'));

if (!class_exists('IUCASAuthentication')) {
  class IUCASAuthentication {    

	function authenticate(&$username, &$password) {
		global $using_cookie, $cas_configured;
		$casLogin = "https://cas.iu.edu/cas/login?cassvc=IU&casurl=".get_option('siteurl')."/wp-login.php";
		
		/*
			Are they an IU user?
		*/
		if ( !isset($_GET['casticket']) || (empty($_GET['casticket'])) ) { //no cas ticket set
			wp_redirect($casLogin);
			exit();
		}
		
		$casTicket = $_GET['casticket']; //we know they have a cas ticket set
		
		//send them out for validation from CAS
		$casValidateUrl = 'https://cas.iu.edu/cas/validate?cassvc=IU&casticket='.$casTicket.'&casurl='.get_option('siteurl').'/wp-login.php';
		
		$lines = file($casValidateUrl);								//response from cas
		$casResponse = rtrim($lines[0]);							//first line: yes or no response from CAS
		
		if ($casResponse != "no") { $casuID = rtrim($lines[1]);	} 	//grab the IU Network ID sent back by CAS
		else { wp_redirect($casLogin);	exit();	}					//if the ticket is not valid, send them to get one
		
		/*
			We know they're in the IU Network.
			Do they have an account in this wordpress blog?
		*/
		$wp_user = get_userdatabylogin($casuID);
		
		if ( $casResponse == 'yes' && !$wp_user ) { 
			wp_redirect(site_url());
			exit();
		} else {
			$wp_username = $wp_user->user_login;
			wp_set_auth_cookie($wp_user->ID);
			wp_redirect( site_url( '/wp-admin/' ));
			die();
		}
		
		
	}
	
	// set the passwords on user creation
	// patched Mar 25 2010 by Jonathan Rogers jonathan via findyourfans.com
	function check_passwords( $user, $pass1, $pass2 ) {
		$random_password = substr( md5( uniqid( microtime( ))), 0, 8 );
		$pass1=$pass2=$random_password;
	}
	
	/* WP login bypass to IU CAS service */
    function bypass_reauth($login_url) {
        $login_url = 'https://cas.iu.edu/cas/login?cassvc=IU&casurl='.get_option('siteurl').'/wp-login.php';
        return $login_url;
    }
	
	function logout(){
		wp_clear_auth_cookie();
		wp_redirect('https://cas.iu.edu');
		exit();
	}
		
    
	/* Don't show password fields on user profile page. */
    function show_password_fields($show_password_fields) {
      return false;
    }
    
    
    function disable_function() {
      die('Disabled');
    }
    
  }
 }







