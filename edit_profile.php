<?php

function wpsn_sc_profile_edit() {

    global $wpdb, $current_user;
    $html = '';
	
    if (is_user_logged_in()) {

		$html .= '<div class="wpsn-wrapper">';

			$html .= '<div class="wpsn-wrapper-edit-profile">';

				$user = new WP_User( $current_user->ID );

				if ( $user->exists() ) {

					// Fake fields to avoid auto-completion
					$html .= '<input type="text" id="first_name_fake" name="first_name_fake" style="position: absolute; top: -1000px;">';
					$html .= '<input type="text" id="last_name_fake" name="first_name_fake" style="position: absolute; top: -1000px;">';
					$html .= '<input type="text" id="email_fake" name="first_name_fake" style="position: absolute; top: -1000px;">';
					$html .= '<input type="text" id="password_fake" name="first_name_fake" style="position: absolute; top: -1000px;">';
					
					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<div class="wpsn-edit-profile-row-header">';
							$html .= '<span>'.__('First name', 'wpsn-instant-social-network').'</span>';
						$html .= '</div>';
						$html .= '<div class="wpsn-edit-profile-row-value">';
							$html .= '<input id="first_name" type="text" autocomplete="off" class="wpsn-core-field wpsn-edit-profile-row-input" value="'.$user->get('user_firstname').'" />';
						$html .= '</div>';
					$html .= '</div>';

					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<div class="wpsn-edit-profile-row-header">';
							$html .= '<span>'.__('Last name', 'wpsn-instant-social-network').'</span>';
						$html .= '</div>';
						$html .= '<div class="wpsn-edit-profile-row-value">';
							$html .= '<input id="last_name" type="text" class="wpsn-core-field wpsn-edit-profile-row-input" value="'.$user->get('user_lastname').'" />';
						$html .= '</div>';
					$html .= '</div>';

					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<div class="wpsn-edit-profile-row-header">';
							$html .= '<span>'.__('Email address', 'wpsn-instant-social-network').'</span>';
						$html .= '</div>';
						$html .= '<div class="wpsn-edit-profile-row-value">';
							$html .= '<input id="user_email" type="text" class="wpsn-core-field wpsn-edit-profile-row-input" value="'.$user->get('user_email').'" />';
						$html .= '</div>';
					$html .= '</div>';

					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<div class="wpsn-edit-profile-row-header">';
							$html .= '<span>'.__('Change password', 'wpsn-instant-social-network').'</span>';
						$html .= '</div>';
						$html .= '<div class="wpsn-edit-profile-row-value">';
							// Following line avoids auto-filling of password field
							$html .= '<input type="password" name="password" style="display:none;" autocomplete="new-password">';
							$html .= '<input id="change_password" type="password" class="wpsn-core-field wpsn-edit-profile-row-input" value="" />';
						$html .= '</div>';
					$html .= '</div>';

					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<div class="wpsn-edit-profile-row-header">';
							$html .= '<span>'.__('Repeat new password', 'wpsn-instant-social-network').'</span>';
						$html .= '</div>';
						$html .= '<div class="wpsn-edit-profile-row-value">';
							$html .= '<input id="change_password2" type="password" class="wpsn-core-field wpsn-edit-profile-row-input" value="" />';
						$html .= '</div>';
					$html .= '</div>';

					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<h2>'.__('Privacy Setting', 'wpsn-instant-social-network').'</h2>';
					$html .= '</div>';
					
					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<div class="wpsn-edit-profile-row-header">';
							$html .= '<span>'.__('Profile posts visible to non-friends', 'wpsn-instant-social-network').'</span>';
						$html .= '</div>';
						$html .= '<div class="wpsn-edit-profile-row-value">';
							$html .= '<label class="switch">';
								$profile_public = get_user_meta($current_user->ID, 'wpsn_profile_public', true);
								$html .= '<input type="checkbox" id="profile_public" class="wpsn-core-field"';
									if ($profile_public) { $html .= ' CHECKED'; }
									$html .= ' />';
								$html .= '<span class="slider round"></span>';
							$html .= '</label>';
						$html .= '</div>';
					$html .= '</div>';

					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<div class="wpsn-edit-profile-row-header">';
							$html .= '<span>'.__('Friends visible to non-friends', 'wpsn-instant-social-network').'</span>';
						$html .= '</div>';
						$html .= '<div class="wpsn-edit-profile-row-value">';
							$html .= '<label class="switch">';
								$friends_public = get_user_meta($current_user->ID, 'wpsn_friends_public', true);
								$html .= '<input type="checkbox" id="friends_public" class="wpsn-core-field"';
									if ($friends_public) { $html .= ' CHECKED'; }
									$html .= ' />';
								$html .= '<span class="slider round"></span>';
							$html .= '</label>';
						$html .= '</div>';
					$html .= '</div>';

					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<h2>'.__('Email notifications', 'wpsn-instant-social-network').'</h2>';
					$html .= '</div>';
					
					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<div class="wpsn-edit-profile-row-header">';
							$html .= '<span>'.__('Friend Requests and Replies', 'wpsn-instant-social-network').'</span>';
						$html .= '</div>';
						$html .= '<div class="wpsn-edit-profile-row-value">';
							$html .= '<label class="switch">';
								$wpsn_email_friends = get_user_meta($current_user->ID, 'wpsn_email_friends', true);
								if ($wpsn_email_friends === false) {
									update_user_meta($current_user->ID, 'wpsn_email_friends', 1);
									$wpsn_email_friends = 1;
								}
								$html .= '<input type="checkbox" id="wpsn_email_friends" class="wpsn-core-field"';
									if ($wpsn_email_friends) { $html .= ' CHECKED'; }
									$html .= ' />';
								$html .= '<span class="slider round"></span>';
							$html .= '</label>';
						$html .= '</div>';
					$html .= '</div>';
					
					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<div class="wpsn-edit-profile-row-header">';
							$html .= '<span>'.__('Posts and Replies', 'wpsn-instant-social-network').'</span>';
						$html .= '</div>';
						$html .= '<div class="wpsn-edit-profile-row-value">';
							$html .= '<label class="switch">';
								$wpsn_email_posts = get_user_meta($current_user->ID, 'wpsn_email_posts', true);
								if ($wpsn_email_posts === false) {
									update_user_meta($current_user->ID, 'wpsn_email_posts', 1);
									$wpsn_email_posts = 1;
								}
								$html .= '<input type="checkbox" id="wpsn_email_posts" class="wpsn-core-field"';
									if ($wpsn_email_posts) { $html .= ' CHECKED'; }
									$html .= ' />';
								$html .= '<span class="slider round"></span>';
							$html .= '</label>';
						$html .= '</div>';
					$html .= '</div>';

					$html = apply_filters('wpsn_edit_profile_email_notifications_filter', $html);

					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<div class="wpsn-edit-profile-row-header">';
						$html .= '</div>';
						$html .= '<div class="wpsn-edit-profile-row-value">';
							$html .= '<button class="wpsn-button-submit wpsn-edit-profile-save"><i class="fa-solid fa-floppy-disk"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Save', 'wpsn-instant-social-network').'</span></button>';
						$html .= '</div>';
					$html .= '</div>';

					$html .= '<div class="wpsn-edit-profile-row"><a name="avatar-cover-change"></a>';
						$html .= '<h2>'.__('Avatar and Cover images', 'wpsn-instant-social-network').'</h2>';
					$html .= '</div>';
					
					$html .= '<div class="wpsn-edit-profile-row">';
						$html .= '<div class="wpsn-edit-profile-row-header">';
							$html .= '<span>'.__('Change profile picture', 'wpsn-instant-social-network').'</span>';							
						$html .= '</div>';
						$html .= '<div class="wpsn-edit-profile-row-value wpsn-edit-profile-avatar-form">';
							$html .= '<img class="wpsn-feed-post-header-avatar-img wpsn-edit-profile-avatar" src="'.wpsn_get_avatar($current_user->ID).'" />';
							$html .= '<div>';
								$html .= '<form enctype="multipart/form-data" id="wpsn-avatar-form" action="'.admin_url('admin-post.php').'" method="post">';
									$html .= '<label for="wpsn-avatar-input" class="wpsn-edit-avatar-upload wpsn-button-submit"><i class="fa-solid fa-upload"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Change', 'wpsn-instant-social-network').'</span></label>';
									$html .= '<input type="hidden" name="action" value="upload_avatar">';
									$html .= '<input type="file" id="wpsn-avatar-input" class="wpsn-core-field" accept="image/*">';
									$html .= '<div style="clear:both"></div>';
									$html .= '<div id="wpsn-avatar-preview"></div>';
									$html .= '<button class="wpsn-button-submit edit-avatar-save-cancel wpsn-button-cancel wpsn_hide" type="submit"><i class="fa-solid fa-xmark"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Cancel', 'wpsn-instant-social-network').'</span></button>';
									$html .= '<button class="wpsn-button-submit edit-avatar-save wpsn-button-cta wpsn_hide" type="submit"><i class="fa-solid fa-floppy-disk"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Save', 'wpsn-instant-social-network').'</span></button>';
								$html .= '</form>';
							$html .= '</div>';
						$html .= '</div>';
					$html .= '</div>';

					$html .= '<div class="wpsn-edit-profile-row" style="padding-top: 30px;">';
						$html .= '<div class="wpsn-edit-profile-row-header">';
							$html .= '<span>'.__('Change profile page cover picture', 'wpsn-instant-social-network').'</span>';
						$html .= '</div>';
						$html .= '<div class="wpsn-edit-profile-row-value wpsn-edit-profile-cover-form">';
							$the_cover = get_user_meta($current_user->ID, 'wpsn_custom_cover', true);
							if (!$the_cover) {
								$the_cover = plugins_url() . '/wpsn-instant-social-network/img/placeholder_cover.jpg';
							}
							$html .= '<img class="wpsn-background-image wpsn-edit-profile-cover" src="'.$the_cover.'" />';
							$html .= '<div>';
								$html .= '<form enctype="multipart/form-data" id="wpsn-cover-form" action="'.admin_url('admin-post.php').'" method="post">';
									$html .= '<label for="wpsn-cover-input" class="wpsn-edit-cover-upload wpsn-button-submit"><i class="fa-solid fa-upload"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Change', 'wpsn-instant-social-network').'</span></label>';
									$html .= '<input type="hidden" name="action" value="upload_cover">';
									$html .= '<input type="file" id="wpsn-cover-input" class="wpsn-core-field" accept="image/*">';
									$html .= '<div style="clear:both"></div>';
									$html .= '<div id="wpsn-cover-preview"></div>';
									$html .= '<button class="wpsn-button-submit edit-cover-save-cancel wpsn-button-cancel wpsn_hide" type="submit"><i class="fa-solid fa-xmark"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Cancel', 'wpsn-instant-social-network').'</span></button>';
									$html .= '<button class="wpsn-button-submit edit-cover-save wpsn-button-cta wpsn_hide" type="submit"><i class="fa-solid fa-floppy-disk"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Save', 'wpsn-instant-social-network').'</span></button>';
								$html .= '</form>';
							$html .= '</div>';
						$html .= '</div>';
					$html .= '</div>';


					$html = apply_filters('wpsn_edit_profile_add_filter', $html, $current_user->ID);

				} else {

					$html = '<div class="wpsn-problem">Invalid user</div>';

				}
				
			$html .= '</div>';

		$html .= '</div>';
		
	}

    return $html;

}


