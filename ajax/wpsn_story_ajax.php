<?php

/* GET MEMBER STATUS */

function wpsn_get_user_status() {	

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$uid = isset($_POST['uid']) ? sanitize_text_field( wp_unslash ( $_POST['uid'] ) ) : '';
		
		$seconds_since_active = wpsn_get_seconds_since_active($uid);
		$dot_class = 'wpsn_active_green';
		if ($seconds_since_active > 60) { $dot_class = 'wpsn_active_amber'; }
		if ($seconds_since_active > 300) { $dot_class = 'wpsn_active_none'; }
		
		$ret = array(
			'status' => 'ok',
			'dot_class' => $dot_class
		);
		
	} else {

		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (wpsn_get_user_status)',
		);
		
	}

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_get_user_status', 'wpsn_get_user_status');

/* SIDE BAR - PHOTOS */

function wpsn_side_bar_photos() {	

	global $current_user;
	
    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$uid = isset($_POST['uid']) ? sanitize_text_field( wp_unslash ( $_POST['uid'] ) ) : '';
		
		$is_friend = wpsn_are_friends($current_user->ID, $uid);
		$friends_public = get_user_meta($uid, 'wpsn_friends_public', true);

		// Get photos
		
		$wpsn_home_photos_check = get_option ('wpsn_home_photos_check');
		if ($wpsn_home_photos_check == false) { $wpsn_home_photos_check = 200; }
		
		$wpsn_home_photos_include = get_option ('wpsn_home_photos_include');
		if ($wpsn_home_photos_include == false) { $wpsn_home_photos_include = 30; }
		
		$images_array = array();
		
		// Get posts
		$args = array(
			'post_type'      => 'wpsn-feed', // Custom post type
			'post_status'	 => 'publish',
			'posts_per_page' => $wpsn_home_photos_check, // Number of posts to retrieve
			'author'         => $uid, // Filter by user ID
			'orderby'        => 'date', // Order by date
			'order'          => 'DESC' // Most recent first
		);

		// Create a new query
		$query = new WP_Query($args);
		$included = 0;

		// Check if there are posts
		if ($query->have_posts()) {
			// Loop through the posts
			while ($query->have_posts() && $included < $wpsn_home_photos_include) {
				$query->the_post();
				// Get images				
				$attachments = get_post_meta(get_the_ID(), 'wpsn_post_files', true);
				$images = unserialize($attachments);
							
				if (!empty($images)) {
					$included++;
					foreach ($images as $file_path) {
						$file_path = str_replace('-big.jpg', '-thumb.jpg', $file_path);
						$images_array[] = array(
							"thumb" => $file_path,
							"post_id" => get_the_ID()
						);
					}														
				}
				
			}

		}

		// Restore original post data
		wp_reset_postdata();
		
		$ret = array(
			'status' => 'ok',
			'text' => 'Images returned',
			'images' => $images_array
		);
		
	} else {

		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (wpsn_side_bar_photos)',
		);
		
	}

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_side_bar_photos', 'wpsn_side_bar_photos');
	
/* FEED */

