jQuery(document).ready(function() {
	
	/* PROFILE - SHOW/HIDE "CHANGE" BUTTON VISIBILITY */
	
    jQuery('.wpsn-container').hover(
        function() {
            jQuery('.wpsn-edit-avatar-and-cover').removeClass('wpsn_hide');
        }, 
        function() {
            jQuery('.wpsn-edit-avatar-and-cover').addClass('wpsn_hide');
        }
    );

	/* SIDE BAR - PHOTOS */
	
	if (jQuery('.wpsn-side-bar-photos-content').length && jQuery('.wpsn-side-bar-photos-content').html() == '') {
		
		jQuery('.wpsn-side-bar-photos-content').html('<i class="fa-solid fa-spinner fa-spin"></i>');
		
		jQuery.ajax({
			url : wpsn_story_ajax.ajaxurl,
			data : {
				security: 	wpsn_story_ajax.security,
				action : 	'wpsn_side_bar_photos',
				uid :		jQuery('#wpsn_viewing_user_id').html()
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status != 'ok') {
					alert(response.text);
				} else {

					if (response.images.length > 0) {
					
						var html = '<div class="photo-grid">';
						var imagesArray = Object.values(response.images);

						var count = 0;
						var max = response.images.length;
						imagesArray.forEach(function(item) {
							count++;
							html += '<div class="photo-item"><img class="wpsn-show-all-images" data-wpsn-image="'+count+'" data-wpsn-image-max="'+max+'" data-wpsn-single-image="1" data-wpsn-post-id="'+item.post_id+'" src="'+item.thumb+'" /></div>';
						});
						
						html += '</div>';
						
						jQuery('.wpsn-side-bar-photos-content').html(html);
						
					} else {
						
						jQuery('.wpsn-side-bar-photos-content').html(wpsn_story_ajax.lang_no_recent_photos);
						
					}
					
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert('SIDE BAR - PHOTOS.'+textStatus+'/'+errorThrown);
			}
			
		});
		
	}	
	
	/* FEED - SHOW FEED POSTS */
	if (jQuery('.wpsn-feed-posts').length) {
		var limit = jQuery('#wpsn_home_posts_limit').html();
		var offset = 0;
		var mode = jQuery('.wpsn-feed-posts').data('wpsn-activity-mode');
		var post_type = jQuery('.wpsn-feed-posts').data('wpsn-post-type');
		var show_comments_limit = jQuery('#wpsn_home_posts_show_comments_limit').html();
		var pid = jQuery('.wpsn_reply_pid').html(); // Top level Parent ID
		if (pid == '') { pid = 0; }
		
		wpsn_home_show_posts(pid, 0, limit, offset, show_comments_limit, mode, '.wpsn-feed-posts', '', 'load', post_type);			
	}
	
	/* EXCLULDED DUE TO IRRATIC BEHAVIOUR 
	
	// Check when the page is scrolled
	jQuery(window).on('scroll resize', function() {
		if (window.matchMedia("(min-width: 768px)").matches) {
			//checkInView();
		}
	});
	
	// Function to check if element is in view
	function checkInView() {
		var $element = jQuery('.wpsn-load-more');
		var offset = $element.data('load-more-offset');
		if (typeof offset !== 'undefined') {
			var elementTop = $element.offset().top;
			var elementBottom = elementTop + $element.outerHeight();
			var viewportTop = jQuery(window).scrollTop();
			var viewportBottom = viewportTop + jQuery(window).height();
			var isInView = (elementBottom > viewportTop && elementTop < viewportBottom);
			var isReadyToLoad = $element.html().includes("Load more");
			if (isInView && isReadyToLoad) {
				jQuery('.wpsn-load-more').html('<i class="fa-solid fa-spinner fa-spin"></i>');
				do_load_more(offset);
			}
		}
	}
	*/

	jQuery('body').on('click', '.wpsn-load-more', function(e) {
		var $element = jQuery('.wpsn-load-more');
		var offset = $element.data('load-more-offset');
		if (typeof offset !== 'undefined') {
			var elementTop = $element.offset().top;
			var elementBottom = elementTop + $element.outerHeight();
			var viewportTop = jQuery(window).scrollTop();
			var viewportBottom = viewportTop + jQuery(window).height();
			var isReadyToLoad = $element.html().includes("Load more");
			if (isReadyToLoad) {
				jQuery('.wpsn-load-more').html('<i class="fa-solid fa-spinner fa-spin"></i>');
				do_load_more(offset);
			}
		}
	});
	
	function do_load_more(offset) {
	
		var limit = jQuery('#wpsn_home_posts_limit').html();
		var mode = jQuery('.wpsn-feed-posts').data('wpsn-activity-mode');
		var post_type = jQuery('.wpsn-feed-posts').data('wpsn-post-type');
		var show_comments_limit = jQuery('#wpsn_home_posts_show_comments_limit').html();
		wpsn_home_show_posts(0, 0, limit, offset, show_comments_limit, mode, '.wpsn-feed-posts', '', 'wpsn-load-more', post_type);
		
		jQuery('html, body').animate({
			scrollTop: jQuery(window).scrollTop() + 300
		}, 1000);

		setTimeout(function() {
			// Remove Load More if nothing return
			if (jQuery('.wpsn-load-more').length && jQuery('.wpsn-load-more').html().length == 660) {
				jQuery('.wpsn-load-more').remove();
			}
		}, 1000);
		
	}
	
	jQuery('body').on('click', '.wpsn-load-more-replies', function(e) {

		var el = jQuery(this);
		
		var limit = jQuery('#wpsn_home_posts_limit').html();
		var offset = el.data('load-more-offset');
		var mode = el.data('load-more-mode');
		var post_type = jQuery('.wpsn-feed-posts').data('wpsn-post-type');
		var pid = el.data('load-more-pid');
		var indent = el.data('load-more-indent');
		var insert_class = el.data('load-more-insert-class');
		var show_comments_limit = jQuery('#wpsn_home_posts_show_comments_limit').html();

		var offset = jQuery(this).data('load-more-offset');
		
		el.html('<i class="fa-solid fa-spinner fa-spin"></i>');
		
		wpsn_home_show_posts(pid, indent, limit, offset, show_comments_limit, mode, insert_class, el, 'replies', post_type);


	});

	function wpsn_home_show_posts(pid, indent, limit, offset, show_comments_limit, mode, html_element, element_to_remove, from, post_type) {
		
		var uid = jQuery('.wpsn-feed-posts').data('wpsn-id');

		if (offset == 0) {
			//jQuery('.wpsn-feed-posts').html('<i class="fa-solid fa-spinner fa-spin"></i>');
			html = '<div class="wpsn-insert-here-0"></div>';
		} else {
			html = '';
		}
		
		var get = wpsnGetUrlVars();
		
		jQuery.ajax({
			url : wpsn_story_ajax.ajaxurl,
			data : {
				security: wpsn_story_ajax.security,
				action : 'wpsn_get_posts',
				pid : pid,
				uid : uid,
				indent : indent,
				limit : limit,
				offset : offset,
				show_comments_limit : show_comments_limit,
				mode : mode,
				from : from,
				post_type : post_type,
				current_page_id : wpsn_story_ajax.wpsn_current_page_id,
				get : get
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status != 'ok') {
					alert(response.text);
				} else {

					if (response.html.length > 0) {
						
						html += response.html;

						if (indent == 0) {
							if (response.count >= parseInt(limit)) {
								jQuery('.wpsn-load-more').remove();
								html += '<div class="wpsn-load-more" data-load-more-offset="' + (parseInt(offset)+parseInt(limit)) + '" data-load-more-mode="' + mode + '">';
									html += '<i class="fa-solid fa-circle-down"></i>&nbsp;&nbsp;Load more';
								html += '</div>';
							} else {
								jQuery('.wpsn-load-more').remove();
							}
						}							
							
						if (offset == 0) {
							jQuery(html_element).html(html);
						} else {
							jQuery(html_element).append(html);
						}

						// Get the hash parameter value
						var hash = window.location.hash.substring(1);
						if (hash != '') {
							var element = document.getElementById(hash);
							if (element) {
								var topOffset = element.offsetTop; // Calculate the top offset of the element
								window.scrollTo(0, topOffset); // Scroll to the calculated top offset
								history.replaceState(null, null, window.location.pathname);
							}
							
							window.scrollTo(0, window.scrollY - 60);
							wpsn_update_side_divs();
							setTimeout(function() {
								jQuery('#'+hash).fadeOut('fast').fadeIn('fast').fadeOut('fast').fadeIn('fast');
								wpsn_update_side_divs();
							}, 250);
						}
						
					} else {
						
						var isReadyToLoad = false;
						if (typeof jQuery('.wpsn-load-more').html() !== 'undefined') {
							isReadyToLoad = jQuery('.wpsn-load-more').html().includes("Load more");
						} else {
							isReadyToLoad = true;
						}
						if (isReadyToLoad) {
							html = '<div class="wpsn-insert-here-0"></div>';
							html += '<div class="wpsn_no_results"></div>';
							jQuery(html_element).html('');
						}
						
						jQuery('.wpsn-load-more').remove();
						
					}
					
					if (element_to_remove) {
						jQuery(element_to_remove).remove();
					}
										
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
			}
			
		});
		
	}

	/* FEED - SHOW/HIDE POST COMMENTS */
	
    jQuery('body').on('click', '.wpsn-feed-post-toggle', function(e) {
		if (jQuery(this).html().includes('Show comments')) {
			jQuery(this).html('<i class="fa-solid fa-eye-slash"></i>&nbsp;&nbsp;Hide comments').addClass('wpsn-feed-post-toggle-hide').removeClass('wpsn-feed-post-toggle-show');
			jQuery(this).parent().find('.wpsn-feed-post').fadeIn(300, function() {
                jQuery(this).parent().find('.wpsn-feed-post').removeClass('wpsn-feed-post-hidden');
            });
		} else {
			jQuery(this).html('<i class="fa-solid fa-eye"></i>&nbsp;&nbsp;Show comments').addClass('wpsn-feed-post-toggle-show').removeClass('wpsn-feed-post-toggle-hide');
			jQuery(this).parent().find('.wpsn-feed-post').fadeOut(200, function() {
				jQuery(this).parent().find('.wpsn-feed-post').addClass('wpsn-feed-post-hidden');
            });
		}
    });
	
    /* FEED - ADD POST AND REPLY */
						
    jQuery('body').on('click', '.wpsn-feed-post-placeholder', function(e) {
		var wpsn_top_level = jQuery('.wpsn_reply_pid').html();
		if (wpsn_top_level == '') { wpsn_top_level = 0; }
		show_wpsn_popup('<i class="fa-solid fa-floppy-disk"></i><span class="wpsn_button_label">&nbsp;&nbsp;'+wpsn_story_ajax.lang_post+'</span>', 'Post', wpsn_top_level, 0);
    });
    jQuery('body').on('click', '.wpsn-feed-post-content-action-reply', function(e) {
		show_wpsn_popup('<i class="fa-solid fa-floppy-disk"></i><span class="wpsn_button_label">&nbsp;&nbsp;'+wpsn_story_ajax.lang_reply+'</span>', 'Reply', jQuery(this).data('wpsn-pid'), jQuery(this).data('wpsn-indent'));
	});
	
	function show_wpsn_popup(label, type, pid, indent) {
		
		jQuery('#wpsn_new_post').find('.feed-post-save').html(label);
		jQuery('#wpsn_new_post').find('.feed-post-save').attr('data-wpsn-mode', type);
		jQuery('#wpsn_new_post').find('.wpsn-feed-post-input').val('');
		jQuery('#wpsn_new_post').find('#wpsn_fileInput').val('');
		jQuery('#wpsn_new_post').find('.wpsn_reply_pid').html(pid);
		jQuery('#wpsn_new_post').find('.wpsn_reply_indent').html(indent);
		jQuery('#wpsn_new_post').find('#wpsn-fileList').html('');
		jQuery('#wpsn_new_post').find('#wpsn-existingFileList').html('');
        jQuery('#wpsn_new_post').removeClass('wpsn_hide');
		if (jQuery('.wpsn-feed-post-input-subject').parent().hasClass('wpsn-hidden')) {
			jQuery('#wpsn_new_post').find('.wpsn-feed-post-input').focus();
		} else {
			jQuery('#wpsn_new_post').find('#wpsn-feed-post-input-subject').focus();
		}
		var wpsn_fileInput = document.getElementById('wpsn_fileInput');
		if (wpsn_fileInput !== null) {
			wpsn_fileInput.addEventListener('change', wpsn_handleChange);		
		}

	}
	
	/* FEED - CANCEL */

    jQuery('body').on('click', '.wpsn_post_popup_cancel', function(e) {
        jQuery('body').find('.wpsn_post_popup').addClass('wpsn_hide');
        jQuery('.wpsn-feed-post-input').val('');
    });
	jQuery(document).on('keydown', function(event) {
       if (event.key == "Escape") {
			if (jQuery('.wpsn_post_popup_cancel').length == 1) {
				jQuery('body').find('.wpsn_post_popup').addClass('wpsn_hide');
				jQuery('.wpsn-feed-post-input').val('');
			}
       }
	});
	
	/* FEED - SAVE POST */

	jQuery('body').on('keydown', '.wpsn-feed-post-input', function(event) {
		if (event.ctrlKey && event.keyCode == 13) {
			save_new_post();
		}
	});
	jQuery('body').on('click', '.feed-post-save', function() {
		save_new_post();
    });
	
	function save_new_post() {
		
        var feed_subject = jQuery('.wpsn-feed-post-input-subject').val().trim();
        var feed_post = jQuery('.wpsn-feed-post-input').val().trim();
        if (feed_post != '' || jQuery('#wpsn-existingFileList').html() != '' || jQuery('#wpsn-fileList').html()) {
			
			var post_id = jQuery('.feed-post-save').attr('data-wpsn-post-id');
			var pid = jQuery('.wpsn_reply_pid').html();
			var indent = jQuery('.wpsn_reply_indent').html();
			var mode = jQuery('.feed-post-save').attr('data-wpsn-mode'); // Post, Edit or Reply

			jQuery(".feed-post-save").html('<i class="fa-solid fa-spinner fa-spin"></i>');

			var author_name = jQuery('#wpsn-user-name').html();
			var avatar_url = jQuery('#wpsn-user-avatar-url').html();
			var the_author_id = jQuery('#wpsn-author-id').html();
			var target_id = jQuery('#wpsn-target-id').html();
			var target_name = jQuery('#wpsn-target_name').html();
			
			// Assuming you have an input element with ID 'wpsn_fileInput' for file uploads
			var wpsn_fileInput = document.getElementById('wpsn_fileInput');
			var formData = new FormData();
			// Check if files are selected
			if (wpsn_fileInput.files.length > 0) {
				for (var i = 0; i < wpsn_fileInput.files.length; i++) {
					formData.append('data[]', wpsn_fileInput.files[i]);
				}
			}
			
			formData.append('security', wpsn_story_ajax.security);
			formData.append('action', 'wpsn_insert_feed_post');
			formData.append('page_id', wpsn_story_ajax.wpsn_current_page_id);
			formData.append('feed_subject', feed_subject);
			formData.append('feed_post', feed_post);
			formData.append('target_id', target_id);
			formData.append('post_id', post_id);
			formData.append('pid', pid);
			formData.append('mode', mode);
			
			// Create an array to display immediately, and pass back to update database
			var existingImages = [];
			// Existing Images
			if (jQuery('#wpsn-existingFileList').html() != '') {
				// Select all images within the div with id "wpsn-existingFileList" and iterate over them
				jQuery('#wpsn-existingFileList .wpsn-edit-image').each(function() {
					// Get the src attribute of the current img element
					var src = jQuery(this).find('img').attr('src');
					// Replace "-big." with an empty string in the src attribute
					//src = src.replace(/-big\./g, ".");
					// Push the modified src to the existingImages array
					existingImages.push(src);
				});
			}
			
			// Create an array of new images, to display immediately ($FILES will be processed by AJAX)
			var newImages = [];
			var pastedImages = [];
			if (jQuery('#wpsn-fileList').html() != '') {
				// Select all images within the div with id "wpsn-fileList" and iterate over them
				jQuery('#wpsn-fileList img').each(function() {
					// Get the src attribute of the current img element
					var src = jQuery(this).attr('src');
					// Push the src to the pastedImages array
					if (jQuery(this).parent().hasClass('wpsn-edit-image')) {
						pastedImages.push(src.replace(/,/g, "*****"));
					}
					newImages.push(src);
				});
			}

			formData.append('pastedImages', pastedImages);
			formData.append('existingImages', existingImages);

			var the_post = feed_post;
			// Exclude <p> and <br> from conversion
			the_post = the_post.replace(/<p>/g, "{{{p}}}");
			the_post = the_post.replace(/<\/p>/g, "{{{/p}}}");
			the_post = the_post.replace(/<br \/>/g, "{{{br}}}");
			// Convert remaining < and > to &lt; and &gt;
			the_post = the_post.replace(/</g, "&lt;");
			the_post = the_post.replace(/>/g, "&gt;");
			// Bring back <p> and <br> to original form
			the_post = the_post.replace(/\{\{\{p\}\}\}/g, "<p>");
			the_post = the_post.replace(/\{\{\{\/p\}\}\}/g, "</p>");
			the_post = the_post.replace(/\{\{\{br\}\}\}/g, "<br />");
			
			// Replace first <p>
			the_post = the_post.replace('<p>', '<p class="wpsn-post-content">');
			
			// Replace banned words
			if (wpsn_story_ajax.wpsn_banned != '') {
				var words = wpsn_story_ajax.wpsn_banned.split(",");
				for (let i = 0; i < words.length; i++) {
					the_post = the_post.replace(new RegExp(words[i].trim(), "gi"), wpsn_story_ajax.wpsn_banned_sub);
				}
			}
			
			var author_name = jQuery('#wpsn-user-name').html();
			var avatar_url = jQuery('#wpsn-user-avatar-url').html();
			
			var new_indent = 0;
			if (pid > 0) {
				new_indent = parseInt(indent) + 1;
			}
			
			var the_post_id = post_id;
			if (post_id == '') { the_post_id = 'tmp_post_id_'+the_author_id; }
			
			// Insert if reply (will reload page if not)
			
			if (mode == 'Reply') {
				
				if (jQuery('.wpsn_reply_url').length) {
					// Found a redirect URL, so go there
					var redirect_url = jQuery('.wpsn_reply_url').text();
					window.location = redirect_url+'&scroll=1';
				} else {

					var new_post = '<div id="wpsn_post_'+the_post_id+'" class="wpsn-feed-post wpsn-feed-post-line" style="padding-left: 60px;">';
					
						new_post += '<div class="wpsn-feed-post-header">';
							new_post += '<div class="wpsn-feed-post-header-avatar">';
								new_post += '<img class="wpsn-feed-post-header-avatar-img" src="' + avatar_url + '" />';
							new_post += '</div>';
							new_post += '<div class="wpsn-feed-post-header-info">';
								new_post += '<p class="wpsn-feed-post-header-author-name"><a href="' + wpsn_page('home') + '?uid=' + target_id + '">' + author_name + '</a>';
								if (the_author_id != target_id && pid == 0) {										
									new_post += ' <i class="fa-solid fa-caret-right wpsn-posted-to"></i> <a href="' + wpsn_page('home') + '?uid=' + target_id + '">' + target_name + '</a>';
								}
								new_post += '</p>';
								
								new_post += '<p class="wpsn-daysago">'+wpsn_story_ajax.lang_just_now+'</p>';
							new_post += '</div>';
						new_post += '</div>';
						new_post += '<div class="wpsn-feed-post-content">';
						
							// Replace new lines with HTML
							the_post = the_post.replace(/(?:\r\n|\r|\n)/g, '<br />');
						
							new_post += convertUrlsToLinks(the_post);

							// Regular expression pattern to match YouTube URLs
							var pattern = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;

							// Check if the string contains a YouTube URL
							var youtubeUrl = '';
							var matches = the_post.match(pattern);
							if (matches) {
								// Extract the YouTube URL
								youtubeUrl = matches[0];
								var videoId = getYouTubeVideoId(youtubeUrl);
							}
							
							if (youtubeUrl != '') {
								new_post += '<div class="wpsn-video-container">';
									// Note: This is a 3rd party external service to display videos
									new_post += '<iframe width="560" height="315" src="https://www.youtube.com/embed/' + videoId + '" frameborder="0" allowfullscreen></iframe>';
								new_post += '</div>';
							}
						
							// Images
							var allImages = existingImages.concat(newImages);

							if (allImages.length > 0) {
								

								new_post += '<div class="wpsn-images-container">';
								var count = 1;
								
								allImages.forEach(function(image) {
									
									if (count <= 5) {
										
										if (count == 1) {
											new_post += '<div class="wpsn-images-container-image-first">';
												new_post += '<img src="' + image + '" />';
											new_post += '</div>';
										}

									}
									
									if (count >= 2 && count <=5) {
										new_post += '<div class="wpsn-images-container-image">';
											new_post += '<img src="' + image + '" />';
											if (count == 5 && allImages.length > 5) {
												new_post += '<div class="wpsn-images-container-image-overlay-no-click" data-wpsn-single-image="0" data-wpsn-post-id="' + the_post_id + '">+' + (allImages.length-5) + '</div>';
											}
										new_post += '</div>';
									}
									
									count++;

								});
							}
								
							// Actions
							new_post += '<div class="wpsn-feed-post-content-actions">';
								if (new_indent < 2) {
									new_post += '<div class="wpsn-feed-post-content-action-reply" data-wpsn-indent="'+new_indent+'" data-wpsn-pid="'+the_post_id+'"><i class="fa-solid fa-reply"></i><span class="wpsn_button_label">&nbsp;&nbsp;Reply</span></div>';
								}
								new_post += '<div class="wpsn-feed-post-content-action-edit wpsn_hide" data-wpsn-pid="' + the_post_id + '"><i class="fa-solid fa-pen-to-square"></i><span class="wpsn_button_label">&nbsp;&nbsp;'+wpsn_story_ajax.lang_edit+'</span></div>';
								new_post += '<div class="wpsn-feed-post-content-action-delete wpsn_hide" data-wpsn-pid="' + the_post_id + '"><i class="fa-solid fa-trash-can"></i><span class="wpsn_button_label">&nbsp;&nbsp;'+wpsn_story_ajax.lang_delete+'</span></div>';
							new_post += '</div>';

						new_post += '</div>';

						new_post += '<div class="wpsn-insert-here-'+the_post_id+'"></div>';
						
					new_post += '</div>';
							
					jQuery('.wpsn-insert-here-'+pid).prepend(new_post);
					jQuery('#wpsn_post_'+the_post_id).fadeOut(200).fadeIn(200);
					jQuery('body').find('.wpsn_post_popup').addClass('wpsn_hide');
					jQuery('.wpsn-feed-post-input').val('');
					jQuery('.wpsn-feed-post-input').css('height', '46px'); 				
				
				}
			}
						
						
            jQuery.ajax({
                url : wpsn_story_ajax.ajaxurl,
				type: 'POST',
				data : formData,
				processData: false, // Prevent jQuery from automatically processing the data
				contentType: false, // Prevent jQuery from automatically setting Content-Type
                success : function(response) {
                    response = JSON.parse(response);
					if (response.status == 'fail') {
						jQuery('body').find('.wpsn_post_popup').addClass('wpsn_hide');
					} else {
						
						jQuery('input[type="file"]').val(''); // Clear the file input field
						
						if (mode == 'Post' || mode == 'Edit') {
							// window.location.hash = '#wpsn_post_' + the_post_id; // Set the hash to the ID of the div
							// Removed the above as page reloads, and editing post will be at the top
							setTimeout(function() {
								location.reload(); // Reload the page
							}, 1000);
						}
						
						if (mode == 'Reply') {
							// If new post, replace temporary post ID with newly created post id
							if (the_post_id == 'tmp_post_id_'+the_author_id) {
								setTimeout(function() {
									jQuery('.wpsn-wrapper').html(function(index, oldHtml) {
										// Use a regular expression with the global flag to replace all instances
										var regex = new RegExp('tmp_post_id_' + the_author_id, 'g');
										return oldHtml.replace(regex, response.id);
									});
									jQuery('.wpsn-feed-post-content-action-edit').removeClass('wpsn_hide');
									jQuery('.wpsn-feed-post-content-action-delete').removeClass('wpsn_hide');
								}, 250);
								
							}
						}

					}
                },
                error : function(error){
                    alert('AJAX error! '+error.textStatus);
					jQuery('body').find('.wpsn_post_popup').addClass('wpsn_hide');
					jQuery('.wpsn-feed-post-input').val('');
					jQuery('.wpsn-feed-post-input').css('height', '46px'); 

                }
            });

        }
		
	}
	
	/* FEED - EDIT */
	
	jQuery('body').on('click', '.wpsn-feed-post-content-action-edit', function(e) {
		var post_id = jQuery(this).data('wpsn-pid');
		jQuery.ajax({
			url : wpsn_story_ajax.ajaxurl,
			data : {
				security: wpsn_story_ajax.security,
				action : 'wpsn_get_post_info',
				post_id : post_id
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status != 'ok') {
					alert(response.text);
				} else {
					jQuery('#wpsn_new_post').find('.wpsn-feed-post-input').val(response.post);
					jQuery('#wpsn_new_post').find('.wpsn_reply_pid').html(response.post_parent);
					jQuery('#wpsn_new_post').find('#wpsn-fileList').html(response.post_parent);
					jQuery('#wpsn_new_post').find('#wpsn-existingFileList').html('');
					response.images.forEach(function(item) {
						jQuery('#wpsn-existingFileList').append('<div class="wpsn-edit-image"><img src="' + item + '" /><div class="wpsn-overlay-delete"><i class="fa-solid fa-trash-can"></i></div></div>');
					});
					jQuery('#wpsn_new_post').find('#wpsn-fileList').html('');
					jQuery(".feed-post-save").attr("data-wpsn-post-id", post_id);
					jQuery(".feed-post-save").attr("data-wpsn-mode", 'Edit');
					jQuery('.feed-post-save').html('<i class="fa-solid fa-floppy-disk"></i><span class="wpsn_button_label">&nbsp;&nbsp;'+wpsn_story_ajax.lang_edit+'</span>');
					jQuery('#wpsn_new_post').removeClass('wpsn_hide');
					
					var wpsn_fileInput = document.getElementById('wpsn_fileInput');
					wpsn_fileInput.addEventListener('change', wpsn_handleChange);		
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert('Error! '+textStatus+'/'+errorThrown);
			}
		});
		
	});

	
    /* FEED - DELETE */
						
    jQuery('body').on('click', '.wpsn-feed-post-content-action-delete', function(e) {
		var id_to_delete = jQuery(this).data('wpsn-pid');
		jQuery('.wpsn_delete_id').html(id_to_delete);		
		jQuery('#wpsn_confirm').removeClass('wpsn_hide');
    });
	jQuery(document).on('keydown', function(event) {
		if (event.key == "Escape") {
			if (!(jQuery('#wpsn_confirm').hasClass('wpsn_hide'))) {
				jQuery('#wpsn_confirm').addClass('wpsn_hide');
			}
		}
	});
	jQuery('body').on('click', '.wpsn-confirm-post-delete-no', function(e) {
		jQuery('#wpsn_confirm').addClass('wpsn_hide');
	});
	jQuery('body').on('click', '.wpsn-confirm-post-delete-yes', function(e) {
		var id_to_delete = jQuery('.wpsn_delete_id').html();
		jQuery('#wpsn_confirm').addClass('wpsn_hide');
		// Delete from screen
		jQuery('#wpsn_post_'+id_to_delete)
			.fadeOut(function() { // Hide the div after the delay
				jQuery(this).remove(); // Remove the div from the DOM				
			});
				
			// Via ajax, delete from database		
			jQuery.ajax({
				url : wpsn_story_ajax.ajaxurl,
				data : {
					security: wpsn_story_ajax.security,
					action : 'wpsn_delete_post',
					id_to_delete : id_to_delete
				},
				method : 'POST',
				success : function(response) {
					response = JSON.parse(response);
					if (response.status != 'ok') {
						alert(response.text);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					//alert(textStatus+'/'+errorThrown);
				}
			});

	});
	
	jQuery('body').on('click', '.wpsn-show-all-images', function(e) {
		var post_id = jQuery(this).data('wpsn-post-id');
		var single = false;
		if (jQuery(this).data('wpsn-single-image') == '1') {
			single = jQuery(this).attr('src');
		}
		wpsn_show_all_images(post_id, single);
	});
	jQuery('body').on('click', '.wpsn-images-container-image-overlay', function(e) {
		var post_id = jQuery(this).data('wpsn-post-id');
		var single = false;
		if (jQuery(this).data('wpsn-single-image') == '1') {
			single = jQuery(this).attr('src');
		}
		wpsn_show_all_images(post_id, single);
	});
	
	/* FEED - DELETE IMAGE WHEN EDITING */
	jQuery('body').on('click', '.wpsn-overlay-delete', function(e) {
		jQuery(this).parent().fadeOut(500, function() {
			// After fade-out animation completes, remove the parent element from the DOM
			jQuery(this).remove();
		});
	});
	
	/* FEED - VIEW ALL IMAGES */
	
	function wpsn_show_all_images(post_id, single) {

		if (single) {
			jQuery('#wpsn_show_images').find('.wpsn_show_images_container').html('<i class="fa-solid fa-spinner fa-spin"></i>');
			jQuery('.wpsn_show_images_inner').css('min-height', '200px');
		} else {
			jQuery('.wpsn_show_images_inner').css('min-height', '80vh');
		}
		jQuery('#wpsn_show_images').removeClass('wpsn_hide');
				
		// Via Ajax, get a list of images for this post
		// And then show them
		jQuery.ajax({
			url : wpsn_story_ajax.ajaxurl,
			data : {
				security: wpsn_story_ajax.security,
				action : 'wpsn_get_post_images',
				post_id : post_id
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status != 'ok') {
					if (response.status != 'none') {
						alert(response.text);
					}
				} else {
					var html = '';
					var done_single = false;
					response.images.forEach(function(item) {
						if (!single || (single.replace("-thumb.jpg", "-big.jpg") == item && !done_single)) {
							html += '<div class="wpsn-images-container-image-first">';
								html += '<p class="wpsn-show-images-image"><img src="' + item + '" /></p>';
							html += '</div>';
							if (single) { done_single = true; }
						}
					});

					jQuery('#wpsn_show_images').find('.wpsn_show_images_container').html(html);
					
					if (single) {
						
						jQuery('.wpsn_show_images_container').find('img').css('max-height', '60vh').css('width', 'auto').css('max-width', '100%');
						
						// Get the .wpsn_show_images_inner element
						var wpsnShowImagesInner = document.querySelector('.wpsn_show_images_inner');

						// Get the height of the image
						var imageHeight = document.querySelector('.wpsn-show-images-image img').clientHeight;

						// Set the container height to the image height plus space
						wpsnShowImagesInner.style.height = (imageHeight + 125) + 'px';
					
						jQuery('.wpsn_show_images_container').css('height', imageHeight);
						
					}
					
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
			}
		});
	};
	
	jQuery('body').on('click', '.wpsn_show_images_cancel_button', function(e) {
		jQuery('#wpsn_show_images').addClass('wpsn_hide');
		jQuery('.wpsn_show_images_inner').css('height', '100px');
	});
	jQuery(document).on('keydown', function(event) {
		if (event.key == "Escape") {
			if (!(jQuery('#wpsn_show_images').hasClass('wpsn_hide'))) {
				jQuery('#wpsn_show_images').addClass('wpsn_hide');
			}
		}
	});

	// Hide background scroll bar

	const frontDiv1 = document.querySelector('#wpsn_new_post');
	const frontDiv2 = document.querySelector('#wpsn_show_images');
	const backgroundDiv = document.querySelector('body');

	// Check if frontDiv2 exists before adding event listeners
	if (frontDiv1 && frontDiv2) {
		frontDiv1.addEventListener('mouseenter', () => {
			backgroundDiv.style.overflow = 'hidden'; // Disable scrolling in the background div
		});
		frontDiv1.addEventListener('mouseleave', () => {
			backgroundDiv.style.overflow = 'auto'; // Enable scrolling in the background div
		});

		frontDiv2.addEventListener('mouseenter', () => {
			backgroundDiv.style.overflow = 'hidden'; // Disable scrolling in the background div
		});
		frontDiv2.addEventListener('mouseleave', () => {
			backgroundDiv.style.overflow = 'auto'; // Enable scrolling in the background div
		});
	}

});

function wpsn_handleChange() {
	const wpsn_fileList = document.getElementById('wpsn-fileList');
	wpsn_fileList.innerHTML = ''; // Clear previous list
	
	// Loop through each selected file
	for (let i = 0; i < this.files.length; i++) {
		const file = this.files[i];
		
		// Check if the selected file is an image
		if (file.type.startsWith('image/')) {
		const reader = new FileReader();
		reader.onload = function(e) {
			const img = document.createElement('img');
			img.src = e.target.result;
			wpsn_fileList.appendChild(img);
		}
		reader.readAsDataURL(file);
		}
	}
}


