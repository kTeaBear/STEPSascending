<?php
/**
 * lib.php - Library functions
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
if (!defined('INSTALL_PROCESS')) exit('No direct script access allowed');
// There are some undefined CI constant references in cms_constant.php so need the @
//@require_once('../system/application/config/cms_constant.php');
require_once('../application/helpers/osautil_helper.php');

//Check .htaccess for app install status
function is_installed() {
	if (file_exists(INSTALL_CHECK_FILE)) {
		return true;
	}
	else {
		return false;
	}
}

//Match OS string
function is_os($os) {
	if ( file_exists(FORCE_OS_FILE)) {
		$force_os = file_get_contents(FORCE_OS_FILE);
		if ($force_os == $os) {
			return true;
		}
		else {
			return false;
		}
	}
	if (strstr(strtolower(php_uname('s')), $os)) {
		return true;
	}
	else {
		return false;
	}
}

//Returns true if currect OS is supported else returns the current os name
function is_supported_os() {
	global $g_supported_os;
	foreach ($g_supported_os as $os) {
		if ( is_os($os) ) {
			return true;
		}
		return php_uname('s');
	}
}

//Check force install
//Not really used any more
function is_force_install() {
	if ( array_key_exists('force', $_REQUEST) ) {
		return true;
	}
	return false;
}

function request_value($index) {
	if ( array_key_exists($index, $_REQUEST) ) {
		return trim($_REQUEST[$index]);
	}
	return false;
}

//Get the json data from the form submit
function json_data() {
	if ( !function_exists('json_decode') ) {
		echo '<br /><br /><h2>PHP JSON extension missing. <br />
				Your PHP version is 5.1 or lower and not supported by STEPS.<br />
			   Please upgrade your PHP to version 5.2 or higher.</h2>';
		exit;
	}
	if ( array_key_exists('data', $_REQUEST) ) {
		$json = rawurldecode($_REQUEST['data']);
		return json_decode($json);
	}
	return false;
}

//Check previous install
function check_installed() {
	if ( is_installed() && !is_force_install() ) {
		require_once('view_header.php');
		require_once('view_installed.php');
		require_once('view_footer.php');
		exit;
	}
}

//Function to get the real path to the cgi-bin folder. Almost impossible to get the cgi path, this is like a hack!
function cgi_path($return_url_path=false, $user_uri=false) {
	if ( !function_exists('apache_lookup_uri') ) {
		return false;
	}
	$win_path_format = is_os(PLATFORM_WINDOWS);
	//Try to guess the cgi url. It is configurable and can be anything :( ...
	//99.999999999% works on the 1st and 2nd one, the rest is just a wild guess...
	$cgi_urls = array('/cgi-bin', '/cgi', '/cgibin', '/apache/cgi-bin', '/apache/cgi', '/apache/cgibin',
							'/bin', '/apache/bin', '/perl', '/apache/perl', '/pl', '/apache/pl', '/cgi_bin',
							'/apache/cgi_bin', '/exe', '/apache/exe', '/script', '/apache/script',
							'/scripts', '/apache/scripts', '/binary', '/apache/binary');
	if ( $user_uri ) {
		$cgi_urls = array($user_uri);
	}
	foreach ($cgi_urls as $url) {
		/*
			This looks up the server URI and gives all kinds of stat including the cgi info needed here.
			The problem is that this will not work if we have a non-standard cgi url other than /cgi-bin etc......
			Like to add an option in the install for the user to supply the non-standard cgi url
			but i think that is getting a little too much...
		*/
		$stat = apache_lookup_uri("{$url}/test");
		if ( (is_object($stat) ) && (property_exists($stat, 'handler') && property_exists($stat, 'filename') &&
			  strtolower($stat->handler) == 'cgi-script') && (strlen($stat->filename) > 6) ) {
		   if ( $return_url_path ) {
				return 'http://' . $_SERVER['SERVER_NAME'] . "$url/";
			}
			$real_cgi_path = dirname($stat->filename);
			if ( strlen($real_cgi_path) > 2 ) {
				if (!$win_path_format) {
					return $real_cgi_path;
				}
				return(str_replace('/','\\',$real_cgi_path));
			}
		}
	}
	return false;
}

function isarch($str) {
	$types = array(strtolower(php_uname('m')), strtolower(php_uname('p')));
	foreach ($types as $type) {
		if (strstr($type, $str)) {
			return true;
		}
	}
	return false;

}

