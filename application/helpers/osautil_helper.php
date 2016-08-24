<?php
/**
 * osautil_helper.php - Put all the application helper functions here
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
 */

require_once('osautil_ext_helper.php');

function osa_echo(&$str) {
	if (isset($str) && !is_null($str) ) {
		echo $str;
	}
	echo '';
}

// This one will cause ajaxpostform to return false and discard the response content.
function osa_ajaxmsg($msg) {
	if ( empty($msg) ) {
		return '';
	}
	return sprintf(CMS_AJAX_TAG_ERROR, $msg);
}

// This one will process the response content and print the message in a popup.
function osa_ajaxmsg_normal($msg) {
	if ( empty($msg) ) {
		return '';
	}
	return sprintf(CMS_AJAX_TAG_MSG, $msg);
}

function osa_ajaxmsg_redirecthome($msg=FALSE, $use_redirect_button=TRUE) {
	if ( empty($msg) ) {
		$msg = lang('gen_js_ajaxsessiontimeoutmsg');
	}
	if ( $use_redirect_button ) {
		$msg .= '<br /><br /><input type="button" value="' . lang('gen_redirectbutton') .
				  '" onclick="newLocation(\'' . base_url() . '\')"' . osa_btn(FALSE) . ' />';
	}
	return sprintf(CMS_AJAX_TAG_REDIRECT, $msg);
}

function osa_nosession_ajax_redirect() {
	$CI = get_instance();
	$user = $CI->login_model->getLogin();
	if ( ! is_object($user) ) {
		echo osa_ajaxmsg_redirecthome(lang('gen_js_ajaxsessiontimeoutmsg'));
		exit;
	}
}

function osa_ajax_url($url) {
	if ( empty($url) ) {
		return '';
	}
	return sprintf(CMS_AJAX_TAG_URL, $url);
}

function osa_ajax_clipboard($text){
	return sprintf(CMS_AJAX_TAG_CLIPBOARD, $text);
}

// Pass in a PHP array and this will process boolean, string and numeric data types.
// Pass in a string and it will get passed to jscript directly.
function osa_ajax_clipboard_array($jarray){
	if ( is_array($jarray) && count($jarray) <= 0) {
		return '';
	}
	if ( is_array($jarray) ) {
		$str = '[';
		$sep = '';
		foreach($jarray as $value) {
			if ( $str != '[' ) {
				$sep = ',';
			}
			$str .= $sep;
			if ( $value === TRUE ) {
				$str .= 'true';
			}
			elseif ( $value === FALSE) {
				$str .= 'false';
			}
			elseif (is_numeric($value)) {
				$str .= $value;
			}
			else {
				$str .= "'{$value}'";
			}
		}
		$str .= ']';
	}
	else {
		$str = $jarray;
	}
	return sprintf(CMS_AJAX_TAG_CLIPBOARD_ARRAY, $str);
}

// This one can determine is ajax msg format needed or not.
function osa_msg($msg) {
	if ( osa_is_ajax() ) {
		return osa_ajaxmsg($msg);
	}
	return $msg;
}

//Get the user display name from one or more user objects
//$br_count will print <br /> tage for every number specified
function &osa_user_getname($users, $sep=', ', $br_count=FALSE) {
	if ( empty($users) ) {
		$empty = '';
		return $empty;
	}
	if (is_object($users)) {
		$users = array($users);
	}
	if (!is_array($users) ) {
		$empty = '';
		return $empty;
	}
	$retstr = '';
	$delimeter = '';
	$loop_count = 1; //Firs loop will continue and not increment so set it to 2
	foreach ($users as $user) {
		if ( !is_object($user) ) {
			continue;
		}
		if ( isset($user->displayname) ) {
			$displayname = &$user->displayname;
		}
		else {
			$displayname = '';
		}
		if ( !empty($displayname) ) {
			$retstr .= "{$delimeter}{$displayname}";
			if ($delimeter == '') {
				$delimeter = $sep;
			}
			if ( $br_count !== FALSE ) {
				if ( $loop_count >= $br_count ) {
					$delimeter = "$sep<br />";
					$loop_count=0;
				}
				else {
					$delimeter = $sep;
				}
			}
			$loop_count++;
			continue;
		}
		$firstname = &$user->firstname;
		if ( isset($user->middlename) ) {
			$middlename = $user->middlename;
		}
		else {
			$middlename = '';
		}
		$lastname = &$user->lastname;
		if ( !empty($middlename) ) {
			$retstr .= "{$delimeter}{$firstname} $middlename $lastname";
		}
		else {
			$retstr .= "{$delimeter}{$firstname} $lastname";
		}
		if ( $retstr != '' ) {
			$delimeter = $sep;
			if ( $br_count !== FALSE ) {
				if ( $loop_count >= $br_count ) {
					$delimeter = "$sep<br />";
					$loop_count=0;
				}
				else {
					$delimeter = $sep;
				}
			}
		}
		$loop_count++;
	}
	return $retstr;
}

//Gets the user real name by id. This is a standalone function and no prior DB calls are needed.
//This will get all the active users into the DB cahce on the first call.
function osa_user_getname_byid($userid) {
	if ( !osa_is_int($userid) && $userid <= 0 ) {
		return FALSE;
	}
	$CI = get_instance();
	//This function is expected to be called multiple times so we read in all the active users in one query
	//Model function uses cache
	$users = & $CI->login_model->get_active_usernames();
	if ( is_array($users) && isset($users[$userid]) ) {
		return osa_user_getname($users[$userid]);
	}
	//User is not an active user so do individual user lookup
	//Worst case is that all the lookup users are inactive users and no one call gets the same user again,
	//and ended up we make many DB calls.
	//Model function uses cache
	return osa_user_getname($CI->login_model->get_username_byid($userid));
}

/*
 * Loop thru objects and get values from a specific field
 * Stop using this and use osa_value() instead. But, it does support separators though
 */
function osa_getfield($objects, $field, $sep=',') {
	if (is_object($objects))
	$objects = array($objects);
	if ( !is_array($objects) )
	return '';
	$retstr='';
	$delimeter = '';
	foreach ($objects as $object) {
		if ( !is_object($object) )
		return '';
		$value = FALSE;
		$code = "\$value=(isset(\$object->{$field}) ? \$object->{$field} : FALSE);";
		eval($code);
		if ( $value ) {
			$retstr .= $delimeter . trim($value);
		}
		if ( $retstr != '' )
		$delimeter = $sep;
	}
	return $retstr;
}

/*
 * Gets all the value from array and put it in a str and separated by $sep
 */
function osa_getarrayvalue($array, $sep=',') {
	if ( !is_array($array) ) {
	return '';
	}
	$retstr = '';
	$delimeter = '';
	foreach ($array as $value) {
		$retstr .= $delimeter . $value;
		if ( $retstr != '' ) {
		$delimeter = $sep;
	}
	}
	return $retstr;
}

/*
 * Function to get ids from dynamic form post names or values
 * Typical formname - newlt_123, newlt is the pattern, 123 is the id
 * If $usevalue is false then array value is id
 * Returns a array with index being the id, if $usevalue is ture then array value is value
 * 	A typeical array elemnt would look like ids[123]=>123 or ids[123]=>1 if using _POST value
 * Returns False when there is no match
 * Can use finction osa_getarrayvalue to get a string with delimited ids from the result of this function
 */
function osa_get_dynpost_values($pattern, $usevalue=TRUE) {
	$keys = array_keys($_POST);
	$ids = array();
	foreach($keys as $key) {
		$tok = strtok($key,'_');
		if ($tok==$pattern) {
			$formid = strtok('_');
			if ( is_numeric($formid)) {
				$value = trim($_POST[$key]);
				if ( $value == '' ) //Form element is present even no value, so skip if no value
				continue;
				if ( $usevalue ) {
					$ids[$formid] = $value;
				}
				else {
					$ids[$formid] = $formid;
				}
			}
		}
	}
	if ( count($ids) > 0 )
	return $ids;
	return FALSE;
}

//This will make buttons to behave like buttons and also hides buttons for PDF generation
function osa_btn($echo=TRUE, $disabled=FALSE) {
	if ( osa_pdf_mode() ) {
		$ret = ' class="btnhide" ';
	}
	else {
		if ( $disabled ) {
			$class = 'dis';
		}
		else {
			$class = 'btn';
		}
		$ret = ' class="' . $class . '" onMouseover="btn_mouse(this,1);" onMouseout="btn_mouse(this);" ';
	}
	if ( !$echo ) {
		return $ret;
	}
	echo $ret;
}

// $jscript - the jscript to run, make sure to return false.
// $button_text - the button display name, default is close.
// $id - the button id, default is a random id.
// $name - the button name, default is $id.
function osa_button($jscript, $button_text=FALSE, $id=FALSE, $name=FALSE, $alt_text=FALSE) {
	if ( !$button_text ) {
		$button_text = lang('gen_closebutton');
	}
	if ( !$id ) {
		$id = 'button' . mt_rand(100000001, 999999999);
	}
	if ( !$name ) {
		$name = &$id;
	}
	if ( empty($alt_text) ) {
		$title='';
	}
	else {
		$title = "title=\"$alt_text\" ";
	}

	return "<input id=\"{$id}\" name=\"{$name}\" type=\"button\" value=\"{$button_text}\" {$title}onClick=\"{$jscript}\"" . osa_btn(FALSE) . ' />';
}

function osa_button_disable($button_text) {
	return "<input class=\"dis\" type=\"button\" disabled=\"disabled\" value=\"{$button_text}\" />";
}

function osa_button_close($close_window_id) {
	$jscript = "closeDhtmlWindow('{$close_window_id}');return false;";
	return osa_button($jscript);
}

function osa_dbdate() {
	return date("Y-m-d H:i:s");
}

//Get all the request checked checkbox data matching the name prefix in an array similar to $_REQUEST
function osa_chkbox_array($prefix, $chknum=TRUE) {
	$keys = array_keys($_REQUEST);
	$data = array();
	foreach ($keys as $key) {
		$value = trim($_REQUEST[$key]);
		if ( strpos($key,$prefix) === 0 && $value != '' ) {
			if ($chknum && !is_numeric($value)) {
				continue;
			}
			$data[$key] = $value;
		}
	}
	return $data;
}
function osa_db_lastinsertid($db=FALSE) {
	if ( $db === FALSE ) {
		$CI = get_instance();
		$db = $CI->db;
	}
	$res = $db->query('select last_insert_id() id');
	if ( $res->num_rows() <= 0 ) {
		return FALSE;
	}
	$rows = $res->result();
	$row = $rows[0];
	if ($row->id <= 0) {
		return FALSE;
	}
	return  $row->id;
}

function osa_view_exists($modelfile) {
	$filename = APPPATH . 'views/' . $modelfile . EXT;
	return file_exists($filename);
}

function osa_lib_exists($file) {
	$filename = APPPATH . 'libraries/' . $file . EXT;
	return file_exists($filename);
}

function osa_date_format($date, $print_time=FALSE) {
	if ( !$date ) {
		return '';
	}
	$time = strtotime($date);
	if ( !$time ) {
		return $date;
	}
	if ( !$print_time ) {
		return date('Y-m-d', $time);
	}
	return date('Y-m-d G:i:s', $time);
}

function osa_buildererror() {
	$e = new Exception();
	osa_log(__FUNCTION__, 'Builder error.', $e->getTraceAsString());
	return lang('unit_buildererror');
}

//$template action can be 'add|edit|read|all', $appaction is always a single action
function osa_valid_action($templateaction, $appaction) {
	$pos = strpos($templateaction, $appaction);
	if ( $pos === FALSE ) {
		if ( $templateaction == CMS_ACTION_ALL ) {
			return TRUE;
		}
		return FALSE;
	}
	return TRUE;
}

//Get value from array or object, this is for the view files
//Returns '' if fails. Remember to turn echo off if you don't need it
function osa_value(&$obj, $field, $echo=TRUE) {
	if ( empty($obj) ) {
		return FALSE;
	}
	if ( is_array($obj) ) {
		if ( isset($obj[$field]) ) {
			if ( $echo ) echo $obj[$field];
			return $obj[$field];
		}
	}
	else {
		if ( isset($obj->$field ) ) {
			if ( $echo ) echo $obj->$field;
			return $obj->$field;
		}
	}
	return FALSE;
}

/*
 * This is the upload dir before the $resid
 * Still used by the tmpupload and sometimes used to build the actual downloaddir by appending the resid
 */
