<?php

function wpsn_add_page() {
	
    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$title = sanitize_text_field( wp_unslash ( $_POST['title'] ) );
		$shortcode = sanitize_text_field( wp_unslash ( $_POST['shortcode'] ) );
		$db = sanitize_text_field( wp_unslash ( $_POST['db'] ) );
		
		$allowed = array('wpsn_home', 'wpsn_activity', 'wpsn_friends', 'wpsn_edit_profile', 'wpsn_search', 'wpsn_alerts', 'wpsn_login', 'wpsn_signup');
		
		if (in_array($db, $allowed)) {
			$page_id = wpsn_create_new_page_with_content($title, '['.$shortcode.']');
			$success = update_option( $db, $page_id );
		}
		
		do_action('wpsn_admin_create_single_page_action', $title, $shortcode, $db);	
			
		$ret = array(
			'status' => 'ok',
			'text' => 'Page created',
		);
	
	} else {
		$ret = array(
			'status' => 'invalid',
			'text' => 'Invalid security token (wpsn_add_page)',
		);
	}
	
	echo wp_json_encode($ret);
	exit;
}
add_action('wp_ajax_wpsn_add_page', 'wpsn_add_page');

function wpsn_admin_email_send() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$post_id = sanitize_text_field( wp_unslash ( $_POST['post_id'] ) );
		$email_post = sanitize_text_field( wp_unslash ( get_post($post_id) ) );
		
		$to = get_userdata($email_post->post_parent)->user_email;
		$subject = $email_post->post_title;
		$message = $email_post->post_content;
		$headers = array('Content-Type: text/html; charset=UTF-8');

		$sent = wp_mail($to, $subject, $message, $headers);
	
		if ($sent) {
			
			$ret = array(
				'status' => 'ok',
				'text' => 'Email sent to '.$to,
			);
			
			// Update post as sent
			wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
			
		} else {
			
			$ret = array(
				'status' => 'fail',
				'text' => 'Email failed to send to '.$to,
			);
		}
	
	} else {
		$ret = array(
			'status' => 'invalid',
			'text' => 'Invalid security token (wpsn_admin_email_send)',
		);
	}
	
	echo wp_json_encode($ret);
	exit;
}
add_action('wp_ajax_wpsn_admin_email_send', 'wpsn_admin_email_send');

function wpsn_save_email() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	
	
		$wpsn_cron_seconds 	= sanitize_text_field( wp_unslash ( $_POST['wpsn_cron_seconds'] ) );
		$wpsn_cron_count 	= sanitize_text_field( wp_unslash ( $_POST['wpsn_cron_count'] ) );	
				
		$success = update_option( 'wpsn_cron_seconds',	$wpsn_cron_seconds );
		$success = update_option( 'wpsn_cron_count', 	$wpsn_cron_count );
		
		wpsn_reset_cron_job($wpsn_cron_seconds);

		$ret = array(
			'status' => 'ok',
			'text' => 'Updated',
		);

	} else {
		$ret = array(
			'status' => 'invalid',
			'text' => 'Invalid security token (wpsn_save_email)',
		);
	}				

	echo wp_json_encode($ret);
	exit;
}
add_action('wp_ajax_wpsn_save_email', 'wpsn_save_email');

function wpsn_reset_cron_job() {
	
	// Clear any existing schedules to avoid duplicates
	$timestamp = wp_next_scheduled('wpsn_custom_cron_job');
	if ($timestamp) {
		wp_unschedule_event($timestamp, 'wpsn_custom_cron_job');
	}
	
	// And re-add it
    wp_schedule_event(time(), 'wpsn_cron_schedule', 'wpsn_custom_cron_job');
		
}

