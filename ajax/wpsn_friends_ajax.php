<?php

/* FRIENDS - BUBBLE */

function wpsn_theme_friends() {
	
	global $wpdb, $current_user;
	
    $ret = '';

	if (is_user_logged_in()) {

		if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

			$friend_requests = get_user_meta($current_user->ID, 'wpsn_friend_requests_received', true);
			
			// Array to hold alerts with valid users
			$count = 0;

			// Loop through the alerts
			if ($friend_requests) {
				foreach ($friend_requests as $request) {
					// Check if the user (post_parent) exists
					$user = get_user_by('id', $request['from']);
					
					// If the user exists, add the alert to the valid alerts array
					if ($user) { $count++; }
				}
			}
		
			$ret = array(
				'status' => 'ok',
				'count' => $count
			);
		
		} else {
			$ret = array(
				'status' => 'invalid',
				'text' => 'Invalid security token (wpsn_theme_friends)',
			);
		}
	
	} else {
		$ret = array(
			'status' => 'loggedout',
			'text' => 'Not logged in',
		);
	}

	echo wp_json_encode($ret);
	exit;
}
add_action('wp_ajax_wpsn_theme_friends', 'wpsn_theme_friends');

/* REMOVE FRIENDS */

function wpsn_cancel_friend_remove() {

	global $current_user;
	
    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$user_id = $current_user->ID;
		$from_id = isset($_POST['from_id']) ? sanitize_text_field( wp_unslash ( $_POST['from_id'] ) ): '';
		
		if ($user_id != $from_id) {
			
			// Remove friends of the requesting user
			$wpsn_friends_array = array();
			$wpsn_friends = get_user_meta($from_id, 'wpsn_friends', true);
			
			if ($wpsn_friends) {
				foreach ($wpsn_friends as $friend) {
					if ($friend['ID'] != $user_id) { 
						$wpsn_friends_array[] = $friend;
					}
				}				
			}
							
			update_user_meta($from_id, 'wpsn_friends', $wpsn_friends_array);
							
			// Remove friends of the receiving user
			$wpsn_friends_array = array();
			$wpsn_friends = get_user_meta($user_id, 'wpsn_friends', true);
			
			if ($wpsn_friends) {
				foreach ($wpsn_friends as $friend) {
					if ($friend['ID'] != $from_id) { 
						$wpsn_friends_array[] = $friend;
					}
				}				
			}
							
			update_user_meta($user_id, 'wpsn_friends', $wpsn_friends_array);

			/* Done -------------------------------- */
			
			$ret = array(
				'status' => 'ok',
				'text' => 'Friend request cancelled'
			);
			
		} else {
			
			$ret = array(
				'status' => 'ok',
				'text' => 'Same user, so no action'
			);
			
		}
		
	} else {
		
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (wpsn_cancel_friend_remove)',
		);
		
	}			

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_cancel_friend_remove', 'wpsn_cancel_friend_remove');

/* ACCEPT REQUEST RECEIVED */

