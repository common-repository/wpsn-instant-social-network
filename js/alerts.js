jQuery(document).ready(function() {
	
	/* UPDATE ALERTS BUBBLE */

	function wpsn_refresh_alerts() {

		if (jQuery('#wpsn_viewing_user_id').html() != 0) {
		
			jQuery.ajax({
				url : wpsn_alerts_ajax.ajaxurl,
				data : {
					security :			wpsn_alerts_ajax.security,
					action : 			'wpsn_theme_alerts'
				},
				method : 'POST',
				success : function(response) {
					response = JSON.parse(response);
					if (response.status != 'ok') {
						alert(response.text);
					} else {
						var count = parseInt(response.count);
						var current = parseInt('0' + jQuery('.wpsn-top-alerts-count').html());
						if (count > 9) {
							count = '9+';
						}
						if (count > 0 || count == '9+') {
							jQuery('.wpsn-top-alerts-count').html(count);
							jQuery('.wpsn-top-alerts-count').removeClass('wpsn_hide');
							jQuery('.wpsn-menu-alerts').addClass('wpsn-menu-alerts-with-count');
							if (jQuery('.wpsn-story-action-alerts-count').length) {
								jQuery('.wpsn-story-action-alerts-count').html(count);
								jQuery('.wpsn-story-action-alerts-count').removeClass('wpsn_hide');
							}
						} else {
							jQuery('.wpsn-top-alerts-count').addClass('wpsn_hide');
							jQuery('.wpsn-menu-alerts').removeClass('wpsn-menu-alerts-with-count');
							if (jQuery('.wpsn-story-action-alerts-count').length) {
								jQuery('.wpsn-story-action-alerts-count').addClass('wpsn_hide');
							}
						}
						
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					//alert(textStatus+'/'+errorThrown);
				}
			});
			
		}

	}
	wpsn_refresh_alerts();
	const intervalId_alerts = setInterval(wpsn_refresh_alerts, 1000);
	
	/* GO TO ALERTS FROM HEADER */
	
	jQuery('body').on('click', '.wpsn-story-action-alerts', function(e) {
		var alerts_page = wpsn_page('alerts');
		window.location.href = alerts_page;
	});
	
	/* CLEAR ALERTS */
	
	jQuery('body').on('click', '.wpsn-clear-alerts-popup', function(e) {
		jQuery('#wpsn_clear_alerts').removeClass('wpsn_hide');
	});
	
	jQuery('body').on('click', '.wpsn-confirm-clear-alerts-no', function(e) {
		jQuery('#wpsn_clear_alerts').addClass('wpsn_hide');
	});

	jQuery('body').on('click', '.wpsn-confirm-clear-alerts-yes', function(e) {
		jQuery('#wpsn_clear_alerts').addClass('wpsn_hide');
		
		jQuery.ajax({
			url : wpsn_alerts_ajax.ajaxurl,
			data : {
				security: 	wpsn_alerts_ajax.security,
				action : 	'wpsn_clear_alerts'
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status != 'ok' && response.status != 'none') {
					alert(response.status+' / '+response.text);
				} else {
					jQuery('.wpsn-alerts').remove();
					jQuery('.wpsn-clear-alerts-popup').addClass('wpsn_hide');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+' / '+errorThrown);
			}
			
		});
	
	});

	/* ALERTS - FRIENDS BUBBLE */
	
	jQuery('body').on('click', '.wpsn-story-action-friends-request-count', function(e) {
		var alerts_page = wpsn_page('alerts');
		window.location.href = alerts_page;
	});
	
	/* ALERTS - SHOW ALERTS */
	
	jQuery('body').on('click', '.wpsn-alert-button', function(e) {
		jQuery(this).html('<i class="fa-solid fa-spinner fa-spin"></i>');		
	});
	
	if (jQuery('.wpsn-alerts').length) {
		
		jQuery('.wpsn-alerts').html('<i class="fa-solid fa-spinner fa-spin"></i><br /><br />');
		wpsn_get_alerts(0);
		
	}
	
	/* ALERTS - LOAD MORE */
	
	jQuery('body').on('click', '.wpsn-alert-load-more', function(e) {
		
		jQuery('.wpsn-alert-load-more').html('<i class="fa-solid fa-spinner fa-spin"></i>');
		var start = jQuery(this).data('alert-load-more-offset');
		wpsn_get_alerts(start);
		
	});
	
});

