<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* FUNCTIONS */

function wpsn_load_textdomain() {
    $domain = 'wpsn-instant-social-network';
    $locale = determine_locale(); // This gets the current locale
    $lang = substr($locale, 0, 2); // Extract the general language code

    // Paths to the .mo files
    $specific_mo_file = dirname(plugin_basename(__FILE__)) . '/languages/' . $domain . '-' . $locale . '.mo';
    $general_mo_file = dirname(plugin_basename(__FILE__)) . '/languages/' . $domain . '-' . $lang . '.mo';

    // Attempt to load the specific locale file
    if (file_exists(WP_PLUGIN_DIR . '/' . $specific_mo_file)) {
        load_textdomain($domain, WP_PLUGIN_DIR . '/' . $specific_mo_file);
    }
    // Attempt to load the general language file if specific one is not found
    if (file_exists(WP_PLUGIN_DIR . '/' . $general_mo_file)) {
        load_textdomain($domain, WP_PLUGIN_DIR . '/' . $general_mo_file);
    }
}
add_action('plugins_loaded', 'wpsn_load_textdomain');

// Add WPSN Menu

function wpsn_admin_menu() {
	$menu_slug = add_menu_page( 'WP Social Network', 'WPSN Admin', 'manage_options', 'wpsn-instant-social-network/wpsn-admin-page.php', 'wpsn_admin_page', '/wp-content/plugins/wpsn-instant-social-network/img/wpsn_menu_logo.png', 6  );
	//'dashicons-format-status'
	
    add_submenu_page(
        'wpsn-instant-social-network/wpsn-admin-page.php',
        'Posts/Replies',
        'Posts/Replies',
        'manage_options',
        'edit.php?post_type=wpsn-feed'
    );
	
    add_submenu_page(
        'wpsn-instant-social-network/wpsn-admin-page.php',
        'Alerts',
        'Alerts',
        'manage_options',
        'edit.php?post_type=wpsn-alert'
    );
	
    add_submenu_page(
        'wpsn-instant-social-network/wpsn-admin-page.php',
        'Emails',
        'Emails',
        'manage_options',
        'edit.php?post_type=wpsn-email'
    );

}

// Add Settings to Plugins page

function wpsn_plugin_add_settings_link($links) {
    // Add the settings link
    $settings_link = '<a href="options-general.php?page=wpsn-instant-social-network/wpsn-admin-page.php">Settings</a>';
    array_unshift($links, $settings_link); // Add the link to the beginning of the array
    return $links;
}

// WordPress Admin - Customise View Posts
function wpsn_add_custom_emails_columns($columns) {
    // Insert a new column after the 'Title' column
    $columns['wpsn_from'] = 'From';
    $columns['wpsn_to'] = 'To';
	$columns['wpsn_action'] = 'Action';
    return $columns;
}
function wpsn_custom_emails_column_content($column_name, $post_id) {
    if ($column_name == 'wpsn_from') {
		$from = get_post_field('post_author', $post_id);
		$from_email = get_userdata($from)->user_email;
		echo '<a href="mailto:'.esc_html($from_email).'">'.esc_html(get_user_meta( $from, 'first_name', true )) . ' ' . esc_html(get_user_meta( $from, 'last_name', true )).'</a>';
    }
	if ($column_name == 'wpsn_to') {
		$to = get_post_field('post_parent', $post_id);
		$to_email = get_userdata($to)->user_email;
		echo '<a href="mailto:'.esc_html($to_email).'">'.esc_html(get_user_meta( $to, 'first_name', true )) . ' ' . esc_html(get_user_meta( $to, 'last_name', true )).'</a>';
	}
	if ($column_name == 'wpsn_action') {
		$status = get_post_field('post_status', $post_id);
		if ($status == 'publish') {
			echo '<input class="button action wpsn_admin_email_send" data-post-id="'.esc_html($post_id).'" value="Send again" style="text-align: center; width: 85px" />';
		} else {
			echo '<input class="button action wpsn_admin_email_send" data-post-id="'.esc_html($post_id).'" value="Send now" style="text-align: center; width: 85px" />';
		}
	}
}
function wpsn_add_content_above_posts_table($which) {
    global $typenow;
    // Ensure we are only adding the content at the top of the table and for the specific post type
    if ($typenow == 'wpsn-email' && $which == 'bottom') {
		$scheduled = wp_next_scheduled('wpsn_custom_cron_job');
		$now = time();
		$diff = $scheduled-$now;
		if ($diff < 0) { $diff = 0; }
		if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
			echo 'WP Cron is disabled - have you set it on your server? (see WPSN Admin - "Notes and Tips for Admins")';
		} else {
			echo 'Server time: '.esc_html(gmdate('h:i:sa', time())).', next run: '.esc_html(gmdate('h:i:sa', $scheduled)).', '.esc_html($diff).' seconds to next run [<a href="'.esc_html('edit.php?post_type=wpsn-email').'">Refresh</a>]';
		}
    }
}


