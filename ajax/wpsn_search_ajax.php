<?php

/* SEARCH */

function wpsn_do_search() {

	global $wpdb, $current_user;

	if (is_user_logged_in()) {

		if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	
		
			$search = strtolower( sanitize_text_field( wp_unslash ($_POST['search']) ) );

			// Get all users
			$users = get_users();
			$results = [];

			foreach ($users as $user) {
				$first_name = get_user_meta($user->ID, 'first_name', true);
				$last_name = get_user_meta($user->ID, 'last_name', true);

				if (stripos($first_name, $search) !== false || stripos($last_name, $search) !== false) {
					$results[] = $user;
				}
			}
			
			$results = apply_filters('wpsn_search_results_filter', $results);

			$ret_values = [];
			if ($results) {
				foreach ($results as $user) {
					$avatar_url = wpsn_get_avatar($user->ID);
					
					$last_active_timestamp = get_user_meta( $user->ID, 'wpsn_last_active', true );
					if ($last_active_timestamp) {
						$diff = human_time_diff($last_active_timestamp, current_time('timestamp'));
						// Translators: %s is the time difference
						$last_active = sprintf(__('%s ago', 'wpsn-instant-social-network'), $diff);
					} else {
						$last_active = __('Never', 'wpsn-instant-social-network');
					}

					$ret_values[] = array(
						'ID' => $user->ID,
						'firstname' => get_user_meta($user->ID, 'first_name', true),
						'lastname' => get_user_meta($user->ID, 'last_name', true),
						'avatar_url' => $avatar_url,
						'last_active' => $last_active
					);
				}
				
				// Sort the $ret_values array using the custom sorting function
				usort($ret_values, 'wpsn_sort_by_last_active_desc');

				$ret = array(
					'status' => 'ok',
					'results' => $ret_values,
					'nonce' => get_transient('wpsn_nonce')
				);
			} else {
				$ret = array(
					'status' => 'none',
					'text' => 'No results',
				);
			}
			
		} else {
			
			$ret = array(
				'status' => 'fail',
				'text' => 'Security error',
			);

		}
		
	} else {
		
		$ret = array(
			'status' => 'fail',
			'text' => 'Not logged in',
		);

	}

	// Assuming you want to return the response as JSON
	echo wp_json_encode($ret);
	exit;

}
add_action('wp_ajax_wpsn_do_search', 'wpsn_do_search');

// Define a custom sorting function
function wpsn_sort_by_last_active_desc($a, $b) {
    if ($a['last_active'] == $b['last_active']) {
        return 0;
    }
    return ($a['last_active'] < $b['last_active']) ? 1 : -1;
}

?>