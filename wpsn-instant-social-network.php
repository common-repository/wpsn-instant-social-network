<?php
/*
 * Plugin Name:       WPSN: Instant Social Network
 * Plugin URI:        https://wpsn.site/
 * Description:       Instantly turn your website into a social network! Activity, Friends, Alerts - and more!
 * Version:           0.8.7
 * Requires at least: 6.5.3
 * Requires PHP:      7.0
 * Author:            Simon Goodchild
 * Author URI:        https://profiles.wordpress.org/simongoodchild
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpsn-instant-social-network
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
 
// Create session for WPS nonce
session_start();

add_action( 'init', 'wpsn_create_globals' );
function wpsn_create_globals() {
	
	// Generate a nonce and store it in a global variable
	$_SESSION['wpsn_nonce'] = wp_create_nonce('wpsn_nonce');
	set_transient( 'wpsn_nonce', esc_html(sanitize_text_field($_SESSION['wpsn_nonce'])), 1 * HOUR_IN_SECONDS ); // Storing nonce for hour(s)
	
	// Current user ID
	global $current_user;
	$_SESSION['wpsn_current_user_id'] = intval($current_user->ID);
	
	session_write_close();

}

// ******************** Components ********************

require_once(dirname(__FILE__) . '/functions.php');
require_once(dirname(__FILE__) . '/edit_profile.php');
require_once(dirname(__FILE__) . '/story.php');
require_once(dirname(__FILE__) . '/activity.php');
require_once(dirname(__FILE__) . '/friends.php');
require_once(dirname(__FILE__) . '/login.php');
require_once(dirname(__FILE__) . '/search.php');
require_once(dirname(__FILE__) . '/alerts.php');


// ******************** Init ********************

add_action( 'wp_login', 'wpsn_custom_login_action', 10, 2 );
add_action( 'admin_enqueue_scripts', 'wpsn_admin_init');
add_action( 'wp_enqueue_scripts', 'wpsn_init');
add_action( 'init', 'wpsn_posts_post_type' );
add_action( 'init', 'wpsn_posts_alert_type' );
add_action( 'init', 'wpsn_posts_email_type' );
add_action( 'init', 'wpsn_custom_post_status' );
add_action( 'wp_head', 'wpsn_add_dynamic_background_css');

add_action('template_redirect', 'wpsn_insert_html_body_open');
function wpsn_insert_html_body_open() {

	// Redirect to admin if not yet visited since activation
	if (get_option( 'wpsn_menu_bar' ) === false) {
		wp_redirect( admin_url( 'admin.php?page=wpsn-instant-social-network/wpsn-admin-page.php&first_time=1' ) );
		exit();
	} else {
		$current_theme = wp_get_theme();
		if ($current_theme->get('Name') != 'WPSN: Social Network for WordPress Plugin Theme') {
			if (get_option( 'wpsn_menu_bar' ) == 1) {
				wpsn_menu(0);
			}
		}
	}
}

// ******************** Includes for everything ********************

include( plugin_dir_path( __FILE__ ) . '../../../wp-admin/includes/upgrade.php');

// ******************** AJAX ********************

require_once(dirname(__FILE__) . '/ajax/wpsn_story_ajax.php');
require_once(dirname(__FILE__) . '/ajax/wpsn_friends_ajax.php');
require_once(dirname(__FILE__) . '/ajax/wpsn_login_ajax.php');
require_once(dirname(__FILE__) . '/ajax/wpsn_alerts_ajax.php');
require_once(dirname(__FILE__) . '/ajax/wpsn_profile_ajax.php');
require_once(dirname(__FILE__) . '/ajax/wpsn_search_ajax.php');
require_once(dirname(__FILE__) . '/menu.php');

// ******************** WP Cron ********************

// Step 1: Add custom cron schedules
add_filter('cron_schedules', 'wpsn_custom_cron_schedules');

function wpsn_custom_cron_schedules($schedules) {
	
	$wpsn_cron_seconds = get_option( 'wpsn_cron_seconds' ) ? get_option( 'wpsn_cron_seconds' ) : 60;
	
    $schedules['wpsn_cron_schedule'] = array(
        'interval' => $wpsn_cron_seconds,
        'display'  => esc_html__('WPSN Cron schedule', 'wpsn-instant-social-network'),
    );
	//error_log('Custom cron job schedule created.');
    return $schedules;
}

// Step 2: Schedule event on plugin activation
register_activation_hook(__FILE__, 'wpsn_activate_cron');

function wpsn_activate_cron() {
    // Clear any existing schedules to avoid duplicates
    $timestamp = wp_next_scheduled('wpsn_custom_cron_job');
    if ($timestamp) {
		//error_log('Unscheduled cron job cleared.');
        wp_unschedule_event($timestamp, 'wpsn_custom_cron_job');
    } else {
		//error_log('No unscheduled cron job to clear.');
	}

    // Schedule the event
    if (!wp_next_scheduled('wpsn_custom_cron_job')) {
        //error_log('Scheduling custom cron job.');
        wp_schedule_event(time(), 'wpsn_cron_schedule', 'wpsn_custom_cron_job');
    } else {
        //error_log('Custom cron job already scheduled.');
    }
}

// Step 3: Hook the custom cron job function to the action
add_action('wpsn_custom_cron_job', 'wpsn_custom_cron_job_function');

function wpsn_custom_cron_job_function() {
	
	// Alert site admin that WPSN Cron function is running?
	
	$cron_alerts = get_option('wpsn_cron_alerts');
	if ($cron_alerts !== false && $cron_alerts == 1) { 

		$to = get_site_option('admin_email');
		$subject = 'WPSN Cron Email';
		$message = '<p>This has been sent from WPSN Cron schedule.</p>';
		$headers = array('Content-Type: text/html; charset=UTF-8');

		$sent = wp_mail($to, $subject, $message, $headers);

		if ($sent) {
			//error_log('WPSN Cron failed to send email.');
		}
		
	}
	
	// Now loop through and send all draft emails in the queue
	
	$wpsn_cron_count = get_option( 'wpsn_cron_count' ) ? get_option( 'wpsn_cron_count' ) : 9999;
	
	// Arguments for the query
	$args = array(
		'post_type'      => 'wpsn-email',
		'post_status'    => 'draft',
		'posts_per_page' => $wpsn_cron_count
	);

	// The Query
	$query = new WP_Query($args);

	// Check if there are any posts to display
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			global $post;

			$to_id = $post->post_parent;
			$to = get_userdata($to_id)->user_email;
			$subject = $post->post_title;
			$message = $post->post_content;
			$headers = array('Content-Type: text/html; charset=UTF-8');

			$sent = wp_mail($to, $subject, $message, $headers);

			if ($sent) {
				// Email was successfully sent
				wp_update_post(array('ID' => $post->ID, 'post_status' => 'publish'));
				
			} else {
				// Email sending failed
				//error_log('WPSN Cron failed to send email (Post ID = '.$post->ID.'.');
			}

		}
	} else {
		echo 'No draft posts found.';
	}

	// Restore original Post Data
	wp_reset_postdata();

}

// Step 4: Clear the scheduled event on plugin deactivation
register_deactivation_hook(__FILE__, 'wpsn_deactivate_cron');

function wpsn_deactivate_cron() {
    $timestamp = wp_next_scheduled('wpsn_custom_cron_job');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'wpsn_custom_cron_job');
        //error_log('Custom cron job unscheduled.');
    } else {
		//error_log('No custom cron job to unscheduled.');
	}
}


if ( is_admin() ) {
	
	// Components
	
	require_once(dirname(__FILE__) . '/admin.php');
	require_once(dirname(__FILE__) . '/ajax/wpsn_admin_ajax.php');
	
	// Add WPSN Menu
    add_action( 'admin_menu', 'wpsn_admin_menu' );
	// Enqueue admin scripts
	add_action( 'admin_enqueue_scripts', 'wpsn_enqueue_admin_script' );
	// Add media library support for use in admin pages
	add_action('admin_enqueue_scripts', 'wpsn_enqueue_media');
	// Add scripts and styles for color picker
	add_action('admin_enqueue_scripts', 'wpsn_enqueue_color_picker');
	// Add Settings to Plugins page
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wpsn_plugin_add_settings_link');
	// Customise Email Posts page
	add_filter('manage_wpsn-email_posts_columns', 'wpsn_add_custom_emails_columns');
	add_action('manage_wpsn-email_posts_custom_column', 'wpsn_custom_emails_column_content', 10, 2);
	add_action('manage_posts_extra_tablenav', 'wpsn_add_content_above_posts_table');	

} else {	

	add_action('template_redirect', 'wpsn_restrict_custom_post_type_access');
	add_action('template_redirect', 'wpsn_page_viewed');
	// Custom HTML/CSS
	add_action('wp_head', 'wpsn_add_custom_css');
	add_action('wp_head', 'wpsn_custom_html');
    // Shortcodes - Profile Page
	add_shortcode('wpsn-home', 'wpsn_sc_profile_home');	
    // Shortcodes - Activity
    add_shortcode('wpsn-activity', 'wpsn_sc_activity');
	// Shortcodes - Friends
	add_shortcode('wpsn-friends', 'wpsn_sc_friends');	
	// Shortcodes - Profile Edit
    add_shortcode('wpsn-profile-edit', 'wpsn_sc_profile_edit');
	// Shortcodes - Alerts
	add_shortcode('wpsn-alerts', 'wpsn_sc_alerts');
	// Shortcodes - Search
    add_shortcode('wpsn-search', 'wpsn_sc_search');		
	// Shortcodes - Login/Register
    add_shortcode('wpsn-login', 'wpsn_sc_login');
    add_shortcode('wpsn-signup', 'wpsn_sc_signup');	
}

function wpsn_nonce($url) {
	
	$url = rtrim($url, '/');
	if (strpos($url, '?') !== false) {
		$url .= '&wpsn='.esc_html(sanitize_text_field($_SESSION['wpsn_nonce']));
	} else {
		$url .= '?wpsn='.esc_html(sanitize_text_field($_SESSION['wpsn_nonce']));
	}
	return $url;
	
}

?>