function wpsn_insert_feed_post() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		global $current_user;
		global $wp_filesystem;

		if ( ! WP_Filesystem() ) {
			// Failed to initialize WP_Filesystem, handle error.
			error_log ('--------------- Failed to initialize WP_Filesystem (wpsn_insert_feed_post).');
			return;
		}
			
        $mode = isset($_POST['mode']) 						? sanitize_text_field( wp_unslash ( $_POST['mode'] ) )				: '';
		$feed_subject = isset($_POST['feed_subject']) 		? sanitize_textarea_field( wp_unslash ( $_POST['feed_subject'] ) )	: '';
		$feed_subject = wpautop($feed_subject);
        $feed_post = isset($_POST['feed_post']) 			? sanitize_textarea_field( wp_unslash ( $_POST['feed_post'] ) )		: '';
		$feed_post = wpautop($feed_post);
        $target_id = isset($_POST['target_id']) 			? sanitize_text_field( wp_unslash ( $_POST['target_id'] ) )			: '';
		$pid = isset($_POST['pid']) 						? sanitize_text_field( wp_unslash ( $_POST['pid'] ) )				: '';
		$post_id = isset($_POST['post_id']) 				? sanitize_text_field( wp_unslash ( $_POST['post_id'] ) ) 			: '';
		$existingImages = isset($_POST['existingImages']) 	? sanitize_text_field( wp_unslash ( $_POST['existingImages'] ) ) 	: '';
		$pastedImages = isset($_POST['pastedImages']) 		? sanitize_text_field( wp_unslash ( $_POST['pastedImages'] ) ) 		: '';
		$page_id = isset($_POST['page_id']) 				? sanitize_text_field( wp_unslash ( $_POST['page_id'] ) ) 			: '';
		
		$text = '';
		
		if ($mode != 'Edit') {
			
			// Add

			$tracked_parent_pid = $pid;
			$immediate_parent_author_id = $target_id;
			
			$type = 'wpsn-feed';
			if ($mode == 'Reply') {
				// If reply, check post parent type, and change $type to Match
				$type = get_post_type($pid);
			}
			
			$type = apply_filters('wpsn_insert_feed_post_type_filter', $type, $mode, $page_id);
						
			$id = wp_insert_post(array(
				'post_author'   => $current_user->ID,
				'post_title'    => wp_strip_all_tags($feed_subject), 
				'post_type'     => $type, 
				'post_content'  => $feed_post,
				'post_parent'   => $pid,
				'post_status'   => 'publish'
			));
			
			// Update last active
			wpsn_update_last_active_now($current_user->ID);
						
			if ($mode == 'Post' || $mode == 'Reply') {
				
				/* POST OR REPLY */
				
				// Sort out parent posts
				if ($pid > 0) {
					
					$parent_post = get_post($id);
					$continue = true;
					$depth = 0;
					
					do {
						
						if ($parent_post->post_parent > 0) {
							// Has a parent post, so get it
							$higher_parent_pid = $parent_post->post_parent;
							// Store the parent post
							$parent_post = get_post($higher_parent_pid);
							// Get the parent post ID
							$tracked_parent_pid = $parent_post->post_parent;
							if ($tracked_parent_pid == 0) {
								$tracked_parent_pid = $parent_post->ID;
							}
							// Get the post author
							$target_id = $parent_post->post_author;
							
							if ($depth == 0) {
								// Get immedate parent post owner
								$immediate_parent_author_id = $parent_post->post_author;
							}
							
							// Update the parent post modified time
							$parent_post->post_modified = current_time('timestamp');
							wp_update_post($parent_post);
							
							$depth++;

						} else {
							$continue = false;
						}
						
					} while ($continue == true);
					
				}
				
				$success = add_post_meta( $id, 'wpsn_target_id', $target_id, true );
				$success = add_post_meta( $id, 'wpsn_creation_date', current_time('timestamp'), true );

				/* ANYTHING ELSE? */
				do_action('wpsn_insert_feed_post_action', $id, $pid, $mode, get_post_type($id));
				
			} else {
				
				/* ANYTHING ELSE? */
				do_action('wpsn_insert_feed_post_action', $id, $pid, $mode, get_post_type($id));
				
			}


		} else {
			
			// Edit

			$my_post = array(
				'ID'           => $post_id,
				'post_title'   => $feed_post,
				'post_content' => $feed_post,
			);

			wp_update_post( $my_post );
			$id = $post_id;
			
			update_post_meta($id, 'wpsn_creation_date', current_time('timestamp'));
			
			$parent_post = get_post($id);
			$continue = true;
			$depth = 0;
			
			do {
				
				if ($parent_post->post_parent > 0) {
					// Has a parent post, so get it
					$higher_parent_pid = $parent_post->post_parent;
					// Store the parent post
					$parent_post = get_post($higher_parent_pid);
					// Get the parent post ID
					$tracked_parent_pid = $parent_post->post_parent;
					if ($tracked_parent_pid == 0) {
						$tracked_parent_pid = $parent_post->ID;
					}
					// Get the post author
					$target_id = $parent_post->post_author;
					
					if ($depth == 0) {
						// Get immedate parent post owner
						$immediate_parent_author_id = $parent_post->post_author;
					}
					
					// Update the parent post modified time
					$parent_post->post_modified = current_time('timestamp');
					wp_update_post($parent_post);
					
					$depth++;

				} else {
					$continue = false;
				}
				
			} while ($continue == true);

		}

		// First deal with existing images (may have been removed)
				
		$images = array();
					
		// Split the string into an array using comma as the delimiter
		if ($existingImages != '') {
			$image_array = explode(",", $existingImages);
			// Loop through the array
			foreach ($image_array as $image_url) {
				$images[] = $image_url;
			}
		}
		
		// Check if files have been pasted		
		if ($pastedImages != '') {
			
			$pastedImages_array = explode(",", $pastedImages);
			// Loop through the array		
			foreach ($pastedImages_array as $dataURL) {
			
				$dataURL = str_replace("*****", ",", $dataURL);
				
				$year = gmdate('Y');  // Full numeric representation of a year, 4 digits
				$month = gmdate('m'); // Numeric representation of a month, with leading zeros
				
				// Create folder structure if not yet there
				$upload_dir = wp_upload_dir();
				$directory_path = trailingslashit( $upload_dir['basedir'] ) . $current_user->ID;
				if ( ! $wp_filesystem->is_dir( $directory_path ) ) {
					$wp_filesystem->mkdir( $directory_path );
					//error_log('Created folder '.$directory_path);
				}
				
				$next_level = $directory_path . '/' . $year;
				if ( ! $wp_filesystem->is_dir( $next_level ) ) {
					$wp_filesystem->mkdir( $next_level );
					//error_log('Created folder '.$next_level);
				}
				$next_level = $next_level . '/' . $month;
				if ( ! $wp_filesystem->is_dir( $next_level ) ) {
					$wp_filesystem->mkdir( $next_level );
					//error_log('Created folder '.$next_level);
				}
				
				// Extract the base64-encoded image data from the data URL
				$base64Image = explode(',', $dataURL)[1];

				// Decode the base64-encoded image data
				$imageData = base64_decode($base64Image);

				// Define the directory path where you want to save the image	
				$dir_path = trailingslashit( $upload_dir['basedir'] ) . $current_user->ID . '/' . $year . '/' . $month . '/';

				// Generate a unique filename for the image
				$uniqid = uniqid();
				$filename = 'image_' . $uniqid . '.jpg';
				$filename_big = 'image_' . $uniqid . '-big.jpg';
				$filename_thumb = 'image_' . $uniqid . '-thumb.jpg';

				// Define the file path where you want to save the images
				$file_path = trailingslashit($dir_path) . $filename;
				$file_path_big = trailingslashit($dir_path) . $filename_big;
				$file_path_thumb = trailingslashit($dir_path) . $filename_thumb;

				// Save the decoded image data to a file using $wp_filesystem
				if ( ! $wp_filesystem->put_contents( $file_path, $imageData, FS_CHMOD_FILE ) ) {
					error_log('Failed to save the image.');
				}
				if ( ! $wp_filesystem->put_contents( $file_path_big, $imageData, FS_CHMOD_FILE ) ) {
					error_log('Failed to save the image.');
				}
				if ( ! $wp_filesystem->put_contents( $file_path_thumb, $imageData, FS_CHMOD_FILE ) ) {
					error_log('Failed to save the image.');
				}
				
				// Log to return for displaying via JS
				$image_url = trailingslashit($upload_dir['baseurl']) . $current_user->ID . '/' . $year . '/' . $month . '/' . $filename;
				// Add to array to store
				$images[] = $image_url;
				
			}
			
		} else {
			//error_log('No pasted images.');
		}
		
		// Check if files have been uploaded.
		if ( ! empty( $_FILES['data'] ) && is_array( $_FILES['data']['name'] ) ) {
			// Loop through each uploaded file.
			foreach ( $_FILES['data']['name'] as $index => $filename ) {
				// Prepare file data for upload.
				$file = array(
					'name'     => sanitize_file_name($_FILES['data']['name'][$index]),
					'type'     => sanitize_mime_type($_FILES['data']['type'][$index]),
					'tmp_name' => sanitize_text_field($_FILES['data']['tmp_name'][$index]),
					'error'    => intval($_FILES['data']['error'][$index]),
					'size'     => intval($_FILES['data']['size'][$index])
				);
				error_log(esc_html(sanitize_text_field($_FILES['data']['name'][$index])));
				
				// Get the file extension.
				$file_extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

				// Check if the file extension is allowed.
				if ( ! in_array( $file_extension, array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ), true ) ) {
					error_log('Error: File type not allowed for ' . $filename);
					continue; // Skip this file and continue with the next one.
				}
				
				// Define allowed file types.
				$allowed_types = array(
					'jpg' => 'image/jpeg',
					'jpeg' => 'image/jpeg',
					'png' => 'image/png',
					'gif' => 'image/gif',
					'webp' => 'image/webp'
				);
				
				// Define the upload directory and other overrides.
				$upload_overrides = array(
					'test_form' => false, // Prevents running the wp_handle_upload() "test_upload" action hook.
					'mimes'     => $allowed_types // Specify allowed MIME types.
				);

				// Process the file upload.
				$upload_result = wp_handle_upload( $file, $upload_overrides );

				// Check for upload errors.
				if ( isset( $upload_result['error'] ) ) {
					// Handle upload error.
					error_log('Error uploading file ' . $filename . ': ' . $upload_result['error']);
				} else {
					// File successfully uploaded.
					$uploaded_file_path = $upload_result['file'];
				}
				
				$year = gmdate('Y');  // Full numeric representation of a year, 4 digits
				$month = gmdate('m'); // Numeric representation of a month, with leading zeros

				// Create folder structure if not yet there
				$upload_dir = wp_upload_dir();
				$directory_path = trailingslashit( $upload_dir['basedir'] ) . $current_user->ID;
				if ( ! $wp_filesystem->is_dir( $directory_path ) ) {
					$wp_filesystem->mkdir( $directory_path );
					//error_log('Created folder '.$directory_path);
				}
				
				$next_level = $directory_path . '/' . $year;
				if ( ! $wp_filesystem->is_dir( $next_level ) ) {
					$wp_filesystem->mkdir( $next_level );
					//error_log('Created folder '.$next_level);
				}
				$next_level = $next_level . '/' . $month;
				if ( ! $wp_filesystem->is_dir( $next_level ) ) {
					$wp_filesystem->mkdir( $next_level );
					//error_log('Created folder '.$next_level);
				}
				
				// Now move the file to it's correct place
				$source_file = $uploaded_file_path;
				$time_stamp = current_time('timestamp');
				$filename = $time_stamp . '_' . $filename;
				$destination_file = trailingslashit( $upload_dir['basedir'] ) . $current_user->ID . '/' . $year . '/' . $month . '/' . $filename;
				$destination_folder = trailingslashit( $upload_dir['basedir'] ) . $current_user->ID . '/' . $year . '/' . $month . '/';
				$destination_url = trailingslashit( $upload_dir['baseurl'] ) . $current_user->ID . '/' . $year . '/' . $month . '/' . $filename;

				// Check if the source file exists
				if ( file_exists( $source_file ) ) {
					// Attempt to move the file to the destination
					if ( $wp_filesystem->move( $source_file, $destination_file ) ) {
						// File moved successfully
						
						// Re-adjusted version for thumbnail
						$width = 200;
						$comp = 75;					
						$extension = pathinfo($filename, PATHINFO_EXTENSION);
						$filename_without_extension = str_replace('.' . $extension, '', $filename) . '-thumb';
						$new_destination_file = $destination_folder . $filename_without_extension . '.' . $extension;
						wpsn_resizeAndCompressImage($destination_file, $new_destination_file, $width, $comp);
														
						// Re-adjusted version so not too big
						$width = 1000;
						$comp = 75;					
						$extension = pathinfo($filename, PATHINFO_EXTENSION);
						$filename_without_extension = str_replace('.' . $extension, '', $filename) . '-big';
						$new_destination_file = $destination_folder . $filename_without_extension . '.' . $extension;
						wpsn_resizeAndCompressImage($destination_file, $new_destination_file, $width, $comp);

						// Log to return for displaying via JS
						$image_url = trailingslashit($upload_dir['baseurl']) . $current_user->ID . '/' . $year . '/' . $month . '/' . $filename;
						// Add to array to store
						$images[] = $image_url;
						
					} else {
						// Handle the error
						error_log( 'Error moving file '.$source_file.' to '.$destination_file );
					}
				} else {
					// Source file does not exist
					echo 'Source file does not exist.';
				}
			
			}
			
			// Clear each element in $_FILES['data']
			foreach ($_FILES['data'] as $key => $value) {
				unset($_FILES['data'][$key]);
			}
			// Clear the entire $_FILES array
			$_FILES = array();
			
		} else {
			//error_log('No files to upload');
		}
		
		// Sort out parent posts (after creating content so returned content remains in same order)
		if ($mode == 'Post' || $mode == 'Reply') {
			if ($pid > 0) {
				
				$parent_post = get_post($id);
				$continue = true;
				$depth = 0;
				
				do {
					
					if ($parent_post->post_parent > 0) {
						// Has a parent post, so get it
						$higher_parent_pid = $parent_post->post_parent;
						// Store the parent post
						$parent_post = get_post($higher_parent_pid);
						// Get the parent post ID
						$tracked_parent_pid = $parent_post->post_parent;
						if ($tracked_parent_pid == 0) {
							$tracked_parent_pid = $parent_post->ID;
						}
						// Get the post author
						$target_id = $parent_post->post_author;
						
						if ($depth == 0) {
							// Get immedate parent post owner
							$immediate_parent_author_id = $parent_post->post_author;
						}
						
						// Update the parent post modified time
						$parent_post->post_modified = current_time('timestamp');
						wp_update_post($parent_post);
						
						$depth++;

					} else {
						$continue = false;
					}
					
				} while ($continue == true);
				
			}
		}

		// Update associated images
		if (empty($images)) {
			
			// No images, just update post
			$ret = array(
				'status' => 'ok',
				'id' => $id,
				'post' => deslash($feed_post),
				'text' => $text.'Processed post without attachments to process',
				'images' => $images
			);
			
		} else {
			
			error_log(print_r($images, true));
			
			// Associate with this post
			$meta_value = serialize($images);
			update_post_meta($id, 'wpsn_post_files', $meta_value);
			
			$ret = array(
				'status' => 'ok',
				'id' => $id,
				'post' => deslash($feed_post),
				'text' => $text.'Processed post with attachment(s)',
				'images' => $images
			);
			
		}
		
	} else {
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (insert_feed_post)',
		);
	}		

	// Alerts
	if ($ret['status'] == 'ok') {
		if ($mode == 'Post') {
			if ($target_id != $current_user->ID) {
				wpsn_add_alert($current_user->ID, $target_id, $id, 'post', deslash($feed_post));
			}
		}
		if ($mode == 'Reply') {
			// Alert to the immediate parent
			if ($immediate_parent_author_id != $current_user->ID) {
				wpsn_add_alert($current_user->ID, $immediate_parent_author_id, $tracked_parent_pid, 'reply', deslash($feed_post));
			}
		}
	}

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_insert_feed_post', 'wpsn_insert_feed_post');

