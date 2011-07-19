<?php
session_start ();
include_once ('ressources/class.templates.inc');
include_once ('ressources/class.ldap.inc');
include_once ('ressources/class.users.menus.inc');
include_once ('ressources/class.computers.inc');

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["send-magick"])){sendmagick();exit;}


js();

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$comp=new computers($_GET["uid"]);
	$wakeup_computer=$tpl->javascript_parse_text("{wakeup_computer}");
	$title=$tpl->_ENGINE_parse_body("{wakeup_computer}::$comp->DisplayName");
	$user=new usersMenus();
	if(!$user->AsArticaAdministrator){echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";return;}
	echo "
	function SendMagicPackets(){
		if(confirm('$wakeup_computer $comp->DisplayName ?')){
			YahooWin2(400,'$page?popup=yes&uid={$_GET["uid"]}','$title');
		}
	
	}
	
	SendMagicPackets();
	";
	
	
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$computer=new computers($_GET["uid"]);
	$html="
	<center>
	<table class=form>
	<tr>
		<td class=legend nowrap>{computer_name}:</strong></td>
		<td align=left><strong style='font-size:13px'>$computer->uid</strong></td>
		
	</tr>								
	<tr>
		<td class=legend nowrap>{computer_ip}:</strong></td>
		<td align=left><strong style='font-size:13px'>$computer->ComputerIP</strong></td>
	</tr>			
	<tr>
	<td class=legend nowrap>{ComputerMacAddress}:</strong></td>
	<td align=left><strong style='font-size:13px'>$computer->ComputerMacAddress</strong></td>
	</tr>
	</table>
	<p>&nbsp;</p>
	<div id='wakeonlan-perform' style='height:250px;overflow:auto'></div>
	</center>
	
	<script>
		LoadAjax('wakeonlan-perform','$page?send-magick=yes&uid={$_GET["uid"]}');
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}


function sendmagick(){
	$page=CurrentPageName();
	$tpl=new templates();
	$comp=new computers($_GET["uid"]);
	$wakeup_computer=$tpl->javascript_parse_text("{wakeup_computer}");
	$title=$tpl->_ENGINE_parse_body("{wakeup_computer}:$comp->DisplayName");
	$user=new usersMenus();
	echo "<div><code style='font-size:14px'>$title</div>";
	if(!$user->AsArticaAdministrator){echo "<H2>".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."</H2>";return;}	
	echo $tpl->_ENGINE_parse_body("<div><code style='font-size:14px'>{ComputerMacAddress}: $comp->ComputerMacAddress</div>");
	
	
	$wol = new Wol();
	echo "<H2>". $tpl->_ENGINE_parse_body($wol->wake($comp->ComputerMacAddress))."</H2>";
}