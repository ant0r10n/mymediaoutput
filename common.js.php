<?php
	@header("Content-Type: text/javascript; charset=UTF-8");
	$currDir=dirname(__FILE__);
	include("$currDir/defaultLang.php");
	include("$currDir/language.php");
?>

document.observe("dom:loaded", function() {
});

/* initials and fixes */
jQuery(function(){
	jQuery(window).resize(function(){ fix_table_responsive_width(); });
	fix_table_responsive_width();

	jQuery('img.img-responsive').each(function(){
		jQuery(this).css({ maxWidth: jQuery(this)[0].naturalWidth });
	});

});

/* fix table-responsive behavior on Chrome */
function fix_table_responsive_width(){
	var resp_width = jQuery('div.table-responsive').width();
	var table_width;

	if(resp_width){
		jQuery('div.table-responsive table').width('100%');
		table_width = jQuery('div.table-responsive table').width();
		resp_width = jQuery('div.table-responsive').width();
		if(resp_width == table_width){
			jQuery('div.table-responsive table').width(resp_width - 1);
		}
	}
}

function colorize(){ }

function media_validateData(){
	if($('type').value == ''){ modal_window({ message: '<div class="alert alert-danger"><?php echo addslashes($Translation['field not null']); ?></div>', title: "<?php echo addslashes($Translation['error:']); ?> Type", close: function(){ jQuery('#type').focus(); } }); return false; };
	if($('title').value == ''){ modal_window({ message: '<div class="alert alert-danger"><?php echo addslashes($Translation['field not null']); ?></div>', title: "<?php echo addslashes($Translation['error:']); ?> Title", close: function(){ jQuery('#title').focus(); } }); return false; };
	if($('artist').value == ''){ modal_window({ message: '<div class="alert alert-danger"><?php echo addslashes($Translation['field not null']); ?></div>', title: "<?php echo addslashes($Translation['error:']); ?> Artist", close: function(){ jQuery('#artist').focus(); } }); return false; };
	if($('condition').value == ''){ modal_window({ message: '<div class="alert alert-danger"><?php echo addslashes($Translation['field not null']); ?></div>', title: "<?php echo addslashes($Translation['error:']); ?> Condition", close: function(){ jQuery('#condition').focus(); } }); return false; };
	if($('price').value == ''){ modal_window({ message: '<div class="alert alert-danger"><?php echo addslashes($Translation['field not null']); ?></div>', title: "<?php echo addslashes($Translation['error:']); ?> Price", close: function(){ jQuery('#price').focus(); } }); return false; };
	if($('active').value == ''){ modal_window({ message: '<div class="alert alert-danger"><?php echo addslashes($Translation['field not null']); ?></div>', title: "<?php echo addslashes($Translation['error:']); ?> Active", close: function(){ jQuery('#active').focus(); } }); return false; };
	var nicdescription=nicEditors.findEditor('description').getContent();
	if(nicdescription=='' || nicdescription=='<br>' || nicdescription=='<br/>' || nicdescription=='<br />'){ modal_window({ message: '<div class="alert alert-danger"><?php echo addslashes($Translation['field not null']); ?></div>', title: "<?php echo addslashes($Translation['error:']); ?> Description" }); return false; }
	return true;
}
function owners_validateData(){
	return true;
}
function post(url, params, update, disable, loading){
	new Ajax.Request(
		url, {
			method: 'post',
			parameters: params,
			onCreate: function() {
				if($(disable) != undefined) $(disable).disabled=true;
				if($(loading) != undefined && update != loading) $(loading).update('<div style="direction: ltr;"><img src="loading.gif"> <?php echo $Translation['Loading ...']; ?></div>');
			},
			onSuccess: function(resp) {
				if($(update) != undefined) $(update).update(resp.responseText);
			},
			onComplete: function() {
				if($(disable) != undefined) $(disable).disabled=false;
				if($(loading) != undefined && loading != update) $(loading).update('');
			}
		}
	);
}
function post2(url, params, notify, disable, loading, redirectOnSuccess){
	new Ajax.Request(
		url, {
			method: 'post',
			parameters: params,
			onCreate: function() {
				if($(disable) != undefined) $(disable).disabled=true;
				if($(loading) != undefined) $(loading).show();
			},
			onSuccess: function(resp) {
				/* show notification containing returned text */
				if($(notify) != undefined) $(notify).removeClassName('Error').appear().update(resp.responseText);

				/* in case no errors returned, */
				if(!resp.responseText.match(/<?php echo $Translation['error:']; ?>/)){
					/* redirect to provided url */
					if(redirectOnSuccess != undefined){
						window.location=redirectOnSuccess;

					/* or hide notification after a few seconds if no url is provided */
					}else{
						if($(notify) != undefined) window.setTimeout(function(){ $(notify).fade(); }, 15000);
					}

				/* in case of error, apply error class */
				}else{
					$(notify).addClassName('Error');
				}
			},
			onComplete: function() {
				if($(disable) != undefined) $(disable).disabled=false;
				if($(loading) != undefined) $(loading).hide();
			}
		}
	);
}
function passwordStrength(password, username){
	// score calculation (out of 10)
	var score = 0;
	re = new RegExp(username, 'i');
	if(username.length && password.match(re)) score -= 5;
	if(password.length < 6) score -= 3;
	else if(password.length > 8) score += 5;
	else score += 3;
	if(password.match(/(.*[0-9].*[0-9].*[0-9])/)) score += 3;
	if(password.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)) score += 5;
	if(password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) score += 2;

	if(score >= 9)
		return 'strong';
	else if(score >= 5)
		return 'good';
	else
		return 'weak';
}
function validateEmail(email) { 
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}
function loadScript(jsUrl, cssUrl, callback){
	// adding the script tag to the head
	var head = document.getElementsByTagName('head')[0];
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = jsUrl;

	if(cssUrl != ''){
		var css = document.createElement('link');
		css.href = cssUrl;
		css.rel = "stylesheet";
		css.type = "text/css";
		head.appendChild(css);
	}

	// then bind the event to the callback function 
	// there are several events for cross browser compatibility
	if(script.onreadystatechange != undefined){ script.onreadystatechange = callback; }
	if(script.onload != undefined){ script.onload = callback; }

	// fire the loading
	head.appendChild(script);
}
/**
 * options object. The following members can be provided:
 *    url: iframe url to load
 *    message: instead of a url to open, you could pass a message. HTML tags allowed.
 *    id: id attribute of modal window
 *    title: optional modal window title
 *    size: 'default', 'full'
 *    close: optional function to execute on closing the modal
 *    footer: optional array of objects describing the buttons to display in the footer.
 *       Each button object can have the following members:
 *          label: string, label of button
 *          bs_class: string, button bootstrap class. Can be 'primary', 'default', 'success', 'warning' or 'danger'
 *          click: function to execute on clicking the button. If the button closes the modal, this
 *                 function is executed before the close handler
 *          causes_closing: boolean, default is true.
 */