// Custom Post Types

function wpsn_posts_post_type() {
	
    $args = array(
        'public' => true,
        'label'  => 'WPSN Posts',
        'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ), // Customize supported features
        'show_in_menu' => false
    );
    register_post_type( 'wpsn-feed', $args );

}

function wpsn_posts_alert_type() {
	
    $args = array(
        'public' => true,
        'label'  => 'WPSN Alerts',
        'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ), // Customize supported features
        'show_in_menu' => false
    );
    register_post_type( 'wpsn-alert', $args );

}

function wpsn_posts_email_type() {
	
    $args = array(
        'public' => true,
        'label'  => 'WPSN Emails',
        'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ), // Customize supported features
        'show_in_menu' => false
    );
    register_post_type( 'wpsn-email', $args );

}

function wpsn_custom_post_status(){
	register_post_status( 'unread', array(
		'label'                     => __( 'Unread', 'wpsn-instant-social-network' ),
		'public'                    => true,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true
	) );
}

function wpsn_restrict_custom_post_type_access() {
    if (is_singular('wpsn-feed') || is_singular('wpsn-alert') || is_singular('wpsn-email')) {
		if ( !current_user_can( 'manage_options' ) ) {
			wp_redirect(home_url());
			exit;
		}
    }
}

function wpsn_admin_init() {
	// Font Awesome script
	wp_enqueue_script( 'font-awesome-loader', plugins_url('js/fontawesome.min.js', __FILE__), array(), '6.5.2', true );
	wp_enqueue_script( 'font-awesome-solid', plugins_url('js/solid.js', __FILE__), array(), '6.5.2', true );
}

