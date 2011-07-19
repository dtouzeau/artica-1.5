<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ldap.inc');
	
	$user=new usersMenus();
	if(!$user->AsSystemAdministrator){echo "alert('no privileges');";die();}

	if(isset($_GET["disks-list"])){disks_list();exit;}
	if(isset($_GET["tools"])){tools();exit;}
	
	
	start();
	
	
	
function start(){
	$page=CurrentPageName();
	$html="<table style='width:720px'>
	<tr>
		<td valign='top' width=1%><div id='loop-tools'></div>
		<td valign='top' width=99%><div id='loop-disks-list'></div>
	</tr>
	</table>
	
	
	<script>
		function refreshLoopList(){
			LoadAjax('loop-disks-list','$page?disks-list=yes');
		}
	refreshLVMList();
	</script>
	
	";
	echo $html;
	
}

function disks_list(){
	$page=CurrentPageName();
	echo "
	<script>
		LoadAjax('loop-tools','$page?tools=yes');
	</script>";
}
function tools(){
	$page=CurrentPageName();
	$tpl=new templates();
	$p=Paragraphe("64-hd-plus.png","{create_new_disk}","{create_new_virtual_disk}","javascript:LoopAddForm()");
	
	$html="
	$p
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