function is32os() {
	if ( file_exists(FORCE_ARCH_FILE)) {
		$arch = file_get_contents(FORCE_ARCH_FILE);
		if ($arch == 'i386') {
			return true;
		}
		else {
			return false;
		}
	}
	return ( isarch('i386') || isarch('i486') || isarch('i586') || isarch('i686') || isarch('i786'));
}

function is64os() {
if ( file_exists(FORCE_ARCH_FILE)) {
		$arch = file_get_contents(FORCE_ARCH_FILE);
		if ($arch == 'amd64') {
			return true;
		}
		else {
			return false;
		}
	}
	return ( isarch('86_64') || isarch('86-64') || isarch('amd64') );
}

//Inits. the platform config class properties
// ./mimetex.cgi: Bad CPU type in executable - Mac 32-bit error
function init_platform_config() {
	$config = new platform_config();
	//dirname() returns without ending slash
	$install_dir = dirname(__FILE__);
	$web_root = dirname($install_dir);

	if ( is_os(PLATFORM_WINDOWS) ) {
		$config->slash = '\\';
		$config->pdf_option = '--zoom 1.4';
		$config->pdf_exe_filename = 'binary\pdf\win\wkhtmltopdf_i386.exe';
		$config->mimetext_cgi_filename = 'binary\mimetex\win\mimetex_i386.exe';
	}
	elseif ( is_os(PLATFORM_MAC) ) {
		$config->slash = '/';
		$config->pdf_option = '';
		$config->pdf_exe_filename = 'binary/pdf/mac/wkhtmltopdf_mac_i386';
		$config->mimetext_cgi_filename = 'binary/mimetex/mac/mimetex_mac_i386.cgi';
		if ( is64os() ) {
			$config->mimetext_cgi_filename = 'binary/mimetex/mac/mimetex_mac_amd64.cgi';
		}
	}
	elseif (is_os(PLATFORM_LINUX)) {
		$config->slash = '/';
		$config->pdf_option = '';
		$config->pdf_exe_filename = 'binary/pdf/linux/wkhtmltopdf_linux_i386';
		$config->mimetext_cgi_filename = 'binary/mimetex/linux/mimetex_linux_i386.cgi';
		if ( is64os() ) {
			$config->pdf_exe_filename = 'binary/pdf/linux/wkhtmltopdf_linux_amd64';
			$config->mimetext_cgi_filename = 'binary/mimetex/linux/mimetex_linux_amd64.cgi';
		}
	}
	else {
		$config->slash = '/';
		$config->pdf_option = '';
		$config->pdf_exe_filename = '';
		$config->mimetext_cgi_filename = '';
	}
	$config->data_folder = $web_root . $config->slash . DEFAULT_DATA_FOLDERNAME ;
	return $config;
}

//is_writable does not always work so use this function
//If you use this function on a folder then you can use is_writable for files in that folder.
function check_folder_writable($folder) {
	$filename = '.steps_test_file';
	$config = init_platform_config();
	$filename = $folder . $config->slash . $filename;
	if (!file_exists($folder))  {
		return false;
	}
	$ret = @file_put_contents($filename, 'STEPS write test dummy file, please delete me.');
	if ( !$ret ) {
		return false;
	}
	@unlink($filename);
	return true;
}

function get_site_url() {
	$url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	$count = 0;
	while ( true ) {
		$filename = strtolower(basename($url));
		if ( $filename == 'install' ) {
			return dirname($url) . '/';
		}
		$url = dirname($url);
		if ( $count++ > 10 ) {
			break;
		}
	}
	return '';
}