function wpsn_init() {

	// Font Awesome script
	wp_enqueue_script( 'font-awesome-loader', plugins_url('js/fontawesome.min.js', __FILE__), array(), '6.5.2', true );
	wp_enqueue_script( 'font-awesome-solid', plugins_url('js/solid.js', __FILE__), array(), '6.5.2', true );

	// Croppie
	wp_enqueue_script( 'croppie-js', plugins_url('js/croppie.mins.js', __FILE__), array(), '2.6.5', true );
    wp_enqueue_style( 'croppie-css', plugins_url( 'css/croppie.min.css', __FILE__ ), array(), '2.6.5', 'screen' );   

    // CSS
    wp_enqueue_style( 'front-css', plugins_url( 'css/front.css', __FILE__ ), array(), '1.0.0', 'screen' );   

    // Javascript
    $nonce = wp_create_nonce( 'wpsn-security-nonce' );

	// Library
	wp_enqueue_script('wpsn-js', plugins_url('js/wpsn.js', __FILE__), array('jquery'), '1.0.0', true);	
	wp_localize_script( 'wpsn-js', 'wpsn_ajax', array( 
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'security'  => $nonce
	));
	
	// Profile
	$profile_nonce = wp_create_nonce( 'wpsn_action' );
	wp_enqueue_script('wpsn-profile', plugins_url('js/profile.js', __FILE__), array('jquery'), '1.0.0', true);	
	wp_localize_script( 'wpsn-profile', 'wpsn_profile_ajax', array( 
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'security'  => $nonce,
		'profile_nonce' => $profile_nonce,
		'lang_change' => __('Change', 'wpsn-instant-social-network'),
		'lang_save' => __('Save', 'wpsn-instant-social-network'),
		'lang_save_error' => __('Problem encountered - please try zooming in and trying again.', 'wpsn-instant-social-network'),
		'lang_save_error2' => __('Chosen image area too big, try zooming in.', 'wpsn-instant-social-network'),
		'wpsn_current_user_id' => wp_kses($_SESSION['wpsn_current_user_id'], 'strip')
	));
	
	// Story
	wp_enqueue_script('wpsn-story', plugins_url('js/story.js', __FILE__), array('jquery'), '1.0.0', true);	
	wp_localize_script( 'wpsn-story', 'wpsn_story_ajax', array( 
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'security'  => $nonce,
		'wpsn_current_page_id' => get_the_ID(),
		'lang_no_recent_photos' => __('No recent photos.', 'wpsn-instant-social-network'),
		'lang_post' =>  __('Post', 'wpsn-instant-social-network'),
		'lang_reply' =>  __('Reply', 'wpsn-instant-social-network'),
		'lang_edit' =>  __('Edit', 'wpsn-instant-social-network'),
		'lang_delete' =>  __('Delete', 'wpsn-instant-social-network'),
		'lang_just_now' => __('just now', 'wpsn-instant-social-network'),
		'wpsn_banned' => get_option( 'wpsn_banned' ) !== false ? get_option( 'wpsn_banned' ) : '',
		'wpsn_banned_sub' => get_option( 'wpsn_banned_sub' ) !== false ? get_option( 'wpsn_banned_sub' ) : '*****',
	));	

	// Friends
	wp_enqueue_script('wpsn-friends', plugins_url('js/friends.js', __FILE__), array('jquery'), '1.0.0', true);	
	wp_localize_script( 'wpsn-friends', 'wpsn_friends_ajax', array( 
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'security'  => $nonce,
		'nonce' => esc_html(wp_kses($_SESSION['wpsn_nonce'], 'strip')),
		'wpsn_current_user_id' => wp_kses($_SESSION['wpsn_current_user_id'], 'strip'),
		'lang_no_friends' => __('No friends.', 'wpsn-instant-social-network'),
		'lang_last_active' => __('Last active', 'wpsn-instant-social-network'),
		'lang_remove' => __('Remove', 'wpsn-instant-social-network'),
		'lang_done' => __('Done', 'wpsn-instant-social-network'),
		'lang_accept' => __('Accept', 'wpsn-instant-social-network'),
		'lang_reject' => __('Reject', 'wpsn-instant-social-network'),
		'lang_cancel' => __('Cancel', 'wpsn-instant-social-network'),
		'lang_request_sent' => __('Request sent', 'wpsn-instant-social-network'),
	));	

	// Alerts
	wp_enqueue_script('wpsn-alerts', plugins_url('js/alerts.js', __FILE__), array('jquery'), '1.0.0', true);	
	wp_localize_script( 'wpsn-alerts', 'wpsn_alerts_ajax', array( 
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'security'  => $nonce,
		'nonce' => esc_html(wp_kses($_SESSION['wpsn_nonce'], 'strip'))
	));	

	// Login and Signup
	wp_enqueue_script('wpsn-login', plugins_url('js/login.js', __FILE__), array('jquery'), '1.0.0', true);	
	wp_localize_script( 'wpsn-login', 'wpsn_login_ajax', array( 
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'security'  => $nonce
	));

	// Search
	wp_enqueue_script('wpsn-search', plugins_url('js/search.js', __FILE__), array('jquery'), '1.0.0', true);	
	wp_localize_script( 'wpsn-search', 'wpsn_search_ajax', array( 
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'security'  => $nonce,
		'lang_3_letters' => __('Enter at least 3 letters...', 'wpsn-instant-social-network'),
	));

}

// Enqueue admin script
function wpsn_enqueue_admin_script() {
    // Enqueue script only on specific admin pages
    $screen = get_current_screen();
	$nonce = wp_create_nonce( 'wpsn-security-nonce' );

    if ( $screen && ('toplevel_page_wpsn-instant-social-network/wpsn-admin-page' === $screen->id || 'edit-wpsn-email' === $screen->id) ) {
		wp_enqueue_script('wpsn-admin', plugins_url('js/admin.js', __FILE__), array('jquery', 'wp-color-picker'), '1.0.0', true);
		wp_localize_script( 'wpsn-admin', 'wpsn_ajax', array( 
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'security'  => $nonce
		));
    }
	
	wp_enqueue_style('admin-css', plugins_url('css/admin.css', __FILE__), array(), '1.0.0', 'all' );  
}

// Enqueue media scripts
function wpsn_enqueue_media() {
	if (is_admin()) {
		wp_enqueue_media();
	}
}

// Enqueue scripts and styles for color picker
function wpsn_enqueue_color_picker() {
    wp_enqueue_style('wp-color-picker');
}