function osa_unituploaddir($topdir, $courseid, $unitid, $type, $mkdir=FALSE) {
	if (strlen($courseid)<=0) {
		return FALSE;
	}
	$firstchar = substr($courseid, 0, 1);
	if ($unitid == '') {
		$dir = "{$topdir}/" . CMS_UPLOAD_FOLDR_NAME . "/{$firstchar}/{$courseid}";
	}
	elseif($type=='') {
		$dir = "{$topdir}/" . CMS_UPLOAD_FOLDR_NAME . "/{$firstchar}/{$courseid}/{$unitid}";
	}
	else {
		$dir = "{$topdir}/" . CMS_UPLOAD_FOLDR_NAME . "/{$firstchar}/{$courseid}/{$unitid}/{$type}";
	}
	if ($mkdir) {
		if ( !is_dir($dir) ) {
			if ( !osa_mkdir($dir) ) {
				return FALSE;
			}
		}
	}
	return $dir;
}

/*
 * Remove a direcory under the upload directory.
 * If type is '' then unit is remove. If unitis is '' then course is removed
 */
function osa_remove_uploaddir($courseid, $unitid, $type, $resid='') {
	if (!is_numeric($courseid))
	return FALSE;
	$CI = get_instance();
	$topdir=$CI->config->item('cms_resource_uploadfolder');
	$dir = osa_unituploaddir($topdir, $courseid, $unitid, $type);
	if ( $resid != '')
	$dir = $dir . "/{$resid}";
	if ( is_dir($dir) )
	return osa_remove_directory($dir);
	return TRUE;
}

/*
 * Found a bug that different resources with the same type use the same uploaddir
 * So, use the resid in the path.
 * The orignal osa_unituploaddir() is still used for tmp upload storage.
 */
function osa_unituploaddir_res($resobj) {
	if (!is_object($resobj))
	return FALSE;
	if  (!property_exists($resobj,'courseid') || !property_exists($resobj,'unitid') ||
	!property_exists($resobj,'type') || !property_exists($resobj,'id'))
	return FALSE;
	$CI = get_instance();
	$tmpdir = osa_unituploaddir($CI->config->item('cms_resource_uploadfolder'),
	$resobj->courseid, $resobj->unitid, $resobj->type);
	if ( $tmpdir === FALSE )
	return FALSE;
	return $tmpdir.  '/' . $resobj->id;
}

/* function osa_uploadfilepath($resobj, $property='upload1') {
 if (!is_object($resobj))
 return FALSE;
 if  (!property_exists($resobj,$property))
 return FALSE;
 if ( is_null($resobj->$property) || $resobj->$property == '' )
 return FALSE;
 $CI =get_instance();
 return osa_unituploaddir($CI->config->item('cms_resource_uploadfolder'),
 $resobj->courseid, $resobj->unitid, $resobj->type) . '/' . $resobj->$property;
 }
 */

/*
 function osa_uploadfilepath($resobj, $uploadobj, $property='filename') {
 if (!is_object($resobj))
 return FALSE;
 if  (!property_exists($uploadobj,$property))
 return FALSE;
 if ( is_null($uploadobj->$property) || $uploadobj->$property == '' )
 return FALSE;
 $CI =get_instance();
 return osa_unituploaddir($CI->config->item('cms_resource_uploadfolder'),
 $resobj->courseid, $resobj->unitid, $resobj->type) . '/' . $uploadobj->$property;
 }
 */
/*
 function osa_uploadfolder($resobj) {
 if (!is_object($resobj))
 return FALSE;
 $CI =get_instance();
 return osa_unituploaddir($CI->config->item('cms_resource_uploadfolder'),
 $resobj->courseid, $resobj->unitid, $resobj->type);
 }
 */

/*
 * It is now used to create a dir to indicate the folder is deleted by adding the 11deletedxx dir
 */
function osa_uploadfolder_deleted($resobj, $resid) {
	if (!is_object($resobj) || !is_numeric($resid) )
	return FALSE;
	$CI = get_instance();
	$dir = osa_unituploaddir($CI->config->item('cms_resource_uploadfolder'),
	$resobj->courseid, $resobj->unitid, $resobj->type) . "/$resid/11deletedxx";
	if ( !is_dir($dir) ) {
		if ( !osa_mkdir($dir) ) {
			return FALSE;
		}
	}
	return $dir;
}

/*
 * Copy files form a unit to a tmp directory so we can
 * do a fast directory rename after some DB operations
 */
function osa_prepare_unitfiles($origcourseid, $origunitid, $newcourseid) {
	$CI = get_instance();
	$uploadtop = $CI->config->item('cms_resource_uploadfolder');
	$origfolder = osa_unituploaddir($uploadtop,$origcourseid,$origunitid,'');
	$tmpfolder = osa_unituploaddir($uploadtop,$newcourseid,'tmp_unit','');
	//Prepare the tmp directory
	if ( is_dir($tmpfolder) ) {
		//This should not happen but do it anyway
		if ( !osa_remove_directory($tmpfolder) )
		return FALSE;
	}
	if (!is_dir($origfolder)) {
		//No upload folder from original unit so no copy and just return success
		return TRUE;
	}
	if ( !osa_copy_directory($origfolder, $tmpfolder))
	return FALSE;
	return TRUE;
}

/*
 * Rename/move the prepared unit folder after the call osa_prepare_unitfiles()
 */
function osa_rename_tmpunit($courseid, $unitid, $resource_dirs) {
	$CI = get_instance();
	$uploadtop = $CI->config->item('cms_resource_uploadfolder');
	$newfolder = osa_unituploaddir($uploadtop,$courseid,$unitid,'');
	$tmpfolder = osa_unituploaddir($uploadtop,$courseid,'tmp_unit','');
	//This means original unit has no uploads
	if ( !is_dir($tmpfolder) )
	return TRUE;
	if ( is_dir($newfolder) ) {
		osa_errorlog(__METHOD__ . " - Trying to copy unit but the destination folder $newfolder already exits.");
		return FALSE;
	}
	if ( rename($tmpfolder,$newfolder) === FALSE ) {
		osa_remove_directory($tmpfolder);
		osa_remove_directory($newfolder);
		return FALSE;
	}
	//Rename all the directories based on resourceid
	foreach ($resource_dirs as $dir) {
		if ( is_dir("$newfolder/$dir->type/$dir->origid") ) {
			if ( !rename("$newfolder/$dir->type/$dir->origid", "$newfolder/$dir->type/$dir->id") ) {
				osa_remove_directory($tmpfolder);
				osa_remove_directory($newfolder);
				return FALSE;
			}
		}
	}
	return TRUE;
}

function osa_filesizestr($size, $decimals=2) {
	if ( !is_numeric($size) )
	return '';
	$unit='Bytes';
	$div=1;
	if ($size > 1073741824) {
		$unit = 'GB';
		$div = 1073741824;
	}
	if ($size > 1048576) {
		$unit = 'MB';
		$div = 1048576;
	}
	elseif ($size > 1024) {
		$unit = 'KB';
		$div = 1024;
	}
	$size = number_format($size / $div, $decimals);
	return $size . " $unit";
}

function osa_rename($fromfile, $tofile) {
	$stat = rename($fromfile, $tofile);
	if ( $stat )
	chmod($tofile, 0666);
	return $stat;
}

function osa_mkdir($dir) {
	if ( !is_dir($dir) ) {
		return mkdir($dir, 0777, TRUE);
	}
	return TRUE;
}

//This is depricated
function osa_errorlog($msg, $var=NULL) {
	osa_log('', $msg, $var);
}

// The main/real log routine, do not use this function and use the warappers instead.
function osa_log_xdo_not_call($level, $method, $msg, $args) {
	static $CI=NULL;
	if ( $CI === NULL ) {
		$CI = get_instance();
	}
	$output = '';
	if ( is_array($args) ) {
		if ( count($args) > 2 ) {
			array_shift ($args);
			array_shift ($args);
			$output = "\nVariable dump :\n";
			@ob_start();
			var_dump($args);
			$output .= ob_get_clean();
		}
	}
	log_message($level, $CI->config->item('cms_unit_org') . " CMS_LOG: {$method} - {$msg}" . $output);
}

// Level 1, the orginal function.
function osa_log($method, $msg) {
	$args = func_get_args();
	osa_log_xdo_not_call('error', $method, $msg, $args);
}

// Level 2
function osa_log_debug($method, $msg) {
	$args = func_get_args();
	osa_log_xdo_not_call('debug', $method, $msg, $args);
}

// Level 3
function osa_log_info($method, $msg) {
	$args = func_get_args();
	osa_log_xdo_not_call('info', $method, $msg, $args);
}

// Level 4 which applies to levels 1, 2 and 3.
function osa_log_all($method, $msg) {
	$args = func_get_args();
	osa_log_xdo_not_call('all', $method, $msg, $args);
}

function osa_process_readtext(&$text) {
	$modtext = str_replace("\r\n", '<br />', $text);
	return str_replace("\n", '<br />', $modtext);
}

/*
 * start with FALSE first
 */
function osa_colortrack(&$flag, $item1, $item2) {
	$flag = !$flag;
	return($flag ? $item2 : $item1);
}

/*
 * Delete all the files in an array with the full path file name in index 'fullpath'
 * _extra_extension is used when there are files based on the orignal file with additional extension.
 * For example: orginal is a.doc and then there is a.doc.pdf, _extra_extension would be '.pdf'.
 */
function osa_deletefiles($file_array, $index='fullpath') {
	if ( is_array($file_array) ) {
		foreach ($file_array as $file) {
			if ( is_array($file) && array_key_exists($index, $file) ) {
				@unlink($file[$index]);
				if ( isset($file['_extra_extension']) && $file[$index]['_extra_extension']) {
					@unlink($file[$index] . $file['_extra_extension']);
				}
			}
		}
	}
}

//Get template from array or object, this is for the view files
function osa_get_template_value($obj, $field) {
	return osa_value($obj, $field, FALSE);
}

//This function used to check the client and server IPs but changed to
//to check a PDF key - generated from osa_pdfkey.
//This will work in any environment including load balancing
/*
 function osa_server_self_request() {
 $uri = $_SERVER['REQUEST_URI'];
 if ( empty($uri) ) {
 osa_errorlog('osa_server_self_request: REQUEST_URI is empty.', $_SERVER);
 return FALSE;
 }
 if ( stripos($uri, osa_pdfkey()) !== FALSE )
 return TRUE;
 return FALSE;
 }
 */

// Set longer timeout for PDF gen
function osa_set_pdf_timeout() {
	$CI = get_instance();
	$timeout = $CI->config->item('cms_pdf_max_timeout');
	if ( is_numeric($timeout) ) {
		set_time_limit($timeout);
	}
	return $timeout;
}

//Inits the PHP session and the CI session based on the CI session cookie values saved in PHP session
//This is currently used in the PDF gen only
function osa_init_phpci_sessions($phpsessionid) {
	$phpsessionid = trim($phpsessionid);
	if ( strlen($phpsessionid) < 10 )
	return;
	session_id($phpsessionid);
	osa_php_session_start();
	$CI = get_instance();
	$cookie_name = $CI->config->item('sess_cookie_name');
	if ( array_key_exists($cookie_name, $_SESSION )) {
		osa_ci_init_session_hack($_SESSION[$cookie_name]);
	}
}

//Inits PHP session and then saves the CI sessin cookie value in PHP session
//It is ok if the PHP session already started before this
function osa_save_ci_sess_cookie() {
	$CI = get_instance();
	$cookie_name = $CI->config->item('sess_cookie_name');
	osa_php_session_start();
	$_SESSION[$cookie_name] = $_COOKIE[$cookie_name];
	return session_id();
}

//This is a hack to reinit the CI session based on the saved session cookie value
function osa_ci_init_session_hack($ci_sessionid) {
	$ci_sessionid = trim($ci_sessionid);
	//Should be very long but just check for 32 chars
	if ( strlen($ci_sessionid) < 32 )
	return;
	$CI = get_instance();
	$CI->session->sess_destroy();
	$cookie_name = $CI->config->item('sess_cookie_name');
	$_COOKIE[$cookie_name] = $ci_sessionid;
	$CI->load->library('session');
	$CI->session->sess_read();
	//This is needed to populate the CI session
	$login = osa_login_object();
}