function wpsn_get_alerts(start) {
	
	var html = '';

	jQuery.ajax({
		url : wpsn_alerts_ajax.ajaxurl,
		data : {
			security: 	wpsn_alerts_ajax.security,
			action : 	'wpsn_get_alerts',
			start :		start,
		},
		method : 'POST',
		success : function(response) {
			response = JSON.parse(response);
			if (response.status != 'ok' && response.status != 'none') {
				alert(response.status+' / '+response.text);
			} else {
				
				if (response.status == 'ok') {

					var requestsArray = Object.values(response.data);					
					requestsArray.forEach(function(item) {

						html += '<div class="wpsn-alert-row">';
						
							html += '<div class="wpsn-alert-row-avatar">';
								html += '<a href="' + wpsn_page("home") + '?uid=' + item.from_id + '&wpsn='+wpsn_alerts_ajax.nonce+'">';
									html += '<img class="wpsn-feed-post-header-avatar-img" src="' + item.from_avatar + '" alt="' + item.from_name + '\'s profile avatar" />';
								html += '</a>';
							html += '</div>';
						
							html += '<div class="wpsn-alert-row-label">';
								html += '<div class="wpsn-alert-row-name">';
									html += '<a href="' + wpsn_page("home") + '?uid=' + item.from_id + '&wpsn='+wpsn_alerts_ajax.nonce+'">' + item.from_name+'</a>';
								html += '</div>';
								html += '<div class="wpsn-alert-row-date">';
									html += item.date;
								html += '</div>';
							html += '</div>';
							
							var contentLength = item.post_content.length;
							var trimLength = 200;
							var post_content = item.post_content;
							var html_prefix = '';
							if (contentLength > trimLength) {
								post_content = item.post_content.substring(0, trimLength) + '...';
							}
							if (item.type == 'post') {
								html_prefix = 'Posted ';
							}
							if (item.type == 'reply') {
								html_prefix = 'Replied ';
							}
							html += '<div class="wpsn-alert-row-content">';
								if (post_content.startsWith('<p>')) {
									post_content = post_content.replace('<p>', '<p>' + html_prefix);
								} else {
									post_content = html_prefix + post_content;
								}
								if (item.unread == 1) {
									html += '<strong>';
								}
								html += post_content;
								if (item.unread == 1) {
									html += '</strong>';
								}
							html += '</div>';
							
							html += '<div class="wpsn-alert-row-action">';
								var url = '';
								if (item.type == 'post' || item.type == 'reaction') {
									url = wpsn_page('home')+'?uid='+item.from_id+'&wpsn='+wpsn_alerts_ajax.nonce+'#wpsn_post_'+item.post_id;
								}
								if (item.type == 'reply') {
									url = wpsn_page('home')+'?uid='+item.to_id+'&wpsn='+wpsn_alerts_ajax.nonce+'#wpsn_post_'+item.post_id;
								}
								if (item.type == 'friend') {
									url = wpsn_page('home')+'?uid='+item.from_id+'&wpsn='+wpsn_alerts_ajax.nonce;
								}
								html += '<a href="' + url + '"><div class="wpsn-button-submit wpsn-alert-button"><i class="fa-solid fa-caret-right wpsn-alert-go"></i></div></a>';
							html += '</div>';
							

						html += '</div>';
														
					});

					if (parseInt(response.remaining) > 0) {
						html += '<div class="wpsn-alert-load-more" data-alert-load-more-offset="' + (parseInt(response.offset)) + '">';
							html += '<i class="fa-solid fa-circle-down"></i>&nbsp;&nbsp;Load more';
						html += '</div>';
					}
					
					jQuery('.wpsn-clear-alerts-popup').removeClass('wpsn_hide');
						
					
				} else {
					
					html += '<p>No alerts.</p>';
					
				}
				
				if (start == 0) {				
					jQuery('.wpsn-alerts').html(html);
				} else {
					jQuery('.wpsn-alert-load-more').remove();
					jQuery('.wpsn-alerts').append(html);
				}
				
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			//alert(textStatus+' / '+errorThrown);
		}
		
	});
	
}