// AJAX handler to save cropped image as user's avatar
add_action('wp_ajax_wpsn_save_avatar', 'wpsn_save_avatar');
//add_action('wp_ajax_nopriv_wpsn_save_avatar', 'wpsn_save_avatar');

function wpsn_save_avatar() {
	if ( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ), 'wpsn_action' ) ) {
		if (isset($_POST['avatar_data']) && isset($_POST['user_id'])) {
			
			// Get the original memory limit
			$original_memory_limit = ini_get('memory_limit');

			// Set the new memory limit
			ini_set('memory_limit', '256M');
			
			global $wp_filesystem;
			
			if ( ! WP_Filesystem() ) {
				// Failed to initialize WP_Filesystem, handle error.
				error_log ('--------------- Failed to initialize WP_Filesystem (wpsn_save_avatar).');
				return;
			}				

			$avatar_data = sanitize_text_field( wp_unslash ( $_POST['avatar_data'] ) );
			$user_id = sanitize_text_field( wp_unslash ( $_POST['user_id'] ) );

			// Decode base64 image data
			$base64_image = str_replace('data:image/png;base64,', '', $avatar_data);
			$base64_image = str_replace(' ', '+', $base64_image);
			$avatar_image = base64_decode($base64_image);

			// Create image resource
			$image = @imagecreatefromstring($avatar_image);

			// Resize image
			$resized_image = imagescale($image, 400); // Resize to 200px width (maintains aspect ratio)

			// Compress image
			ob_start();
			imagejpeg($resized_image, null, 75); // 75 is the quality parameter
			$compressed_image = ob_get_clean();

			// Create folder structure if not yet there
			$upload_dir = wp_upload_dir();
			$directory_path = trailingslashit( $upload_dir['basedir'] ) . $user_id;
			if ( ! $wp_filesystem->is_dir( $directory_path ) ) {
				$wp_filesystem->mkdir( $directory_path );
				error_log('Created folder '.$directory_path);
			}
			
			// Save compressed image to uploads directory
			$time_stamp = current_time('timestamp');
			$avatar_file = $upload_dir['basedir'] . '/'. $user_id . '/avatar_' . $time_stamp . '.jpg';
			$wp_filesystem->put_contents( $avatar_file, $compressed_image, FS_CHMOD_FILE );
			
			// Update user meta with avatar file URL
			update_user_meta($user_id, 'wpsn_custom_avatar', $upload_dir['baseurl'] . '/' . $user_id . '/avatar_' . $time_stamp . '.jpg');

			// Return success response
			echo esc_html($avatar_file);
			
			// Restore the original memory limit
			ini_set('memory_limit', $original_memory_limit);
			
		} else {
			// Return error response
			echo 'Invalid request';
		}	
	}
}