//Checks the html for patterns for SVG and Math so we know to use the related Jscript or not
function osa_jscript_check_tags(&$html_text) {
	$pattern_math = '<span class="am">';
	$pattern_svg = 'type="image/svg+xml"';
	if ( stripos($html_text, $pattern_math) !== FALSE )
	return TRUE;
	if ( stripos($html_text, $pattern_svg) !== FALSE )
	return TRUE;
	return FALSE;
}

//Reassign array of array index based on the supplied assoicate/numeric index element
function osa_array_rekey($array, $field) {
	if ( !is_array($array) || count($array) < 1 ) {
		return FALSE;
	}
	$ret = array();
	foreach ($array as $item) {
		if ( ($key = osa_value($item, $field, FALSE)) == FALSE ) {
			return FALSE;
		}
		$ret[$key] = $item;
	}
	return $ret;
}

//Removes any duplicated array values. The values have to be primitive data types.
//This will always reindex the array.
function osa_array_remove_duplicate($array) {
	if ( !is_array($array) && count($array) <=0 ) {
		return FALSE;
	}
	$ret = array();
	foreach($array as $value) {
		$ret[$value] = $value;
	}
	return $ret;
}

// Create a new array using the value from indexname as the index and the value from $field as the actual value.
// Good for creating a array for a selection dropdown.
function osa_array_mod($array, $indexname, $field) {
	$data = array();
	foreach ($array as $record) {
		$data[osa_value($record, $indexname, FALSE)] = osa_value($record, $field, FALSE);
	}
	return $data;
}

// Compares 2 arrays and returns true if the values are identical.
// The order of the values is not significant, array(1,2) equals array (2,1).
function osa_array_compare($a1, $a2) {
	if ( !is_array($a1) || !is_array($a2) || count($a1) != count($a2) ) {
		return FALSE;
	}
	$diff = array_diff($a1, $a2);
	if ( empty($diff) ) {
		return TRUE;
	}
	return FALSE;
}

//Key to skip access check for PDF controllers
//We just get first 10 chars from upload key since it is unique
function osa_pdfkey() {
	$CI = get_instance();
	$source = $CI->config->item('cms_pdf_accesskey');
	if ( !$source ) {
		$source = 'test';
	}
	return $source;
}

function osa_php_session_start() {
	$CI = get_instance();
	$id = session_id();
	$timeout = $CI->config->item('sess_expiration') + 1800;
	//@session_set_cookie_params($timeout, $CI->config->item('cookie_path'));
	// PHP session times out frequently because the following 2 lines were inside the if condition. (issue: 469)
	@session_set_cookie_params(0, $CI->config->item('cookie_path'));
	@ini_set('session.gc_maxlifetime', "$timeout");
	if ( empty($id) || !isset($_SESSION) ) {
		@session_start();
	}
}

function osa_php_session_destroy() {
	$id = session_id();
	if ( !empty($id) ) {
		session_destroy();
	}
	else {
		$CI = get_instance();
		//@session_set_cookie_params($CI->config->item('sess_expiration'), $CI->config->item('cookie_path'));
		@session_set_cookie_params(0, $CI->config->item('cookie_path'));
		@session_start();
		@session_destroy();
	}
}

function osa_get_datamodule($data=FALSE, $module=FALSE) {
	if ($data === FALSE)
	return FALSE;

	if ($module === FALSE)
	return $data;

	if (!is_array($data))
	return $data;

	if (  !is_array($module) && array_key_exists($module, $data)) {
		return $data[$module];
	}
	else if (is_array($module) ) {
		$res = FALSE;
		foreach($module as $key) {
			if (!is_array($key) && array_key_exists($key, $data))
			$res[$key] = $data[$key];
		}
		return $res;
	}
	return FALSE;
}

//$templatename is actually filepart like ubd py myp goal
function &osa_get_template_object($templatename) {
	require_once(APPPATH . "libraries/unit/configfactory.php");
	$CI = get_instance();
	$CI->load->library('cms/cms_template/cms_template');
	$folder_hint = $CI->cms_template->filename_templateid($templatename);
	$ret = &configfactory::factory($CI->config->item('cms_unit_org') . '_' . $templatename, $folder_hint);
	return $ret;
}

function osa_load_template_obj($file_part='', $rename_class=TRUE, $folder=CMS_TEMPLATE_UNIT_IDENTIFIER,
										 $class_prefix='unit_config_data', $org_override=FALSE) {
	return osa_load_data_obj($class_prefix, $file_part, $rename_class, $folder, $org_override, CMS_TEMPLATEPATH_BUILDER);
}

function osa_load_mapping_obj($org_override=FALSE, $class_prefix='search_config_data') {
	return osa_load_data_obj($class_prefix, '', TRUE, '', $org_override, CMS_TEMPLATEPATH_MAPPING);
}

//Function to handle class loading with the same class name
//Load class as boject name like unit_config_data_pyp, unit_config_data_myp etc.
//file_part is like pyp, ubd etc.
function osa_load_data_obj($class_prefix, $file_part='', $rename_class=TRUE,
									$folder=CMS_TEMPLATE_UNIT_IDENTIFIER, $org_override=FALSE,
									$filepath=CMS_TEMPLATEPATH_BUILDER) {
	$new_class_name = $class_prefix; //Set new name to the default name
	if ( !empty($file_part) ) {
		$file_part = "_$file_part";
	}
	if ($rename_class) {
		if ( $file_part ) {
			//This is for supporting legacy code that expects the class name in this format
			$new_class_name = $class_prefix . $file_part;
		}
		else {
			//This is for new code that gets the class name from the return code.
			//So, we can use any class name as long as it is unique.
			$new_class_name = '_' . $class_prefix;
			while (TRUE) {
				//This will loop until we have a unique class name
				if ( !class_exists($new_class_name) ) {
					break;
				}
				$new_class_name = '_' . $new_class_name;
			}
		}
	}
	//To prevent loading the same class twice
	if ( class_exists($new_class_name, FALSE) ) {
		return $new_class_name;
	}

	$CI = get_instance();
	//$filepath = CMS_TEMPLATEPATH_BUILDER;
	if ( ! empty($folder) ) {
	$filepath .= "{$folder}/"; //This can be goal, survey etc.
	}

	if ( $folder == CMS_TEMPLATE_SYSTEM_IDENTIFIER ) {
		$filename = CMS_TEMPLATE_SYSTEM_IDENTIFIER . $file_part;
	}
	else {
		if ( !$org_override ) {
			$unit_org = $CI->config->item('cms_unit_org');
		}
		else {
			$unit_org = $org_override;
		}
		$filename = $unit_org . $file_part;
	}

	$fullname = $filepath . $filename . '.php';
	$file_exist_checked = TRUE;
	if ( !file_exists($fullname) ) {
		// Template based on org does not exist so use fallback template.
		$fullname = $filepath . $CI->config->item('cms_fallback_org_name') . $file_part . '.php';
		$file_exist_checked = FALSE;
	}

	if ($file_exist_checked || file_exists($fullname)) {
		$contents = file_get_contents($fullname);
		//Use strpos to check which is faster than preg_match
		if ( strpos($contents, ':LOAD_REDIRECT_BEGIN:') ) {
			//For templates that just include the TODCM version and has a load redirect directive
			//The load redirect directive is a home-grown cheat to load the proper template file
			$matches = FALSE;
			preg_match("/:LOAD_REDIRECT_BEGIN:(.*?):LOAD_REDIRECT_END:/", $contents, $matches);
			if ( is_array($matches) && array_key_exists(1, $matches) ) {
				//This will always load from the builder folder
				//Loads the redirected file instead of the original which usually only contains a require_once statement
				$contents = file_get_contents(CMS_TEMPLATEPATH_BUILDER . $matches[1]);
			}
			else {
				return FALSE;
			}
		}

		if ($rename_class) {
			$class_pattern = 'class unit_config_data';
			if ( $filepath == CMS_TEMPLATEPATH_MAPPING ) {
				$class_pattern = 'class search_config_data';
			}
			$contents = str_ireplace($class_pattern, 'class ' . $new_class_name, $contents);
		}

		if ( !empty($contents) ) {
			/*
			 $contents = preg_replace("/\?>(.*?)(<\?php|<\?)/si", "echo \"\\1\";",$contents);
			 */
			$contents = str_replace("<?php", "", $contents);
			$contents = str_replace("?>", "", $contents);
			eval($contents);
			if ( class_exists($new_class_name) ) {
				return $new_class_name;
			}
		}
	}
	return FALSE;
}
//loop unit template
function osa_unit_template(){
	$CI = get_instance();
	$unit_template = $CI->config->item('cms_unit_templates');
	$_templae = FALSE;
	if (is_array($unit_template)){
		foreach ($unit_template as $key => $template)
		if (is_array($template)){
			foreach ($template as $value)
			$_templae[$value] = $value;
		}
	}

	return $_templae;
}

//$template is like pyp, ubd. index 0 - unit type only 1 - like ubd unit
//This will also read the templateobj->title and short_title
function osa_unittype_translate($template, $index=0) {
	if ( empty($template) ) {
		return FALSE;
	}

	$CI = get_instance();
	$skip_obj_get = FALSE;
	if ( osa_3p_available() && ($cache_lib = tp_cache_object()) ) {
		if ( ($cache = &$cache_lib->get(CMS_3P_PRESISTENT_FOLDER_TEMPLATE, $CI->config->item('cms_unit_org') . "_{$template}")) !== NULL ) {
			if ( $index == 0 && isset($cache->short_title) ) {
				return $cache->short_title;
			}
			if ( $index == 1 && isset($cache->title) ) {
				return $cache->title;
			}
			$skip_obj_get = TRUE;
		}
	}

	if ( !$skip_obj_get ) {
		$class = "unit_config_data_$template";
		if ( !class_exists($class) ) {
			//We use the expensive way to load the template in case we are loading
			//more than 1 template in a single request to avoid class name conflict
			osa_load_template_obj($template);
		}
		if ( class_exists($class) ) {
			$templateobj = FALSE;
			@eval("\$templateobj={$class}::get();");
			if ( is_object($templateobj) ) {
				if ( $index == 0 && isset($templateobj->short_title) ) {
					return $templateobj->short_title;
				}
				if ( $index == 1 && isset($templateobj->title) ) {
					return $templateobj->title;
				}
			}
		}
	}

	$unit_name_config = $CI->config->item('cms_unittype_translate');
	if ( is_array($unit_name_config) && array_key_exists($template, $unit_name_config) && array_key_exists($index, $unit_name_config[$template]) ) {
		//Use config for add unit link title
		$ret = $unit_name_config[$template][$index];
	}
	else {
		//Just use the file part for add unit link title
		osa_load_lang('unit');
		if ( $index==0 ) {
			$ret = strtoupper($template);
		}
		else {
			$ret = strtoupper($template) . ' ' . lang('unit_unit');
		}
	}
	return $ret;
}

//See if the site realted language file exists
function osa_sitelang_exists($file, $site, $lang) {
	$filename = APPPATH . "language/{$lang}/site/{$site}/{$file}_lang.php";
	return file_exists($filename);
}

//Loads the system language file and any related site lang file
function osa_load_lang($langfile) {
	static $CI=NULL;
	static $site=NULL;

	if ( $CI === NULL ) {
		$CI = get_instance();
		$site = $CI->config->item('cms_unit_org');
	}
	if ( $CI->cms_cache->gen_get(__METHOD__, $langfile) !== NULL ) {
		return;
	}
	$CI->lang->load($langfile);
	if ( osa_sitelang_exists($langfile, $site, $CI->config->item('language')) ) {
		$CI->lang->load("site/$site/$langfile");
	}
	$CI->cms_cache->gen_add(__METHOD__, $langfile, $langfile);
}

//Sets one language item
function osa_lang_set($item, $value) {
	static $CI=NULL;
	static $isset_lang_object = FALSE;
	if ( $CI === NULL ) {
		$CI = get_instance();
	}
	if ( $isset_lang_object || ($isset_lang_object = property_exists($CI, 'lang')) ) {
		$CI->lang->language[$item] = $value;
	}
}

