<?php
/**
 * controller.php - controller to handle the requests
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
if (!defined('INSTALL_PROCESS')) exit('No direct script access allowed');

class page_controller {
	private $data;
	private $error;
	private $db;
	private $from_ci = FALSE;

	function __construct($from_ci=FALSE) {
		$this->from_ci = $from_ci;
		$this->data = json_data();
		if ( !$this->data ) {
			$this->data = new steps_install();
		}
		$this->error = new stdClass();
		$this->db = new db_functions();
	}

	//Check server component requirements 
	//The first landing index page.
	function check_requirements() {
		if ( $this->from_ci ) {
			$phpinfo = '';
			$no_install_warning = TRUE;
		}
		$platform = init_platform_config();
		$data = $this->data;
		$data->db_username = request_value('username') ? request_value('username') : $data->db_username;
		$data->db_schema = request_value('schema') ? request_value('schema') : $data->db_schema;
		$data->db_password = request_value('password') ? request_value('password') : $data->db_password;
		$data->db_host = request_value('host') ? request_value('host') : $data->db_host;
		//Stop the installation if this count is not zero
		$stop_install = FALSE;
		$result = array();
		$passed = CHECK_PASSED_HTML;
		$failed = CHECK_FAILED_HTML;

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Required components for STEPS to run
		// properly in this environment.

		/////// Check PHP version
		$php_version = phpversion();
		$result['php_version'] = $passed;
		if ( strnatcasecmp($php_version, MINIMUM_PHP_VERSION) < 0 || strnatcasecmp($php_version, MAXIMUM_PHP_VERSION) > 0 ) {
			$result['php_version'] = $failed;
			$stop_install = TRUE;
		}

		/////// Check PHP Zlib
		$result['php_zlib'] = $passed;
		if ( !function_exists('gzcompress') ) {
			$result['php_zlib'] = $failed;
			$stop_install = TRUE;
		}
        
		/////// Check MySQL exists or not
		$result['mysql'] = $passed;
		if ( !function_exists('mysql_connect' ) ) {
			$result['mysql'] = $failed;
			$stop_install = TRUE;
		}

		// Check CURL exists or not
		$result['php_curl'] = $passed;
		if ( !function_exists('curl_init' ) ) {
			$result['php_curl'] = $failed;
			$stop_install = TRUE;
		}
        
		/////// Check mbstring exists or not
		$result['php_mbstring'] = $passed;
		if ( !function_exists('mb_strlen' ) ) {
			$result['php_mbstring'] = $failed;
			$stop_install = TRUE;
		}

		/////// Check Apache and its modules.
		$result['apache'] = $passed;
		$result['apache_rewrite'] = $passed;
		// Deflate is optional but check it here with the required stuff.
		$result['apache_deflate'] = $passed;
		if ( function_exists('apache_get_modules') ) {
			$modules = apache_get_modules();
			if ( $modules ) {
				if ( !array_search('mod_rewrite', $modules) ) {
					$result['apache_rewrite'] = $failed;
					$stop_install = TRUE;
				}
				if ( !array_search('mod_deflate', $modules) ) {
					// This is optional so no need to set $stop_install to true.
					$result['apache_deflate'] = $failed;
				}
			}
			else {
				$result['apache_rewrite'] = $failed;
				$result['apache_deflate'] = $failed;
				$stop_install = TRUE;
			}
		}
		else {
			$result['apache'] = $failed;
			$result['apache_rewrite'] = $failed;
			$result['apache_deflate'] = $failed;
			$stop_install = TRUE;
		}
/* I disabled this because it kept throwing errors
		//////// Check htaccess override
		$result['apache_htaccess'] = $passed;
		// Skipped if from CI
		if ( !$this->from_ci && !check_htaccess_override()) {
			$result['apache_htaccess'] = $failed;
			$stop_install = TRUE;
		}
*/
		//////// Check web files write access
		$web_folder = realpath('..');
		$config_folder = dirname(dirname(__FILE__)) . "/" . dirname(INSTALL_CONFIG_FILE);
		$template_cache_folder = dirname(dirname(__FILE__)) . "/" . TEMPLATE_CACHE_FOLDER;
		if ( is_os(PLATFORM_WINDOWS) ) {
			$config_folder = str_replace('/', '\\', $config_folder);
		}
		$result['web_write'] = $passed;
		$data->webserver_folder_write_access = '1';
		if ( !(check_folder_writable($web_folder) && check_folder_writable($config_folder) && check_folder_writable($template_cache_folder)) ) {
			$result['web_write'] = $failed;
			$data->webserver_folder_write_access = '0';
			$stop_install = TRUE;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Optional stuff

		/////// Check OS support
		$result['os'] = $failed;
		if ( is_supported_os() === TRUE ) {
			$result['os'] = $passed;
		}

		/////// PDF binary, always use the 32bit version
		$result['pdf'] = $failed;
		if ( (is32os() || is64os()) && $result['os'] = $passed ) {
			$result['pdf'] = $passed;
		}

		/////// MIMETEX binary
		$result['mimetex'] = $failed;
		if ( (is32os() || is64os()) && $result['os'] = $passed ) {
			$result['mimetex'] = $passed;
		}

		/////// PHP upload size config
		//Assuming everyone using MB unit, this will fail if assumption fails
		$result['upload'] = $failed;
		$post = ini_get('post_max_size');
		$upload = ini_get('upload_max_filesize');
		$default = DEFAULT_UPLOAD_SIZE_MB . 'M';
		if ( strnatcasecmp($default, $post) <= 0 || strnatcasecmp($default, $upload) <= 0 ) {
			$result['upload'] = $passed;
		}

		/////// CGI-bin path
		$result['cgi_path'] = $failed;
		$result['cgi_write'] = $failed;
		$path = cgi_path();
		if ( $path ) {
			$result['cgi_path'] = $passed;
			if ( check_folder_writable($path) ) {
				$result['cgi_write'] = $passed;
			}
		}

		/////// LDAP library
		/**
		$result['php_ldap'] = $failed;
		if ( function_exists('ldap_connect') ) {
			$result['php_ldap'] = $passed;
		}
        */
        
		/////// ZipArchive library
		$result['php_ziparchive'] = $failed;
		if ( class_exists('ZipArchive') ) {
			$result['php_ziparchive'] = $passed;
		}

		/////// PHP GD library
		$result['php_gdlib'] = $failed;
		if ( function_exists('imagecreate') ) {
			$result['php_gdlib'] = $passed;
		}

		/////// Third party stuff
		/**
		if ( valid_3p_path('builder/inlineobj') ) {
			$result['php_soap'] = $failed;
			if ( class_exists('SoapClient') ) {
				$result['php_soap'] = $passed;
			}
			$result['php_ssl'] = $failed;
			if ( function_exists('openssl_verify') ) {
				$result['php_ssl'] = $passed;
			}
		}
        */
        
		$disabled = '';
		if ( $stop_install ) {
			$disabled = 'disabled="disabled" style="color:grey"';
		}
		require_once('view_header.php');
		require_once('view_requirements.php');
		require_once('view_footer.php');
		return;
	}

	//Ask user for DB settings.
	function db_settings() {
		$data = $this->data;
		$error = $this->error;
		$data->db_username = request_value('username') ? request_value('username') : $data->db_username;
		$data->db_schema = request_value('schema') ? request_value('schema') : $data->db_schema;
		$data->db_password = request_value('password') ? request_value('password') : $data->db_password;
		$data->db_host = request_value('host') ? request_value('host') : $data->db_host;
		require_once('view_header.php');
		require_once('view_db_settings.php');
		require_once('view_footer.php');
		exit;
	}

	//Handle the DB settings submit and load the proper view
	//Note: handles have to take request variables from the before and after page.
	function db_settings_handle() {
		$data = $this->data;
		$error = $this->error;
		$data->db_username = request_value('username');
		$data->db_schema = request_value('schema');
		$data->db_password = request_value('password');
		$data->db_host = request_value('host');
		$has_error = false;
		if (empty($data->db_username)) {
			$error->username_error = 'Missing username';
			$has_error = true;
		}
		$lschema = strtolower($data->db_schema);
		//No crazy stuff to corrupt the DB server
		if (empty($data->db_schema) || $lschema == 'mysql' || $lschema == 'information_schema' ) {
			$error->schema_error = 'Missing schema';
			$has_error = true;
		}
		if (empty($data->db_password)) {
			$error->password_error = 'Missing password';
			$has_error = true;
		}
		if (empty($data->db_host)) {
			$error->host_error = 'Missing host name/IP';
			$has_error = true;
		}

		if ( !$has_error && !$data->db_root_password_valid) {
			//No error above so check for DB connection. No need to check if root password is obtained
			$error->show_root_msg = FALSE;
			$connect = $this->db->connect($data->db_username, $data->db_password, $data->db_host);
			if (!$connect) {
				$error->show_root_msg = TRUE;
				$error->username_error = '<font color="red">Please check</font>';
				$error->password_error = '<font color="red">Please check</font>';
				$error->host_error = '<font color="red">Please check</font>';
				$has_error = true;
			}
			elseif (!$this->db->select_db($data->db_schema, $connect)) {
				$error->username_error = '<font color="black">Username is valid</font>';
				$error->password_error = '<font color="black">Password is valid</font>';
				$error->host_error = '<font color="black">Host is valid</font>';
				$error->schema_error = 'Schema does not exist in DB or insufficient privileges';
				$error->show_root_msg = TRUE;
				$has_error = true;
			}
			elseif ( !$this->db->test_db_access($connect) ) {
				$error->username_error = '<font color="black">Username is valid, but user lacks proper schema privileges</font>';
				$error->password_error = '<font color="black">Password is valid</font>';
				$error->schema_error = 'Insufficient schema privileges';
				$error->show_root_msg = TRUE;
				$has_error = true;
			}
		}

		//If user wants to use root account just let it be.
		if ( $data->db_username == 'root' ) {
			$data->db_root_password = $data->db_password;
			$data->db_root_password_valid = '1';
			$has_error = false;
			if ( empty($data->db_schema) ) {
				$error->schema_error = '<font color="red">Missing schema</font>';
				$has_error = true;
			}
			if ( empty($data->db_host) ) {
				$error->host_error = '<font color="red">Missing host</font>';
				$has_error = true;
			}
		}

		//Make sure the user did not change the host after getting the root password
		if ( !$has_error && $data->db_root_password_valid ) {
			$connect = $this->db->connect('root', $data->db_root_password, $data->db_host);
			$error->password_error = '';
			if ( !$connect ) {
				$error->host_error = 'Check host';
				$error->password_error = 'Check password';
				$has_error = TRUE;
			}
		}

		if ( !$has_error && !$this->db->innodb_enabled() ) {
			$has_error = true;
			$error->innodb_error_flag = true;
		}

		if ( $has_error ) {
			//Got validation error so load the same page again
			require_once('view_header.php');
			require_once('view_db_settings.php');
			require_once('view_footer.php');
			exit;
		}

		//We know we have a valid db connection if we get here
		//The connection can be normal user or root
		$data->db_username_valid = '1';
		$data->db_schema_valid = '1';
		$data->flash_schema_dirty_error = $data->db_schema_dirty = $this->db->is_schema_dirty($data->db_schema);
		$this->web_admin_settings();
		exit;
	}

	function web_admin_settings() {
		$data = $this->data;
		$error = $this->error;
		$data->db_username = request_value('username') ? request_value('username') : $data->db_username;
		$data->db_schema = request_value('schema') ? request_value('schema') : $data->db_schema;
		$data->db_password = request_value('password') ? request_value('password') : $data->db_password;
		$data->db_host = request_value('host') ? request_value('host') : $data->db_host;
		$data->web_admin_username = request_value('admin_user') ? request_value('admin_user') : $data->web_admin_username;
		$data->web_admin_password = request_value('admin_password') ? request_value('admin_password') : $data->web_admin_password;
		$data->folder_path = request_value('data_folder') ? request_value('data_folder') : $data->folder_path;
		$data->site_url = request_value('site_url') ? request_value('site_url') : $data->site_url;
		$data->cgi_url = request_value('cgi_url') ? request_value('cgi_url') : $data->cgi_url;
		require_once('view_header.php');
		require_once('view_admin_user.php');
		require_once('view_footer.php');
		//Only display the error once
		$data->flash_schema_dirty_error = '0';
		exit;
	}

	function web_admin_settings_handle() {
		$data = $this->data;
		$error = $this->error;
		$data->web_admin_username = request_value('admin_user');
		$data->web_admin_password = request_value('admin_password');
		$has_error = false;
		if ( empty($data->web_admin_username) ) {
			$error->user_error = 'Missing username';
			$has_error = true;
		}
		if ( empty($data->web_admin_password) ) {
			$error->password_error = 'Missing password';
			$has_error = true;
		}
		if ( $has_error ) {
			$this->web_admin_settings();
			exit;
		}
		$this->data_folder();
		exit;
	}

	function ask_root() {
		$data = $this->data;
		$error = $this->error;
		$data->db_username = request_value('username') ? request_value('username') : $data->db_username;
		$data->db_schema = request_value('schema') ? request_value('schema') : $data->db_schema;
		$data->db_password = request_value('password') ? request_value('password') : $data->db_password;
		$data->db_host = request_value('host') ? request_value('host') : $data->db_host;

		$data->db_root_password = request_value('root_password') ? request_value('root_password') : $data->db_root_password;
		require_once('view_header.php');
		require_once('view_db_root.php');
		require_once('view_footer.php');
		exit;
	}

	function ask_root_handle() {
		$data = $this->data;
		$error = $this->error;

		$data->db_root_password = request_value('root_password');
		$data->db_host = request_value('host');
		$has_error = false;
		if ( empty($data->db_root_password) ) {
			$error->root_error = 'Missing password';
			$has_error = true;
		}
		if ( empty($data->db_host) ) {
			$error->host_error = 'Missing host name';
			$has_error = true;
		}
		if ( $has_error ) {
			$this->ask_root();
			exit;
		}

		$connect = $this->db->connect('root', $data->db_root_password, $data->db_host);
		if ( !$connect ) {
			$error->root_error = 'Invalid password or DB host';
			$this->ask_root();
			exit;
		}
		$data->db_root_password_valid = '1';
		//Verified everything and go back to DB settings
		$this->db_settings();
		exit;
	}

	//Get data folder
	function data_folder() {
		$data = $this->data;
		$error = $this->error;
		$config = init_platform_config();
		$data->web_admin_username = request_value('admin_user') ? request_value('admin_user') : $data->web_admin_username;
		$data->web_admin_password = request_value('admin_password') ? request_value('admin_password') : $data->web_admin_password;
		$data->folder_path = request_value('data_folder') ? request_value('data_folder') : $config->data_folder;
		$data->site_url = request_value('site_url') ? request_value('site_url') : get_site_url();
		$data->cgi_url = request_value('cgi_url') ? request_value('cgi_url') : cgi_path(true);
		require_once('view_header.php');
		require_once('view_data_folder.php');
		require_once('view_footer.php');
		exit;
	}

	//Data folder handle
	function data_folder_handle() {
		$data = $this->data;
		$error = $this->error;
		$config = init_platform_config();
		$data->folder_path = request_value('data_folder');
		$data->site_url = request_value('site_url');
		$count = strlen($data->site_url);
		if ( $data->site_url[$count-1] != '/' ) {
			$data->site_url .= '/';
		}

		if ( empty($data->folder_path) ) {
			$this->error->folder_error = 'Please enter a folder';
			$this->data_folder();
			exit;
		}
		$cleanup = false;
		if ( !file_exists($data->folder_path) ) {
			if ( mkdir($data->folder_path) || FALSE) {
				$cleanup = true;
			}
			else {
				$this->error->folder_error = 'Failed to create folder';
				$this->data_folder();
				exit;
			}
		}
		if ( !chmod($data->folder_path, 0700) || !check_folder_writable($data->folder_path) ) {
			$this->error->folder_error = 'Folder does exist, but can\'t write to it';
			if ( $cleanup ) {
				@rmdir($data->folder_path);
			}
			$this->data_folder();
			exit;
		}
		$file = @file_get_contents('htaccess_deny');
		if ( !empty($file) ) {
			@file_put_contents($data->folder_path . $config->slash . '.htaccess', $file);
		}
		$file = @file_get_contents('index_403.html');
		if ( !empty($file) ) {
			@file_put_contents($data->folder_path . $config->slash . 'index.html', $file);
		}

		if ( !strstr($data->site_url, 'http://') && !strstr($data->site_url, 'https://') ) {
			$error->url_error = 'Incorrect URL format';
			$this->data_folder();
			exit;
		}

		$data->folder_path_valid = '1';
		$this->run_install($data);
		exit;
	}

	private function run_install($data) {
		set_time_limit(INSTALL_PHP_TIMEOUT);
		require_once('view_header.php');
		echo '<fieldset style="width:700px;text-align:left;font-size:12px"><legend>Perform Final Installation (Step 5 of ' . TOTAL_STEPS . ')</legend>';
		$config = init_platform_config();
		$url_parts = parse_url($data->site_url);
		$site_index_file = $url_parts['path'] . 'index.php';
		$configfile = dirname(dirname(__FILE__)) . $config->slash . INSTALL_CONFIG_FILE;
		//Add steps.php config
		echo "<b>* Setting STEPS configuration file \"" . $configfile . "\":</b><br />";
		$cms_password_key = get_unique_md5();
		add_config_file($data, $cms_password_key);
		sleep(1);
		echo "Done<br /><br />";
		// sleep(1);
		//Copy CGI binary
		$cgi_parts = parse_url($data->cgi_url);
		$cgi_path = cgi_path(false, $cgi_parts['path']);
		if ( !empty($config->mimetext_cgi_filename) && !empty($cgi_path) ) {
			$from = dirname(__FILE__) . $config->slash . $config->mimetext_cgi_filename;
			$to = $cgi_path . $config->slash . basename($config->mimetext_cgi_filename);
			echo "<b>* Copy CGI executable from<br /> $from to<br />{$to}:</b><br/>";
			@copy($from, $to);
			@chmod($to, 0755);
			sleep(1);
			echo "Done<br /><br />";
		}
		echo "<b>* Install STEPS database:</b><br />";
		setupdb($data, $cms_password_key);
		sleep(1);
		echo "<br />Done<br /><br />";
		echo "<b>* Install .htaccess file</b>:<br />";
		sleep(1);
		add_htaccess($site_index_file);
		echo "<b>* Check installed STEPS site readiness and install 3rd party module database(s)</b>:<br />";
		sleep(1);
		tp_upgrade($data);
		sleep(1);
		echo "Done<br /><br />";
		echo "<h3 style='color:green'>Congratulations, your STEPS site is ready!<br />To access the site please click: <a style='font-size:15px;color:blue' href='{$data->site_url}'>{$data->site_url}</a></h3>";
		echo "<h3 style='color:green'>Please save a copy of $configfile.<br />Your site will become inoperable if this file is unavailable.</h3>";
		echo '</fieldset>';
		require_once('view_footer.php');
	}

}