function wpsn_page($page, $just_id = false) {
	
	$id = 0;
	$id = apply_filters('wpsn_page_filter', 0, $page);

	if ($id == 0) {
				
		switch ($page) {
			case 'landing-page':
				$id = get_option( 'wpsn_landing_page' ) 	? get_option( 'wpsn_landing_page' ) : 0;
				break;
			case 'home':
				$id = get_option( 'wpsn_home' ) 			? get_option( 'wpsn_home' ) 		: 0;
				break;
			case 'activity':
				$id = get_option( 'wpsn_activity' ) 		? get_option( 'wpsn_activity' ) 	: 0;
				break;
			case 'friends':
				$id = get_option( 'wpsn_friends' ) 			? get_option( 'wpsn_friends' ) 		: 0;
				break;
			case 'edit-profile':
				$id = get_option( 'wpsn_edit_profile' )		? get_option( 'wpsn_edit_profile' ) : 0;
				break;
			case 'alerts':
				$id = get_option( 'wpsn_alerts' ) 			? get_option( 'wpsn_alerts' ) 		: 0;
				break;
			case 'search':
				$id = get_option( 'wpsn_search' ) 			? get_option( 'wpsn_search' ) 		: 0;
				break;
			case 'login':
				$id = get_option( 'wpsn_login' ) 			? get_option( 'wpsn_login' ) 		: 0;
				break;
			case 'signup':
				$id = get_option( 'wpsn_signup' ) 			? get_option( 'wpsn_signup' ) 		: 0;
				break;
			default:
				$id = 0;
		}

	}
	
	if (!$just_id) {
		if ($id == 0) {
			$ret = home_url();
		} else {
			$ret = get_permalink($id);
		}
	} else {
		$ret = $id;
	}
	
	return $ret;
		
}

function wpsn_custom_html() {
	
	global $current_user;

	if (wp_verify_nonce( sanitize_text_field( wp_unslash ( esc_html (sanitize_text_field($_SESSION['wpsn_nonce'])) ) ), 'wpsn_nonce' )) {
		
		$wpsn_landing_page = 	get_option( 'wpsn_landing_page' ) 	? get_option( 'wpsn_landing_page' )	: 0;
		$wpsn_home = 			get_option( 'wpsn_home' ) 			? get_option( 'wpsn_home' ) 		: 0;
		$wpsn_activity = 		get_option( 'wpsn_activity' ) 		? get_option( 'wpsn_activity' ) 	: 0;
		$wpsn_friends = 		get_option( 'wpsn_friends' ) 		? get_option( 'wpsn_friends' ) 		: 0;
		$wpsn_edit_profile = 	get_option( 'wpsn_edit_profile' ) 	? get_option( 'wpsn_edit_profile' ) : 0;
		$wpsn_search = 			get_option( 'wpsn_search' ) 		? get_option( 'wpsn_search' ) 		: 0;
		$wpsn_alerts = 			get_option( 'wpsn_alerts' ) 		? get_option( 'wpsn_alerts' ) 		: 0;
		$wpsn_login = 			get_option( 'wpsn_login' ) 			? get_option( 'wpsn_login' ) 		: 0;
		$wpsn_signup = 			get_option( 'wpsn_signup' ) 		? get_option( 'wpsn_signup' ) 		: 0;	

		$wpsn_landing_page = 	$wpsn_landing_page 	? get_permalink($wpsn_landing_page) : site_url();
		$wpsn_home = 			$wpsn_home 			? get_permalink($wpsn_home) 		: site_url();
		$wpsn_activity = 		$wpsn_activity 		? get_permalink($wpsn_activity) 	: site_url();
		$wpsn_friends = 		$wpsn_friends 		? get_permalink($wpsn_friends) 		: site_url();
		$wpsn_edit_profile = 	$wpsn_edit_profile 	? get_permalink($wpsn_edit_profile) : site_url();
		$wpsn_search = 			$wpsn_search 		? get_permalink($wpsn_search) 		: site_url();
		$wpsn_alerts = 			$wpsn_alerts 		? get_permalink($wpsn_alerts) 		: site_url();
		$wpsn_login = 			$wpsn_login 		? get_permalink($wpsn_login) 		: site_url();
		$wpsn_signup = 			$wpsn_signup 		? get_permalink($wpsn_signup) 		: site_url();

		$wpsn_home_posts_limit = 				get_option( 'wpsn_home_posts_limit' ) !== false					? get_option( 'wpsn_home_posts_limit' ) 				: 13;
		$wpsn_home_posts_show_comments_limit = 	get_option( 'wpsn_home_posts_show_comments_limit' ) !== false	? get_option( 'wpsn_home_posts_show_comments_limit' ) 	: 5;	

		$viewing_user_id = isset($_GET['uid']) ? sanitize_text_field( wp_unslash ( $_GET['uid'] ) ) : $current_user->ID;
		?>
		<!-- WPSN values -->
		<div class="wpsn_hide">
			
			<div id="wpsn_viewing_user_id"><?php echo esc_html($viewing_user_id); ?></div>
			<div id="wpsn_current_page_id"><?php echo esc_html(get_queried_object_id()); ?></div>
			<div id="wpsn_landing_page"><?php echo esc_html($wpsn_landing_page); ?></div>
			<div id="wpsn_home"><?php echo esc_html($wpsn_home); ?></div>
			<div id="wpsn_activity"><?php echo esc_html($wpsn_activity); ?></div>
			<div id="wpsn_friends"><?php echo esc_html($wpsn_friends); ?></div>
			<div id="wpsn_edit_profile"><?php echo esc_html($wpsn_edit_profile); ?></div>
			<div id="wpsn_search"><?php echo esc_html($wpsn_search); ?></div>
			<div id="wpsn_alerts"><?php echo esc_html($wpsn_alerts); ?></div>
			<div id="wpsn_login"><?php echo esc_html($wpsn_login); ?></div>
			<div id="wpsn_signup"><?php echo esc_html($wpsn_signup); ?></div>
			<div id="wpsn_home_url"><?php echo esc_url(home_url()); ?></div>
			<div id="wpsn_home_posts_limit"><?php echo esc_html($wpsn_home_posts_limit); ?></div>
			<div id="wpsn_home_posts_show_comments_limit"><?php echo esc_html($wpsn_home_posts_show_comments_limit); ?></div>
			
			<?php
				// Filter to add reference data
				global $wpsn_allowed_tags;
				echo wp_kses(apply_filters('wpsn_custom_html_filter', ''), $wpsn_allowed_tags);
			?>

		</div>
		
		<!-- Confirm Post Delete -->
		<div id="wpsn_confirm" class="wpsn_hide">
			<div id="wpsn_confirm_inner">
				<div class="wpsn_delete_id wpsn_hide"></div>
				<div class="wpsn_label"><p><strong><?php esc_html_e('Are you sure?', 'wpsn-instant-social-network'); ?></strong></p><p><?php esc_html_e('This post, and any replies, will all be deleted!', 'wpsn-instant-social-network'); ?></p></div>
				<div id="wpsn_confirm_actions">
					<div class="wpsn_confirm_actions_buttons">
						<div class="wpsn-button-submit wpsn-button-primary wpsn-confirm-post-delete-yes"><i class="fa-solid fa-check"></i><span class="wpsn_button_label">&nbsp;&nbsp;<?php esc_html_e('Yes', 'wpsn-instant-social-network'); ?></span></div>
						<div class="wpsn-button-submit wpsn-confirm-post-delete-no"><i class="fa-solid fa-xmark"></i><span class="wpsn_button_label">&nbsp;&nbsp;<?php esc_html_e('No', 'wpsn-instant-social-network'); ?></span></div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- View all post images -->
		<div id="wpsn_show_images" class="wpsn_post_popup wpsn_hide">
			<div class="wpsn_show_images_inner">
				<div class="wpsn_show_images_cancel"><div class="wpsn_show_images_cancel_button"><i class="fa-solid fa-xmark"></i></div></div>
				<div class="wpsn_show_images_container"></div>
			</div>
		</div>

    <?php		
	}

}