function osa_unit_cachetable() {
	$CI = get_instance();
	$CI->load->library('cms/memory_table/unit_cache_table');
	return $CI->unit_cache_table->table();
}
/*
function osa_numeric_str_compare(&$a, &$b) {
	if ( $a < $b ) {
		return -1;
	}
	if ( $a > $b ) {
		return 1;
	}
	return 0;
}
*/
// Natural sort/compare for array of arrays/objects, used for usort(). This is optimized to the max.
// Return value is -1, 0, or 1 like from a PHP compare function.
// Ok, can't put too much code here (too slow) so assume the input params are always correct.
// This is the asc array function, there are asc obj, desc aray and desc obj functions.
// One strange thing is the local funciton variables are slower if they are static.
// if ( $retcode ) is faster than if ( $retcode !==0 ). One global array is faster than multiple global vars.
// if elseif is faster than switch. @ makes it 15% slower. We ended up making this 40% faster!
function osa_array_natsort_real(&$a, &$b) {
	/* sort_indexes 0, sort_indexes_count 1, flag 2, sort_function 3 */
	global $g_osa_array_sort_data;

	//Loop thru the different array elements or object properties
	//The sort order depends on the input sort_indexes order
	for ($i=0; $i<$g_osa_array_sort_data[1]; ++$i) {
		$index = &$g_osa_array_sort_data[0][$i];
		if ( $g_osa_array_sort_data[2] === 1 ) {
			$retcode = strnatcasecmp($a[$index], $b[$index]);
		}
		elseif ( $g_osa_array_sort_data[2] === 2 ) {
			$retcode = strnatcasecmp(strip_tags($a[$index]), strip_tags($b[$index]));
		}
		else {
			if ( $a[$index] < $b[$index] ) {
				return -1;
			}
			if ( $a[$index] > $b[$index] ) {
				return 1;
			}
			continue;
		}
		if ( $retcode ) {
			return $retcode;
		}
	}
	return 0;
}

// Asc object compare function.
function osa_array_natsort_real_obj(&$a, &$b) {
	global $g_osa_array_sort_data;

	for ($i=0; $i<$g_osa_array_sort_data[1]; ++$i) {
		$index = &$g_osa_array_sort_data[0][$i];
		if ( $g_osa_array_sort_data[2] === 1 ) {
			$retcode = strnatcasecmp($a->$index, $b->$index);
		}
		elseif ( $g_osa_array_sort_data[2] === 2 ) {
			$retcode = strnatcasecmp(strip_tags($a->$index), strip_tags($b->$index));
		}
		else {
			if ( $a->$index < $b->$index ) {
				return -1;
			}
			if ( $a->$index > $b->$index ) {
				return 1;
			}
			continue;
		}
		if ( $retcode ) {
			return $retcode;
		}
	}
	return 0;
}

// Desc array compare function.
function osa_array_natsort_real_desc(&$a, &$b) {
	global $g_osa_array_sort_data;

	for ($i=0; $i<$g_osa_array_sort_data[1]; ++$i) {
		$index = &$g_osa_array_sort_data[0][$i];
		if ( $g_osa_array_sort_data[2] === 1 ) {
			$retcode = strnatcasecmp($b[$index], $a[$index]);
		}
		elseif ( $g_osa_array_sort_data[2] === 2 ) {
			$retcode = strnatcasecmp(strip_tags($b[$index]), strip_tags($a[$index]));
		}
		else {
			if ( $b[$index] < $a[$index] ) {
				return -1;
			}
			if ( $b[$index] > $a[$index] ) {
				return 1;
			}
			continue;
		}
		if ( $retcode ) {
			return $retcode;
		}
	}
	return 0;
}

// Desc object compare function.
function osa_array_natsort_real_desc_obj(&$a, &$b) {
	global $g_osa_array_sort_data;

	for ($i=0; $i<$g_osa_array_sort_data[1]; ++$i) {
		$index = &$g_osa_array_sort_data[0][$i];
		if ( $g_osa_array_sort_data[2] === 1 ) {
			$retcode = strnatcasecmp($b->$index, $a->$index);
		}
		elseif ( $g_osa_array_sort_data[2] === 2 ) {
			$retcode = strnatcasecmp(strip_tags($b->$index), strip_tags($a->$index));
		}
		else {
			if ( $b->$index < $a->$index ) {
				return -1;
			}
			if ( $b->$index > $a->$index ) {
				return 1;
			}
			continue;
		}
		if ( $retcode ) {
			return $retcode;
		}
	}
	return 0;
}

// Sorts an array by multiple fields using case-insensitve natural compare
// This is useful for sorting query results since MySQL does not support order by with natural sort
// $fileds is a array containing a list of column names. It will sort the 1st column in the list and then 2nd, 3rd...
// $maintain_index to maintain original indexes or not
// $desc - use asc or desc order
// $compare_function is now true or false and always use numeric compare if true.
function osa_array_natsort(&$array, $fields, $maintain_index=false, $desc=FALSE, $striptags=FALSE, $compare_function=FALSE) {
	global $g_osa_array_sort_data;

	if ( empty($fields) || empty($array) || !is_array($array) ) {
		return FALSE;
	}

	/*
	 * $compare_function is no longer used and it defaults to local < > compare.
	 * This feature can be added later if necessary.
	if ( $compare_function && !function_exists($compare_function) ) {
		osa_errorlog(__FUNCTION__ . " - Invalid user compare function - $compare_function");
		return FALSE;
	}
	*/

	if ( !is_array($fields) ) {
		$fields = array($fields);
	}

	//Check to make sure correct data type and the data indexes/properties exist
	$first_node = reset($array);
	if ( is_object($first_node) ) {
		$array_sort_isobject = TRUE;
		foreach ($fields as $value)  {
			// Value can be null so no isset
			if ( !property_exists($first_node, $value) ) {
				osa_errorlog(__METHOD__ . ' - Missing field.', array($value, $first_node));
				return FALSE;
			}
		}
	}
	elseif ( is_array($first_node) ) {
		$array_sort_isobject = FALSE;
		foreach ($fields as $value) {
			// Value can be null so no isset
			if ( !array_key_exists($value, $first_node) ) {
				osa_errorlog(__METHOD__ . ' - Missing field.', array($value, $first_node));
				return FALSE;
			}
		}
	}
	else {
		osa_errorlog(__METHOD__ . ' - First element is niether array nor object.', $first_node);
		return FALSE;
	}

	if (!$striptags && !$compare_function) {
		$flag = 1;
	}
	elseif($striptags) {
		$flag = 2;
	}
	else {
		// Right now, it is only osa_numeric_str_compare and we stop using it for performance reason.
		$flag = 3;
	}
	$g_osa_array_sort_data = array($fields, count($fields), $flag);
	if ( $maintain_index ) {
		$sort_func = 'uasort';
	}
	else {
		$sort_func = 'usort';
	}
	if ( $desc ) {
		if ( $array_sort_isobject ) {
			$sort_func($array, 'osa_array_natsort_real_desc_obj');
		}
		else {
			$sort_func($array, 'osa_array_natsort_real_desc');
		}
	}
	else {
		if ( $array_sort_isobject ) {
			$sort_func($array, 'osa_array_natsort_real_obj');
		}
		else {
			$sort_func($array, 'osa_array_natsort_real');
		}
	}
	return TRUE;
}

/* No longer needed since we use natural sort on the DB results
 //Converts a string containg numbers and dots to a unique integer. Needed for importid sorting for MySQL queries
 //Returns an integer base on the string. Please make sure the return value is less than MySQL Bigint
 //Example: 13.1.22 becomes 13001022
 function osa_dotnum2int($str, $padding_digit=4, $sep='.') {
 $str = trim($str);
 if ( empty($str) )
 return FALSE;
 $array = explode($sep, $str);
 $count = count($array);
 if ( count($array) < 1 )
 return FALSE;
 $ret = '';
 for ( $i=$count-1; $i>=0; $i-- ) {
 $tmp = "{$array[$i]}";
 //Add padding except the first set of numbers
 if ( $i != 0 ) {
 $tmp = str_pad($tmp,$padding_digit,'0',STR_PAD_LEFT);
 }
 $ret = "$tmp{$ret}";
 }
 return $ret;
 }
 */

//The hex id contains 2 parity digits for the 1st 2 digits and the rest is the actual id, all in hex value
//This function returns an array with the associate index 'parity' containing the parity decimal number and 'id' the id decimal number
//($origid)1f1a returns array('parity' => 31, 'id' => 26)
function osa_hex_id_numval($origid) {
	$origid = strtolower($origid);
	//There are no character o so replace any with number zero.
	//Users can make this mistake without knowing it
	$origid = str_replace('o', '0', $origid);
	if ( strlen($origid) < 3 )
	return FALSE;
	$str_array = str_split($origid);
	$i = 1;
	$parity = '';
	$hex_id = '';
	//First 2 chars are randomly assigned hex number to make guessing the survey id diffcult
	//but it is by no mean a security measure.
	foreach ($str_array as $char) {
		if ( $i <= 2 ) {
			$parity .= $char;
		}
		else {
			$hex_id .= $char;
		}
		$i++;
	}
	$ret = array();
	$ret['parity'] = $parity;
	$ret['id'] = hexdec($hex_id);
	if ( empty($ret['parity']) || empty($ret['id']) || $ret['id'] <=0 )
	return FALSE;
	return $ret;
}

//This function is for getting the survey hex code
function osa_hex_code($parity, $id) {
	return $parity . dechex($id);
}

/*
 //Set or get the prvious URL in session. This does not happen automatically, you have to put it in your controller functions where needed.
 //if $value is not supplied then it is a get action
 function osa_previous_url($value=NULL) {
 $CI = get_instance();
 if ( $value === NULL ) {
 return $CI->session->userdata(CMS_SESSION_PREVIOUS_URL);
 }
 $CI->session->set_userdata(array(CMS_SESSION_PREVIOUS_URL => $value));
 return TRUE;
 }
 */

function osa_url_history($index, $value=FALSE) {
	$CI = get_instance();
	$history = $CI->session->userdata(CMS_SESSION_URL_HISTORY);
	if ( empty($history) ) {
		$history = array();
	}
	if ( $value === FALSE ) {
		if ( array_key_exists($index, $history) ) {
			return base_url() . $history[$index];
		}
		return '';
	}
	else {
		if ( array_key_exists($index, $history) && $history[$index] == $value ) {
			return TRUE;
		}
		$history[$index] = $value;
		$CI->session->set_userdata(array(CMS_SESSION_URL_HISTORY => $history));
	}
}

//Function to create a hash value based on the input text/filename
//Added this function to fix (issue: 85)
function osa_filename_hash($str) {
	$extra = 11111; $limit = 150; $maxcount = 15;
	$len = strlen($str);
	if ( $len <= 0 ) {
		return '';
	}
	$total = 0;
	switch($len) {
		case 2:
			$a = ord($str[1]); $b = ord($str[1]); $c = ord($str[0]);
			break;
		case 1:
			$a = ord($str[0]); $b = ord($str[0]); $c = ord($str[0]);
			break;
		default:
			$a = ord($str[2]); $b = ord($str[1]); $c = ord($str[0]);
			break;
	}
	if ( $a > $limit || $b > $limit || $c > $limit ) {
		$total = $a * $b + $c;
	}
	else {
		$total = $a * $b * $c;
	}
	if ($len > $maxcount) {
		$len = $maxcount;
	}
	for ($i=3; $i<$len; $i++) {
		$total += ord($str[$i]);
	}
	return $total + $extra;
}

//Delete menu tree cookies
function osa_del_treecookies() {
	$time = time() - 3600;
	$CI = get_instance();
	$cookie_path = $CI->config->item('cookie_path');
	$cookie_path_noendslash = substr($cookie_path, 0, -1);
	setcookie('jstree_select', '',$time, $cookie_path);
	setcookie('jstree_load', '',$time, $cookie_path);
	setcookie('jstree_open', '',$time, $cookie_path);
	// For some browser that doesn't has slash at the end of cookie path
	setcookie('jstree_select', '',$time, $cookie_path_noendslash);
	setcookie('jstree_load', '',$time, $cookie_path_noendslash);
	setcookie('jstree_open', '',$time, $cookie_path_noendslash);
}

function osa_appcss_include() {
	$CI = get_instance();
	$include_path = osa_include_path();
	$string1 = '<link rel="stylesheet" href="' . $include_path . 'css/';
	$string2 = '" type="text/css" media="screen,projection" />';
	echo $string1 . 'style.css' . $string2;
	if ( $CI->config->item('cms_site_image_folder') == 'site' ) {
		echo $string1 . 'style_site.css' . $string2;
	}
	if ( osa_3p_available() && $CI->config->item('cms_3p_sys_css_filename') ) {
		echo $string1 . $CI->config->item('cms_3p_sys_css_filename') . $string2;
	}
}