//At this stage we should have everything we need for the DB
function setupdb($data, $cms_password_key) {
	$db = new db_functions();
	//Recheck everything again and not trust the $data info
	$valid_user = false;
	$schema_exists = false;
	$schema_access = false;
	//Connect using the account first
	if ( $connect = $db->connect($data->db_username, $data->db_password, $data->db_host)) {
		$valid_user = true;
		if ( $db->select_db($data->db_schema, $connect) ) {
			$schema_exists = true;
			if ( $db->test_db_access($connect) ) {
				$schema_access = true;
			}
		}
	}

	if ( empty($data->db_root_password) && (!$valid_user || !$schema_exists || !$schema_access) ) {
		//If failed to connect and we don;t have the root password then quit. Should not get here anyway.
		//This should not happen but if it does then just exit
		echo "<H2 style='color:red'>Database error, aborted. Please restart your installation again.</H2>";
		exit;
	}

	$root_connection = FALSE;

	//Use root to setup new account and/or schema if needed
	if ( !empty($data->db_root_password) && (!$valid_user || !$schema_exists || !$schema_access) ) {
		//Just in case...
		if ( $data->db_schema == 'mysql' || $data->db_schema == 'information_schema' ) {
			exit;
		}
		//root access was checked but just in case
		if ( !($root_connection = $db->connect('root', $data->db_root_password, $data->db_host))) {
			echo "<H2 style='color:red'>Database root access error, aborted. Please restart your installation again.</H2>";
			exit;
		}
		//Set up the user account and schema using root connection
		$ret = $db->setup_user($data->db_username, $data->db_password, $data->db_schema, $root_connection);
		if ( !$ret ) {
			echo "<H2 style='color:red'>Database user/schema create error, aborted. Please restart your installation again.</H2>";
			exit;
		}
	}

	if ( $root_connection ) {
		// (issue: 312)
		// Added this to close root connection and hope this can fix a install issue
		// that the newly created DB user is not usable. This assumes the root connection was opened and
		// the changes (new user and DB) are held in a transaction and didn't get written to the DB yet.
		// This DB close will make sure the transaction gets committed.
		$db->close($root_connection);
	}

	$connect = $db->connect($data->db_username, $data->db_password, $data->db_host, $data->db_schema);
	if ( !$connect ) {
		echo "<H2 style='color:red'>Database connect error after account setup, aborted. Please restart your installation again.</H2>";
		echo "<H2 style='color:red'>Please create the STEPS database and database user account manually before the next installation.</H2>";
		exit;
	}
	//$db->set_key_check($connect, false);
	$sql = osa_next_sql(realpath(DB_SCRIPT_FILE));
	// $count = 0;
	while ($sql) {
		// We have multiple string patterns in the db_init.sql file that need to be replaced by the version #.
		// The STEPS release system automatically replaces these but the development environment
		// needs the following str_replace to work. It is a good idea to store the version in a single location.
		// STEPS_APPLICATION_VERSION_NUMBER is a install system constant.
		// CMS_SOURCE_VERSION is a STEPS app constant.
		$sql = str_replace(STEPS_APPLICATION_VERSION_NUMBER, CMS_SOURCE_VERSION, $sql);
		$ret = $db->steps_query($sql, $connect);
		if ( !$ret ) {
			echo "<H2 style='color:red'>Failed to run DB query $sql, aborted. Please restart your installation again.</h2>";
			exit;
		}
		/*
		if ( $count > 75 ) {
			echo '<br />';
			$count = 0;
		}
		*/
		echo '.' . str_repeat(' ', 16);
		$sql = osa_next_sql();
		// $count++;
	}
	if ( !$db->set_steps_password($data->web_admin_username, $data->web_admin_password, $cms_password_key, $connect) ) {
		echo "<H2 style='color:red'>Failed to set STEPS admin password, aborted. Please restart your installation again.</h2>";
		exit;
	}
	//$db->set_key_check($connect, true);
}

// Function to upgrade 3rd party tables. This also checks the new site and make sure it is functional.
function tp_upgrade($data) {
	$baseurl = $data->site_url;
	osa_str_addslash($baseurl);
	$key = upgrade_key();
	$prepare_url = $baseurl . "admin/dbupgrade/prepare/{$key}";
	$upgrade_url = $baseurl. "admin/dbupgrade/upgrade/{$key}";

	$ret = osa_get_url_content($prepare_url);
	if ( !tp_result_valid($ret) ) {
		// Let's try 127.0.0.1 instead of hostname. This is needed if no DNS on server.
		// This will not work if apache is using vhost without the proper config.
		$hostname = parse_url($baseurl, PHP_URL_HOST);
		$baseurl_mod = str_replace($hostname, '127.0.0.1', $baseurl);
		$prepare_url = $baseurl_mod . "admin/dbupgrade/prepare/{$key}";
		$upgrade_url = $baseurl_mod . "admin/dbupgrade/upgrade/{$key}";
		$ret = osa_get_url_content($prepare_url);
	}
	if ( !tp_result_valid($ret) ) {
		echo "<H4 style='color:red'>The newly installed STEPS site <a href='{$baseurl}'>{$baseurl}</a> failed to respond. " .
			  "<br />The possible cause can be:<br /><br />" .
			  "1) Apache rewrite module is not enabled.<br />" .
			  "<br />&nbsp;&nbsp;&nbsp Add the line \"LoadModule rewrite_module modules/mod_rewrite.so\" to your Apache config file.<br /><br />" .
			  "2) The Apache directory directive AllowOverride option for the STEPS web folder is not on.<br />" .
			  "<br />&nbsp;&nbsp;&nbsp Set \"AllowOverride All\" in the web directory directive (e.g. &lt;Directory \"/YOUR/WEB_FOLDER\"&gt;) to your Apache config file.<H4>";
		echo "<br /><br />Server Error Response:<br />" . $ret;
		exit;
	}
	if ( tp_result_error($ret) ) {
		echo $ret;
		echo "<H2 style='color:red'>Unexpected error during DB upgrade preparation, aborted.</H2>";
		exit;
	}
	if ( tp_result_done($ret) ) {
		return TRUE;
	}
	// 30 app upgrades should be plenty for now.
	$count = 0;
	while ($count++ < MAX_3P_APPLICATIONS) {
		$ret = osa_get_url_content($upgrade_url);
		if ( !tp_result_valid($ret) ) {
			echo "<H2 style='color:red'>Error, failed to connect to the TODCM site during a database upgrade.</H2>";
			exit;
		}
		echo $ret;
		if ( tp_result_done($ret) ) {
			return TRUE;
		}
		if ( tp_result_error($ret) ) {
			echo "<H2 style='color:red'>Error, the new site failed to process a database upgrade.</H2>";
			exit;
		}
		echo str_repeat(' ', 32);
	}
	// Max loop reached so print error.
	echo "<H2 style='color:red'>Unexpected error during module database upgrade, aborted.</H2>";
	exit;
}

