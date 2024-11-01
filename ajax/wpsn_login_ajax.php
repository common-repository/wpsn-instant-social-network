<?php

/* LOGOUT */

function wpsn_logout() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		wp_logout(); // Log out the user
		
		$ret = array(
			'status' => 'ok',
			'text' => 'Logged out',
		);
		
	} else {
		$ret = array(
			'status' => 'invalid',
			'text' => 'Invalid security token (logout)',
		);
	}				

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_logout', 'wpsn_logout');
add_action('wp_ajax_nopriv_wpsn_logout', 'wpsn_logout');

/* NEW PASSWORD */

function wpsn_new_password() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

        $forgottenEmail = sanitize_email($_POST['username']);
		
	    $html = '';

	    $userId = get_user_by('email', $forgottenEmail);

	    if ($userId) {

	    	// Re-generate the password
		    $userPassword = wp_generate_password( 12, false );
			wp_set_password( $userPassword, $userId->ID );
			
			$http_host = sanitize_text_field($_SERVER['HTTP_HOST']);
			$userPassword = sanitize_text_field($userPassword);

			$to = esc_html($userId->user_email);
			$subject = 'Your new password';
			$message = esc_html('<p>This your new password for '.$http_host.':</p>');
			$message .= esc_html('<p>'.$userPassword.'</p>');
			$message = wp_kses_post( $message );
			$headers = array('Content-Type: text/html; charset=UTF-8');

			$sent = wp_mail($to, $subject, $message, $headers);

			if ($sent) {
				// Email was successfully sent
				$ret = array(
					'status' => 'ok',
					'text' => wp_kses('Email sent to '.$to.' successfully', 'strip')
				);	
			} else {
				// Email sending failed
				$ret = array(
					'status' => 'fail',
					'text' => wp_kses('Failed to send email to '.$to, 'strip')
				);	
			}


	    } else {
			
			$ret = array(
				'status' => 'fail',
				'text' => 'User account not found'
			);			
	    }
		
	} else {
		$ret = array(
			'status' => 'invalid',
			'text' => 'Invalid security token (new_password)',
		);
	}				

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_nopriv_wpsn_new_password', 'wpsn_new_password');

/* LOGIN */

function wpsn_validate_login() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

        $username = sanitize_email($_POST['username']);
		$password = sanitize_text_field( wp_unslash ( $_POST['password'] ) );
		
		if (is_email($username)) {
			// Get the user by email
			$user = get_user_by('email', $username);
			if ($user) {
				$username = $user->user_login;
			}
		}

        $credentials = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true // You can set this to false if you don't want to remember the user
        );

        // Attempt to log the user in
        $user = wp_signon($credentials, false);

        if (is_wp_error($user)) {
            // Login failed
			$ret = array(
				'status' => 'fail',
				'text' => 'Invalid username or password',
			);
        } else {
            // Login successful
			$current_timestamp = current_time('timestamp');

			// Update the 'wpsn_last_active' and 'wpsn_last_login' user meta fields with the current timestamp
			wpsn_update_last_active_now($user->ID);
			update_user_meta($user->ID, 'wpsn_last_login', $current_timestamp);
			
			$ret = array(
				'status' => 'ok',
				'url' => wpsn_page('home'),
			);
        }
    	
	} else {
		$ret = array(
			'status' => 'invalid',
			'text' => 'Invalid security token (validate_login)',
		);
	}				

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_validate_login', 'wpsn_validate_login');
add_action('wp_ajax_nopriv_wpsn_validate_login', 'wpsn_validate_login');

function wpsn_sign_up_user() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$firstname = sanitize_text_field( wp_unslash ( $_POST['firstname'] ) );
		$lastname = sanitize_text_field( wp_unslash ( $_POST['lastname'] ) );
		$email = sanitize_email($_POST['email']);
		$password = sanitize_text_field( wp_unslash ($_POST['password']) );
		
		$user_data = array(
			'user_login'  => $email, // The user's username
			'user_pass'   => $password, // The user's password
			'user_email'  => $email, // The user's email address
			'first_name'  => $firstname, // The user's first name (optional)
			'last_name'   => $lastname, // The user's last name (optional)
			'role'        => 'subscriber' // The user's role (optional, default is subscriber)
		);
		
		// Create the user
		$user_id = wp_insert_user( $user_data );
		
		// Check if the user was successfully created
		if ( ! is_wp_error( $user_id ) ) {
			$ret = array(
				'status' => 'ok',
				'url' => wpsn_page('home'),
			);
		} else {

			$ret = array(
				'status' => 'ok',
				'url' => wp_kses("Error creating user: " . $user_id->get_error_message(), 'strip'),
			);
		}
		
	} else {
		$ret = array(
			'status' => 'invalid',
			'text' => 'Invalid security token (wpsn_sign_up_user)',
		);
	}				

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_nopriv_wpsn_sign_up_user', 'wpsn_sign_up_user');


?>