function osa_appjscript_include() {
	$include_path = osa_include_path();
	echo "<script type=\"text/javascript\" src=\"{$include_path}js/sys.js\"></script>" .
		  "<script type=\"text/javascript\" src=\"{$include_path}js/app.js\"></script>";
	if ( osa_3p_available() ) {
		$CI = get_instance();
			if ( $CI->config->item('cms_3p_sys_jscript_filename') ) {
				echo "<script type=\"text/javascript\" src=\"{$include_path}js/" .
					  $CI->config->item('cms_3p_sys_jscript_filename') . "\"></script>";
		}
	}
}

//Takes out any comments before the actual SQL statement
function osa_process_sql_statement($text) {
	//These should be enough for upgrade and install. Add more as needed
	$commands = array('drop', 'create', 'alter', 'insert', 'update', 'delete', 'load', 'replace',
							'start', 'commit', 'rollback', 'set', 'lock', 'unlock');
	//Seems PHP always converts to UNIX "\n" but do it anyway here to be safe
	$text = str_replace("\r\n", "\n", $text);
	$text = str_replace("\r", "\n", $text);
	$lines = explode("\n", $text);
	$ret = '';
	$start_copy = false;
	//Find the command and start copy, this will skip all comments
	foreach ($lines as $line) {
		$line = trim($line);
		$lower = strtolower($line);
		$words = explode(' ', $lower);
		if ( count($words) > 0 && in_array($words[0], $commands) ) {
			$start_copy = true;
		}
		if ( $start_copy && $line[0] != '#') {
			$ret .= " $line";
		}
	}
	return trim($ret);
}

//Reads a SQL script and returns one sql statement at a time.
//Pass in the file name only needed during the first call.
function osa_next_sql($file=false) {
	static $index=0, $total=0;
	static $script_buffer=NULL;
	static $last_file = NULL;
	if ( $file && $last_file !== $file) {
		$index=0; $total=0; $script_buffer=NULL; $last_file = NULL;
	}
	if ( $script_buffer === NULL ) {
		if ( empty($file) ) {
			return false;
		}
		$content = file_get_contents($file);
		if ( empty($content) ) {
			return false;
		}
		$index=0;
		$script_buffer = explode(';', $content);
		$total = count($script_buffer);
		$last_file = $file;
	}
	if ( $total <= $index ) {
		return false;
	}
	$ret = '';
	while ( !$ret ) {
		if ( empty($ret) && $total > $index ) {
			$ret = osa_process_sql_statement($script_buffer[$index++]);
		}
		else {
			return false;
		}
	}
	return($ret);
}

//Gets the different resource refresh urls for unit, goal, survey and system etc.
//TODO: Should modify the ICT upload code to use this too later.
function osa_stage_refresh_urlpart($type) {
	$CI = get_instance();
	$refresh_page_config = $CI->config->item('cms_stage_refresh_url_config');
	$CI->load->library('cms/cms_template/cms_template');
	//It is safe to use type_filename() here since it is used to look up
	//URLs belonging to the templates in the template system folder
	$template_filename = $CI->cms_template->type_filename($type);
	$template_templateid = $CI->cms_template->type_templateid($type);
	if ( empty($template_filename) || empty($template_templateid) ) {
		//Guess this only applies to ICT/GC/TaIL/NETS uploads
		return '';
	}
	$ret = '';
	if ( array_key_exists($template_filename, $refresh_page_config) && ($template_templateid == CMS_TEMPLATE_SYSTEM_IDENTIFIER) ) {
		$ret = $refresh_page_config[$template_filename];
	}
	elseif ( array_key_exists($template_templateid, $refresh_page_config) ) {
		$ret = $refresh_page_config[$template_templateid];
	}
	return $ret;
}

//PHP is_int only checks for data type so write our own
function osa_is_int($str) {
	if (!is_numeric($str)) {
		return FALSE;
	}
	$int = (int) $str;
	if ( $int != $str ) {
		return FALSE;
	}
	return TRUE;
}

//Runs osa_is_int with each array element. Returns false on the first failed check.
function osa_is_int_array($array) {
	if ( !is_array($array) || count($array) <= 0 ) {
		return FALSE;
	}
	foreach($array as $value) {
		if ( !osa_is_int($value) ) {
			return FALSE;
		}
	}
	return TRUE;
}

//Like osa_is_int() but also checks to make sure 1 or greater
function osa_is_int_one($str) {
	if ( !osa_is_int($str) || $str < 1 ) {
		return FALSE;
	}
	return TRUE;
}

//Runs osa_is_int_one with each array element. Returns false on the first failed check.
function osa_is_int_one_array($array) {
	if ( !is_array($array) || count($array) <= 0 ) {
		return FALSE;
	}
	foreach($array as $value) {
		if ( !osa_is_int_one($value) ) {
			return FALSE;
		}
	}
	return TRUE;
}

function osa_clear_ci_sessions($keep_current_user=TRUE) {
	$CI = get_instance();
	$CI->load->model('admin/config_model');
	$cur_sessionid = FALSE;
	if ( $keep_current_user ) {
		$cur_sessionid = array($CI->session->userdata('session_id'));
	}
	return $CI->config_model->clear_ci_sessions($cur_sessionid);
}


//Not using this function now, but keep it here anyway
function &osa_decompress(&$var) {
	$ret = &osa_compress($var, FALSE, TRUE);
	return $ret;
}

//Compress a varaible, did this for mapping session but can be used in other places. Returns false if fails
//This function will not do anything except returing the exact input variable if no PHP compression is availabe.
function &osa_compress(&$var, $is_serialized=FALSE, $decompress=FALSE) {
	//gzdeflate, gzcompress, gzencode are the same except header and packaging...
	//gzdeflate is always preferred
	static $functions = array(
		array('gzdeflate', 'gzinflate', 1),
		array('gzcompress', 'gzuncompress', 1),
		array('gzencode', 'gzdecode', 1),
		array('bzcompress', 'bzdecompress', 1), //Very slow
	);
	if ( empty($var) ) {
		return $var;
	}
	$func_compress = '';
	$func_decompress = '';
	$compress_level = 0;
	foreach ($functions as $value) {
		if ( function_exists($value[0]) ) {
			$func_compress = &$value[0];
			$func_decompress = &$value[1];
			$compress_level = $value[2];
			break;
		}
	}

	if ( empty($func_compress) || empty($func_decompress) ) {
		//No compression available so return the orginal string
		$false = FALSE;
		return $false;
	}

	$ret = '';
	//For decompression
	if ( $decompress ) {
		$ret = $func_decompress($var);
		if ( empty($ret) || is_numeric($ret) ) {
			//Different compressions use different return code, can be false or an error number
			$false = FALSE;
			return $false;
		}
		$ret = unserialize($ret);
		return $ret;
	}
	//For compression
	if ( $is_serialized ) {
		$ret = $func_compress($var, $compress_level);
	}
	else {
		$ret = $func_compress(serialize($var), $compress_level);
	}
	if ( empty($ret) || is_numeric($ret) ) {
		$false = FALSE;
		return $false;
	}
	return $ret;
}

// This may be broken if itstd use 'school' and 'school_type' table at the same time.
// if return TRUE mean have use school_type table
function osa_itstd_use_schooltype() {
	$CI = get_instance();
	// Don't get any language string from the admin template so no error message in the log.
	$CI->config->set_item('cms_lang_ignore_all', TRUE);
	$CI->load->library('menu/menu_main');
	$template = $CI->menu_main->get_template('admin');
	$CI->config->set_item('cms_lang_ignore_all', FALSE);
	if ( !is_array($template) ) {
		osa_errorlog(__METHOD__ . '$template is not an array.');
		return FALSE;
	}
	foreach ( $template as $index ) {
		if ( !is_array($index) ){
			osa_errorlog(__METHOD__ . '$index is not an array.', $template);
			return FALSE;
		}
		foreach ( $index as $sub_index ) {
			if ( !is_array($sub_index) ) {
				osa_errorlog(__METHOD__ . '$sub_index is not an array.', $index);
				return FALSE;
			}
			if ( array_key_exists('_table', $sub_index) ) {
				//check have _table => 'school_type'
				if ( $sub_index['_table'] == 'school_type' ){
					return TRUE;
				}
			}
		}
	}
	return FALSE;
}

//Extract fields from a string
//$first_field - True to get field before delimiter, False to get field after delimiter
//$first_delimiter - True to use the first delimiter, False to use the last delimiter
//If your string contains the same delimiter multiple times then you have make sure the 2 params above are correct
function osa_str_extract($str, $delimiter, $first_field=TRUE, $first_delimiter=TRUE, $ignorecase=FALSE) {
	if ( empty($str) || empty($delimiter) ) {
		return FALSE;
	}

	if ( $first_delimiter ) {
		$strpos = 'strpos';
		if ( $ignorecase ) {
			$strpos = 'stripos';
		}
	}
	else {
		$strpos = 'strrpos';
		if ( $ignorecase ) {
			$strpos = 'strripos';
		}
	}

	$begin = $strpos($str, $delimiter);
	if ( $begin === FALSE  || ($first_field && $begin == 0) ) {
		return FALSE;
	}

	if ( $first_field ) {
		return substr($str, 0, $begin);
	}

	$delimiter_len = strlen($delimiter);
	$begin += $delimiter_len;
	$length = strlen($str);
	if ( $begin >= $length ) {
		return FALSE;
	}
	return (substr($str, $begin, $length-$begin));
}

//Reindex an array of object, useful for db results
//$result is array of object
//$index tells which object property to use as the array index, empty values will be ignored
//$data_field is the object property to assign in the array value, the default vlaue is to assign the original object
function osa_reindex_result($result, $index='id', $data_field='') {
	if ( empty($result) || !is_array($result) || count($result) <= 0 ) {
		return FALSE;
	}
	reset($result);
	$test_obj = current($result);
	if ( !is_object($test_obj) || !property_exists($test_obj, $index) ) {
		return FALSE;
	}
	if ( !empty($data_field) && !property_exists($test_obj, $data_field)) {
		return FALSE;
	}
	reset($result);
	$ret_array = array();
	//We are assuming all the objects are the same as the first one tested above so no property checking here
	foreach ($result as $data) {
		$key = $data->$index;
		if ( !empty($data_field) ) {
			$data = $data->$data_field;
		}
		$ret_array[$key] = $data;
	}
	return $ret_array;
}

//Gets the js/css include URL such as http://www.todcm.org/include/
function osa_include_path() {
	static $path = NULL;
	if ( $path === NULL ) {
		$CI = get_instance();
		$path = base_url() . $CI->config->item('cms_app_include_folder') . '/';
	}
	return $path;
}

//This gets the site/site_old URL where we store generic images - http://www.todcm.org/images/site/
function osa_imagepath() {
	static $image_path = NULL;
	if ( $image_path === NULL ) {
		$CI = get_instance();
		$folder = $CI->config->item('cms_site_image_folder');
		$image_path = osa_image_toppath() . "{$folder}/";
	}
	return $image_path;
}

//This gets the top image URL such as http://www.todcm.org/images/
function osa_image_toppath() {
	static $image_path = NULL;
	if ( $image_path === NULL ) {
		$CI = get_instance();
		$folder = $CI->config->item('cms_top_image_folder');
		$image_path = base_url() . "{$folder}/";
	}
	return $image_path;
}

//Loops thru an array of user object and assigns displayname if it doesn't exist.
//This requires $user containing fields displayname, firstname and lastname
//This does not check the fields exists or not so you have to make sure they exist before calling
function osa_assign_displayname(&$users) {
	if ( !is_array($users) ) {
		return FALSE;
	}
	foreach ( $users as $user ) {
		if ( is_array($user) ) {
			if ( empty($user['displayname']) ) {
				$user['displayname'] = osa_user_getname($user);
			}
		}
		elseif (is_object($user) )  {
			if ( empty($user->displayname) ) {
				$user->displayname = osa_user_getname($user);
			}
		}
	}
	return TRUE;
}

//Assigns the displaynames for non-exisiting ones and sort the array by the new displaynames
function osa_sort_user_displayname(&$users) {
	if ( !is_array($users) ) {
		return FALSE;
	}
	if ( count($users) <= 0 ) {
		return TRUE;
	}
	osa_assign_displayname($users);
	osa_array_natsort($users, 'displayname');
	return TRUE;
}

