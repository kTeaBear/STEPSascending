<div id="login_form">
	<?php if (isset($info)) { ?>
		<h3><?php echo $info; ?></h3>
	<?php } else { ?>
		<h1 style="text-align: center;">Welcome to STEPS.</h1>
	<?php } ?>
	
	<?php
	echo form_open('account/validate_creds');
	echo form_input('email', set_value('email', 'Email'));
	echo form_password('password', '', 'placeholder="Password" class="password"');
	echo form_submit('submit', 'Login');
	echo anchor('account/register', 'Create Account');
	?>
	
	<?php if (isset($bad_creds)) { ?>
		<p class="error"><?php echo $bad_creds; ?></p>
	<?php } ?>
		
</div>