function wpsn_su_allow_subscriber_to_uploads() {
    $subscriber = get_role('subscriber');

    if ( ! $subscriber->has_cap('upload_files') ) {
        $subscriber->add_cap('upload_files');
    }
}
add_action('admin_init', 'wpsn_su_allow_subscriber_to_uploads');

/* LIBRARY */

function wpsn_get_display_name($id) {
	
	$name = get_user_meta($id, 'first_name', true).' '.get_user_meta($id, 'last_name', true);
	if ($name == ' ') {
		$name = get_user_meta($id, 'user_email', true);
		$name = strstr($name, '@', true);
	}
	
	return $name;

}

function wpsn_are_friends($uid1, $uid2) {
	$wpsn_friends = get_user_meta($uid1, 'wpsn_friends', true);
	$found = false;
	if ($wpsn_friends) {
		foreach ($wpsn_friends as $friend) {
			if ($friend['ID'] == $uid2) { $found = true; }
		}
	}
	if ($uid1 == $uid2) { $found = true; }
	return $found;
}

function wpsn_get_avatar($id) {
	
	$url = get_user_meta($id, 'wpsn_custom_avatar', true);
	if (!$url) {
		$option = get_option('wpsn_dummy_avatar');
		if (!$option) {
			$url = plugins_url('img/dummy_avatar.jpg', __FILE__);
		} else {
			$url = $option;
		}
	} 
	
	return $url;
}