function wpsn_delete_post() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		global $current_user;

		$id_to_delete = isset($_POST['id_to_delete']) ? sanitize_text_field($_POST['id_to_delete']) : '';
		
		// Get post author
		$author_id = get_post_field('post_author', $id_to_delete);
		
		$can_delete = false;
		if ($author_id == $current_user->ID) { $can_delete = true; }
		$can_delete = apply_filters('wpsn_delete_post_filter', $can_delete, $id_to_delete);
		
		if ($can_delete) {
			
			$post_data = array(
				'ID'          => $id_to_delete,
				'post_status' => 'draft' // Set the post status to 'draft' to unpublish the post
			);
			wp_update_post($post_data);
			
			$ret = array(
				'status' => 'ok',
				'text' => 'Post '.$id_to_delete.' deleted',
			);
			
		} else {
			$ret = array(
				'status' => 'fail',
				'text' => 'Not post author',
			);
		}
		
	} else {
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (wpsn_delete_post)',
		);
	}			

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_delete_post', 'wpsn_delete_post');

function wpsn_get_post_images() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		global $current_user;

		$post_id = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : '';
		
		// Get post images
		$attachments = get_post_meta($post_id, 'wpsn_post_files', true);
		$images = unserialize($attachments);
					
		if (!empty($images)) {
			
			$images_array = array();

			foreach ($images as $file_path) {
				$images_array[] = $file_path;
			}
				
			$ret = array(
				'status' => 'ok',
				'text' => 'Images returned',
				'images' => $images_array
			);
						
		} else {
			$ret = array(
				'status' => 'none',
				'text' => 'No images for this post',
			);
		}
		
	} else {
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (delete_post)',
		);
	}			

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_get_post_images', 'wpsn_get_post_images');

