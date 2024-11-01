<?php

function wpsn_sc_friends() {

    global $wpdb, $current_user;
    $html = '';

    if (is_user_logged_in()) {
		
        if ( (isset($_GET['uid']) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_GET['wpsn'] ) ), 'wpsn_nonce' ) && (is_numeric($_GET['uid']))) ) {
            $uid = sanitize_text_field( wp_unslash ( $_GET['uid'] ) );
        } else {
            $uid = $current_user->ID;
        }
		
		
        $html .= '<div class="wpsn-wrapper">';

			$html .= wpsn_sc_profile_header($uid);
	
 			$html .= '<div class="wpsn-friend-requests-sent"></div>';
			$html .= '<div class="wpsn-friend-requests-received"></div>';
			
			$html .= '<div class="wpsn-box-wrapper">';
				$html .= '<h2>Friends</h2>';
					$html .= '<div class="wpsn-friends"></div>';
			$html .= '</div>';
			
        $html .= '</div>';
		
    }

    return $html;
	
}

function wpsn_sc_activity_sidebar_friends($uid, $profile_page) {

    global $wpdb, $current_user;
    $html = '';

	$first_name = get_user_meta( $current_user->ID, 'first_name', true );
	$last_name = get_user_meta( $current_user->ID, 'last_name', true );
	
	if (!$first_name || !$last_name) {
		
		// Need to update profile details
		
	} else {

		$profile_public = get_user_meta($uid, 'wpsn_profile_public', true);
		if ($profile_public || wpsn_are_friends($current_user->ID, $uid)) {

			$html .= '<div class="wpsn-wrapper-sidebar">';
			
				$html .= '<div class="wpsn-side-bar-friends">';
		
					$html .= '<h2>'.__('Active friends', 'wpsn-instant-social-network').'</h2>';
					
					$html .= '<div class="wpsn-side-bar-friends-content"></div>';
					
				$html .= '</div>';
					
			$html .= '</div>';
			
		}
		
	}
		
    return $html;
	
}

?>