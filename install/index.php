<?php
/**
 * index.php - Main install file. All requests come into this one index file.
 * 				The implementation is based on MVC concept but without a MVC framework!
 * 				This is the initial experience users will get so it should be done properly!
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright 
*/
define ('INSTALL_PROCESS', TRUE);

require_once('constant.php');
require_once('controller.php');
require_once('lib.php');
require_once('db_functions.php');

//error_reporting(E_ALL);
error_reporting(0);
//osa_header_nocache();
@ob_implicit_flush(true);
@ob_end_flush();

$page_controller = new page_controller();

$cmd = FALSE;
if ( array_key_exists('cmd', $_REQUEST) ) {
	$cmd = $_REQUEST['cmd'];
}

//URL command to execute.
//The navigation logic is built into each view page.
//The final step will check and validate everything again.
switch ($cmd) {
	// Step 2
	case CMD_DB_SETTINGS:
		$page_controller->db_settings();
		break;
	case CMD_DB_SETTINGS_HANDLE:
		$page_controller->db_settings_handle();
		break;
	//Step 2.1
	case CMD_DB_ASKROOT:
		$page_controller->ask_root();
		break;
	case CMD_DB_ASKROOT_HANDLE:
		$page_controller->ask_root_handle();
		break;
	// Step 3
	case CMD_WEB_ADMIN_SETTINGS:
		$page_controller->web_admin_settings();
		break;
	case CMD_WEB_ADMIN_SETTINGS_HANDLE:
		$page_controller->web_admin_settings_handle();
		break;
	// Step 4
	case CMD_WEB_DATA_FOLDER:
		$page_controller->data_folder();
		break;
	// Step 5
	case CMD_WEB_DATA_FOLDER_HANDLE:
		$page_controller->data_folder_handle();
		break;
	// Default index page - step 1
	case CMD_CHECK_REQUIREMENTS:
		$page_controller->check_requirements();
		break;
	case CMD_PHPINFO:
		phpinfo();
		exit;
	default:
		check_installed();
		//This is the first page
		$page_controller->check_requirements();
}