function osa_login_object() {
	static $CI=NULL;
	/*
	static $login_obj = null;
	if ( $login_obj === null ) {
		$CI = get_instance();
		if ( !property_exists($CI, 'login_model') ) {
			$CI->load->model('auth/login_model');
		}
		$login_obj = $CI->login_model->getLogin();
	}
	return $login_obj;
	*/
	// Caching the login object creates issues with some PDF code. Always return the latest object now.
	// Some new code also uses 2 different user logins in the same request so can't cache any more.
	if ( $CI === NULL ) {
		$CI = get_instance();
	}
	// Call the session directly and not the login_model to save a function call since this gets called frequently.
	return $CI->session->userobject(CMS_SESSION_LOGIN_OBJECT);
}

// Check if user is login or not, exit and redirect to home if not.
function osa_login_check($redirect_home=TRUE) {
	if ( !osa_login_object() ) {
		if ( $redirect_home ) {
			echo osa_ajaxmsg_redirecthome('gen_js_ajaxsessiontimeoutmsg');
		}
		exit;
	}
	return TRUE;
}

function osa_login_object_refresh() {
	$CI = get_instance();
	if ( !isset($CI->loginas_model) ) {
		$CI->load->model('admin/loginas_model');
	}
	$user = osa_login_object();
	$CI->loginas_model->refresh_login_session($user->username);
}

function osa_login_object_set($record) {
	$CI = get_instance();
	return $CI->login_model->setLogin($record);
}

// This is a hack to support email login by stripping out the email domain.
// This assumes all email addresses uses LDAP/AD usernames. The system does not enforce
// unique email addresses so can't realy on the actual emails in the system.
function osa_login_username_convert($username) {
	$CI = get_instance();
	if ( $CI->config->item('cms_login_use_email') ) {
		// This works even if a username contains a '@' char.
		if ( ($tmp = osa_str_extract($username, '@', TRUE, FALSE)) ) {
			$username = &$tmp;
		}
	}
	return $username;
}

// Override the top logo image for the site and survey login page.
function osa_login_logo_override() {
	$CI = get_instance();
	if ( $CI->config->item('cms_header_image_login') !== FALSE ) {
		$CI->config->set_item('cms_header_image', $CI->config->item('cms_header_image_login'));
		$CI->config->set_item('cms_header_image_left_align', $CI->config->item('cms_header_image_left_align_login'));
	}
}

// Our interface to CURL. Called without any parameters to get the last transfer info,
// same as curl_getinfo(), an array of statuses.
// If curl is not avaiable then fall back to file_get_contents().
// $headers is any array of name value pairs.
// $save_info, default is false, true to save info in order to get the last transfer info.
function osa_get_url_content($url=FALSE, $headers=FALSE, $timeout=15, $save_info=FALSE) {
	static $last_info = FALSE;

	if ( empty($url) ) {
		return $last_info;
	}
	$last_info = FALSE;
	if ( !osa_is_int_one($timeout) ) {
		osa_log(__FUNCTION__, 'Invalid timeout.', $timeout);
		$timeout = 15;
	}
	if ( function_exists('curl_init') && function_exists('curl_exec') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER , 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		if ( is_array($headers) && !empty($headers) ) {
			curl_setopt($ch, CURLOPT_HTTPHEADER , $headers);
		}
		$ret = @curl_exec($ch);
		if ( $save_info ) {
			$last_info = @curl_getinfo($ch);
		}
		@curl_close($ch);
		return($ret);
	}
	else {
		// Fall back to use file_get_contents() to get url content.
		if ( (is_array($headers) && !empty($headers)) || !ini_get('allow_url_fopen') ) {
			// Can't use headers or url open is off so just return false.
			osa_log(__FUNCTION__. 'Failed to run.', $url, $headers);
			return FALSE;
		}
		$orig_timeout = @ini_get('default_socket_timeout');
		@ini_set('default_socket_timeout', "$timeout");
		// Only in php.ini or httpd.conf and can't use ini_set('allow_url_fopen', '1');
		$ret = @file_get_contents($url);
		if ( osa_is_int_one($orig_timeout) ) {
			// Set the socket timeout back to the original.
			@ini_set('default_socket_timeout', $orig_timeout);
		}
		return $ret;
	}
	return FALSE;
}

//Check the URI segment against the input array of names.
//$names can be a string for a single name
function osa_is_function_name($names, $segment=3) {
	if ( empty($names) ) {
		return FALSE;
	}
	if ( !is_array($names) ) {
		$names = array($names);
	}
	$CI = get_instance();
	$uri_name = $CI->uri->segment($segment);
	return in_array($uri_name, $names);
}

function osa_valid_date_str($date) {
	if ( preg_match('/^[0-9][0-9][0-9][0-9].[01][0-9].[0-3][0-9]$/', $date) > 0 ) {
		return TRUE;
	}
	return FALSE;
}

//This does not check for admin access so you will need to check admin separately
//There are times that admin access should not be check.
function osa_is_mycourse($courseid) {
	static $CI = NULL;
	if ( $CI === NULL ) {
		$CI = get_instance();
	}
	if ( !isset($CI->course_model) ) {
		$CI->load->model('course/course_model');
	}
	return $CI->course_model->isMyCourse(FALSE, $courseid);
}

//This also checks for admin access
function osa_is_course_editor($courseid) {
	static $CI = NULL;
	if ( $CI == NULL ) {
		$CI = get_instance();
		$CI->load->model('unit/unit_model');
	}
	return $CI->unit_model->iseditor($courseid);
}

function osa_is_admin_user() {
	$user = & osa_login_object();
	if ( isset($user->accessid) && $user->accessid == CMS_ACCESS_ADMIN ) {
		return TRUE;
	}
	return FALSE;
}

//This is similiar to unit_base->check_sort but for clsses that are not unit_ classes
//This just returns one flag sort_type, if sort_type is false then it implies _sort_flag is flase.
function osa_template_sort_type(&$template) {
	if ( !is_array($template) ) {
		return FALSE;
	}
	//Sorting is off
	if ( array_key_exists('_sort_flag', $template) && !$template['_sort_flag'] ) {
		return FALSE;
	}
	//Sort type override
	if ( array_key_exists('_sort_typeid', $template) && osa_is_int_one($template['_sort_typeid']) ) {
		return $template['_sort_typeid'];
	}
	//Use default typeid.
	$typeid = FALSE;
	if ( array_key_exists('_typeid', $template) ) {
		$typeid = $template['_typeid'];
		if ( is_array($typeid) && osa_is_int_one_array($typeid) ) {
			$typeid = reset($typeid);
		}
	}
	if ( osa_is_int_one($typeid) ) {
		return $typeid;
	}
	return FALSE;
}

//Use learning targets or use outcomes
function osa_flag_use_lt() {
	$CI = get_instance();
	if ( $CI->config->item('cms_outcomes_skills_strandid') === FALSE ) {
		return TRUE;
	}
	return FALSE;
}

//This will fake a unit record for non-unit resources
function osa_get_unit_special($unitid, $type) {
	if ( !osa_is_int($unitid) || !osa_is_int_one($type) ) {
		return FALSE;
	}
	$unit = new stdClass();
	$unit->id = $unitid;
	$unit->type = $type;
	if ( $unitid != 0 ){
		$CI = get_instance();
		$CI->load->library('cms/cms_template/cms_template');
		if ( ($templateid = $CI->cms_template->type_templateid($type)) == CMS_TEMPLATE_UNIT_IDENTIFIER ){
			$CI->load->model('unti/unit_model');
			$unit = $CI->unit_model->get_unit_any($unitid);
			if ( !$unit ) {
				return FALSE;
			}
		}
		else {
			//For goal, system etc.
			//The following line will call cms_template->type_filename()
			//It is safe to do it here as long as all non-unit templates don't have conflict types
			$unit->template = $CI->cms_template->type_filename($type);
			if ( !$unit->template ) {
				return FALSE;
			}
			$unit->courseid = 0;
		}
	}
	else {
		// ICT (unitid is zero)
		//TODO: Hack, for non-unit resoruces
		//$unit->template= CMS_UBD_TEMPLATE;
		//Setting the template to fasle will get the default template from the factory
		$unit->template = FALSE;
		$unit->courseid = 0;
	}
	return $unit;
}

// all of ajax in the site now using X-Requested-With header with XMLHttpRequest
// use this function to tell is ajax or not
function osa_is_ajax() {
	static $ret=NULL;
	if ( $ret !== NULL ) {
		return $ret;
	}
	if ( isset($_REQUEST['bustcache']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ) {
		$ret = TRUE;
		return TRUE;
	}
	$ret = FALSE;
	return FALSE;
}

function osa_header_nocache(){
	static $ready = FALSE;
	if ( $ready ) {
		return;
	}
	header('Cache-Control: post-check=0, pre-check=0, no-store, no-cache, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	$ready = TRUE;
}

// $seconds - how many seconds to cache. Default is one year.
// $mod_unixtime - the last modified tiem in Unix time (integer), default is jul 26, 1997. You can use the file mod time for this.
// $revalidate - cache-control revalidate or not. The default is no revalidation. Show always use the default.
function osa_header_cache($seconds=31536000, $mod_unixtime=FALSE, $revalidate=FALSE) {
	// In case if we added pragma before calling this function.
	if ( function_exists('header_remove') ) {
		@header_remove('Pragma');
	}
	else {
		header('Pragma:');
	}

	if ( !osa_is_int_one($seconds) ) {
		$seconds = 31536000;
	}
	if ( $revalidate === TRUE ) {
		header("Cache-Control: max-age={$seconds}, public, no-transform, must-revalidate");
	}
	else {
		header("Cache-Control: max-age={$seconds}, public, no-transform");
	}
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $seconds) . ' GM');

	if ( osa_is_int_one($mod_unixtime) ) {
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mod_unixtime) . ' GMT');
	}
	else {
		header('Last-Modified: Sat, 26 Jul 1997 05:00:00 GMT');
	}
}

function osa_des_random_key($new_key=FALSE) {
	if ( !$new_key ) {
		$key = osa_des_session_key();
		if ( $key ) {
			return $key;
		}
	}
	$ret = mt_rand(100000000, 999999999) . mt_rand(100000000, 999999999) . mt_rand(100000, 999999);
	$CI = get_instance();
	$CI->session->set_userdata(CMS_SESSION_ENCRYPTION_KEY, $ret);
	return $ret;
}

function osa_des_session_key() {
	$CI = get_instance();
	return $CI->session->userdata(CMS_SESSION_ENCRYPTION_KEY);
}

function osa_des_decrypt($value) {
	$ishex = strpos($value, '0x');
	//Check to make sure it is a hex string else it was not encrypted so just return the value
	if ( empty($value) || ($ishex === FALSE || $ishex != 0) ) {
		return $value;
	}

	//Make sure we have a key in session
	$validstr = osa_des_session_key() . '_';
	$validstrlen = strlen($validstr);
	if ( $validstrlen < 24 ) {
		return FALSE;
	}

	$CI = get_instance();
	$CI->load->helper('des_helper');
	$ret = des(osa_des_session_key(), hexToString($value), 0, 0, false, false);
	$ret = str_replace(chr(0), '', $ret);
	$len = strlen($ret);
	if ( $len <= $validstrlen ) {
		//Not a valid encrypted string
		return FALSE;
	}
	$checkstr = substr($ret, 0, $validstrlen);
	if ( $checkstr != $validstr) {
		//Failed embedded string check
		return FALSE;
	}
	return substr($ret, $validstrlen, $len-$validstrlen);
}

/**
 * convert string to bytes unit
 * @link http://en.wikipedia.org/wiki/SI_prefix
 * @param string $value a number of bytes with optinal SI decimal prefix (e.g. 7k, 5mb, 3GB or 1 Tb)
 * @return integer|float A number representation of the size in BYTES.
 */
function osa_str2bytes($value) {
	if ( osa_is_int($value) ) {
		return $value;
	}
	$value = trim($value);
	$unit_byte = preg_replace('/[^a-zA-Z]/', '', $value);
	$unit_byte = strtolower($unit_byte);
	switch($unit_byte) {
		case 't':	// terabyte
		case 'tb':
			$value *= 1024;
		case 'g':	// gigabyte
		case 'gb':
			$value *= 1024;
		case 'm':	// megabyte
		case 'mb':
			$value *= 1024;
		case 'k':	// kilobyte
		case 'kb':
			$value *= 1024;
		case 'b';	// byte
			return $value *= 1;
			break; // make sure
    }
    return FALSE;
}

/**
 * The maximum file upload size by getting PHP settings
 * @return integer|float file size limit in BYTES based
 */
