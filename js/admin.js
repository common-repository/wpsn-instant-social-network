jQuery(document).ready(function() {
	
	/* ------------------------------------------------ */

	jQuery('#wpsn_background_image_button').click(function(e) {
		e.preventDefault();
		var image = wp.media({
			title: 'Choose Image',
			multiple: false
		}).open()
		.on('select', function(e){
			var uploaded_image = image.state().get('selection').first();
			var image_url = uploaded_image.toJSON().url;
			jQuery('#wpsn_background_image_url').val(image_url);
			jQuery('#wpsn_background_image').attr('src', image_url);
			jQuery('#wpsn_background_image').removeClass('wpsn_hide');
		});
	});
	jQuery('#wpsn_background_remove_button').click(function(e) {
		e.preventDefault();
		jQuery('#wpsn_background_image_url').val('');
		jQuery('#wpsn_background_image').attr('src', '');
		jQuery('#wpsn_background_image').addClass('wpsn_hide');
	});
	
	jQuery('#wpsn_dummy_avatar_image_button').click(function(e) {
		e.preventDefault();
		var image = wp.media({
			title: 'Choose Image',
			multiple: false
		}).open()
		.on('select', function(e){
			var uploaded_image = image.state().get('selection').first();
			var image_url = uploaded_image.toJSON().url;
			jQuery('#wpsn_dummy_avatar_image_url').val(image_url);
			jQuery('#wpsn_dummy_avatar').attr('src', image_url);
		});
	});
	jQuery('#wpsn_dummy_avatar_image_remove_button').click(function(e) {
		e.preventDefault();
		jQuery('#wpsn_dummy_avatar_image_url').val(jQuery('.wps_dummy_avatar_default').html());
		jQuery('#wpsn_dummy_avatar').attr('src', jQuery('.wps_dummy_avatar_default').html());
	});

	/* ------------------------------------------------ */
	
	jQuery('.wp-color-picker').wpColorPicker();
	
	jQuery('body').on('click', '.wpsn_create_page', function(e) {
		
		var button = jQuery(this);
		var title = button.attr('data-title');
		var shortcode = button.attr('data-shortcode');
		var db = button.attr('data-db');
		var w = button.width();
		button.html('<i class="fa-solid fa-spinner fa-spin"></i>').width(w);

		jQuery.ajax({
			url : wpsn_ajax.ajaxurl,
			data : {
				security :			wpsn_ajax.security,
				action : 			'wpsn_add_page',
				title : 			title,
				shortcode :			shortcode,
				db :				db
			},
			method : 'POST',
			success : function(response) {
				
				// Get the current URL
				const url = new URL(window.location.href);

				// Get the URL parameters
				const params = new URLSearchParams(url.search);
				
				// Get the value of the 'page' parameter
				const pageValue = params.get('page');

				// Create a new URLSearchParams object with only the 'page' parameter
				const newParams = new URLSearchParams();

				if (pageValue) {
					newParams.set('page', pageValue);
				}

				// Add the new 'new_page' parameter
				newParams.set('new_page', '1');

				// Construct the new URL with the updated parameters
				url.search = newParams.toString();

				// Reload the page with the new URL
				window.location.href = url.toString();
				
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
			}
		});
		
	});

	jQuery('body').on('click', '.wpsn_admin_email_send', function(e) {
			
		var button = jQuery(this);
		var original_value = button.val();
		button.blur();
		var post_id = button.data('post-id');
		button.val('Sending...');
		
		jQuery.ajax({
			url : wpsn_ajax.ajaxurl,
			data : {
				security :			wpsn_ajax.security,
				action : 			'wpsn_admin_email_send',
				post_id : 			post_id
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status != 'ok') {
					button.val('Failed');
					console.log(response.text);
					setTimeout(function() {
						button.val(original_value);
					}, 1000);
				} else {
					button.val('Sent');
					setTimeout(function() {
						button.val(original_value);
					}, 1000);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
			}
		});
		
	});

	jQuery('body').on('click', '.wpsn_admin_heading', function(e) {
		
		var element = jQuery(this);
			
		if (jQuery(this).find('.fa-caret-up').css('display') != 'none') {
			
			jQuery('.wpsn_admin_section_content').slideUp('fast');
			jQuery('.fa-caret-up').show();
			jQuery('.fa-caret-down').hide();

			jQuery(this).find('.fa-caret-up').hide();
			jQuery(this).find('.fa-caret-down').show();
			jQuery(this).parent().find('.wpsn_admin_section_content').slideDown('fast');
			
		} else {

			jQuery('.wpsn_admin_section_content').slideUp('fast');
			jQuery('.fa-caret-up').show();
			jQuery('.fa-caret-down').hide();

		}
		
	});
		
	jQuery('body').on('click', '.wpsn_banned_suggest', function(e) {
		var words = 'anal,anus,arse,ass,ballsack,balls,bastard,bitch,biatch,bloody,blowjob,blow job,bollock,bollok,boner,boob,bugger,bum,butt,buttplug,clitoris,cock,coon,crap,cunt,damn,dick,dildo,dyke,fag,feck,fellate,fellatio,felching,fuck,f u c k,fudgepacker,fudge packer,flange,Goddamn,God damn,hell,homo,jerk,jizz,knobend,knob end,labia,lmao,lmfao,muff,nigger,nigga,omg,penis,piss,poop,prick,pube,pussy,queer,scrotum,sex,shit,s hit,sh1t,slut,smegma,spunk,tit,tosser,turd,twat,vagina,wank,whore,wtf';
		jQuery('#wpsn_banned').val(words);
	});
	
	jQuery('body').on('click', '.wpsn-reset-activity', function(e) {
		
		jQuery('#wpsn_prompt').val							('What\'s up?');
		jQuery('#wpsn_home_posts_limit').val				('13');
		jQuery('#wpsn_home_posts_show_comments_limit').val	('5');
		jQuery('#wpsn_reply_limit').val						('2');
		jQuery('#wpsn_banned').val							('');
		jQuery('#wpsn_banned_sub').val						('*****');
		
	});
	
	jQuery('body').on('click', '.wpsn-reset-photos', function(e) {
		
		jQuery('#wpsn_home_photos_check').val				('200');
		jQuery('#wpsn_home_photos_include').val				('30');
		
	});
	
	jQuery('body').on('click', '.wpsn-reset-alerts', function(e) {
		
		jQuery('#wpsn_alert_count').val						('100');
		
	});
	
	jQuery('body').on('click', '.wpsn-reset-emails', function(e) {
		
		jQuery('#wpsn_cron_seconds').val					('60');
		jQuery('#wpsn_cron_count').val						('9999');
		
	});
	
	jQuery('body').on('click', '.wpsn-reset-colors', function(e) {
		
		jQuery('#wpsn_color_menu').wpColorPicker			('color', '#4267B2');
		jQuery('#wpsn_color_menu_text').wpColorPicker		('color', '#FFF');
		jQuery('#wpsn_color_primary').wpColorPicker			('color', '#4267B2');
		jQuery('#wpsn_color_primary_hover').wpColorPicker	('color', '#4267B2');
		jQuery('#wpsn_color_primary_border').val('').change();
		jQuery('#wpsn_color_primary_contrast').wpColorPicker('color', '#FFF');
		jQuery('#wpsn_color_secondary').wpColorPicker		('color', '#c6c6c6');
		jQuery('#wpsn_color_active').wpColorPicker			('color', '#000');
		jQuery('#wpsn_color_page_background').wpColorPicker	('color', '#EFEFEF');
		jQuery('#wpsn_color_page_text').wpColorPicker		('color', '#000');
		jQuery('#wpsn_box_background_color').wpColorPicker	('color', '#FFF');
		jQuery('#wpsn_box_border_color').wpColorPicker		('color', '#6f6f6f');
		jQuery('#wpsn_box_text_color').wpColorPicker		('color', '#000');
		jQuery('#wpsn_box_link_color').wpColorPicker		('color', '#4267B2');
		jQuery('#wpsn_box_link_hover_color').wpColorPicker	('color', '#000');
		jQuery('#wpsn_box_input_color').wpColorPicker		('color', '#FFF');
		jQuery('#wpsn_color_cta').wpColorPicker				('color', '#228B22');
		jQuery('#wpsn_color_cancel').wpColorPicker			('color', '#D21404');
		jQuery('#wpsn_color_disabled').wpColorPicker		('color', '#c6c6c6');
		
	});
	
	jQuery('body').on('click', '.wpsn-reset-colors-red', function(e) {
		
		jQuery('#wpsn_color_menu').wpColorPicker			('color', '#ba4444');
		jQuery('#wpsn_color_menu_text').wpColorPicker		('color', '#ffd8d8');
		jQuery('#wpsn_color_primary').wpColorPicker			('color', '#ba4444');
		jQuery('#wpsn_color_primary_hover').wpColorPicker	('color', '#ba4444');
		jQuery('#wpsn_color_primary_border').val('').change();
		jQuery('#wpsn_color_primary_contrast').wpColorPicker('color', '#ffd8d8');
		jQuery('#wpsn_color_secondary').wpColorPicker		('color', '#c69797');
		jQuery('#wpsn_color_active').wpColorPicker			('color', '#ce0000');
		jQuery('#wpsn_color_page_background').wpColorPicker	('color', '#efd7d7');
		jQuery('#wpsn_color_page_text').wpColorPicker		('color', '#680c0c');
		jQuery('#wpsn_box_background_color').wpColorPicker	('color', '#e8a9a9');
		jQuery('#wpsn_box_border_color').wpColorPicker		('color', '#9e1f1f');
		jQuery('#wpsn_box_text_color').wpColorPicker		('color', '#680c0c');
		jQuery('#wpsn_box_link_color').wpColorPicker		('color', '#e03535');
		jQuery('#wpsn_box_link_hover_color').wpColorPicker	('color', '#e03535');
		jQuery('#wpsn_box_input_color').wpColorPicker		('color', '#e8c0c0');
		jQuery('#wpsn_color_cta').wpColorPicker				('color', '#0f5e02');
		jQuery('#wpsn_color_cancel').wpColorPicker			('color', '#ff0000');
		jQuery('#wpsn_color_disabled').wpColorPicker		('color', '#aa918c');
		
	});
	
	jQuery('body').on('click', '.wpsn-reset-colors-green', function(e) {
		
		jQuery('#wpsn_color_menu').wpColorPicker			('color', '#4aba44');
		jQuery('#wpsn_color_menu_text').wpColorPicker		('color', '#d3f7d2');
		jQuery('#wpsn_color_primary').wpColorPicker			('color', '#4aba44');
		jQuery('#wpsn_color_primary_hover').wpColorPicker	('color', '#4aba44');
		jQuery('#wpsn_color_primary_border').val('').change();
		jQuery('#wpsn_color_primary_contrast').wpColorPicker('color', '#d3f7d2');
		jQuery('#wpsn_color_secondary').wpColorPicker		('color', '#9fcc9b');
		jQuery('#wpsn_color_active').wpColorPicker			('color', '#0ace00');
		jQuery('#wpsn_color_page_background').wpColorPicker	('color', '#e5fce3');
		jQuery('#wpsn_color_page_text').wpColorPicker		('color', '#0e6d0d');
		jQuery('#wpsn_box_background_color').wpColorPicker	('color', '#aceaab');
		jQuery('#wpsn_box_border_color').wpColorPicker		('color', '#259e1f');
		jQuery('#wpsn_box_text_color').wpColorPicker		('color', '#11680c');
		jQuery('#wpsn_box_link_color').wpColorPicker		('color', '#35e038');
		jQuery('#wpsn_box_link_hover_color').wpColorPicker	('color', '#35e038');
		jQuery('#wpsn_box_input_color').wpColorPicker		('color', '#bee5bf');
		jQuery('#wpsn_color_cta').wpColorPicker				('color', '#00ff04');
		jQuery('#wpsn_color_cancel').wpColorPicker			('color', '#dd0000');
		jQuery('#wpsn_color_disabled').wpColorPicker		('color', '#8ead8f');
		
	});
	
	jQuery('body').on('click', '.wpsn-reset-colors-blue', function(e) {
		
		jQuery('#wpsn_color_menu').wpColorPicker			('color', '#455fbc');
		jQuery('#wpsn_color_menu_text').wpColorPicker		('color', '#d4dbf9');
		jQuery('#wpsn_color_primary').wpColorPicker			('color', '#455fbc');
		jQuery('#wpsn_color_primary_hover').wpColorPicker	('color', '#455fbc');
		jQuery('#wpsn_color_primary_border').val('').change();
		jQuery('#wpsn_color_primary_contrast').wpColorPicker('color', '#d4dbf9');
		jQuery('#wpsn_color_secondary').wpColorPicker		('color', '#9ca6ce');
		jQuery('#wpsn_color_active').wpColorPicker			('color', '#002dd1');
		jQuery('#wpsn_color_page_background').wpColorPicker	('color', '#e5efff');
		jQuery('#wpsn_color_page_text').wpColorPicker		('color', '#0d3070');
		jQuery('#wpsn_box_background_color').wpColorPicker	('color', '#a7aee5');
		jQuery('#wpsn_box_border_color').wpColorPicker		('color', '#1f3b9e');
		jQuery('#wpsn_box_text_color').wpColorPicker		('color', '#081668');
		jQuery('#wpsn_box_link_color').wpColorPicker		('color', '#3639e2');
		jQuery('#wpsn_box_link_hover_color').wpColorPicker	('color', '#3639e2');
		jQuery('#wpsn_box_input_color').wpColorPicker		('color', '#bec9e5');
		jQuery('#wpsn_color_cta').wpColorPicker				('color', '#0adb00');
		jQuery('#wpsn_color_cancel').wpColorPicker			('color', '#dd0000');
		jQuery('#wpsn_color_disabled').wpColorPicker		('color', '#8e97ad');
		
	});
	
	jQuery('body').on('click', '.wpsn-reset-colors-orange', function(e) {
		
		jQuery('#wpsn_color_menu').wpColorPicker			('color', '#ff7c19');
		jQuery('#wpsn_color_menu_text').wpColorPicker		('color', '#fce9d9');
		jQuery('#wpsn_color_primary').wpColorPicker			('color', '#ff7c19');
		jQuery('#wpsn_color_primary_hover').wpColorPicker	('color', '#ff7c19');
		jQuery('#wpsn_color_primary_border').val('').change();
		jQuery('#wpsn_color_primary_contrast').wpColorPicker('color', '#fce9d9');
		jQuery('#wpsn_color_secondary').wpColorPicker		('color', '#b57c32');
		jQuery('#wpsn_color_active').wpColorPicker			('color', '#fca800');
		jQuery('#wpsn_color_page_background').wpColorPicker	('color', '#f9efe0');
		jQuery('#wpsn_color_page_text').wpColorPicker		('color', '#995e06');
		jQuery('#wpsn_box_background_color').wpColorPicker	('color', '#ffe4ba');
		jQuery('#wpsn_box_border_color').wpColorPicker		('color', '#ff8433');
		jQuery('#wpsn_box_text_color').wpColorPicker		('color', '#ef6413');
		jQuery('#wpsn_box_link_color').wpColorPicker		('color', '#f95300');
		jQuery('#wpsn_box_link_hover_color').wpColorPicker	('color', '#f95300');
		jQuery('#wpsn_box_input_color').wpColorPicker		('color', '#ffedd6');
		jQuery('#wpsn_color_cta').wpColorPicker				('color', '#02a500');
		jQuery('#wpsn_color_cancel').wpColorPicker			('color', '#b50600');
		jQuery('#wpsn_color_disabled').wpColorPicker		('color', '#c9b6a5');
		
	});
	
	jQuery('body').on('click', '.wpsn-reset-colors-stone', function(e) {
		
		jQuery('#wpsn_color_menu').wpColorPicker			('color', '#686868');
		jQuery('#wpsn_color_menu_text').wpColorPicker		('color', '#e8e8e8');
		jQuery('#wpsn_color_primary').val('').change();
		jQuery('#wpsn_color_primary_hover').wpColorPicker	('color', '#dddddd');
		jQuery('#wpsn_color_primary_border').wpColorPicker	('color', '#686868');
		jQuery('#wpsn_color_primary_contrast').wpColorPicker('color', '#686868');
		jQuery('#wpsn_color_secondary').val('').change();
		jQuery('#wpsn_color_cta').wpColorPicker				('color', '#eaf9eb');
		jQuery('#wpsn_color_cancel').wpColorPicker			('color', '#ffeaea');
		jQuery('#wpsn_color_disabled').val('').change();
		jQuery('#wpsn_color_active').wpColorPicker			('color', '#d6d6d6');
		jQuery('#wpsn_color_page_background').wpColorPicker	('color', '#efefef');
		jQuery('#wpsn_color_page_text').wpColorPicker		('color', '#686868');
		jQuery('#wpsn_box_background_color').wpColorPicker	('color', '#efefef');
		jQuery('#wpsn_box_border_color').wpColorPicker		('color', '#686868');
		jQuery('#wpsn_box_text_color').wpColorPicker		('color', '#686868');
		jQuery('#wpsn_box_link_color').wpColorPicker		('color', '#3f3f3f');
		jQuery('#wpsn_box_link_hover_color').wpColorPicker	('color', '#000');
		jQuery('#wpsn_box_input_color').wpColorPicker		('color', '#ffffff');
		
	});
	
	jQuery('body').on('click', '.wpsn-reset-font-sizes', function(e) {

		jQuery('#wpsn_font_x_large').val	(24);
		jQuery('#wpsn_font_large').val		(18);
		jQuery('#wpsn_font_medium').val		(14);
		jQuery('#wpsn_font_small').val		(12);
		jQuery('#wpsn_font_x_small').val	(10);
		jQuery('#wpsn_font_xx_small').val	(8);
		jQuery('#wpsn_font_xxx_small').val	(4);
		
	});
	
	jQuery('body').on('keydown', '.wpsn-admin-input-error', function(e) {
		jQuery(this).removeClass('wpsn-admin-input-error');
	});
	
	jQuery('body').on('click', '.wpsn-admin-email-submit', function(e) {
		
		var button = jQuery(this);
		button.html('<i class="fa-solid fa-spinner fa-spin"></i>');
		
		var wpsn_cron_seconds 	= jQuery('#wpsn_cron_seconds').val();
		var wpsn_cron_count		= jQuery('#wpsn_cron_count').val();
		
		var isNumeric = /^\d+$/;
		if (!isNumeric.test(wpsn_cron_seconds) || wpsn_cron_seconds == '' || wpsn_cron_seconds < 60) { wpsn_cron_seconds = 60 };
		if (!isNumeric.test(wpsn_cron_count) || wpsn_cron_count == '' || wpsn_cron_count < 1) { wpsn_cron_count = 1 };

		jQuery.ajax({
			url : wpsn_ajax.ajaxurl,
			data : {
				security :			wpsn_ajax.security,
				action : 			'wpsn_save_email',
				wpsn_cron_seconds : wpsn_cron_seconds,
				wpsn_cron_count : 	wpsn_cron_count,
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status != 'ok') {
					alert(response.text);
				} else {
					button.html('<i class="fa-solid fa-check"></i>');
					setTimeout(function() {
						button.html('<i class="fa-solid fa-floppy-disk"></i>');
					}, 1000);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
				button.html('<i class="fa-solid triangle-exclamation"></i>');
			}
		});

	});	
	
	
	jQuery('body').on('click', '.wpsn-admin-pages-submit', function(e) {
		
		var button = jQuery(this);
		button.html('<i class="fa-solid fa-spinner fa-spin"></i>');
		
		var wpsn_landing_page 	= jQuery('#wpsn-landing-page').val();
		var wpsn_home 			= jQuery('#wpsn-home').val();
		var wpsn_activity 		= jQuery('#wpsn-activity').val();
		var wpsn_friends		= jQuery('#wpsn-friends').val();
		var wpsn_edit_profile 	= jQuery('#wpsn-edit-profile').val();
		var wpsn_search 		= jQuery('#wpsn-search').val();
		var wpsn_alerts 		= jQuery('#wpsn-alerts').val();
		var wpsn_login 			= jQuery('#wpsn-login').val();
		var wpsn_signup 		= jQuery('#wpsn-signup').val();
		
		var form_data = {};
		jQuery('#wpsn-admin-page-select select').each(function() {
			var fieldId = jQuery(this).attr('id');
	
			if (typeof fieldId !== 'undefined' && !(jQuery(this).hasClass('wpsn-core-field'))) {

				if (jQuery(this).attr('type') === 'checkbox') {
					var fieldValue = jQuery(this).is(':checked') ? 1 : 0;
				} else {
					var fieldValue = jQuery(this).val();
				}
				
				form_data[fieldId] = fieldValue;
					
			}
		});

		jQuery.ajax({
			url : wpsn_ajax.ajaxurl,
			data : {
				security :			wpsn_ajax.security,
				action : 			'wpsn_save_pages',
				wpsn_landing_page : wpsn_landing_page,
				wpsn_home : 		wpsn_home,
				wpsn_activity : 	wpsn_activity,
				wpsn_friends : 		wpsn_friends,
				wpsn_edit_profile : wpsn_edit_profile,
				wpsn_search : 		wpsn_search,
				wpsn_alerts : 		wpsn_alerts,
				wpsn_login : 		wpsn_login,
				wpsn_signup : 		wpsn_signup,
				form_data : 		form_data,
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status != 'ok') {
					//alert(response.text);
				} else {
					button.html('<i class="fa-solid fa-check"></i>');
					setTimeout(function() {
						button.html('<i class="fa-solid fa-floppy-disk"></i>');
					}, 1000);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
				button.html('<i class="fa-solid triangle-exclamation"></i>');
			}
		});

	});	
	
	jQuery('body').on('click', '.wpsn-admin-customize-submit', function(e) {
			
		var button = jQuery(this);
		button.html('<i class="fa-solid fa-spinner fa-spin"></i>');

		var wpsn_content_max_width 				= jQuery('#wpsn_content_max_width').val();
		var wpsn_corner_radius 					= jQuery('#wpsn_corner_radius').val();

		var wpsn_menu_bar				 		= jQuery('#wpsn_menu_bar').is(':checked') ? 1 : 0;
		var wpsn_menu_loggedout 				= jQuery('#wpsn_menu_loggedout').is(':checked') ? 1 : 0;
		var wpsn_top_bar_labels					= jQuery('#wpsn_top_bar_labels').is(':checked') ? 1 : 0;
		var wpsn_theme_home_avatar				= jQuery('#wpsn_theme_home_avatar').is(':checked') ? 1 : 0;
		
		var wpsn_side_activity			 		= jQuery('#wpsn_side_activity').is(':checked') ? 1 : 0;

		var wpsn_background_image_url 			= jQuery('#wpsn_background_image_url').val();
		var wpsn_background_image_space 		= jQuery('#wpsn_background_image_space').val();
		var wpsn_background_image_specific		= jQuery('#wpsn_background_image_specific').is(':checked') ? 1 : 0;
		
		var wpsn_dummy_avatar_image_url 		= jQuery('#wpsn_dummy_avatar_image_url').val();
		
		var wpsn_color_menu						= jQuery('#wpsn_color_menu').val();
		var wpsn_color_menu_text				= jQuery('#wpsn_color_menu_text').val();
		var wpsn_color_primary					= jQuery('#wpsn_color_primary').val();
		var wpsn_color_primary_hover			= jQuery('#wpsn_color_primary_hover').val();
		var wpsn_color_primary_border			= jQuery('#wpsn_color_primary_border').val();
		var wpsn_color_primary_contrast			= jQuery('#wpsn_color_primary_contrast').val();
		var wpsn_color_secondary				= jQuery('#wpsn_color_secondary').val();
		var wpsn_color_active					= jQuery('#wpsn_color_active').val();
		var wpsn_color_page_background			= jQuery('#wpsn_color_page_background').val();
		var wpsn_color_page_text				= jQuery('#wpsn_color_page_text').val();
		var wpsn_box_background_color			= jQuery('#wpsn_box_background_color').val();
		var wpsn_box_border_color				= jQuery('#wpsn_box_border_color').val();
		var wpsn_box_text_color					= jQuery('#wpsn_box_text_color').val();
		var wpsn_box_link_color					= jQuery('#wpsn_box_link_color').val();
		var wpsn_box_link_hover_color			= jQuery('#wpsn_box_link_hover_color').val();
		var wpsn_box_input_color				= jQuery('#wpsn_box_input_color').val();
		var wpsn_color_cta						= jQuery('#wpsn_color_cta').val();
		var wpsn_color_cancel					= jQuery('#wpsn_color_cancel').val();
		var wpsn_color_disabled					= jQuery('#wpsn_color_disabled').val();
		
		var wpsn_font_x_large					= jQuery('#wpsn_font_x_large').val();
		var wpsn_font_large						= jQuery('#wpsn_font_large').val();
		var wpsn_font_medium					= jQuery('#wpsn_font_medium').val();
		var wpsn_font_small						= jQuery('#wpsn_font_small').val();
		var wpsn_font_x_small					= jQuery('#wpsn_font_x_small').val();
		var wpsn_font_xx_small					= jQuery('#wpsn_font_xx_small').val();
		var wpsn_font_xxx_small					= jQuery('#wpsn_font_xxx_small').val();
		
		var wpsn_hide_action_buttons 			= jQuery('#wpsn_hide_action_buttons').is(':checked') ? 1 : 0;
				
		var hasErrors = false;
		
		if (wpsn_content_max_width === "") {
			jQuery('#wpsn_content_max_width').addClass('wpsn-admin-input-error');
			hasErrors = true;
		}
		if (wpsn_corner_radius === "") {
			jQuery('#wpsn_corner_radius').addClass('wpsn-admin-input-error');
			hasErrors = true;
		}
							
		if (!hasErrors) {
			
			jQuery.ajax({
				url : wpsn_ajax.ajaxurl,
				data : {
					security :								wpsn_ajax.security,
					action : 								'wpsn_save_customize',
					wpsn_content_max_width :				wpsn_content_max_width,
					wpsn_corner_radius :					wpsn_corner_radius,
					wpsn_menu_bar :							wpsn_menu_bar,
					wpsn_menu_loggedout : 	 				wpsn_menu_loggedout,
					wpsn_top_bar_labels :	 				wpsn_top_bar_labels,
					wpsn_theme_home_avatar : 				wpsn_theme_home_avatar,
					wpsn_side_activity :					wpsn_side_activity,
					wpsn_background_image_url :				wpsn_background_image_url,
					wpsn_background_image_space :			wpsn_background_image_space,
					wpsn_background_image_specific :		wpsn_background_image_specific,
					wpsn_dummy_avatar_image_url :			wpsn_dummy_avatar_image_url,
					wpsn_color_menu :						wpsn_color_menu,
					wpsn_color_menu_text :					wpsn_color_menu_text,
					wpsn_color_primary :					wpsn_color_primary,
					wpsn_color_primary_hover :				wpsn_color_primary_hover,
					wpsn_color_primary_border :				wpsn_color_primary_border,
					wpsn_color_primary_contrast :			wpsn_color_primary_contrast,
					wpsn_color_secondary :					wpsn_color_secondary,
					wpsn_color_active :						wpsn_color_active,
					wpsn_color_page_background :			wpsn_color_page_background,
					wpsn_color_page_text :					wpsn_color_page_text,
					wpsn_box_background_color :				wpsn_box_background_color,
					wpsn_box_border_color :					wpsn_box_border_color,
					wpsn_box_text_color :					wpsn_box_text_color,
					wpsn_box_link_color :					wpsn_box_link_color,
					wpsn_box_link_hover_color :				wpsn_box_link_hover_color,
					wpsn_box_input_color :					wpsn_box_input_color,
					wpsn_color_cta :						wpsn_color_cta,
					wpsn_color_cancel :						wpsn_color_cancel,
					wpsn_color_disabled :					wpsn_color_disabled,
					wpsn_font_x_large :						wpsn_font_x_large,
					wpsn_font_large :						wpsn_font_large,
					wpsn_font_medium :						wpsn_font_medium,
					wpsn_font_small :						wpsn_font_small,
					wpsn_font_x_small :						wpsn_font_x_small,
					wpsn_font_xx_small :					wpsn_font_xx_small,
					wpsn_font_xxx_small :					wpsn_font_xxx_small,
					wpsn_hide_action_buttons : 				wpsn_hide_action_buttons
					
				},
				method : 'POST',
				success : function(response) {
					response = JSON.parse(response);
					if (response.status != 'ok') {
						//alert(response.text);
					} else {
						button.html('<i class="fa-solid fa-check"></i>');
						setTimeout(function() {
							button.html('<i class="fa-solid fa-floppy-disk"></i>');
						}, 1000);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					button.html('<i class="fa-solid triangle-exclamation"></i>');
				}
			});
			
		} else {
			
			button.html('<i class="fa-solid triangle-exclamation"></i>');

		}

	});	
	
	jQuery('body').on('click', '.wpsn-admin-activity-submit', function(e) {
		
		var button = jQuery(this);
		button.html('<i class="fa-solid fa-spinner fa-spin"></i>');
		
		var wpsn_prompt 						= jQuery('#wpsn_prompt').val();
		var wpsn_home_posts_limit 				= jQuery('#wpsn_home_posts_limit').val();
		var wpsn_home_posts_show_comments_limit = jQuery('#wpsn_home_posts_show_comments_limit').val();
		var wpsn_alert_count 					= jQuery('#wpsn_alert_count').val();
		var wpsn_reply_limit 					= jQuery('#wpsn_reply_limit').val();
		var wpsn_banned 						= jQuery('#wpsn_banned').val();
		var wpsn_banned_sub						= jQuery('#wpsn_banned_sub').val();
		var wpsn_markdown 						= jQuery('#wpsn_markdown').is(':checked') ? 1 : 0;
		
				
		var isNumeric = /^\d+$/;
		var hasErrors = false;
		
		if (wpsn_home_posts_limit === "") {
			jQuery('#wpsn_home_posts_limit').addClass('wpsn-admin-input-error');
			hasErrors = true;
		} else if (!isNumeric.test(wpsn_home_posts_limit)) {
			jQuery('#wpsn_home_posts_limit').addClass('wpsn-admin-input-error');
			hasErrors = true;
		}
					
		if (wpsn_home_posts_show_comments_limit === "") {
			jQuery('#wpsn_home_posts_show_comments_limit').addClass('wpsn-admin-input-error');
			hasErrors = true;
		} else if (!isNumeric.test(wpsn_home_posts_show_comments_limit)) {
			jQuery('#wpsn_home_posts_show_comments_limit').addClass('wpsn-admin-input-error');
			hasErrors = true;
		}
					
		if (wpsn_alert_count === "") {
			jQuery('#wpsn_alert_count').addClass('wpsn-admin-input-error');
			hasErrors = true;
		} else if (!isNumeric.test(wpsn_alert_count)) {
			jQuery('#wpsn_alert_count').addClass('wpsn-admin-input-error');
			hasErrors = true;
		}
					
		if (wpsn_reply_limit === "") {
			jQuery('#wpsn_reply_limit').addClass('wpsn-admin-input-error');
			hasErrors = true;
		} else if (!isNumeric.test(wpsn_reply_limit)) {
			jQuery('#wpsn_reply_limit').addClass('wpsn-admin-input-error');
			hasErrors = true;
		}		

		if (wpsn_prompt === "") {
			jQuery('#wpsn_prompt').addClass('wpsn-admin-input-error');
			hasErrors = true;
		}		
		
		if (!hasErrors) {
			
			jQuery.ajax({
				url : wpsn_ajax.ajaxurl,
				data : {
					security :								wpsn_ajax.security,
					action : 								'wpsn_save_activity',
					wpsn_prompt : 							wpsn_prompt,
					wpsn_home_posts_limit : 				wpsn_home_posts_limit,
					wpsn_home_posts_show_comments_limit : 	wpsn_home_posts_show_comments_limit,	
					wpsn_alert_count :						wpsn_alert_count,
					wpsn_reply_limit :						wpsn_reply_limit,
					wpsn_banned :							wpsn_banned,
					wpsn_banned_sub :						wpsn_banned_sub,
					wpsn_markdown : 						wpsn_markdown
				},
				method : 'POST',
				success : function(response) {
					response = JSON.parse(response);
					if (response.status != 'ok') {
						//alert(response.text);
					} else {
						button.html('<i class="fa-solid fa-check"></i>');
						setTimeout(function() {
							button.html('<i class="fa-solid fa-floppy-disk"></i>');
						}, 1000);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					button.html('<i class="fa-solid triangle-exclamation"></i>');
				}
			});
			
		} else {
			
			button.html('<i class="fa-solid triangle-exclamation"></i>');
			
		}

	});	
	
});

function setColorPickerValue(pickerId, colorValue) {
	document.getElementById(pickerId).value = colorValue;
}