// Make sure we has return status from URL
function tp_result_valid($html) {
	if ( stripos($html, '<!-- STATUS_') !== FALSE ) {
		return true;
	}
	return false;
}

function tp_result_real($html, $str) {
	// <!-- STATUS_ERROR -->, <!-- STATUS_OK -->, <!-- STATUS_DONE -->
	if ( stripos($html, "<!-- STATUS_{$str} -->") !== FALSE ) {
		return true;
	}
	return false;
}

// Return status error from URL. Should stop processing for the rest of install.
function tp_result_error($html) {
	return tp_result_real($html, 'ERROR');
}

/*
// Return status ok from URL. Everything is working and there are 3rd party upgrades needed.
function tp_result_ok($html) {
	return tp_result_real($html, 'OK');
}
*/

// Return status done from URL. No 3rd party upgrade is needed.
function tp_result_done($html) {
	return tp_result_real($html, 'DONE');
}

function add_htaccess($site_index_file) {
	$file = file_get_contents('htaccess');
	if ( empty($file) ) {
		echo "<H2 style='color:red'>Missing install/htaccess file, aborted. Please download a new copy of STEPS and restart your installation.</h2>";
		exit;
	}
	// So many people are using shared-hosting with CGI so make their life easier
	// by making it CGI freindly.
	$cgi_str = '';
	if ( is_cgi_mode() ) {
		$cgi_str = '?';
	}
	$newfile = str_ireplace('/index.php/$1', "{$site_index_file}{$cgi_str}/\$1", $file);
	$ret = file_put_contents('../.htaccess', $newfile);
	if ( $ret < 10 ) {
		echo "<H2 style='color:red'>Failed to create .htaccess file, aborted. Please delete " . realpath('../.htaccess') . " and restart your installation.</h2>";
		exit;
	}
	$index_ci = file_get_contents('../index_ci.php');
	if ( empty($index_ci) ) {
		echo "<H2 style='color:red'>Failed to read index_ci.php file, aborted. Please delete " . realpath('../.htaccess') . " and restart your installation.</h2>";
		exit;
	}
	$ret = file_put_contents('../index.php', $index_ci);
	if ( $ret < 10 ) {
		echo "<H2 style='color:red'>Failed to modify index.php file, aborted. Please delete " . realpath('../.htaccess') . " and restart your installation.</h2>";
		exit;
	}
	echo "Done<br /><br />";
}

