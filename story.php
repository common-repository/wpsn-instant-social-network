<?php

function wpsn_sc_profile_home() {
	
    global $current_user;
    $html = '';

    if (is_user_logged_in()) {
		
        if (isset($_GET['uid']) ) {
			if ( !isset($_GET['wpsn']) || wp_verify_nonce( sanitize_text_field( wp_unslash ( $_GET['wpsn'] ) ), 'wpsn_nonce' ) ) {
				$uid = sanitize_text_field( wp_unslash ( $_GET['uid'] ) );
			} else {
				die('Nonce error!');
			}
        } else {
            $uid = $current_user->ID;
        }
		
		$html .= '<div class="wpsn-wrapper">';
	
			$html .= wpsn_sc_profile_header($uid);

			$wpsn_side_activity = get_option( 'wpsn_side_activity' );
			if ($wpsn_side_activity === false) {
				$wpsn_side_activity = 1;
				update_option('wpsn_side_activity', $wpsn_side_activity);
			}
			
			if ($wpsn_side_activity) {
				
				$html .= '<div class="wpsn-home-and-activity-container">';
					$html .= '<div class="wpsn-profile-left-column">';
					
						$html .= wpsn_sc_profile_sidebar_photos($uid);
						$html .= wpsn_sc_activity_sidebar_friends($uid, true);
						
						$more = apply_filters('wpsn_side_activity_filter', $uid, '');
						$html .= $more;
						
					$html .= '</div>';
					$html .= '<div class="wpsn-profile-right-column">';
						$html .= wpsn_sc_feed_post();
						$html .= wpsn_sc_feed_show($uid);
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
	

function wpsn_sc_profile_header($uid) {

    global $current_user;
    $html = '';

	$first_name = get_user_meta( $current_user->ID, 'first_name', true );
	$last_name = get_user_meta( $current_user->ID, 'last_name', true );
	
	if (!$first_name || !$last_name) {
		
		$html .= 'Please update your <a href="'.wpsn_page('edit-profile').'">profile details</a>.';
		
	} else {
		
		$is_friend = wpsn_are_friends($current_user->ID, $uid);
		
		$display_name = wpsn_get_display_name($uid);
		
		$profile_public = get_user_meta($uid, 'wpsn_profile_public', true);
		$friends_public = get_user_meta($uid, 'wpsn_friends_public', true);

		$html .= '<div class="wpsn-container">';
			
			$the_cover = get_user_meta($uid, 'wpsn_custom_cover', true);
			if (!$the_cover) {
				$the_cover = plugins_url() . '/wpsn-instant-social-network/img/placeholder_cover.jpg';
			}
			$avatar_url = wpsn_get_avatar($uid);
			
			$html .= '<img src="'.$the_cover.'" class="wpsn-background-image" alt="'.$display_name.'\'s cover image" />';
			
			$html .= '<div class="wpsn-overlay-image-container">';
				$html .= '<img src="'.$avatar_url.'" class="wpsn-overlay-image" alt="'.$display_name.'\'s profile avatar" >';
				$seconds_since_active = wpsn_get_seconds_since_active($uid);
				$html .= '<div class="wpsn-home-page-avatar-dot '.wpsn_user_dot($uid).'"></div>';
			$html .= '</div>';
			
			if ($uid == $current_user->ID) {
				$html .= '<a href="'.wpsn_page('edit-profile').'#avatar-cover-change"><div class="wpsn-edit-avatar-and-cover wpsn_hide"><i class="fa-solid fa-camera"></i>&nbsp;&nbsp;'.__('Change', 'wpsn-instant-social-network').'</div></a>';
			}
			
		$html .= '</div>';

		$html .= '<div class="wpsn-header-footer">';
	
			$html .= '<div class="wpsn-header-row">';

				$html .= '<div class="wpsn-header-actions">';
				
					/* FOR CURRENT USER */
				
					if ($uid == $current_user->ID) {

						/* ALERTS */
						
						$args = array(
							'post_type'      => 'wpsn-alert',
							'post_status'    => 'unread',
							'post_parent'    => $current_user->ID,
							'posts_per_page' => -1, // Retrieve all posts that match the criteria
						);

						// Create a new WP_Query instance
						$query = new WP_Query( $args );

						// Get the results
						$unread_alerts = $query->posts;

						// Reset the post data
						wp_reset_postdata();

						if ($unread_alerts) {
							$count = count($unread_alerts) <= 9 ? count($unread_alerts) : '9+';
							if ($count > 0) {
								$html .= '<div class="wpsn-button-submit-small wpsn-story-action-alerts"><i class="fa-solid fa-bell"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Alerts', 'wpsn-instant-social-network').'</span>';
								$html .= '<div class="wpsn-story-action-alerts-count">'.$count.'</div>';
								$html .= '</div>';
							}
						}
						
						$html = apply_filters('wpsn_sc_profile_header_filter', $html);
						
					}
					
					/* IF A FRIEND */
					
					if ($is_friend) {
						if ($uid != $current_user->ID) {

							$html .= '<div class="wpsn-button-submit-small wpsn_cancel_friend_remove" data-wpsn-from-id="'.$uid.'"><i class="fa-solid fa-user-xmark"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Cancel Friendship', 'wpsn-instant-social-network').'</span></div>';
							
							// Apply the filter
							$html = apply_filters('wpsn_filter_profile_friend_actions', $html, $uid);

						}
					} else {
						
					/* IF NOT A FRIEND */	
						
						if ($uid != $current_user->ID) {
							// First check if you have sent this user a friend request
							$wpsn_requests_received = get_user_meta($uid, 'wpsn_friend_requests_received', true);
							$found = false;
							if ($wpsn_requests_received) {
								foreach ($wpsn_requests_received as $received) {
									if ($received['from'] == $current_user->ID) { $found = true; }
								}
							}
							if (!$found) {
								// If not found, check first if you have received a request from this user
								$wpsn_requests_sent = get_user_meta($uid, 'wpsn_friend_requests_sent', true);
								$found = false;
								if ($wpsn_requests_sent) {
									foreach ($wpsn_requests_sent as $sent) {
										if ($sent['target'] == $current_user->ID) { $found = true; }
									}
									if (!$found) {
										// Not found, so can request a friendship
										$html .= '<div class="wpsn-button-submit-small wpsn-story-action-friends-request"><i class="fa-solid fa-user-plus"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Request Friendship', 'wpsn-instant-social-network').'</span></div>';
									} else {
										// Found, so allow to accept or cancel
										$html .= '<div class="wpsn-button-submit-small wpsn-story-action-friends-cancel-request wpsn-on-header" data-wpsn-from-id="'.$uid.'" data-wpsn-target="'.$current_user->ID.'"><i class="fa-solid fa-user-xmark"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Reject Friendship', 'wpsn-instant-social-network').'</span></div>';
										$html .= '<div class="wpsn-button-submit-small wpsn-story-action-friends-accept-request wpsn-on-header" data-wpsn-from-id="'.$uid.'"><i class="fa-solid fa-user-check"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Accept Friendship', 'wpsn-instant-social-network').'</span></div>';
									}
								} else {
									// Not found, so can request a friendship
									$html .= '<div class="wpsn-button-submit-small wpsn-story-action-friends-request"><i class="fa-solid fa-user-plus"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Request Friendship', 'wpsn-instant-social-network').'</span></div>';
								}
							} else {
								// If found, can cancel request
								$html .= '<div class="wpsn-button-submit-small wpsn-story-action-friends-cancel-request"><i class="fa-solid fa-user-xmark"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Cancel Friend Request', 'wpsn-instant-social-network').'</span></div>';
							}
						}
					}
				$html .= '</div>';
				
				$html .= '<div class="wpsn-header-info">';
					$html .= '<!-- More info to show -->';
					$html = apply_filters('wpsn_filter_profile_header_actions', $html, $uid);
				$html .= '</div>';
				
				$html .= '<div class="wpsn-header-name">'.$display_name.'</div>';
				
			$html .= '</div>'; // wpsn-header-row
		
		$html .= '</div>'; // wpsn-header-footer

		
	}

    return $html;
	
}