// Custom colors
function wpsn_add_custom_css() {
	
	$wpsn_content_max_width			= get_option('wpsn_content_max_width') 			? esc_attr(get_option('wpsn_content_max_width')) 					: '1000px';
	$wpsn_corner_radius				= get_option('wpsn_corner_radius') !== false	? esc_attr(get_option('wpsn_corner_radius')) 						: '4';
	$wpsn_color_menu	 			= get_option('wpsn_color_menu') 				? esc_attr(get_option('wpsn_color_menu')) 							: '#4267B2';
	$wpsn_color_menu_text	 		= get_option('wpsn_color_menu_text') 			? esc_attr(get_option('wpsn_color_menu_text')) 						: '#FFF';
	$wpsn_color_primary 			= get_option('wpsn_color_primary') 				? esc_attr(get_option('wpsn_color_primary')) 						: '#4267B2';
	$wpsn_color_primary_hover		= get_option('wpsn_color_primary_hover') 		? esc_attr(get_option('wpsn_color_primary_hover')) 					: '#4267B2';
	$wpsn_color_primary_border		= get_option('wpsn_color_primary_border') 		? '1px solid '.esc_attr(get_option('wpsn_color_primary_border'))	: 'none';
	$wpsn_color_primary_contrast 	= get_option('wpsn_color_primary_contrast') 	? esc_attr(get_option('wpsn_color_primary_contrast')) 				: '#FFF';
	$wpsn_color_secondary 			= get_option('wpsn_color_secondary') 			? esc_attr(get_option('wpsn_color_secondary')) 						: '#c6c6c6';
	$wpsn_color_active 				= get_option('wpsn_color_active') 				? esc_attr(get_option('wpsn_color_active')) 						: '#000';
	$wpsn_color_page_background 	= get_option('wpsn_color_page_background') 		? esc_attr(get_option('wpsn_color_page_background')) 				: '#EFEFEF';
	$wpsn_background_image_space 	= get_option('wpsn_background_image_space') 	? esc_attr(get_option('wpsn_background_image_space')) 				: '0';
	$wpsn_color_page_text 			= get_option('wpsn_color_page_text') 			? esc_attr(get_option('wpsn_color_page_text')) 						: '#4f4f4f';
	$wpsn_box_background_color 		= get_option('wpsn_box_background_color') 		? esc_attr(get_option('wpsn_box_background_color')) 				: '#FFF';
	$wpsn_box_border_color 			= get_option('wpsn_box_border_color') 			? '1px solid '.esc_attr(get_option('wpsn_box_border_color')) 		: 'none';
	$wpsn_box_text_color 			= get_option('wpsn_box_text_color') 			? esc_attr(get_option('wpsn_box_text_color')) 						: '#000';
	$wpsn_box_link_color 			= get_option('wpsn_box_link_color') 			? esc_attr(get_option('wpsn_box_link_color')) 						: '#4267B2';
	$wpsn_box_link_hover_color 		= get_option('wpsn_box_link_hover_color') 		? esc_attr(get_option('wpsn_box_link_hover_color')) 				: '#000';
	$wpsn_box_input_color 			= get_option('wpsn_box_input_color') 			? esc_attr(get_option('wpsn_box_input_color')) 						: '#FFF';
	$wpsn_color_cta 				= get_option('wpsn_color_cta') 					? esc_attr(get_option('wpsn_color_cta')) 							: '#228B22';
	$wpsn_color_cancel 				= get_option('wpsn_color_cancel') 				? esc_attr(get_option('wpsn_color_cancel')) 						: '#D21404';
	$wpsn_color_disabled 			= get_option('wpsn_color_disabled') 			? esc_attr(get_option('wpsn_color_disabled')) 						: '#c6c6c6';
	$wpsn_font_x_large 				= get_option('wpsn_font_x_large') 				? esc_attr(get_option('wpsn_font_x_large')) 						: 24;
	$wpsn_font_large 				= get_option('wpsn_font_large') 				? esc_attr(get_option('wpsn_font_large')) 							: 18;
	$wpsn_font_medium 				= get_option('wpsn_font_medium') 				? esc_attr(get_option('wpsn_font_medium')) 							: 14;
	$wpsn_font_small 				= get_option('wpsn_font_small') 				? esc_attr(get_option('wpsn_font_small')) 							: 12;
	$wpsn_font_x_small 				= get_option('wpsn_font_x_small') 				? esc_attr(get_option('wpsn_font_x_small')) 						: 10;
	$wpsn_font_xx_small 			= get_option('wpsn_font_xx_small') 				? esc_attr(get_option('wpsn_font_xx_small')) 						: 8;
	$wpsn_font_xxx_small			= get_option('wpsn_font_xxx_small') 			? esc_attr(get_option('wpsn_font_xxx_small')) 						: 4;

	$css = '';

	$css .= ':root {' . "\n";
	  $css .= '    --wpsn-content-max-width: ' . esc_html($wpsn_content_max_width) . ';' . "\n";
	  $css .= '    --wpsn-corner-radius: ' . esc_html($wpsn_corner_radius) . 'px;' . "\n";
	  $css .= '    --wpsn-menu-color: ' . esc_html($wpsn_color_menu) . ';' . "\n";
	  $css .= '    --wpsn-menu-color-text: ' . esc_html($wpsn_color_menu_text) . ';' . "\n";
	  $css .= '    --wpsn-primary: ' . esc_html($wpsn_color_primary) . ';' . "\n";
	  $css .= '    --wpsn-primary-hover: ' . esc_html($wpsn_color_primary_hover) . ';' . "\n";
	  $css .= '    --wpsn-primary-border: ' . esc_html($wpsn_color_primary_border) . ';' . "\n";
	  $css .= '    --wpsn-primary-contrast: ' . esc_html($wpsn_color_primary_contrast) . ';' . "\n";
	  $css .= '    --wpsn-secondary: ' . esc_html($wpsn_color_secondary) . ';' . "\n";
	  $css .= '    --wpsn-active: ' . esc_html($wpsn_color_active) . ';' . "\n";
	  $css .= '    --wpsn-page-background: ' . esc_html($wpsn_color_page_background) . ';' . "\n";
	  $css .= '    --wpsn-page-background-space: ' . esc_html($wpsn_background_image_space) . 'px;' . "\n";
	  $css .= '    --wpsn-box-background-color: ' . esc_html($wpsn_box_background_color) . ';' . "\n";
	  $css .= '    --wpsn-background-text-color: ' . esc_html($wpsn_color_page_text) . ';' . "\n";
	  $css .= '    --wpsn-box-border-color: ' . esc_html($wpsn_box_border_color) . ';' . "\n";
	  $css .= '    --wpsn-box-text-color: ' . esc_html($wpsn_box_text_color) . ';' . "\n";
	  $css .= '    --wpsn-box-link-color: ' . esc_html($wpsn_box_link_color) . ';' . "\n";
	  $css .= '    --wpsn-box-link-hover-color: ' . esc_html($wpsn_box_link_hover_color) . ';' . "\n";
	  $css .= '    --wpsn-box-input-color: ' . esc_html($wpsn_box_input_color) . ';' . "\n";
	  $css .= '    --wpsn-cta: ' . esc_html($wpsn_color_cta) . ';' . "\n";
	  $css .= '    --wpsn-cancel: ' . esc_html($wpsn_color_cancel) . ';' . "\n";
	  $css .= '    --wpsn-disabled: ' . esc_html($wpsn_color_disabled) . ';' . "\n";
	  $css .= '    --wpsn-xx-large: 44px;' . "\n";
	  $css .= '    --wpsn-x-large: ' . esc_html($wpsn_font_x_large) . 'px;' . "\n";
	  $css .= '    --wpsn-large: ' . esc_html($wpsn_font_large) . 'px;' . "\n";
	  $css .= '    --wpsn-medium: ' . esc_html($wpsn_font_medium) . 'px;' . "\n";
	  $css .= '    --wpsn-small: ' . esc_html($wpsn_font_small) . 'px;' . "\n";
	  $css .= '    --wpsn-x-small: ' . esc_html($wpsn_font_x_small) . 'px;' . "\n";
	  $css .= '    --wpsn-xx-small: ' . esc_html($wpsn_font_xx_small) . 'px;' . "\n";
	  $css .= '    --wpsn-xxx-small: ' . esc_html($wpsn_font_xxx_small) . 'px;' . "\n";
	$css .= '}' . "\n";
	
    echo '<!-- WPSN custom CSS -->' . "\n";
	echo "<style type='text/css'>" . esc_html($css) . "</style>";
	echo '<!-- End custom CSS background -->' . "\n";
	
}

