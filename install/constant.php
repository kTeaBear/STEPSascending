<?php
/**
 * constant - All constant values.
 *     NOTE: Most of these settings are similar to that of the TODCM
 *           install so thank those developers for doing the leg work.
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
if (!defined('INSTALL_PROCESS')) exit('No direct script access allowed');

//Check for previous installation, relative to the install folder
define('INSTALL_CHECK_FILE', '../config.php');
//main config file, relative to the top website folder, put it here so users acan find it easy
define('INSTALL_CONFIG_FILE', 'config.php');
define('MINIMUM_PHP_VERSION', '5.2.0');
define('MAXIMUM_PHP_VERSION', '6.0.0');
define('TOTAL_STEPS', 5);
//Default values
define('DEFAULT_DB_SCHEMA', 'steps');
define('DEFAULT_DB_USERNAME', 'steps');
define('DEFAULT_DB_HOST', 'localhost');
define('DEFAULT_ADMIN_USERNAME', 'admin');
define('DEFAULT_DATA_FOLDERNAME', 'steps_data');
define('DEFAULT_UPLOAD_SIZE_MB', 8);

//OS strings from php_uname()
define('PLATFORM_WINDOWS', 'windows');
define('PLATFORM_LINUX', 'linux');
define('PLATFORM_MAC', 'darwin');
global $g_supported_os;
$g_supported_os = array(PLATFORM_LINUX,PLATFORM_MAC,PLATFORM_WINDOWS);

//URL commands
define('CMD_DB_SETTINGS', 1);
define('CMD_DB_SETTINGS_HANDLE', 2);
define('CMD_DB_ASKROOT', 3);
define('CMD_DB_ASKROOT_HANDLE', 4);
define('CMD_WEB_ADMIN_SETTINGS', 5);
define('CMD_WEB_ADMIN_SETTINGS_HANDLE', 6);
define('CMD_WEB_DATA_FOLDER', 7);
define('CMD_WEB_DATA_FOLDER_HANDLE', 8);
define('CMD_CHECK_REQUIREMENTS', 9);
define('CMD_PHPINFO', 10);

define('CHECK_PASSED_HTML', '<font color="green">Passed</font>');
define('CHECK_FAILED_HTML', '<font color="red" size="+1"><b>Failed</b></font>');

define('INSTALL_PHP_TIMEOUT', 600);
define('TEMPLATE_CACHE_FOLDER', 'application/libraries/cms/cms_template');
define('DB_SCRIPT_FILE', 'db_init.sql');
define('FORCE_OS_FILE', 'force.os');
define('FORCE_ARCH_FILE', 'force.arch');
define('MYSQL_CHARSET', 'utf8');
define('MYSQL_COLLATE', 'utf8_general_ci');
// This is for a safe check to make sure we don't go into an infinite loop while installing 3p app DBs.
// 30 is a high number but you will need to increase this when there are many 3p apps.
define('MAX_3P_APPLICATIONS', 30);

//Use for storing data between requests
class steps_install {
	public $db_password = '';
	public $db_username = DEFAULT_DB_USERNAME;
	public $db_username_valid = '0';
	public $db_schema = DEFAULT_DB_SCHEMA;
	public $db_schema_valid = '0';
	public $db_schema_dirty = '0';
	public $flash_schema_dirty_error = '0';
	public $db_host = DEFAULT_DB_HOST;
	public $db_root_password = '';
	public $db_root_password_valid = '0';
	public $web_admin_username = DEFAULT_ADMIN_USERNAME;
	public $web_admin_password = '';
	public $folder_path = '';
	public $folder_path_valid = '0';
	public $webserver_folder_write_access = '0';
	public $site_url = '';
	public $cgi_url = '';
}

//Options for various hardware platforms, Linux, Mac etc.
class platform_config {
	public $pdf_option='';
	public $pdf_exe_filename='';
	public $mimetext_cgi_filename='';
	public $slash='';
	public $data_folder='';
}
