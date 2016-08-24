<div id="register_form">
	<h1>Create an Account</h1>
	<?php
	echo form_open('account/create_user');
	echo form_input('first_name', set_value('first_name', 'First Name'));
	echo form_input('last_name', set_value('last_name', 'Last Name'));
	echo form_input('email', set_value('email', 'Email Address'));
	echo form_password('password', '', 'placeholder="Password" class="password"');
	echo form_password('password_confirm', '', 'placeholder="Confirm Password" class="password"');
	echo form_submit('submit', 'Create Account');
	?>
	<?php echo validation_errors('<p class="error">'); ?>
</div>
