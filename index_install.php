<?php
/**
 * index_install.php - Top install index file.
 *
 * @author $Author: $
 * @version $Id: $
 * @copyright Copyright (c) 2016, STEPS, CSUMB Capstone
*/
$has_htaccess = file_exists('.htaccess');
if ( $has_htaccess && file_exists('index_ci.php') && file_exists('config.php') ) {
	// Just run the CI system.
	// This is for people who don't follow instructions :( and
	// didn't copy the index_ci.php to index.php during the STEPS upgrade.
	// So, every request will need to do 3 addtional file checks and one file open.
	// This is slower but won't affect much in a single-school/org env.
	// Good thing is no one would notice this, it's foolproof.
	include_once('index_ci.php');
	exit;
}
elseif ($has_htaccess) {
   $file = getcwd() . DIRECTORY_SEPARATOR . '.htaccess';
	// Install can't run with .htaccess hanging around.
	echo "<br /><br /><H3>Please delete $file if you need a new installation of STEPS.</H3>";
	exit;
}
elseif ( !file_exists('index_ci.php') ) {
   $file = getcwd() . DIRECTORY_SEPARATOR . 'index_ci.php';
   echo "<br /><br ><H3>File $file is missing, please fix this.</H3>";
}
else {
	// Redirect to the install
	header('Location:install');
	exit;
}