function wpsn_friend_accept_received() {

	global $current_user;
	
    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$user_id = $current_user->ID;
		$from_id = isset($_POST['from_id']) ? sanitize_text_field( wp_unslash ( $_POST['from_id'] ) ) : '';
		
		if ($user_id != $from_id) {
			
			/* Add friends ------------------------- */
			
			// Update friends of the requesting user
			$wpsn_friends_array = array();
			$wpsn_friends = get_user_meta($from_id, 'wpsn_friends', true);
			
			if ($wpsn_friends) {
				foreach ($wpsn_friends as $friend) {
					if ($friend['ID'] != $user_id) {
						$wpsn_friends_array[] = $friend;
					}
				}				
			}
			$new_record = array(
				'ID' => $user_id,
				'timestamp' => current_time( 'timestamp' )
			);
			$wpsn_friends_array[] = $new_record;
							
			update_user_meta($from_id, 'wpsn_friends', $wpsn_friends_array);
							
			// Update friends of the receiving user
			$wpsn_friends_array = array();
			$wpsn_friends = get_user_meta($user_id, 'wpsn_friends', true);
			
			if ($wpsn_friends) {
				foreach ($wpsn_friends as $friend) {
					if ($friend['ID'] != $from_id) {
						$wpsn_friends_array[] = $friend;
					}
				}				
			}
			$new_record = array(
				'ID' => $from_id,
				'timestamp' => current_time( 'timestamp' )
			);
			$wpsn_friends_array[] = $new_record;
							
			update_user_meta($user_id, 'wpsn_friends', $wpsn_friends_array);
			
			/* Remove requests --------------------- */
			
			// Remove sent
			$wpsn_requests_sent_array = array();
			$wpsn_requests_sent = get_user_meta($from_id, 'wpsn_friend_requests_sent', true);
			
			if ($wpsn_requests_sent) {
				
				foreach ($wpsn_requests_sent as $sent) {
					if ($sent['target'] != $user_id) { 
						// Not a match, so add
						$wpsn_requests_sent_array[] = $sent;
					}
				}
				
				// Update sent record
				update_user_meta($from_id, 'wpsn_friend_requests_sent', $wpsn_requests_sent_array);
				
			}
			
			$wpsn_requests_sent_array = array();
			$wpsn_requests_sent = get_user_meta($user_id, 'wpsn_friend_requests_sent', true);
			
			if ($wpsn_requests_sent) {
				
				foreach ($wpsn_requests_sent as $sent) {
					if ($sent['target'] != $from_id) { 
						// Not a match, so add
						$wpsn_requests_sent_array[] = $sent;
					}
				}
				
				// Update sent record
				update_user_meta($user_id, 'wpsn_friend_requests_sent', $wpsn_requests_sent_array);
				
			}
			
			$wpsn_requests_sent_array = array();
			$wpsn_requests_sent = get_user_meta($user_id, 'wpsn_friend_requests_sent', true);
			
			// Remove received
			$wpsn_requests_received_array = array();
			$wpsn_requests_received = get_user_meta($user_id, 'wpsn_friend_requests_received', true);
			
			if ($wpsn_requests_received) {
				
				foreach ($wpsn_requests_received as $received) {
					if ($received['from'] != $from_id) { 
						// Not a match, so add
						$wpsn_requests_received_array[] = $received;
					}
				}
				
				// Update received record
				update_user_meta($user_id, 'wpsn_friend_requests_received', $wpsn_requests_received_array);
				
			}

			$wpsn_requests_received_array = array();
			$wpsn_requests_received = get_user_meta($from_id, 'wpsn_friend_requests_received', true);
			
			if ($wpsn_requests_received) {
				
				foreach ($wpsn_requests_received as $received) {
					if ($received['from'] != $user_id) { 
						// Not a match, so add
						$wpsn_requests_received_array[] = $received;
					}
				}
				
				// Update received record
				update_user_meta($from_id, 'wpsn_friend_requests_received', $wpsn_requests_received_array);
				
			}

			/* Done -------------------------------- */
			
			// Alert
			// Translators: %s is the first name of the friend
			$msg = sprintf(__('%s has accepted your friend request!', 'wpsn-instant-social-network'), get_user_meta( $user_id, 'first_name', true ));
			wpsn_add_alert($user_id, $from_id, $user_id, 'friend', $msg);
			 
			$ret = array(
				'status' => 'ok',
				'text' => 'Friend request accepted'
			);
			
		} else {
			
			$ret = array(
				'status' => 'ok',
				'text' => 'Same user, so no action'
			);
			
		}
		
	} else {
		
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (wpsn_friend_accept_received)',
		);
		
	}			

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_friend_accept_received', 'wpsn_friend_accept_received');

/* CANCEL REQUEST RECEIVED */

function wpsn_cancel_friend_request_received() {

	global $current_user;
	
    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$from_id = isset($_POST['from_id']) ? sanitize_text_field( wp_unslash ( $_POST['from_id'] ) ) : '';
		$target_user_id = isset($_POST['tid']) ? sanitize_text_field( wp_unslash ( $_POST['tid'] ) ) : '';
		
		if ($from_id == $current_user->ID || $target_user_id == $current_user->ID) {
		
			// Remove from sent
			$wpsn_requests_sent_array = array();
			$wpsn_requests_sent = get_user_meta($from_id, 'wpsn_friend_requests_sent', true);
			
			if ($wpsn_requests_sent) {
				
				foreach ($wpsn_requests_sent as $sent) {
					if ($sent['target'] != $target_user_id) { 
						// Not a match, so add
						$wpsn_requests_sent_array[] = $sent;
					}
				}
				
				// Update sent record
				update_user_meta($from_id, 'wpsn_friend_requests_sent', $wpsn_requests_sent_array);
				
			}
			
			// Remove from received
			$wpsn_requests_received_array = array();
			$wpsn_requests_received = get_user_meta($target_user_id, 'wpsn_friend_requests_received', true);
			
			if ($wpsn_requests_received) {
				
				foreach ($wpsn_requests_received as $received) {
					if ($received['from'] != $from_id) { 
						// Not a match, so add
						$wpsn_requests_received_array[] = $received;
					}
				}
				
				// Update received record
				update_user_meta($target_user_id, 'wpsn_friend_requests_received', $wpsn_requests_received_array);
				
			}
			
		}

		$ret = array(
			'status' => 'ok',
			'text' => 'Friend request cancelled'
		);
					
	} else {
		
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (wpsn_cancel_friend_request_received)',
		);
		
	}			

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_cancel_friend_request_received', 'wpsn_cancel_friend_request_received');

