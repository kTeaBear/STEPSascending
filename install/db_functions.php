<?php
/**
 * db_functions.php - Like the name suggests, all DB functions.
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
if (!defined('INSTALL_PROCESS')) exit('No direct script access allowed');

class db_functions {
	private $connect = FALSE;
	//try to pick the non-generic names
	private $schema_dirty_tables = array('ci_sessions', 'course_sis', 'resource_url', 'unit_search_cache1', 'user_course');

	//Connect to a database
	function connect($user, $password, $host, $schema=false) {
		if ( !($this->connect = @mysql_connect($host, $user, $password)) ) {
			return false;
		}
		if ( $schema === false ) {
			return $this->connect;
		}
		if ( mysql_select_db($schema, $this->connect) ) {
			return $this->connect;
		}
		$this->connect = false;
		return false;
	}

	function close($connect) {
		@mysql_close($connect);
		$this->connect = FALSE;
	}

	//Selects the default schema
	function select_db($schema, $connect=false) {
		if ( !$connect ) {
			$connect = $this->connect;
		}
		if ( $connect ) {
			return  mysql_select_db($schema, $connect);
		}
		return false;
	}

	//Run various sqls to check for schema access
	function test_db_access($connect=false) {
		if ( !$connect ) {
			$connect = $this->connect;
		}
		if ( !$connect ) {
			return false;
		}
		//This should test all necessary db permissions
		$sql = array();
		$table = '_steps_test_please_delete_';
		$sql[] = "drop table if exists $table";
		$sql[] = "create table $table (id int)";
		$sql[] = "alter table $table modify column id varchar(10)";
		$sql[] = "insert into $table values ('1')";
		$sql[] = "update $table set id='2'";
		$sql[] = "select * from $table";
		$sql[] = "drop table $table";
		foreach ($sql as $sql_str) {
			$ret = @mysql_query($sql_str);
			if ( !$ret ) {
				//Try to cleanup anyway
				@mysql_query("drop table $table", $connect);
				return false;
			}
		}
		return true;
	}

	//Checks a sample of the STEPS tables and see is the schema populated already
	function is_schema_dirty($schema, $connect=false) {
		if ( !$connect ) {
			$connect = $this->connect;
		}
		if ( !$connect ) {
			return false;
		}
		$this->select_db($schema, $connect);
		$sql = 'show tables';
		$resource = mysql_query($sql, $connect);
		$tables = array();
		if ( $resource ) {
			while ( $row = mysql_fetch_row($resource) ) {
				$tables[] = $row[0];
			}
		}
		$match_count = 0;
		$match_total = count($this->schema_dirty_tables);

		foreach ($this->schema_dirty_tables as $check_table) {
			if ( $match_count >= $match_total ) {
				break;
			}
			foreach ($tables as $db_table) {
				//Not doing a string comparison or use array search so this will continue to work when we add DB table prefix support
				if ( strpos($db_table, $check_table) !== false ) {
					$match_count++;
					break;
				}
			}
		}

		//4 out of 5 matches is dirty
		if ( $match_count >= ($match_total-1) ) {
			return '1';
		}
		return '0';
	}

	//For running DB queries from the DB script
	function steps_query($sql, $connect) {
		$sql = trim($sql);
		$pos = stripos($sql, 'alter');
		$return_true = false;
		if ( $pos === 0 ) {
			//Some alter statements results error due to unspported DB constraints with MyISAM.
			//So, ignore all alters
			$return_true = true;
		}
		$ret = mysql_query($sql, $connect);
		if ( $return_true ) {
			return true;
		}
		return $ret;
	}

	private function r_user_exists($db_username) {
		$db_username = mysql_real_escape_string($db_username);
		$sql = "select user,host from mysql.user where user='$db_username'";
		$resource = mysql_query($sql, $this->connect);
		if ( mysql_num_rows($resource) > 0 ) {
			// We need to get all the hosts due to (issue: 312).
			$ret = array();
			while ( $row = mysql_fetch_assoc($resource) ) {
				$ret[] = $row['host'];
			}
			return $ret;
		}
		return false;
	}

	// Reset password for existing db user account. This accoutn can come from the user.
	private function r_set_password($db_username, $db_password, $hosts) {
		if ( empty($hosts) || !is_array($hosts) ) {
			return false;
		}
		// This sets the password for all available hosts associated with the supplied user.
		$db_password = mysql_real_escape_string($db_password);
		$db_username = mysql_real_escape_string($db_username);
		$sql = "update mysql.user set password=password('{$db_password}') where user='{$db_username}'";
		if (!mysql_query($sql, $this->connect)) {
			$this->print_error(__METHOD__, $sql);
			return false;
		}
		if ( !mysql_query('flush privileges', $this->connect) ) {
			$this->print_error(__METHOD__, 'flush privileges');
			return false;
		}
		// If someone is installing on a separate DB server then he/she better know how to create
		// a db user properly so not checking here. Too much work to make this 100% fool-proof.
		// Shared-hosted sites would never get here.
		return true;
	}

	private function r_create_schema($db_schema) {
		$sql = "create database $db_schema CHARACTER SET " . MYSQL_CHARSET . " COLLATE " . MYSQL_COLLATE;
		$ret = mysql_query($sql, $this->connect);
		if ( !$ret ) {
			$this->print_error(__METHOD__, $sql);
		}
		return $ret;
	}

	private function r_create_user($db_username, $db_password) {
		$db_password = mysql_real_escape_string($db_password);
		$db_username = mysql_real_escape_string($db_username);
		// (issue: 312) Need to add host localhost else if DB has an anonymous for localhost then it would break.
		// So we add the user for localhost and then host wildcard.
		$sql_temp = "create user '$db_username'@'%s' identified by '$db_password'";
		$hosts = $this->get_client_hosts();
		if ( !empty($hosts) ) {
			foreach($hosts as $host) {
				$sql = sprintf($sql_temp, $host);
				if ( !mysql_query($sql, $this->connect) ) {
					$this->print_error(__METHOD__, $sql);
					return false;
				}
			}
		}
		// The wildcard one.
		$sql = "create user '$db_username' identified by '$db_password'";
		if ( mysql_query($sql, $this->connect) ) {
			if ( !mysql_query('flush privileges', $this->connect) ) {
				$this->print_error(__METHOD__, 'flush privileges');
				return false;
			}
			return true;
		}
		$this->print_error(__METHOD__, $sql);
		return false;
	}

	private function r_assign_privillage($db_username, $db_schema) {
		$db_username = mysql_real_escape_string($db_username);
		// (issue: 312) Have to grant to user for localhost and host wildcard.
		$sql_temp = "grant all privileges on {$db_schema}.* to '{$db_username}'@'%s'";
		$hosts = $this->get_client_hosts();
		$host_ret = FALSE;
		if ( !empty($hosts) ) {
			foreach($hosts as $host) {
				$sql = sprintf($sql_temp, $host);
				if ( @mysql_query($sql, $this->connect) ) {
					// $this->print_error(__METHOD__, $sql);
					$host_ret = TRUE;
				}
			}
		}
		// Wildcard one.
		$sql = "grant all privileges on {$db_schema}.* to '{$db_username}'";
		$ret = @mysql_query($sql, $this->connect);
		if ( !mysql_query('flush privileges', $this->connect) ) {
			$this->print_error(__METHOD__, 'flush privileges');
			return false;
		}
		if ( !$ret && !$host_ret ) {
			$this->print_error(__METHOD__, $sql);
		}
		// Existing user might not have both localhost and % so one is good enough.
		return ($ret || $host_ret);
	}

	function set_steps_password($user, $password, $cms_password_key, $connect) {
		$user = mysql_real_escape_string($user);
		$password = md5("{$password}{$cms_password_key}");
		//$sql = "update user set password='{$password}' where username='$user'";
		$sql = "insert into user " .
				 "(id,auth_id,accessid,enabled,username,password,firstname,lastname,displayname,autogen) " .
				 "values (1,1,1,1,'{$user}','{$password}','Admin','User','Admin User',0)";
		if ( !($ret = mysql_query($sql, $connect)) ) {
			$this->print_error(__METHOD__, $sql);
		}
		return $ret;
	}

	function setup_user($db_username, $db_password, $db_schema, $root_connection) {
		$this->connect = $root_connection;
		//Process user account
		if ( $db_username != 'root' ) {
			if ( ($hosts = $this->r_user_exists($db_username)) ) {
				if (!$this->r_set_password($db_username, $db_password, $hosts)) {
					return false;
				}
			}
			else {
				if ( !$this->r_create_user($db_username, $db_password) ) {
					return false;
				}
			}
		}
		//Process schema
		if ( !$this->select_db($db_schema) ) {
			if ( !$this->r_create_schema($db_schema) ) {
				return false;
			}
		}

		//Assign privillages. Always assign the privillages no matter what happened above.
		if ( $db_username != 'root' ) {
			if ( !$this->r_assign_privillage($db_username, $db_schema) ) {
				return false;
			}
		}
		return true;
	}

	function innodb_enabled() {
		if ( $this->connect ) {
			$sql = 'show variables like \'have_innodb\'';
			$resource = mysql_query($sql, $this->connect);
			if ( $resource ) {
				while ( $row = mysql_fetch_row($resource) ) {
					if ( is_array($row) && isset($row[0]) && isset($row[1]) ) {
						if ( strtolower($row[0]) == 'have_innodb' && strtolower($row[1]) == 'yes' ) {
							return true;
						}
					}
				}
			}
		}
		return false;
	}

	// (issue: 312)
	function has_anonymous_user() {
		if ( $this->connect('', '', 'localhost') ) {
			return true;
		}
		$this->connect = false;
		return false;
	}

	// Need this for (issue: 312).
	private function get_client_hosts() {
		// Always add localhost and local IPV4 and IPV6 IPs.
		// $ret = array('localhost', '127.0.0.1', '::1');
		$ret = array('localhost');
		/*
		// Get the web server hostname. This is almost unnecessary but just to make sure.
		if ( function_exists('gethostname') ) {
			// PHP 5.3+
			$current_host = gethostname();
		}
		else {
			// Before 5.3.
			$current_host = php_uname('n');
		}
		if ( strtolower($current_host) != 'localhost' ) {
			$ret[] = $current_host;
		}*/
		// We have everything covered.
		return $ret;
	}

	private function print_error($function, $value) {
		echo "<font color=\"red\"><br />Failed to run query in {$function}():<br />$value<br /></font>";
	}

/*
	function set_key_check($connection, $action) {
		$sql = 'set foreign_key_checks=0';
		if ( $action ) {
			$sql = 'set foreign_key_checks=1';
		}
		@mysql_query($sql, $connection);
	}
*/
}