function wpsn_sc_feed_post($reply_id = '') {

    global $current_user;
    $html = '';

	if ( (isset($_GET['uid']) && (!isset($_GET['wpsn']) || wp_verify_nonce( sanitize_text_field( wp_unslash ( $_GET['wpsn'] ) ), 'wpsn_nonce' )) && (is_numeric($_GET['uid']))) ) {
		$target_id = sanitize_text_field( wp_unslash ( $_GET['uid'] ) );
	} else {
		$target_id = $current_user->ID;
	}
	
	$first_name = get_user_meta( $current_user->ID, 'first_name', true );
	$last_name = get_user_meta( $current_user->ID, 'last_name', true );
	
	if (!$first_name || !$last_name) {
		
		// Need to update profile details
		
	} else {
	
		if (wpsn_are_friends($current_user->ID, $target_id)) {
		
			$target_name = get_user_meta( $target_id, 'first_name', true ) . ' ' . get_user_meta( $target_id, 'last_name', true );

			$the_author_name = $first_name . ' ' . $last_name;
			$the_avatar = wpsn_get_avatar($current_user->ID);
			
			$html .= '<div id="wpsn-author-id" class="wpsn-hidden">'.$current_user->ID.'</div>';
			$html .= '<div id="wpsn-user-name" class="wpsn-hidden">'.$the_author_name.'</div>';
			$html .= '<div id="wpsn-user-avatar-url" class="wpsn-hidden">'.$the_avatar.'</div>';
			
			$html .= '<div id="wpsn-target-id" class="wpsn-hidden">'.$target_id.'</div>';
			$html .= '<div id="wpsn-target_name" class="wpsn-hidden">'.$target_name.'</div>';
			
			if (wpsn_are_friends($current_user->ID, $target_id)) {

						$html_placeholder = '<div class="wpsn-feed-post-row">';
							$prompt = get_option( 'wpsn_prompt' ) !== false ? get_option( 'wpsn_prompt' ) : 'What\'s up?';
							$prompt = stripslashes($prompt);
							$html_placeholder .= '<div id="wpsn-feed-post-placeholder" class="wpsn-feed-post-placeholder">'.esc_html($prompt).'</div>';
						$html_placeholder .= '</div>';

				
				$html_placeholder = apply_filters('wpsn_placeholder_filter', $html_placeholder, $current_user->ID, $target_id);
				
				$html .= $html_placeholder;
				
			}
			
			$html .= '<div id="wpsn_new_post" class="wpsn_post_popup wpsn_hide">';

				$html .= '<div class="wpsn_reply_pid wpsn_hide">'.$reply_id.'</div>';
				$html .= '<div class="wpsn_reply_indent wpsn_hide"></div>';
				
				$html .= '<div class="wpsn_post_popup_inner">';
					$html .= '<div class="wpsn-feed-post-row wpsn-hidden">';
						$html .= '<input id="wpsn-feed-post-input-subject" type="text" class="wpsn-feed-post-input-subject" />';
						$html .= '<label for="wpsn-feed-post-input-subject" class="wpsn-hidden">Post Subject text area</label>';
					$html .= '</div>';
					$html .= '<div class="wpsn-feed-post-row">';
						$html .= '<textarea id="wpsn-feed-post-input" type="text" class="wpsn-autosize wpsn-feed-post-input"></textarea>';
						$html .= '<label for="wpsn-feed-post-input" class="wpsn-hidden">Post Edit text area</label>';
					$html .= '</div>';
					$html .= '<div class="wpsn-feed-post-row wpsn-feed-post-actions">';
						$html .= '<div class="wpsn_post_popup_cancel"><div>'.__('Cancel', 'wpsn-instant-social-network').'</div></div>';
						$html .= '<button class="wpsn-button-submit feed-post-save wpsn-button-submit-wide" data-wpsn-mode="" data-wpsn-post-id="">'.__('Post', 'wpsn-instant-social-network').'</button>';
						$html .= '<label for="wpsn_fileInput" class="wpsn-button-submit wpsn-custom-file-input"><i class="fa-solid fa-image"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Add Images', 'wpsn-instant-social-network').'</span></label>';
					$html .= '</div>';
					$html .= '<div class="wpsn-feed-post-row wpsn-file-input-container">';
						$html .= '<form id="uploadForm" enctype="multipart/form-data">';
							$html .= '<input type="file" id="wpsn_fileInput" class="wpsn-file-input" name="files[]" multiple>';
						$html .= '</form>';
					$html .= '</div>';
					$html .= '<div id="wpsn-existingFileList"></div>';
					$html .= '<div id="wpsn-fileList"></div>';
				$html .= '</div>';
			$html .= '</div>';
			
		}
	}


    return $html;

}

