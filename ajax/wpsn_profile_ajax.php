<?php

/* PROFILE */

function wpsn_save_profile_details() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		global $current_user;

        $first_name = sanitize_text_field( wp_unslash ($_POST['first_name'] ) );
        $last_name = sanitize_text_field( wp_unslash ($_POST['last_name'] ) );
        $user_email = sanitize_email( $_POST['user_email'] );
		$password = sanitize_text_field( wp_unslash ($_POST['password'] ) );
		$user_id = $current_user->ID;
		
		$public_profile = sanitize_text_field( wp_unslash ($_POST['profile_public'] ) );
		$friends_public = sanitize_text_field( wp_unslash ($_POST['friends_public'] ) );
		
		$email_friends = sanitize_text_field( wp_unslash ($_POST['wpsn_email_friends'] ) );
		$email_posts = sanitize_text_field( wp_unslash ($_POST['wpsn_email_posts'] ) );
				
		$form_data = isset($_POST['form_data']) ? sanitize_text_field( wp_unslash ($_POST['form_data'] ) ) : '';
		
		// Check if the email already exists
		$existing_user_id = email_exists($user_email);
		
		if ($existing_user_id && $existing_user_id != $user_id) {
			
			$ret = array(
				'status' => 'exists',
				'text' => $user_email,
			);

		} else {
			
			$user_data = wp_update_user( array( 
				'ID' => $user_id, 
				'first_name' => $first_name,
				'last_name' => $last_name,
				'user_email' => $user_email
			));
			
			if ( is_wp_error( $user_data ) ) {
				
				$ret = array(
					'status' => 'fail',
					'text' => 'Failed to update user',
				);
				
			} else {
			
				update_user_meta($user_id, 'wpsn_profile_public', $public_profile);
				update_user_meta($user_id, 'wpsn_friends_public', $friends_public);
				
				update_user_meta($user_id, 'wpsn_email_friends', $email_friends);
				update_user_meta($user_id, 'wpsn_email_posts', $email_posts);
				
				$form_data = apply_filters('wpsn_profile_ajax_form_data', $form_data);
								
				if ($password != '') {
					
					$user_id = wp_update_user( 
						array(  'ID' => $user_id, 
								'user_pass' => $password
						) );
					
					$ret = array(
						'status' => 'password_changed',
						'text' => 'Updated'
					);
					
				} else {
			
					$ret = array(
						'status' => 'ok',
						'text' => 'Updated'
					);
				
				}
			}
			
		}

	} else {
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (save_profile_details)',
		);
	}				

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_save_profile_details', 'wpsn_save_profile_details');


?>