/* CANCEL REQUEST */

function wpsn_cancel_friend_request() {

	global $current_user;
	
    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$user_id = $current_user->ID;
		$target_user_id = isset($_POST['tid']) ? sanitize_text_field( wp_unslash ( $_POST['tid'] ) ) : '';
		
		if ($user_id != $target_user_id) {
			
			// Remove from sent
			$wpsn_requests_sent_array = array();
			$wpsn_requests_sent = get_user_meta($user_id, 'wpsn_friend_requests_sent', true);
			
			if ($wpsn_requests_sent) {
				
				foreach ($wpsn_requests_sent as $sent) {
					if ($sent['target'] != $target_user_id) { 
						// Not a match, so add
						$wpsn_requests_sent_array[] = $sent;
					}
				}
				
				// Update sent record
				update_user_meta($user_id, 'wpsn_friend_requests_sent', $wpsn_requests_sent_array);
				
			}
			
			// Remove from received
			$wpsn_requests_received_array = array();
			$wpsn_requests_received = get_user_meta($target_user_id, 'wpsn_friend_requests_received', true);
			
			if ($wpsn_requests_received) {
				
				foreach ($wpsn_requests_received as $received) {
					if ($received['from'] != $user_id) { 
						// Not a match, so add
						$wpsn_requests_received_array[] = $received;
					}
				}
				
				// Update received record
				update_user_meta($target_user_id, 'wpsn_friend_requests_received', $wpsn_requests_received_array);
				
			}

			$ret = array(
				'status' => 'ok',
				'text' => 'Friend request cancelled'
			);
			
		} else {
			
			$ret = array(
				'status' => 'ok',
				'text' => 'Same user, so no action'
			);
			
		}
		
	} else {
		
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (wpsn_cancel_friend_request)',
		);
		
	}			

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_cancel_friend_request', 'wpsn_cancel_friend_request');


/* ADD REQUEST */

function wpsn_add_friend_request() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$user_id = isset($_POST['uid']) ? sanitize_text_field( wp_unslash ( $_POST['uid'] ) ) : '';
		$target_user_id = isset($_POST['tid']) ? sanitize_text_field( wp_unslash ( $_POST['tid'] ) ) : '';
		
		if ($user_id != $target_user_id) {
			
			$wpsn_requests_sent_array = array();
			
			$wpsn_requests_sent = get_user_meta($user_id, 'wpsn_friend_requests_sent', true);
			
			$do_update = false;
			if ($wpsn_requests_sent) {
				
				$found = false;
				foreach ($wpsn_requests_sent as $sent) {
					if ($sent['target'] == $target_user_id) { $found = true; }
					$wpsn_requests_sent_array[] = $sent;
				}
				if (!$found) {
					$new_record = array(
						'target' => $target_user_id,
						'timestamp' => current_time( 'timestamp' )
					);
					$wpsn_requests_sent_array[] = $new_record;
					
					$do_update = true;
				}
				
			} else {
				
				$new_record = array(
					'target' => $target_user_id,
					'timestamp' => current_time( 'timestamp' )
				);				
				$wpsn_requests_sent_array[] = $new_record;
				
				$do_update = true;
			}
			
			if ($do_update) {
				
				// Update sent record
				update_user_meta($user_id, 'wpsn_friend_requests_sent', $wpsn_requests_sent_array);
				
				// Update equivalent received record
				$wpsn_requests_received_array = array();
				
				$wpsn_requests_received = get_user_meta($target_user_id, 'wpsn_friend_requests_received', true);
				
				$do_update = false;
				if ($wpsn_requests_received) {
					
					$found = false;
					foreach ($wpsn_requests_received as $received) {
						if ($received['from'] == $user_id) { $found = true; }
						$wpsn_requests_received_array[] = $received;
					}
					if (!$found) {
						$new_record = array(
							'from' => $user_id,
							'timestamp' => current_time( 'timestamp' )
						);
						$wpsn_requests_received_array[] = $new_record;
						
						$do_update = true;
					}
				
				} else {
					
					$new_record = array(
						'from' => $user_id,
						'timestamp' => current_time( 'timestamp' )
					);				
					$wpsn_requests_received_array[] = $new_record;
					
					$do_update = true;
				}
				
				if ($do_update) {
					update_user_meta($target_user_id, 'wpsn_friend_requests_received', $wpsn_requests_received_array);
				}
			}

			$ret = array(
				'status' => 'ok',
				'text' => 'Friend request sent'
			);
			
			// Alert
			// Translators: %s is the first name of the friend
			$msg = sprintf(__('You have a friend request from %s!', 'wpsn-instant-social-network'), get_user_meta( $user_id, 'first_name', true ));
			wpsn_add_alert($user_id, $target_user_id, $user_id, 'friend', $msg);

			
		} else {
			
			$ret = array(
				'status' => 'ok',
				'text' => 'Same user, so no action'
			);
			
		}
		
	} else {
		
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (wpsn_add_friend_request)',
		);
		
	}			

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_add_friend_request', 'wpsn_add_friend_request');


