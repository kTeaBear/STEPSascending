<?php
/**
 * osautil_ext_helper.php - Functions came from other sources.
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/

// Tells a file expired or not, the time precision can be serveral seconds depending on server load.
function osa_file_expired($filepath, $expire_seconds) {
	static $currenttime=NULL;
	if ( $currenttime === NULL ) {
		// We don't expect a request would run too long like more than a second.
		// If you need precision then please don't use this function.
		$currenttime = time();
	}
	$stat = stat($filepath);
	if ( !$stat || !isset($stat['mtime']) ) {
		// This should never happen but if it does then do nothing.
		return FALSE;
	}
	if ( ($currenttime - $stat['mtime']) > $expire_seconds ) {
		return TRUE;
	}
	return FALSE;
}

/*
 * Delete a directory and all its files and sub-directories. Supports time checking on files.
 *
 * $keepparent - Set to true to retain top most parent. $keepempty has to be false if deleting parent.
 * $checktime - In seconds, only delete a file that is older than this time (stat mtime).
 * 				 False will always delete. Does not apply to directories.
 * $keepempty - Keep empty directories or not.
 * $pattern - file/folder pattern.
 * $echo - echo a dot on every echocount entry.
 */
function osa_remove_directory($directory, $checktime=FALSE, $keepparent=FALSE, $keepempty=FALSE, $pattern='*', $echocount=FALSE) {
	static $count=0;
	if ( $echocount && ($count++ > $echocount) ) {
		echo '.';
		$count = 0;
	}
	$base = basename($directory);
	if ( $directory == '/' || $directory == "\\" || $base == '.' || $base == '..' ) {
		return FALSE;
	}
	if ( !is_dir($directory) || !is_readable($directory)) {
		// if the path is not valid or is not a directory.
		return FALSE;
	}
	
	$nodes = glob($directory . DIRECTORY_SEPARATOR . $pattern, GLOB_NOSORT|GLOB_BRACE);
	if ( $nodes ) {
		foreach ( $nodes as $path ) {
			if ( is_dir($path) ) {
				// we call this function with the new path, notice the 3rd param is always false,
				// only the first iteration can have true and this is what makes the flag only applies to the top most directory.
				osa_remove_directory($path, $checktime, FALSE, $keepempty, $pattern, $echocount);
			}
			// if the new path is a file
			else {
				// we remove the file if no need to check time or the file has expired.
				if ( !$checktime || osa_file_expired($path, $checktime) ) {
					@unlink($path);
				}
			}
		}
	}
	
	// Delete the directory passed to the current iteration.
	if ($keepparent == FALSE && $keepempty == FALSE) {
		// try to delete the now empty directory
		if (!@rmdir($directory) && $checktime === FALSE && $pattern == '*') {
			// return false if not possible. $checktime or $pattern can make this fail so ignore.
			return FALSE;
		}
	}
	// return success
	return TRUE;
}

function osa_copy_directory($source, $dest) {
	if ( !is_dir($source) ) {
		return FALSE;
	}
	if (osa_copy_directory_actual($source, $dest) === FALSE) {
		osa_remove_directory($dest);
		return FALSE;
	}
	return TRUE;
}

/*
 * Skips copying any folder named '11deletedxx'
 * This was modified for better error handling and better file/directory permission settings
 */
function osa_copy_directory_actual($source, $dest){
	if(substr($source,-1) == '/')
		$source = substr($source,0,-1);
	if(substr($dest,-1) == '/')
		$dest = substr($dest,0,-1);
	// Simple copy for a file
	if (is_file($source)) {
		$c = copy($source, $dest);
		chmod($dest, 0666);
		return $c;
	}
	// Make destination directory
	if (!is_dir($dest)) {
		//$oldumask = umask(0);
		if ( mkdir($dest, 0777, TRUE) == FALSE )
			return FALSE;
		//umask($oldumask);
	}
	// Loop through the folder
	$dir = dir($source);
	while (false !== $entry = $dir->read()) {
		// Skip pointers
		if ($entry == "." || $entry == ".." ||
			($entry == '11deletedxx' && is_dir("$source/$entry"))) {
		continue;
	}
		// Deep copy directories
		if ($dest !== "$source/$entry") {
			if ( osa_copy_directory_actual("$source/$entry", "$dest/$entry") === FALSE )
				return FALSE;
		}
	}
	// Clean up
	$dir->close();
return true;
}
