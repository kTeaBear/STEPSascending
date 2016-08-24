<?php
/**
 * view_db_settings.php - Page to ask for DB username and password
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
if (!defined('INSTALL_PROCESS')) exit('No direct script access allowed');
$json_data = rawurlencode(json_encode($data));
$mydb = new db_functions();
?>

<form method="post" name="install_form" autocomplete="off">
	<input type="hidden" name="cmd" value="<?php echo CMD_DB_SETTINGS_HANDLE?>" />
	<input type="hidden" name="data" value="<?php echo $json_data?>" />
	<fieldset style="width:600px"><legend>MySQL Database Settings (Step 2 of <?php echo TOTAL_STEPS?>)</legend>
	<?php if ($mydb->has_anonymous_user()) { ?>
	<p style="color:red;text-align:left">Warning, MySQL anonymous user exists. It is recommended to remove the anonymous user if it is not used.
		The anonymous user account can cause the TODCM installation to fail if you are using an existing MySQL user account and this account's
		host setting is not set properly. Please disregard this warning if you are installing on a separate DB server.</p>
	<?php } ?>
	<?php if ( $data->db_root_password_valid ) { ?>
	<p style="color:red">MySQL root access obtained. Please continue...</p>
	<?php } ?>
		<p class="notice" style="margin:10px;font-size:12px">This can be an existing MySQL account or a new account to be created:</p>

		<div style="text-align: left;">
			<label class="red_text">DB Username:</label>
			<input class="text_box" type="text" name="username" value="<?php echo @$data->db_username?>" />
			<span id="area" class="error" ><?php echo @$error->username_error?></span>
		</div>
		<div style="text-align: left;">
			<label class="red_text">DB Password:</label>
			<input class="text_box" type="password" id="password" name="password"
					 value="<?php echo @$data->db_password?>" />
			<span class="error" id="area"><?php echo @$error->password_error?></span></div>
		<div style="text-align: left;">
			<label></label>
			<input class="left" type="checkbox" onclick="return toggle_password('password', this.checked)"
					 style="background-color:white;" />
			<span class="left" style="font-size: 85%; display: block; margin-top: 2px; margin-left: 2px;">Show password</span>
		</div>
		<div style="text-align: left;">
			<label class="red_text">DB Schema Name:</label>
			<input class="text_box" type="text" name="schema" value="<?php echo @$data->db_schema?>" />
			<span class="error" id="area"><?php echo @$error->schema_error?></span>
		</div>
		<div style="text-align: left;">
			<label class="red_text">DB Host:</label>
			<input class="text_box_long" type="text" name="host" value="<?php echo @$data->db_host?>" />
			<span class="error" id="area"><?php echo @$error->host_error?></span>
		</div>
		<div style="text-align: left;">
			<span class="error" id="area" style="font-size:14px;"><?php echo @$error->error?></span>
		</div>

		<?php if (isset($error->show_root_msg) && $error->show_root_msg) { ?>
			<div>
				<span class="error" id="area" style="font-size:14px;">
					Failed to connect database. Please correct your settings and try again.
					<br/><br/>OR<br /><br />
					If you have the MySQL root password and you would like to create a new database schema and/or a new
					database user account then please<br /><br />
					<a href="" onClick="install_form.cmd.value='<?php echo CMD_DB_ASKROOT?>';install_form.submit();return false" style="font-size:14px;" >click here</a>
				</span>
			</div>
		<?php } ?>

		<?php if (isset($error->innodb_error_flag) && $error->innodb_error_flag) { ?>
			<div>
				<span class="error" id="area" style="font-size:14px;font-weight:bold">
					The supplied database settings are working 100%, but
					InnoDB support is not enabled in your MySQL server.<br /><br />
					The installation can not be continued without InnoDB support.
					Please enable InnoDB support in your MySQL server before moving to the next installation step.<br /><br />
					The InnoDB support can be enabled by editing your MySQL configuration file (my.cnf or my.ini) or using a MySQL administrative tool such as
					MySQL Workbench etc. You will need to restart your MySQL server after enabling InnoDB support.
				</span>
			</div>
		<?php } ?>

		<div style="text-align:left;font-size:10px;padding-top:20px">
			If you create the database/schema yourself then please make sure it is created with "utf8" character set
			and "utf8_general_ci" collation if you plan to store data in foreign languages in addition to English.
			If you are not sure about the database/schema creation then please do not create the database/schema yourself
			and supply the MySQL root password so the installation can create the database/schema and assign the proper character set and collation.
			You will be prompted to enter the MySQL root password in the next step if the supplied database/schema or username doesn't exist.
		</div>
	</fieldset>

	<div style="margin-top:30px">
		<input type="submit" name="previous" value="Previous" class="btn"
						 onmouseout="if(this.className) this.className='btn';"
						 onmouseover="if(this.className) this.className='btnhov';"
						 onClick="install_form.cmd.value='<?php echo CMD_CHECK_REQUIREMENTS?>'; install_form.submit();return false;" />
		<input type="submit" name="next" value="Next" class="btn"
				 onmouseout="if(this.className) this.className='btn';"
				 onmouseover="if(this.className) this.className='btnhov';" />
	</div>
</form>

