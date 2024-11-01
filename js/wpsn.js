// SHARED 

/* Interval for refreshing avatar dot */

wpsn_update_avatar_dot();
const intervalId_chat = setInterval(wpsn_update_avatar_dot, 5000);
function wpsn_update_avatar_dot() {
	
	var uid = jQuery('#wpsn_viewing_user_id').html();
	if (uid > 0 && jQuery('.wpsn-home-page-avatar-dot').length) {
		
		jQuery.ajax({
			url : wpsn_ajax.ajaxurl,
			data : {
				security: 	wpsn_ajax.security,
				action : 	'wpsn_get_user_status',
				uid :		uid
			},
			method : 'POST',
			success : function(response) {
				response = JSON.parse(response);
				if (response.status != 'ok') {
					alert(response.text);
				} else {
					jQuery('.wpsn-home-page-avatar-dot').removeClass('wpsn_active_none').removeClass('wpsn_active_amber').removeClass('wpsn_active_green');
					jQuery('.wpsn-home-page-avatar-dot').addClass(response.dot_class);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert('wpsn_update_avatar_dot/'+textStatus+'/'+errorThrown);
			}
			
		});
	}

}

/* Event listener for paste event on textarea */

jQuery('#wpsn-feed-post-input').on('paste', function(event) {
	var items = (event.clipboardData || event.originalEvent.clipboardData).items;
	for (var i = 0; i < items.length; i++) {
	  if (items[i].type.indexOf('image') !== -1) {
			var blob = items[i].getAsFile();
			var reader = new FileReader();
			reader.onload = function(e) {
				var dataURL = e.target.result;
				var fileList = jQuery('#wpsn-fileList');
				fileList.append('<div class="wpsn-edit-image"><img src="' + dataURL + '" alt="Pasted Image"><div class="wpsn-overlay-delete"><i class="fa-solid fa-trash-can"></i></div></div>');
			};
			reader.readAsDataURL(blob);
		}
	}
});

// LIBRARY

function isEmail(email) {
    // regex from https://emailregex.com
    //var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return regex.test(email);
}

function wpsn_page(page) {
	var id = 'wpsn_'+page.replace(/-/g, '_');
	var url = document.getElementById("wpsn_home_url").innerHTML;
	var element = document.getElementById(id);
	if (element) {
		url = element.innerHTML;
	}
	return url;
}

// Function to get URL parameter by name
function wpsnGetUrlParam(parameter, defaultvalue){
    var urlParameter = defaultvalue;
    if(window.location.href.indexOf(parameter) > -1){
        urlParameter = wpsnGetUrlVars()[parameter];
    }
    return urlParameter;
}

// Function to parse URL parameters
function wpsnGetUrlVars(){
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}

function wpsnRemoveUrlParameter(url, parameter) {
    var urlParts = url.split('?');
    if (urlParts.length >= 2) {
        var prefix = encodeURIComponent(parameter) + '=';
        var params = urlParts[1].split(/[&;]/g);

        // Reverse iteration to safely remove the parameter
        for (var i = params.length; i-- > 0;) { 
            if (params[i].lastIndexOf(prefix, 0) !== -1) { 
                params.splice(i, 1);
            }
        }

        url = urlParts[0] + (params.length > 0 ? '?' + params.join('&') : '');
        return url;
    } else {
        return url;
    }
}

function getYouTubeVideoId(url) {
    var pattern = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=))([\w-]{10,12})$/;
    
    var matches = url.match(pattern);
    if (matches) {
        return matches[1];
    }
    return null;
}

function convertUrlsToLinks(text) {
    // Regular expression to match URLs without whitespace
    var pattern = /(?<!["'])\b(?:https?:\/\/|www\.)\S+\/?\b(?![^<>]*>|[^>]*<\/a>)/gi;

    // Replace URLs with anchor tags
    text = text.replace(pattern, function(match) {
        var url = match.trim(); // Remove leading and trailing whitespace
        // Add http:// prefix if missing
        if (!/^https?:\/\//i.test(url)) {
            url = "http://" + url;
        }
		url = url.replace(/<[^>]*>?/gm, '');
        
        // Check if the URL includes the current domain
        var target = (url.indexOf(window.location.hostname) === -1) ? " target='_blank'" : "";
        
        return "<a href='" + url + "'" + target + ">" + match + "</a>";
    });
    
    return text;
}