function wpsn_get_post_info() {

    $ret = '';

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		global $current_user;

		$post_id = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : '';
		$post = get_post($post_id);
		$post_parent = $post->post_parent;
		
		$the_post = deslash($post->post_content);
		
		$the_post = str_replace('<p>', "", $the_post);
		$the_post = str_replace('</p>', "", $the_post);
		$the_post = str_replace('<br />', "", $the_post);
				
		// Get post images
		$attachments = get_post_meta($post_id, 'wpsn_post_files', true);
		$images = unserialize($attachments);

		$images_array = array();					
		if (!empty($images)) {
			
			foreach ($images as $file_path) {
				$images_array[] = $file_path;				
			}
				
		}
			
		$ret = array(
			'status' => 'ok',
			'text' => 'Success',
			'post_parent' => $post_parent,
			'post' => $the_post,
			'images' => $images_array
		);
		
	} else {
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (delete_post)',
		);
	}			

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_get_post_info', 'wpsn_get_post_info');

function wpsn_resizeAndCompressImage($source_path, $destination_path, $max_width = 1000, $quality = 75) {
    // Get the original image dimensions and type
    list($original_width, $original_height, $image_type) = getimagesize($source_path);

    // Determine the correct image creation function based on the image type
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $image = @imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $image = @imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $image = @imagecreatefromgif($source_path);
            break;
        // Add support for more image types as needed
        default:
            // Unsupported image type
            return false;
    }

    // Calculate the new dimensions
    $ratio = $original_width / $max_width;
    $new_width = $max_width;
    $new_height = $original_height / $ratio;

    // Create a new true color image with the new dimensions
    $new_image = imagecreatetruecolor($new_width, $new_height);

    // Resize the original image to fit the new dimensions
    imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

    // Save the resized and compressed image to the destination path
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            imagejpeg($new_image, $destination_path, $quality);
            break;
        case IMAGETYPE_PNG:
            imagepng($new_image, $destination_path);
            break;
        case IMAGETYPE_GIF:
            imagegif($new_image, $destination_path);
            break;
        // Add support for more image types as needed
        default:
            // Unsupported image type
            return false;
    }

    // Free up memory
    imagedestroy($image);
    imagedestroy($new_image);

    return true;
}

function wpsn_get_posts() {

    $ret = '';
	$count = 0;

	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		global $wpdb, $current_user;

		$pid = isset($_POST['pid']) ? sanitize_text_field($_POST['pid']) : '';
		$uid = isset($_POST['uid']) ? sanitize_text_field($_POST['uid']) : '';
		$indent = isset($_POST['indent']) ? sanitize_text_field($_POST['indent']) : '';
		$offset = isset($_POST['offset']) ? sanitize_text_field($_POST['offset']) : '';
		$limit = isset($_POST['limit']) ? sanitize_text_field($_POST['limit']) : '';
		$show_comments_limit = isset($_POST['show_comments_limit']) ? sanitize_text_field($_POST['show_comments_limit']) : '';
		$mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : '';
		$from = isset($_POST['from']) ? sanitize_text_field($_POST['from']) : '';
		$post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
		
		$ret = wpsn_show_posts($pid, $uid, $indent, $limit, $offset, $count, $show_comments_limit, $mode, $from, $post_type);
		$html = $ret["html"];
		$count = $ret['count'];

		$ret = array(
			'status' => 'ok',
			'html' => $html,
			'count' => $count
		);
						
	} else {
		$ret = array(
			'status' => 'fail',
			'text' => 'Invalid security token (delete_post)',
			'count' => 0
		);
	}			

	echo wp_json_encode( $ret );
	exit;
}
add_action('wp_ajax_wpsn_get_posts', 'wpsn_get_posts');