function wpsn_save_pages() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	
	
		$wpsn_landing_page 	= sanitize_text_field( wp_unslash ( $_POST['wpsn_landing_page'] ) );
		$wpsn_home 			= sanitize_text_field( wp_unslash ( $_POST['wpsn_home'] ) );
		$wpsn_activity 		= sanitize_text_field( wp_unslash ( $_POST['wpsn_activity'] ) );
		$wpsn_friends 		= sanitize_text_field( wp_unslash ( $_POST['wpsn_friends'] ) );
		$wpsn_edit_profile 	= sanitize_text_field( wp_unslash ( $_POST['wpsn_edit_profile'] ) );
		$wpsn_search 		= sanitize_text_field( wp_unslash ( $_POST['wpsn_search'] ) );
		$wpsn_alerts 		= sanitize_text_field( wp_unslash ( $_POST['wpsn_alerts'] ) );
		$wpsn_login 		= sanitize_text_field( wp_unslash ( $_POST['wpsn_login'] ) );
		$wpsn_signup 		= sanitize_text_field( wp_unslash ( $_POST['wpsn_signup'] ) );	
		
		$form_data = isset($_POST['form_data']) ? sanitize_text_field( wp_unslash ( $_POST['form_data'] ) ) : '';
		
		$success = update_option( 'wpsn_landing_page',	$wpsn_landing_page );
		$success = update_option( 'wpsn_home', 			$wpsn_home );
		$success = update_option( 'wpsn_activity', 		$wpsn_activity );
		$success = update_option( 'wpsn_friends', 		$wpsn_friends );
		$success = update_option( 'wpsn_edit_profile', 	$wpsn_edit_profile );
		$success = update_option( 'wpsn_search', 		$wpsn_search );
		$success = update_option( 'wpsn_alerts', 		$wpsn_alerts );
		$success = update_option( 'wpsn_login', 		$wpsn_login );
		$success = update_option( 'wpsn_signup', 		$wpsn_signup );
		
		$form_data = apply_filters('wpsn_save_pages_form_data_filter', $form_data);

		$ret = array(
			'status' => 'ok',
			'text' => 'Updated',
		);

	} else {
		$ret = array(
			'status' => 'invalid',
			'text' => 'Invalid security token (wpsn_save_pages)',
		);
	}				

	echo wp_json_encode($ret);
	exit;
}
add_action('wp_ajax_wpsn_save_pages', 'wpsn_save_pages');