function wpsn_page_viewed() {
	global $current_user;
	if (is_user_logged_in()) {
		update_user_meta($current_user->ID, 'wpsn_last_active', time());
	}
}

function wpsn_custom_login_action( $user_login, $user ) {

	$last_logged_in = get_user_meta( $user->ID, 'wpsn_last_logged_in', true );
	update_user_meta($user->ID, 'wpsn_previous_logged_in', $last_logged_in);
	update_user_meta($user->ID, 'wpsn_last_logged_in', time());
	
}

function wpsn_add_dynamic_background_css() {

	// Get the background image URL from the custom field
	$show_url = wpsn_is_wpsn_page();
	$css = '';
	if ($show_url !== false) {
		
		$css .= 'body {' . "\n";
			$css .= '    background-image: url('.esc_url($show_url).') !important;' . "\n";
			$css .= '    background-size: cover;' . "\n";
			$css .= '    background-position: center;' . "\n";
			$css .= '    background-repeat: no-repeat;' . "\n";
			$css .= '    background-attachment: fixed;' . "\n";
		$css .= '}' . "\n";
		$css .= 'body, html, #site-content {' . "\n";
			$css .= '    background-color: none !important;' . "\n";
		$css .= '}' . "\n";

	} else {

		$css .= 'body, html, #site-content {' . "\n";
			$css .= '    background-color: var(--wpsn-page-background);' . "\n";
		$css .= '}' . "\n";

	}
		
    echo '<!-- WPSN page background -->' . "\n";
	echo "<style type='text/css'>" . esc_html($css) . "</style>" . "\n";
	echo '<!-- End WPSN page background -->' . "\n";
	
}

