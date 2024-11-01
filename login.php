<?php

function wpsn_sc_login() {

    global $wpdb, $current_user;

	$html = '';

	$html .= '<div class="wpsn-wrapper">';

		$html .= '<div class="wpsn_post_popup">';
		
			$html .= '<div class="wpsn_post_popup_inner">';
				
				$html .= '<div class="wpsn-login-wrapper-row">';
					$html .= '<div class="wpsn-login-wrapper-label forgotten-password-label">Enter your email address - a new password will be sent to you</div>';
					$html .= '<div class="wpsn-login-wrapper-label">'.__('Username or Email address', 'wpsn-instant-social-network').'</div>';
					$html .= '<div class="wpsn-login-wrapper-input"><input type="text" class="wpsn-login-wrapper-email" /></div>';
				$html .= '</div>';
				
				$html .= '<div class="wpsn-login-wrapper-row wpsn-login-wrapper-row-password">';
					$html .= '<div class="wpsn-login-wrapper-label">'.__('Password', 'wpsn-instant-social-network').'</div>';
					$html .= '<div class="wpsn-login-wrapper-input"><input type="password" class="wpsn-login-wrapper-password" /></div>';
				$html .= '</div>';
				
				$html .= '<div class="wpsn-login-wrapper-row">';
					$html .= '<div class="wpsn_login_popup_cancel">';
						$html .= '<div class="wpsn-login-forgotten-password">'.__('Forgotten password', 'wpsn-instant-social-network').'</div>';
						$html .= '<div><a class="wpsn-login-cancel-url" href="'.home_url().'">Cancel</a></div>';
					$html .= '</div>';
					$html .= '<button class="wpsn-button-submit wpsn-button-submit-wide wpsn-login-wrapper-submit"><i class="fa-solid fa-right-to-bracket"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Login', 'wpsn-instant-social-network').'</span></button>';
					$html .= '<button class="wpsn-button-submit wpsn-button-submit-wide wpsn-login-wrapper-forgotten-password-submit"><i class="fa-solid fa-paper-plane"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Send', 'wpsn-instant-social-network').'</span></button>';
					$html .= '<div class="wpsn-login-error"></div>';
				$html .= '</div>';
				
			$html .= '</div>';
			
		$html .= '</div>';			
		
	$html .= '</div>';

    return $html;

}

function wpsn_sc_signup() {

    global $wpdb, $current_user;
    $html = '';

	if (is_user_logged_in()) {
		wp_safe_redirect( home_url() );
	}

    $html .= '<div class="wpsn-wrapper">';

		$html .= '<div class="wpsn_post_popup">';
		
			$html .= '<div class="wpsn_post_popup_inner">';
			
				$html .= '<div class="wpsn-signup-wrapper-row">';
					$html .= '<div class="wpsn-signup-wrapper-label">'.__('First name', 'wpsn-instant-social-network').'</div>';
					$html .= '<div class="wpsn-signup-wrapper-input"><input type="text" class="wpsn-signup-wrapper-firstname" /></div>';
				$html .= '</div>';
				
				$html .= '<div class="wpsn-signup-wrapper-row">';
					$html .= '<div class="wpsn-signup-wrapper-label">'.__('Last name', 'wpsn-instant-social-network').'</div>';
					$html .= '<div class="wpsn-signup-wrapper-input"><input type="text" class="wpsn-signup-wrapper-lastname" /></div>';
				$html .= '</div>';
				
				$html .= '<div class="wpsn-signup-wrapper-row">';
					$html .= '<div class="wpsn-signup-wrapper-label">'.__('Email address', 'wpsn-instant-social-network').'</div>';
					$html .= '<div class="wpsn-signup-wrapper-input"><input type="text" class="wpsn-signup-wrapper-email" /></div>';
				$html .= '</div>';
				
				$html .= '<div class="wpsn-signup-wrapper-row">';
					$html .= '<div class="wpsn-signup-wrapper-label">'.__('Password', 'wpsn-instant-social-network').'</div>';
					$html .= '<div class="wpsn-signup-wrapper-input"><input type="password" class="wpsn-signup-wrapper-password" /></div>';
				$html .= '</div>';
				
				$html .= '<div class="wpsn-signup-wrapper-row">';
					$html .= '<div class="wpsn-signup-wrapper-label">'.__('Repeat password', 'wpsn-instant-social-network').'</div>';
					$html .= '<div class="wpsn-signup-wrapper-input"><input type="password" class="wpsn-signup-wrapper-password2" /></div>';
				$html .= '</div>';
				
				$html .= '<div class="wpsn-signup-wrapper-row">';
					$html .= '<div class="wpsn_login_popup_cancel"><div><a class="wpsn-signup-cancel-url" href="'.home_url().'">'.__('Cancel', 'wpsn-instant-social-network').'</a></div></div>';
					$html .= '<button class="wpsn-button-submit wpsn-button-submit-wide wpsn-signup-wrapper-submit"><i class="fa-solid fa-user-plus"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Join', 'wpsn-instant-social-network').'</span></button>';
					$html .= '<div class="wpsn-signup-error"></div>';
				$html .= '</div>';
				
			$html .= '</div>';
			
		$html .= '</div>';			
		
    $html .= '</div>';

    return $html;
	
}

?>