/* FEED */

function wpsn_get_friends() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$vid = isset($_POST['vid']) ? sanitize_text_field( wp_unslash ( $_POST['vid'] ) ) : '';

		// Get friend requests sent
		$wpsn_requests_sent = get_user_meta($vid, 'wpsn_friend_requests_sent', true);
		$sentArray = array();
		if ($wpsn_requests_sent) {
			foreach ($wpsn_requests_sent as $sent) {
				$id = $sent['target'];		
				if (get_user_by('id', $id)) {
					$avatar_url = wpsn_get_avatar($id);
					$display_name = get_user_meta( $id, 'first_name', true ) . ' ' . get_user_meta( $id, 'last_name', true );
					
					$diff = human_time_diff($sent['timestamp'], current_time('timestamp'));
					// Translators: %s is the time difference
					$requested = sprintf(__('%s ago', 'wpsn-instant-social-network'), $diff);
					$daysago = __('Requested', 'wpsn-instant-social-network').' '.$requested;
					
					$details = array(
						'id' => $id,
						'avatar_url' => $avatar_url,
						'display_name' => $display_name,
						'daysago' => $daysago,
						'seconds_since_active' => $seconds_since_active, // Include for sorting
						'dot_class' => wpsn_user_dot($id)
					);
					$sentArray[] = $details;
				}
			}
			// Sort the array by timestamp in descending order
			usort($sentArray, function($a, $b) {
				return $a['seconds_since_active'] - $b['seconds_since_active'];
			});
		}
		
		// Get friend requests received
		$wpsn_requests_received = get_user_meta($vid, 'wpsn_friend_requests_received', true);
		$receivedArray = array();
		if ($wpsn_requests_received) {
			foreach ($wpsn_requests_received as $sent) {
				$id = $sent['from'];		
				if (get_user_by('id', $id)) {				
					$avatar_url = wpsn_get_avatar($id);
					$display_name = get_user_meta( $id, 'first_name', true ) . ' ' . get_user_meta( $id, 'last_name', true );
					
					$diff = human_time_diff($sent['timestamp'], current_time('timestamp'));
					// Translators: %s is the time difference
					$requested = sprintf(__('%s ago', 'wpsn-instant-social-network'), $diff);
					$daysago = __('Requested', 'wpsn-instant-social-network').' '.$requested;
					$seconds_since_active = wpsn_get_seconds_since_active($id);
					$dot_class = 'wpsn_active_green';
					if ($seconds_since_active > 60) { $dot_class = 'wpsn_active_amber'; }
					if ($seconds_since_active > 300) { $dot_class = 'wpsn_active_none'; }

					$details = array(
						'id' => $id,
						'avatar_url' => $avatar_url,
						'display_name' => $display_name,
						'daysago' => $daysago,
						'seconds_since_active' => $seconds_since_active, // Include for sorting
						'dot_class' => $dot_class
					);
					$receivedArray[] = $details;
				}
			}
			// Sort the array by timestamp in descending order
			usort($receivedArray, function($a, $b) {
				return $a['seconds_since_active'] - $b['seconds_since_active'];
			});
		}
		
		// Get friends
		$wpsn_friends = get_user_meta($vid, 'wpsn_friends', true);
		$friendsArray = array();
		if ($wpsn_friends) {
			foreach ($wpsn_friends as $friend) {
				$id = $friend['ID'];	
				if (get_user_by('id', $id)) {
					$avatar_url = wpsn_get_avatar($id);
					$display_name = get_user_meta( $id, 'first_name', true ) . ' ' . get_user_meta( $id, 'last_name', true );
					
					$diff = human_time_diff($friend['timestamp'], current_time('timestamp'));
					// Translators: %s is the time difference
					$daysago = sprintf(__('Friends for %s', 'wpsn-instant-social-network'), $diff);
					$seconds_since_active = wpsn_get_seconds_since_active($id);
					$dot_class = 'wpsn_active_green';
					if ($seconds_since_active > 60) { $dot_class = 'wpsn_active_amber'; }
					if ($seconds_since_active > 300) { $dot_class = 'wpsn_active_none'; }

					$details = array(
						'id' => $id,
						'avatar_url' => $avatar_url,
						'display_name' => $display_name,
						'daysago' => $daysago,
						'seconds_since_active' => $seconds_since_active, // Include for sorting
						'dot_class' => $dot_class
					);
					$friendsArray[] = $details;
				}
			}
			// Sort the array by timestamp in descending order
			usort($friendsArray, function($a, $b) {
				return $a['seconds_since_active'] - $b['seconds_since_active'];
			});
		}

		$ret = array(
			'status' => 'ok',
			'requests_sent' => $sentArray,
			'requests_received' => $receivedArray,
			'friends' => $friendsArray
		);
		
	} else {
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (wpsn_get_friends)',
		);
	}			

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_get_friends', 'wpsn_get_friends');

