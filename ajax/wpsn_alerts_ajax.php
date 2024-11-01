<?php

/* ALERTS - BUBBLE */

function wpsn_theme_alerts() {
	
	global $wpdb, $current_user;
	
    $ret = '';
	
	if (is_user_logged_in()) {

		if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		// Arguments for WP_Query
		$args = array(
			'post_type'      => 'wpsn-alert',
			'post_status'    => 'unread',
			'post_parent'    => $current_user->ID
		);

		// Query for the unread alerts
		$unread_alerts_query = new WP_Query($args);

		// Get the IDs of the posts (post_parent in this case)
		$unread_alerts = $unread_alerts_query->posts;

			// Array to hold alerts with valid users
			$count = 0;

			// Loop through the alerts
			if ( ! empty( $unread_alerts ) ) {
				foreach ($unread_alerts as $alert) {
					// Check if the user (post_parent) exists
					$user = get_user_by('id', $alert->post_parent);
					
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
				'text' => 'Invalid security token (wpsn_theme_alerts)',
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
add_action('wp_ajax_wpsn_theme_alerts', 'wpsn_theme_alerts');

/* ALERTS - GET ALERTS */

function wpsn_get_alerts() {

	global $wpdb, $current_user;
	
	$ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$start = isset($_POST['start']) 			? sanitize_text_field( wp_unslash ( $_POST['start'] ) )						: 0;
		$limit = get_option( 'wpsn_alert_count' ) 	? sanitize_text_field( wp_unslash ( get_option( 'wpsn_alert_count' ) ) ) 	: 100;		
		
		$uid = $current_user->ID;

		// Fetch results

		// All Results
		$args = array(
			'post_type'      => 'wpsn-alert',
			'post_parent'    => $uid,
			'posts_per_page' => -1, // Retrieve all posts matching the criteria
		);
		$query = new WP_Query( $args );
		$all_results_count = $query->found_posts;
		$all_results_count -= $start;
		wp_reset_postdata();

		// Limited results
		$args = array(
			'post_type'      => 'wpsn-alert',
			'post_parent'    => $uid,
			'orderby'        => 'post_date',
			'order'          => 'DESC',
			'posts_per_page' => $limit,
			'offset'         => $start,
		);
		$query = new WP_Query($args);
		$results = array();

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$post_id = get_the_ID();
				$post_author_id = get_the_author_meta('ID');
				$post_parent_id = wp_get_post_parent_id($post_id);

				$meta_value = get_post_meta($post_id, 'wpsn_alert_post_id', true);

				// Get author details
				$author_data = get_userdata($post_author_id);
				$author_first_name = $author_data ? $author_data->first_name : '';
				$author_last_name = $author_data ? $author_data->last_name : '';

				// Get parent post author details if the post has a parent
				$parent_author_first_name = '';
				$parent_author_last_name = '';

				if ($post_parent_id) {
					$parent_post = get_post($post_parent_id);
					if ($parent_post) {
						$parent_author_id = $parent_post->post_author;
						$parent_author_data = get_userdata($parent_author_id);
						$parent_author_first_name = $parent_author_data ? $parent_author_data->first_name : '';
						$parent_author_last_name = $parent_author_data ? $parent_author_data->last_name : '';
					}
				}
				
				$result = array(
					'ID'                       => $post_id,
					'post_title'               => get_the_title(),
					'post_content'             => get_the_content(),
					'wpsn_alert_post_id'       => $meta_value,
					'post_author'              => $post_author_id,
					'post_parent'              => $post_parent_id,
					'post_date'                => get_the_date('Y-m-d H:i:s'),
					'post_status'              => get_post_status($post_id),
					'author_first_name'        => $author_first_name,
					'author_last_name'         => $author_last_name,
					'parent_author_first_name' => $parent_author_first_name,
					'parent_author_last_name'  => $parent_author_last_name,
				);
				
				$result = apply_filters('wpsn_alert_filter', $result, $uid);
				
				if (!empty($result)) {
					$results[] = $result;
				}
			}
		}

		wp_reset_postdata();
		
		$remaining = $all_results_count - $limit;

		if (count($results) > 0) {
			
			// Loop through results
			$resultsArray = array();
			foreach ($results as $result) {
				// Extract post ID
				$post_id = $result["ID"];
				
				if (get_user_by('id', $result["post_author"])) {
					
					$from_display_name = $result["author_first_name"] . ' ' . $result["author_last_name"];
					$to_display_name = $result["parent_author_first_name"] . ' ' . $result["parent_author_last_name"];
					
					$diff = human_time_diff(strtotime($result["post_date"]), current_time('timestamp'));
					// Translators: %s is the time difference
					$date = sprintf(__('%s ago', 'wpsn-instant-social-network'), $diff);
					
					$resultsArray[] = array(
						"from_id" 		=> $result["post_author"],
						"to_id" 		=> $result["post_parent"],
						"from_name" 	=> $from_display_name,
						"to_name"		=> $to_display_name,
						"from_avatar"	=> wpsn_get_avatar($result["post_author"]),
						"type"			=> $result["post_title"],
						"post_content"	=> $result["post_content"],
						"post_id"		=> $result["wpsn_alert_post_id"],
						"date"			=> $date,
						"unread"		=> $result["post_status"] == 'publish' ? 0 : 1
					);
					
					// Mark alert as read
					wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
					
				}
			}
			
			// Sort $resultsArray by post_status descending
			if (!empty($resultsArray)) {
				usort($resultsArray, function($a, $b) {
					return strcmp($b['unread'], $a['unread']);
				});
			}
			
			$ret = array(
				'status' => 'ok',
				'text' => 'Results returned',
				'data' => $resultsArray,
				'offset' => $start + count($results),
				'remaining' => $remaining,
				'limit' => $limit
			);
			
		} else {
			
			$ret = array(
				'status' => 'none',
				'text' => 'No results returned',
			);
			
		}
				
	} else {
		
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (wpsn_get_alerts)',
		);
		
	}			

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_get_alerts', 'wpsn_get_alerts');

/* ALERTS - GET ALERTS */

function wpsn_clear_alerts() {

	global $wpdb, $current_user;
	
	$ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$uid = $current_user->ID;
		
		// Set up the query to find all posts of type 'wpsn-alert' by the specified author
		$args = array(
			'post_type'      => 'wpsn-alert',
			'post_status'    => 'any',
			'post_parent'    => $uid,
			'posts_per_page' => -1, // Retrieve all matching posts
		);

		$query = new WP_Query($args);

		// Check if there are any posts to delete
		if ($query->have_posts()) {
			// Loop through the posts and delete each one permanently
			while ($query->have_posts()) {
				$query->the_post();
				wp_delete_post(get_the_ID(), true); // true ensures the post is deleted permanently
			}
			// Restore original Post Data
			wp_reset_postdata();
		}
			
		$ret = array(
			'status' => 'ok',
			'text' => 'All alerts deleted',
		);
				
	} else {
		
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (wpsn_clear_alerts)',
		);
		
	}			

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_clear_alerts', 'wpsn_clear_alerts');

?>