function wpsn_is_wpsn_page() {
	
	global $post;
	$url = false;
	
	$wpsn_background_image_specific = get_option( 'wpsn_background_image_specific' );
	if ($wpsn_background_image_specific !== false && $wpsn_background_image_specific == 1) {
		
		$wpsn_home = 			get_option( 'wpsn_home' ) 			? get_option( 'wpsn_home' ) 		: 0;
		$wpsn_activity = 		get_option( 'wpsn_activity' ) 		? get_option( 'wpsn_activity' ) 	: 0;
		$wpsn_friends = 		get_option( 'wpsn_friends' ) 		? get_option( 'wpsn_friends' ) 		: 0;
		$wpsn_edit_profile = 	get_option( 'wpsn_edit_profile' ) 	? get_option( 'wpsn_edit_profile' ) : 0;
		$wpsn_search = 			get_option( 'wpsn_search' ) 		? get_option( 'wpsn_search' ) 		: 0;
		$wpsn_alerts = 			get_option( 'wpsn_alerts' ) 		? get_option( 'wpsn_alerts' ) 		: 0;
		
		if (($post->ID == $wpsn_home) ||
			($post->ID == $wpsn_activity) ||
			($post->ID == $wpsn_friends) ||
			($post->ID == $wpsn_edit_profile) ||
			($post->ID == $wpsn_search) ||
			($post->ID == $wpsn_alerts)) {
			$url = get_option('wpsn_background_image_url');
		}
		
	} else {
		
		$url = get_option('wpsn_background_image_url');

	}
	
	return $url;
}

function wpsn_is_user_active($user_id) {
	$last_active_timestamp = get_user_meta( $user_id, 'wpsn_last_active', true );
	$is_active = ( $last_active_timestamp >= strtotime('-1 minutes') );
	return $is_active;
}

function wpsn_update_last_active_now($user_id) {
	return update_user_meta($user_id, 'wpsn_last_active', current_time('timestamp'));
}

function wpsn_get_seconds_since_active($user_id) {
	// Retrieve the stored timestamp (assuming it's saved as a post meta)
	$stored_timestamp = get_user_meta($user_id, 'wpsn_last_active', true);

	// Check if the stored timestamp is valid
	if (!$stored_timestamp) {
		return false;
	}

	// Get the current timestamp
	$current_timestamp = current_time('timestamp');

	// Calculate the difference in seconds
	$time_difference = $current_timestamp - $stored_timestamp;

	return $time_difference;
}

function wpsn_user_dot($id) {
	$seconds_since_active = wpsn_get_seconds_since_active($id);
	$dot_class = 'wpsn_active_green';
	if ($seconds_since_active > 60) { $dot_class = 'wpsn_active_amber'; }
	if ($seconds_since_active > 300) { $dot_class = 'wpsn_active_none'; }
	return $dot_class;
}

global $wpsn_allowed_tags;
$wpsn_allowed_tags = array(
	'*' => array(
		'class' => true,
		'id' => true,
		'style' => true,
	),
	'div' => array(
		'class' => true,
		'id' => true,
	),
	'a' => array(
		'href' => true,
		'title' => true,
		'target' => true,
		'rel' => true,
	),
	'button' => array(
		'class' => true,
		'data-title' => true,
		'data-db' => true,
		'data-shortcode' => true,
	),
	'span' => array(
		'style' => true,
	),
	'p' => array(),
	'strong' => array(),
	'em' => array(),
	'ul' => array(),
	'ol' => array(),
	'li' => array(),
	'br' => array(),
	'tr' => array(),
	'td' => array(),
	'select' => array(
		'id' => true,
	),
	'option' => array(
		'value' => true,
		'selected' => true,
	),
	'img' => array(
		'src' => true,
		'alt' => true,
		'title' => true,
	),
);

?>