function add_config_file($data, $cms_password_key) {
	$platform = init_platform_config();
	$slash = $platform->slash;

	//////////////////////////////////////////////////////////
	//configs
	//Default site ID
	$config['cms_unit_org'] = 'steps';
	$config['base_url'] = $data->site_url;
	//magic_quotes_gpc = On in php.ini causes double back slashes
	$data->folder_path = str_replace('\\\\', '\\', $data->folder_path);
	$config['cms_resource_uploadfolder'] = $data->folder_path . "{$slash}upload";
	$config['cms_resource_uploadtmpfolder'] = $data->folder_path . "{$slash}tmp";
	$config['cms_pdf_exe'] = '';
	if ( !empty($platform->pdf_exe_filename) ) {
		$config['cms_pdf_exe'] = dirname(__FILE__) . "{$slash}{$platform->pdf_exe_filename}";
	}
	$config['cms_pdf_tmp_dir'] = "{$config['cms_resource_uploadtmpfolder']}{$slash}pdf{$slash}";
	if ( is_os(PLATFORM_WINDOWS) ) {
		$config['cms_pdf_tmp_dir'] .= $slash;
	}
	$config['cms_pdf_exe_options_extra'] = $platform->pdf_option;
	$config['cms_html_cgi_bin'] = $data->cgi_url . basename($platform->mimetext_cgi_filename);
	$config['cms_password_key'] = $cms_password_key;
	$config['cms_magic_password'] = 'false';
	$config['encryption_key'] = get_unique_md5();
	// Set the upgrade key
	upgrade_key($config['encryption_key']);

	//////////////////////////////////////////////////////////
	//globals
	$global['CMS_DB_USERNAME'] = $data->db_username;
	$global['CMS_DB_PASSWORD'] = $data->db_password;
	$global['CMS_DB_HOSTNAME'] = $data->db_host;
	$global['CMS_DB_SCHEMA'] = $data->db_schema;
	$global['CMS_DB_ACTIVEGROUP '] = 'production';

	$text = "<?php\n" .
		"//This file was generated by the STEPS instllation.\n" .
		"//Do not modify the MD5 values in this file else your site will not function.\n" .
		"//Please always backup this file along with your database backups.\n\n";
	foreach ($config as $key=>$value) {
		if ($value == 'false' || $value == 'true') {
			$text .= '$config[\'' . $key . "'] = {$value};\n";
		}
		else {
			$text .= '$config[\'' . $key . "'] = '{$value}';\n";
		}
	}
	foreach ($global as $key=>$value) {
		if ($value == 'false' || $value == 'true') {
			$text .= "\${$key} = $value;\n";
		}
		else {
			$text .= "\${$key} = '$value';\n";
		}
	}
	file_put_contents("../" . INSTALL_CONFIG_FILE, $text);
}

// Just a place to set and get the upgrade key
function upgrade_key($newkey=FALSE) {
	static $key=false;
	if ( !$newkey ) {
		return $key;
	}
	$key = $newkey;
}

function get_unique_md5() {
	$seed = rand(9999,99999999) . serialize($_REQUEST);
	return md5($seed);
}

// Creates a test folder with a .htaccess file and a test file to test that
// htaccess works in a subfolder. This test is skipped if installing on a root folder.
/*
function check_htaccess_override() {
	$url_path = trim(parse_url(get_site_url(), PHP_URL_PATH));
	$web_root = realpath('..');
	$folder = CMS_INSTALL_HTACCESS_TEST_PREFIX . rand(1000000, 9999999);
	$folder_path = $web_root . '/' . $folder .'/';
	$htaccess = $folder_path . '.htaccess';
	$testfile = $folder_path . 'test.txt';
	$testurl = get_site_url() . $folder . '/test.htm';
	$ret = @mkdir($folder_path);
	if ( !$ret ) {
		return false;
	}
	file_put_contents($testfile, '__success__');
	file_put_contents($htaccess, "RewriteEngine on\nRewriteRule (.*)\\.htm \$1.txt\n");
	$ret = osa_get_url_content($testurl);
	$pos = strpos($ret, '__success__');
	if ( $pos !== FALSE && $pos < 3 ) {
		@unlink($testfile);
		@unlink($htaccess);
		@rmdir($folder_path);
		return true;
	}
	// Failed so try 127.0.0.1
	$hostname = parse_url($testurl, PHP_URL_HOST);
	$testurl = str_replace($hostname, '127.0.0.1', $testurl);
	$ret = osa_get_url_content($testurl);
	$pos = strpos($ret, '__success__');
	@unlink($testfile);
	@unlink($htaccess);
	@rmdir($folder_path);
	return ( $pos !== FALSE && $pos < 3 );
}
*/

function is_cgi_mode() {
	if ( stripos(php_sapi_name(), 'cgi') !== false ) {
		return true;
	}
	return false;
}

function valid_3p_path($path) {
	if ( empty($path) ) {
		return false;
	}
	$check_path = '../system/application/third_party/' . $path;
	return ( is_dir($check_path) || is_file($check_path) );
}
