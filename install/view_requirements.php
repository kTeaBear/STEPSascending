<?php
/**
 * view_requirements.php - Failed requirements
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
if (!defined('INSTALL_PROCESS')) exit('No direct script access allowed');
$json_data = rawurlencode(json_encode($data));

if ( !isset($phpinfo) ) {
	$phpinfo = ', <a target="install_phpinfo" href=index.php?cmd=10>Click to see PHP Info</a>';
}

$td_style = 'style="text-align:left"';
$count = 1;
?>
<form method="post" name="install_form">
	<input type="hidden" name="cmd" value="<?php echo CMD_DB_SETTINGS?>" />
	<input type="hidden" name="data" value="<?php echo $json_data?>" />
<center>
<div>
<fieldset style="width:700px;text-align:left;margin:20px">
 <legend>Installation Requirements Checklist (Step 1 of <?php echo TOTAL_STEPS?>)</legend>
 <div style="margin-left:50px">
 <?php if ( is_cgi_mode() ) {?>
 <p style="color:red;font-size:12px;text-align:left;">
Your web server is running PHP in CGI mode (most shared-hosting environments use CGI) and it is not officially supported by STEPS.
All Apache and CGI-bin related checks on this page fail in CGI mode. You can continue the installation with the mentioned check failures
but step 3 "Apache Htaccess Override" must be valid.
The .htaccess file will be modified to accommodate your CGI environment but your new STEPS site is not guaranteed to work.
The final step of the installation can take 1-2 minutes to finish and there will be no real-time status update due to CGI limitation.
The real-time PDF generation feature will not work due to missing libraries in a restrictive shared-hosting environment and the
math formula image generation feature will not work due to the failed CGI-bin MIMETEX check.
</p>
<?php }?>
<!-- DATA_TABLE_BEGIN -->
<b style="font-size:15px">Components required to run the STEPS application properly:</b><br />
<table width="600" border="1" cellspacing="1" cellpadding="10">
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. Apache Web Server
			<?php
				if ($result['apache'] == CHECK_FAILED_HTML) {
					echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;<font color="red">Only the Apache web server is supported.
							This and all other Apache related checks will fail if you are running PHP as a CGI.</font>';
				}
			?>
		</td>
		<td><?php echo $result['apache']?></td>
	</tr>
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. Apache Rewrite Module
			<?php
				if ($result['apache_rewrite'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">
						Please turn on the Apache rewrite module by adding or uncommenting the
						following line to the Apache configuration file (make sure to restart Apache afterward):<br />
						<div class="req_failed">LoadModule rewrite_module modules/mod_rewrite.so</div>
						</div>';
				}
			?>
		</td>
		 <td><?php echo $result['apache_rewrite']?></td></tr>

	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. Apache Htaccess Override
			<?php
				if ($result['apache_htaccess'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">
						Please edit your Apache config file and change "AllowOverride None" to "AllowOverride All".
						Restarting your Apache server is required after this change. This can fail if
						"Website Folder Write Access" check fails or you are using Apache virtual hosts with
						a non-working DNS configuration on your web server.
						</div>';
				}
			?>
		</td>
		 <td><?php echo $result['apache_htaccess']?></td>
	</tr>

	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. Website Folder "<?php echo $web_folder?>" Write Access
			<?php
				if ($result['web_write'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">Please make folder ' . $web_folder . ' and all its sub-folders writable for the Apache server process. ' .
						  "For Linux, run \"chown -R apache {$web_folder}\" (assumes user apache owns the httpd processes) as the Linux root user.</div>";
				}
			?>
		</td>
		<td><?php echo $result['web_write']?></td>
	</tr>

	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. PHP Version (5.2.x - 5.4.x are supported. 5.1.x should work with additional PHP JSON extension)
			<?php
				if ($result['php_version'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">You are running PHP version ' . phpversion() . "${phpinfo}.</div>";
				}
			?>
		</td>
		<td><?php echo $result['php_version']?></td>
	</tr>
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. PHP MySQL Support
			<?php
				if ($result['mysql'] == CHECK_FAILED_HTML) {
					echo "<div class=\"req_failed\">MySQL extension is missing. Please install the PHP MySQL extension${phpinfo}. " .
						  "You might need to set extension_dir=\"FULL_EXTENSION_PATH\" (replace FULL_EXTENSION_PATH with actual path such as c:/windows/php/ext) or add/uncomment extension=php_mysql.dll in your php configuration file</div>";
				}
			?>
		</td>
		<td><?php echo $result['mysql']?></td>
	</tr>

	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. PHP Zlib Support
			<?php
				if ($result['php_zlib'] == CHECK_FAILED_HTML) {
					echo "<div class=\"req_failed\">PHP Zlib support is not enabled${phpinfo}. You need a version of PHP that supports Zlib. </div>";
				}
			?>
		</td>
		<td><?php echo $result['php_zlib']?></td>
	</tr>

	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. PHP CURL Support
			<?php
				if ($result['php_curl'] == CHECK_FAILED_HTML) {
					echo "<div class=\"req_failed\">PHP CURL support is not enabled${phpinfo}. You need a version of PHP that supports CURL. </div>";
				}
			?>
		</td>
		<td><?php echo $result['php_curl']?></td>
	</tr>

	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. PHP Mbstring Support
			<?php
				if ($result['php_mbstring'] == CHECK_FAILED_HTML) {
					echo "<div class=\"req_failed\">PHP Mbstring support is not enabled${phpinfo}. You need a version of PHP that supports Mbstring. </div>";
				}
			?>
		</td>
		<td><?php echo $result['php_mbstring']?></td>
	</tr>

	</table>
	<?php if ( !isset($no_install_warning) || !$no_install_warning ) { ?>
		<font size="-2">The installation can not be continued if any one of the above fails.</font>
	<?php } ?>
	<div style="padding:1px"> &nbsp; </div>

	<b style="font-size:15px">Optional Components:</b><br />
	<table width="600" border="1" cellspacing="1" cellpadding="10">
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. OS Support (Linux, Mac OS-X and Windows)
			<?php
				 if ($result['os'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">Your Operating System: ' . php_uname('s') . '</div>';
				}
			?>
		</td>
		<td><?php echo $result['os']?></td>
	</tr>
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. WKHTMLTOPDF Binary Support (PDF generator, OS dependent)
			<?php
				 if ($result['pdf'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">Sorry, we have to disable PDF generation because your OS ('  . php_uname('m') . ') is not supported.</div>';
				}
			?>
		</td>
		<td><?php echo $result['pdf']?></td>
	</tr>
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. MIMETEX Binary Support (Math formula image generator, OS dependent)
			<?php
				 if ($result['mimetex'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">Sorry, we have to disable math formula image generation because your OS ('  . php_uname('m') . ') is not supported.</div>';
				}
			?>
		</td>
		<td><?php echo $result['mimetex']?></td>
	</tr>
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. Apache Deflate/Compression Module (Makes pages load faster from internet)
			<?php
				 if ($result['apache_deflate'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">Please add or uncomment the following line to the Apache configuration file:
							<div class="req_failed">LoadModule deflate_module modules/mod_deflate.so</div></div>';
				}
			?>
		</td>
		<td><?php echo $result['apache_deflate']?></td>
	</tr>
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. CGI-bin Support (For MIMETEX)
			<?php
				 if ($result['cgi_path'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">Please check your Apache configuration and turn on CGI support.</div>';
				}
			?>
		</td>
		<td><?php echo $result['cgi_path']?></td>
	</tr>
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. CGI-bin Folder <?php if ($path) echo "\"$path\""?> Write Access (For copying the MIMETEX binary)
		</td>
		<td><?php echo $result['cgi_write']?></td>
	</tr>
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. PHP Config File - Upload Size (Minimum <?php echo DEFAULT_UPLOAD_SIZE_MB?> MB recommended)
			<?php
				 if ($result['upload'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">Your upload size will be limited by the smaller of these 2 settings in your PHP config file:' .
						  '<div class="req_failed">post_max_size = ' . ini_get('post_max_size') . '</div>' .
						  '<div class="req_failed">upload_max_filesize = ' . ini_get('upload_max_filesize') . '</div>' .
						  '</div>';
				}
			?>
		</td>
		<td><?php echo $result['upload']?></td>
	</tr>
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. PHP ZipArchive Library
			<?php
				 if ($result['php_ziparchive'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">The ZipArchive library is used for STEPS professional version only and not required for the open source version. ' .
						  'This is required for the zip file bulk download function on the unit page, course description page and site resource page.</div>';
				}
			?>
		</td>
		<td><?php echo $result['php_ziparchive']?></td>
	</tr>
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. PHP GD Library
			<?php
				 if ($result['php_gdlib'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">The graphing feature is disabled for the popup editor (TinyMCE).</div>';
				}
			?>
		</td>
		<td><?php echo $result['php_gdlib']?></td>
	</tr>

	<?php if (isset($result['php_soap'])) { ?>
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. PHP SOAP Library
			<?php
				 if ($result['php_soap'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">PHP SOAP library is needed to access the LiveDocx.com web service.</div>';
				}
			?>
		</td>
		<td><?php echo $result['php_soap']?></td>
	</tr>
	<?php } ?>

	<?php if (isset($result['php_ssl'])) { ?>
	<tr>
		<td <?php echo $td_style?>>
			<?php echo $count++?>. PHP Open SSL Library
			<?php
				 if ($result['php_ssl'] == CHECK_FAILED_HTML) {
					echo '<div class="req_failed">PHP Open SSL library is needed to access the LiveDocx.com web service.</div>';
				}
			?>
		</td>
		<td><?php echo $result['php_soap']?></td>
	</tr>
	<?php } ?>
</table>
<!-- DATA_TABLE_END -->
</div>
<div style="margin-top:10px">
	 <?php if ( !empty($disabled) ) { ?>
		 <div style="color:red;font-size:12px;padding:8px;font-weight:bold">
		 Please fix the failed required components.<br/>Your site will not be operational if you continue.
		 </div>
	 <?php } ?>
	<center> 
	<input type="submit" name="recheck" value="Recheck" class="btn"
			 onmouseout="if(this.className) this.className='btn';"
			 onmouseover="if(this.className) this.className='btnhov';"
			 onClick="install_form.cmd.value='<?php echo CMD_CHECK_REQUIREMENTS?>'; install_form.submit();return false;" />
	<input <?php echo $disabled?> type="submit" name="next" value="Next" class="btn"
			 onmouseout="if(this.className) this.className='btn';"
			 onmouseover="if(this.className) this.className='btnhov';" />
	</center>	 
	<?php if ( !empty($disabled) ) { ?>
		<input type="submit" name="continue" value="Continue Anyway" class="btn"
			 onmouseout="if(this.className) this.className='btn';"
			 onmouseover="if(this.className) this.className='btnhov';" />
	<?php } ?>
</div>
</fieldset>
</div>
</center>
</form>
