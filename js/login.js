jQuery(document).ready(function() {
	
	/* LOGOUT */
	
	jQuery('body').on('click', '.wpsn-menu-log-out', function(e) {
		
		jQuery.ajax({
			url : wpsn_login_ajax.ajaxurl,
			data : {
				security: wpsn_login_ajax.security,
				action : 'wpsn_logout',
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status == 'ok') {
					window.location.href = '/';
				} else {
					//alert(response.text);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(textStatus+'/'+errorThrown);
			}
		});
		
	});
	
	
	/* FORGOTTEN PASSWORD */
	
	jQuery('body').on('click', '.wpsn-login-forgotten-password', function(e) {
		jQuery('.wpsn-login-wrapper-label').css('display', 'none');
		jQuery('.wpsn-login-wrapper-row-password').css('display', 'none');
		jQuery('.wpsn-login-forgotten-password').css('display', 'none');
		jQuery('.wpsn-login-wrapper-submit').css('display', 'none');
		jQuery('.wpsn-login-wrapper-forgotten-password-submit').css('display', 'block');
		jQuery('.forgotten-password-label').css('display', 'block');
    });

	jQuery('body').on('click', '.wpsn-login-wrapper-forgotten-password-submit', function(e) {
		var email = jQuery('.wpsn-login-wrapper-email').val().trim().toLowerCase();
		if (isEmail(email)) {

			jQuery('.wpsn-login-wrapper-forgotten-password-submit').html('<i class="fa-solid fa-spin fa-spinner"></i>');
	
			jQuery.ajax({
				url : wpsn_login_ajax.ajaxurl,
				data : {
					security: wpsn_login_ajax.security,
					action : 'wpsn_new_password',
					username: email,
				},
				method : 'POST',
				success : function(response) {
					response = JSON.parse(response);
					jQuery('.wpsn-login-wrapper-forgotten-password-submit').html('Send');
					jQuery('.wpsn-login-error').html('Check your email...');
				},
				error: function(jqXHR, textStatus, errorThrown) {
					jQuery('.wpsn-login-wrapper-forgotten-password-submit').html('Send');
					//alert(textStatus+'/'+errorThrown);
				}
			});

		} else {
			jQuery('.wpsn-login-error').html('Invalid email address format');
		}
		
	});
											
	/* REGISTER */

	jQuery('body').on('click', '.wpsn-signup-wrapper-firstname', function(e) {
		jQuery('.wpsn-signup-error').html('');
    });
	jQuery('.wpsn-signup-wrapper-firstname').keypress(function(event) {
		jQuery('.wpsn-signup-error').html('');
		// Check if Enter key is pressed (keyCode 13)
		if (event.which === 13) {
			// Your code to react to Enter key press here
			jQuery('.wpsn-signup-wrapper-lastname').focus();	
		}
    });

	jQuery('body').on('click', '.wpsn-signup-wrapper-lastname', function(e) {
		jQuery('.wpsn-signup-error').html('');
    });
	jQuery('.wpsn-signup-wrapper-lastname').keypress(function(event) {
		jQuery('.wpsn-signup-error').html('');
		// Check if Enter key is pressed (keyCode 13)
		if (event.which === 13) {
			// Your code to react to Enter key press here
			jQuery('.wpsn-signup-wrapper-email').focus();	
		}
    });

	jQuery('body').on('click', '.wpsn-signup-wrapper-email', function(e) {
		jQuery('.wpsn-signup-error').html('');
    });
	jQuery('.wpsn-signup-wrapper-email').keypress(function(event) {
		jQuery('.wpsn-signup-error').html('');
		// Check if Enter key is pressed (keyCode 13)
		if (event.which === 13) {
			// Your code to react to Enter key press here
			jQuery('.wpsn-signup-wrapper-password').focus();	
		}
    });

	jQuery('body').on('click', '.wpsn-signup-wrapper-password', function(e) {
		jQuery('.wpsn-signup-error').html('');
    });
	jQuery('.wpsn-signup-wrapper-password').keypress(function(event) {
		jQuery('.wpsn-signup-error').html('');
		// Check if Enter key is pressed (keyCode 13)
		if (event.which === 13) {
			// Your code to react to Enter key press here
			jQuery('.wpsn-signup-wrapper-password2').focus();	
		}
    });

	jQuery('body').on('click', '.wpsn-signup-wrapper-password2', function(e) {
		jQuery('.wpsn-signup-error').html('');
    });
	jQuery('.wpsn-signup-wrapper-password2').keypress(function(event) {
		jQuery('.wpsn-signup-error').html('');
		// Check if Enter key is pressed (keyCode 13)
		if (event.which === 13) {
			// Your code to react to Enter key press here
			process_signup();	
		}
    });
	
    jQuery('body').on('click', '.wpsn-signup-wrapper-submit', function(e) {
		process_signup();
    });
	
	// Focus on username
	jQuery('.wpsn-login-wrapper-email').focus();
	
	function process_signup() {
		
        var firstname = jQuery('.wpsn-signup-wrapper-firstname').val().trim();
        var lastname = jQuery('.wpsn-signup-wrapper-lastname').val().trim();
		var email = jQuery('.wpsn-signup-wrapper-email').val().trim().toLowerCase();
		var password = jQuery('.wpsn-signup-wrapper-password').val().trim();
		var password2 = jQuery('.wpsn-signup-wrapper-password2').val().trim();

		if (firstname == '') {
			jQuery('.wpsn-signup-error').html('Please enter your first name');
		} else {
			
			if (lastname == '') {
				jQuery('.wpsn-signup-error').html('Please enter your last name');
			} else {


				if (isEmail(email)) {
					
					if (password == '') {
						jQuery('.wpsn-signup-error').html('Please enter your password');
					} else {

						if (password2 == '') {
							jQuery('.wpsn-signup-error').html('Please enter your password twice');
						} else {

							if (password != password2) {
								jQuery('.wpsn-signup-error').html('Make sure you enter the same password twice');
							} else {
						
								jQuery('.wpsn-signup-error').html('');
								jQuery('.wpsn-signup-wrapper-submit').html('<i class="fa-solid fa-spin fa-spinner"></i>');

								jQuery.ajax({
									url : wpsn_login_ajax.ajaxurl,
									data : {
										security: wpsn_login_ajax.security,
										action : 'wpsn_sign_up_user',
										firstname: firstname,
										lastname: lastname,
										email: email,
										password: password
									},
									method : 'POST',
									success : function(response) {
										response = JSON.parse(response);
										if (response.status == 'ok') {
											
											// Now login automatically...
											jQuery.ajax({
												url : wpsn_login_ajax.ajaxurl,
												data : {
													security: wpsn_login_ajax.security,
													action : 'wpsn_validate_login',
													username: email,
													password: password
												},
												method : 'POST',
												success : function(response) {
													response = JSON.parse(response);
													if (response.status == 'ok') {
														window.location.href = response.url;
													} else {
														jQuery('.wpsn-login-wrapper-submit').html('Login');
														jQuery('.wpsn-login-error').html(response.text);
													}
												},
												error: function(jqXHR, textStatus, errorThrown) {
													jQuery('.wpsn-login-wrapper-submit').html('Login');
													jQuery('.wpsn-login-error').html(response.text);
												}
											});
											
										} else {
											jQuery('.wpsn-signup-wrapper-submit').html('Join');
											jQuery('.wpsn-signup-error').html(response.text);
										}
									},
									error : function(error){
										jQuery('.wpsn-signup-wrapper-submit').html('Join');
										alert('process_signup/'+textStatus+'/'+errorThrown);
									}
								});
							}
							
						}
						
					}
					
				} else {
					jQuery('.wpsn-signup-error').html('Invalid email address format');
				}
			}
		}
		
	}	

    /* LOGIN */

	jQuery('body').on('click', '.wpsn-login-wrapper-email', function(e) {
		jQuery('.wpsn-login-error').html('');
    });
	jQuery('body').on('click', '.wpsn-login-wrapper-password', function(e) {
		jQuery('.wpsn-login-error').html('');
    });
	jQuery('.wpsn-login-wrapper-email').keypress(function(event) {
		jQuery('.wpsn-login-error').html('');
		// Check if Enter key is pressed (keyCode 13)
		if (event.which === 13) {
			// Your code to react to Enter key press here
			jQuery('.wpsn-login-wrapper-password').focus();	
		}
	});
	jQuery('.wpsn-login-wrapper-password').keypress(function(event) {
		jQuery('.wpsn-login-error').html('');
		// Check if Enter key is pressed (keyCode 13)
		if (event.which === 13) {
			// Your code to react to Enter key press here
			process_login();	
		}
	});

    jQuery('body').on('click', '.wpsn-login-wrapper-submit', function(e) {
		process_login();
    });

	function process_login() {
		
        var username = jQuery('.wpsn-login-wrapper-email').val().trim();
		var password = jQuery('.wpsn-login-wrapper-password').val().trim();
		
		if (username == '') {
			jQuery('.wpsn-login-error').html('Please enter your login email address');
		} else {
				
			if (password == '') {
				jQuery('.wpsn-login-error').html('Please enter your password');
			} else {
				
				jQuery('.wpsn-login-error').html('');
				jQuery('.wpsn-login-wrapper-submit').html('<i class="fa-solid fa-spin fa-spinner"></i>');

				jQuery.ajax({
					url : wpsn_login_ajax.ajaxurl,
					data : {
						security: wpsn_login_ajax.security,
						action : 'wpsn_validate_login',
						username: username,
						password: password
					},
					method : 'POST',
					success : function(response) {
						response = JSON.parse(response);
						if (response.status == 'ok') {
							window.location.href = response.url;
						} else {
							jQuery('.wpsn-login-wrapper-submit').html('Login');
							jQuery('.wpsn-login-error').html(response.text);
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						jQuery('.wpsn-login-wrapper-submit').html('Login');
						//alert(textStatus+'/'+errorThrown);
					}
				});
				
			}
				
		}
		
	}


});
