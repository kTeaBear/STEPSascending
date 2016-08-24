<?php
/**
 * data_folder.php - Get the data folder
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
if (!defined('INSTALL_PROCESS')) exit('No direct script access allowed');
$json_data = rawurlencode(json_encode($data));
?>
<form method="post" name="install_form" autocomplete="off">
	<input type="hidden" name="cmd" value="<?php echo CMD_WEB_DATA_FOLDER_HANDLE ?>" />
	<input type="hidden" name="data" value="<?php echo $json_data?>" />
	<fieldset style="width:700px;text-align:left"><legend>STEPS Site Configuration (Step 4 of <?php echo TOTAL_STEPS?>)</legend>
		<font size="-1">It is suggested to leave all the following with the default values.<br />
		Please make sure you know what you are changing if you modify any of the following.<br />
		The STEPS site will not function if they are configured incorrectly.<br /></font>
		<div style="text-align:left;margin-top:20px">This is the web URL address you will access the site:</div>
		<div style="padding-left:20px">
			<label style="text-align:left;width:auto;margin-left:0px;color:red" >Your STEPS Site URL</label>
			<input class="text_box" type="text" name="site_url" value="<?php echo $data->site_url ?>" style="width:350px" />
			<span class="text error" id="area"><?php echo @$error->url_error ?></span>
		</div>
		<div style="text-align:left;padding-top:15px">This is your CGI URL:</div>
		<div style="padding-left:20px">
			<label style="text-align:left;width:auto;margin-left:0px" >CGI-bin URL</label>
			<input class="text_box" type="text" name="cgi_url" value="<?php echo $data->cgi_url ?>" style="width:350px" />
			<span class="text error" id="area"><?php echo @$error->cgi_error ?></span>
		</div>
		<div style="text-align:left;padding-top:15px">This is the folder that stores the application file uploads:</div>
		<div style="padding-left:20px">
			<label style="text-align:left;width:auto;margin-left:0px;color:red" >STEPS Data Folder</label>
			<input class="text_box" type="text" name="data_folder" value="<?php echo $data->folder_path ?>" style="width:350px" />
			<span class="text error" id="area"><?php echo @$error->folder_error ?></span>
		</div>
	</fieldset>

	<div>
		<input type="submit" name="previous" value="Previous" class="btn"
				 onmouseout="if(this.className) this.className='btn';"
				 onmouseover="if(this.className) this.className='btnhov';"
				 onClick="install_form.cmd.value='<?php echo CMD_WEB_ADMIN_SETTINGS?>'; install_form.submit();return false;" />
		<input type="submit" name="next" value="Install Now" class="btn"
				 onmouseout="if(this.className) this.className='btn';"
				 onmouseover="if(this.className) this.className='btnhov';" />
	</div>
</form>
