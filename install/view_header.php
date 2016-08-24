<?php
/**
 * view_header.php - Header
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
if (!defined('INSTALL_PROCESS')) exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE" />
<title>STEPS - Student Tailored Educational Planning Software</title>

   <link href="http://fonts.googleapis.com/css?family=Arbutus+Slab" rel="Stylesheet" type="text/css" />
   <link rel="stylesheet" href='/steps/include/css/style.css' type="text/css" media="screen, projection" />

<script type="text/javascript">
//<![CDATA[
	function toggle_password(password, show_passwd_flag) {
		var password_box = document.getElementById(password);
		var text_box = document.createElement('input');
		with(text_box) {
			id = password_box.id;
         name = password_box.name;
         value = password_box.value;
         className = password_box.className;
         type = show_passwd_flag ? 'text' : 'password';
		}
		password_box.parentNode.replaceChild(text_box,password_box);
		return true;
	}
//]]>
</script>
</head>
<body>
<center>
<div style="margin-left:100px;text-align:left;">
<!--<img src="../images/site/header.jpg" />-->
<h2>STEPS Installation</h2>
</div>