function wpsn_save_customize() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	
	
		// Get values
		$wpsn_content_max_width					= sanitize_text_field( wp_unslash ( $_POST['wpsn_content_max_width'] ) );
		if ($wpsn_content_max_width == '') { $wpsn_content_max_width = '1000px'; }
		if (strpos($wpsn_content_max_width, 'px') === false && strpos($wpsn_content_max_width, '%') === false) {
			$wpsn_content_max_width .= 'px';
		}

		$wpsn_corner_radius						= sanitize_text_field( wp_unslash ( $_POST['wpsn_corner_radius'] ) );
		
		$wpsn_menu_bar							= sanitize_text_field( wp_unslash ( $_POST['wpsn_menu_bar'] ) );
		$wpsn_menu_loggedout 					= sanitize_text_field( wp_unslash ( $_POST['wpsn_menu_loggedout'] ) );
		$wpsn_top_bar_labels					= sanitize_text_field( wp_unslash ( $_POST['wpsn_top_bar_labels'] ) );
		$wpsn_theme_home_avatar					= sanitize_text_field( wp_unslash ( $_POST['wpsn_theme_home_avatar'] ) );
		$wpsn_side_activity						= sanitize_text_field( wp_unslash ( $_POST['wpsn_side_activity'] ) );

		$wpsn_background_image_url				= sanitize_text_field( wp_unslash ( $_POST['wpsn_background_image_url'] ) );
		$wpsn_background_image_space			= sanitize_text_field( wp_unslash ( $_POST['wpsn_background_image_space'] ) );
		$wpsn_background_image_specific			= sanitize_text_field( wp_unslash ( $_POST['wpsn_background_image_specific'] ) );

		$wpsn_dummy_avatar						= sanitize_text_field( wp_unslash ( $_POST['wpsn_dummy_avatar_image_url'] ) );	

		$wpsn_color_menu						= $_POST['wpsn_color_menu'] 			? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_menu'] ) )				: 'transparent';	
		$wpsn_color_menu_text					= $_POST['wpsn_color_menu_text'] 		? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_menu_text'] ) ) 			: 'transparent';	
		$wpsn_color_primary						= $_POST['wpsn_color_primary'] 			? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_primary'] ) ) 			: 'transparent';	
		$wpsn_color_primary_hover				= $_POST['wpsn_color_primary_hover'] 	? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_primary_hover'] ) ) 		: 'transparent';	
		$wpsn_color_primary_border				= $_POST['wpsn_color_primary_border'] 	? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_primary_border'] ) ) 	: 'transparent';	
		$wpsn_color_primary_contrast			= $_POST['wpsn_color_primary_contrast'] ? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_primary_contrast'] ) ) 	: 'transparent';
		$wpsn_color_secondary					= $_POST['wpsn_color_secondary'] 		? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_secondary'] ) ) 			: 'transparent';	
		$wpsn_color_active						= $_POST['wpsn_color_active'] 			? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_active'] ) ) 			: 'transparent';	
		$wpsn_color_page_background				= $_POST['wpsn_color_page_background'] 	? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_page_background'] ) ) 	: 'transparent';	
		$wpsn_color_page_text					= $_POST['wpsn_color_page_text'] 		? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_page_text'] ) ) 			: 'transparent';	
		$wpsn_box_background_color				= $_POST['wpsn_box_background_color'] 	? sanitize_text_field( wp_unslash ( $_POST['wpsn_box_background_color'] ) ) 	: 'transparent';
		$wpsn_box_border_color					= $_POST['wpsn_box_border_color'] 		? sanitize_text_field( wp_unslash ( $_POST['wpsn_box_border_color'] ) ) 		: 'transparent';
		$wpsn_box_text_color					= $_POST['wpsn_box_text_color'] 		? sanitize_text_field( wp_unslash ( $_POST['wpsn_box_text_color'] ) ) 			: 'transparent';
		$wpsn_box_link_color					= $_POST['wpsn_box_link_color'] 		? sanitize_text_field( wp_unslash ( $_POST['wpsn_box_link_color'] ) ) 			: 'transparent';
		$wpsn_box_link_hover_color				= $_POST['wpsn_box_link_hover_color'] 	? sanitize_text_field( wp_unslash ( $_POST['wpsn_box_link_hover_color'] ) ) 	: 'transparent';
		$wpsn_box_input_color					= $_POST['wpsn_box_input_color'] 		? sanitize_text_field( wp_unslash ( $_POST['wpsn_box_input_color'] ) ) 			: 'transparent';
		$wpsn_color_cta							= $_POST['wpsn_color_cta'] 				? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_cta'] ) ) 				: 'transparent';	
		$wpsn_color_cancel						= $_POST['wpsn_color_cancel'] 			? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_cancel'] ) ) 			: 'transparent';	
		$wpsn_color_disabled					= $_POST['wpsn_color_disabled'] 		? sanitize_text_field( wp_unslash ( $_POST['wpsn_color_disabled'] ) ) 			: 'transparent';	
		
		$wpsn_font_x_large						= sanitize_text_field( wp_unslash ( $_POST['wpsn_font_x_large'] ) );
		$wpsn_font_large						= sanitize_text_field( wp_unslash ( $_POST['wpsn_font_large'] ) );
		$wpsn_font_medium						= sanitize_text_field( wp_unslash ( $_POST['wpsn_font_medium'] ) );
		$wpsn_font_small						= sanitize_text_field( wp_unslash ( $_POST['wpsn_font_small'] ) );
		$wpsn_font_x_small						= sanitize_text_field( wp_unslash ( $_POST['wpsn_font_x_small'] ) );
		$wpsn_font_xx_small						= sanitize_text_field( wp_unslash ( $_POST['wpsn_font_xx_small'] ) );
		$wpsn_font_xxx_small					= sanitize_text_field( wp_unslash ( $_POST['wpsn_font_xxx_small'] ) );	

		$wpsn_hide_action_buttons				= sanitize_text_field( wp_unslash ( $_POST['wpsn_hide_action_buttons'] ) );		
		
		// Update

		update_option( 'wpsn_content_max_width',				$wpsn_content_max_width );
		update_option( 'wpsn_corner_radius',					wpsn_strip_units($wpsn_corner_radius) );
		
		update_option( 'wpsn_menu_bar',							$wpsn_menu_bar );
		update_option( 'wpsn_menu_loggedout',	 				$wpsn_menu_loggedout );
		update_option( 'wpsn_top_bar_labels',	 				$wpsn_top_bar_labels );
		update_option( 'wpsn_theme_home_avatar', 				$wpsn_theme_home_avatar );
		
		update_option( 'wpsn_side_activity',					$wpsn_side_activity );
		
		if ($wpsn_background_image_url != '') {
			update_option('wpsn_background_image_url', $wpsn_background_image_url);
		} else {
			delete_option('wpsn_background_image_url');
		}
		update_option( 'wpsn_background_image_space',			$wpsn_background_image_space );
		update_option( 'wpsn_background_image_specific',		$wpsn_background_image_specific );

		update_option( 'wpsn_dummy_avatar',						$wpsn_dummy_avatar );

		update_option( 'wpsn_color_menu',						$wpsn_color_menu );
		update_option( 'wpsn_color_menu_text',					$wpsn_color_menu_text );
		update_option( 'wpsn_color_primary',					$wpsn_color_primary );
		update_option( 'wpsn_color_primary_hover',				$wpsn_color_primary_hover );
		update_option( 'wpsn_color_primary_border',				$wpsn_color_primary_border );
		update_option( 'wpsn_color_primary_contrast',			$wpsn_color_primary_contrast );
		update_option( 'wpsn_color_secondary',					$wpsn_color_secondary );
		update_option( 'wpsn_color_active',						$wpsn_color_active );
		update_option( 'wpsn_color_page_background',			$wpsn_color_page_background );
		update_option( 'wpsn_color_page_text',					$wpsn_color_page_text );
		update_option( 'wpsn_box_background_color',				$wpsn_box_background_color );
		update_option( 'wpsn_box_border_color',					$wpsn_box_border_color );
		update_option( 'wpsn_box_text_color',					$wpsn_box_text_color );
		update_option( 'wpsn_box_link_color',					$wpsn_box_link_color );
		update_option( 'wpsn_box_link_hover_color',				$wpsn_box_link_hover_color );
		update_option( 'wpsn_box_input_color',					$wpsn_box_input_color );		
		update_option( 'wpsn_color_cta',						$wpsn_color_cta );
		update_option( 'wpsn_color_cancel',						$wpsn_color_cancel );
		update_option( 'wpsn_color_disabled',					$wpsn_color_disabled );
		
		update_option( 'wpsn_font_x_large',						wpsn_strip_units($wpsn_font_x_large) );
		update_option( 'wpsn_font_large',						wpsn_strip_units($wpsn_font_large) );
		update_option( 'wpsn_font_medium',						wpsn_strip_units($wpsn_font_medium) );
		update_option( 'wpsn_font_small',						wpsn_strip_units($wpsn_font_small) );
		update_option( 'wpsn_font_x_small',						wpsn_strip_units($wpsn_font_x_small) );
		update_option( 'wpsn_font_xx_small',					wpsn_strip_units($wpsn_font_xx_small) );
		update_option( 'wpsn_font_xxx_small',					wpsn_strip_units($wpsn_font_xxx_small) );
		
		update_option( 'wpsn_hide_action_buttons',				$wpsn_hide_action_buttons );
				
		$ret = array(
			'status' => 'ok',
			'text' => 'Updated'
		);

	} else {
		$ret = array(
			'status' => 'invalid',
			'text' => 'Invalid security token (wpsn_save_customize)',
		);
	}				

	
	echo wp_json_encode($ret);
	exit;
}
add_action('wp_ajax_wpsn_save_customize', 'wpsn_save_customize');

