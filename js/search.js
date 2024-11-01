jQuery(document).ready(function() {

	if (jQuery('.wpsn-search-input').length > 0) {
		jQuery('.wpsn-search-input').focus();
	}

	var typingTimer; // Timer identifier
	var doneTypingInterval = 500; // Time in milliseconds
	var keyupCount = 0;
	
	jQuery('.wpsn-search-input').on('keyup', function(event) {
		keyupCount++;

		if (keyupCount <= 2) {
			// Ignore the first two keyup events
			return;
		} else {
			if (typingTimer) {
				clearTimeout(typingTimer); // Clear the previous timer if it exists
			}
		    
			// Execute the function immediately on the third keyup event
			if (keyupCount === 3) {
				wpsn_get_search_results();
			} else {
				// Throttle subsequent executions by 1 second
				typingTimer = setTimeout(function() {
					if (jQuery('.wpsn-search-input').val().length >= 3) {
						wpsn_get_search_results();
					}
				}, doneTypingInterval);
			}
			
       }
	});
	

});

function wpsn_get_search_results() {

	var search = jQuery('.wpsn-search-input').val().toLowerCase().trim();

	if (search.length >= 3) {

		jQuery('.wpsn-search-results').html('<div class="wpsn-search-results-returned"><i class="fa-solid fa-spinner fa-spin"></i></div>');
		jQuery.ajax({
			url : wpsn_search_ajax.ajaxurl,
			data : {
				security: wpsn_search_ajax.security,
				action : 'wpsn_do_search',
				search : search
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status == 'ok') {
					jQuery('.wpsn-search-results').html('');
					
					var resultsArray = Object.values(response.results);
					resultsArray.forEach(function(item) {
						
						var html = '<div class="wpsn-feed-post">';
							html += '<div class="wpsn-feed-post-header">';
							
								html += '<div class="wpsn-feed-post-header-avatar">';
									html += '<a href="'+wpsn_page('home')+'?uid='+item.ID+'&wpsn='+response.nonce+'"><img class="wpsn-feed-post-header-avatar-img" src="'+item.avatar_url+'" /></a>';
								html += '</div>';
								html += '<div class="wpsn-feed-post-header-info">';
									html += '<p class="wpsn-feed-post-header-author-name"><a href="'+wpsn_page('home')+'?uid='+item.ID+'&wpsn='+response.nonce+'">'+item.firstname + ' ' + item.lastname+'</a></p>';
									html += '<p class="wpsn-feed-post-last-active">'+wpsn_friends_ajax.lang_last_active+': '+item.last_active+'</p>';
								html += '</div>';
								
							html += '</div>';
						html += '</div>';
						
						jQuery('.wpsn-search-results').append('<div class="wpsn-search-results-returned">'+html+'</div>');
		
					});
				} else {
					jQuery('.wpsn-search-results').html('<div class="wpsn-search-results-returned">'+response.text+'</div>');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				jQuery('.wpsn-search-results').html('');
				//alert(textStatus+'/'+errorThrown);
			}
		});

	} else {

		jQuery('.wpsn-search-results').html('<div class="wpsn-search-results-returned">'+wpsn_search_ajax.lang_3_letters+'</div>');

	}

}