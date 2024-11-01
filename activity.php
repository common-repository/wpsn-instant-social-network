<?php

function wpsn_sc_activity() {

    global $wpdb, $current_user;
    $html = '';

	if (is_user_logged_in()) {
		
		if ( (isset($_GET['uid']) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_GET['wpsn'] ) ), 'wpsn_nonce' ) && (is_numeric($_GET['uid']))) ) {
			$uid = sanitize_text_field( wp_unslash ( $_GET['uid'] ) );
		} else {
			$uid = $current_user->ID;
		}

		$html .= '<div class="wpsn-wrapper">';

			$wpsn_side_activity = get_option( 'wpsn_side_activity' );
			if ($wpsn_side_activity === false) {
				$wpsn_side_activity = 1;
				update_option('wpsn_side_activity', $wpsn_side_activity);
			}
			
			if ($wpsn_side_activity) {
			
				$html .= '<div class="wpsn-home-and-activity-container">';
					$html .= '<div class="wpsn-activity-left-column">';
						$html .= wpsn_sc_feed_post();
						$html .= wpsn_sc_activity_show($uid);
					$html .= '</div>';
					$html .= '<div class="wpsn-activity-right-column">';
						$html .= wpsn_sc_activity_sidebar_friends($uid, false);
					$html .= '</div>';
				$html .= '</div>';

			} else {
				$html .= wpsn_sc_feed_post();
				$html .= wpsn_sc_activity_show($uid);
			}
			
		$html .= '</div>';
		
	}
		
    return $html;
	
}


function wpsn_sc_activity_show($uid, $post_type = 'wpsn-feed') {

    global $wpdb, $current_user;
    $html = '';
    
	$first_name = get_user_meta( $current_user->ID, 'first_name', true );
	$last_name = get_user_meta( $current_user->ID, 'last_name', true );
	
	if (!$first_name || !$last_name) {
		
		// Need to update profile details
		
	} else {

		$profile_public = get_user_meta($uid, 'wpsn_profile_public', true);
		if ($profile_public || wpsn_are_friends($current_user->ID, $uid)) {

				$html .= '<div data-wpsn-id="'.$uid.'" class="wpsn-feed-posts" data-wpsn-post-type="'.$post_type.'" data-wpsn-activity-mode="activity">';
				$html .= '</div>';
				
			
			// Activity Init Function
			$html .= apply_filters('wpsn_activity_init', '');
		}
		
	}
		
    return $html;

}

?>