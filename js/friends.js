jQuery(document).ready(function() {
	
	/* UPDATE FRIENDS BUBBLE */
	
	function wpsn_refresh_friends() {
		
		if (jQuery('#wpsn_viewing_user_id').html() != 0) {
			
			jQuery.ajax({
				url : wpsn_friends_ajax.ajaxurl,
				data : {
					security :			wpsn_friends_ajax.security,
					action : 			'wpsn_theme_friends'
				},
				method : 'POST',
				success : function(response) {
					response = JSON.parse(response);
					if (response.status != 'ok') {
						alert(response.text);
					} else {
						var count = parseInt(response.count);					
						var current = parseInt('0' + jQuery('.wpsn-top-friend-requests-count').html());
						if (count > 9) {
							count = '9+';
						}
						if (count > 0) {
							jQuery('.wpsn-top-friend-requests-count').html(count);
							jQuery('.wpsn-top-friend-requests-count').removeClass('wpsn_hide');
						} else {
							jQuery('.wpsn-top-friend-requests-count').addClass('wpsn_hide');
						}
							
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					//alert(textStatus+'/'+errorThrown);
				}
			});
			
		}

	}
	
	wpsn_refresh_friends();
	const intervalId_friends = setInterval(wpsn_refresh_friends, 1000);

	/* SIDE BAR - FRIENDS */

	if (jQuery('.wpsn-side-bar-friends').length && jQuery('.wpsn-side-bar-friends-content').html() == '') {
		jQuery('.wpsn-side-bar-friends-content').html('<i class="fa-solid fa-spinner fa-spin"></i>');		
		wpsn_update_friends_side_bar();
		const intervalId_friends_side_bar = setInterval(wpsn_update_friends_side_bar, 1000);
	}
	function wpsn_update_friends_side_bar() {
		
		jQuery.ajax({
			url : wpsn_friends_ajax.ajaxurl,
			data : {
				security: 	wpsn_friends_ajax.security,
				action : 	'wpsn_side_bar_friends',
				uid :		jQuery('#wpsn_viewing_user_id').html()
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);

				if (response.status != 'ok') {
					alert(response.text);
				} else {

					if (response.data.length > 0) {
						
						var html = '';
							
						var requestsArray = Object.values(response.data);
						requestsArray.forEach(function(item) {
							
							html += '<div class="wpsn-side-bar-content-row">';
								html += '<div class="wpsn-side-bar-content-column-fixed">';
									html += '<a href="'+wpsn_page('home')+'?uid='+item.id+'&wpsn='+wpsn_friends_ajax.nonce+'">';
										html += '<div class="wpsn-friend-side-bar-avatar">';
											html += '<img class="wpsn-side-bar-content-column-fixed-avatar" src="'+item.avatar_url+'" />';
											html += '<div class="wpsn-side-bar-avatar-dot '+item.dot_class+'"></div>';
										html += '</div>';
									html += '</a>';
								html += '</div>';
								html += '<div class="wpsn-side-bar-content-column-flex">';
									html += '<div class="wpsn-side-bar-display-name"><a href="'+wpsn_page('home')+'?uid='+item.id+'&wpsn='+wpsn_friends_ajax.nonce+'">'+item.display_name+'</a></div>';
									html += '<div class="wpsn-side-bar-last-active">'+wpsn_friends_ajax.lang_last_active+': '+item.last_active+'</div>';
								html += '</div>';
							html += '</div>';
														
						});
						
						jQuery('.wpsn-side-bar-friends-content').html(html);
						
					} else {
						
						jQuery('.wpsn-side-bar-friends-content').html(wpsn_friends_ajax.lang_no_friends);
						
					}
					
				}
				var r = jQuery('.wpsn-side-bar-friends-content').css('height').replace(/px/, '');
				var l = response.data.length;
				var h = 87 + ( r * l );
				jQuery('.wpsn-activity-right-column').css('height', h);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
			}
			
		});
		
	}
	
	/* FRIENDS - COUNT BUBBLE */
	
	jQuery('body').on('click', '.wpsn-story-action-friends-request-count', function(e) {
		window.location.href = wpsn_page('friends');
	});
	

	/* FRIENDS - REMOVE */
	
    jQuery('.wpsn-friends').on('mouseenter mouseleave', '.wpsn-friend-row', function(event) {
        if (event.type === 'mouseenter') {
			jQuery(this).find('.wpsn_cancel_friend_remove').removeClass('wpsn_hide');
        } else {
            jQuery(this).find('.wpsn_cancel_friend_remove').addClass('wpsn_hide');
        }
    });
	
	jQuery('body').on('click', '.wpsn_cancel_friend_remove', function(e) {
		
		var button = jQuery(this);
		var w = button.width();
		button.html('<i class="fa-solid fa-spinner fa-spin"></i>').width(w);

		var from_id = button.data('wpsn-from-id');

		jQuery.ajax({
			url : wpsn_friends_ajax.ajaxurl,
			data : {
				security: wpsn_friends_ajax.security,
				action : 'wpsn_cancel_friend_remove',
				from_id : from_id
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);

				if (response.status != 'ok') {
					alert(response.text);
				} else {
					location.reload();
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
			}
			
		});
		
	});	
	
	/* FRIENDS - ACCEPT REQUEST */
	
	jQuery('body').on('click', '.wpsn-story-action-friends-accept-request', function(e) {
		
		var button = jQuery(this);
		var w = button.width();
		button.html('<i class="fa-solid fa-spinner fa-spin"></i>').width(w);

		var from_id = button.data('wpsn-from-id');

		jQuery.ajax({
			url : wpsn_friends_ajax.ajaxurl,
			data : {
				security: wpsn_friends_ajax.security,
				action : 'wpsn_friend_accept_received',
				from_id : from_id
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);

				if (response.status != 'ok') {
					alert(response.text);
				} else {
					if (button.hasClass('wpsn-on-header')) {
						location.reload();
					} else {
						setTimeout(function() {
							button.closest('.wpsn-friend-row-received').fadeOut();
							var tid = jQuery('#wpsn_viewing_user_id').html();
							wpsn_update_friends_lists(tid, from_id);
						}, 250);
					}
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
			}
			
		});
		
	});
	
	/* FRIENDS - CANCEL REQUEST */
	
	jQuery('body').on('click', '.wpsn-story-action-friends-cancel-request', function(e) {

		var button = jQuery(this);
		var w = button.width();
		button.html('<i class="fa-solid fa-spinner fa-spin"></i>').width(w);

		var tid = jQuery('#wpsn_viewing_user_id').html();
		var action = 'wpsn_cancel_friend_request';
		var button_tid = button.data('wpsn-target');
		if (button_tid) { 
			tid = button_tid;
			action = 'wpsn_cancel_friend_request_received';
		}
		var from_id = button.data('wpsn-from-id');

		jQuery.ajax({
			url : wpsn_friends_ajax.ajaxurl,
			data : {
				security: wpsn_friends_ajax.security,
				action : action,
				tid : tid,
				from_id : from_id
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);

				if (response.status != 'ok') {
					alert(response.text);
				} else {
					if (button.hasClass('wpsn-on-header')) {
						location.reload();
					} else {
						if (button_tid) {
							setTimeout(function() {
								button.closest('.wpsn-friend-row-requests').fadeOut();
								button.closest('.wpsn-friend-row-received').fadeOut();
								wpsn_update_friends_lists(tid, from_id);
							}, 250);
						}
					}
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
			}
			
		});
		
	});
	
	/* FRIENDS - REQUEST */
	
	jQuery('body').on('click', '.wpsn-story-action-friends-request', function(e) {
		var button = jQuery(this);
		var w = button.width();
		var html = button.html();
		button.html('<i class="fa-solid fa-spinner fa-spin"></i>').width(w);
		
		var tid = jQuery('#wpsn_viewing_user_id').html();
		if (typeof jQuery(this).data('wpsn-target') !== 'undefined') {
			tid = jQuery(this).data('wpsn-target');
		}
		var uid = wpsn_friends_ajax.wpsn_current_user_id;
		
		jQuery.ajax({
			url : wpsn_friends_ajax.ajaxurl,
			data : {
				security: wpsn_friends_ajax.security,
				action : 'wpsn_add_friend_request',
				uid : uid,
				tid : tid
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status != 'ok') {
					alert(response.text);
				} else {
					button.html('<i class="fa-solid fa-paper-plane"></i><span class="wpsn_button_label">&nbsp;&nbsp;'+wpsn_friends_ajax.lang_request_sent+'</span>').width(w).removeClass('wpsn-story-action-friends-request').addClass('wpsn-disabled');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
			}
			
		});
		
	});

	/* FEED - SHOW FRIENDS */
	
	if (jQuery('.wpsn-friends').length) {
		var uid = wpsn_friends_ajax.wpsn_current_user_id;
		var vid = jQuery('#wpsn_viewing_user_id').html();
		
		if (jQuery('.wpsn-friends').html() == '') {
			jQuery('.wpsn-friends').html('<i class="fa-solid fa-spinner fa-spin"></i><br /><br />');
		}
		
		wpsn_update_friends_lists(uid, vid);
		const intervalId_friends_lists = setInterval(() => wpsn_update_friends_lists(uid, vid), 5000);
	}
	
	function wpsn_update_friends_lists(vid) {

		jQuery.ajax({
			url : wpsn_friends_ajax.ajaxurl,
			data : {
				security: wpsn_friends_ajax.security,
				action : 'wpsn_get_friends',
				vid : vid,
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status != 'ok') {
					alert(response.text);
				} else {
					
					var wpsn_home = jQuery('#wpsn_home').html();
					var html = '';

					if (uid == vid) {
						
						// Friend Requests Sent
						if (response.hasOwnProperty('requests_sent') && response.requests_sent !== null && Object.keys(response.requests_sent).length > 0) {
								
							html = '<div class="wpsn-box-wrapper">';
								html += '<h2>Friend requests sent</h2>';							
								var requestsArray = Object.values(response.requests_sent);

								// Loop through the items in the array
								html += '<div class="wpsn-friend-container">';
									requestsArray.forEach(function(item) {
										
										html += '<div class="wpsn-friend-row-requests">';
											html += '<div class="wpsn-friend-column col1">'
												html += '<a href="'+wpsn_home+'?uid='+item.id+'&wpsn='+wpsn_friends_ajax.nonce+'">';
													html += '<div class="wpsn-friend-side-bar-avatar">';
														html += '<img class="wpsn-feed-post-header-avatar-img" src="' + item.avatar_url + '" alt="Avatar" />';
														html += '<div class="wpsn-side-bar-avatar-dot '+item.dot_class+'"></div>';
													html += '</div>';
												html += '</a>';
											html += '</div>';
											html += '<div class="wpsn-friend-column col2">'
												html += '<div class="wpsn-friend-display-name"><a href="'+wpsn_home+'?uid='+item.id+'&wpsn='+wpsn_friends_ajax.nonce+'">'+item.display_name+'</a></div>';
												html += '<div class="wpsn-friend-daysago">'+item.daysago+'</div>';
											html += '</div>';
											html += '<div class="wpsn-friend-column col3">'
												html += '<div class="wpsn-button-submit wpsn-story-action-friends-cancel-request" data-wpsn-target="'+item.id+'" data-wpsn-from-id="'+uid+'"><i class="fa-solid fa-user-xmark"></i><span class="wpsn_button_label">&nbsp;&nbsp;'+wpsn_friends_ajax.lang_cancel+'</div></div>';
											html += '</div>';
										html += '</div>';
										
									});
								html += '</div>';
								jQuery('.wpsn-friend-requests-sent').html(html);
							html += '</div>';
							
							jQuery('.wpsn-friend-requests-sent').html(html);
						} else {
							jQuery('.wpsn-friend-requests-sent').html('');
						}
						
						// Friend Requests Received
						if (response.hasOwnProperty('requests_received') && response.requests_received !== null && Object.keys(response.requests_received).length > 0) {
								
							html = '<div class="wpsn-box-wrapper">';
								html += '<h2>Friend requests received</h2>';

								var requestsArray = Object.values(response.requests_received);

								// Loop through the items in the array
								html += '<div class="wpsn-friend-container">';
									requestsArray.forEach(function(item) {
										
										html += '<div class="wpsn-friend-row-received">';
											html += '<div class="wpsn-friend-column col1">'
												html += '<a href="'+wpsn_home+'?uid='+item.id+'&wpsn='+wpsn_friends_ajax.nonce+'">';
													html += '<div class="wpsn-friend-side-bar-avatar">';
														html += '<img class="wpsn-feed-post-header-avatar-img" src="' + item.avatar_url + '" alt="Avatar" />';
														html += '<div class="wpsn-side-bar-avatar-dot '+item.dot_class+'"></div>';
													html += '</div>';
												html += '</a>';
											html += '</div>';
											html += '<div class="wpsn-friend-column col2">'
												html += '<div class="wpsn-friend-display-name"><a href="'+wpsn_home+'?uid='+item.id+'&wpsn='+wpsn_friends_ajax.nonce+'">'+item.display_name+'</a></div>';
												html += '<div class="wpsn-friend-daysago">'+item.daysago+'</div>';
											html += '</div>';
											html += '<div class="wpsn-friend-column col3">'
												html += '<div class="wpsn-button-submit wpsn-story-action-friends-accept-request" data-wpsn-from-id="'+item.id+'"><i class="fa-solid fa-user-check"></i><span class="wpsn_button_label">&nbsp;&nbsp;'+wpsn_friends_ajax.lang_accept+'</span></div>';
												html += '<div class="wpsn-button-submit wpsn-story-action-friends-cancel-request" data-wpsn-from-id="'+item.id+'" data-wpsn-target="'+uid+'"><i class="fa-solid fa-user-xmark"></i><span class="wpsn_button_label">&nbsp;&nbsp;'+wpsn_friends_ajax.lang_reject+'</span></div>';
											html += '</div>';
										html += '</div>';
										
									});
								html += '</div>';
							html += '</div>';
							jQuery('.wpsn-friend-requests-received').html(html);

						} else {
							jQuery('.wpsn-friend-requests-received').html('');
						}
						
					}
						
					// Existing Friends
					html = '';
					if (response.hasOwnProperty('friends') && response.friends !== null && Object.keys(response.friends).length > 0) {

						var friendsArray = Object.values(response.friends);

						// Loop through the items in the array
						html += '<div class="wpsn-friend-container">';
							friendsArray.forEach(function(item) {
								
								html += '<div class="wpsn-friend-row">';
									html += '<div class="wpsn-friend-column col1">'
										html += '<a href="'+wpsn_home+'?uid='+item.id+'&wpsn='+wpsn_friends_ajax.nonce+'">';
											html += '<div class="wpsn-friend-side-bar-avatar">';
												html += '<img class="wpsn-feed-post-header-avatar-img" src="' + item.avatar_url + '" alt="Avatar" />';
												html += '<div class="wpsn-side-bar-avatar-dot '+item.dot_class+'"></div>';
											html += '</div>';
										html += '</a>';

									html += '</div>';
									html += '<div class="wpsn-friend-column col2">'
										html += '<div class="wpsn-friend-display-name"><a href="'+wpsn_home+'?uid='+item.id+'&wpsn='+wpsn_friends_ajax.nonce+'">'+item.display_name+'</a></div>';
										html += '<div class="wpsn-friend-daysago">'+item.daysago+'</div>';
									html += '</div>';
									html += '<div class="wpsn-friend-column col3">'
										html += '<div class="wpsn-button-submit wpsn_cancel_friend_remove wpsn_hide" data-wpsn-target="'+item.id+'" data-wpsn-from-id="'+item.id+'"><i class="fa-solid fa-user-xmark"></i><span class="wpsn_button_label">&nbsp;&nbsp;'+wpsn_friends_ajax.lang_remove+'</span></div>';
									html += '</div>';
								html += '</div>';
								
							});
						html += '</div>';

					} else {
						html += '<p>'+wpsn_friends_ajax.lang_no_friends+'</p>';
					}
					jQuery('.wpsn-friends').html(html);					
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
			}
			
		});
		
	}
	
});