// AJAX handler to save cropped image as user's cover image
add_action('wp_ajax_wpsn_save_cover', 'wpsn_save_cover');
//add_action('wp_ajax_nopriv_wpsn_save_cover', 'wpsn_save_cover');

function wpsn_save_cover() {
	if ( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ), 'wpsn_action' ) ) {
		if (isset($_POST['cover_data']) && isset($_POST['user_id'])) {
			
			// Get the original memory limit
			$original_memory_limit = ini_get('memory_limit');

			// Set the new memory limit
			ini_set('memory_limit', '256M');

			global $wp_filesystem;
			
			if ( ! WP_Filesystem() ) {
				// Failed to initialize WP_Filesystem, handle error.
				error_log ('--------------- Failed to initialize WP_Filesystem (wpsn_save_cover).');
				return;
			}				

			$cover_data = sanitize_text_field( wp_unslash ( $_POST['cover_data'] ) );
			$user_id = sanitize_text_field( wp_unslash ( $_POST['user_id'] ) );

			// Decode base64 image data
			$base64_image = str_replace('data:image/png;base64,', '', $cover_data);
			$base64_image = str_replace(' ', '+', $base64_image);
			$cover_data = base64_decode($base64_image);

			// Create image resource
			$image = @imagecreatefromstring($cover_data);

			// Resize image
			$resized_image = imagescale($image, 1200); // Resize to width (maintains aspect ratio)

			// Compress image
			ob_start();
			imagejpeg($resized_image, null, 100); // 75 is the quality parameter
			$compressed_image = ob_get_clean();

			// Create folder structure if not yet there
			$upload_dir = wp_upload_dir();
			$directory_path = trailingslashit( $upload_dir['basedir'] ) . $user_id;
			if ( ! $wp_filesystem->is_dir( $directory_path ) ) {
				$wp_filesystem->mkdir( $directory_path );
				error_log('Created folder '.$directory_path);
			}
			
			// Save compressed image to uploads directory
			$time_stamp = current_time('timestamp');
			$cover_file = $upload_dir['basedir'] . '/'. $user_id . '/cover_' . $time_stamp . '.jpg';
			$wp_filesystem->put_contents( $cover_file, $compressed_image, FS_CHMOD_FILE );
			
			// Update user meta with cover file URL
			update_user_meta($user_id, 'wpsn_custom_cover', $upload_dir['baseurl'] . '/' . $user_id . '/cover_' . $time_stamp . '.jpg');

			// Return success response
			$ret = array(
				'status' => 'ok',
				'text' => esc_html($cover_file)
			);
			
			// Restore the original memory limit
			ini_set('memory_limit', $original_memory_limit);
					
		} 


	} else {
		$ret = array(
			'status' => 'fail',
			'text' => 'Security error'
		);
	}
	echo wp_json_encode( $ret );
	exit();
}

?>