function wpsn_show_posts($pid, $uid, $indent, $limit, $offset, $count, $show_comments_limit, $mode, $from, $post_type) {

    global $wpdb, $current_user;
	
	if ( check_ajax_referer( 'wpsn-security-nonce', 'security' ) ) {	

		$html = '';
		$posts_count = 0;
		$post_types = array($post_type);
		
		$get = isset($_POST['get']) ? array_map('sanitize_text_field', wp_unslash($_POST['get'])) : '-----';
		$current_page_id = isset($_POST['current_page_id']) ? sanitize_text_field($_POST['current_page_id']) : '';
		
		$results = new stdClass();

		/* ********** Home Page ************** */

		if ($mode == 'home') {
			
			if ($indent == 0) {
				
				/* Top level */
							
				// First get the limited posts by this user

				$args = array(
					'post_type'      => $post_types, 	 // Specify your post type
					'post_parent'    => $pid,
					'post_status'    => 'publish',   	 // Only get published posts
					'orderby'        => 'post_modified', // Order by last modified date
					'order'          => 'DESC',          // In descending order
					'posts_per_page' => $limit,      		 // Number of posts per page
					'offset'         => $offset,         // Offset for pagination
					'author' 		 => $uid
				);

				$query = new WP_Query($args);

				$i = 0;
					
				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();
						$copy_post = new stdClass();
						$copy_post->ID = get_the_ID();
						$copy_post->post_title = get_the_title();
						$copy_post->post_content = get_the_content();
						$copy_post->post_author = get_the_author_meta('ID');
						$copy_post->post_parent = wp_get_post_parent_id(get_the_ID());
						$copy_post->post_modified = strtotime(get_post_field('post_modified_gmt', get_the_ID()));
						$copy_post->wpsn_target_id = get_post_meta(get_the_ID(), 'wpsn_target_id', true);
						$copy_post->wpsn_creation_date = get_post_meta(get_the_ID(), 'wpsn_creation_date', true);
						$copy_post->post_type = get_post_type(get_the_ID());
						// Add more post details as needed

						$results->$i = $copy_post;
						$i++;
						$posts_count++;
					}
					wp_reset_postdata(); // Reset post data after the query loop
				}
				
				// Now get all recent postmeta where wpsn_target_id = $uid and the associated post's post_author is not $uid
				
				$args = [
					'post_type' 	 => $post_types, // Adjust the post type as needed
					'orderby'        => 'post_modified', // Order by last modified date
					'order'          => 'DESC',          // In descending order
					'posts_per_page' => $limit,
					'offset'         => $offset,         // Offset for pagination
					'meta_query' => [
						[
							'key' => 'wpsn_target_id',
							'value' => $uid,
							'compare' => '='
						]
					],
					'author__not_in' => [$uid],
					'post_parent' => $pid // Ensure the post parent is 0 (or passed post parent)
				];

				$query = new WP_Query($args);

				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();
						$copy_post = new stdClass();
						$copy_post->ID = get_the_ID();
						$copy_post->post_title = get_the_title();
						$copy_post->post_content = get_the_content();
						$copy_post->post_author = get_the_author_meta('ID');
						$copy_post->post_parent = wp_get_post_parent_id(get_the_ID());
						$copy_post->post_modified = strtotime(get_post_field('post_modified_gmt', get_the_ID()));
						$copy_post->wpsn_target_id = get_post_meta(get_the_ID(), 'wpsn_target_id', true);
						$copy_post->wpsn_creation_date = get_post_meta(get_the_ID(), 'wpsn_creation_date', true);
						$copy_post->post_type = get_post_type(get_the_ID());
						// Add more post details as needed

						$results->$i = $copy_post;
						$i++;
						$posts_count++;
					}
					wp_reset_postdata(); // Reset post data after the query loop
				}

				/* ***** Allow more posts to be added ***** */
				$results = apply_filters('wpsn_add_home_feed_top_filter', $results, $uid, $offset, $get, $mode, $limit);
				$i = count(get_object_vars($results));
				$posts_count = count(get_object_vars($results));
				
				// Convert the object to an array for sorting
				$results_array = [];
				foreach ($results as $key => $value) {
					$results_array[] = $value;
				}

				// Define a custom sorting function
				usort($results_array, function($a, $b) {
					return $b->post_modified - $a->post_modified;
				});

				// Convert the sorted array back to an object
				$sorted_results = new stdClass();
				foreach ($results_array as $index => $post) {
					$sorted_results->$index = $post;
				}
				
				$results = $sorted_results;

					
			} else {
				
				/* Lower levels */

				$i = 0;
				
				$args = array(
					'post_type'      => $post_types,
					'post_status'    => 'publish',
					'post_parent'    => $pid,
					'orderby'        => 'post_modified',
					'order'          => 'DESC',
					'posts_per_page' => 99,
					'offset'         => $offset,
				);

				// Create a new WP_Query instance
				$query = new WP_Query($args);
					
				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();
						$copy_post = new stdClass();
						$copy_post->ID = get_the_ID();
						$copy_post->post_title = get_the_title();
						$copy_post->post_content = get_the_content();
						$copy_post->post_author = get_the_author_meta('ID');
						$copy_post->post_parent = wp_get_post_parent_id(get_the_ID());
						$copy_post->post_modified = strtotime(get_post_field('post_modified_gmt', get_the_ID()));
						$copy_post->wpsn_target_id = get_post_meta(get_the_ID(), 'wpsn_target_id', true);
						$copy_post->wpsn_creation_date = get_post_meta(get_the_ID(), 'wpsn_creation_date', true);
						$copy_post->post_type = get_post_type(get_the_ID());
						// Add more post details as needed

						$results->$i = $copy_post;
						$i++;
						$posts_count++;
					}
					wp_reset_postdata(); // Reset post data after the query loop
				}
				
				/* ***** Allow more posts to be added ***** */
				$results = apply_filters('wpsn_add_home_feed_lower_filter', $results, $pid, $offset, $get, $limit);
				$i = count(get_object_vars($results));
				$posts_count = count(get_object_vars($results));
				
			}
			
		}
		
		/* ********** Activity Page ********** */
			
		if ($mode == 'activity') {
			
			// Get all friends
			$inClause = array();
			$wpsn_friends = get_user_meta($uid, 'wpsn_friends', true);
			if ($wpsn_friends) {
				$inClause[] = $uid;
				foreach ($wpsn_friends as $friend) {
					$inClause[] = $friend['ID'];
				}
			} else {
				$inClause[] = $uid;
			}

			$results = new stdClass();
			$i = 0;
			
			if ($indent == 0) {
				
				/* Top level */
				
				$args = array(
					'post_type'      => $post_types,
					'post_status'    => 'publish',
					'post_parent'    => $pid,
					'orderby'        => 'post_modified',
					'order'          => 'DESC',
					'posts_per_page' => 99,
					'offset'         => $offset,
					'author__in'     => $inClause, // Array of user IDs
				);

				// Create a new WP_Query instance
				$query = new WP_Query($args);
					
				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();
						$copy_post = new stdClass();
						$copy_post->ID = get_the_ID();
						$copy_post->post_title = get_the_title();
						$copy_post->post_content = get_the_content();
						$copy_post->post_author = get_the_author_meta('ID');
						$copy_post->post_parent = wp_get_post_parent_id(get_the_ID());
						$copy_post->post_modified = strtotime(get_post_field('post_modified_gmt', get_the_ID()));
						$copy_post->wpsn_target_id = get_post_meta(get_the_ID(), 'wpsn_target_id', true);
						$copy_post->wpsn_creation_date = get_post_meta(get_the_ID(), 'wpsn_creation_date', true);
						$copy_post->post_type = get_post_type(get_the_ID());
						// Add more post details as needed

						$results->$i = $copy_post;
						$i++;
						$posts_count++;
					}
					wp_reset_postdata(); // Reset post data after the query loop
				}
				
				/* ***** Allow more posts to be added ***** */
				$results = apply_filters('wpsn_add_home_feed_top_filter', $results, $uid, $offset, $get, $mode, $limit);
				$i = count(get_object_vars($results));
				$posts_count = count(get_object_vars($results));
				
				// Convert the object to an array for sorting
				$results_array = [];
				foreach ($results as $key => $value) {
					$results_array[] = $value;
				}

				// Define a custom sorting function
				usort($results_array, function($a, $b) {
					return $b->post_modified - $a->post_modified;
				});

				// Convert the sorted array back to an object
				$sorted_results = new stdClass();
				foreach ($results_array as $index => $post) {
					$sorted_results->$index = $post;
				}

				$results = $sorted_results;

			} else {
				
				/* Lower levels */
						
				$results = new stdClass();

				$i = 0;
				
				$args = array(
					'post_type'      => $post_types,
					'post_status'    => 'publish',
					'post_parent'    => $pid,
					'orderby'        => 'post_modified',
					'order'          => 'DESC',
					'posts_per_page' => 99,
					'offset'         => $offset,
				);

				// Create a new WP_Query instance
				$query = new WP_Query($args);
					
				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();
						$copy_post = new stdClass();
						$copy_post->ID = get_the_ID();
						$copy_post->post_title = get_the_title();
						$copy_post->post_content = get_the_content();
						$copy_post->post_author = get_the_author_meta('ID');
						$copy_post->post_parent = wp_get_post_parent_id(get_the_ID());
						$copy_post->post_modified = strtotime(get_post_field('post_modified_gmt', get_the_ID()));
						$copy_post->wpsn_target_id = get_post_meta(get_the_ID(), 'wpsn_target_id', true);
						$copy_post->wpsn_creation_date = get_post_meta(get_the_ID(), 'wpsn_creation_date', true);
						$copy_post->post_type = get_post_type(get_the_ID());
						// Add more post details as needed

						$results->$i = $copy_post;
						$i++;
						$posts_count++;
					}
					wp_reset_postdata(); // Reset post data after the query loop
				}
				
				/* ***** Allow more posts to be added ***** */
				$results = apply_filters('wpsn_add_home_feed_lower_filter', $results, $pid, $offset, $get, $limit);
				$i = count(get_object_vars($results));
				$posts_count = count(get_object_vars($results));
				
			}
			
		}
		
		/* ********** Other Pages ************ */
		
		$results = apply_filters('wpsn_show_posts_results_filter', $results, $pid, $get, $indent, $post_types, $offset, $uid, $limit);
		$i = count(get_object_vars($results));
		$posts_count = count(get_object_vars($results));

		
		/* *********************************** */
		
		$nonce = get_transient( 'wpsn_nonce' );
		
		if ($indent == 0) {
			$count += $posts_count;
		}
		
		$wpsn_reply_limit = get_option( 'wpsn_reply_limit' ) !== false ? get_option( 'wpsn_reply_limit' ) : 2;

		$local_count = 0;
		
		if ($posts_count > 0) {
			
			foreach ($results as $post) {
				
				if (!empty($post)) {
								
					$local_count++;
					
					$the_id = $post->ID;
			
					$the_post = deslash($post->post_content);
					// Exclude <p> and <br> from conversion
					$the_post = str_replace(["<p>", "</p>", "<br />"], ["{{{p}}}", "{{{/p}}}", "{{{br}}}" ], $the_post);
					// Convert remaining < and > to &lt; and &gt;
					$the_post = htmlspecialchars($the_post);
					// Bring back <p> and <br> to original form
					$the_post = str_replace(["{{{p}}}", "{{{/p}}}", "{{{br}}}"], ["<p>", "</p>", "<br />"], $the_post);
					// Smilies
					$the_post = convert_smilies($the_post);
						
					$parent_id = $post->post_parent;
					$the_author_id = $post->post_author;
					$the_author_name = wpsn_get_display_name($the_author_id);
					$avatar_url = wpsn_get_avatar($the_author_id);
					$target_id = $post->wpsn_target_id;
					$post_modified = $post->post_modified;
					$wpsn_creation_date = $post->wpsn_creation_date;
					$post_type = $post->post_type;

					// Translators: %s is the time difference
					$daysago = sprintf( __('%s ago', 'wpsn-instant-social-network'), human_time_diff($wpsn_creation_date, current_time('timestamp')) );
					
					$indent_padding = 0;
					if ($indent > 0) {
						$indent_padding = 60;
					}
									
					$indent_padding = ($indent == 0) ? 10 : $indent_padding;
					$box_class = ($indent == 0) ? ' wpsn-feed-post-top' : '';
					$indent_class = ($indent == 0) ? ' ' : ' wpsn-feed-post-line';
					if ($indent == 0) { $indent_class = ' wpsn-feed-post-top'; }
					
					$post_html = '<div id="wpsn_post_'.$the_id.'" class="wpsn-feed-post' . $box_class . $indent_class . '" style="padding-left: '.$indent_padding.'px;">';
					
						if (wpsn_are_friends($current_user->ID, $uid)) {
							
							$shown_actions = false;
							$actions_html = '<div class="wpsn-feed-post-content-actions">';
								$reply_html = '';
								if ($indent < $wpsn_reply_limit) {
									$reply_html .= '<div class="wpsn-feed-post-content-action-reply" data-wpsn-indent="'.$indent.'" data-wpsn-pid="'.$the_id.'"><i class="fa-solid fa-reply"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Reply', 'wpsn-instant-social-network').'</span></div>';									
									$reply_html = apply_filters('wpsn_activity_actions_reply_filter', $reply_html, $mode, $the_id, $indent);
								}
								$actions_html .= $reply_html;

								$actions_html = apply_filters('wpsn_activity_actions_filter', $actions_html, $mode, $the_id, $the_author_id);

								if ($the_author_id == $current_user->ID) {
									$actions_html .= '<div class="wpsn-feed-post-content-action-edit" data-wpsn-pid="'.$the_id.'"><i class="fa-solid fa-pen-to-square"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Edit', 'wpsn-instant-social-network').'</span></div>';
									$actions_html .= '<div class="wpsn-feed-post-content-action-delete" data-wpsn-pid="'.$the_id.'"><i class="fa-solid fa-trash-can"></i><span class="wpsn_button_label">&nbsp;&nbsp;'.__('Delete', 'wpsn-instant-social-network').'</span></div>';
								}					

							$actions_html .= '</div>';
							
						}

						$post_html .= '<div class="wpsn-feed-post-header">';
							
							$post_html .= '<div class="wpsn-feed-post-header-avatar">';
								if ($uid == $current_user->ID) {
									$post_html .= '<a href="'.wpsn_page('home').'?wpsn='.$nonce.'"><img class="wpsn-feed-post-header-avatar-img" src="'.$avatar_url.'" alt="'.$the_author_name.'\'s profile avatar" /></a>';
								} else {
									$post_html .= '<a href="'.wpsn_page('home').'?uid='.$the_author_id.'&wpsn='.$nonce.'"><img class="wpsn-feed-post-header-avatar-img" src="'.$avatar_url.'" alt="'.$the_author_name.'\'s profile avatar" /></a>';
								}
							$post_html .= '</div>';
							$post_html .= '<div class="wpsn-feed-post-header-info">';
								$post_html .= '<p class="wpsn-feed-post-header-author-name">';
									if ($the_author_id == $current_user->ID) {
										$post_html .= '<a href="'.wpsn_page('home').'?wpsn='.$nonce.'">'.$the_author_name.'</a>';
									} else {
										$post_html .= '<a href="'.wpsn_page('home').'?uid='.$the_author_id.'&wpsn='.$nonce.'">'.$the_author_name.'</a>';
									}
									
									// Target
									$target_html = '';
									if ($the_author_id != $target_id) {
										$target_name = get_user_meta( $target_id, 'first_name', true ) . ' ' . get_user_meta( $target_id, 'last_name', true );
										if ($indent == 0) {
											if ($target_id == $current_user->ID) {
												$target_html .= ' <i class="fa-solid fa-caret-right wpsn-posted-to"></i> <a href="'.wpsn_page('home').'?wpsn='.$nonce.'">'.$target_name.'</a>';
											} else {
												$target_html .= ' <i class="fa-solid fa-caret-right wpsn-posted-to"></i> <a href="'.wpsn_page('home').'?uid='.$target_id.'&wpsn='.$nonce.'">'.$target_name.'</a>';
											}
										}
									}

									$target_html = apply_filters('wpsn_show_posts_target_filter', $target_html, $the_id, $post_type, $current_page_id);
									$post_html .= $target_html;
									
								$post_html .= '</p>';

								$post_date = get_post_time('U', true, $post->ID); // Get the post's publish date in Unix timestamp

								// Get the current date
								$current_date = current_time('timestamp');
								// Calculate the difference in seconds
								$difference_seconds = $current_date - $post_date;
								// Convert seconds to days
								$difference_days = floor($difference_seconds / (60 * 60 * 24));
								
								if ($difference_days < 1) {
									$post_html .= '<p class="wpsn-daysago">';
										$post_html .= $daysago;
									$post_html .= '</p>';
								} else {
									$post_date = get_the_date('Y-m-d', $post->ID);
									$spoken_date = ucfirst(date_i18n('F j, Y', strtotime(gmdate('Y-m-d H:i:s', strtotime($post_date)))));
									$post_html .= '<p class="wpsn-daysago">';
										$post_html .= $spoken_date;
									$post_html .= '</p>';
								}
							$post_html .= '</div>';
						$post_html .= '</div>';

						/* POST CONTENT */

						$post_content_html = '<div class="wpsn-feed-post-content">';
						
							$the_post = wpsn_parse_post($the_post);
							
							/* POST CONTENT IMAGES */
							
							$attachments = get_post_meta($the_id, 'wpsn_post_files', true);
							$images = unserialize($attachments);
							
							$post_content_images = '';
							
							if (!empty($images)) {
								$post_content_images .= '<div class="wpsn-images-container">';
								$images_count = 1;
								foreach ($images as $file_path) {
									
									if ($images_count <= 5) {
									
										if ($images_count == 1) {
											$post_content_images .= '<div class="wpsn-images-container-image wpsn-images-container-image-first">';
												$post_content_images .= '<img class="wpsn-show-all-images" data-wpsn-single-image="1" data-wpsn-post-id="'.$the_id.'" src="'.$file_path.'" alt="'.$file_path.'" />';
											$post_content_images .= '</div>';
										}

										if ($images_count >= 2 && $images_count <=5) {
											$post_content_images .= '<div class="wpsn-images-container-image">';
												$post_content_images .= '<img class="wpsn-show-all-images" data-wpsn-single-image="1" data-wpsn-post-id="'.$the_id.'" src="'.$file_path.'" alt="'.$file_path.'" />';
												if ($images_count == 5 && count($images) > 5) {
													$post_content_images .= '<div class="wpsn-images-container-image-overlay" data-wpsn-single-image="0" data-wpsn-post-id="'.$the_id.'">+'.(count($images)-5).'</div>';
												}
											$post_content_images .= '</div>';
										}
										
									}
									
									$images_count++;
								}
								$post_content_images .= '</div>';
							}
							
							$post_content_html .= $the_post;
							
							$post_content_html .= $post_content_images;
									
							$post_content_html = apply_filters('wpsn_activity_filter', $post_content_html, $mode, $the_id);
							
							$actions_html = apply_filters('wpsn_activity_actions_end_filter', $actions_html, $current_page_id, $get);
							
							$post_content_html .= $actions_html;
							
						$post_content_html .= '</div>';

						$post_html .= $post_content_html;
												
						$post_html .= '<div class="wpsn-insert-here-'.$the_id.'"></div>';

						if ($indent < $wpsn_reply_limit) {
													
							/* RECURSIVELY ADD */
							if ($from == 'wpsn-load-more') {
								$offset = 0;
							}
							$next = wpsn_show_posts($the_id, $uid, $indent+1, $limit, $offset, $count, $show_comments_limit, $mode, $from, $post_type);

							if ($next['html'] != '') {
								$returnedHtml = $next['html'];
								if ($local_count > 1) {
									if ($local_count > $show_comments_limit) {
										$returnedHtml = str_replace('class="wpsn-feed-post"', 'class="wpsn-feed-post wpsn-feed-post-hidden"', $returnedHtml);
									}
								}
								if ($local_count > $show_comments_limit) {
									$post_html .= '<div class="wpsn-feed-post-toggle wpsn-feed-post-toggle-show"><i class="fa-solid fa-eye"></i>&nbsp;&nbsp;Show comments</div>';
								} else {
									$post_html .= '<div class="wpsn-feed-post-toggle wpsn-feed-post-toggle-hide"><i class="fa-solid fa-eye-slash"></i>&nbsp;&nbsp;Hide comments</div>';
								}
								$post_html .= $returnedHtml;
							}

						}

						if ($local_count == $limit && $posts_count > $local_count && $pid > 0) {
							
							$post_html .= '<div class="wpsn-load-more-replies" data-load-more-indent="'.$indent.'" data-load-more-offset="' . $offset+$limit . '" data-load-more-mode="' . $mode . '" data-load-more-pid="'. $pid .'" data-load-more-insert-class="#wpsn_post_'. $pid .'">';
								$post_html .= '<i class="fa-solid fa-circle-down"></i>&nbsp;&nbsp;Load more...';
							$post_html .= '</div>';
						}
					
					$post_html .= '</div>';
					
					$post_html = apply_filters('wpsn_activity_post_filter', $post_html, $the_id, $current_user->ID, $the_author_id, $target_id);				
					$html .= $post_html;
								
				}

			}
			
		}

		$ret = array(
			"html" => $html,
			"count" => $count
		);
		
	} else {
		
		$ret = array(
			"html" => 'Security issue',
			"count" => 0
		);
		
	}
	
    return $ret;	

}

