<?php

function wpsn_sc_alerts() {

    global $wpdb, $current_user;
    $html = '';

    if (is_user_logged_in()) {
		
        if ( (isset($_GET['uid']) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_GET['wpsn'] ) ), 'wpsn_nonce' ) && (is_numeric($_GET['uid']))) ) {
            $uid = sanitize_text_field( wp_unslash ( $_GET['uid'] ) );
        } else {
            $uid = $current_user->ID;
        }
		
		$html = wpsn_sc_profile_header($uid);
		
		$html .= '<div class="wpsn-wrapper">';
		
			$html .= '<div class="wpsn-box-wrapper">';
			
				$html .= '<div class="wpsn-clear-alerts-popup wpsn_hide"><i class="fa-solid fa-trash-can"></i><span class="wpsn_button_label">&nbsp;&nbsp;Remove all alerts</span></div>';
				$html .= '<h2>Alerts</h2>';

				$html .= '<div class="wpsn-alerts"></div>';
						
			$html .= '</div>';
			
		$html .= '</div>';
		?>
		
		<!-- Confirm Clear Alerts -->
		<div id="wpsn_clear_alerts" class="wpsn_hide">
			<div id="wpsn_confirm_inner">
				<div class="wpsn_label"><p><strong><?php esc_html_e('Are you sure?', 'wpsn-instant-social-network'); ?></strong></p><p><?php esc_html_e('All alerts will be deleted!', 'wpsn-instant-social-network'); ?></p></div>
				<div id="wpsn_confirm_actions">
					<div class="wpsn_confirm_actions_buttons">
						<div class="wpsn-button-submit wpsn-button-primary wpsn-confirm-clear-alerts-yes"><i class="fa-solid fa-check"></i><span class="wpsn_button_label">&nbsp;&nbsp;<?php esc_html_e('Yes', 'wpsn-instant-social-network'); ?></span></div>
						<div class="wpsn-button-submit wpsn-confirm-clear-alerts-no"><i class="fa-solid fa-xmark"></i><span class="wpsn_button_label">&nbsp;&nbsp;<?php esc_html_e('No', 'wpsn-instant-social-network'); ?></span></div>
					</div>
				</div>
			</div>
		</div>
		<?php
		
    }

    return $html;
	
}

// LIBRARY

function wpsn_add_alert($from, $to, $post_id, $type, $alert) {
	
	$id = wp_insert_post(array(
		'post_author'   => $from,
		'post_parent'   => $to,
		'post_title'    => $type, 
		'post_type'     => 'wpsn-alert', 
		'post_content'  => $alert,
		'post_status'   => 'unread'
	));
			
	update_post_meta($id, 'wpsn_alert_post_id', $post_id);
	
	// Queue email if enabled
	$enabled = false;

	$enabled = apply_filters('wpsn_add_alert_check_filter', $to, $enabled);
	
	if (($type == 'post' || $type == 'reply') && get_user_meta($to, 'wpsn_email_posts', true)) { $enabled = true; }
	if ($type == 'friend' && get_user_meta($to, 'wpsn_email_friends', true)) { $enabled = true; }
	
	if ($enabled) {
		// Add queued email
		
		$title = get_bloginfo('name').': New '.ucfirst($type).' Alert!';
		$user_data = get_user_by( 'ID', $from );
		$from_display_name = $user_data->first_name . ' ' . $user_data->last_name;
		
		$url = '';
		$the_post = get_post($post_id);

		if ($type == 'post') {
			$url = array(
				"url" => wpsn_page('home').'?uid='.$from.'#wpsn_post_'.$post_id,
				"message" => 'Go to the post...'
			);
		}
		if ($type == 'reply') {
			$url = array(
				"url" => wpsn_page('home').'?uid='.$to.'#wpsn_post_'.$post_id,
				"message" => 'Go to the reply...'
			);
		}
		if ($type == 'friend') {
			$url = array(
				"url" => wpsn_page('home').'?uid='.$post_id,
				"message" => 'Go to their profile page...'
			);
		}

		$url = apply_filters('wpsn_pro_extend_email_filter', $url, $type, $to, $from, $post_id);
		
		$body = $alert;
		$body .= '<p><a href="'.$url["url"].'">'.$url["message"].'</a></p>';
		$body .= '<p>Sent from '.get_bloginfo('name').'</p>';
		
		$id = wp_insert_post(array(
			'post_author'   => $from,
			'post_parent'   => $to,
			'post_title'    => $title, 
			'post_type'     => 'wpsn-email', 
			'post_content'  => $body,
			'post_status'   => 'draft'
		));
	}
}

?>