function wpsn_sc_feed_show($uid, $post_type = 'wpsn-feed', $activity_type = 'home') {

    global $current_user;
    $html = '';

	$first_name = get_user_meta( $current_user->ID, 'first_name', true );
	$last_name = get_user_meta( $current_user->ID, 'last_name', true );
	
	if (!$first_name || !$last_name) {
		
		// Need to update profile details
		
	} else {
		
		$profile_public = get_user_meta($uid, 'wpsn_profile_public', true);
		if ($profile_public || wpsn_are_friends($current_user->ID, $uid)) {

				$html .= '<div data-wpsn-id="'.$uid.'" data-wpsn-post-type="'.$post_type.'" class="wpsn-feed-posts" data-wpsn-activity-mode="'.$activity_type.'">';
					$html .= '<i class="wpsn-background-text-color fa-solid fa-spinner fa-spin"></i>';
				$html .= '</div>';
			
			// Story Init Function
			$html .= apply_filters('wpsn_story_init', '');

		} else {
			$html .= '<p>This profile is only visible to friends.</p>';
		}
		
	}
		
    return $html;

}

function wpsn_sc_profile_sidebar_photos($uid) {

    global $current_user;
    $html = '';

	$first_name = get_user_meta( $current_user->ID, 'first_name', true );
	$last_name = get_user_meta( $current_user->ID, 'last_name', true );
	
	if (!$first_name || !$last_name) {
		
		// Need to update profile details
		
	} else {

		$profile_public = get_user_meta($uid, 'wpsn_profile_public', true);
		if ($profile_public || wpsn_are_friends($current_user->ID, $uid)) {

			$html .= '<div class="wpsn-wrapper-sidebar">';
		
				$html .= '<div class="wpsn-side-bar-photos">';
					$html .= '<h2>'.__('Recent Photos', 'wpsn-instant-social-network').'</h2>';
					$html .= '<div class="wpsn-side-bar-photos-content">';
					$html .= '</div>';
				$html .= '</div>';
				
			$html .= '</div>';
			
		}
		
	}
		
    return $html;
	
}
?>