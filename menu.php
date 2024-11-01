<?php
function wpsn_menu($wpsn_theme_installed) {
	
	global $wpdb;

	$show_labels = true;
	$wpsn_labels_class = '';
	
	$current_user = wp_get_current_user();
	if (function_exists('the_custom_logo')) {
		if (has_custom_logo()) {
			$site_logo = get_theme_mod('custom_logo');
			$site_logo = wp_get_attachment_image_src($site_logo , 'full');
			$site_logo = $site_logo[0];
		} else {
			$site_logo = plugins_url() . '/wpsn-instant-social-network/img/wpsn_short_white_logo.png';
		}
	} else {
		$site_logo = plugins_url() . '/wpsn-instant-social-network/img/wpsn_short_white_logo.png';
	}
	
	// Check if the is_plugin_active() function exists
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	if ( is_plugin_active( 'wpsn-instant-social-network/wpsn-instant-social-network.php' ) ) {
					
		$wpsn_top_bar_labels = get_option( 'wpsn_top_bar_labels' );
		if ($wpsn_top_bar_labels !== false && $wpsn_top_bar_labels == 0) { $show_labels = false; }
		
		$wpsn_labels_class = $show_labels ? '' : ' wpsn-label-only';
		$current_user = wp_get_current_user();
		
		if (function_exists('the_custom_logo')) {
			if (has_custom_logo()) {
				$site_logo = get_theme_mod('custom_logo');
				$site_logo = wp_get_attachment_image_src($site_logo , 'full');
				$site_logo = $site_logo[0];
			} else {
				$site_logo = plugins_url() . '/wpsn-instant-social-network/img/wpsn_short_white_logo.png';
			}
		} else {
			$site_logo = plugins_url() . '/wpsn-instant-social-network/img/wpsn_short_white_logo.png';
		}
		
		// Array to hold alerts with valid users
		$valid_friend_requests = [];

		// Array to hold alerts with valid users
		$valid_unread_alerts = [];
		
		if (is_user_logged_in()) {
		
			$friend_requests = get_user_meta($current_user->ID, 'wpsn_friend_requests_received', true);
			
			// Loop through the alerts
			if ($friend_requests) {
				foreach ($friend_requests as $request) {
					// Check if the user (post_parent) exists
					$user = get_user_by('id', $request['from']);
					
					// If the user exists, add the alert to the valid alerts array
					if ($user) {
						$valid_friend_requests[] = $request;
					}
				}
			}

			$args = array(
				'post_type'   => 'wpsn-alert',
				'post_status' => 'unread',
				'post_parent' => $current_user->ID,
				'numberposts' => -1 // Retrieve all matching posts
			);
			
			$unread_alerts = get_posts($args);

			// Loop through the alerts
			if ($unread_alerts) {
				foreach ($unread_alerts as $alert) {
					// Check if the user (post_parent) exists
					$user = get_user_by('id', $alert->post_parent);
					
					// If the user exists, add the alert to the valid alerts array
					if ($user) {
						$valid_unread_alerts[] = $alert;
					}
				}
			}
			
		}

		$wpsn_hide_menu = '';
		$wpsn_menu_loggedout = get_option( 'wpsn_menu_loggedout' );
		
		// Logged out visibility
		if ($wpsn_menu_loggedout === false) {
			update_option('wpsn_menu_loggedout', 1);
			$wpsn_menu_loggedout = 1;
		}
		if (!is_user_logged_in() && $wpsn_menu_loggedout == 0) {
			$wpsn_hide_menu = ' wpsn_hide_menu';
		}

		$wpsn_theme_home_avatar = get_option( 'wpsn_theme_home_avatar' );
		$wpsn_avatar_class = '';
		if ($wpsn_theme_home_avatar) {
			$wpsn_avatar_class = ' wpsn-top-icon-wrapper-using-avatar';
		}

		$wpsn_not_wpsn_theme = $wpsn_theme_installed ? '' : 'wpsn_not_wpsn_theme';
		
		?>
		<div class="wpsn-top-box <?php echo esc_attr($wpsn_hide_menu); ?>">
			

			<div class="wpsn-top-box-logo"><a href="<?php echo esc_html(site_url()); ?>"><img src="<?php echo esc_attr($site_logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" /></a></div>
			<div class="wpsn-top-box-icons<?php echo esc_attr($wpsn_labels_class); ?>">

			<?php if (is_user_logged_in()) { ?>

				<!-- *** Home *** -->
				<div class="wpsn-top-icon-wrapper wpsn-menu-home<?php echo esc_attr($wpsn_avatar_class); ?>"><a href="<?php echo esc_attr(wpsn_nonce(wpsn_page('home'))); ?>">
					<?php
					if ($wpsn_theme_home_avatar && is_user_logged_in()) {
						if ($show_labels) {
							echo '<img class="wpsn-top-box-avatar" src="'.esc_attr(wpsn_get_avatar($current_user->ID)).'" />';
						} else {
							echo '<img class="wpsn-top-box-avatar wpsn-top-box-avatar-no-labels" src="'.esc_attr(wpsn_get_avatar($current_user->ID)).'" />';
						}
					} else {								
						echo '<i class="fa-solid fa-user wpsn-top-icon"></i>';
					}
					if ($show_labels) { 
						echo '<span class="wpsn-top-icon-label">'.esc_attr(get_the_title(wpsn_page('home', true))).'</span>'; 
					} ?>
				</a></div>

				<!-- *** Activity *** -->
				<div class="wpsn-top-icon-wrapper wpsn-menu-activity<?php echo esc_attr($wpsn_avatar_class); ?>"><a href="<?php echo esc_attr(wpsn_nonce(wpsn_page('activity'))); ?>"><i class="fa-solid fa-house wpsn-top-icon wpsn-menu-activity"></i><?php if ($show_labels) { echo '<span class="wpsn-top-icon-label">'.esc_attr(get_the_title(wpsn_page('activity', true))).'</span>'; } ?></a></div>

				<!-- *** Friends *** -->
				<div class="wpsn-top-icon-wrapper wpsn-menu-friends<?php echo esc_attr($wpsn_avatar_class); ?>"><a href="<?php echo esc_attr(wpsn_nonce(wpsn_page('friends'))); ?>"><i class="fa-solid fa-user-group wpsn-top-icon wpsn-menu-friends
					<?php
					if ($friend_requests && !empty($valid_friend_requests)) {
						echo ' wpsn-menu-friends-with-count';
					}
					?>
					"></i><?php if ($show_labels) { echo '<span class="wpsn-top-icon-label">'.esc_attr(get_the_title(wpsn_page('friends', true))).'</span>'; } ?></a>
					<?php
					if ($friend_requests && !empty($valid_friend_requests)) {
						$count = count($friend_requests) <= 9 ? count($valid_friend_requests) : '9+';
						echo '<div class="wpsn-top-friend-requests-count">'.esc_attr($count).'</div>';
					} else {
						echo '<div class="wpsn-top-friend-requests-count wpsn_hide"></div>';
					}
					?>
					</div>	

				<!-- *** External menu items *** -->
				<?php
					do_action('wpsn_menu_action', $wpsn_avatar_class, $show_labels);
				?>

				<!-- *** Search *** -->
				<div class="wpsn-top-icon-wrapper wpsn-menu-search<?php echo esc_attr($wpsn_avatar_class); ?>"><a href="<?php echo esc_attr(wpsn_nonce(wpsn_page('search'))); ?>"><i class="fa-solid fa-magnifying-glass wpsn-top-icon wpsn-menu-edit-search"></i><?php if ($show_labels) { echo '<span class="wpsn-top-icon-label">'.esc_attr(get_the_title(wpsn_page('search', true))).'</span>'; } ?></a></div>	

				<!-- *** Alerts *** -->	
				<div class="wpsn-top-icon-wrapper wpsn-menu-alerts<?php echo esc_attr($wpsn_avatar_class); ?>"><a href="<?php echo esc_attr(wpsn_nonce(wpsn_page('alerts'))); ?>"><i class="fa-solid fa-bell wpsn-top-icon wpsn-menu-alerts
					<?php
					if ($unread_alerts && !empty($valid_unread_alerts)) {
						echo ' wpsn-menu-alerts-with-count';
					}
					?>
					"></i><?php if ($show_labels) { echo '<span class="wpsn-top-icon-label">'.esc_attr(get_the_title(wpsn_page('alerts', true))).'</span>'; } ?></a>
					<?php
					if ($unread_alerts && !empty($valid_unread_alerts)) {
						$count = count($valid_unread_alerts) <= 9 ? count($valid_unread_alerts) : '9+';
						echo '<div class="wpsn-top-alerts-count">'.esc_attr($count).'</div>';
					} else {
						echo '<div class="wpsn-top-alerts-count wpsn_hide"></div>';
					}
					?>
				</div>	

				<!-- *** Edit Profile *** -->
				<div class="wpsn-top-icon-wrapper wpsn-menu-edit-profile<?php echo esc_attr($wpsn_avatar_class); ?>"><a href="<?php echo esc_attr(wpsn_nonce(wpsn_page('edit-profile'))); ?>"><i class="fa-solid fa-screwdriver-wrench wpsn-top-icon wpsn-menu-edit-profile"></i><?php if ($show_labels) { echo '<span class="wpsn-top-icon-label">'.esc_attr(get_the_title(wpsn_page('edit-profile', true))).'</span>'; } ?></a></div>

				<!-- *** Logout *** -->	
				<div class="wpsn-top-icon-wrapper wpsn-menu-logout<?php echo esc_attr($wpsn_avatar_class); ?>"><i class="fa-solid fa-door-open wpsn-top-icon wpsn-menu-log-out"></i><?php if ($show_labels) { echo '<span class="wpsn-top-icon-label wpsn-menu-log-out">'.esc_attr(__('Logout', 'wpsn-instant-social-network-theme')).'</span>'; } ?></div>	

				<!-- *** Additional Menu Items (at end of menu) *** -->
				<?php
					do_action('wpsn_menu_end_action', $wpsn_avatar_class, $show_labels);
				?>

				<!-- *** Admin *** -->
				<?php 
				if (in_array('administrator', $current_user->roles)) {
					$admin_url = admin_url( 'admin.php?page=wpsn-instant-social-network' ) . '/wpsn-admin-page.php';
					?>							
					<div class="wpsn-top-icon-wrapper wpsn-menu-admin<?php echo esc_attr($wpsn_avatar_class); ?>"><a href="<?php echo esc_attr($admin_url); ?>"><i class="fa-solid fa-gear wpsn-top-icon wpsn-menu-stories"></i><span class="wpsn-top-icon-label">&nbsp;</span></a></div>	
				<?php } ?>
				
			<?php } else { ?>
				<div class="wpsn-top-icon-wrapper"><a href="<?php echo esc_attr(wpsn_page('landing-page')); ?>"><i class="fa-solid fa-house wpsn-top-icon"></i><span class="wpsn-top-icon-label"><?php echo esc_attr(__('Home', 'wpsn-instant-social-network-theme')); ?></span></a></div>
				<div class="wpsn-top-icon-wrapper"><a href="<?php echo esc_attr(wpsn_page('login')); ?>"><i class="fa-solid fa-right-to-bracket wpsn-top-icon"></i><span class="wpsn-top-icon-label"><?php echo esc_attr(__('Login', 'wpsn-instant-social-network-theme')); ?></span></a></div>
				<div class="wpsn-top-icon-wrapper"><a href="<?php echo esc_attr(wpsn_page('signup')); ?>"><i class="fa-solid fa-user-plus wpsn-top-icon"></i><span class="wpsn-top-icon-label"><?php echo esc_attr(__('Sign Up', 'wpsn-instant-social-network-theme')); ?></span></a></div>
				<!-- *** Additional Menu Items (at end of menu) *** -->
				<?php
					do_action('wpsn_menu_end_action', $wpsn_avatar_class, $show_labels);
				?>
			<?php } ?>

			</div>
		</div>
		
		<div class="<?php echo esc_attr($wpsn_not_wpsn_theme); ?>"></div>
		<?php
			
	} else {
		echo '<div class="plugin_not_activated">';
			echo '<h1>Please activate the WPSN Plugin via <a href="'.esc_attr(admin_url('plugins.php?plugin_status=all&paged=1&s')).'">Appearance->Plugins</a>.</h1>';
			echo '<p>Configure the theme and plugin options via the WPSN admin menu.</p>';
		echo '</div>';
		die();
	}	
	?>
	
	
	<?php
}
?>