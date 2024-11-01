<?php

function wpsn_admin_page(){
	
	global $current_user;
	
	?>	
	
	<div class="wpsn-admin-header">
		<img class="wpsn-admin-header-image-large" src="<?php echo esc_html(plugins_url('img/wpsn_wide_admin_banner.jpg', __FILE__)); ?>" />
	</div>

	<?php if (isset($_GET['first_time'])) { ?>
		<div class="notice notice-info is-dismissable" style="margin-left: 0">
			<h1>Welcome to WPSN!</h1>
			<p>Please review the settings below. You can return here any time via the menu.</p>
			<h2>Quick Start Guide</h2>
			<p>Want to get things setup quick? Check out the <a target="_new" href="https://wpsn.site/quick-start">Quick Start Guide</a>.</p>
		</div>
	<?php }
	
	$current_theme = wp_get_theme();
	if (isset($_GET['first_time']) && $current_theme->get('Name') != 'WPSN: Social Network for WordPress Plugin Theme') {
	?>	
		<div class="notice notice-info is-dismissable" style="margin-left: 0">
			<h3><i class="fa-solid fa-thumbs-up wpsn-info"></i> Making your Theme awesome!</h3>
			<p>The goal is that WPSN is compatible with all themes. Check out the useful <a target="_new" href="https://wpsn.site/themes">tips for your theme</a>.<br />
			As used at <a target="_new" href="https://wpsn.site">https://wpsn.site</a>, you can download the free WPSN Theme and instantly set up your social network - <a target="_new" href="https://wpsn.site">download now</a>.</p>
		</div>
		<div class="notice notice-info is-dismissable" style="margin-left: 0">
			<h3><i class="fa-solid fa-user wpsn-info"></i> Need some support?</h3>
			<p>All feedback and questions are welcome - do so via the <a target="_new" href="https://wordpress.org/support/plugin/wpsn-instant-social-network/">WPSN Support forum</a>.</p>
		</div>
	<?php } 
	
	$admin_url = admin_url( 'admin.php?page=wpsn-instant-social-network' ) . '/wpsn-admin-page.php';

	/* ACT ON PARAMETERS */
	
	if (isset($_GET['action']) && current_user_can('manage_options') ) {
		
		// Cron Email
		if ($_GET['action'] == 'cron_email') {
			
			$cron_alerts = get_option('wpsn_cron_alerts');
			$cron_alerts = $cron_alerts == 1 ? 0 : 1;
			
			update_option('wpsn_cron_alerts', $cron_alerts);
			
			wpsn_admin_reload_with_alert('cronemail', 'success', 'Setting toggled.', false);
			
		}
		
		// Test Email
		if ($_GET['action'] == 'test_email') {

			$http_host = sanitize_text_field($_SERVER['HTTP_HOST']);

			$to = $current_user->user_email;
			$subject = 'Test Email';
			$message = wp_kses_post('This is a test email sent from '.$http_host.'.');
			$headers = array('Content-Type: text/html; charset=UTF-8');

			$sent = wp_mail($to, $subject, $message, $headers);

			if ($sent) {
				// Email was successfully sent
				wpsn_admin_reload_with_alert('testemail', 'success', wp_kses('Email sent to '.$current_user->user_email.' successfully.', 'strip'), false);
			} else {
				// Email sending failed
				wpsn_admin_reload_with_alert('testemail', 'error', wp_kses('Failed to send email to '.$current_user->user_email.'.', 'strip'), false);
			}

		}

		// Delete pages - confirm
		
		if ($_GET['action'] == 'delete_pages_confirm') {
			
			$current_url = esc_html(wpsn_admin_get_current_url());

			$final_string = 'Are you sure you want to delete all the pages?</br><br /><a class="button" href="'.$current_url.'&action=delete_pages&trash=0">Yes (permanently)</a> <a class="button" href="'.$current_url.'&action=delete_pages&trash=1">Yes (move to trash)</a> <a class="button" href="'.$current_url.'">No</a>';
			
			wpsn_admin_reload_with_alert('deletewarning', 'warning', $final_string, false);

		}
		
		// Delete Pages
		
		if ($_GET['action'] == 'delete_pages') {
			
			$destroy = true;
			if ($_GET['trash'] == '1') { $destroy = false; }
			
			$wpsn_home = 			get_option( 'wpsn_home' ) 			? get_option( 'wpsn_home' ) 		: 0;
			$wpsn_activity = 		get_option( 'wpsn_activity' ) 		? get_option( 'wpsn_activity' ) 	: 0;
			$wpsn_friends = 		get_option( 'wpsn_friends' ) 		? get_option( 'wpsn_friends' ) 		: 0;
			$wpsn_edit_profile = 	get_option( 'wpsn_edit_profile' ) 	? get_option( 'wpsn_edit_profile' ) : 0;
			$wpsn_search = 			get_option( 'wpsn_search' ) 		? get_option( 'wpsn_search' ) 		: 0;
			$wpsn_alerts = 			get_option( 'wpsn_alerts' ) 		? get_option( 'wpsn_alerts' ) 		: 0;
			$wpsn_login = 			get_option( 'wpsn_login' ) 			? get_option( 'wpsn_login' ) 		: 0;
			$wpsn_signup = 			get_option( 'wpsn_signup' ) 		? get_option( 'wpsn_signup' ) 		: 0;
			
			if ($wpsn_home) { wp_delete_post($wpsn_home, $destroy); }
			if ($wpsn_activity) { wp_delete_post($wpsn_activity, $destroy); }
			if ($wpsn_friends) { wp_delete_post($wpsn_friends, $destroy); }
			if ($wpsn_edit_profile) { wp_delete_post($wpsn_edit_profile, $destroy); }
			if ($wpsn_search) { wp_delete_post($wpsn_search, $destroy); }
			if ($wpsn_alerts) { wp_delete_post($wpsn_alerts, $destroy); }
			if ($wpsn_login) { wp_delete_post($wpsn_login, $destroy); }
			if ($wpsn_signup) { wp_delete_post($wpsn_signup, $destroy); }
			
			delete_option( 'wpsn_home' );
			delete_option( 'wpsn_activity' );
			delete_option( 'wpsn_friends' );
			delete_option( 'wpsn_edit_profile' );
			delete_option( 'wpsn_search' );
			delete_option( 'wpsn_alerts' );
			delete_option( 'wpsn_login' );
			delete_option( 'wpsn_home' );
			delete_option( 'wpsn_signup' );
			
			do_action('wpsn_admin_delete_pages_action');
			
			if ($destroy) {
				wpsn_admin_reload_with_alert('deletedpages', 'success', 'All pages have been permanently deleted.', false);				
			} else {
				wpsn_admin_reload_with_alert('deletedpages', 'success', 'All pages have been deleted, and moved to trash.', false);
			}

		}		
		
		// Create pages - confirm
		
		if ($_GET['action'] == 'create_pages_confirm') {
			
			$current_url = esc_html(wpsn_admin_get_current_url());
			
			$final_string = 'Are you sure you want to create all the pages, and add the shortcodes to them?<br />If you have already created them, you will have duplicate pages!</br><br /><a class="button" href="'.$current_url.'&action=create_pages">Yes</a> <a class="button" href="'.$current_url.'">No</a>';
			
			wpsn_admin_reload_with_alert('createwarning', 'warning', $final_string, false);

		}
		
		// Create Pages
		
		if ($_GET['action'] == 'create_pages') {
			
			$success = update_option( 'wpsn_landing_page',	site_url() );
			
			$page_id = wpsn_create_new_page_with_content('Profile', '[wpsn-home]');
			$success = update_option( 'wpsn_home', $page_id );
			
			$page_id = wpsn_create_new_page_with_content('Home', '[wpsn-activity]');
			$success = update_option( 'wpsn_activity', $page_id );
			
			$page_id = wpsn_create_new_page_with_content('Friends', '[wpsn-friends]');
			$success = update_option( 'wpsn_friends', $page_id );
			
			$page_id = wpsn_create_new_page_with_content('Account', '[wpsn-profile-edit]');
			$success = update_option( 'wpsn_edit_profile', $page_id );
			
			$page_id = wpsn_create_new_page_with_content('Search', '[wpsn-search]');
			$success = update_option( 'wpsn_search', $page_id );
			
			$page_id = wpsn_create_new_page_with_content('Alerts', '[wpsn-alerts]');
			$success = update_option( 'wpsn_alerts', $page_id );
			
			$page_id = wpsn_create_new_page_with_content('Login', '[wpsn-login]');
			$success = update_option( 'wpsn_login', $page_id );

			$page_id = wpsn_create_new_page_with_content('Sign Up', '[wpsn-signup]');
			$success = update_option( 'wpsn_signup', $page_id );
			
			do_action('wpsn_admin_create_pages_action');			
			
			wpsn_admin_reload_with_alert('createdpages', 'success', 'All pages now set up! (check below) - if you repeat, you will have duplicate pages!', false);

		}

		// Delete all posts - confirm

		if ($_GET['action'] == 'delete_all_posts_confirm') {
			
			$current_url = esc_html(wpsn_admin_get_current_url());
			
			$final_string = 'Are you sure you want to delete all posts and replies?</br><br /><a class="button" href="'.$current_url.'&action=delete_all_posts">Yes</a> <a class="button" href="'.$current_url.'">No</a>';
			
			wpsn_admin_reload_with_alert('deletepostswarning', 'warning', $final_string, false);

		}

		// Delete all posts
		if ($_GET['action'] == 'delete_all_posts') {
			
			$args = array(
				'post_type' => 'wpsn-feed',
				'post_status' => 'any',
				'numberposts' => -1
			);

			$wpsn_posts = get_posts($args);
			$count = count($wpsn_posts);

			// Loop through each post and delete it
			foreach ($wpsn_posts as $post) {
				wp_delete_post($post->ID, true); // true to force delete
			}

			wpsn_admin_reload_with_alert('deletedposts', 'success', 'All '.$count.' posts and replies deleted!', false);

		}

		// Delete all alerts - confirm

		if ($_GET['action'] == 'delete_all_alerts_confirm') {
			
			$current_url = esc_html(wpsn_admin_get_current_url());
			
			$final_string = 'Are you sure you want to delete all alerts?</br><br /><a class="button" href="'.$current_url.'&action=delete_all_alerts">Yes</a> <a class="button" href="'.$current_url.'">No</a>';
			
			wpsn_admin_reload_with_alert('deletealertswarning', 'warning', $final_string, false);

		}

		// Delete all alerts
		if ($_GET['action'] == 'delete_all_alerts') {
			
			$args = array(
				'post_type' => 'wpsn-alert',
				'post_status' => array( 'publish', 'unread' ),
				'numberposts' => -1
			);

			$wpsn_alerts = get_posts($args);
			$count = count($wpsn_alerts);

			// Loop through each alert and delete it
			foreach ($wpsn_alerts as $alert) {
				wp_delete_post($alert->ID, true); // true to force delete
			}

			wpsn_admin_reload_with_alert('deletedalerts', 'success', 'All '.$count.' alerts deleted!', false);

		}
		
		// Delete all WPSN - confirm

		if ($_GET['action'] == 'delete_wpsn_confirm') {
			
			$current_url = esc_html(wpsn_admin_get_current_url());
						
			$final_string = 'Are you sure you want to delete all WPSN system and user data?<br /><strong>This may take some time, don\'t interrupt it and wait for confirmation it has completed.</strong><br />This assumes all posts, postmeta, usermeta, options, etc uniquely use wpsn_ as a prefix.<br /><strong>Backup your website and database first!</strong><br /><br /><a class="button" href="'.$current_url.'&action=delete_wpsn">Yes</a> <a class="button" href="'.$current_url.'">No</a>';
			
			wpsn_admin_reload_with_alert('deletewpsn', 'warning', $final_string, false);

		}
		
		if ($_GET['action'] == 'delete_wpsn') {

			global $wpdb;
			$html = '';
			
			// Delete all WPSN posts
			
			$post_types = array('wpsn-feed', 'wpsn-alert', 'wpsn-email');

			$args = array(
				'post_type'      => $post_types,
				'post_status'    => 'any',
				'numberposts'    => -1, // Retrieve all posts
				'fields'         => 'ids' // Fetch only the IDs to optimize performance
			);

			$post_ids = get_posts($args);

			// Check if there are any posts to delete
			if (!empty($post_ids)) {
				// Use array_map to delete all posts
				array_map(function($post_id) {
					$deleted = wp_delete_post($post_id, true); // true to force delete
				}, $post_ids);
			}
			
			do_action('wpsn_admin_delete_posts_action');
			
			$html .= 'All WordPress posts deleted.<br />';
			
			// Delete all pages
			$destroy = true;
			
			$wpsn_home = 			get_option( 'wpsn_home' ) 			? get_option( 'wpsn_home' ) 		: 0;
			$wpsn_activity = 		get_option( 'wpsn_activity' ) 		? get_option( 'wpsn_activity' ) 	: 0;
			$wpsn_friends = 		get_option( 'wpsn_friends' ) 		? get_option( 'wpsn_friends' ) 		: 0;
			$wpsn_edit_profile = 	get_option( 'wpsn_edit_profile' ) 	? get_option( 'wpsn_edit_profile' ) : 0;
			$wpsn_search = 			get_option( 'wpsn_search' ) 		? get_option( 'wpsn_search' ) 		: 0;
			$wpsn_alerts = 			get_option( 'wpsn_alerts' ) 		? get_option( 'wpsn_alerts' ) 		: 0;
			$wpsn_login = 			get_option( 'wpsn_login' ) 			? get_option( 'wpsn_login' ) 		: 0;
			$wpsn_signup = 			get_option( 'wpsn_signup' ) 		? get_option( 'wpsn_signup' ) 		: 0;
			
			if ($wpsn_home) { wp_delete_post($wpsn_home, $destroy); }
			if ($wpsn_activity) { wp_delete_post($wpsn_activity, $destroy); }
			if ($wpsn_friends) { wp_delete_post($wpsn_friends, $destroy); }
			if ($wpsn_edit_profile) { wp_delete_post($wpsn_edit_profile, $destroy); }
			if ($wpsn_search) { wp_delete_post($wpsn_search, $destroy); }
			if ($wpsn_alerts) { wp_delete_post($wpsn_alerts, $destroy); }
			if ($wpsn_login) { wp_delete_post($wpsn_login, $destroy); }
			if ($wpsn_signup) { wp_delete_post($wpsn_signup, $destroy); }
			
			do_action('wpsn_admin_delete_pages_action');
			
			$html .= 'All WordPress pages deleted.<br />';

			/*
			
			// Delete all options
			$options = wp_load_alloptions();
			foreach ( $options as $option_name => $option_value ) {
				if ( strpos( $option_name, 'wpsn_' ) === 0 ) {
					delete_option( $option_name );
				}
			}
			
			$html .= 'All WordPress options deleted.<br />';

*/
			// Delete all WPSN user meta 
			
			// Get all users
			$users = get_users();

			// Iterate through each user
			foreach ($users as $user) {
				$user_id = $user->ID;

				// Get all meta data for the user
				$user_meta = get_user_meta($user_id);

				// Iterate through each meta data
				foreach ($user_meta as $meta_key => $meta_value) {
					// Check if the meta key starts with 'wpsn_'
					if (strpos($meta_key, 'wpsn_') === 0) {
						// Delete the meta data
						delete_user_meta($user_id, $meta_key);
					}
				}
			}

			$html .= 'All WordPress user meta deleted.<br />';
			
			// Delete all user upload
			
			// Get all users
			$users = get_users();
			
			// Include the WP_Filesystem class
			require_once ABSPATH . 'wp-admin/includes/file.php';

			// Initialize the WP_Filesystem
			global $wp_filesystem;

			if ( ! WP_Filesystem() ) {
				// Failed to initialize WP_Filesystem, handle error.
				error_log ('--------------- Failed to initialize WP_Filesystem (wpsn_insert_feed_post).');
				return;
			}

			// Get all users
			$users = get_users();

			// Iterate through each user
			foreach ($users as $user) {
				$user_id = $user->ID;

				// Get the user's uploads directory path
				$user_upload_dir = 'C:\\MAMP\\htdocs/wp-content/uploads/' . $user_id;

				// Replace forward slashes with backslashes if running on Windows
				if (DIRECTORY_SEPARATOR === '\\') {
					$user_upload_dir = str_replace('/', '\\', $user_upload_dir);
				}
				$user_upload_dir = str_replace('\\\\', '\\', $user_upload_dir);

				// Check if the user's upload directory exists
				if (file_exists($user_upload_dir) && is_dir($user_upload_dir)) {
				
					// Delete directory recursively
					$wp_filesystem->delete($user_upload_dir, true);
					
					// Optionally, log deletion success
					$html .= "Deleted upload directory for user ID: " . $user_id . '<br />';
					   
				}
			}

			$html .= 'All user uploaded content deleted.<br />';
			
			// Hook for PRO
			
			wpsn_admin_reload_with_alert('deletedwpsn', 'success', $html, true);
			
		}


		// Delete all alerts - confirm

		if ($_GET['action'] == 'delete_all_emails_confirm') {
			
			$current_url = esc_html(wpsn_admin_get_current_url());
			
			wpsn_admin_reload_with_alert('deleteemailswarning', 'success', 'Are you sure you want to delete emails?</br><br /><a class="button" href="'.$current_url.'&action=delete_all_emails">Delete ALL emails</a> <a class="button" href="'.$current_url.'&action=delete_all_emails&status=draft">All UNSENT emails</a> <a class="button" href="'.$current_url.'&action=delete_all_emails&status=publish">All SENT emails</a> <a class="button" href="'.$current_url.'">No</a>', false);

		}

		// Delete all alerts
		if ($_GET['action'] == 'delete_all_emails') {

			$post_status = array( 'publish', 'draft' );
			$post_status_to_delete = 'all';
			if (isset($_GET['status']) && ($_GET['status'] == 'publish' || $_GET['status'] == 'draft')) { 
				$post_status = sanitize_text_field( wp_unslash ( $_GET['status'] ) );
				$post_status_to_delete = sanitize_text_field( wp_unslash ( $_GET['status'] ) );
			}

			$args = array(
				'post_type' => 'wpsn-email',
				'post_status' => $post_status,
				'numberposts' => -1
			);

			$wpsn_emails = get_posts($args);
			$count = count($wpsn_emails);

			// Loop through each email and delete it
			foreach ($wpsn_emails as $email) {
				if ($post_status_to_delete == 'all' || ($post_status_to_delete == $email->post_status)) {
					wp_delete_post($email->ID, true); // true to force delete
				}
			}

			wpsn_admin_reload_with_alert('deletedemails', 'success', 'All '.$count.' emails deleted!', false);

		}
		
	}
	
	do_action('wpsn_admin_act_on_parameters');
	
	?>
	
	<div class="wpsn-admin-table">

			<div class="wpsn-admin-table-section">
	
				<h3>Create</h3>
				<ul style="list-style-type: disc; margin-left: 20px;">
					<li><a style="background-color: yellow" target="_new" href="https://wpsn.site/quick-start">Quick Start Guide</a></li>
					<li><a href="<?php echo esc_html($admin_url); ?>&action=create_pages_confirm">Create all WPSN pages</a> <i class="fa-solid fa-triangle-exclamation wpsn-alert"></i></li>
					<li><a href="<?php echo esc_html($admin_url); ?>&action=test_email">Send a test email to your email address</a></li>
					<?php
						$cron_alerts = esc_html(get_option('wpsn_cron_alerts'));
						if ($cron_alerts === false) { 
							$cron_alerts = 0;
							update_option('wpsn_cron_alerts', $cron_alerts);
						}
						$cron_alerts = $cron_alerts == 1 ? 'ON' : 'OFF';
					?>
					<li>Send email to <?php echo esc_html(get_site_option('admin_email')); ?> every time<br />the Email queue is processed for testing purposes - <a href="<?php echo esc_html($admin_url); ?>&action=cron_email">currently <?php echo esc_html($cron_alerts); ?></a></li>
					<li><a href="<?php echo esc_html($admin_url); ?>&first_time=1">Display welcome message</a></li>
				</ul>
			</div>
			
			<div class="wpsn-admin-table-section">
	
				<h3>Delete (be careful!)</h3>
				<ul style="list-style-type: disc; margin-left: 20px;">
					<li><a href="<?php echo esc_html($admin_url); ?>&action=delete_pages_confirm">Delete all WPSN pages</a></li>
					<li><a href="<?php echo esc_html($admin_url); ?>&action=delete_all_posts_confirm">Delete all activity posts and replies</a></li>
					<li><a href="<?php echo esc_html($admin_url); ?>&action=delete_all_alerts_confirm">Delete all alerts</a></li>
					<li><a href="<?php echo esc_html($admin_url); ?>&action=delete_all_emails_confirm">Delete all emails</a></li>
					<li><a href="<?php echo esc_html($admin_url); ?>&action=delete_wpsn_confirm">Delete all WPSN pages and data</a> <i class="fa-solid fa-triangle-exclamation wpsn-alert"></i></li>
					<?php do_action('wpsn_admin_list');; ?>
				</ul>
			</div>
			
			<div class="wpsn-admin-table-section">
	
				<h3>Support</h3>
				<p>Making you happy is the goal.<br />For any reason, please do reach out via the <a href="https://wordpress.org/support/plugin/wpsn-instant-social-network/">WPSN Support Forum</a> for:</p>
				<ul style="list-style-type: disc; margin-left: 20px;">
					<li>Questions</li>
					<li>Bug reports</li>
					<li>Suggestions and Ideas</li>
					<li>Promoting your site</li>
					<li>Or just to say hello!</li>
				</ul>
			</div>

	</div>
	
	<p>Click on each of the following to expand/collapse each section, and save each section separately.</p>
	
	<?php
	// Get an array of pages
	$pages = get_pages(array(
		'sort_column' => 'post_title',
		'sort_order' => 'ASC'
	));
	$wpsn_landing_page = 	get_option( 'wpsn_landing_page' ) 	? get_option( 'wpsn_landing_page' )	: 0;
	$wpsn_home = 			get_option( 'wpsn_home' ) 			? get_option( 'wpsn_home' ) 		: 0;
	$wpsn_activity = 		get_option( 'wpsn_activity' ) 		? get_option( 'wpsn_activity' ) 	: 0;
	$wpsn_friends = 		get_option( 'wpsn_friends' ) 		? get_option( 'wpsn_friends' ) 		: 0;
	$wpsn_edit_profile = 	get_option( 'wpsn_edit_profile' ) 	? get_option( 'wpsn_edit_profile' ) : 0;
	$wpsn_search = 			get_option( 'wpsn_search' ) 		? get_option( 'wpsn_search' ) 		: 0;
	$wpsn_alerts = 			get_option( 'wpsn_alerts' ) 		? get_option( 'wpsn_alerts' ) 		: 0;
	$wpsn_login = 			get_option( 'wpsn_login' ) 			? get_option( 'wpsn_login' ) 		: 0;
	$wpsn_signup = 			get_option( 'wpsn_signup' ) 		? get_option( 'wpsn_signup' ) 		: 0;	

	$down = 'none';
	$up = 'block';
	if ( (isset($_GET['admin_alert_from']) && $_GET['admin_alert_from'] == 'createdpages' ) ||
	     (isset($_GET['admin_alert_from']) && $_GET['admin_alert_from'] == 'deletedpages' ) ||
		 (isset($_GET['new_page']) && $_GET['new_page'] == '1' ) ) {
		
		$down = 'block';
		$up = 'none';
	}
	
	?>
	
	<div class="wpsn_admin_section wpsn_admin_section_pages">
		<p class="wpsn_admin_heading">Pages<i class="fa-solid fa-caret-down" style="display: <?php echo esc_html($down); ?>"></i><i class="fa-solid fa-caret-up" style="display: <?php echo esc_html($up); ?>;"></i></p>
		<div class="wpsn_admin_section_content" style="display: <?php echo esc_html($down); ?>;">

			<?php
			$current_permalink_structure = get_option('permalink_structure');
			if (strpos($current_permalink_structure, '%postname%') === false) {
				?>
				<p><i class="fa-solid fa-triangle-exclamation wpsn-alert"></i><strong>Important:</strong> Set your <a href="<?php echo esc_attr(admin_url('options-permalink.php')); ?>">Permalinks</a> to "Post name".</p>
			<?php } ?>
			
			<p>Create a page for each of the following, add the shortcode shown, and select the page from the drop-down lists - and save.</p>

			<div class="wpsn-admin-table-wrapper">
			
				<div class="wpsn-admin-table-row wpsn-admin-table-instructions">
					<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						TITLE
					</div>
					<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						PURPOSE
					</div>
					<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						SHORTCODE TO ADD
					</div>
					<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						SELECT PAGE...
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						Landing Page
					</div>
					<div class="wpsn-admin-table-cell">
						Page user redirected to when logged out
					</div>
					<div class="wpsn-admin-table-cell">
						-
					</div>
					<div class="wpsn-admin-table-cell">
						<select name="wpsn-landing-page" id="wpsn-landing-page" class="wpsn-core-field">
						<?php
							foreach ($pages as $page) {
								echo '<option ';
								if ($page->ID == $wpsn_landing_page) { echo ' SELECTED'; }
								echo ' value="'.esc_html($page->ID).'">'.esc_html($page->post_title).'</option>';
							}
						?>
						</select>
					</div>
				</div>		
				
				<div id="wpsn-admin-page-select">
				
					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						<?php 
						$id = wpsn_has_shortcode_in_any_page('wpsn-home');
						if ($id === false) {
							echo 'Profile Page';
						} else {
							echo '<a href="'.esc_attr( get_permalink($id) ).'">Profile Page</a>';
							echo '<div class="wpsn-admin-table-pageid">Page ID: '.esc_attr( $id ).'</div>';
						}
						?>
						</div>
						<div class="wpsn-admin-table-cell">
							Page that represents a user's profile page
						</div>
						<div class="wpsn-admin-table-cell">
							[wpsn-home]
						</div>
						<div class="wpsn-admin-table-cell">
							<select name="wpsn-home" id="wpsn-home" class="wpsn-core-field">
							<?php
								foreach ($pages as $page) {
									echo '<option ';
									if ($page->ID == $wpsn_home) { echo ' SELECTED'; }
									echo ' value="'.esc_html($page->ID).'">'.esc_html($page->post_title).'</option>';
								}
							?>
							</select>
							<?php
							if ($id === false) {
								echo '<button data-title="Profile" data-db="wpsn_home" data-shortcode="wpsn-home" class="wpsn_create_page">'.esc_attr(__('Create new page and add shortcode', 'wpsn-instant-social-network')).'</button>';
							}
							?>

						</div>
					</div>			

					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						<?php 
						$id = wpsn_has_shortcode_in_any_page('wpsn-activity');
						if ($id === false) {
							echo 'User Home Page';
						} else {
							echo '<a href="'.esc_attr(get_permalink($id)).'">User Home Page</a>';
							echo '<div class="wpsn-admin-table-pageid">Page ID: '.esc_attr($id).'</div>';
						}
						?>
						</div>
						<div class="wpsn-admin-table-cell">
							Home page to shows all user/friend's activity
						</div>
						<div class="wpsn-admin-table-cell">
							[wpsn-activity]
						</div>
						<div class="wpsn-admin-table-cell">
							<select name="wpsn-activity" id="wpsn-activity" class="wpsn-core-field">
							<?php
								foreach ($pages as $page) {
									echo '<option ';
									if ($page->ID == $wpsn_activity) { echo ' SELECTED'; }
									echo ' value="'.esc_html($page->ID).'">'.esc_html($page->post_title).'</option>';
								}
							?>
							</select>
							<?php
							if ($id === false) {
								echo '<button data-title="Home" data-db="wpsn_activity" data-shortcode="wpsn-activity" class="wpsn_create_page">'.esc_attr(__('Create new page and add shortcode', 'wpsn-instant-social-network')).'</button>';
							}
							?>
						</div>
					</div>			

					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						<?php 
						$id = wpsn_has_shortcode_in_any_page('wpsn-friends');
						if ($id === false) {
							echo 'Friends Page';
						} else {
							echo '<a href="'.esc_attr(get_permalink($id)).'">Friends Page</a>';
							echo '<div class="wpsn-admin-table-pageid">Page ID: '.esc_attr($id).'</div>';
						}
						?>
						</div>
						<div class="wpsn-admin-table-cell">
							Page where user manages their friends
						</div>
						<div class="wpsn-admin-table-cell">
							[wpsn-friends]
						</div>
						<div class="wpsn-admin-table-cell">
							<select name="wpsn-friends" id="wpsn-friends" class="wpsn-core-field">
							<?php
								foreach ($pages as $page) {
									echo '<option ';
									if ($page->ID == $wpsn_friends) { echo ' SELECTED'; }
									echo ' value="'.esc_html($page->ID).'">'.esc_html($page->post_title).'</option>';
								}
							?>
							</select>
							<?php
							if ($id === false) {
								echo '<button data-title="Friends" data-db="wpsn_friends" data-shortcode="wpsn-friends" class="wpsn_create_page">'.esc_attr(__('Create new page and add shortcode', 'wpsn-instant-social-network')).'</button>';
							}
							?>
						</div>
					</div>			

					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						<?php 
						$id = wpsn_has_shortcode_in_any_page('wpsn-profile-edit');
						if ($id === false) {
							echo 'Account Page';
						} else {
							echo '<a href="'.esc_attr(get_permalink($id)).'">Account Page</a>';
							echo '<div class="wpsn-admin-table-pageid">Page ID: '.esc_attr($id).'</div>';
						}
						?>
						</div>
						<div class="wpsn-admin-table-cell">
							Where users edit their account details
						</div>
						<div class="wpsn-admin-table-cell">
							[wpsn-profile-edit]
						</div>
						<div class="wpsn-admin-table-cell">
							<select name="wpsn-edit-profile" id="wpsn-edit-profile" class="wpsn-core-field">
							<?php
								foreach ($pages as $page) {
									echo '<option ';
									if ($page->ID == $wpsn_edit_profile) { echo ' SELECTED'; }
									echo ' value="'.esc_html($page->ID).'">'.esc_html($page->post_title).'</option>';
								}
							?>
							</select>
							<?php
							if ($id === false) {
								echo '<button data-title="Account" data-db="wpsn_edit_profile" data-shortcode="wpsn-profile-edit" class="wpsn_create_page">'.esc_attr(__('Create new page and add shortcode', 'wpsn-instant-social-network')).'</button>';
							}
							?>
						</div>
					</div>			

					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						<?php 
						$id = wpsn_has_shortcode_in_any_page('wpsn-search');
						if ($id === false) {
							echo 'Search Page';
						} else {
							echo '<a href="'.esc_attr(get_permalink($id)).'">Search Page</a>';
							echo '<div class="wpsn-admin-table-pageid">Page ID: '.esc_attr($id).'</div>';
						}
						?>
						</div>
						<div class="wpsn-admin-table-cell">
							Search for other users
						</div>
						<div class="wpsn-admin-table-cell">
							[wpsn-search]
						</div>
						<div class="wpsn-admin-table-cell">
							<select name="wpsn-search" id="wpsn-search" class="wpsn-core-field">
							<?php
								foreach ($pages as $page) {
									echo '<option ';
									if ($page->ID == $wpsn_search) { echo ' SELECTED'; }
									echo ' value="'.esc_html($page->ID).'">'.esc_html($page->post_title).'</option>';
								}
							?>
							</select>
							<?php
							if ($id === false) {
								echo '<button data-title="Search" data-db="wpsn_search" data-shortcode="wpsn-search" class="wpsn_create_page">'.esc_attr(__('Create new page and add shortcode', 'wpsn-instant-social-network')).'</button>';
							}
							?>
						</div>
					</div>				

					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						<?php 
						$id = wpsn_has_shortcode_in_any_page('wpsn-alerts');
						if ($id === false) {
							echo 'Alerts Page';
						} else {
							echo '<a href="'.esc_attr(get_permalink($id)).'">Alerts Page</a>';
							echo '<div class="wpsn-admin-table-pageid">Page ID: '.esc_attr($id).'</div>';
						}
						?>
						</div>
						<div class="wpsn-admin-table-cell">
							Activity notifications
						</div>
						<div class="wpsn-admin-table-cell">
							[wpsn-alerts]
						</div>
						<div class="wpsn-admin-table-cell">
							<select name="wpsn-alerts" id="wpsn-alerts" class="wpsn-core-field">
							<?php
								foreach ($pages as $page) {
									echo '<option ';
									if ($page->ID == $wpsn_alerts) { echo ' SELECTED'; }
									echo ' value="'.esc_html($page->ID).'">'.esc_html($page->post_title).'</option>';
								}
							?>
							</select>
							<?php
							if ($id === false) {
								echo '<button data-title="Alerts" data-db="wpsn_alerts" data-shortcode="wpsn-alerts" class="wpsn_create_page">'.esc_attr(__('Create new page and add shortcode', 'wpsn-instant-social-network')).'</button>';
							}
							?>
						</div>
					</div>
					
					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						<?php 
						$id = wpsn_has_shortcode_in_any_page('wpsn-login');
						if ($id === false) {
							echo 'Login Page';
						} else {
							echo '<a href="'.esc_attr(get_permalink($id)).'">Login Page</a>';
							echo '<div class="wpsn-admin-table-pageid">Page ID: '.esc_attr($id).'</div>';
						}
						?>
						</div>
						<div class="wpsn-admin-table-cell">
							Where users login
						</div>
						<div class="wpsn-admin-table-cell">
							[wpsn-login]
						</div>
						<div class="wpsn-admin-table-cell">
							<select name="wpsn-login" id="wpsn-login" class="wpsn-core-field">
							<?php
								foreach ($pages as $page) {
									echo '<option ';
									if ($page->ID == $wpsn_login) { echo ' SELECTED'; }
									echo ' value="'.esc_html($page->ID).'">'.esc_html($page->post_title).'</option>';
								}
							?>
							</select>
							<?php
							if ($id === false) {
								echo '<button data-title="Login" data-db="wpsn_login" data-shortcode="wpsn-login" class="wpsn_create_page">'.esc_attr(__('Create new page and add shortcode', 'wpsn-instant-social-network')).'</button>';
							}
							?>
						</div>
					</div>			

					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-cell wpsn-admin-table-header">
						<?php 
						$id = wpsn_has_shortcode_in_any_page('wpsn-signup');
						if ($id === false) {
							echo 'Sign Up Page';
						} else {
							echo '<a href="'.esc_attr(get_permalink($id)).'">Sign Up Page</a>';
							echo '<div class="wpsn-admin-table-pageid">Page ID: '.esc_attr($id).'</div>';
						}
						?>
						</div>
						<div class="wpsn-admin-table-cell">
							Where users join the site
						</div>
						<div class="wpsn-admin-table-cell">
							[wpsn-signup]
						</div>
						<div class="wpsn-admin-table-cell">
							<select name="wpsn-signup" id="wpsn-signup" class="wpsn-core-field">
							<?php
								foreach ($pages as $page) {
									echo '<option ';
									if ($page->ID == $wpsn_signup) { echo ' SELECTED'; }
									echo ' value="'.esc_html($page->ID).'">'.esc_html($page->post_title).'</option>';
								}
							?>
							</select>
							<?php
							if ($id === false) {
								echo '<button data-title="Sign Up" data-db="wpsn_signup" data-shortcode="wpsn-signup" class="wpsn_create_page">'.esc_attr(__('Create new page and add shortcode', 'wpsn-instant-social-network')).'</button>';
							}
							?>
						</div>
					</div>

					<?php
						
						// Action for more pages
						do_action('wpsn_admin_pages_action');

					?>
					
				</div>

			</div>
			
			<?php wp_nonce_field( 'wpsn_admin_nonce', 'wpsn_admin_nonce_field' ); ?>
			<div class="wpsn-admin-submit-wrapper">
				<div class="wpsn-admin-pages-submit wp-core-ui button-primary"><i class="fa-solid fa-floppy-disk"></i></div>
			</div>

		</div>
	</div>
	
	
	<?php
	// Get design options
	$wpsn_background_image_url = false;
	if (get_option('wpsn_background_image_url')) {
		$wpsn_background_image_url = get_option('wpsn_background_image_url');
	}
	$wpsn_default_dummy_avatar = plugins_url('img/dummy_avatar.jpg', __FILE__);
	if (get_option('wpsn_dummy_avatar')) {
		$wpsn_dummy_avatar = get_option('wpsn_dummy_avatar');
	} else {
		$wpsn_dummy_avatar = $wpsn_default_dummy_avatar;
		update_option('wpsn_dummy_avatar', $wpsn_default_dummy_avatar);
	}
	// User Activity / etc
	$wpsn_prompt		 					= get_option( 'wpsn_prompt' ) !== false							? get_option( 'wpsn_prompt' ) 							: 'What\'s up?';
	$wpsn_home_posts_limit 					= get_option( 'wpsn_home_posts_limit' ) !== false				? get_option( 'wpsn_home_posts_limit' ) 				: 13;
	$wpsn_home_posts_show_comments_limit 	= get_option( 'wpsn_home_posts_show_comments_limit' ) !== false	? get_option( 'wpsn_home_posts_show_comments_limit' ) 	: 5;	
	$wpsn_alert_count					 	= get_option( 'wpsn_alert_count' ) !== false					? get_option( 'wpsn_alert_count' ) 						: 100;		
	$wpsn_reply_limit					 	= get_option( 'wpsn_reply_limit' ) !== false					? get_option( 'wpsn_reply_limit' ) 						: 2;		
	$wpsn_banned		 					= get_option( 'wpsn_banned' ) !== false							? get_option( 'wpsn_banned' ) 							: '';
	$wpsn_banned_sub	 					= get_option( 'wpsn_banned_sub' ) !== false						? get_option( 'wpsn_banned_sub' ) 						: '*****';
	?>
	
	<div class="wpsn_admin_section">
		<p class="wpsn_admin_heading">Design<i class="fa-solid fa-caret-down"></i><i class="fa-solid fa-caret-up"></i></p>
		<div class="wpsn_admin_section_content">
		
			<p>
				<i class="fa-solid fa-lightbulb wpsn-tip"></i><strong>Tip:</strong> Make sure your page content width, via your theme and page settings, is set wide enough. 1000px is recommended. Some themes default to less.<br />
				<i class="fa-solid fa-lightbulb wpsn-tip"></i><strong>Tip:</strong> You can save here, and in a different window refresh to see the changes.
			</p>
			
			<div class="wpsn-admin-table-wrapper">

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Layout
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Maximum Content Width
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						$wpsn_content_max_width	= get_option( 'wpsn_content_max_width' ) !== false ? get_option( 'wpsn_content_max_width' ) : 1000;
						?>
						<input type="text" id="wpsn_content_max_width" name="wpsn_content_max_width" value="<?php echo esc_html($wpsn_content_max_width); ?>" /> (include "px" pixels or "%" for percentage, e.g. 1000px or 100%)
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Corner Radius
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						$wpsn_corner_radius	= get_option( 'wpsn_corner_radius' ) !== false ? get_option( 'wpsn_corner_radius' ) : 4;
						?>
						<input type="text" id="wpsn_corner_radius" name="wpsn_corner_radius" value="<?php echo esc_html($wpsn_corner_radius); ?>" /> (pixels)
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Menu Bar
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Display WPSN menu at top of page
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						
						$html = '<label class="switch">';
							$wpsn_menu_bar = get_option( 'wpsn_menu_bar' );
							if ($wpsn_menu_bar === false) {
								$wpsn_menu_bar = 1;
								update_option('wpsn_menu_bar', $wpsn_menu_bar);
							}
							$html .= '<input type="checkbox" id="wpsn_menu_bar"';
								if ($wpsn_menu_bar) { $html .= ' CHECKED'; }
								$html .= ' />';
							$html .= '<span class="slider round"></span>';
						$html .= '</label>';

						echo wp_kses( $html, array(
							'label' => array(
								'class' => array()
							),
							'input' => array(
								'type' => array(),
								'id' => array(),
								'checked' => array()
							),
							'span' => array(
								'class' => array()
							)
						) );
							
						?>
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Show Menu bar when logged out
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						$html = '<label class="switch">';
							$wpsn_menu_loggedout = get_option( 'wpsn_menu_loggedout' );
							if ($wpsn_menu_loggedout === false) {
								update_option( 'wpsn_menu_loggedout', 1 );
								$wpsn_menu_loggedout = 1;
							}
							$html .= '<input type="checkbox" id="wpsn_menu_loggedout"';
								if ($wpsn_menu_loggedout == 1) { $html .= ' CHECKED'; }
								$html .= ' />';
							$html .= '<span class="slider round"></span>';
						$html .= '</label>';
						echo wp_kses( $html, array(
							'label' => array(
								'class' => array()
							),
							'input' => array(
								'type' => array(),
								'id' => array(),
								'checked' => array()
							),
							'span' => array(
								'class' => array()
							)
						) );
						?>
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Show menu icon labels
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						$html = '<label class="switch">';
							$wpsn_top_bar_labels = get_option( 'wpsn_top_bar_labels' );
							if ($wpsn_top_bar_labels === false) {
								update_option( 'wpsn_top_bar_labels', 1 );
								$wpsn_top_bar_labels = 1;
							}
							$html .= '<input type="checkbox" id="wpsn_top_bar_labels"';
								if ($wpsn_top_bar_labels == 1) { $html .= ' CHECKED'; }
								$html .= ' />';
							$html .= '<span class="slider round"></span>';
						$html .= '</label>';
						echo wp_kses( $html, array(
							'label' => array(
								'class' => array()
							),
							'input' => array(
								'type' => array(),
								'id' => array(),
								'checked' => array()
							),
							'span' => array(
								'class' => array()
							)
						) );
						?>
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Show user avatar instead of home icon
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						$html = '<label class="switch">';
							$wpsn_theme_home_avatar = get_option( 'wpsn_theme_home_avatar' );
							if ($wpsn_theme_home_avatar === false) {
								update_option( 'wpsn_theme_home_avatar', 0 );
								$wpsn_theme_home_avatar = 0;
							}
							$html .= '<input type="checkbox" id="wpsn_theme_home_avatar"';
								if ($wpsn_theme_home_avatar == 1) { $html .= ' CHECKED'; }
								$html .= ' />';
							$html .= '<span class="slider round"></span>';
						$html .= '</label>';
						echo wp_kses( $html, array(
							'label' => array(
								'class' => array()
							),
							'input' => array(
								'type' => array(),
								'id' => array(),
								'checked' => array()
							),
							'span' => array(
								'class' => array()
							)
						) );
						?>
					</div>
				</div>	
					
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Side Bar
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Show on Profile and Activity
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						$html = '<label class="switch">';
							$wpsn_side_activity = get_option( 'wpsn_side_activity' );
							if ($wpsn_side_activity === false) {
								$wpsn_side_activity = 1;
								update_option('wpsn_side_activity', $wpsn_side_activity);
							}
							$html .= '<input type="checkbox" id="wpsn_side_activity"';
								if ($wpsn_side_activity) { $html .= ' CHECKED'; }
								$html .= ' />';
							$html .= '<span class="slider round"></span>';
						$html .= '</label>';

						echo wp_kses( $html, array(
							'label' => array(
								'class' => array()
							),
							'input' => array(
								'type' => array(),
								'id' => array(),
								'checked' => array()
							),
							'span' => array(
								'class' => array()
							)
						) );

						?>
					</div>
				</div>				

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Page Background
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Optional image displayed as page background
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php if ($wpsn_background_image_url !== false) { 
							$hide_class = '-';
						} else {
							$hide_class = 'wpsn_hide';
						} ?>
						<img id="wpsn_background_image" class=<?php echo esc_attr($hide_class); ?> style="width: 30px; height: 30px; margin-right: 10px;" src="<?php echo esc_attr($wpsn_background_image_url); ?>" alt="Page background image" />
						<input type="text" style="display:none;" id="wpsn_background_image_url" name="wpsn_background_image_url" value="<?php echo esc_attr($wpsn_background_image_url); ?>" style="width: 70%;" />
						<button id="wpsn_background_image_button" class="button button-primary">Change Image</button>
						<button id="wpsn_background_remove_button" class="button">Remove</button>
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Spacer at top and bottom of background
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						$wpsn_background_image_space	= get_option( 'wpsn_background_image_space' ) !== false ? get_option( 'wpsn_background_image_space' ) : 0;
						?>
						<input type="text" id="wpsn_background_image_space" name="wpsn_background_image_space" value="<?php echo esc_html($wpsn_background_image_space); ?>" /> (pixels)
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Only show background image on WPSN pages
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						$html = '<label class="switch">';
							$wpsn_background_image_specific = get_option( 'wpsn_background_image_specific' );
							if ($wpsn_background_image_specific === false) {
								$wpsn_background_image_specific = 0;
								update_option('wpsn_background_image_specific', $wpsn_background_image_specific);
							}
							$html .= '<input type="checkbox" id="wpsn_background_image_specific"';
								if ($wpsn_background_image_specific) { $html .= ' CHECKED'; }
								$html .= ' />';
							$html .= '<span class="slider round"></span>';
						$html .= '</label>';

						echo wp_kses( $html, array(
							'label' => array(
								'class' => array()
							),
							'input' => array(
								'type' => array(),
								'id' => array(),
								'checked' => array()
							),
							'span' => array(
								'class' => array()
							)
						) );

						?>
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Dummy Avatar
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Dummy avatar if not set by user
					</div>
					<div class="wpsn-admin-table-design-cell">
						<img id="wpsn_dummy_avatar" style="width: 30px; height: 30px; margin-right: 10px; border-radius: 50%;" src="<?php echo esc_attr($wpsn_dummy_avatar); ?>" alt="Dummy avatar image" />
						<input type="text" style="display:none;" id="wpsn_dummy_avatar_image_url" name="wpsn_dummy_avatar_image_url" value="<?php echo esc_attr($wpsn_dummy_avatar); ?>" style="width: 70%;" />
						<button id="wpsn_dummy_avatar_image_button" class="button button-primary">Change Image</button>
						<button id="wpsn_dummy_avatar_image_remove_button" class="button">Remove</button>
						<div class="wps_dummy_avatar_default wpsn_hide"><?php echo esc_attr($wpsn_default_dummy_avatar); ?></div>
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Mobile Responsive
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Hide profile header action buttons
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						$html = '<label class="switch">';
							$wpsn_hide_action_buttons = get_option( 'wpsn_hide_action_buttons' );
							if ($wpsn_hide_action_buttons === false) {
								$wpsn_hide_action_buttons = 1;
								update_option('wpsn_hide_action_buttons', $wpsn_hide_action_buttons);
							}
							$html .= '<input type="checkbox" id="wpsn_hide_action_buttons"';
								if ($wpsn_hide_action_buttons) { $html .= ' CHECKED'; }
								$html .= ' />';
							$html .= '<span class="slider round"></span>';
						$html .= '</label>';
						
						echo wp_kses( $html, array(
							'label' => array(
								'class' => array()
							),
							'input' => array(
								'type' => array(),
								'id' => array(),
								'checked' => array()
							),
							'span' => array(
								'class' => array()
							)
						) );
						?>
					</div>
				</div>
				
				<?php
				// Colors
				$wpsn_color_menu	 			= get_option('wpsn_color_menu') 			? esc_attr(get_option('wpsn_color_menu')) 				: '#4267B2';
				$wpsn_color_menu_text 			= get_option('wpsn_color_menu_text') 		? esc_attr(get_option('wpsn_color_menu_text')) 			: '#FFF';
				$wpsn_color_primary 			= get_option('wpsn_color_primary') 			? esc_attr(get_option('wpsn_color_primary')) 			: '#4267B2';
				$wpsn_color_primary_hover 		= get_option('wpsn_color_primary_hover') 	? esc_attr(get_option('wpsn_color_primary_hover')) 		: '#4267B2';
				$wpsn_color_primary_border		= get_option('wpsn_color_primary_border') 	? esc_attr(get_option('wpsn_color_primary_border')) 	: '';
				$wpsn_color_primary_contrast 	= get_option('wpsn_color_primary_contrast') ? esc_attr(get_option('wpsn_color_primary_contrast')) 	: '#FFF';
				$wpsn_color_secondary 			= get_option('wpsn_color_secondary') 		? esc_attr(get_option('wpsn_color_secondary')) 			: '#c6c6c6';
				$wpsn_color_active 				= get_option('wpsn_color_active') 			? esc_attr(get_option('wpsn_color_active')) 			: '#000';
				$wpsn_color_page_background 	= get_option('wpsn_color_page_background') 	? esc_attr(get_option('wpsn_color_page_background')) 	: '#EFEFEF';
				$wpsn_box_background_color 		= get_option('wpsn_box_background_color') 	? esc_attr(get_option('wpsn_box_background_color')) 	: '#FFF';
				$wpsn_color_page_text 			= get_option('wpsn_color_page_text') 		? esc_attr(get_option('wpsn_color_page_text')) 			: '#4f4f4f';	
				$wpsn_box_border_color 			= get_option('wpsn_box_border_color') 		? esc_attr(get_option('wpsn_box_border_color')) 		: '#6f6f6f';
				$wpsn_box_text_color 			= get_option('wpsn_box_text_color') 		? esc_attr(get_option('wpsn_box_text_color')) 			: '#000';
				$wpsn_box_link_color 			= get_option('wpsn_box_link_color') 		? esc_attr(get_option('wpsn_box_link_color')) 			: '#4267B2';
				$wpsn_box_link_hover_color		= get_option('wpsn_box_link_hover_color')	? esc_attr(get_option('wpsn_box_link_color')) 			: '#000';
				$wpsn_box_input_color 			= get_option('wpsn_box_input_color') 		? esc_attr(get_option('wpsn_box_input_color')) 			: '#FFF';
				$wpsn_color_cta 				= get_option('wpsn_color_cta') 				? esc_attr(get_option('wpsn_color_cta')) 				: '#228B22';
				$wpsn_color_cancel 				= get_option('wpsn_color_cancel') 			? esc_attr(get_option('wpsn_color_cancel')) 			: '#D21404';
				$wpsn_color_disabled 			= get_option('wpsn_color_disabled') 		? esc_attr(get_option('wpsn_color_disabled')) 			: '#c6c6c6';
				?>

				<h1 class="wpsn-admin-inline-heading" style="margin-top:8px">Colors</h1>
						
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						<?php if ($current_theme->get('Name') != 'WPSN: Social Network for WordPress Plugin Theme') { ?>
							<div style="font-weight: normal; font-size: 14px; margin-bottom:8px;">
								<i class="fa-solid fa-triangle-exclamation wpsn-alert"></i><strong>Warning:</strong> If you are not using the WPSN Theme, your theme might over-ride your color choices.<br />
							</div>
						<?php } ?>
						<div style="font-weight: normal; font-size: 14px;">
							<i class="fa-solid fa-lightbulb wpsn-tip"></i><strong>Tip:</strong> Apply Color Pre-Set:&nbsp; 
							<span class="wpsn-reset-colors" style="cursor:pointer; text-decoration:underline;">Default</span>,
							<span class="wpsn-reset-colors-red" style="cursor:pointer; text-decoration:underline;">Ruby</span>,
							<span class="wpsn-reset-colors-green" style="cursor:pointer; text-decoration:underline;">Emerald</span>,
							<span class="wpsn-reset-colors-blue" style="cursor:pointer; text-decoration:underline;">Sapphire</span>,
							<span class="wpsn-reset-colors-orange" style="cursor:pointer; text-decoration:underline;">Pyrite</span>,
							<span class="wpsn-reset-colors-stone" style="cursor:pointer; text-decoration:underline;">Stone</span>.
						</div>
						<div style="margin-top: 10px;font-weight: normal; font-size: 14px;">
							<i class="fa-solid fa-lightbulb wpsn-tip"></i><strong>Tip:</strong> Clear a color to set as transparent.<br />
						</div>
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Menu Bar Colors
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Menu background color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_menu" name="wpsn_color_menu" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_menu); ?>" />
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Menu text color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_menu_text" name="wpsn_color_menu_text" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_menu_text); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Button Colors
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Button background color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_primary" name="wpsn_color_primary" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_primary); ?>" />
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Button background hover color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_primary_hover" name="wpsn_color_primary_hover" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_primary_hover); ?>" />
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Button border color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_primary_border" name="wpsn_color_primary_border" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_primary_border); ?>" />
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Button text color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_primary_contrast" name="wpsn_color_primary_contrast" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_primary_contrast); ?>" />
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Secondary button background color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_secondary" name="wpsn_color_secondary" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_secondary); ?>" />
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Action/Yes button background color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_cta" name="wpsn_color_cta" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_cta); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Cancel/Off/Remove button background color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_cancel" name="wpsn_color_cancel" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_cancel); ?>" />
					</div>
				</div>	
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Disabled button background color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_disabled" name="wpsn_color_disabled" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_disabled); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Active button background color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_active" name="wpsn_color_active" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_active); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Page Colors
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Page background color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_page_background" name="wpsn_color_page_background" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_page_background); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Text on page background
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_color_page_text" name="wpsn_color_page_text" class="wp-color-picker" value="<?php echo esc_html($wpsn_color_page_text); ?>" />
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Box Colors
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Box background color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_box_background_color" name="wpsn_box_background_color" class="wp-color-picker" value="<?php echo esc_html($wpsn_box_background_color); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Box border color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_box_border_color" name="wpsn_box_border_color" class="wp-color-picker" value="<?php echo esc_html($wpsn_box_border_color); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Box text color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_box_text_color" name="wpsn_box_text_color" class="wp-color-picker" value="<?php echo esc_html($wpsn_box_text_color); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Box link color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_box_link_color" name="wpsn_box_link_color" class="wp-color-picker" value="<?php echo esc_html($wpsn_box_link_color); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Box link hover color
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_box_link_hover_color" name="wpsn_box_link_hover_color" class="wp-color-picker" value="<?php echo esc_html($wpsn_box_link_hover_color); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Box input area
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_box_input_color" name="wpsn_box_input_color" class="wp-color-picker" value="<?php echo esc_html($wpsn_box_input_color); ?>" />
					</div>
				</div>
				
				<?php
				// Fonts
				$wpsn_font_x_large 				= get_option('wpsn_font_x_large') 			? esc_attr(get_option('wpsn_font_x_large')) 			: 24;
				$wpsn_font_large 				= get_option('wpsn_font_large') 			? esc_attr(get_option('wpsn_font_large')) 				: 18;
				$wpsn_font_medium 				= get_option('wpsn_font_medium') 			? esc_attr(get_option('wpsn_font_medium')) 				: 14;
				$wpsn_font_small 				= get_option('wpsn_font_small') 			? esc_attr(get_option('wpsn_font_small')) 				: 12;
				$wpsn_font_x_small 				= get_option('wpsn_font_x_small') 			? esc_attr(get_option('wpsn_font_x_small')) 			: 10;
				$wpsn_font_xx_small 			= get_option('wpsn_font_xx_small') 			? esc_attr(get_option('wpsn_font_xx_small')) 			: 8;
				$wpsn_font_xxx_small			= get_option('wpsn_font_xxx_small') 		? esc_attr(get_option('wpsn_font_xxx_small')) 			: 4;
				?>
				
				<div class="wpsn_hide"> <!-- HIDE TEXT SIZES -->
				
					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-design-cell">
							Font Sizes <span style="font-weight: normal;"> (</span><span class="wpsn-reset-font-sizes" style="cursor:pointer; text-decoration:underline; font-weight: normal;">Reset</span><span style="font-weight: normal;">)</span>
						</div>
					</div>

					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-design-cell">
							X-Large
						</div>
						<div class="wpsn-admin-table-design-cell">
							<input type="text" id="wpsn_font_x_large" name="wpsn_font_x_large" value="<?php echo esc_html($wpsn_font_x_large); ?>" />
						</div>
					</div>
					
					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-design-cell">
							Large
						</div>
						<div class="wpsn-admin-table-design-cell">
							<input type="text" id="wpsn_font_large" name="wpsn_font_large" value="<?php echo esc_html($wpsn_font_large); ?>" />
						</div>
					</div>
					
					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-design-cell">
							Medium
						</div>
						<div class="wpsn-admin-table-design-cell">
							<input type="text" id="wpsn_font_medium" name="wpsn_font_medium" value="<?php echo esc_html($wpsn_font_medium); ?>" />
						</div>
					</div>
					
					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-design-cell">
							Small
						</div>
						<div class="wpsn-admin-table-design-cell">
							<input type="text" id="wpsn_font_small" name="wpsn_font_small" value="<?php echo esc_html($wpsn_font_small); ?>" />
						</div>
					</div>
					
					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-design-cell">
							X-Small
						</div>
						<div class="wpsn-admin-table-design-cell">
							<input type="text" id="wpsn_font_x_small" name="wpsn_font_x_small" value="<?php echo esc_html($wpsn_font_x_small); ?>" />
						</div>
					</div>
					
					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-design-cell">
							XX-Small
						</div>
						<div class="wpsn-admin-table-design-cell">
							<input type="text" id="wpsn_font_xx_small" name="wpsn_font_xx_small" value="<?php echo esc_html($wpsn_font_xx_small); ?>" />
						</div>
					</div>
					
					<div class="wpsn-admin-table-row">
						<div class="wpsn-admin-table-design-cell">
							Tiny
						</div>
						<div class="wpsn-admin-table-design-cell">
							<input type="text" id="wpsn_font_xxx_small" name="wpsn_font_xxx_small" value="<?php echo esc_html($wpsn_font_xxx_small); ?>" />
						</div>
					</div>
				
				</div> <!-- END HIDE TEXT SIZES -->
				
			</div>
				
			<?php wp_nonce_field( 'wpsn_admin_nonce', 'wpsn_admin_nonce_field' ); ?>
			<div class="wpsn-admin-submit-wrapper">
				<div class="wpsn-admin-customize-submit wp-core-ui button-primary"><i class="fa-solid fa-floppy-disk"></i></div>
			</div>
			
		</div>
	</div>
	
	<div class="wpsn_admin_section">
		<p class="wpsn_admin_heading">Member Content<i class="fa-solid fa-caret-down"></i><i class="fa-solid fa-caret-up"></i></p>
		<div class="wpsn_admin_section_content">
		
			<div class="wpsn-admin-table-wrapper">

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Activity (</span><span class="wpsn-reset-activity" style="cursor:pointer; text-decoration:underline; font-weight: normal;">Reset</span><span style="font-weight: normal;">)
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Prompt
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_prompt" name="wpsn_prompt" style="width: 100%" value="<?php echo esc_html(stripslashes($wpsn_prompt)); ?>" />
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Initial number of posts shown
					</div>
					<div class="wpsn-admin-table-design-cell">
						<select id="wpsn_home_posts_limit" name="wpsn_home_posts_limit">
							<option value="3"<?php if ($wpsn_home_posts_limit == 3) { echo " SELECTED"; } ?>>Minimal</option>
							<option value="8"<?php if ($wpsn_home_posts_limit == 8) { echo " SELECTED"; } ?>>Few</option>
							<option value="13"<?php if ($wpsn_home_posts_limit == 13) { echo " SELECTED"; } ?>>Normal</option>
							<option value="21"<?php if ($wpsn_home_posts_limit == 21) { echo " SELECTED"; } ?>>More</option>
							<option value="34"<?php if ($wpsn_home_posts_limit == 34) { echo " SELECTED"; } ?>>Maximum</option>
							<option value="9999"<?php if ($wpsn_home_posts_limit == 9999) { echo " SELECTED"; } ?>>Unlimited</option>
						</select>
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Number of posts to show comments by default
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_home_posts_show_comments_limit" name="wpsn_home_posts_show_comments_limit" value="<?php echo esc_html($wpsn_home_posts_show_comments_limit); ?>" />
					</div>
				</div>	
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Maximum depth of replies permitted
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_reply_limit" name="wpsn_reply_limit" value="<?php echo esc_html($wpsn_reply_limit); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Banned Words, comma separated list (<span class="wpsn_banned_suggest" style="cursor:pointer; text-decoration:underline; font-weight: normal;">Fill</span>)
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_banned" name="wpsn_banned" style="width: 100%" value="<?php echo esc_html($wpsn_banned); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Banned Words replacement string
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_banned_sub" name="wpsn_banned_sub" style="width: 100%" value="<?php echo esc_html($wpsn_banned_sub); ?>" />
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Allow Markdown
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						$html = '<label class="switch">';
							$wpsn_markdown = get_option( 'wpsn_markdown' );
							if ($wpsn_markdown === false) {
								$wpsn_markdown = 0;
								update_option('wpsn_markdown', $wpsn_markdown);
							}
							$html .= '<input type="checkbox" id="wpsn_markdown"';
								if ($wpsn_markdown) { $html .= ' CHECKED'; }
								$html .= ' />';
							$html .= '<span class="slider round"></span>';
						$html .= '</label>';
						$html .= '&nbsp;&nbsp;&nbsp;Usage: *bold*, **italic**, `code`, */-/+ list item';
						
						echo wp_kses( $html, array(
							'label' => array(
								'class' => array()
							),
							'input' => array(
								'type' => array(),
								'id' => array(),
								'checked' => array()
							),
							'span' => array(
								'class' => array()
							)
						) );
						?>
					</div>
				</div>

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Recent Photos (</span><span class="wpsn-reset-photos" style="cursor:pointer; text-decoration:underline; font-weight: normal;">Reset</span><span style="font-weight: normal;">)
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Number of posts to check for photos
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
							$wpsn_home_photos_check = get_option ('wpsn_home_photos_check');
							if ($wpsn_home_photos_check === false) {
								$wpsn_home_photos_check = 200;
								update_option ('wpsn_home_photos_check', $wpsn_home_photos_check);
							}
						?>
						<input type="text" id="wpsn_home_photos_check" name="wpsn_home_photos_check" value="<?php echo esc_html($wpsn_home_photos_check); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Number of posts with photos to include
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
							$wpsn_home_photos_include = get_option ('wpsn_home_photos_include');
							if ($wpsn_home_photos_include === false) {
								$wpsn_home_photos_include = 30;
								update_option ('wpsn_home_photos_include', $wpsn_home_photos_include);
							}
						?>
						<input type="text" id="wpsn_home_photos_include" name="wpsn_home_photos_include" value="<?php echo esc_html($wpsn_home_photos_include); ?>" />
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Alerts (</span><span class="wpsn-reset-alerts" style="cursor:pointer; text-decoration:underline; font-weight: normal;">Reset</span><span style="font-weight: normal;">)
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Number of Alerts to show on Alerts page
					</div>
					<div class="wpsn-admin-table-design-cell">
						<input type="text" id="wpsn_alert_count" name="wpsn_alert_count" value="<?php echo esc_html($wpsn_alert_count); ?>" />
					</div>
				</div>

			</div>
				
			<?php wp_nonce_field( 'wpsn_admin_nonce', 'wpsn_admin_nonce_field' ); ?>
			<div class="wpsn-admin-submit-wrapper">				
				<div class="wpsn-admin-activity-submit wp-core-ui button-primary"><i class="fa-solid fa-floppy-disk"></i></div>
			</div>

		</div>
	</div>
	
	<div class="wpsn_admin_section">
		<p class="wpsn_admin_heading">Email Notifications<i class="fa-solid fa-caret-down"></i><i class="fa-solid fa-caret-up"></i></p>
		<div class="wpsn_admin_section_content">
			
			<div class="wpsn-admin-table-wrapper">
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell wpsn-admin-table-header">
						Email queue processing <span style="font-weight: normal;"> (</span><span class="wpsn-reset-emails" style="cursor:pointer; text-decoration:underline; font-weight: normal;">Reset</span><span style="font-weight: normal;">)</span>
					</div>
				</div>
				
				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Seconds between processing queued emails
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						$wpsn_cron_seconds = get_option( 'wpsn_cron_seconds' );
						if ($wpsn_cron_seconds === false) {
							$wpsn_cron_seconds = 60;
							update_option('wpsn_cron_seconds', $wpsn_cron_seconds);
						}
						$wpsn_cron_count = get_option( 'wpsn_cron_count' ) ? get_option( 'wpsn_cron_count' ) : 9999;
						?>
						<input type="text" id="wpsn_cron_seconds" name="wpsn_cron_seconds" value="<?php echo esc_html($wpsn_cron_seconds); ?>" /> <i class="fa-solid fa-triangle-exclamation wpsn-alert"></i>Minimum 60 seconds. Over-ridden if you apply #4 in "Notes and Tips for Admins.
					</div>
				</div>	

				<div class="wpsn-admin-table-row">
					<div class="wpsn-admin-table-design-cell">
						Number to process each time
					</div>
					<div class="wpsn-admin-table-design-cell">
						<?php
						$wpsn_cron_count = get_option( 'wpsn_cron_count' );
						if ($wpsn_cron_count === false) {
							$wpsn_cron_count = 9999;
							update_option('wpsn_cron_count', $wpsn_cron_count);
						}
						?>
						<input type="text" id="wpsn_cron_count" name="wpsn_cron_count" value="<?php echo esc_html($wpsn_cron_count); ?>" />
					</div>
				</div>	

			</div>
				
			<?php wp_nonce_field( 'wpsn_admin_nonce', 'wpsn_admin_nonce_field' ); ?>
			<div class="wpsn-admin-submit-wrapper">
				<div class="wpsn-admin-email-submit wp-core-ui button-primary"><i class="fa-solid fa-floppy-disk"></i></div>
			</div>
			
		</div>
	</div>
	
	<div class="wpsn_admin_section">
		<p class="wpsn_admin_heading">Translations<i class="fa-solid fa-caret-down"></i><i class="fa-solid fa-caret-up"></i></p>
		<div class="wpsn_admin_section_content">
		
			<p>WPSN is currently available in the following languages (admin is only in English). Just set your site language (Settings, General) to your prefered language.</p>
			<ol>
			<li>English</li>
			<li>French - Français</li>
			<li>Spanish - Español</li>
			<li>German - Deutsch</li>
			<li>Hindi - हिंदी</li>
			</ol>
			<p>If you can improve the translations, I'd be glad to do so with your suggestions - contact me (Simon) via <a href=https://wpsn.site">https://wpsn.site</a>.</p>
			<p>If you would like a new language added, please request in the same way - they are added when asked for.</p>
			<p>Current locale: <?php echo esc_html(get_locale()); ?></p>
			
		</div>
	</div>
	
	<?php 
		// PRO: Admin hook
		do_action('wpsn_admin_page_hook');
	?>
	
	<div class="wpsn_admin_section">
		<p class="wpsn_admin_heading">Notes and Tips for Admins<i class="fa-solid fa-caret-down"></i><i class="fa-solid fa-caret-up"></i></p>
		<div class="wpsn_admin_section_content">
		
			<?php 
				include( plugin_dir_path( __FILE__ ) . '../../../wp-load.php');
				$path = rtrim(ABSPATH, '/') . '/';
				$path = str_replace('\\', '/', $path);
			?>
	
			<div style="margin-right:20px;">
			<div style="margin-right:20px;">
			
				<h2 class="wpsn-admin-sub-menu">1. Tips for your Theme</h2>
				<p>The goal is to be compatible with all themes, but a few tweaks may be needed. Check out the <a href="https://wpsn.site/themes/">Theme Tips</a> to customise your theme.</p>
				
				<h2 class="wpsn-admin-sub-menu">2. Showing and Hiding Menu Items (if users are or aren't logged in)</h2>
				<p>The WPSN official theme handles it's own menu, and visibility of menu items if a user is logged in or not.</p>
				<p>If you are using your own theme, you may want to provide a dynamic menu where menu items are shown (or not) depending on whether a user is logged in or not automatically. For example, you want to show the Login page when they are logged out, but not when they are logged in.</p>
				<p>How you do this, depends on your theme, and potentially other plugins, you have enabled.</p>
				<ol>
					<li>For block enabled themes (like Twenty Twenty-Four), try the <a href="<?php echo esc_url(admin_url()); ?>plugin-install.php?s=Block%2520Visibility&tab=search&type=term">Block Visibility</a> plugin (you may have to enabled "Full Control" mode via the plugin's General Settings).</li>
					<li>For other themes, which have Appearance->Menus enabled, try the <a href="<?php echo esc_url(admin_url()); ?>/plugin-install.php?s=If%2520Menu%2520%25E2%2580%2593%2520Visibility%2520control%2520for%2520Menus&tab=search&type=term">If Menu – Visibility control for Menus</a>.</p>
				</ol>

				<h2 class="wpsn-admin-sub-menu">3. Hiding the WordPress "admin bar" across the top of the screen</h2>
				<p>It's just not needed. You can hide the admin bar by using the <a href="<?php echo esc_url(admin_url()); ?>plugin-install.php?s=Hide%2520Admin%2520Bar%2520Based%2520on%2520User%2520Roles&tab=search&type=term">Hide Admin Bar Based on User Roles</a> plugin.</p>
				
				<h2 class="wpsn-admin-sub-menu">4. Scheduling emails reliably</h2>
				<p>WPSN uses WordPress to send out email notifications regularly (once per minute), via a queue to throttle load on your server. It does so using the in-built WordPress "Cron" system. However, this relies on traffic to your site. When no traffic is received, the in-build "Cron" system may not operate regularly and on time.</p>
				<p>Whilst this may not cause you problems, you can make the schedule more reliable by setting up a real cron job on your server.</p>
				<ol>
					<li>To prevent wp-cron.php from running on every page load, add the following line to you wp-config.php file which is probably in the <?php echo esc_html($path); ?> folder:<br />
						<pre style="font-weight: bold">define('DISABLE_WP_CRON', true);</pre>
						</li> 
					<li>Log in to cPanel or equivalent control panel provided by your hosting provider.</li>
					<li>Either, go to the "Cron Jobs" section and add a new cron job, setting the time interval to every minute, using the following command to trigger the WP-Cron system:<br />
						<pre style="font-weight: bold">cd <?php echo esc_html($path); ?> && php -q wp-cron.php</pre>
						</li>
					<li>Or, if you prefer to edit your server's crontab (cron table) file directly, add the following line to the frequency to every 60 seconds ("* * * * *"):<br />
						<pre style="font-weight: bold">* * * * * wget -q -O - <?php echo esc_html(home_url()) ?>/wp-cron.php?doing_wp_cron >/dev/null 2>&1</pre>
						</li> 
					<li><i class="fa-solid fa-triangle-exclamation wpsn-alert"></i>For each of the above, check the path to your WordPress installation is correct, and change to the frequency you want.</li>
				</ol>
								
				<h2 class="wpsn-admin-sub-menu">5. After installing/upgrading a WPSN plugin or WPSN Theme</h2>
				<p>Always purge your server's cached files.</p>
				
			</div>
		</div>
	</div>
	<?php
}

function wpsn_admin_reload_with_alert($from, $status, $text, $first_time) {
	
	$wpsn_alert_nonce = wp_create_nonce( 'wpsn_alert_nonce' );
	$first_time_param = $first_time ? $first_time_param = 'first_time=1' : '';
	
	$url = '/wp-admin/admin.php?'.esc_html($first_time_param).'&page=wpsn-instant-social-network%2Fwpsn-admin-page.php&admin_alert_from='.esc_html($from).'&admin_alert_status='.esc_html($status).'&admin_alert=' . urlencode($text) . '&wpsn_alert_nonce='.urlencode($wpsn_alert_nonce);
	$html = '<div class="notice notice-'.$status.' is-dismissable" style="margin-left: 0">';
		$html .= '<p>'.stripslashes($text).'</p>';
	$html .= '</div>';
	
	echo wp_kses_post($html);

}

// Function to create a new page and add content to it
function wpsn_create_new_page_with_content($title, $post_content) {
    // Create the new page
    $new_page = array(
        'post_title'    => $title,
        'post_content'  => $post_content,
        'post_status'   => 'publish',
        'post_type'     => 'page',
    );

    // Insert the post into the database
    $page_id = wp_insert_post($new_page);

    // Check if the page was created successfully
    if (!is_wp_error($page_id)) {
        // Return the page ID
        return $page_id;
    } else {
        // Handle the error
        return $page_id->get_error_message();
    }
}

// Check if a shortcode exists on a page
function wpsn_has_shortcode_in_any_page($shortcode) {
    // Query all pages
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => -1 // Retrieve all pages
    );
    
    $pages = get_posts($args);
    // Loop through all pages and check for the shortcode
    foreach ($pages as $page) {
		$pos = strpos( $page->post_content, '['.$shortcode);
        if ( $pos !== false && $pos >= 0) {
            return $page->ID; // Shortcode found
		}
    }
    
    return false; // Shortcode not found in any page
}

function wpsn_admin_get_current_url() {

	$request_uri = sanitize_url($_SERVER['REQUEST_URI']);
	$https = isset($_SERVER['HTTPS']) ? sanitize_text_field($_SERVER['HTTPS']) : 'http';
	$http_host = sanitize_text_field($_SERVER['HTTP_HOST']);
	$get_page = sanitize_text_field($_GET['page']);
	
	$scheme = $https === 'on' ? 'https' : 'http';
	$current_path = strtok($request_uri, '?');
	$current_url = $scheme . '://' . $http_host . $current_path . '?page=' . wp_unslash ($get_page);
		
	return $current_url;

}	
?>