function wpsn_getYouTubeVideoId($url) {
    $pattern =
        '%^# Match any youtube URL
        (?:https?://)?  # Optional scheme. Either http or https
        (?:www\.)?      # Optional www subdomain
        (?:             # Group host alternatives
          youtu\.be/    # Either youtu.be,
        | youtube\.com  # or youtube.com
          (?:           # Group path alternatives
            /embed/     # Either /embed/
          | /v/         # or /v/
          | /watch\?v=  # or /watch?v=
          )             # End path alternatives.
        )               # End host alternatives.
        ([\w-]{10,12})  # Group 1: Video ID
        %x';

    $result = preg_match($pattern, $url, $matches);
    if ($result) {
        return $matches[1];
    }
    return null;
}

function wpsn_convertMarkdown($text) {
	
	$wpsn_markdown = get_option( 'wpsn_markdown' ) ? get_option( 'wpsn_markdown' ) : false;
								
	if ($wpsn_markdown) {
			
		// Bold and Italic (**, *)
		$text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
		$text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);

		// List Items (*, -, +)
		$text = preg_replace('/^(\*|\+|\-) (.+)$/m', '<li>$2</li>', $text);

		// Replace backtick-enclosed text with a <pre> tag
		$text = preg_replace('/`([^`]+)`/', '<pre class="wpsn_code">$1</pre>', $text);

		// Strip out <br> tags only inside the <pre> tags
		$text = preg_replace_callback(
			'/<pre class="wpsn_code">(.*?)<\/pre>/s',
			function($matches) {
				return '<pre class="wpsn_code">' . preg_replace('/<br\s*\/?>/i', '', $matches[1]) . '</pre>';
			},
			$text
		);
		
	}
	
	return $text;
}

