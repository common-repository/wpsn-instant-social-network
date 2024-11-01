jQuery(document).ready(function() {
	
	/* ------------------------------------------------ */
	
	if (jQuery('#wpsn-avatar-input').length > 0) {
			
		var error_msg = wpsn_profile_ajax.lang_save_error;
					
		var avatarInput = document.getElementById('wpsn-avatar-input');
		var avatarPreview = document.getElementById('wpsn-avatar-preview');
		var croppie;

		avatarInput.addEventListener('change', function (event) {
			var file = event.target.files[0];
			var reader = new FileReader();
			
			jQuery('.edit-avatar-save-cancel').removeClass('wpsn_hide');

			reader.onload = function (e) {
				avatarPreview.innerHTML = '<div id="wpsn-avatar-image"></div>';
				croppie = new Croppie(document.getElementById('wpsn-avatar-image'), {
					viewport: { width: 200, height: 200, type: 'circle' },
					boundary: { width: 300, height: 300 },
					showZoomer: true,
				});
				croppie.bind({
					url: e.target.result
				});
				jQuery('.edit-avatar-save').removeClass('wpsn_hide');
				jQuery('.wpsn-edit-avatar-upload').addClass('wpsn_hide');
			};

			reader.readAsDataURL(file);
		});

		document.getElementById('wpsn-avatar-form').addEventListener('submit', function (event) {
			
			jQuery('.edit-avatar-save').html('<i class="fa-solid fa-spinner fa-spin"></i>');
							
			event.preventDefault();
			croppie.result({
				type: 'base64',
				size: 'original'
			}).then(function(base64) {
				var user_id = wpsn_profile_ajax.wpsn_current_user_id;
				jQuery.ajax({
					url: wpsn_profile_ajax.ajaxurl,
					type: 'POST',
					data: {
						action: 'wpsn_save_avatar',
						nonce : wpsn_profile_ajax.profile_nonce,
						avatar_data: base64,
						user_id: user_id
					},
					success: function(response) {
						window.location.href = wpsn_page('home');
					},
					error: function(xhr, status, error) {
						console.error(xhr.responseText);
						jQuery('.edit-cover-save').html('Upload');
					}
				});
			});
		});
	}

	/* ------------------------------------------------ */

	if (jQuery('#wpsn-cover-input').length) {
		
		var error_msg = wpsn_profile_ajax.lang_save_error2;
		
		var coverInput = document.getElementById('wpsn-cover-input');
		var coverPreview = document.getElementById('wpsn-cover-preview');
		var croppie;

		coverInput.addEventListener('change', function (event) {
			var file = event.target.files[0];
			var reader = new FileReader();

			jQuery('.edit-cover-save-cancel').removeClass('wpsn_hide');

			reader.onload = function (e) {
				coverPreview.innerHTML = '<div id="wpsn-cover-image"></div>';
				croppie = new Croppie(document.getElementById('wpsn-cover-image'), {
					viewport: { width: 241, height: 100, type: 'rectangle' },
					boundary: { width: 300, height: 200 },
					showZoomer: true,
				});
				croppie.bind({
					url: e.target.result
				});
				jQuery('.edit-cover-save').removeClass('wpsn_hide');
				jQuery('.wpsn-edit-cover-upload').addClass('wpsn_hide');
			};

			reader.readAsDataURL(file);

		});

		document.getElementById('wpsn-cover-form').addEventListener('submit', function (event) {
			
			jQuery('.edit-cover-save').html('<i class="fa-solid fa-spinner fa-spin"></i>');
			
			event.preventDefault();
			croppie.result({
				type: 'base64',
				size: 'original'
			}).then(function(base64) {
				var user_id = wpsn_profile_ajax.wpsn_current_user_id;
				jQuery.ajax({
					url: wpsn_profile_ajax.ajaxurl,
					type: 'POST',
					data: {
						action: 'wpsn_save_cover',
						nonce : wpsn_profile_ajax.profile_nonce,
						cover_data: base64,
						user_id: user_id
					},
					success: function(response) {
						response = JSON.parse(response);
						if (response.status != 'ok') {
							jQuery('.edit-cover-save').html('<i class="fa-solid fa-floppy-disk"></i><span class="wpsn_button_label">&nbsp;&nbsp;'+wpsn_profile_ajax.lang_save+'</span>');
						} else {
							window.location.href = wpsn_page('home');
						}
					},
					error: function(xhr, status, error) {
						console.error(xhr.responseText);
						jQuery('.edit-cover-save').html('Upload');
						alert(error_msg);
					}
				});
			});
		});
	}
		
	/* ------------------------------------------------ */

	jQuery('body').on('click','.wpsn-edit-cover-upload', function(e) { 
		jQuery('.wpsn-edit-profile-cover').addClass('wpsn_hide');
		jQuery(this).html('<i class="fa-solid fa-spinner fa-spin"></i>');	
		setTimeout(function() {
			jQuery('.wpsn-edit-cover-upload').css('display', 'none');	
			jQuery('.edit-cover-save').css('display', 'block');
			jQuery('.edit-cover-save-cancel').css('display', 'block');
		}, 1000);
	});
	
	jQuery('body').on('click','.edit-cover-save-cancel', function(e) { 
		e.preventDefault();
		jQuery(this).html('<i class="fa-solid fa-spinner fa-spin"></i>');	
		location.reload();
	});
	
	jQuery('body').on('click','.wpsn-edit-avatar-upload', function(e) { 
		jQuery('.wpsn-feed-post-header-avatar-img').css('display', 'none');
		jQuery(this).html('<i class="fa-solid fa-spinner fa-spin"></i>');		
		setTimeout(function() {
			jQuery('.wpsn-edit-avatar-upload').css('display', 'none');	
			jQuery('.edit-avatar-save').css('display', 'block');
			jQuery('.edit-avatar-save-cancel').css('display', 'block');
		}, 1000);
	});
	
	jQuery('body').on('click','.edit-avatar-save-cancel', function(e) { 
		e.preventDefault();
		jQuery(this).html('<i class="fa-solid fa-spinner fa-spin"></i>');			
		location.reload();
	});
	
    /* PROFILE */

	jQuery('.wpsn-edit-profile-row-input').on('keydown', function(event) {
		jQuery(this).removeClass('wpsn-input-error');
	});

    jQuery('body').on('click','.wpsn-edit-profile-save', function(e) { 

        var first_name 			= jQuery('#first_name').val().trim();
        var last_name			= jQuery('#last_name').val().trim();
        var user_email 			= jQuery('#user_email').val().toLowerCase().trim();
		var password 			= jQuery('#change_password').val().trim();
		var password2 			= jQuery('#change_password2').val().trim();
		
		var profile_public 		= jQuery('#profile_public').is(':checked') ? 1 : 0;
		var friends_public 		= jQuery('#friends_public').is(':checked') ? 1 : 0;
		
		var wpsn_email_friends 	= jQuery('#wpsn_email_friends').is(':checked') ? 1 : 0;
		var wpsn_email_posts 	= jQuery('#wpsn_email_posts').is(':checked') ? 1 : 0;

        var cont = true;
        jQuery("input").removeClass("wpsn-input-error");

        if (first_name == '')      { jQuery('#first_name').addClass('wpsn-input-error'); cont = false; }
        if (last_name == '')       { jQuery('#last_name').addClass('wpsn-input-error');  cont = false; }
        if (user_email == '')      { jQuery('#user_email').addClass('wpsn-input-error'); cont = false; }
        if (!isEmail(user_email))  { jQuery('#user_email').addClass('wpsn-input-error'); cont = false; }
		if (password != password2) { jQuery('#change_password').addClass('wpsn-input-error'); jQuery('#change_password2').addClass('wpsn-input-error'); cont = false; }
		
        if (cont) {

            jQuery('.wpsn-edit-profile-save').html('<i class="fa-solid fa-spinner fa-spin"></i>');
			
			var form_data = {};
			jQuery('.wpsn-wrapper-edit-profile input').each(function() {
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
                url : wpsn_profile_ajax.ajaxurl,
                data : {
                    security: wpsn_profile_ajax.security,
                    action : 'wpsn_save_profile_details',
                    first_name : first_name,
                    last_name : last_name,
                    user_email : user_email,
					password : password,
					profile_public : profile_public,
					friends_public : friends_public,
					wpsn_email_friends : wpsn_email_friends,
					wpsn_email_posts : wpsn_email_posts,
					form_data : form_data
                },
                method : 'POST',
                success : function(response) {
					response = JSON.parse(response);

					if (response.status != 'fail') {
						
						if (response.status == 'exists') {
							jQuery('#user_email').val(response.text + ' already taken').addClass('wpsn-input-error');
							jQuery('.wpsn-edit-profile-save').html('Save');
						} else {
							window.location.href = wpsn_page('home');						
						}
						
					} else {
												
						jQuery('.wpsn-edit-profile-save').html('Save');
						alert(response.text);
					}
					
                },
                error : function(error){
                    alert(error.textStatus);
                }
            });

        }
		
    }); 
	
});