function wpsn_save_activity() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	
	
		// Get values
		$wpsn_prompt 							= esc_html ( sanitize_text_field( $_POST['wpsn_prompt'] ) );	
		$wpsn_home_posts_limit 					= sanitize_text_field( wp_unslash ( $_POST['wpsn_home_posts_limit'] ) );				
		$wpsn_home_posts_show_comments_limit 	= sanitize_text_field( wp_unslash ( $_POST['wpsn_home_posts_show_comments_limit'] ) );
		$wpsn_alert_count 						= sanitize_text_field( wp_unslash ( $_POST['wpsn_alert_count'] ) );
		$wpsn_reply_limit 						= sanitize_text_field( wp_unslash ( $_POST['wpsn_reply_limit'] ) );
		$wpsn_banned 							= esc_html( sanitize_text_field ( $_POST['wpsn_banned']) );
		$wpsn_banned_sub						= esc_html( sanitize_text_field ( $_POST['wpsn_banned_sub']) );
		$wpsn_markdown							= esc_html( sanitize_text_field ( $_POST['wpsn_markdown']) );

		// Update
		update_option( 'wpsn_prompt',							$wpsn_prompt );
		update_option( 'wpsn_home_posts_limit',					$wpsn_home_posts_limit );
		update_option( 'wpsn_home_posts_show_comments_limit',	$wpsn_home_posts_show_comments_limit );
		update_option( 'wpsn_alert_count',						$wpsn_alert_count );
		update_option( 'wpsn_banned',							trim($wpsn_banned) );
		update_option( 'wpsn_banned_sub',						trim($wpsn_banned_sub) );
		update_option( 'wpsn_markdown',							$wpsn_markdown );
				
		$ret = array(
			'status' => 'ok',
			'text' => 'Updated'
		);

	} else {
		$ret = array(
			'status' => 'invalid',
			'text' => 'Invalid security token (wpsn_save_activity)',
		);
	}				

	
	echo wp_json_encode($ret);
	exit;
}
add_action('wp_ajax_wpsn_save_activity', 'wpsn_save_activity');

function wpsn_strip_units($string) {
	$string = trim($string);
    return preg_replace('/(px|em|%)$/', '', $string);
}
?>