function wpsn_convertUrlsToLinks($text) {
    // Regular expression to match URLs without whitespace
	$pattern = '~(?<!["\'])\b(?:https?://|www\.)\S+/?\b(?![^<>]*>|[^>]*<\/a>)|(?:(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11}))~';

    // Replace URLs with anchor tags
    $text = preg_replace_callback($pattern, function($matches) {
        $url = rtrim($matches[0], '/'); // Remove trailing slashes from the URL
        // Add http:// prefix if missing
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        
        // Check if the URL includes the current domain
        $target = (strpos($url, $_SERVER['HTTP_HOST']) === false) ? " target='_blank'" : "";
        
        return "<a href='$url'$target>$matches[0]</a>";
    }, $text);
		
    return $text;
}

function wpsn_parse_post($the_post) {
	
	// Replace any bad words
	$wpsn_banned = get_option( 'wpsn_banned' );
	if ($wpsn_banned !== false && $wpsn_banned != '') {
		$words = explode(',', $wpsn_banned);
		$wpsn_banned_sub = get_option( 'wpsn_banned_sub' ) ? get_option( 'wpsn_banned_sub' ) : '*****';
		foreach ($words as $word) {
			$the_post = preg_replace('/'.trim($word).'/i', $wpsn_banned_sub, $the_post);
		}
	}
	
	// Replace the first <p>
	$the_post = preg_replace('/<p>/', '<p class="wpsn-post-content">', $the_post, 1);
					
	// Markdown
	$the_post = wpsn_convertMarkdown($the_post);
	
	// Regular expression pattern to match YouTube URLs
	$pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';

	// Check if the string contains a YouTube URL
	$youtube_url = '';
	if (preg_match($pattern, $the_post, $matches)) {
		// Extract the YouTube URL
		$youtube_url = $matches[0];
		$videoId = wpsn_getYouTubeVideoId($youtube_url);
	}

	if ($youtube_url != '') {
		$the_post = wpsn_convertUrlsToLinks($the_post);
		
		$the_post .= '<div class="wpsn-video-container">';
			// Note: This is a 3rd party external service to display videos
			$the_post .= '<iframe width="560" height="315" src="https://www.youtube.com/embed/'.$videoId.'" frameborder="0" allowfullscreen></iframe>';
		$the_post .= '</div>';
	} else {
		// Check for other links
		$the_post = wpsn_convertUrlsToLinks($the_post);

	}
	
	return $the_post;
}

?>