function osa_maximum_uploadsize(){
	static $uploadsize = NULL;
	if ( $uploadsize === NULL ){
		$post_max_size = osa_str2bytes( ini_get('post_max_size') );
		$upload_max_filesize = osa_str2bytes( ini_get('upload_max_filesize') );
		$memory_limit = osa_str2bytes( ini_get('memory_limit') );
		// Even though we disable all of variables in php.ini. These still use default value
		// Nearly impossible but check for sure
		if ( empty($post_max_size) && empty($upload_max_filesize) && empty($memory_limit) ) {
			return FALSE;
		}
		$upload_settings_array = array($post_max_size, $upload_max_filesize, $memory_limit);
		$uploadsize = min($upload_settings_array);
	}
	return $uploadsize;
}
/**
 * @param boolean $cssselector_string_format
 * @return array Each one has an array containing arrays for the 3 trees on the left menu,
 * [0] - the tree name,
 * [1] - the actual jstree html,
 * [2] - name for the other 2 trees (for hightlight deselect)
 * or
 * string A list of tree names in css selector format
 */
function osa_menu_treedata($cssselector_string_format = FALSE){
	$user = osa_login_object();
	if ( !is_object($user) ){
		return FALSE;
	}
	$params = array('user' => $user);
	$CI = get_instance();
	$CI->load->library('cms/Osa_menutree', $params);
	$course_tree = CMS_MENU_COURSE_TREE;
	$dept_tree = CMS_MENU_DEPARTMENT_TREE;
	$grade_tree = CMS_MENU_GRADE_TREE;
	$course_tree_otherdeselect = '#'.$dept_tree.",#".$grade_tree;
	$dept_tree_otherdeselect = '#'.$course_tree.",#".$grade_tree;
	$grade_tree_otherdeselect = '#'.$course_tree.",#".$dept_tree;
	$tree_init_params = array();
	$tmp = array(osa_escape_jscript_str($course_tree, FALSE), osa_escape_jscript_str($CI->osa_menutree->get_mycoursetree()), osa_escape_jscript_str($course_tree_otherdeselect, FALSE));
	$tree_init_params[$course_tree] = $tmp;
	$tmp = array(osa_escape_jscript_str($dept_tree, FALSE), osa_escape_jscript_str($CI->osa_menutree->get_depttree()), osa_escape_jscript_str($dept_tree_otherdeselect, FALSE));
	$tree_init_params[$dept_tree] = $tmp;
	$tmp = array(osa_escape_jscript_str($grade_tree, FALSE), osa_escape_jscript_str($CI->osa_menutree->get_gradetree()), osa_escape_jscript_str($grade_tree_otherdeselect, FALSE));
	$tree_init_params[$grade_tree] = $tmp;
	if ( $cssselector_string_format ) {
		$tree_array = array();
		foreach ($tree_init_params as $key=>$value) {
			$tree_array[] = '#'.$key;
		}
		$tree_names_cssselector_format = implode(',', $tree_array);
		return $tree_names_cssselector_format;
	}
	return $tree_init_params;
}

function osa_escape_jscript_str($str, $string_replace = TRUE){
	if ( $string_replace ) {
		$str = str_replace("\\", "\\\\", $str);
		$str = str_replace("'","\'",$str);
	}
	return "'{$str}'";
}
/**
 * Generate random string by using string pool
 * @param integer $length Length of string
 * @param string $strpool
 * @return string Generated string
 */
function osa_gen_randomstr($length=5, $strpool=''){
	if ( !is_string($strpool) || !osa_is_int_one($length) ) {
		return FALSE;
	}
	if ( $strpool == '' ){
		$strpool = '123456789ABCDEF';
	}
	$strlen = strlen($strpool)-1;
	if ( $strlen <= 0 ) {
		return FALSE;
	}
	$str = '';
   for ($i = 0; $i < $length; $i++) {
   	$str .= $strpool{mt_rand(0, $strlen)};
   }
	return $str;
}

//Wrapper function to access model checkaccess_all(). Default is no exit
function osa_check_access($rights, $do_exit=FALSE, $skip_functions=FALSE) {
	static $CI=NULL;
	if ( $CI === NULL ) {
		$CI = get_instance();
	}
	if ( $skip_functions ) {
		if ( osa_is_function_name($skip_functions) ) {
			return TRUE;
		}
	}
	if ( !$do_exit ) {
		return $CI->access_model->checkaccess_all($rights, FALSE, TRUE, FALSE);
	}
	// Exists if no access.
	$CI->access_model->checkaccess_all($rights, FALSE, TRUE, TRUE);
	return TRUE;
}

/**
 * Add item to list with delimeter
 * @param string $list A list with delimeter
 * @param string $item
 * @param integer $max_item_length maximum item length
 * @param integer $max_item_storage Default is 10k = 10240
 * @param string $delimeter Default is ',' (punctuation mark)
 * @return A list of string with delimeter
 */
function osa_add_item2list($list, $item, $max_item_length ,$max_item_storage=10240 ,$delimeter=','){
	$list = trim($list);
   $item = trim($item);
	if ( empty($item) || strlen($item) > $max_item_length ) {
   	osa_log(__METHOD__, 'Invalid input.', $item);
   	return $list;
   }
   if ( empty($list) ) {
   	return $item;
   }
   $itemarray = explode($delimeter, $list);
   if ( in_array($item, $itemarray) ) { // if item already in list, do nothing
		return $list;
   }
   // Needs one more for the delimeter
   $item_length = strlen($item) + 1;
	$templist = $list;
	if ( (strlen($list) + $item_length) > $max_item_storage ) {
		// Not enough storage so purge old ones
		$count = 0;
		while (TRUE) {
			$templist = osa_str_extract($templist, $delimeter, FALSE);
			if ( (strlen($templist) + $item_length) <= $max_item_storage ) {
				break;
			}
			if ( $count++ > 100 ) {
				// Just in case something goes wrong
				osa_log(__METHOD__, 'Looped too many times, just return the original list.', $item, $list);
				return $list;
			}
		}
	}
	return $templist . $delimeter . $item;
}

/**
 * check item in list, related to osa_add_item2list function
 * @param string $item
 * @param string $list A list with delimeter
 * @param string $delimeter Default is ',' (punctuation mark)
 * @return boolean
 */
function osa_in_itemlist($item, $list, $delimeter=','){
	if ( empty($list) || empty($item) || empty($delimeter) ){
		return FALSE;
	}
	$listarray = explode($delimeter, $list);
   if( in_array($item, $listarray) ){
   	return TRUE;
   }
   return FALSE;
}

//Helper function to interface with pure PHP objects in TODCM.
function osa_pureobject($classpath, $include_only=FALSE) {
	static $CI = NULL;
	if ( $CI === NULL ) {
		$CI = get_instance();
		$CI->load->library('pureobject/pureobject');
	}
	if ( !$include_only ) {
		return $CI->pureobject->get($classpath);
	}
	return $CI->pureobject->include_class($classpath);
}

/**
 * Highlight a string with one or more replacement patterns.
 * @param string $str_orig
 * 	The orginal string to highlight.
 * @param string|array $patterns
 * 	One or more replacement strings/patterns.
 * @param string $color
 * 	The highlight color. Can use word 'red', 'blue' or hex code '#a5ce05'. Default is red.
 */
function osa_str_highlight($str_orig, &$patterns, $color='red'){
	// if an array of strings to highlight is empty, return the old string
	if ( !empty($patterns) && (is_array($patterns) || is_string($patterns)) ) {
		if ( is_string($patterns) ){
			// if type of string to be highlight is STRING, convert to an array
			$patterns = array($patterns);
		}
		$mod_patterns = array();
		foreach (array_keys($patterns) as $key) {
			$mod_patterns[$key] = "/({$patterns[$key]})/i";
		}
		return preg_replace($mod_patterns, "<font color=\"{$color}\"><b>\\1</b></font>", $str_orig);
	}
	return $str_orig;
}

// Function to add/update CI config items to the app_config table
function osa_config_update2db($item, $value, $config_index=FALSE, $update_memory=TRUE) {
	static $CI=NULL;
	if ( $CI === NULL ) {
		require_once APPPATH . 'libraries/admin/config_data.php';
		$CI = get_instance();
		$CI->load->model('admin/config_model');
	}
	if ( !$config_index ) {
		$config_index = config_data::SYSTEM_GENERAL;
	}
	$ret = $CI->config_model->update($config_index, $item, $value);
	if ( $ret && $update_memory ) {
		$CI->config->set_item($item, $value);
	}
	return $ret;
}

// Adds a terminating slash if there isn't one
function osa_str_addslash(& $str) {
	if ( empty($str) || !is_string($str) ) {
		return FALSE;
	}
	$len = strlen($str) - 1;
	if ( $len >= 0 ) {
		$char = substr($str, $len, 1);
		if ( $char != '/' && $char != '\\') {
			$str .= '/';
		}
		return TRUE;
	}
	return FALSE;
}

// Gets the path name for Windows or Linux also takes our extra slashes.
function osa_path_os($path) {
	if ( DIRECTORY_SEPARATOR == '\\' ) {
		$path = str_replace('/', '\\', $path);
		$path = str_replace('\\\\', '\\', $path);
	}
	else {
		$path = str_replace('//', '/', $path);
	}
	return $path;
}

// For performance reason, we put the 3rd party check here as a helper function
// This will also load the cms_3p library if necessary.
function osa_3p_available($reset_value=NULL) {
	static $CI = NULL;
	static $ret = FALSE;

	if ( $CI === NULL ) {
		$CI = get_instance();
		if ( $CI->config->item('cms_3p_truned_off') ) {
			return FALSE;
		}
		$ret = $CI->config->item('cms_3p_available');
		if ( $ret && !isset($CI->cms_3p) ) {
			$CI->load->library('cms/Cms_3p');
		}
	}
	elseif ( $reset_value !== NULL ) {
		// The above code does reset if $CI is null.
		$ret = $reset_value;
	}

	return $ret;
}

// Check to see if a 3rd party module is present
// $modules can be a module name string, module name constant string or array or module names.
// If $modules is an array then any one of the libraries fails the check then this will return false.
function osa_3p_valid_module($modules, $group=FALSE) {
	if ( !osa_3p_available() ) {
		return FALSE;
	}
	static $CI=NULL;
	if ( $CI === NULL ) {
		$CI = get_instance();
		if ( !isset($CI->cms_3p) ) {
			$CI->load->library('cms/Cms_3p');
		}
	}
	if ( !is_array($modules) && defined($modules) ) {
		$modules = constant($modules);
	}
	return $CI->cms_3p->valid_module($modules, $group);
}

// Check library existance for 3rd party UPH modules
function osa_3p_valid_uph($libname, $do_include=TRUE) {
	// osa_3p_valid_module() also calls osa_3p_available() but checking it here first
	// results less execution for sites without 3p.
	if ( !osa_3p_available() ) {
		return FALSE;
	}
	$ret = osa_3p_valid_module($libname, 'uph');
	if ( $ret && $do_include ) {
		$filename = APPPATH . "third_party/uph/{$libname}/libraries/uph_{$libname}" . EXT;
		return include_once($filename);
	}
	return $ret;
}

// Load a 3rd party library.
function osa_3p_load_lib($lib, $module=FALSE, $params=NULL) {
	static $CI=NULL;
	if ( !osa_3p_available() ) {
		return FALSE;
	}
	// TODO: Make all callers to pass quoted constants and take out the strpos and defined functions.
	if ( strpos($lib, 'CMS_3P_') === 0 && defined($lib) ) {
		$lib = constant($lib);
	}
	if ( $module === FALSE ) {
		$module = 'CMS_3P_FOLDER_COMMON';
	}
	if ( strpos($module, 'CMS_3P_') === 0 && defined($module) ) {
		$module = constant($module);
	}
	if ( $CI === NULL ) {
		$CI = get_instance();
	}
	return $CI->cms_3p->load_lib($lib, $module, $params);
}

// Load a 3rd party model.
function osa_3p_load_model($model, $module=FALSE) {
	static $CI=NULL;
	if ( !osa_3p_available() ) {
		return FALSE;
	}
	if ( $module === FALSE ) {
		$module = 'CMS_3P_FOLDER_COMMON';
	}
	$module = constant($module);
	$model = constant($model);
	if ( $CI === NULL ) {
		$CI = get_instance();
	}
	return $CI->cms_3p->load_model($model, $module);
}

/*
 * Actual function for osa_3p_url_tp and osa_3p_url_uph
 */
