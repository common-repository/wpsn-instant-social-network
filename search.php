<?php

function wpsn_sc_search() {

    global $wpdb, $current_user;
    $html = '';

    if (is_user_logged_in()) {

        $html .= '<div class="wpsn-wrapper">';
		
			$html .= '<div class="wpsn-wrapper-search">';

				$html .= '<div class="wpsn-search-post-row">';
					$title = esc_html__("Enter part of a person's name", 'wpsn-instant-social-network');
					$title = apply_filters('wpsn_search_title_filter', $title);
					$html .= '<div class="wpsn-login-wrapper-label">'.$title.'</div>';
					$html .= '<input type="text" class="wpsn-search-input" value="" />';
				$html .= '</div>';

				$html .= '<div class="wpsn-search-results">';
				$html .= '</div>';
				
			$html .= '</div>';

        $html .= '</div>';
		
    }

    return $html;

}

?>