/* SIDE BAR - FRIENDS */

function wpsn_side_bar_friends() {	

	global $current_user;
	
    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$uid = isset($_POST['uid']) ? sanitize_text_field( wp_unslash ( $_POST['uid'] ) ) : '';
		$mode = isset($_POST['mode']) ? sanitize_text_field( wp_unslash ( $_POST['mode'] ) ) : 'new';
		$pid = isset($_POST['pid']) ? sanitize_text_field( wp_unslash ( $_POST['pid'] ) ) : 0;
		
		$is_friend = wpsn_are_friends($current_user->ID, $uid);
		$friends_public = get_user_meta($uid, 'wpsn_friends_public', true);

		// Get friends
		$wpsn_friends = get_user_meta($uid, 'wpsn_friends', true);
		$friendsArray = array();
		$count = 0;
		if ( $wpsn_friends && (($is_friend || $friends_public)) ) {
			foreach ($wpsn_friends as $friend) {
				$count++;
				if ($count <= 10) {
					$id = $friend['ID'];
					if (get_user_by('id', $id)) {					
						$avatar_url = wpsn_get_avatar($id);
						$display_name = get_user_meta( $id, 'first_name', true ) . ' ' . get_user_meta( $id, 'last_name', true );
						$last_active_timestamp = get_user_meta( $id, 'wpsn_last_active', true );
						$diff = human_time_diff($last_active_timestamp, current_time('timestamp'));
						// Translators: %s is the time difference
						$last_active = sprintf(__('%s ago', 'wpsn-instant-social-network'), $diff);
						$seconds_since_active = wpsn_get_seconds_since_active($id);
						$dot_class = 'wpsn_active_green';
						if ($seconds_since_active > 60) { $dot_class = 'wpsn_active_amber'; }
						if ($seconds_since_active > 300) { $dot_class = 'wpsn_active_none'; }
						
						$details = array(
							'id' => $id,
							'avatar_url' => $avatar_url,
							'display_name' => $display_name,
							'last_active' => $last_active,
							'last_active_timestamp' => $last_active_timestamp,
							'dot_class' => $dot_class
						);
						$friendsArray[] = $details;
					}
				}
			}
			usort($friendsArray, 'wpsn_compare_last_active_timestamp_desc');
		}
		
		// Provide hook to filter array, including useful parameters
		$friendsArray = apply_filters('wpsn_side_bar_friends_filter', $friendsArray, $pid, $mode);
		
		$ret = array(
			'status' => 'ok',
			'data' => $friendsArray,
			'count' => $count
		);
		
	} else {

		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (wpsn_side_bar_friends)',
		);
		
	}

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_side_bar_friends', 'wpsn_side_bar_friends');

// Function to compare the last_active_timestamp field
function wpsn_compare_last_active_timestamp_desc($a, $b) {
    return $b['last_active_timestamp'] <=> $a['last_active_timestamp'];
}

?>