function modal_window(options){
	var id = options.id;
	var url = options.url;
	var title = options.title;
	var footer = options.footer;
	var message = options.message;
	
	if(typeof(id) == 'undefined') id = random_string(20);
	if(typeof(footer) == 'undefined') footer = [];
	
	if(jQuery('#' + id).length){
		/* modal exists -- remove it first */
		jQuery('#' + id).remove();
	}
	
	/* prepare footer buttons, if any */
	var footer_buttons = '';
	for(i = 0; i < footer.length; i++){
		if(typeof(footer[i].causes_closing) == 'undefined'){ footer[i].causes_closing = true; }
		if(typeof(footer[i].bs_class) == 'undefined'){ footer[i].bs_class = 'default'; }
		footer[i].id = id + '_footer_button_' + random_string(10);
		
		footer_buttons += '<button type="button" class="btn btn-' + footer[i].bs_class + '" ' +
				(footer[i].causes_closing ? 'data-dismiss="modal" ' : '') +
				'id="' + footer[i].id + '" ' +
				'>' + footer[i].label + '</button>';
	}

	jQuery('body').append(
		'<div class="modal fade" id="' + id + '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' +
			'<div class="modal-dialog">' +
				'<div class="modal-content">' +
					( title != undefined ?
						'<div class="modal-header">' +
							'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
							'<h3 class="modal-title" id="myModalLabel">' + title + '</h3>' +
						'</div>'
						: ''
					) +
					'<div class="modal-body" style="overflow-y: auto;">' +
						( url != undefined ?
							'<iframe width="100%" height="100%" sandbox="allow-forms allow-scripts allow-same-origin" src="' + url + '"></iframe>'
							: message
						) +
					'</div>' +
					( footer != undefined ?
						'<div class="modal-footer">' + footer_buttons + '</div>'
						: ''
					) +
				'</div>' +
			'</div>' +
		'</div>'
	);
	
	for(i = 0; i < footer.length; i++){
		if(typeof(footer[i].click) == 'function'){
			jQuery('#' + footer[i].id).click(footer[i].click);
		}
	}
	
	jQuery('#' + id).modal();
	
	if(typeof(options.close) == 'function'){
		jQuery('#' + id).on('hidden.bs.modal', options.close);
	}

	if(typeof(options.size) == 'undefined') options.size = 'default';
	
	if(options.size == 'full'){
		jQuery(window).resize(function(){
			jQuery('#' + id + ' .modal-dialog').width(jQuery(window).width() * 0.95);
			jQuery('#' + id + ' .modal-body').height(jQuery(window).height() * 0.7);
		}).trigger('resize');
	}
	
	return id;
}

function random_string(string_length){
	var text = "";
	var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

	for(var i = 0; i < string_length; i++)
		text += possible.charAt(Math.floor(Math.random() * possible.length));

	return text;
}

