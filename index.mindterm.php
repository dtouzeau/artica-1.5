<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');

$user=new usersMenus();
if($user->AsSystemAdministrator==false){exit;}

echo "
  <APPLET CODE=\"com.mindbright.application.MindTerm.class\"
          ARCHIVE=\"js/mindterm.jar\" WIDTH=0 HEIGHT=0>
           <PARAM NAME=\"server\" value=\"{$_SERVER['SERVER_NAME']}\">
  
    <PARAM NAME=\"sepframe\" value=\"true\">
    <PARAM NAME=\"debug\" value=\"true\">
  </APPLET>";
?>