function osa_3p_url_path($use_base_url, $function, $args) {
	static $CI=NULL;
	static $url_path=NULL;
	if ( !osa_3p_available() ) {
		return FALSE;
	}
	if ( $CI === NULL ) {
		$CI = get_instance();
	}
	if ( $use_base_url ) {
		if ( $url_path === NULL ) {
			$url_path = base_url();
		}
		$url = "{$url_path}tp/{$function}/";
	}
	else {
		$url = "tp/{$function}/";
	}
	foreach ($args as $key=>$value) {
		switch ($key) {
			case 0:
				$module = dirname($value);
				$controller = basename($value);
				if ( !$module || !$controller ) {
					return FALSE;
				}
				$module_path = $CI->cms_3p->find_module_from_config($module);
				if ( !$module_path ) {
					return FALSE;
				}
				$url .= str_replace('/', '-', $module_path) . "/$controller";
				break;
			default:
				$url .= $value;
		}
		$url .= '/';
	}
	return $url;
}

/*
 * Get url path for 3p URL. You are required to use this function for all 3rd party application URLs
 * $apppath has to be 'MODULENAME/CONTROLLER' e.g. 'unitzip/controller'. The code will find the actual path based on MODULENAME.
 * The default value of $functionname is actually never used due to how
 * func_get_args() works. You can add as many additional params you like. Example:
 *
 * osa_3p_url_tp('test') => http://host/tp/run/test/
 * osa_3p_url_tp('unitzip/controller','index',1,2) => http://host/tp/run/misc-unitzip/controller/index/1/2/
 */
function osa_3p_url_tp($apppath, $functionname='index') {
	$args = func_get_args();
	return osa_3p_url_path(TRUE, 'run', $args);
}
function osa_3p_url_tp_nobase($apppath, $functionname='index') {
	$args = func_get_args();
	return osa_3p_url_path(FALSE, 'run', $args);
}

/*
 * Get url path for 3p uph URLs. Refer to osa_3p_url_tp() for detail.
 */
function osa_3p_url_uph($apppath, $functionname='index') {
	$args = func_get_args();
	return osa_3p_url_path(TRUE, 'uph', $args);
}
function osa_3p_url_uph_nobase($apppath, $functionname='index') {
	$args = func_get_args();
	return osa_3p_url_path(FALSE, 'uph', $args);
}

// Provides the 3rd party image URL. This is needed since the 3p image top folder name is a random name saved in config.
function osa_3p_image_url($module, $file) {
	static $CI=NULL;
	static $tpfolder;
	static $toppath;
	if ( !osa_3p_available() ) {
		return '';
	}
	if ( $CI === NULL ) {
		$CI = get_instance();
		$tpfolder = $CI->config->item('cms_3p_sys_image_foldername');
		$toppath = osa_image_toppath();
	}
	if ( !empty($tpfolder) ) {
		return "{$toppath}{$tpfolder}/$module/$file";
	}
	return '';
}

// Helper for generating the builder/template bulk zip file download.
function osa_3p_download_button($params, $filesuffix) {
	$CI = get_instance();
	try {
		if ( osa_3p_available() &&
			  ($untizip_lib = $CI->cms_3p->load_lib(CMS_3P_LIB_UNITZIP_UTIL, CMS_3P_FOLDER_UNITZIP)) ) {
			return $untizip_lib->download_button($params, $filesuffix);
		}
	}
	catch (cms_exception $e) {}
	return FALSE;
}

// A simplified token security add function.
// $groupname - A unique group name for the resource. Make sure this is not in conflict with any template file name.
// $primaryid - This can be any DB ID
// $secondaryid - Optional secondary DB ID. Default is to use dummy id 1.
// A sample primary id is course id and sample sec. id is unit id.
function osa_security_add($groupname, $primaryid, $secondaryid=1) {
	static $CI=NULL;
	if ( $CI === NULL ) {
		$CI = get_instance();
		if ( !property_exists($CI, 'cms_security') ) {
			$CI->load->library('cms/cms_security');
		}
	}
	$CI->cms_security->init(CMS_ACTION_EDIT, $groupname, $secondaryid, $primaryid);
	$CI->cms_security->has_full_security(CMS_ACTION_EDIT, TRUE);
}

// Validate a security token.
function osa_security_validate($groupname, $primaryid, $secondaryid=1, $type=FALSE, $resourceid=FALSE) {
	static $CI=NULL;
	if ( $CI === NULL ) {
		$CI = get_instance();
		if ( !property_exists($CI, 'cms_security') ) {
			$CI->load->library('cms/cms_security');
		}
	}
	return $CI->cms_security->validate(CMS_ACTION_EDIT, $groupname, $primaryid, $secondaryid, $type, $resourceid);
}

function osa_buffered_output($html) {
	if ( $html ) {
		$CI = get_instance();
		$data = array();
		$data['html'] = &$html;
		$CI->load->view('template/html_output_view', $data);
	}
}

// Check for pdf mode based on the pdf header key. This key never leaves the server so it is very safe.
// External PDF generation can send this key to the internet between 2 servers but who is going to catch this...
function osa_pdf_mode() {
	static $CI=NULL, $flag=FALSE;
	if ( $CI === NULL ) {
		$CI = get_instance();
		$header_index = 'HTTP_' . CMS_PDF_HEADER_VAR_UPCASE;
		if ( (isset($_COOKIE[CMS_PDF_HEADER_VAR]) && strcmp(osa_pdf_header_key(), $_COOKIE[CMS_PDF_HEADER_VAR]) === 0) ||
			(isset($_SERVER[$header_index]) && strcmp(osa_pdf_header_key(), $_SERVER[$header_index]) === 0) ) {
			// 3p inlineimage needs cookie to work and header variable didn't work. So, use both to make sure
			// they work. Looks like wkhtmltopdf does not send supplied header variables for additonal external
			// requests such as a image request but it does send the cookie for all requests.
			$flag = TRUE;
		}
	}
	return $flag;
}

function osa_pdf_header_key() {
	static $CI=NULL, $key;
	if ( $CI === NULL ) {
		$CI = get_instance();
		$key = md5($CI->config->item('encryption_key') . $CI->config->item('cms_data_folder'));
	}
	return $key;
}

function osa_pdf_mode_check() {
	if ( !osa_pdf_mode() ) {
		echo '<center><font color="red"><h2>' . lang('gen_pdf_invalid_request') . '</h2></font></center>';
		exit;
	}
	return TRUE;
}

function osa_url_full($url) {
	if ( empty($url) ) {
		return FALSE;
	}
	$url = trim($url);
	return ( stripos($url, 'http://') === 0 || stripos($url, 'https://') === 0 );
}

function osa_db_bool_value($value) {
	if ( $value ) {
		return CMS_DB_BOOLEAN_TRUE;
	}
	return CMS_DB_BOOLEAN_FALSE;
}

// Call post controller method if required before exit.
function osa_exit($status=0) {
	$CI = get_instance();
	// TODO: We only check for cms_security since this is only thing gets processed
	// in the post controller method. Need to modify this if new conditions are added.
	if ( property_exists($CI, 'cms_security') ) {
		$CI->load->library('cms/hook/Cms_post_controller');
		$CI->cms_post_controller->run();
	}
	exit($status);
}

// Wrapper to CI's get_mime_by_extension. If CI can not provide a mime then use octet-stream.
function osa_get_mime_by_extension($filename) {
	static $CI = NULL;
	if ( $CI === NULL ) {
		$CI = get_instance();
		$CI->load->helper('file');
	}
	if ( !($mime = get_mime_by_extension($filename)) ) {
		$mime = 'application/octet-stream'; // 'application/download';
	}
	return $mime;
}

// This file can override all configs.
function osa_config_override_filepath() {
	$CI = get_instance();
	if ( !$CI->config->item('cms_data_folder') || !$CI->config->item('cms_siteid') ) {
		return FALSE;
	}
	$config_dir = $CI->config->item('cms_data_folder') . 'config/';
	if ( !is_dir($config_dir) ) {
		if ( !(@mkdir($config_dir)) ) {
			return FALSE;
		}
	}
	return $config_dir . 'config_override_' . $CI->config->item('cms_siteid') . '.php';
}

// Reads a file with a predefined buffer size and echo to the browser. This is a 2-step process:
// First, call this function with the parameters and this will return the file size on success or fasle if failed.
// Warning, by default this discards all PHP buffers created prior to calling this function.
// So, make sure HTML headers are placed after this call.
// Second, call this function without any parameters and it will echo the file that was opended from the first step.
// Return statuses are true with success or false when failed.
// Use PHP readfile() for files known to be small like a cached HTML output.
function osa_file_buffered_read($filepath=FALSE, $discard_php_buffers=TRUE, $buffer_size=CMS_DOWNLOAD_BUFFER_SIZE, $mode='rb') {
	static $handle=FALSE;
	static $s_buffer_size;

	if ( empty($filepath) ) {
		// Second operation to echo file content to the browser.
		if ( !empty($handle) ) {
			while( feof($handle) === FALSE ) {
				$readbuffer = fread($handle, $s_buffer_size);
				if ( $readbuffer === FALSE ) {
					$handle = FALSE;
					return FALSE;
				}
				echo $readbuffer;
			}
			flush();
			fclose($handle);
			$handle = FALSE;
			return TRUE;
		}
	}
	else {
		// First operation to open the file and return the file size.
		$handle = @fopen($filepath, $mode);
		if ( empty($handle) ) {
			return FALSE;
		}
		$s_buffer_size = $buffer_size;
		$filestat = fstat($handle);
		if ( !isset($filestat['size']) ) {
			return FALSE;
		}
		// Disacard all existing PHP buffers.
		if ( $discard_php_buffers ) {
			while (@ob_end_clean());
		}
		return $filestat['size'];
	}
	return FALSE;
}

/*
function osa_load_lib($lib=false, $prefix=false, $islibrary=true) {
	if ($prefix === false) {
		$prefix = 'unit';
	}
	if ($lib === false) {
		return FALSE;
	}
	$class = $prefix.'/'. $lib;
	$CI =get_instance();
	if ($islibrary === true) {
		$CI->load->library($class);
	}
	else {
		$CI->load->$islibrary($class);
	}
	if (!isset($CI->$lib) || !$CI->$lib) {
		return FALSE;
	}
	$lib = strtolower($lib);
	return $CI->$lib;
}
*/

/*
// Add a binary flag. $value is passed by reference and stores the result,
// the return code indicates success or not
function osa_flag_add(&$value, $flag) {
	if ( !osa_is_int($value) || !osa_is_int_one($flag) ) {
		return FALSE;
	}
	$value |= $flag;
	return TRUE;
}

// Test a binary flag. Retruns true is set and false if not set
function osa_flag_isset($value, $flag) {
	if ( !osa_is_int_one($value) || !osa_is_int_one($flag) ) {
		return FALSE;
	}
	$result = $value & $flag;
	return ($result > 0);
}
*/

/*
//Test to see if CI config has been already defined. We can't rely on testing false from config->item()
//since a defined value can be false but undefined config also returns false so we need this function
function osa_config_defined($item) {
	static $CI = NULL;
	static $ci_config = NULL;
	//We want to check $CI instead of $ci_config since we are sure $CI will alway avaiable but not $ci_config
	if ( $CI === NULL ) {
		$CI = get_instance();
		if ( property_exists($CI, 'config') && property_exists($CI->config, 'config') &&
			  is_array($CI->config->config) && count($CI->config->config) > 0 ) {
			$ci_config = & $CI->config->config;
		}
		else {
			osa_log(__FUNCTION__, '$CI->config->config is not defined or not accessible.', $item);
			echo('Aborted in function ' . __FUNCTION__ . '(), $CI->config->config is not defined or not accessible.');
			//This is really bad so we have to exit. We should always catch this in development before releasing to the public.
			exit;
		}
	}
	if ( $ci_config === NULL || empty($item) ) {
		//We know there was problem getting the CI config array so just return FALSE
		//If CI chnages the array $CI->config->config to non public then have to chnage the code in this function
		return FALSE;
	}

	return array_key_exists($item, $ci_config);
}

//This is similiar to $CI->config->set_item() with additional 3rd parameter to
//specify to overwrite old value or not. Returns true if set else returns false
function osa_config_set($item, $value, $override=TRUE) {
	static $CI = NULL;
	if ( !$override && osa_config_defined($item) ) {
		return FALSE;
	}
	if ( $CI === NULL ) {
		$CI = get_instance();
	}
	$CI->config->set_item($item, $value);
	return TRUE;
}
*/
