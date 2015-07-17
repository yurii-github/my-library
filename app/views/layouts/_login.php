<?php
use yii\helpers\Url;

?>
<style type="text/css">
#login-form label, #login-form input {
	display: block;
}
#login-form input {
	margin-bottom: 12px;
	width: 95%;
	padding: .4em;
}
.error-list .error { color: red }
</style>
<script type="text/javascript">
$(document).ready(function(e){

	var login_form = $("#login-form");
	var error_list = $('.error-list', login_form);
	 
	login_form.dialog({
		autoOpen: false,
		closeOnEscape: true,
		modal: true,
		open: function(e) {
			var dlg = $(e.target).parent();
			$(".ui-dialog-titlebar", dlg).hide();
			$('.ui-widget-overlay').on('click', function(e) {
				login_form.dialog('close');
			});
		},
		close: function (e) {
			error_list.empty();//clear errors
		},
		buttons: {
			"Login": function(e) {
				error_list.empty();//clear errors
				var form = $('form', login_form);
				var fd = new FormData();
				fd.append('username', $('input#username', form).val());
				fd.append('password', $('input#password', form).val());
				fd.append('remember-me', $('input#remember-me', form).prop('checked'));
				
				$.ajax({
					type: form.attr('method'),
					url: form.attr('action'),
					processData: false,
					contentType: false,
					dataType: 'json',
					data: fd,
					success: function (resp) {
						try {
							if (resp == undefined) {
								throw Error('internal error. object was not set!');
							}
							if(resp.result == true) { //success
								window.location.reload();
							} else { //failed
								error_list.append($('<div>').addClass('error').text(resp.data));
							}
						} catch(e) {
							error_list.append($('<div>').addClass('error').text(e.message));
						}//try-catch
					}
				});
			}
		}
	});

	$("#auth-link").on("click", function(e) {
		e.preventDefault();
		$("#login-form").dialog("open");
	});
});
</script>

<div id="login-form" title="Login Form">
	<form action="<?=Url::to(['site/login'])?>" method="post">
		<input type="text" name="username" id="username" />
		<input type="password" name="password" id="password" />
		<input checked="checked" type="checkbox" name="remember-me" id="remember-me" style="display: inline-block; width: 20px; vertical-align: middle;line-height: 20px"/>
		<label for="remember-me" style="display: inline-block;width:200px; line-height: 20px">Remember Me</label>
	</form>
	<div class="error-list">
	</div>
</div>
