<?php
/**
 * view_db_root - Ask for root password to create DB
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
if (!defined('INSTALL_PROCESS')) exit('No direct script access allowed');
$json_data = rawurlencode(json_encode($data));
?>
<form method="post" name="install_form" autocomplete="off">
	<input type="hidden" name="cmd" value="<?php echo CMD_DB_ASKROOT_HANDLE?>" />
	<input type="hidden" name="data" value="<?php echo $json_data?>" />
	<fieldset style="width:600px"><legend>MySQL Root Password (Step 2.1 of <?php echo TOTAL_STEPS?>)</legend>
			<p style="text-align:left;margin:10px" class="notice">Root password is required to create new MySQL account and/or database schema. The STEPS site will not store this root password after the installation is complete.</p>
			<div>
				<label>DB Host:</label>
				<input class="text_box" type="text" name="host" value="<?php echo @$data->db_host?>" />
				<span class="text error" id="area"><?php echo @$error->host_error?></span>
			</div>
			<div style="margin-bottom:20px">
				<label>MySQL Root Password</label>
				<input class="text_box" type="password" name="root_password" id="root_password"
						 value="<?php echo @$data->db_rootpasswd ?>" />
				<span class="text error" id="area"><?php echo @$error->root_error ?></span>
			</div>
			<div>
				<label></label>
				<input class="left" type="checkbox" onclick="toggle_password('root_password',this.checked)"
						 style="background-color:white;" />
				<span class="left" style="font-size: 85%; display: block; margin-top: 2px; margin-left: 2px;">Show password</span>
			</div>
			<div style="padding-top:10px;text-align:left;font-size:12px;">
			<p style="color:red">Warning, the installation will use the supplied MySQL root password to:</p>
			<p>1. If the previously supplied MySQL account does not exist:<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&bull; Create the user account.<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&bull; Grant all required database permissions to this new user account.<br />
			</p>

			<p>2. If the previously supplied MySQL account does already exist then its password will be reset
			using the supplied password from the previous page.</p>

			<p>3. If the database schema does not exist then a new schema will be created.</p>

			<p style="color:red">4. If the previously supplied database schema does exist and contains TODCM tables form a
			previous installation then all these tables will be deleted and will be recreated by this installation.
			Please abort this installation now if you have old data you need to save.</p>
			</div>
		</fieldset><br />
		<input type="submit" name="previous" value="Previous" class="btn"
						 onmouseout="if(this.className) this.className='btn';"
						 onmouseover="if(this.className) this.className='btnhov';"
						 onClick="install_form.cmd.value='<?php echo CMD_DB_SETTINGS?>'; install_form.submit();return false;" />
		<input type="submit" name="next" value="Next" class="btn"
						  onmouseout="if(this.className) this.className='btn';"
						  onmouseover="if(this.className) this.className='btnhov';" />
</form>
