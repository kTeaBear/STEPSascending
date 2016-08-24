<?php
/**
 * view_admin_user - admin user setup.
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
if (!defined('INSTALL_PROCESS')) exit('No direct script access allowed');
$json_data = rawurlencode(json_encode($data));
?>
<form method="post" name="install_form" autocomplete="off">
	<input type="hidden" name="cmd" value="<?php echo CMD_WEB_ADMIN_SETTINGS_HANDLE?>" />
	<input type="hidden" name="data" value="<?php echo $json_data?>" />
	<fieldset style="width:650px"><legend>Admin Account Configuration (Step 3 of <?php echo TOTAL_STEPS?>)</legend>
	<?php if ( $data->flash_schema_dirty_error ) {?>
		<p style="color:red">Warning, the schema "<?php echo $data->db_schema?>" contains tables from a previous installation.
		They will be deleted if you continue.</p>
	<?php }?>
		<p class="notice" style="margin:20px:text-align:left">This will be your STEPS admin login account.<br />
		 It is suggested to change the default admin username "<?php echo DEFAULT_ADMIN_USERNAME?>" for better security.</p>
		<div style="margin-top:10px; text-align: left;">
			<label class="red_text">STEPS Admin Username</label>
			<input class="text_box" type="text" name="admin_user" value="<?php echo $data->web_admin_username?>" />
			<span class="text error" id="area"><?php echo @$error->user_error ?></span>
		</div>
		<div style="margin-top:10px; text-align: left;">
			<label class="red_text">STEPS Admin Password</label>
			<input class="text_box" type="password" name="admin_password" value="<?php echo $data->web_admin_password ?>"
						 id="admin_password" />
			<span class="text error" id="area"><?php echo @$error->password_error ?></span>
		</div>
		<div style="text-align: left;">
			<label></label>
			<input class="left" type="checkbox" onclick="toggle_password('admin_password',this.checked)"
					 style="background-color:white;" />
			<span class="left" style="font-size: 85%; display: block; margin-top: 2px; margin-left: 2px;">Show password</span>
		</div>

	</fieldset>
	<div>
		<input type="submit" name="previous" value="Previous" class="btn"
				 onmouseout="if(this.className) this.className='btn';"
				 onmouseover="if(this.className) this.className='btnhov';"
				 onClick="install_form.cmd.value='<?php echo CMD_DB_SETTINGS?>'; install_form.submit();return false;" />
		<input type="submit" name="next" value="Next" class="btn"
				 onmouseout="if(this.className) this.className='btn';"
				 onmouseover="if(this.className) this.className='btnhov';" />
	</div>
</form>
