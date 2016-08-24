<?php
/**
 * view_installed.php - HTML page for application already installed
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
if (!defined('INSTALL_PROCESS')) exit('No direct script access allowed');
?>
<br /><br />
<center>
<font color="red"><h3>
This site contains a previous STEPS installation.<br /><br />

Warning, you will overwrite your previous installation if you continue using the link below.<br /><br />
Please backup your database if you plan to install to an existing database.<br /><br />
To continue the installation,
please <a href="index.php?cmd=<?php echo CMD_CHECK_REQUIREMENTS?>"><font color='blue' size='+1'>Click Here</font></a><br /><br />
</h3></font>
</center>
