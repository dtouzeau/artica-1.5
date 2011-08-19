<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsSquidAdministrator){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}



if(isset($_GET["services"])){section_services();exit;}
if(isset($_GET["sharing_behavior"])){section_sharing_behavior();exit;}
if(isset($_GET["ad-status"])){active_directory_status();exit;}

tabs();



function tabs(){
		$tpl=new templates();
		$page=CurrentPageName();
		$users=new usersMenus();
	
		$array["services"]='{samba_quicklinks_services}';
		$array["blacklist_databases"]='{blacklist_databases}';
		if($users->KAV4PROXY_INSTALLED){
			$array["kav4proxy"]='{APP_KAV4PROXY}';
			
		}
		
		
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="kav4proxy"){
			$tab[]="<li><a href=\"kav4proxy.php?inline=yes\"><span style='font-size:14px'>$ligne</span></a></li>\n";
			continue;
		}
		
		
		if($num=="blacklist_databases"){
			$tab[]="<li><a href=\"squid.blacklist.php\"><span style='font-size:14px'>$ligne</span></a></li>\n";
			continue;
		}
	
		$tab[]="<li><a href=\"$page?$num=yes\"><span style='font-size:14px'>$ligne</span></a></li>\n";
			
		}
	
	
	

	$html="
		<div id='main_squid_quicklinks_config' style='background-color:white;margin-top:10px'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_squid_quicklinks_config').tabs();
			

			});
		</script>
	
	";	
	
	echo $tpl->_ENGINE_parse_body($html);

}


function section_services(){
	$tpl=new templates();
	$page=CurrentPageName();	
	
	$plugins=Paragraphe('folder-lego.png','{activate_plugins}','{activate_plugins_text}',"javascript:Loadjs('squid.popups.php?script=plugins')");
	$enable_squid_service=Paragraphe('bg-server-settings-64.png','{enable_squid_service}','{enable_squid_service_text}',"javascript:Loadjs('squid.newbee.php?reactivate-squid=yes')");
	
	$your_network=Paragraphe('folder-realyrules-64.png','{your_network}','{your_network_text}',"javascript:Loadjs('squid.popups.php?script=network')");
	$listen_port=Paragraphe('network-connection2.png','{listen_port}','{listen_port_text}',"javascript:Loadjs('squid.popups.php?script=listen_port')");
	$visible_hostname=Paragraphe('64-work-station-linux.png','{visible_hostname}','{visible_hostname_intro}',"javascript:Loadjs('squid.popups.php?script=visible_hostname')");
	$performances_tuning=Paragraphe('performance-tuning-64.png','{tune_squid_performances}','{tune_squid_performances_text}',"javascript:Loadjs('squid.perfs.php')");
	 
	
	$tr[]=$enable_squid_service;
	$tr[]=$plugins;
	$tr[]=$your_network;
	$tr[]=$listen_port;
	$tr[]=$visible_hostname;
	$tr[]=$performances_tuning;

	
	$tables[]="<table style='width:100%' class=form><tr>";
	$t=0;
	while (list ($key, $line) = each ($tr) ){
			$line=trim($line);
			if($line==null){continue;}
			$t=$t+1;
			$tables[]="<td valign='top'>$line</td>";
			if($t==2){$t=0;$tables[]="</tr><tr>";}
			}
	
	if($t<2){
		for($i=0;$i<=$t;$i++){
			$tables[]="<td valign='top'>&nbsp;</td>";				
		}
	}	
	
	$tables[]="</table>";

	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><div id='squid-status'></div></td>
		<td width=99% valign='top'><div id='squid-services'>". @implode("\n", $tables)."</div></td>
	</tr>
	</table>
	
	<script>
		LoadAjax('squid-status','squid.index.php?status=yes&hostname=&apply-settings=no');
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}